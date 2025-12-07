<?php
/**
 * Nautilus Dive Shop Management System
 * Improved Web-Based Installation Wizard
 *
 * Zero command-line needed - just like WordPress!
 * Now with proper application name usage throughout
 */

// Prevent execution if already installed
define('ROOT_DIR', dirname(__DIR__));
define('INSTALLED_FILE', ROOT_DIR . '/.installed');

if (file_exists(INSTALLED_FILE)) {
    header('Location: /');
    exit;
}

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Auto-fix permissions on first load or when requested
if (!isset($_SESSION['permissions_fixed']) || isset($_GET['autofix'])) {
    auto_fix_permissions();
    $_SESSION['permissions_fixed'] = true;

    // If manually triggered, redirect to remove the URL parameter
    if (isset($_GET['autofix'])) {
        header('Location: ?step=1');
        exit;
    }
}

// Determine current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2: // Application settings
            handle_app_settings();
            break;
        case 3: // Database setup
            handle_database_setup();
            break;
        case 4: // Run migrations
            handle_migrations();
            break;
        case 5: // Admin account
            handle_admin_creation();
            break;
    }
}

/**
 * Automatically fix common permission issues
 * Works on Linux, macOS, and Windows (with appropriate web server setup)
 */
function auto_fix_permissions() {
    $fixed = [];
    $web_user = get_web_server_user();

    // Try to fix storage directory permissions
    if (!is_writable(ROOT_DIR . '/storage')) {
        @chmod(ROOT_DIR . '/storage', 0775);
        @chmod(ROOT_DIR . '/storage/logs', 0775);
        @chmod(ROOT_DIR . '/storage/cache', 0775);
        @chmod(ROOT_DIR . '/storage/backups', 0775);

        if (is_writable(ROOT_DIR . '/storage')) {
            $fixed[] = 'storage';
        }
    }

    // Try to make root directory writable for .env creation
    if (!is_writable(ROOT_DIR) && !file_exists(ROOT_DIR . '/.env')) {
        @chmod(ROOT_DIR, 0775);

        if (is_writable(ROOT_DIR)) {
            $fixed[] = 'root';
        }
    }

    // Store what we fixed
    $_SESSION['auto_fixed'] = $fixed;

    return $fixed;
}

/**
 * Detect web server user (for display purposes)
 */
function get_web_server_user() {
    if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
        $processUser = posix_getpwuid(posix_geteuid());
        return $processUser['name'] ?? 'www-data';
    }

    // Common defaults by OS
    if (stripos(PHP_OS, 'WIN') === 0) {
        return 'IUSR';
    } elseif (file_exists('/etc/fedora-release') || file_exists('/etc/redhat-release')) {
        return 'apache';
    } else {
        return 'www-data';
    }
}

/**
 * Get OS-specific fix commands
 */
function get_fix_commands() {
    $web_user = get_web_server_user();
    $is_fedora = file_exists('/etc/fedora-release') || file_exists('/etc/redhat-release');
    $restart_cmd = $is_fedora ? 'sudo systemctl restart httpd' : 'sudo systemctl restart apache2';

    return [
        'storage' => "sudo chmod -R 775 " . ROOT_DIR . "/storage && sudo chown -R {$web_user}:{$web_user} " . ROOT_DIR . "/storage",
        'root' => "sudo chmod 775 " . ROOT_DIR . " && sudo chown {$web_user}:{$web_user} " . ROOT_DIR,
        'restart' => $restart_cmd
    ];
}

/**
 * Handle application settings (NEW STEP)
 */
function handle_app_settings() {
    $_SESSION['app_config'] = [
        'app_name' => $_POST['app_name'] ?? 'Nautilus Dive Shop',
        'company_name' => $_POST['company_name'] ?? 'My Dive Shop',
        'timezone' => $_POST['timezone'] ?? 'America/New_York'
    ];

    header('Location: ?step=3');
    exit;
}

/**
 * Handle database configuration
 */
