<?php

namespace App\Services\Install;

use PDO;
use PDOException;
use Exception;

/**
 * Simplified WordPress-style Installer
 * Single-file SQL import approach for reliability
 */
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
        $installedFile = __DIR__ . '/../../../.installed';
        return file_exists($installedFile);
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
            ]);

            // Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
            $dbExists = $stmt->fetch();

            // Get MySQL version
            $version = $pdo->query("SELECT VERSION()")->fetchColumn();

            return [
                'success' => true,
                'message' => 'Database connection successful',
                'database_exists' => (bool)$dbExists,
                'mysql_version' => $version
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Run complete installation
     */
    public function runInstallation(array $config): array
    {
        try {
            $this->updateProgress('Starting installation...', 0);

            // Step 1: Update .env file
            $this->updateProgress('Configuring environment...', 10);
            $this->updateEnvFile($config);

            // Step 2: Create database
            $this->updateProgress('Creating database...', 20);
            $this->createDatabase($config);

            // Step 3: Import schema
            $this->updateProgress('Installing database schema...', 30);
            $this->importSchema($config);

            // Step 4: Create default tenant
            $this->updateProgress('Setting up company...', 60);
            $this->createTenant($config);

            // Step 5: Create admin user
            $this->updateProgress('Creating admin account...', 80);
            $this->createAdminUser($config);

            // Step 6: Finalize
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
     * Update .env file
     */
    private function updateEnvFile(array $config): void
    {
        $envPath = __DIR__ . '/../../../.env';
        $envExamplePath = __DIR__ . '/../../../.env.example';

        if (file_exists($envExamplePath)) {
            $envContent = file_get_contents($envExamplePath);
        } else {
            throw new Exception('.env.example file not found');
        }

        // Generate security keys
        $appKey = bin2hex(random_bytes(32));
        $jwtSecret = bin2hex(random_bytes(64));

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

        if (file_put_contents($envPath, $envContent) === false) {
            throw new Exception('Failed to write .env file');
        }
    }

    /**
     * Create database
     */
    private function createDatabase(array $config): void
    {
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_username'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_database']}`
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Import database schema from single SQL file and run all migrations
     */
    private function importSchema(array $config): void
    {
        $sqlFile = __DIR__ . '/../../../database/install.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("Installation SQL file not found: {$sqlFile}");
        }

        // Connect to the database
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_username'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // Execute the base SQL file
        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);
        
        $this->updateProgress('Running database migrations...', 40);
        
        // Run all migrations
        $this->runMigrations($pdo);
    }
    
    /**
     * Run all database migrations
     */
    private function runMigrations(PDO $pdo): void
    {
        $migrationsDir = __DIR__ . '/../../../database/migrations';
        
        if (!is_dir($migrationsDir)) {
            return; // No migrations directory, skip
        }
        
        // Get all .sql files sorted
        $migrations = glob($migrationsDir . '/*.sql');
        sort($migrations);
        
        foreach ($migrations as $migrationFile) {
            $migrationName = basename($migrationFile);
            
            // Check if already run
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
            $stmt->execute([$migrationName]);
            $alreadyRun = $stmt->fetchColumn();
            
            if ($alreadyRun > 0) {
                continue; // Skip already applied migrations
            }
            
            try {
                // Read and execute migration
                $sql = file_get_contents($migrationFile);
                $pdo->exec($sql);
                
                // Record migration
                $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, 1)");
                $stmt->execute([$migrationName]);
            } catch (Exception $e) {
                // Log but continue - some migrations may fail if tables already exist
                error_log("Migration {$migrationName} failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Create default tenant
     */
    private function createTenant(array $config): void
    {
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_username'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $tenantUuid = $this->generateUUID();
        
        $stmt = $pdo->prepare("
            INSERT INTO tenants (id, tenant_uuid, company_name, subdomain, contact_email, status, created_at)
            VALUES (1, ?, ?, 'default', ?, 'active', NOW())
        ");
        
        $stmt->execute([
            $tenantUuid,
            $config['app_name'],
            $config['admin_email']
        ]);
    }

    /**
     * Create admin user
     */
    private function createAdminUser(array $config): void
    {
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_username'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $passwordHash = password_hash($config['admin_password'], PASSWORD_BCRYPT);

        // Create user
        $stmt = $pdo->prepare("
            INSERT INTO users (tenant_id, first_name, last_name, email, password_hash, is_active, created_at)
            VALUES (1, ?, ?, ?, ?, 1, NOW())
        ");
        
        $stmt->execute([
            $config['admin_first_name'],
            $config['admin_last_name'],
            $config['admin_email'],
            $passwordHash
        ]);

        $userId = $pdo->lastInsertId();

        // Assign Super Admin role (role_id = 1)
        $stmt = $pdo->prepare("
            INSERT INTO user_roles (user_id, role_id)
            VALUES (?, 1)
        ");
        
        $stmt->execute([$userId]);
    }

    /**
     * Finalize installation
     */
    private function finalizeInstallation(): void
    {
        // Create .installed file
        $installedFile = __DIR__ . '/../../../.installed';
        file_put_contents($installedFile, date('Y-m-d H:i:s'));

        // Create storage directories
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
    }

    /**
     * Generate UUID v4
     */
    private function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
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

        @file_put_contents($this->progressFile, json_encode($this->progress));
    }

    /**
     * Get installation progress
     */
    public function getProgress(): array
    {
        if (file_exists($this->progressFile)) {
            $progress = json_decode(file_get_contents($this->progressFile), true);
            return $progress ?: ['message' => 'Waiting...', 'percent' => 0];
        }

        return ['message' => 'Waiting...', 'percent' => 0];
    }
}
