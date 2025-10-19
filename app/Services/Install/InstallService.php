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

                if (!$result) {
                    return false;
                }

                // Check if at least one migration has been run
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM migrations");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);

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

            // Check MySQL version
            $stmt = $pdo->query("SELECT VERSION() as version");
            $version = $stmt->fetch();

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

            // Step 5: Create admin user
            $this->updateProgress('Creating admin user...', 80);
            $this->createAdminUser($config);

            // Step 6: Install demo data if requested
            if ($config['install_demo_data']) {
                $this->updateProgress('Installing demo data...', 85);
                $this->installDemoData();
            }

            // Step 7: Finalize
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
            $pdo = $this->createDatabaseConnection();

            // Create migrations table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    batch INT NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
            $lastBatch = $pdo->query("SELECT MAX(batch) as max_batch FROM migrations")->fetch();
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

                $sql = file_get_contents($file);

                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    fn($stmt) => !empty($stmt) && !preg_match('/^\s*--/', $stmt)
                );

                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }

                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$filename, $currentBatch]);

                $newMigrations++;
            }

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

        // Check if admin role exists
        $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
        $adminRole = $stmt->fetch();

        if (!$adminRole) {
            throw new Exception('Admin role not found. Please ensure initial data was seeded.');
        }

        $passwordHash = password_hash($config['admin_password'], PASSWORD_BCRYPT);

        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$config['admin_email']]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            // Update existing user
            $stmt = $pdo->prepare("
                UPDATE users
                SET role_id = ?,
                    first_name = ?,
                    last_name = ?,
                    password_hash = ?,
                    is_active = 1
                WHERE email = ?
            ");
            $stmt->execute([
                $adminRole['id'],
                $config['admin_first_name'],
                $config['admin_last_name'],
                $passwordHash,
                $config['admin_email']
            ]);
        } else {
            // Insert new user
            $stmt = $pdo->prepare("
                INSERT INTO users (role_id, email, password_hash, first_name, last_name, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            $stmt->execute([
                $adminRole['id'],
                $config['admin_email'],
                $passwordHash,
                $config['admin_first_name'],
                $config['admin_last_name']
            ]);
        }
    }

    /**
     * Install demo data
     */
    private function installDemoData(): void
    {
        $pdo = $this->createDatabaseConnection();
        $seedFile = __DIR__ . '/../../../database/seeds/002_seed_demo_data.sql';

        if (file_exists($seedFile)) {
            $sql = file_get_contents($seedFile);

            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($stmt) => !empty($stmt) && !preg_match('/^\s*--/', $stmt)
            );

            foreach ($statements as $statement) {
                if (!empty($statement)) {
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
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
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