function handle_database_setup() {
    try {
        $host = $_POST['db_host'] ?? '127.0.0.1';
        $port = $_POST['db_port'] ?? '3306';
        $database = $_POST['db_database'] ?? 'nautilus';
        $username = $_POST['db_username'] ?? 'root';
        $password = $_POST['db_password'] ?? '';

        // Test connection
        $dsn = "mysql:host={$host};port={$port}";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); // Fix for MariaDB unbuffered query error

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Test connection to new database
        $dsn_with_db = "mysql:host={$host};port={$port};dbname={$database}";
        $pdo_test = new PDO($dsn_with_db, $username, $password);
        $pdo_test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_test->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); // Fix for MariaDB unbuffered query error

        // Store config in session
        $_SESSION['db_config'] = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password
        ];

        // Create .env file
        create_env_file();

        header('Location: ?step=4');
        exit;

    } catch (PDOException $e) {
        $_SESSION['db_error'] = $e->getMessage();
        header('Location: ?step=3');
        exit;
    }
}

/**
 * Create .env configuration file with ALL settings
 */
function create_env_file() {
    $app_config = $_SESSION['app_config'];
    $db_config = $_SESSION['db_config'];

    // Detect if we're running on HTTPS
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $app_url = $protocol . '://' . $_SERVER['HTTP_HOST'];

    $env_content = <<<ENV
# Application Settings
APP_NAME="{$app_config['app_name']}"
APP_ENV=production
APP_DEBUG=false
APP_URL={$app_url}
APP_TIMEZONE={$app_config['timezone']}

# Database Configuration
DB_CONNECTION=mysql
DB_HOST={$db_config['host']}
DB_PORT={$db_config['port']}
DB_DATABASE={$db_config['database']}
DB_USERNAME={$db_config['username']}
DB_PASSWORD={$db_config['password']}

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync

# Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Mail Configuration (can be configured later)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="{$app_config['app_name']}"
ENV;

    file_put_contents(ROOT_DIR . '/.env', $env_content);
    @chmod(ROOT_DIR . '/.env', 0600); // More secure permissions (@ suppresses errors in containers)
}

/**
 * Run database migrations
 */
function handle_migrations() {
    // Increase PHP limits for migration processing
    set_time_limit(600); // 10 minutes
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', '600');

    require_once ROOT_DIR . '/vendor/autoload.php';

    try {
        // Load .env file
        if (file_exists(ROOT_DIR . '/.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
            $dotenv->load();
        }

        $config = $_SESSION['db_config'];
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); // Fix for MariaDB unbuffered query error

        // Create migrations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Get executed migrations
        $stmt = $pdo->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get migration files
        $files = glob(ROOT_DIR . '/database/migrations/*.sql');
        sort($files);

        $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as batch FROM migrations");
        $batchResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $batch = $batchResult['batch'];

        $results = [];
        foreach ($files as $file) {
            $filename = basename($file);

            if (in_array($filename, $executed)) {
                $results[] = [
                    'file' => $filename,
                    'success' => true,
                    'error' => null,
                    'skipped' => true
                ];
                continue;
            }

            $sql = file_get_contents($file);

            // Split into statements
            $statements = array_filter(
                array_map('trim', preg_split('/;(?=(?:[^\'"]|[\'"][^\'"]*[\'"])*$)/', $sql)),
                fn($stmt) => !empty($stmt) && !preg_match('/^\s*--/', $stmt)
            );

            $success = true;
            $error = null;

            foreach ($statements as $statement) {
                if (empty(trim($statement))) continue;

                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    $success = false;
                    $error = $e->getMessage();
                    // Log error but continue
                    error_log("Migration $filename failed: " . $e->getMessage());
                }
            }

            // Mark as executed even if there were warnings
            $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)")
                ->execute([$filename, $batch]);

            $results[] = [
                'file' => $filename,
                'success' => $success,
                'error' => $error,
                'skipped' => false
            ];

            // Flush output and keep connection alive
            if (connection_status() == CONNECTION_NORMAL) {
                flush();
            }
        }

        $_SESSION['migration_results'] = $results;
        header('Location: ?step=5');
        exit;

    } catch (Exception $e) {
        $_SESSION['migration_error'] = $e->getMessage();
        header('Location: ?step=4');
        exit;
    }
}

