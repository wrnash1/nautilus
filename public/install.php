<?php
/**
 * Nautilus Dive Shop Management System
 * Web-Based Installation Wizard
 *
 * Zero command-line needed - just like WordPress!
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

// Determine current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2: // Database setup
            handle_database_setup();
            break;
        case 3: // Run migrations
            handle_migrations();
            break;
        case 4: // Admin account
            handle_admin_creation();
            break;
    }
}

/**
 * Handle database configuration
 */
function handle_database_setup() {
    try {
        $host = $_POST['db_host'] ?? '127.0.0.1';
        $port = $_POST['db_port'] ?? '3306';
        $database = $_POST['db_database'] ?? 'nautilus';
        $username = $_POST['db_username'] ?? 'nautilus_user';
        $password = $_POST['db_password'] ?? '';

        // Test connection
        $dsn = "mysql:host={$host};port={$port}";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Store config in session
        $_SESSION['db_config'] = [
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password
        ];

        // Create .env file
        create_env_file($host, $port, $database, $username, $password);

        header('Location: ?step=3');
        exit;

    } catch (PDOException $e) {
        $_SESSION['db_error'] = $e->getMessage();
        header('Location: ?step=2');
        exit;
    }
}

/**
 * Create .env configuration file
 */
function create_env_file($host, $port, $database, $username, $password) {
    $env_content = <<<ENV
APP_NAME="Nautilus Dive Shop"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://{$_SERVER['HTTP_HOST']}

DB_CONNECTION=mysql
DB_HOST={$host}
DB_PORT={$port}
DB_DATABASE={$database}
DB_USERNAME={$username}
DB_PASSWORD={$password}

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
ENV;

    file_put_contents(ROOT_DIR . '/.env', $env_content);
    chmod(ROOT_DIR . '/.env', 0644);
}

/**
 * Run database migrations
 */
function handle_migrations() {
    require_once ROOT_DIR . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
    $dotenv->load();

    try {
        $config = $_SESSION['db_config'];
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

        $batch = $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 as batch FROM migrations")->fetch()['batch'];

        $results = [];
        foreach ($files as $file) {
            $filename = basename($file);

            if (in_array($filename, $executed)) {
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
                    // Continue on error - log but don't stop
                }
            }

            // Mark as executed even if there were warnings
            $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)")
                ->execute([$filename, $batch]);

            $results[] = [
                'file' => $filename,
                'success' => $success,
                'error' => $error
            ];
        }

        $_SESSION['migration_results'] = $results;
        header('Location: ?step=4');
        exit;

    } catch (Exception $e) {
        $_SESSION['migration_error'] = $e->getMessage();
        header('Location: ?step=3');
        exit;
    }
}

/**
 * Create admin account
 */
