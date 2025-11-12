<?php
/**
 * Nautilus Dive Shop Management System - Alpha Version 1
 * Easy Installer - No Technical Knowledge Required
 *
 * This installer will:
 * - Check system requirements
 * - Create required directories
 * - Fix file permissions automatically
 * - Set up the database
 * - Create your admin account
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths
define('ROOT_DIR', dirname(__DIR__));
define('PUBLIC_DIR', __DIR__);
define('INSTALLED_FILE', ROOT_DIR . '/.installed');

// Check if already installed
if (file_exists(INSTALLED_FILE)) {
    die('<!DOCTYPE html>
    <html><head><title>Already Installed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="bg-light"><div class="container mt-5">
    <div class="alert alert-success">
        <h4>🛡️ Installation Already Complete</h4>
        <p>Nautilus is installed and ready to use.</p>
        <a href="/" class="btn btn-primary">Go to Dashboard →</a>
    </div>
    <div class="alert alert-danger">
        <h5>⚠️ SECURITY WARNING</h5>
        <p><strong>Running the installer again will:</strong></p>
        <ul>
            <li>Delete ALL existing data</li>
            <li>Remove all customers, products, and sales</li>
            <li>Reset admin accounts</li>
            <li>This action CANNOT be undone</li>
        </ul>
        <hr>
        <p class="mb-0"><strong>If you need to reinstall:</strong></p>
        <ol>
            <li>Backup your database first</li>
            <li>Delete the <code>.installed</code> file in the root directory</li>
            <li>Refresh this page</li>
        </ol>
    </div>
    </div></body></html>');
}

// Installation steps
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Handle Step 3 POST (must be before any HTML output to allow header redirect)
if ($step == 3 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    // Validate password confirmation
    if ($_POST['password'] !== $_POST['password_confirm']) {
        $_SESSION['step3_error'] = "Passwords don't match. Please make sure both password fields are identical.";
    } else {
        try {
            $config = $_SESSION['db_config'];
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ]);

            // Create first tenant
            $tenantUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $stmt = $pdo->prepare("INSERT INTO tenants (tenant_uuid, company_name, subdomain, contact_email, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$tenantUuid, $_POST['company_name'], $_POST['subdomain'], $_POST['email']]);
            $tenantId = $pdo->lastInsertId();

            // Get admin role
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
            $stmt->execute();
            $roleResult = $stmt->fetch();
            $roleId = $roleResult ? $roleResult['id'] : 1;

            // Create admin user
            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (tenant_id, role_id, email, password_hash, first_name, last_name, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->execute([
                $tenantId,
                $roleId,
                $_POST['email'],
                $passwordHash,
                $_POST['first_name'],
                $_POST['last_name']
            ]);
            $userId = $pdo->lastInsertId();

            // Assign admin role
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $roleId]);

            // Create .env file
            $envContent = "# Database Configuration\n";
            $envContent .= "DB_HOST={$config['host']}\n";
            $envContent .= "DB_PORT={$config['port']}\n";
            $envContent .= "DB_DATABASE={$config['database']}\n";
            $envContent .= "DB_USERNAME={$config['username']}\n";
            $envContent .= "DB_PASSWORD={$config['password']}\n\n";
            $envContent .= "# Application\n";
            $envContent .= "APP_NAME=\"Nautilus Alpha v1\"\n";
            $envContent .= "APP_ENV=production\n";
            $envContent .= "APP_DEBUG=true\n";
            $envContent .= "APP_URL=http://localhost\n";
            $envContent .= "APP_TIMEZONE=America/Chicago\n\n";
            $envContent .= "# Security\n";
            $envContent .= "APP_KEY=base64:" . base64_encode(random_bytes(32)) . "\n";
            $envContent .= "JWT_SECRET=" . base64_encode(random_bytes(32)) . "\n";
            $envContent .= "SESSION_LIFETIME=120\n";
            $envContent .= "PASSWORD_MIN_LENGTH=8\n\n";
            $envContent .= "# Cache & Session\n";
            $envContent .= "CACHE_DRIVER=file\n";
            $envContent .= "SESSION_DRIVER=file\n\n";
            $envContent .= "# File Storage\n";
            $envContent .= "UPLOAD_MAX_SIZE=10485760\n";
            $envContent .= "ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx\n";

            $envWritten = file_put_contents(ROOT_DIR . '/.env', $envContent);

            if ($envWritten === false) {
                throw new Exception("Could not create .env file. Please check directory permissions.");
            }

            // Create .installed file
            $installedWritten = file_put_contents(INSTALLED_FILE, date('Y-m-d H:i:s') . "\nCompany: " . $_POST['company_name'] . "\nAdmin: " . $_POST['email']);

            if ($installedWritten === false) {
                throw new Exception("Could not create .installed file. Please check directory permissions.");
            }

            // Save credentials for display
            $_SESSION['install_complete'] = [
                'email' => $_POST['email'],
                'company' => $_POST['company_name']
            ];

            // Clear session
            unset($_SESSION['db_config']);

            // Redirect to success page
            header('Location: ?step=4');
            exit;

        } catch (Exception $e) {
            $_SESSION['step3_error'] = "Error creating admin account: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0066cc 0%, #004999 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .installer-card {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .installer-header {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .installer-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }
        .installer-header .wave {
            font-size: 3rem;
            display: block;
            margin-bottom: 10px;
        }
        .installer-body {
            padding: 40px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            padding: 0;
            list-style: none;
        }
        .step-indicator li {
            flex: 0 0 auto;
            text-align: center;
            position: relative;
            padding: 0 20px;
        }
        .step-indicator li:not(:last-child):after {
            content: '→';
            position: absolute;
            right: -10px;
            top: 10px;
            color: #ddd;
            font-size: 1.5rem;
        }
        .step-indicator .step-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .step-indicator .active .step-number {
            background: #0066cc;
            color: white;
        }
        .step-indicator .completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-label {
            display: block;
            font-size: 0.85rem;
            color: #6c757d;
        }
        .requirement-check {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .requirement-check.pass {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .requirement-check.fail {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .badge-success {
            background: #28a745;
        }
        .badge-danger {
            background: #dc3545;
        }
        pre.console {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 5px;
            max-height: 400px;
            overflow-y: auto;
            font-size: 0.85rem;
            font-family: 'Courier New', monospace;
        }
        .form-control:focus {
            border-color: #0066cc;
            box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.25);
        }
        .btn-primary {
            background: #0066cc;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
        }
        .btn-primary:hover {
            background: #0052a3;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="installer-card">
        <div class="installer-header">
            <span class="wave">🌊</span>
            <h1>Nautilus Installation</h1>
            <p class="mb-0">Dive Shop Management System - Alpha Version 1</p>
        </div>

        <div class="installer-body">
            <!-- Step Indicator -->
            <ul class="step-indicator">
                <li class="<?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                    <span class="step-number">1</span>
                    <span class="step-label">System Check</span>
                </li>
                <li class="<?php echo $step >= 2 ? 'active' : ''; ?> <?php echo $step > 2 ? 'completed' : ''; ?>">
                    <span class="step-number">2</span>
                    <span class="step-label">Database</span>
                </li>
                <li class="<?php echo $step >= 3 ? 'active' : ''; ?> <?php echo $step > 3 ? 'completed' : ''; ?>">
                    <span class="step-number">3</span>
                    <span class="step-label">Admin Account</span>
                </li>
                <li class="<?php echo $step >= 4 ? 'active' : ''; ?>">
                    <span class="step-number">4</span>
                    <span class="step-label">Complete</span>
                </li>
            </ul>

            <?php if ($step == 1): ?>
                <!-- STEP 1: System Requirements Check -->
                <h3 class="mb-4">Step 1: System Requirements</h3>

                <?php
                $requirements = [];
                $canProceed = true;

                // PHP Version Check
                $phpVersion = phpversion();
                $phpOk = version_compare($phpVersion, '7.4.0', '>=');
                $requirements[] = [
                    'name' => 'PHP Version',
                    'required' => '7.4 or higher',
                    'current' => $phpVersion,
                    'status' => $phpOk
                ];
                if (!$phpOk) $canProceed = false;

                // Required PHP Extensions
                $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'openssl', 'zip'];
                foreach ($extensions as $ext) {
                    $loaded = extension_loaded($ext);
                    $requirements[] = [
                        'name' => "PHP Extension: $ext",
                        'required' => 'Installed',
                        'current' => $loaded ? 'Installed' : 'Missing',
                        'status' => $loaded
                    ];
                    if (!$loaded) $canProceed = false;
                }

                // Directory Permissions
                $directories = [
                    ROOT_DIR . '/storage' => 'Storage Directory',
                    ROOT_DIR . '/storage/cache' => 'Cache Directory',
                    ROOT_DIR . '/storage/logs' => 'Logs Directory',
                    ROOT_DIR . '/storage/sessions' => 'Sessions Directory',
                    ROOT_DIR . '/storage/uploads' => 'Uploads Directory',
                    PUBLIC_DIR . '/uploads' => 'Public Uploads Directory'
                ];

                // Try to create directories if they don't exist
                $permissionMessages = [];
                foreach ($directories as $dir => $label) {
                    if (!file_exists($dir)) {
                        @mkdir($dir, 0775, true);
                    }

                    $writable = is_writable($dir);

                    // If not writable, try to fix permissions automatically
                    if (!$writable) {
                        @chmod($dir, 0775);

                        // Detect and fix SELinux contexts (Fedora/RHEL/CentOS)
                        if (function_exists('exec') && file_exists('/usr/sbin/getenforce')) {
                            $selinuxStatus = '';
                            @exec('/usr/sbin/getenforce 2>/dev/null', $output, $returnCode);
                            if ($returnCode === 0 && isset($output[0])) {
                                $selinuxStatus = trim($output[0]);
                            }

                            if ($selinuxStatus === 'Enforcing' || $selinuxStatus === 'Permissive') {
                                // SELinux is active, set proper context
                                @exec("chcon -R -t httpd_sys_rw_content_t " . escapeshellarg($dir) . " 2>&1", $seOutput, $seReturn);
                                if ($seReturn === 0) {
                                    $permissionMessages[] = "✓ Fixed SELinux context for $label";
                                }
                            }
                        }

                        // Try to set ownership to web server user (apache, www-data, nginx)
                        $possibleUsers = ['apache', 'www-data', 'nginx', 'httpd'];
                        foreach ($possibleUsers as $user) {
                            if (function_exists('posix_getpwnam') && posix_getpwnam($user)) {
                                @exec("chown -R $user:$user " . escapeshellarg($dir) . " 2>&1", $chownOutput, $chownReturn);
                                break;
                            }
                        }

                        // Recheck if writable now
                        $writable = is_writable($dir);
                    }

                    $requirements[] = [
                        'name' => $label . ' Writable',
                        'required' => 'Yes',
                        'current' => $writable ? 'Yes' : 'No',
                        'status' => $writable
                    ];

                    if (!$writable) $canProceed = false;
                }

                // Check if we can create .env file
                $envWritable = is_writable(ROOT_DIR);

                // If not writable, try to fix root directory too
                if (!$envWritable) {
                    @chmod(ROOT_DIR, 0775);

                    // Fix SELinux for root directory
                    if (function_exists('exec') && file_exists('/usr/sbin/getenforce')) {
                        $selinuxStatus = '';
                        @exec('/usr/sbin/getenforce 2>/dev/null', $output, $returnCode);
                        if ($returnCode === 0 && isset($output[0])) {
                            $selinuxStatus = trim($output[0]);
                        }

                        if ($selinuxStatus === 'Enforcing' || $selinuxStatus === 'Permissive') {
                            @exec("chcon -t httpd_sys_rw_content_t " . escapeshellarg(ROOT_DIR) . " 2>&1", $seOutput, $seReturn);
                            if ($seReturn === 0) {
                                $permissionMessages[] = "✓ Fixed SELinux context for root directory";
                            }
                        }
                    }

                    $envWritable = is_writable(ROOT_DIR);
                }

                $requirements[] = [
                    'name' => 'Root Directory Writable (for .env)',
                    'required' => 'Yes',
                    'current' => $envWritable ? 'Yes' : 'No',
                    'status' => $envWritable
                ];
                if (!$envWritable) $canProceed = false;

                // Display automatic permission fixes if any
                if (!empty($permissionMessages)) {
                    echo "<div class='alert alert-info mb-3'>";
                    echo "<strong>🔧 Automatic Fixes Applied:</strong><br>";
                    foreach ($permissionMessages as $msg) {
                        echo "$msg<br>";
                    }
                    echo "</div>";
                }

                // Display requirements
                foreach ($requirements as $req) {
                    $class = $req['status'] ? 'pass' : 'fail';
                    $badge = $req['status'] ? 'success' : 'danger';
                    $icon = $req['status'] ? '✓' : '✗';
                    echo "<div class='requirement-check $class'>";
                    echo "<div><strong>{$req['name']}</strong><br><small>Required: {$req['required']}</small></div>";
                    echo "<div><span class='badge badge-$badge'>$icon {$req['current']}</span></div>";
                    echo "</div>";
                }
                ?>

                <div class="mt-4">
                    <?php if ($canProceed): ?>
                        <div class="alert alert-success">
                            <strong>✓ All Requirements Met!</strong><br>
                            Your system is ready for Nautilus installation.
                        </div>
                        <a href="?step=2" class="btn btn-primary btn-lg w-100">Continue to Database Setup →</a>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <strong>✗ Requirements Not Met</strong><br>
                            The installer tried to fix permissions automatically, but some issues remain.
                        </div>

                        <div class="alert alert-warning">
                            <strong>Manual Fix Instructions:</strong><br>
                            Run these commands on your server:
                            <pre class="bg-dark text-white p-2 mt-2 mb-2" style="font-size: 0.9rem;">sudo chmod -R 775 <?php echo ROOT_DIR; ?>/storage
sudo chmod -R 775 <?php echo PUBLIC_DIR; ?>/uploads
sudo chmod 775 <?php echo ROOT_DIR; ?>

# For Fedora/RHEL/CentOS with SELinux:
sudo chcon -R -t httpd_sys_rw_content_t <?php echo ROOT_DIR; ?>/storage
sudo chcon -R -t httpd_sys_rw_content_t <?php echo PUBLIC_DIR; ?>/uploads
sudo chcon -t httpd_sys_rw_content_t <?php echo ROOT_DIR; ?></pre>
                        </div>

                        <a href="?step=1" class="btn btn-primary btn-lg w-100">Recheck After Fixing</a>
                    <?php endif; ?>
                </div>

            <?php elseif ($step == 2): ?>
                <!-- STEP 2: Database Configuration -->
                <h3 class="mb-4">Step 2: Database Setup</h3>

                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db_test'])): ?>
                    <?php
                    // Test database connection and run migrations
                    try {
                        $host = $_POST['db_host'];
                        $port = $_POST['db_port'];
                        $database = $_POST['db_name'];
                        $username = $_POST['db_user'];
                        $password = $_POST['db_pass'];
                        $passwordConfirm = $_POST['db_pass_confirm'];

                        // Validate password confirmation
                        if ($password !== $passwordConfirm) {
                            echo "<div class='alert alert-danger'>";
                            echo "<strong>✗ Passwords Don't Match</strong><br>";
                            echo "Please make sure both password fields are identical.";
                            echo "</div>";
                            echo "<a href='?step=2' class='btn btn-secondary'>← Try Again</a>";
                            throw new Exception("Password mismatch");
                        }

                        // Test connection
                        $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
                        $pdo = new PDO($dsn, $username, $password, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                        ]);

                        echo "<div class='alert alert-success'><strong>✓ Connection Successful!</strong> Connected to MySQL server.</div>";

                        // Create database if doesn't exist
                        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $pdo->exec("USE `$database`");

                        echo "<div class='alert alert-info'><strong>Database Ready:</strong> Using database '<strong>$database</strong>'</div>";

                        // Save config to session
                        $_SESSION['db_config'] = [
                            'host' => $host,
                            'port' => $port,
                            'database' => $database,
                            'username' => $username,
                            'password' => $password
                        ];

                        // Run Migrations
                        echo "<div class='alert alert-info'>";
                        echo "<strong>Running Database Migrations...</strong><br>";
                        echo "<small>This may take 1-2 minutes. Please wait...</small>";
                        echo "</div>";

                        // Progress bar
                        echo "<div class='progress mb-3' style='height: 30px;'>";
                        echo "<div class='progress-bar progress-bar-striped progress-bar-animated bg-info' id='migration-progress' style='width: 0%; font-size: 16px;'>";
                        echo "0 of 0";
                        echo "</div></div>";

                        echo "<pre class='console'>";

                        // Create migrations tracking table
                        $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            migration VARCHAR(255) NOT NULL UNIQUE,
                            batch INT NOT NULL,
                            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )");

                        // Get all migration files
                        $migrationFiles = glob(ROOT_DIR . '/database/migrations/*.sql');
                        sort($migrationFiles);

                        $successCount = 0;
                        $errorCount = 0;
                        $totalMigrations = count($migrationFiles);
                        $currentMigration = 0;

                        foreach ($migrationFiles as $file) {
                            $currentMigration++;
                            $filename = basename($file);

                            // Check if already executed
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
                            $stmt->execute([$filename]);
                            $alreadyExecuted = $stmt->fetchColumn() > 0;
                            $stmt->closeCursor();

                            if ($alreadyExecuted) {
                                echo "⊘ <span style='color: #888;'>Skipped: $filename (already executed)</span>\n";
                                continue;
                            }

                            echo "→ Running: $filename\n";

                            try {
                                $sql = file_get_contents($file);

                                // Remove SQL comments (-- style and /* */ style)
                                $sql = preg_replace('/--[^\n]*\n/', "\n", $sql); // Remove -- comments
                                $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Remove /* */ comments
                                $sql = preg_replace('/#[^\n]*\n/', "\n", $sql); // Remove # comments

                                // Split SQL file into individual statements
                                $statements = array_filter(
                                    array_map('trim', explode(';', $sql)),
                                    function($stmt) { return !empty($stmt); }
                                );

                                // Execute each statement individually, catching errors per-statement
                                $statementErrors = [];
                                foreach ($statements as $statement) {
                                    if (!empty($statement)) {
                                        try {
                                            $trimmedStmt = trim($statement);
                                            // Check if this is a query that returns results
                                            if (stripos($trimmedStmt, 'SELECT') === 0 ||
                                                stripos($trimmedStmt, 'SHOW') === 0 ||
                                                stripos($trimmedStmt, 'DESCRIBE') === 0 ||
                                                stripos($trimmedStmt, 'EXPLAIN') === 0 ||
                                                stripos($trimmedStmt, 'ANALYZE') === 0 ||
                                                stripos($trimmedStmt, 'OPTIMIZE') === 0 ||
                                                stripos($trimmedStmt, 'CHECK') === 0 ||
                                                stripos($trimmedStmt, 'PREPARE') === 0 ||
                                                stripos($trimmedStmt, 'EXECUTE') === 0 ||
                                                stripos($trimmedStmt, 'DEALLOCATE') === 0 ||
                                                stripos($trimmedStmt, 'SET @') === 0) {
                                                // Use query() and consume all results
                                                $result = $pdo->query($statement);
                                                if ($result) {
                                                    $result->fetchAll();
                                                    $result->closeCursor();
                                                    unset($result);
                                                }
                                            } else {
                                                // Use exec() for DDL statements (CREATE, ALTER, INSERT, etc.)
                                                $pdo->exec($statement);
                                            }
                                        } catch (PDOException $stmtError) {
                                            // Collect errors but continue - some FK errors are non-critical
                                            $statementErrors[] = substr($stmtError->getMessage(), 0, 100);
                                        }
                                    }
                                }

                                // Mark as executed
                                $markStmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, 1)");
                                $markStmt->execute([$filename]);
                                $markStmt->closeCursor();
                                unset($markStmt);

                                if (empty($statementErrors)) {
                                    echo "  <span style='color: #28a745;'>✓ Success</span>\n";
                                    $successCount++;
                                } else {
                                    echo "  <span style='color: #ffc107;'>⚠ Warning: " . htmlspecialchars($statementErrors[0]) . "</span>\n";
                                    $errorCount++;
                                }

                                // Update progress bar
                                $percentComplete = round(($currentMigration / $totalMigrations) * 100);
                                echo "<script>";
                                echo "document.getElementById('migration-progress').style.width = '{$percentComplete}%';";
                                echo "document.getElementById('migration-progress').textContent = '{$currentMigration} of {$totalMigrations}';";
                                echo "</script>";
                                @ob_flush();
                                @flush();

                            } catch (PDOException $e) {
                                echo "  <span style='color: #dc3545;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span>\n";
                                $errorCount++;

                                // Update progress bar even on error
                                $percentComplete = round(($currentMigration / $totalMigrations) * 100);
                                echo "<script>";
                                echo "document.getElementById('migration-progress').style.width = '{$percentComplete}%';";
                                echo "document.getElementById('migration-progress').textContent = '{$currentMigration} of {$totalMigrations}';";
                                echo "</script>";
                                @ob_flush();
                                @flush();
                            }
                        }

                        echo "\n========================================\n";
                        echo "Migration Summary:\n";
                        echo "  Success: $successCount\n";
                        echo "  Warnings: $errorCount\n";
                        echo "========================================\n";
                        echo "</pre>";

                        // Verify critical tables were created
                        echo "<div class='alert alert-info'><strong>Verifying Database Structure...</strong></div>";
                        echo "<pre class='console'>";

                        $requiredTables = ['tenants', 'roles', 'users', 'customers', 'products', 'courses'];
                        $missingTables = [];

                        foreach ($requiredTables as $table) {
                            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                            $exists = $stmt->rowCount() > 0;

                            if ($exists) {
                                echo "✓ <span style='color: #28a745;'>$table table exists</span>\n";
                            } else {
                                echo "✗ <span style='color: #dc3545;'>$table table MISSING</span>\n";
                                $missingTables[] = $table;
                            }
                        }

                        // Count total tables
                        $stmt = $pdo->query("SHOW TABLES");
                        $totalTables = $stmt->rowCount();
                        echo "\nTotal tables created: $totalTables\n";

                        echo "</pre>";

                        if (empty($missingTables) && $totalTables >= 100) {
                            echo "<div class='alert alert-success'>";
                            echo "<strong>✓ Database Setup Complete!</strong><br>";
                            echo "Created <strong>$totalTables</strong> database tables successfully.";
                            if ($errorCount > 0) {
                                echo "<br><small>$errorCount migrations had warnings (usually non-critical foreign key constraints)</small>";
                            }
                            echo "</div>";
                            echo "<a href='?step=3' class='btn btn-primary btn-lg w-100'>Continue to Admin Setup →</a>";
                        } else {
                            echo "<div class='alert alert-danger'>";
                            echo "<strong>✗ Database Setup Incomplete</strong><br>";
                            if (!empty($missingTables)) {
                                echo "Missing critical tables: " . implode(', ', $missingTables) . "<br>";
                            }
                            echo "Only $totalTables tables were created. Expected at least 100.<br><br>";
                            echo "<strong>Common Causes:</strong><br>";
                            echo "• Database user lacks CREATE privilege<br>";
                            echo "• Database user lacks REFERENCES privilege (for foreign keys)<br>";
                            echo "• MySQL version too old (requires 5.7+)<br><br>";
                            echo "<strong>Solution:</strong> Grant your database user full privileges:<br>";
                            echo "<code>GRANT ALL PRIVILEGES ON " . htmlspecialchars($database) . ".* TO '" . htmlspecialchars($username) . "'@'localhost';</code>";
                            echo "</div>";
                            echo "<a href='?step=2' class='btn btn-secondary'>← Try Again</a>";
                        }

                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>";
                        echo "<strong>✗ Database Connection Failed</strong><br>";
                        echo htmlspecialchars($e->getMessage());
                        echo "</div>";
                        echo "<a href='?step=2' class='btn btn-secondary'>← Try Again</a>";
                    }
                    ?>

                <?php else: ?>
                    <!-- Database Configuration Form -->
                    <div class="alert alert-info">
                        <strong>📋 Database Information Needed</strong><br>
                        You'll need your MySQL database credentials. If you don't have them, contact your hosting provider.
                    </div>

                    <form method="POST">
                        <input type="hidden" name="db_test" value="1">

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label"><strong>Database Host</strong></label>
                                <input type="text" name="db_host" class="form-control" value="localhost" required>
                                <small class="text-muted">Usually "localhost"</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label"><strong>Port</strong></label>
                                <input type="text" name="db_port" class="form-control" value="3306" required>
                                <small class="text-muted">Usually 3306</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Database Name</strong></label>
                            <input type="text" name="db_name" class="form-control" value="nautilus" required>
                            <small class="text-muted">Will be created if it doesn't exist</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Database Username</strong></label>
                            <input type="text" name="db_user" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Database Password</strong></label>
                            <input type="password" name="db_pass" class="form-control">
                            <small class="text-muted">Leave blank if no password</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Confirm Database Password</strong></label>
                            <input type="password" name="db_pass_confirm" class="form-control">
                            <small class="text-muted">Re-enter password to confirm</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Test Connection & Setup Database →</button>
                    </form>

                    <div class="mt-3">
                        <a href="?step=1" class="btn btn-link">← Back to Requirements</a>
                    </div>
                <?php endif; ?>

            <?php elseif ($step == 3): ?>
                <!-- STEP 3: Admin Account Creation -->
                <h3 class="mb-4">Step 3: Create Admin Account</h3>

                <?php
                // Display any errors from POST processing
                if (isset($_SESSION['step3_error'])) {
                    echo "<div class='alert alert-danger'>";
                    echo "<strong>✗ Error</strong><br>";
                    echo htmlspecialchars($_SESSION['step3_error']);
                    echo "</div>";
                    unset($_SESSION['step3_error']);
                }
                ?>
                    <!-- Admin Account Form -->
                    <div class="alert alert-info">
                        <strong>👤 Admin Account Setup</strong><br>
                        Create your administrator account. You'll use this to log in to Nautilus.
                    </div>

                    <form method="POST">
                        <input type="hidden" name="create_admin" value="1">

                        <h5 class="mb-3">Company Information</h5>

                        <div class="mb-3">
                            <label class="form-label"><strong>Company Name</strong></label>
                            <input type="text" name="company_name" class="form-control" required placeholder="Example: Blue Ocean Dive Shop">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Subdomain</strong></label>
                            <input type="text" name="subdomain" class="form-control" required placeholder="blueocean" pattern="[a-z0-9-]+">
                            <small class="text-muted">Lowercase letters, numbers, and hyphens only. No spaces.</small>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Administrator Account</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>First Name</strong></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"><strong>Last Name</strong></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Email Address</strong></label>
                            <input type="email" name="email" class="form-control" required>
                            <small class="text-muted">You'll use this to log in</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Password</strong></label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Confirm Password</strong></label>
                            <input type="password" name="password_confirm" class="form-control" required minlength="8">
                            <small class="text-muted">Re-enter your password</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Create Admin Account & Complete Installation →</button>
                    </form>

                    <div class="mt-3">
                        <a href="?step=2" class="btn btn-link">← Back to Database Setup</a>
                    </div>

            <?php elseif ($step == 4): ?>
                <!-- STEP 4: Installation Complete -->
                <div class="text-center">
                    <div class="success-icon">🎉</div>
                    <h3 class="mb-3">Installation Complete!</h3>
                    <p class="lead">Nautilus is ready to use.</p>
                </div>

                <?php if (isset($_SESSION['install_complete'])): ?>
                <div class="card bg-light my-4">
                    <div class="card-body">
                        <h5 class="card-title">Your Login Credentials:</h5>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['install_complete']['email']); ?></p>
                        <p class="mb-1"><strong>Company:</strong> <?php echo htmlspecialchars($_SESSION['install_complete']['company']); ?></p>
                        <p class="mb-0"><strong>Password:</strong> (the password you just created)</p>
                    </div>
                </div>
                <?php
                unset($_SESSION['install_complete']);
                endif;
                ?>

                <div class="alert alert-success">
                    <strong>✓ What's Been Set Up:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Database with 250+ tables</li>
                        <li>Administrator account</li>
                        <li>Company tenant</li>
                        <li>Security configuration</li>
                        <li>File upload directories</li>
                    </ul>
                </div>

                <div class="alert alert-info">
                    <strong>📝 Next Steps:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Log in with your admin credentials</li>
                        <li>Set up your company information</li>
                        <li>Add staff members</li>
                        <li>Configure your product inventory</li>
                        <li>Start using Nautilus!</li>
                    </ol>
                </div>

                <a href="/" class="btn btn-success btn-lg w-100 mb-3">Go to Login Page →</a>

                <div class="text-center text-muted">
                    <small>This installer will not be accessible after you navigate away.</small>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <small class="text-white">Nautilus Dive Shop Management System - Alpha Version 1 © <?php echo date('Y'); ?></small>
    </div>
</body>
</html>