/**
 * Create admin account and setup tenant
 */
function handle_admin_creation() {
    require_once ROOT_DIR . '/vendor/autoload.php';

    try {
        // Load .env
        if (file_exists(ROOT_DIR . '/.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
            $dotenv->load();
        }

        $config = $_SESSION['db_config'];
        $app_config = $_SESSION['app_config'];

        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); // Fix for MariaDB unbuffered query error

        $company = $app_config['company_name'];
        $email = $_POST['admin_email'];
        $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];

        // Create default tenant with company name from settings
        // Note: tenants table uses 'name' not 'company_name' (from 000_CORE_SCHEMA.sql)
        $stmt = $pdo->prepare("INSERT INTO tenants (id, name, subdomain, status)
                   VALUES (1, ?, 'default', 'active')
                   ON DUPLICATE KEY UPDATE name = VALUES(name)");
        $stmt->execute([$company]);

        // Create admin user (users table doesn't have role_id - uses user_roles junction table)
        $stmt = $pdo->prepare("INSERT INTO users (tenant_id, email, password_hash, first_name, last_name, is_active)
                      VALUES (1, ?, ?, ?, ?, 1)
                      ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), first_name = VALUES(first_name), last_name = VALUES(last_name)");
        $stmt->execute([$email, $password, $firstName, $lastName]);

        // Get the user ID (either just inserted or existing)
        $userId = $pdo->lastInsertId();
        if (!$userId) {
            // If no insert happened (duplicate key), get existing user ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $result['id'] ?? null;
        }

        // Assign Super Admin role (role_id = 1) via user_roles junction table
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 1)
                      ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)")
            ->execute([$userId]);

        // Mark installation complete
        file_put_contents(INSTALLED_FILE, "Installed: " . date('Y-m-d H:i:s') . "\n" .
                                         "Company: {$company}\n" .
                                         "App Name: {$app_config['app_name']}\n" .
                                         "Admin Email: {$email}\n");

        $_SESSION['install_complete'] = true;
        header('Location: ?step=6');
        exit;

    } catch (Exception $e) {
        $_SESSION['admin_error'] = $e->getMessage();
        header('Location: ?step=5');
        exit;
    }
}

/**
 * Check system requirements
 */
function check_requirements() {
    // Check if virtual host DocumentRoot is properly configured
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $currentDir = __DIR__; // This is /path/to/nautilus/public
    $expectedPublicDir = realpath($currentDir);
    $actualDocRoot = realpath($documentRoot);

    // Virtual host is correctly configured if DocumentRoot points to /public folder
    $vhostConfigured = ($actualDocRoot === $expectedPublicDir);

    $checks = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'MySQLi Extension' => extension_loaded('mysqli'),
        'MBString Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'cURL Extension' => extension_loaded('curl'),
        'OpenSSL Extension' => extension_loaded('openssl'),
        'GD Extension' => extension_loaded('gd'),
        'ZIP Extension' => extension_loaded('zip'),
        'XML Extension' => extension_loaded('xml'),
        'Storage Writable' => is_writable(ROOT_DIR . '/storage'),
        'Vendor Directory Exists' => is_dir(ROOT_DIR . '/vendor'),
        '.env File Writable' => is_writable(ROOT_DIR) || is_writable(ROOT_DIR . '/.env'),
        'Virtual Host Configured (DocumentRoot = /public)' => $vhostConfigured
    ];

    return $checks;
}

/**
 * Get list of common timezones
 */
