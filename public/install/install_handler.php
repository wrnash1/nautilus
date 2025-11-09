<?php
/**
 * Installation Handler
 *
 * Processes AJAX requests from the installation wizard
 */

session_start();

// Security check - only allow during installation
if (file_exists(__DIR__ . '/../../.installed')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Application is already installed']);
    exit;
}

// Get action from POST
$action = $_POST['action'] ?? '';

// Response helper
function respond($success, $log = '', $error = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'log' => $log,
        'error' => $error
    ]);
    exit;
}

// Execute installation step
try {
    switch ($action) {
        case 'create_env':
            createEnvironmentFile();
            break;

        case 'create_database':
            createDatabaseTables();
            break;

        case 'run_migrations':
            runMigrations();
            break;

        case 'create_admin':
            createAdminAccount();
            break;

        case 'setup_directories':
            setupDirectories();
            break;

        case 'load_sample_data':
            loadSampleData();
            break;

        case 'finalize':
            finalizeInstallation();
            break;

        default:
            respond(false, '', 'Invalid action');
    }
} catch (Exception $e) {
    respond(false, '', $e->getMessage());
}

/**
 * Create .env file with configuration
 */
function createEnvironmentFile() {
    $basePath = dirname(dirname(__DIR__));
    $envPath = $basePath . '/.env';

    $dbConfig = $_SESSION['db_config'] ?? [];
    $appConfig = $_SESSION['app_config'] ?? [];

    $envContent = <<<ENV
# Application Configuration
APP_NAME="{$appConfig['app_name']}"
APP_URL="{$appConfig['app_url']}"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE="{$appConfig['timezone']}"

# Company Information
COMPANY_NAME="{$appConfig['company_name']}"
COMPANY_EMAIL="{$appConfig['company_email']}"
COMPANY_PHONE="{$appConfig['company_phone']}"

# Regional Settings
DEFAULT_CURRENCY="{$appConfig['currency']}"
DEFAULT_LOCALE="{$appConfig['locale']}"

# Database Configuration
DB_CONNECTION=mysql
DB_HOST={$dbConfig['host']}
DB_PORT={$dbConfig['port']}
DB_DATABASE={$dbConfig['database']}
DB_USERNAME={$dbConfig['username']}
DB_PASSWORD={$dbConfig['password']}

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=file

# Email Configuration (Update with your SMTP settings)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="{$appConfig['company_email']}"
MAIL_FROM_NAME="{$appConfig['company_name']}"

# Security
APP_KEY=
SESSION_SECURE=false
SESSION_HTTPONLY=true

# Logging
LOG_LEVEL=info
LOG_CHANNEL=daily

# Analytics
ANALYTICS_CACHE_ENABLED=true
ANALYTICS_CACHE_TTL=3600

# Notifications
NOTIFICATIONS_ENABLED=true
LOW_STOCK_NOTIFICATIONS=true
MAINTENANCE_NOTIFICATIONS=true

# File Upload
MAX_UPLOAD_SIZE=10485760
ALLOWED_EXTENSIONS=jpg,jpeg,png,pdf,doc,docx

# Backup
BACKUP_RETENTION_DAYS=30
ENV;

    if (file_put_contents($envPath, $envContent) === false) {
        throw new Exception('Failed to create .env file. Check directory permissions.');
    }

    respond(true, "Environment file created at {$envPath}");
}

/**
 * Create initial database tables
 */
function createDatabaseTables() {
    $dbConfig = $_SESSION['db_config'] ?? [];

    try {
        // Connect to database
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Store PDO connection in session for other steps
        $_SESSION['pdo_connection'] = serialize($pdo);

        respond(true, "Database connection established");

    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Run all database migrations
 */
function runMigrations() {
    $basePath = dirname(dirname(__DIR__));
    $migrationsPath = $basePath . '/database/migrations';

    if (!is_dir($migrationsPath)) {
        throw new Exception("Migrations directory not found at {$migrationsPath}");
    }

    $dbConfig = $_SESSION['db_config'] ?? [];
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Get all migration files
    $migrations = glob($migrationsPath . '/*.sql');
    sort($migrations);

    $executed = 0;
    $log = "Running migrations:\n";

    foreach ($migrations as $migration) {
        $sql = file_get_contents($migration);

        // Split by semicolons and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^--/', $stmt);
            }
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignore table already exists errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }
        }

        $executed++;
        $filename = basename($migration);
        $log .= "  ✓ {$filename}\n";
    }

    $log .= "\nTotal migrations executed: {$executed}";
    respond(true, $log);
}

/**
 * Create administrator account
 */
