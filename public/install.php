<?php
/**
 * Nautilus Dive Shop Management System v3.0
 * One-Click Installer
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if already installed
if (file_exists('.installed')) {
    die('<h1>Already Installed</h1><p>Nautilus is already installed. Delete the .installed file to reinstall.</p>');
}

// Installation steps
$step = $_GET['step'] ?? 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .installer-card {
            max-width: 800px;
            margin: 40px auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-bottom: 3px solid #ddd;
            color: #999;
        }
        .step.active {
            border-bottom-color: #667eea;
            color: #667eea;
            font-weight: bold;
        }
        .step.completed {
            border-bottom-color: #28a745;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card installer-card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">🌊 Nautilus v3.0 Installation</h3>
                <p class="mb-0 small">Enterprise Dive Shop Management System</p>
            </div>
            <div class="card-body">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1. Requirements</div>
                    <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2. Database</div>
                    <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">3. Admin</div>
                    <div class="step <?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'completed' : '' ?>">4. Complete</div>
                </div>

                <?php if ($step == 1): ?>
                    <!-- Step 1: System Requirements -->
                    <h4>System Requirements Check</h4>

                    <?php
                    // Auto-fix permissions and create directories
                    $fixLog = [];
                    $directoriesCreated = false;
                    $permissionsFixed = false;

                    // Create required directories
                    $baseDir = dirname(__DIR__);  // /var/www/html/nautilus
                    $requiredDirs = [
                        $baseDir . '/storage',
                        $baseDir . '/storage/cache',
                        $baseDir . '/storage/logs',
                        $baseDir . '/storage/exports',
                        $baseDir . '/storage/backups',
                        __DIR__ . '/uploads'
                    ];

                    foreach ($requiredDirs as $dir) {
                        if (!file_exists($dir)) {
                            if (@mkdir($dir, 0775, true)) {
                                $fixLog[] = "Created directory: " . basename($dir);
                                $directoriesCreated = true;
                            }
                        }
                    }

                    // Try to fix permissions
                    $dirsToFix = [
                        $baseDir . '/storage',
                        __DIR__ . '/uploads'
                    ];

                    foreach ($dirsToFix as $dir) {
                        if (file_exists($dir) && !is_writable($dir)) {
                            if (@chmod($dir, 0775)) {
                                $fixLog[] = "Fixed permissions on: " . basename($dir);
                                $permissionsFixed = true;
                            }
                        }
                    }

                    // Display auto-fix results
                    if (!empty($fixLog)):
                    ?>
                    <div class="alert alert-info mb-3">
                        <h6><strong>Auto-Fix Applied:</strong></h6>
                        <ul class="mb-0">
                            <?php foreach ($fixLog as $log): ?>
                            <li><?= htmlspecialchars($log) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="list-group mb-4">
                        <?php
                        $requirements = [
                            'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
                            'PDO Extension' => extension_loaded('pdo'),
                            'PDO MySQL' => extension_loaded('pdo_mysql'),
                            'MySQLi Extension' => extension_loaded('mysqli'),
                            'MBString Extension' => extension_loaded('mbstring'),
                            'OpenSSL Extension' => extension_loaded('openssl'),
                            'cURL Extension' => extension_loaded('curl'),
                            'JSON Extension' => extension_loaded('json'),
                            'GD Extension' => extension_loaded('gd'),
                            'storage/ Writable' => is_writable($baseDir . '/storage'),
                            'public/uploads/ Writable' => is_writable(__DIR__ . '/uploads')
                        ];

                        $allPassed = true;
                        $permissionIssues = [];

                        foreach ($requirements as $requirement => $passed) {
                            if (!$passed) {
                                $allPassed = false;
                                if (strpos($requirement, 'Writable') !== false) {
                                    $permissionIssues[] = $requirement;
                                }
                            }
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                            echo $requirement;
                            echo '<span class="badge bg-' . ($passed ? 'success' : 'danger') . '">';
                            echo $passed ? '✓ Pass' : '✗ Fail';
                            echo '</span></div>';
                        }
                        ?>
                    </div>

                    <?php if ($allPassed): ?>
                        <div class="alert alert-success">
                            <strong>Great!</strong> Your server meets all requirements.
                        </div>
                        <a href="?step=2" class="btn btn-primary btn-lg">Continue to Database Setup →</a>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <strong>Requirements Not Met</strong>
                            <?php if (!empty($permissionIssues)): ?>
                            <hr>
                            <p class="mb-2">To fix permission issues, run this command on your server:</p>
                            <pre class="bg-dark text-white p-3 mb-2"><code>sudo chmod -R 775 <?= __DIR__ ?>/storage <?= __DIR__ ?>/public/uploads
sudo chown -R apache:apache <?= __DIR__ ?>/storage <?= __DIR__ ?>/public/uploads</code></pre>
                            <p class="mb-2">Or use the included fix script:</p>
                            <pre class="bg-dark text-white p-3 mb-0"><code>sudo bash fix-permissions.sh</code></pre>
                            <?php else: ?>
                            <p class="mb-0">Please install missing PHP extensions.</p>
                            <?php endif; ?>
                        </div>
                        <button onclick="location.reload()" class="btn btn-secondary btn-lg">Retry Check</button>
                    <?php endif; ?>

                <?php elseif ($step == 2): ?>
                    <!-- Step 2: Database Configuration -->
                    <h4>Database Configuration</h4>

                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <?php
                        // Test database connection
                        try {
                            $host = $_POST['db_host'];
                            $port = $_POST['db_port'];
                            $database = $_POST['db_name'];
                            $username = $_POST['db_user'];
                            $password = $_POST['db_pass'];

                            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
                            $pdo = new PDO($dsn, $username, $password, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                            ]);

                            // Create database if it doesn't exist
                            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            $pdo->exec("USE `$database`");

                            // Save credentials to session
                            $_SESSION['db_config'] = [
                                'host' => $host,
                                'port' => $port,
                                'database' => $database,
                                'username' => $username,
                                'password' => $password
                            ];

                            // Run migrations
                            echo '<div class="alert alert-info">Running database migrations...</div>';
                            echo '<pre class="bg-light p-3 mb-3" style="max-height: 400px; overflow-y: auto;">';

                            $migrationFiles = glob(dirname(__DIR__) . '/database/migrations/*.sql');
                            sort($migrationFiles);

                            // Create migrations tracking table
                            $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                migration VARCHAR(255) NOT NULL UNIQUE,
                                batch INT NOT NULL,
                                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                            )");

                            $successCount = 0;
                            $errorCount = 0;

                            foreach ($migrationFiles as $file) {
                                $filename = basename($file);

                                // Check if already executed
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
                                $stmt->execute([$filename]);
                                $alreadyExecuted = $stmt->fetchColumn() > 0;
                                $stmt->closeCursor();

                                if ($alreadyExecuted) {
                                    echo "⊘ Skipped: $filename (already executed)\n";
                                    continue;
                                }

                                echo "→ Running: $filename\n";

                                try {
                                    $sql = file_get_contents($file);

                                    // Split SQL file into individual statements
                                    $statements = array_filter(
                                        array_map('trim', explode(';', $sql)),
                                        function($stmt) { return !empty($stmt); }
                                    );

                                    // Execute each statement
                                    foreach ($statements as $statement) {
                                        if (!empty($statement)) {
                                            // Use query() for statements that might return results (like ANALYZE)
                                            // and free the result set immediately
                                            $result = $pdo->query($statement);
                                            if ($result !== false) {
                                                $result->closeCursor();
                                            }
                                        }
                                    }

                                    // Mark as executed
                                    $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, 1)");
                                    $stmt->execute([$filename]);

                                    echo "  ✓ Success\n";
                                    $successCount++;
                                } catch (PDOException $e) {
                                    echo "  ✗ Error: " . $e->getMessage() . "\n";
                                    $errorCount++;
                                }
                            }

                            echo "\n========================================\n";
                            echo "Migration Summary:\n";
                            echo "  Executed: $successCount\n";
                            echo "  Errors: $errorCount\n";
                            echo "========================================\n";
                            echo '</pre>';

                            if ($errorCount === 0 || $successCount > 50) {
                                echo '<div class="alert alert-success">Database setup complete!</div>';
                                echo '<a href="?step=3" class="btn btn-primary btn-lg">Continue to Admin Setup →</a>';
                            } else {
                                echo '<div class="alert alert-warning">Some migrations failed, but you can continue. Issues can be fixed later.</div>';
                                echo '<a href="?step=3" class="btn btn-primary btn-lg">Continue Anyway →</a>';
                            }

                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger">';
                            echo '<strong>Database Connection Failed:</strong><br>';
                            echo htmlspecialchars($e->getMessage());
                            echo '</div>';
                            echo '<a href="?step=2" class="btn btn-secondary">← Try Again</a>';
                        }
                        ?>

                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Database Host</label>
                                <input type="text" name="db_host" class="form-control" value="localhost" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Database Port</label>
                                <input type="text" name="db_port" class="form-control" value="3306" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Database Name</label>
                                <input type="text" name="db_name" class="form-control" value="nautilus" required>
                                <small class="form-text text-muted">Database will be created if it doesn't exist</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Database Username</label>
                                <input type="text" name="db_user" class="form-control" value="root" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Database Password</label>
                                <input type="password" name="db_pass" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg">Test Connection & Setup Database →</button>
                        </form>
                    <?php endif; ?>

                <?php elseif ($step == 3): ?>
                    <!-- Step 3: Admin Account -->
                    <h4>Create Admin Account</h4>

                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <?php
                        // Validate password confirmation
                        if ($_POST['password'] !== $_POST['password_confirm']) {
                            echo '<div class="alert alert-danger">';
                            echo '<strong>Error:</strong> Passwords do not match. Please try again.';
                            echo '</div>';
                            echo '<a href="?step=3" class="btn btn-secondary">← Try Again</a>';
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

                            // Get or create admin role
                            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
                            $stmt->execute();
                            $roleResult = $stmt->fetch();
                            $roleId = $roleResult ? $roleResult['id'] : 1; // Default to role ID 1 if not found

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

                            // Assign admin role to user (roleId was already set above)
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
                            $envContent .= "APP_NAME=\"Nautilus v3.0\"\n";
                            $envContent .= "APP_ENV=production\n";
                            $envContent .= "APP_DEBUG=true\n";
                            $envContent .= "APP_URL=https://nautilus.local\n";
                            $envContent .= "APP_TIMEZONE=America/Chicago\n\n";
                            $envContent .= "# Security\n";
                            $envContent .= "APP_KEY=base64:" . base64_encode(random_bytes(32)) . "\n";
                            $envContent .= "JWT_SECRET=" . base64_encode(random_bytes(32)) . "\n";
                            $envContent .= "SESSION_LIFETIME=120\n";
                            $envContent .= "PASSWORD_MIN_LENGTH=12\n\n";
                            $envContent .= "# Cache & Session\n";
                            $envContent .= "CACHE_DRIVER=file\n";
                            $envContent .= "SESSION_DRIVER=file\n\n";
                            $envContent .= "# File Storage\n";
                            $envContent .= "UPLOAD_MAX_SIZE=10485760\n";
                            $envContent .= "ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx\n";

                            file_put_contents(dirname(__DIR__) . '/.env', $envContent);

                            // Create .installed file
                            file_put_contents(dirname(__DIR__) . '/.installed', date('Y-m-d H:i:s'));

                            // Clear session
                            session_destroy();

                            echo '<div class="alert alert-success">';
                            echo '<h5>✓ Installation Complete!</h5>';
                            echo '<p>Your Nautilus system is ready to use.</p>';
                            echo '</div>';

                            echo '<div class="card bg-light mb-3">';
                            echo '<div class="card-body">';
                            echo '<h6>Your Admin Credentials:</h6>';
                            echo '<strong>Email:</strong> ' . htmlspecialchars($_POST['email']) . '<br>';
                            echo '<strong>Password:</strong> (the password you just created)<br>';
                            echo '<strong>Company:</strong> ' . htmlspecialchars($_POST['company_name']);
                            echo '</div>';
                            echo '</div>';

                            echo '<a href="/login" class="btn btn-success btn-lg">Go to Login Page →</a>';

                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">';
                            echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                            echo '</div>';
                            echo '<a href="?step=3" class="btn btn-secondary">← Try Again</a>';
                        }
                        } // End password validation else
                        ?>

                    <?php else: ?>
                        <form method="POST">
                            <h5 class="mb-3">Company Information</h5>
                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company_name" class="form-control" required placeholder="Coral Reef Divers">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subdomain</label>
                                <input type="text" name="subdomain" class="form-control" required placeholder="coralreef" pattern="[a-z0-9-]+">
                                <small class="form-text text-muted">Lowercase letters, numbers, and hyphens only</small>
                            </div>

                            <h5 class="mb-3 mt-4">Administrator Account</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                                <small class="form-text text-muted">Minimum 8 characters</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirm" class="form-control" required minlength="8">
                                <small class="form-text text-muted">Re-enter your password</small>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg">Complete Installation →</button>
                        </form>
                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