function get_timezones() {
    return [
        'America/New_York' => 'Eastern Time (US & Canada)',
        'America/Chicago' => 'Central Time (US & Canada)',
        'America/Denver' => 'Mountain Time (US & Canada)',
        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
        'America/Phoenix' => 'Arizona',
        'America/Anchorage' => 'Alaska',
        'Pacific/Honolulu' => 'Hawaii',
        'Europe/London' => 'London',
        'Europe/Paris' => 'Paris',
        'Asia/Tokyo' => 'Tokyo',
        'Australia/Sydney' => 'Sydney',
        'UTC' => 'UTC'
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .install-card {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .install-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }
        .install-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0;
            list-style: none;
            flex-wrap: wrap;
        }
        .step-indicator li {
            flex: 1;
            min-width: 80px;
            text-align: center;
            position: relative;
            padding: 10px 5px;
        }
        .step-indicator li:before {
            content: attr(data-step);
            display: block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step-indicator li.active:before {
            background: #667eea;
            color: white;
        }
        .step-indicator li.completed:before {
            background: #10b981;
            color: white;
            content: "✓";
        }
        .requirement-check {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .requirement-check:last-child {
            border-bottom: none;
        }
        .badge-success {
            background-color: #10b981;
        }
        .badge-danger {
            background-color: #ef4444;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .copy-command {
            position: relative;
            margin: 10px 0;
        }
        .copy-command code {
            display: block;
            padding: 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin: 5px 0;
            font-family: 'Courier New', monospace;
            position: relative;
            padding-right: 100px;
        }
        .copy-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            padding: 4px 12px;
            font-size: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background: #5568d3;
        }
        .copy-btn.copied {
            background: #10b981;
        }
        .copy-btn.copied:after {
            content: ' ✓';
        }
    </style>
    <script>
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                button.textContent = 'Copied!';
                button.classList.add('copied');
                setTimeout(function() {
                    button.textContent = 'Copy';
                    button.classList.remove('copied');
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
                button.textContent = 'Failed';
                setTimeout(function() {
                    button.textContent = 'Copy';
                }, 2000);
            });
        }
    </script>
</head>
<body>
    <div class="install-card">
        <div class="install-header">
            <div style="font-size: 4rem;">🌊</div>
            <h1>Nautilus Installation</h1>
            <p class="mb-0">Dive Shop Management System</p>
        </div>

        <div class="install-body">
            <ul class="step-indicator">
                <li data-step="1" class="<?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">
                    <small>Requirements</small>
                </li>
                <li data-step="2" class="<?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' ?>">
                    <small>Settings</small>
                </li>
                <li data-step="3" class="<?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">
                    <small>Database</small>
                </li>
                <li data-step="4" class="<?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : '' ?>">
                    <small>Migrations</small>
                </li>
                <li data-step="5" class="<?= $step >= 5 ? ($step > 5 ? 'completed' : 'active') : '' ?>">
                    <small>Admin</small>
                </li>
                <li data-step="6" class="<?= $step == 6 ? 'active' : '' ?>">
                    <small>Complete</small>
                </li>
            </ul>

            <?php if ($step == 1): ?>
                <!-- Step 1: System Requirements -->
                <h3 class="mb-4">System Requirements Check</h3>

                <?php
                $requirements = check_requirements();
                $all_passed = !in_array(false, $requirements);

                // Show auto-fix success message
                if (isset($_SESSION['auto_fixed']) && !empty($_SESSION['auto_fixed'])) {
                    echo '<div class="alert alert-success mb-3">';
                    echo '<strong>🎉 Auto-Fixed!</strong><br>';
                    echo 'Automatically repaired: ' . implode(', ', $_SESSION['auto_fixed']);
                    echo '</div>';
                }
                ?>

                <?php foreach ($requirements as $name => $passed): ?>
                    <div class="requirement-check">
                        <span><?= $name ?></span>
                        <span class="badge <?= $passed ? 'badge-success' : 'badge-danger' ?>">
                            <?= $passed ? '✓ Pass' : '✗ Fail' ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php if (!$all_passed): ?>
                    <div class="alert alert-danger mt-4">
                        <strong>⚠️ Setup Needed</strong>
                        <p class="mt-2 mb-3">Some files need permission adjustments to continue.</p>

                        <?php
                        $permission_issues = (!is_writable(ROOT_DIR . '/storage')) ||
                                           (!is_writable(ROOT_DIR) && !is_writable(ROOT_DIR . '/.env'));

                        if ($permission_issues): ?>
                            <div class="alert alert-info mt-2 mb-3">
                                <strong>🔧 Easy Fix Option 1: One-Click Repair</strong><br>
                                Click the button below and the installer will try to fix permissions automatically:
                                <div class="d-grid gap-2 mt-3">
                                    <button onclick="location.href='?step=1&autofix=1'" class="btn btn-warning btn-lg">
                                        ✨ Try Auto-Fix Permissions
                                    </button>
                                </div>
                                <small class="text-muted mt-2 d-block">This works in most hosting environments</small>
                            </div>

                            <div class="alert alert-warning mt-2 mb-2">
                                <strong>🛠️ Alternative: Run This Command</strong><br>
                                If auto-fix doesn't work, ask your hosting provider or system admin to run:
                                <div class="copy-command">
                                    <?php
                                    $web_user = get_web_server_user();
                                    $fix_cmd = "cd " . ROOT_DIR . " && chmod -R 775 storage && chmod 775 . && chown -R {$web_user}:{$web_user} .";
                                    ?>
                                    <code><?= $fix_cmd ?><button class="copy-btn" onclick="copyToClipboard('<?= addslashes($fix_cmd) ?>', this)">Copy</button></code>
                                </div>
                                <small class="text-muted">They can paste this into their terminal</small>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Collect missing PHP extensions
                        $missing_extensions = [];
                        if (!extension_loaded('pdo_mysql')) $missing_extensions[] = 'pdo_mysql';
                        if (!extension_loaded('gd')) $missing_extensions[] = 'gd';
                        if (!extension_loaded('zip')) $missing_extensions[] = 'zip';

                        if (!empty($missing_extensions) || !is_dir(ROOT_DIR . '/vendor')):
                        ?>
                            <div class="alert alert-warning mt-2 mb-2">
                                <strong>📦 Server Components Needed</strong><br>
                                Your hosting provider or system administrator needs to install some PHP components.
                                <br><br>
                                <strong>📋 Send them this information:</strong>
                                <div class="alert alert-light mt-2 mb-2">
                                    <p class="mb-2"><strong>Missing Components:</strong></p>
                                    <ul class="mb-2">
                                        <?php if (!is_dir(ROOT_DIR . '/vendor')): ?>
                                            <li>PHP Composer dependencies (run: <code>composer install --no-dev</code>)</li>
                                        <?php endif; ?>
                                        <?php foreach ($missing_extensions as $ext): ?>
                                            <li>PHP Extension: <?= $ext ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="mb-1"><strong>Installation Command for Them:</strong></p>
                                    <?php
                                    $is_fedora = file_exists('/etc/fedora-release') || file_exists('/etc/redhat-release');
                                    if ($is_fedora) {
                                        $install_cmd = "sudo dnf install php-mysqlnd php-gd php-pecl-zip && sudo systemctl restart httpd";
                                    } else {
                                        $install_cmd = "sudo apt install php-mysql php-gd php-zip && sudo systemctl restart apache2";
                                    }
                                    ?>
                                    <div class="copy-command">
                                        <code><?= $install_cmd ?><button class="copy-btn" onclick="copyToClipboard('<?= addslashes($install_cmd) ?>', this)">Copy</button></code>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    💡 <strong>Using Shared Hosting?</strong> Contact your hosting support and tell them you need these PHP extensions enabled.
                                    Most hosting providers can enable these through their control panel (cPanel, Plesk, etc.)
                                </small>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <button onclick="location.reload()" class="btn btn-sm btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Re-check Requirements
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-4">
                    <a href="?step=2" class="btn btn-primary btn-lg <?= !$all_passed ? 'disabled' : '' ?>">
                        Continue to Application Settings <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: Application Settings -->
                <h3 class="mb-4">Application Settings</h3>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Configure your application name and basic settings. These will be used throughout the system.
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Application Name</label>
                        <input type="text" class="form-control" name="app_name"
                               value="Nautilus Dive Shop" required>
                        <small class="form-text text-muted">
                            This appears in page titles, emails, and system notifications
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Company/Dive Shop Name</label>
                        <input type="text" class="form-control" name="company_name"
                               placeholder="Ocean Adventures Dive Shop" required>
                        <small class="form-text text-muted">
                            Your business name for invoices, certificates, and customer communications
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Timezone</label>
                        <select class="form-control" name="timezone" required>
                            <?php foreach (get_timezones() as $tz => $label): ?>
                                <option value="<?= $tz ?>" <?= $tz === 'America/New_York' ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Used for scheduling, reports, and timestamps
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Continue to Database <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 3): ?>
                <!-- Step 3: Database Configuration -->
                <h3 class="mb-4">Database Configuration</h3>

                <?php if (isset($_SESSION['db_error'])): ?>
                    <div class="alert alert-danger">
                        <strong>Connection Failed:</strong><br>
                        <?= htmlspecialchars($_SESSION['db_error']) ?>
                        <?php unset($_SESSION['db_error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-control" name="db_host" value="<?= gethostbyname('database') !== 'database' ? 'database' : '127.0.0.1' ?>" required>
                        <small class="form-text text-muted">Use 'database' for containers, or 127.0.0.1/localhost for regular hosting</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Port</label>
                        <input type="text" class="form-control" name="db_port" value="3306" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="db_database" value="nautilus" required>
                        <small class="form-text text-muted">Will be created if it doesn't exist</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Username</label>
                        <input type="text" class="form-control" name="db_username" value="root" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-control" name="db_password">
                        <small class="form-text text-muted">Leave empty if no password set</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Test Connection & Continue <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 4): ?>
                <!-- Step 4: Run Migrations -->
                <h3 class="mb-4">Creating Database Tables</h3>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Setting up your database with all required tables. This will take approximately 3-4 minutes...
                </div>

                <!-- Warning for final migrations (shown when reaching migration 100) -->
                <div id="final-migrations-warning" class="alert alert-warning" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Almost done!</strong> The final migrations (101-107) modify existing database tables and may take 2-3 minutes each.
                    <strong>Please be patient - the installer is still working!</strong>
                </div>

                <div id="migration-progress">
                    <!-- Overall Progress Bar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Overall Progress:</label>
                        <div class="progress" style="height: 30px;">
                            <div id="overall-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="overall-progress-text">Preparing...</span>
                            </div>
                        </div>
                        <small class="text-muted" id="overall-status">Initializing database setup...</small>
                    </div>

                    <!-- Current Migration Progress Bar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Migration:</label>
                        <div class="progress" style="height: 25px;">
                            <div id="current-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                                 role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="current-progress-text">Waiting...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Messages -->
                    <div id="migration-status" class="mb-3" style="max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                        <div class="text-muted">Initializing database setup...</div>
                    </div>

                    <!-- Spinner (hidden after progress starts) -->
                    <div id="initial-spinner" class="text-center py-2">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                            <span class="visually-hidden">Starting...</span>
                        </div>
                    </div>
                </div>

                <script>
                    let migrationStartTime = Date.now();

                    // Simulate migration progress with dual progress bars
                    function simulateMigrationProgress() {
                        const totalMigrations = 107;

                        // Different timing for different migration ranges
                        // Migrations 1-89: fast (800ms each)
                        // Migrations 90-100: medium (2 seconds each)
                        // Migrations 101-107: slow (20 seconds each) - these are the large ALTER TABLE operations
                        const timings = {
                            fast: 800,      // Migrations 1-89
                            medium: 2000,   // Migrations 90-100
                            slow: 20000     // Migrations 101-107 (the problematic ones!)
                        };

                        // Calculate total estimated time
                        const totalTimeMs = (89 * timings.fast) + (11 * timings.medium) + (7 * timings.slow);
                        const intervalMs = 500; // Update UI every 500ms

                        let currentMigration = 0;
                        let currentMigrationProgress = 0;
                        let progressInterval;

                        // Hide spinner after 1 second
                        setTimeout(() => {
                            document.getElementById('initial-spinner').style.display = 'none';
                        }, 1000);

                        progressInterval = setInterval(() => {
                            const elapsed = Date.now() - migrationStartTime;

                            // Calculate which migration we should be on based on elapsed time
                            let timeAccumulated = 0;
                            let calculatedMigration = 0;

                            for (let i = 1; i <= totalMigrations; i++) {
                                let migrationTime;
                                if (i <= 89) {
                                    migrationTime = timings.fast;
                                } else if (i <= 100) {
                                    migrationTime = timings.medium;
                                } else {
                                    migrationTime = timings.slow;
                                }

                                if (timeAccumulated + migrationTime <= elapsed) {
                                    timeAccumulated += migrationTime;
                                    calculatedMigration = i;
                                } else {
                                    // We're in the middle of this migration
                                    currentMigrationProgress = ((elapsed - timeAccumulated) / migrationTime) * 100;
                                    break;
                                }
                            }

                            currentMigration = calculatedMigration;

                            // Calculate overall progress
                            const overallPercent = Math.min(98, (currentMigration / totalMigrations) * 100);

                            // Update overall progress bar
                            const overallBar = document.getElementById('overall-progress-bar');
                            overallBar.style.width = overallPercent + '%';
                            overallBar.setAttribute('aria-valuenow', overallPercent);
                            document.getElementById('overall-progress-text').textContent =
                                `Migration ${currentMigration} of ${totalMigrations} (${Math.floor(overallPercent)}%)`;

                            // Update overall status
                            document.getElementById('overall-status').textContent =
                                `Processing migration ${currentMigration} of ${totalMigrations}...`;

                            // Update current migration progress bar
                            const currentBar = document.getElementById('current-progress-bar');
                            currentBar.style.width = currentMigrationProgress + '%';
                            currentBar.setAttribute('aria-valuenow', currentMigrationProgress);

                            let currentMigrationName = `migration_${String(currentMigration).padStart(3, '0')}.sql`;
                            if (currentMigration > 100) {
                                currentMigrationName += ' (Large - modifying tables)';
                            }

                            document.getElementById('current-progress-text').textContent =
                                `${currentMigrationName} - ${Math.floor(currentMigrationProgress)}%`;

                            // Show warning when reaching migration 100
                            if (currentMigration >= 100 && document.getElementById('final-migrations-warning').style.display === 'none') {
                                document.getElementById('final-migrations-warning').style.display = 'block';

                                const statusDiv = document.getElementById('migration-status');
                                const warningMessage = document.createElement('div');
                                warningMessage.className = 'text-warning fw-bold';
                                warningMessage.innerHTML = `⚠ Starting final migrations (101-107). These modify existing tables and will take longer...`;
                                statusDiv.appendChild(warningMessage);
                                statusDiv.scrollTop = statusDiv.scrollHeight;
                            }

                            // Add status messages at milestones
                            if (currentMigration > 0 && currentMigration % 10 === 0 && currentMigrationProgress < 10) {
                                const statusDiv = document.getElementById('migration-status');
                                const newMessage = document.createElement('div');
                                newMessage.className = 'text-success';
                                newMessage.innerHTML = `✓ Completed ${currentMigration} migrations...`;
                                statusDiv.appendChild(newMessage);
                                statusDiv.scrollTop = statusDiv.scrollHeight;
                            }

                            // Stop at 98% and wait for actual completion
                            if (overallPercent >= 98) {
                                clearInterval(progressInterval);
                                document.getElementById('overall-progress-text').textContent =
                                    'Finalizing database setup... Almost done!';
                                document.getElementById('current-progress-text').textContent =
                                    'Completing final steps...';
                            }
                        }, intervalMs);
                    }

                    // Start progress simulation
                    simulateMigrationProgress();

                    // Auto-submit to run migrations in a hidden iframe
                    // This allows the progress bars to continue animating while migrations run
                    const iframe = document.createElement('iframe');
                    iframe.name = 'migration-frame';
                    iframe.style.display = 'none';
                    document.body.appendChild(iframe);

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.target = 'migration-frame';
                    document.body.appendChild(form);
                    form.submit();

                    // Poll to detect when migrations complete
                    // When migrations finish, the session will change and we redirect to step 5
                    const checkCompletionInterval = setInterval(() => {
                        fetch(window.location.href)
                            .then(response => response.text())
                            .then(html => {
                                // Check if we've been redirected to step 5
                                if (html.includes('Create Administrator Account') ||
                                    html.includes('step=5') ||
                                    html.includes('Admin Account')) {
                                    clearInterval(checkCompletionInterval);
                                    window.location.href = '?step=5';
                                }
                            })
                            .catch(error => {
                                console.error('Error checking completion:', error);
                            });
                    }, 3000); // Check every 3 seconds

                    // Safety timeout - redirect after 6 minutes no matter what
                    setTimeout(() => {
                        clearInterval(checkCompletionInterval);
                        window.location.href = '?step=5';
                    }, 360000); // 6 minutes
                </script>

            <?php elseif ($step == 5): ?>
                <!-- Step 5: Admin Account Creation -->
                <h3 class="mb-4">Create Administrator Account</h3>

                <?php if (isset($_SESSION['migration_results'])): ?>
                    <?php
                    $results = $_SESSION['migration_results'];
                    $success_count = count(array_filter($results, fn($r) => $r['success']));
                    $total_count = count($results);
                    $skipped_count = count(array_filter($results, fn($r) => $r['skipped'] ?? false));
                    ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        Successfully created <?= $success_count ?> of <?= $total_count ?> tables
                        <?php if ($skipped_count > 0): ?>
                            (<?= $skipped_count ?> already existed)
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['admin_error']) ?>
                        <?php unset($_SESSION['admin_error']); ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle"></i>
                    Company: <strong><?= htmlspecialchars($_SESSION['app_config']['company_name'] ?? 'Not set') ?></strong>
                </div>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" class="form-control" name="admin_email"
                               placeholder="admin@example.com" required>
                        <small class="form-text text-muted">You'll use this to login</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Password</label>
                        <input type="password" class="form-control" name="admin_password"
                               minlength="8" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Complete Installation <i class="bi bi-check-circle"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 6): ?>
                <!-- Step 6: Installation Complete -->
                <div class="text-center py-5">
                    <div style="font-size: 5rem; color: #10b981;">✓</div>
                    <h2 class="mb-4">Installation Complete!</h2>

                    <?php
                    $app_name = $_SESSION['app_config']['app_name'] ?? 'Nautilus';
                    $company_name = $_SESSION['app_config']['company_name'] ?? 'Your Dive Shop';
                    ?>

                    <div class="alert alert-success text-start">
                        <h5><i class="bi bi-info-circle"></i> <?= htmlspecialchars($app_name) ?> is Ready!</h5>
                        <p class="mb-2"><strong>Company:</strong> <?= htmlspecialchars($company_name) ?></p>

                        <hr>

                        <p class="mb-1"><strong>What's Next?</strong></p>
                        <ul class="mb-0">
                            <li>Login with your admin credentials</li>
                            <li>Configure your dive shop settings</li>
                            <li>Add instructors and staff</li>
                            <li>Setup courses and pricing</li>
                            <li>Start managing bookings!</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="/" class="btn btn-primary btn-lg">
                            <i class="bi bi-speedometer2"></i> Go to Dashboard
                        </a>
                    </div>

                    <div class="mt-3">
                        <small class="text-muted">
                            The installer has been disabled. Delete /public/install.php for extra security.
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