function createAdminAccount() {
    $adminConfig = $_SESSION['admin_config'] ?? [];
    $dbConfig = $_SESSION['db_config'] ?? [];

    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Hash password
    $passwordHash = password_hash($adminConfig['password'], PASSWORD_BCRYPT);

    // Create admin user
    $stmt = $pdo->prepare(
        "INSERT INTO users (
            username, email, password_hash, first_name, last_name,
            role, is_active, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, 'admin', 1, NOW(), NOW())"
    );

    $stmt->execute([
        $adminConfig['username'],
        $adminConfig['email'],
        $passwordHash,
        $adminConfig['first_name'],
        $adminConfig['last_name']
    ]);

    $userId = $pdo->lastInsertId();

    // Create admin role permissions if roles table exists
    try {
        $pdo->exec("
            INSERT INTO user_permissions (user_id, permission, granted_at)
            VALUES
                ({$userId}, 'admin.access', NOW()),
                ({$userId}, 'pos.access', NOW()),
                ({$userId}, 'inventory.manage', NOW()),
                ({$userId}, 'courses.manage', NOW()),
                ({$userId}, 'customers.manage', NOW()),
                ({$userId}, 'reports.access', NOW()),
                ({$userId}, 'settings.manage', NOW())
        ");
    } catch (PDOException $e) {
        // Permissions table might not exist yet, skip
    }

    $log = "Administrator account created:\n";
    $log .= "  Username: {$adminConfig['username']}\n";
    $log .= "  Email: {$adminConfig['email']}\n";
    $log .= "  Name: {$adminConfig['first_name']} {$adminConfig['last_name']}";

    respond(true, $log);
}

/**
 * Setup required directories and permissions
 */
function setupDirectories() {
    $basePath = dirname(dirname(__DIR__));

    $directories = [
        '/storage',
        '/storage/logs',
        '/storage/cache',
        '/storage/sessions',
        '/storage/backups',
        '/storage/uploads',
        '/public/uploads',
        '/public/uploads/products',
        '/public/uploads/customers',
        '/public/uploads/courses'
    ];

    $log = "Setting up directories:\n";

    foreach ($directories as $dir) {
        $path = $basePath . $dir;

        if (!is_dir($path)) {
            if (mkdir($path, 0755, true)) {
                $log .= "  ✓ Created {$dir}\n";
            } else {
                throw new Exception("Failed to create directory: {$dir}");
            }
        } else {
            $log .= "  ✓ {$dir} exists\n";
        }

        // Ensure writable
        if (!is_writable($path)) {
            chmod($path, 0755);
        }
    }

    respond(true, $log);
}

/**
 * Load sample data (optional)
 */
function loadSampleData() {
    $dbConfig = $_SESSION['db_config'] ?? [];
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $log = "Loading sample data:\n";

    // Create sample product categories
    $pdo->exec("
        INSERT IGNORE INTO product_categories (id, name, description, parent_id, is_active, created_at)
        VALUES
            (1, 'Diving Equipment', 'Scuba diving equipment and gear', NULL, 1, NOW()),
            (2, 'Snorkeling', 'Snorkeling equipment', NULL, 1, NOW()),
            (3, 'Apparel', 'Diving apparel and accessories', NULL, 1, NOW()),
            (4, 'Training Materials', 'Course materials and books', NULL, 1, NOW())
    ");
    $log .= "  ✓ Created product categories\n";

    // Create sample payment methods
    $pdo->exec("
        INSERT IGNORE INTO payment_methods (id, name, type, is_active, created_at)
        VALUES
            (1, 'Cash', 'cash', 1, NOW()),
            (2, 'Credit Card', 'credit_card', 1, NOW()),
            (3, 'Debit Card', 'debit_card', 1, NOW())
    ");
    $log .= "  ✓ Created payment methods\n";

    // Create sample course categories
    try {
        $pdo->exec("
            INSERT IGNORE INTO course_categories (id, name, description, is_active, created_at)
            VALUES
                (1, 'Beginner Courses', 'Entry level diving courses', 1, NOW()),
                (2, 'Advanced Courses', 'Advanced diving certifications', 1, NOW()),
                (3, 'Specialty Courses', 'Specialty diving skills', 1, NOW())
        ");
        $log .= "  ✓ Created course categories\n";
    } catch (PDOException $e) {
        // Table might not exist
    }

    // Initialize notification settings
    try {
        $pdo->exec("
            INSERT IGNORE INTO notification_settings (setting_key, setting_value, is_enabled, created_at)
            VALUES
                ('low_stock_threshold', '5', 1, NOW()),
                ('low_stock_enabled', 'true', 1, NOW()),
                ('maintenance_enabled', 'true', 1, NOW()),
                ('course_confirmation_enabled', 'true', 1, NOW()),
                ('transaction_receipt_enabled', 'true', 1, NOW())
        ");
        $log .= "  ✓ Initialized notification settings\n";
    } catch (PDOException $e) {
        // Table might not exist
    }

    $log .= "\nSample data loaded successfully";
    respond(true, $log);
}

/**
 * Finalize installation
 */
function finalizeInstallation() {
    $basePath = dirname(dirname(__DIR__));

    // Create .installed file to prevent re-installation
    $installed = [
        'installed_at' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'app_name' => $_SESSION['app_config']['app_name'] ?? 'Nautilus'
    ];

    file_put_contents(
        $basePath . '/.installed',
        json_encode($installed, JSON_PRETTY_PRINT)
    );

    // Clear installation session data
    $_SESSION['install_step'] = 6;

    $log = "Installation finalized:\n";
    $log .= "  ✓ Installation lock file created\n";
    $log .= "  ✓ Application ready to use\n";
    $log .= "\nIMPORTANT: Delete the /public/install directory for security!";

    respond(true, $log);
}
