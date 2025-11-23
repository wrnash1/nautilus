<?php
/**
 * Database Installer
 * Installs core database schema and creates default data
 */

header('Content-Type: application/json');

session_start();

try {
    // Get configuration from session
    if (!isset($_SESSION['install_config'])) {
        throw new Exception('Configuration not found. Please complete step 2 first.');
    }

    $config = $_SESSION['install_config'];

    // Connect to database
    $dsn = "mysql:host={$config['db_host']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Connect to the database
    $pdo->exec("USE `{$config['db_name']}`");

    // Read and execute core schema
    $schemaFile = dirname(__DIR__, 2) . '/database/migrations/000_CORE_SCHEMA.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception('Core schema file not found: ' . $schemaFile);
    }

    $sql = file_get_contents($schemaFile);

    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Split by semicolons and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($stmt) => !empty($stmt)
    );

    $executedStatements = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            $executedStatements++;
        }
    }

    // Update company_settings with business name
    $stmt = $pdo->prepare("UPDATE company_settings SET setting_value = ? WHERE setting_key = 'business_name'");
    $stmt->execute([$config['business_name']]);

    $stmt = $pdo->prepare("UPDATE company_settings SET setting_value = ? WHERE setting_key = 'admin_email'");
    $stmt->execute([$config['admin_email']]);

    $stmt = $pdo->prepare("UPDATE company_settings SET setting_value = ? WHERE setting_key = 'timezone'");
    $stmt->execute([$config['timezone']]);

    // Get table count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '{$config['db_name']}'");
    $tableCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get record counts
    $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'];
    $roleCount = $pdo->query("SELECT COUNT(*) as count FROM roles")->fetch(PDO::FETCH_ASSOC)['count'];
    $certAgencyCount = $pdo->query("SELECT COUNT(*) as count FROM certification_agencies")->fetch(PDO::FETCH_ASSOC)['count'];

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

    // Create installation complete marker (matches what index.php checks)
    $installMarker = dirname(__DIR__, 2) . '/.installed';
    file_put_contents($installMarker, date('Y-m-d H:i:s'));

    // Clear install session
    unset($_SESSION['install_config']);

    echo json_encode([
        'success' => true,
        'message' => 'Database installed successfully',
        'stats' => [
            'statements_executed' => $executedStatements,
            'tables_created' => $tableCount,
            'users_created' => $userCount,
            'roles_created' => $roleCount,
            'certification_agencies' => $certAgencyCount,
            'demo_data_installed' => $demoInstalled
        ],
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
