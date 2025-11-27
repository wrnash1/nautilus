<?php
/**
 * Database Installer with Complete Migration Support
 * Installs ALL database migrations in correct order
 */

header('Content-Type: application/json');

session_start();

try {
    // Get database configuration from session
    $dbHost = $_SESSION['install_config']['db_host'] ?? 'localhost';
    $dbName = $_SESSION['install_config']['db_name'] ?? 'nautilus';
    $dbUser = $_SESSION['install_config']['db_user'] ?? 'root';
    $dbPass = $_SESSION['install_config']['db_password'] ?? '';
    $dbPort = $_SESSION['install_config']['db_port'] ?? '3306'; // Assuming db_port is now in install_config
    $dbConnection = $_SESSION['install_config']['db_connection'] ?? 'mysql'; // NEW: Get connection type

    // Determine migration directory based on database type
    $migrationsDir = match($dbConnection) {
        'pgsql', 'postgresql' => dirname(__DIR__, 2) . '/database/migrations/postgresql',
        'mysql', 'mariadb' => dirname(__DIR__, 2) . '/database/migrations',
        default => dirname(__DIR__, 2) . '/database/migrations'
    };

    // Build DSN based on database type
    $dsn = match($dbConnection) {
        'pgsql', 'postgresql' => "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName",
        'mysql', 'mariadb' => "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
        default => "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4"
    };

    // Connect to database
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Log connection info
    error_log("Database installer: Using $dbConnection with migrations from $migrationsDir");

    // Find all migration files
    
    if (!is_dir($migrationsDir)) {
        throw new Exception('Migrations directory not found: ' . $migrationsDir);
    }

    // Get all .sql files and sort them
    $migrationFiles = glob($migrationsDir . '/*.sql');
    if (empty($migrationFiles)) {
        throw new Exception('No migration files found in: ' . $migrationsDir);
    }

    // Sort files alphanumerically to ensure correct order
    sort($migrationFiles);

    $totalMigrations = count($migrationFiles);
    $executedMigrations = 0;
    $failedMigrations = [];
    $executedStatements = 0;

    // Execute each migration file
    foreach ($migrationFiles as $index => $migrationFile) {
        $migrationName = basename($migrationFile);
        $migrationNumber = $index + 1;

        try {
            // Read migration file
            $sql = file_get_contents($migrationFile);
            
            // Remove comments (both -- and /* */ style)
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

            // Split by semicolons and filter empty statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($stmt) => !empty($stmt)
            );

            // Execute each statement in the migration
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $stmt = $pdo->prepare($statement);
                        $stmt->execute();
                        $stmt->closeCursor();
                        $executedStatements++;
                    } catch (PDOException $e) {
                        // Log but continue - some statements may fail due to dependencies
                        error_log("Statement error in {$migrationName}: " . $e->getMessage());
                    }
                }
            }

            $executedMigrations++;

        } catch (Exception $e) {
            // Log failed migration but continue with others
            $failedMigrations[] = [
                'file' => $migrationName,
                'error' => $e->getMessage()
            ];
            error_log("Migration failed: {$migrationName} - " . $e->getMessage());
        }
    }

    // Update company_settings with business email
    try {
        $stmt = $pdo->prepare("UPDATE company_settings SET business_email = ? WHERE tenant_id = 1");
        $stmt->execute([$config['admin_email']]);
        $stmt->closeCursor();
    } catch (PDOException $e) {
        error_log("Could not update company_settings: " . $e->getMessage());
    }

    // Update or Insert timezone in settings table
    try {
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = 'timezone'");
        $stmt->execute();
        $timezoneExists = $stmt->fetch();
        $stmt->closeCursor();

        if ($timezoneExists) {
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'timezone'");
            $stmt->execute([$config['timezone']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO settings (category, setting_key, setting_value, type, description) VALUES ('general', 'timezone', ?, 'string', 'System Timezone')");
            $stmt->execute([$config['timezone']]);
        }
        $stmt->closeCursor();
    } catch (PDOException $e) {
        error_log("Could not update timezone: " . $e->getMessage());
    }

    // Get final table count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '{$config['db_name']}'");
    $tableCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $stmt->closeCursor();

    // Get record counts from key tables
    $recordCounts = [];
    $keyTables = ['users', 'roles', 'certification_agencies', 'products', 'customers'];
    
    foreach ($keyTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $recordCounts[$table] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $recordCounts[$table] = 0;
        }
    }

    // Install Demo Data if requested
    $demoInstalled = false;
    if (isset($_GET['demo']) && $_GET['demo'] == '1') {
        $demoFile = dirname(__DIR__, 2) . '/database/seeders/demo_data.sql';
        if (file_exists($demoFile)) {
            $demoSql = file_get_contents($demoFile);
            $demoStatements = array_filter(
                array_map('trim', explode(';', $demoSql)),
                fn($stmt) => !empty($stmt) && !preg_match('/^--/', $stmt)
            );
            
            foreach ($demoStatements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore errors for demo data (e.g. duplicates)
                    }
                }
            }
            $demoInstalled = true;
        }
    }

    // Create installation complete marker
    $installMarker = dirname(__DIR__, 2) . '/.installed';
    file_put_contents($installMarker, date('Y-m-d H:i:s'));

    // Clear install session
    unset($_SESSION['install_config']);

    // Return comprehensive results
    echo json_encode([
        'success' => true,
        'message' => 'Database installed successfully',
        'stats' => [
            'total_migrations' => $totalMigrations,
            'executed_migrations' => $executedMigrations,
            'failed_migrations' => count($failedMigrations),
            'statements_executed' => $executedStatements,
            'tables_created' => $tableCount,
            'record_counts' => $recordCounts,
            'demo_data_installed' => $demoInstalled
        ],
        'failed_migrations' => $failedMigrations,
        'default_credentials' => [
            'email' => 'admin@nautilus.local',
            'password' => 'admin123',
            'warning' => 'Please change the default password immediately after login!'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