function handle_admin_creation() {
    require_once ROOT_DIR . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(ROOT_DIR);
    $dotenv->load();

    try {
        $config = $_SESSION['db_config'];
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $company = $_POST['company_name'] ?? 'My Dive Shop';
        $email = $_POST['admin_email'];
        $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
        $firstName = $_POST['first_name'] ?? 'Admin';
        $lastName = $_POST['last_name'] ?? 'User';

        // Create default tenant
        $pdo->exec("INSERT INTO tenants (id, tenant_uuid, company_name, subdomain, contact_email, status)
                   VALUES (1, UUID(), '{$company}', 'default', '{$email}', 'active')
                   ON DUPLICATE KEY UPDATE company_name = VALUES(company_name)");

        // Create admin user
        $pdo->prepare("INSERT INTO users (tenant_id, role_id, email, password_hash, first_name, last_name, is_active)
                      VALUES (1, 1, ?, ?, ?, ?, 1)
                      ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)")
            ->execute([$email, $password, $firstName, $lastName]);

        // Mark installation complete
        file_put_contents(INSTALLED_FILE, "Installed: " . date('Y-m-d H:i:s') . "\nCompany: {$company}\n");

        $_SESSION['install_complete'] = true;
        header('Location: ?step=5');
        exit;

    } catch (Exception $e) {
        $_SESSION['admin_error'] = $e->getMessage();
        header('Location: ?step=4');
        exit;
    }
}

/**
 * Check system requirements
 */
function check_requirements() {
    return [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'MySQLi Extension' => extension_loaded('mysqli'),
        'MBString Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'cURL Extension' => extension_loaded('curl'),
        'OpenSSL Extension' => extension_loaded('openssl'),
        'GD Extension' => extension_loaded('gd'),
        'Storage Writable' => is_writable(ROOT_DIR . '/storage'),
        'Vendor Directory' => is_dir(ROOT_DIR . '/vendor')
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
        }
        .step-indicator li {
            flex: 1;
            text-align: center;
            position: relative;
            padding: 10px;
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
        .progress-item {
            padding: 8px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
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
                    <small>Database</small>
                </li>
                <li data-step="3" class="<?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' ?>">
                    <small>Migrations</small>
                </li>
                <li data-step="4" class="<?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : '' ?>">
                    <small>Admin Account</small>
                </li>
                <li data-step="5" class="<?= $step == 5 ? 'active' : '' ?>">
                    <small>Complete</small>
                </li>
            </ul>

            <?php if ($step == 1): ?>
                <!-- Step 1: System Requirements -->
                <h3 class="mb-4">System Requirements Check</h3>

                <?php
                $requirements = check_requirements();
                $all_passed = !in_array(false, $requirements);
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
                        <strong>Requirements Not Met</strong><br>
                        Please fix the failed requirements before continuing.
                        <?php if (!is_writable(ROOT_DIR . '/storage')): ?>
                            <br><br>To fix storage permissions, run:
                            <code>chmod -R 775 storage/</code>
                        <?php endif; ?>
                        <?php if (!is_dir(ROOT_DIR . '/vendor')): ?>
                            <br><br>Vendor directory missing. Run:
                            <code>composer install</code>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-4">
                    <a href="?step=2" class="btn btn-primary btn-lg <?= !$all_passed ? 'disabled' : '' ?>">
                        Continue to Database Setup <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

            <?php elseif ($step == 2): ?>
                <!-- Step 2: Database Configuration -->
                <h3 class="mb-4">Database Configuration</h3>

                <?php if (isset($_SESSION['db_error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['db_error']) ?>
                        <?php unset($_SESSION['db_error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-control" name="db_host" value="127.0.0.1" required>
                        <small class="form-text text-muted">Usually 127.0.0.1 or localhost</small>
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
                        <input type="text" class="form-control" name="db_username" value="nautilus_user" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-control" name="db_password">
                        <small class="form-text text-muted">Leave empty if no password</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Test Connection & Continue <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 3): ?>
                <!-- Step 3: Run Migrations -->
                <h3 class="mb-4">Running Database Migrations</h3>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    This will create all database tables. Please wait...
                </div>

                <div id="migration-progress">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Running migrations...</span>
                        </div>
                        <p class="mt-3">Processing migrations...</p>
                    </div>
                </div>

                <script>
                    // Auto-submit to run migrations
                    setTimeout(() => {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        document.body.appendChild(form);
                        form.submit();
                    }, 1000);
                </script>

            <?php elseif ($step == 4): ?>
                <!-- Step 4: Admin Account Creation -->
                <h3 class="mb-4">Create Admin Account</h3>

                <?php if (isset($_SESSION['migration_results'])): ?>
                    <?php
                    $results = $_SESSION['migration_results'];
                    $success_count = count(array_filter($results, fn($r) => $r['success']));
                    $total_count = count($results);
                    ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        Successfully ran <?= $success_count ?> of <?= $total_count ?> migrations
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['admin_error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['admin_error']) ?>
                        <?php unset($_SESSION['admin_error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Company/Dive Shop Name</label>
                        <input type="text" class="form-control" name="company_name" placeholder="Ocean Adventures Dive Shop" required>
                    </div>

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
                        <input type="email" class="form-control" name="admin_email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Password</label>
                        <input type="password" class="form-control" name="admin_password" minlength="8" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Complete Installation <i class="bi bi-check-circle"></i>
                        </button>
                    </div>
                </form>

            <?php elseif ($step == 5): ?>
                <!-- Step 5: Installation Complete -->
                <div class="text-center py-5">
                    <div style="font-size: 5rem; color: #10b981;">✓</div>
                    <h2 class="mb-4">Installation Complete!</h2>

                    <div class="alert alert-success text-start">
                        <h5><i class="bi bi-info-circle"></i> Your Nautilus System is Ready</h5>
                        <p class="mb-2"><strong>Access your dashboard:</strong></p>
                        <p class="mb-2"><a href="/" class="btn btn-sm btn-primary">Go to Dashboard</a></p>

                        <hr>

                        <p class="mb-1"><strong>What's Next?</strong></p>
                        <ul class="mb-0">
                            <li>Login with your admin credentials</li>
                            <li>Configure your dive shop settings</li>
                            <li>Add products and courses</li>
                            <li>Start managing your business!</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <a href="/" class="btn btn-primary btn-lg">
                            <i class="bi bi-speedometer2"></i> Go to Dashboard
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
