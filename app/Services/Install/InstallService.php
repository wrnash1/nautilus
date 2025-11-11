<?php

namespace App\Services\Install;

use PDO;
use PDOException;
use Exception;

class InstallService
{
    private array $progress = [];
    private string $progressFile;

    public function __construct()
    {
        $this->progressFile = __DIR__ . '/../../../storage/install_progress.json';
    }

    /**
     * Check if application is already installed
     */
    public function isInstalled(): bool
    {
        try {
            // Check if .env has database credentials
            $envPath = __DIR__ . '/../../../.env';

            if (!file_exists($envPath)) {
                return false;
            }

            $envContent = file_get_contents($envPath);

            // Check if DB_DATABASE is set and not empty
            if (!preg_match('/^DB_DATABASE=(.+)$/m', $envContent, $matches)) {
                return false;
            }

            $dbName = trim($matches[1]);
            if (empty($dbName)) {
                return false;
            }

            // Try to connect and check if migrations table exists
            try {
                $pdo = $this->createDatabaseConnection();
                $stmt = $pdo->query("SHOW TABLES LIKE 'migrations'");
                $result = $stmt->fetch();
                $stmt->closeCursor();

                if (!$result) {
                    return false;
                }

                // Check if at least one migration has been run
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM migrations");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                return $count['count'] > 0;
            } catch (PDOException $e) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Test database connection
     */
    public function testDatabaseConnection(
        string $host,
        string $port,
        string $database,
        string $username,
        string $password
    ): array {
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
            $dbExists = $stmt->fetch();
            $stmt->closeCursor();

            // Check MySQL version
            $stmt = $pdo->query("SELECT VERSION() as version");
            $version = $stmt->fetch();
            $stmt->closeCursor();

            return [
                'success' => true,
                'message' => 'Database connection successful',
                'database_exists' => (bool)$dbExists,
                'mysql_version' => $version['version']
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Run full installation
     */
    public function runInstallation(array $config): array
    {
        try {
            $this->updateProgress('Starting installation...', 0);

            // Step 1: Update .env file
            $this->updateProgress('Updating .env file...', 10);
            $this->updateEnvFile($config);

            // Step 2: Create database if it doesn't exist
            $this->updateProgress('Creating database...', 20);
            $this->createDatabase($config);

            // Step 3: Run migrations
            $this->updateProgress('Running database migrations...', 30);
            $migrationResult = $this->runMigrations();

            if (!$migrationResult['success']) {
                throw new Exception($migrationResult['message']);
            }

            // Step 4: Seed initial data (roles, permissions)
            $this->updateProgress('Seeding initial data...', 70);
            $this->seedInitialData();

            // Step 5: Seed certification agencies and cash drawers
            $this->updateProgress('Seeding certification agencies...', 72);
            $this->seedCertificationAgencies();

            $this->updateProgress('Seeding cash drawers and customer tags...', 74);
            $this->seedCashDrawers();

            // Step 6: Save company settings
            $this->updateProgress('Saving company settings...', 76);
            $this->saveCompanySettings($config);

            // Step 7: Create admin user
            $this->updateProgress('Creating admin user...', 80);
            $this->createAdminUser($config);

            // Step 8: Install demo data if requested
            if ($config['install_demo_data']) {
                $this->updateProgress('Installing demo data...', 85);
                $this->installDemoData();
            }

            // Step 9: Finalize
            $this->updateProgress('Finalizing installation...', 95);
            $this->finalizeInstallation();

            $this->updateProgress('Installation complete!', 100);

            return [
                'success' => true,
                'message' => 'Installation completed successfully!',
                'admin_email' => $config['admin_email']
            ];
        } catch (Exception $e) {
            $this->updateProgress('Installation failed: ' . $e->getMessage(), -1);

            return [
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update .env file with configuration
     */
    private function updateEnvFile(array $config): void
    {
        $envPath = __DIR__ . '/../../../.env';
        $envExamplePath = __DIR__ . '/../../../.env.example';

        // Load .env.example as template
        if (file_exists($envExamplePath)) {
            $envContent = file_get_contents($envExamplePath);
        } else {
            throw new Exception('.env.example file not found');
        }

        // Generate security keys
        $appKey = $this->generateRandomKey(32);
        $jwtSecret = $this->generateRandomKey(64);

        // Replace placeholders
        $replacements = [
            '/^APP_NAME=.*$/m' => 'APP_NAME="' . $config['app_name'] . '"',
            '/^APP_URL=.*$/m' => 'APP_URL=' . $config['app_url'],
            '/^APP_TIMEZONE=.*$/m' => 'APP_TIMEZONE=' . $config['app_timezone'],
            '/^APP_KEY=.*$/m' => 'APP_KEY=' . $appKey,
            '/^JWT_SECRET=.*$/m' => 'JWT_SECRET=' . $jwtSecret,
            '/^DB_HOST=.*$/m' => 'DB_HOST=' . $config['db_host'],
            '/^DB_PORT=.*$/m' => 'DB_PORT=' . $config['db_port'],
            '/^DB_DATABASE=.*$/m' => 'DB_DATABASE=' . $config['db_database'],
            '/^DB_USERNAME=.*$/m' => 'DB_USERNAME=' . $config['db_username'],
            '/^DB_PASSWORD=.*$/m' => 'DB_PASSWORD=' . $config['db_password'],
        ];

        foreach ($replacements as $pattern => $replacement) {
            $envContent = preg_replace($pattern, $replacement, $envContent);
        }

        // Write to .env file
        if (file_put_contents($envPath, $envContent) === false) {
            throw new Exception('Failed to write .env file');
        }

        // Reload environment variables
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
        $dotenv->load();
    }

    /**
     * Create database if it doesn't exist
     */
    private function createDatabase(array $config): void
    {
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_username'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_database']}`
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Run database migrations
     */
    private function runMigrations(): array
    {
        try {
            // Use mysqli instead of PDO to avoid buffering issues
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            // Create mysqli connection for migration execution
            $mysqli = new \mysqli($host, $username, $password, $database, $port);

            if ($mysqli->connect_error) {
                throw new Exception("Connection failed: " . $mysqli->connect_error);
            }

            $mysqli->set_charset("utf8mb4");

            // Create migrations table
            $mysqli->query("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    batch INT NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // Get executed migrations
            $result = $mysqli->query("SELECT migration FROM migrations");
            $executed = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $executed[] = $row['migration'];
                }
                $result->free();
            }

            // Get current batch
            $result = $mysqli->query("SELECT MAX(batch) as max_batch FROM migrations");
            $lastBatch = $result ? $result->fetch_assoc() : null;
            $result?->free();
            $currentBatch = ($lastBatch['max_batch'] ?? 0) + 1;

            $migrationsDir = __DIR__ . '/../../../database/migrations';
            $files = glob($migrationsDir . '/*.sql');
            sort($files);

            $newMigrations = 0;
            $totalMigrations = count($files);
            $current = 0;

            foreach ($files as $file) {
                $filename = basename($file);
                $current++;

                if (in_array($filename, $executed)) {
                    continue;
                }

                $progress = 30 + (($current / $totalMigrations) * 40);
                $this->updateProgress("Running migration: {$filename}", (int)$progress);

                // Read the entire SQL file
                $sql = file_get_contents($file);

                // Execute using multi_query
                if (!$mysqli->multi_query($sql)) {
                    throw new Exception("Error in {$filename}: " . $mysqli->error);
                }

                // Clear all result sets
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());

                // Check for errors
                if ($mysqli->error) {
                    throw new Exception("Error in {$filename}: " . $mysqli->error);
                }

                // Record migration
                $stmt = $mysqli->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->bind_param("si", $filename, $currentBatch);
                $stmt->execute();
                $stmt->close();

                $newMigrations++;
            }

            $mysqli->close();

            return [
                'success' => true,
                'message' => "Successfully executed {$newMigrations} migration(s)"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Migration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Seed initial data (roles, permissions)
     */
    private function seedInitialData(): void
    {
        $pdo = $this->createDatabaseConnection();
        $seedFile = __DIR__ . '/../../../database/seeds/001_seed_initial_data.sql';

        if (!file_exists($seedFile)) {
            throw new Exception('Seed file not found');
        }

        $sql = file_get_contents($seedFile);

        // Extract only roles and permissions sections (not demo users, products, etc.)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt) && !preg_match('/^\s*--/', $stmt)
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                // Only execute role and permission inserts, skip demo data
                if (
                    stripos($statement, 'INSERT INTO roles') !== false ||
                    stripos($statement, 'INSERT INTO permissions') !== false ||
                    stripos($statement, 'INSERT INTO role_permissions') !== false
                ) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Ignore duplicate errors
                        if ($e->getCode() != 23000) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    /**
     * Create admin user
     */
    private function createAdminUser(array $config): void
    {
        $pdo = $this->createDatabaseConnection();

        // Create default tenant if it doesn't exist
        $stmt = $pdo->query("SELECT id FROM tenants WHERE id = 1 LIMIT 1");
        $defaultTenant = $stmt->fetch();
        $stmt->closeCursor();

        if (!$defaultTenant) {
            // Create default tenant
            $tenantUuid = $this->generateUUID();
            $subdomain = $this->generateSubdomain($config['app_name'] ?? 'default');

            $stmt = $pdo->prepare("
                INSERT INTO tenants (id, tenant_uuid, company_name, subdomain, contact_email, status, created_at)
                VALUES (1, ?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->execute([
                $tenantUuid,
                $config['app_name'] ?? 'Default Company',
                $subdomain,
                $config['admin_email']
            ]);
            $stmt->closeCursor();
            $tenantId = 1;
        } else {
            $tenantId = $defaultTenant['id'];
        }

        // Check if admin role exists
        $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
        $adminRole = $stmt->fetch();
        $stmt->closeCursor();

        if (!$adminRole) {
            throw new Exception('Admin role not found. Please ensure initial data was seeded.');
        }

        $passwordHash = password_hash($config['admin_password'], PASSWORD_BCRYPT);

        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$config['admin_email']]);
        $existingUser = $stmt->fetch();
        $stmt->closeCursor();

        if ($existingUser) {
            // Update existing user
            $stmt = $pdo->prepare("
                UPDATE users
                SET tenant_id = ?,
                    role_id = ?,
                    first_name = ?,
                    last_name = ?,
                    password_hash = ?,
                    is_active = 1
                WHERE email = ?
            ");
            $stmt->execute([
                $tenantId,
                $adminRole['id'],
                $config['admin_first_name'],
                $config['admin_last_name'],
                $passwordHash,
                $config['admin_email']
            ]);
            $stmt->closeCursor();
        } else {
            // Insert new user
            $stmt = $pdo->prepare("
                INSERT INTO users (tenant_id, role_id, email, password_hash, first_name, last_name, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([
                $tenantId,
                $adminRole['id'],
                $config['admin_email'],
                $passwordHash,
                $config['admin_first_name'],
                $config['admin_last_name']
            ]);
            $stmt->closeCursor();
        }
    }

    /**
     * Generate a UUID v4
     */
    private function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Generate a subdomain from company name
     */
    private function generateSubdomain(string $companyName): string
    {
        $subdomain = strtolower($companyName);
        $subdomain = preg_replace('/[^a-z0-9]+/', '-', $subdomain);
        $subdomain = trim($subdomain, '-');
        $subdomain = substr($subdomain, 0, 50);

        if (empty($subdomain)) {
            $subdomain = 'company-' . substr(md5(uniqid()), 0, 8);
        }

        return $subdomain;
    }

    /**
     * Install demo data
     */
    private function installDemoData(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        $mysqli = new \mysqli($host, $username, $password, $database, $port);

        if ($mysqli->connect_error) {
            throw new \Exception("Connection failed: " . $mysqli->connect_error);
        }

        $mysqli->set_charset("utf8mb4");

        $seedFile = __DIR__ . '/../../../database/seeds/002_seed_demo_data.sql';

        if (file_exists($seedFile)) {
            $sql = file_get_contents($seedFile);

            // Execute using multi_query
            if ($mysqli->multi_query($sql)) {
                // Clear all result sets
                do {
                    if ($result = $mysqli->store_result()) {
                        $result->free();
                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }

            // Check for errors (ignore duplicate key errors)
            if ($mysqli->error && $mysqli->errno != 1062) {
                throw new \Exception("Demo data installation failed: " . $mysqli->error);
            }
        }

        $mysqli->close();
    }

    /**
     * Save company settings to database
     */
    private function saveCompanySettings(array $config): void
    {
        $pdo = $this->createDatabaseConnection();

        // Save business/company name to settings
        $stmt = $pdo->prepare("
            INSERT INTO settings (category, `key`, `value`, type, description, updated_at)
            VALUES ('general', 'business_name', ?, 'string', 'Company/Business Name', NOW())
            ON DUPLICATE KEY UPDATE `value` = ?, updated_at = NOW()
        ");
        $stmt->execute([$config['app_name'], $config['app_name']]);
        $stmt->closeCursor();

        // Save other initial settings if needed
        $defaultSettings = [
            ['general', 'timezone', $config['app_timezone'] ?? 'America/New_York', 'string', 'System Timezone'],
            ['general', 'currency', 'USD', 'string', 'Default Currency'],
            ['general', 'date_format', 'Y-m-d', 'string', 'Date Format'],
            ['general', 'time_format', 'H:i:s', 'string', 'Time Format'],
        ];

        foreach ($defaultSettings as $setting) {
            $stmt = $pdo->prepare("
                INSERT INTO settings (category, `key`, `value`, type, description, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE updated_at = NOW()
            ");
            $stmt->execute($setting);
            $stmt->closeCursor();
        }
    }

    /**
     * Seed certification agencies
     */
    private function seedCertificationAgencies(): void
    {
        $pdo = $this->createDatabaseConnection();

        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'certification_agencies'");
        $tableExists = $stmt->fetch();
        $stmt->closeCursor();

        if (!$tableExists) {
            // Table doesn't exist yet, skip silently (will be created by migrations)
            return;
        }

        // Check if already seeded
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM certification_agencies");
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result['count'] > 0) {
            // Already seeded, skip
            return;
        }

        // Run seeder
        $seederFile = __DIR__ . '/../../../database/seeders/certification_agencies.sql';

        if (!file_exists($seederFile)) {
            // Seeder doesn't exist yet, skip silently
            return;
        }

        try {
            $sql = file_get_contents($seederFile);

            // Use mysqli for multi-query support
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            $mysqli = new \mysqli($host, $username, $password, $database, $port);
            $mysqli->set_charset("utf8mb4");

            if (!$mysqli->multi_query($sql)) {
                throw new \Exception("Error seeding certification agencies: " . $mysqli->error);
            }

            // Clear all result sets
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            $mysqli->close();
        } catch (\Exception $e) {
            // Log error but don't fail installation
            error_log("Failed to seed certification agencies: " . $e->getMessage());
        }
    }

    /**
     * Seed cash drawers and customer tags
     */
    private function seedCashDrawers(): void
    {
        $pdo = $this->createDatabaseConnection();

        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'cash_drawers'");
        $tableExists = $stmt->fetch();
        $stmt->closeCursor();

        if (!$tableExists) {
            // Table doesn't exist yet, skip silently (will be created by migration 041)
            return;
        }

        // Check if already seeded
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM cash_drawers");
        $result = $stmt->fetch();
        $stmt->closeCursor();

        if ($result['count'] > 0) {
            // Already seeded, skip
            return;
        }

        // Run seeder
        $seederFile = __DIR__ . '/../../../database/seeders/cash_drawers.sql';

        if (!file_exists($seederFile)) {
            // Seeder doesn't exist yet, skip silently
            return;
        }

        try {
            $sql = file_get_contents($seederFile);

            // Use mysqli for multi-query support
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? '';
            $username = $_ENV['DB_USERNAME'] ?? '';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            $mysqli = new \mysqli($host, $username, $password, $database, $port);
            $mysqli->set_charset("utf8mb4");

            if (!$mysqli->multi_query($sql)) {
                throw new \Exception("Error seeding cash drawers: " . $mysqli->error);
            }

            // Clear all result sets
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());

            $mysqli->close();
        } catch (\Exception $e) {
            // Log error but don't fail installation
            error_log("Failed to seed cash drawers: " . $e->getMessage());
        }
    }

    /**
     * Finalize installation
     */
    private function finalizeInstallation(): void
    {
        // Create storage directories if they don't exist
        $directories = [
            __DIR__ . '/../../../storage/backups',
            __DIR__ . '/../../../storage/cache',
            __DIR__ . '/../../../storage/logs',
            __DIR__ . '/../../../storage/sessions',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Set permissions
        chmod(__DIR__ . '/../../../storage', 0755);
    }

    /**
     * Create database connection using current environment
     */
    private function createDatabaseConnection(): PDO
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? '';
        $username = $_ENV['DB_USERNAME'] ?? '';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true  // Fix for migration PDO buffering errors
        ]);
    }

    /**
     * Generate random key
     */
    private function generateRandomKey(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Update installation progress
     */
    private function updateProgress(string $message, int $percent): void
    {
        $this->progress = [
            'message' => $message,
            'percent' => $percent,
            'timestamp' => time()
        ];

        // Ensure storage directory exists
        $storageDir = dirname($this->progressFile);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        file_put_contents($this->progressFile, json_encode($this->progress));
    }

    /**
     * Get installation progress
     */
    public function getProgress(): array
    {
        if (file_exists($this->progressFile)) {
            $content = file_get_contents($this->progressFile);
            return json_decode($content, true) ?? ['message' => 'Starting...', 'percent' => 0];
        }

        return ['message' => 'Starting...', 'percent' => 0];
    }
}
