<?php
/**
 * Nautilus Installer v2.0 - Using Migration Files
 *
 * Philosophy:
 * - Use existing tested migration files
 * - Fast installation via batch processing
 * - Clear progress feedback
 * - Works on any hosting platform
 */

session_start();
define('ROOT_DIR', dirname(__DIR__));

// Redirect to streamlined installer by default
// Users can still access this by going directly to install.php?legacy=1
if (!isset($_GET['legacy'])) {
    header('Location: /install_streamlined.php');
    exit;
}

// Prevent access after installation
if (file_exists(ROOT_DIR . '/.installed')) {
    header('Location: /'); exit;
}

$step = $_GET['step'] ?? 1;
$error = null;
$success = null;

/**
 * Step 1: Requirements Check
 */
if ($step == 1) {
    $checks = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'MBString Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'cURL Extension' => extension_loaded('curl'),
        'GD Extension' => extension_loaded('gd'),
        'Storage Directory Writable' => is_writable(ROOT_DIR . '/storage') || @mkdir(ROOT_DIR . '/storage', 0775, true),
        'Root Directory Writable' => is_writable(ROOT_DIR)
    ];

    $all_passed = !in_array(false, $checks, true);
}

/**
 * Step 2: Database Configuration
 */
if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_config = [
        'host' => $_POST['db_host'] ?? 'localhost',
        'port' => $_POST['db_port'] ?? 3306,
        'database' => $_POST['db_name'],
        'username' => $_POST['db_user'],
        'password' => 'Frogman09!'  // Hardcoded for container
    ];

    try {
        $pdo = new PDO(
            "mysql:host={$db_config['host']};port={$db_config['port']}",
            $db_config['username'],
            $db_config['password']
        );

        // Create database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_config['database']}`
                    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $_SESSION['db_config'] = $db_config;
        header('Location: ?step=3');
        exit;
    } catch (PDOException $e) {
        $error = "Database connection failed: " . $e->getMessage();
    }
}

/**
 * Step 3: Install Database Schema
 */
if ($step == 3) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Redirect to CLI-based migration runner
        header('Location: run_migrations.php');
        exit;
    }
}

// ORIGINAL STEP 3 PDO CODE - REPLACED WITH REDIRECT ABOVE
/*
if ($step == 3) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $config = $_SESSION['db_config'];
        try {
            set_time_limit(300); // 5 minutes for migration
            
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create migrations tracking table
            $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Get all migration files
            $migration_files = glob(ROOT_DIR . '/database/migrations/*.sql');
            sort($migration_files);
            
            if (empty($migration_files)) {
                throw new Exception("No migration files found in database/migrations/");
            }

            $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
            $processed = 0;
            $errors = 0;

            // Process each migration file
            foreach ($migration_files as $file) {
                $filename = basename($file);
                
                // Skip if already executed
                if (in_array($filename, $executed)) {
                    continue;
                }

                $sql = file_get_contents($file);
                if ($sql === false) {
                    continue;
                }

                // Split into statements
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) { 
                        return !empty($stmt) && !preg_match('/^\s*--/', $stmt); 
                    }
                );

                // Execute each statement
                foreach ($statements as $stmt) {
                    if (empty(trim($stmt))) continue;
                    try {
                        $pdo->exec($stmt);
                    } catch (PDOException $e) {
                        // Log but continue - some statements may fail on duplicate runs
                        $errors++;
                    }
                }

                // Mark migration as executed
                try {
                    $pdo->exec("INSERT IGNORE INTO migrations (migration, batch) 
                               VALUES (" . $pdo->quote($filename) . ", 1)");
                    $processed++;
                } catch (PDOException $e) {
                    // Continue even if tracking fails
                }
            }

            // Verify installation
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('tenants', $tables) || !in_array('users', $tables)) {
                throw new Exception("Installation incomplete - missing critical tables. Processed $processed migrations.");
            }

            $success = "Database installed successfully! ($processed migrations processed)";
            $_SESSION['db_installed'] = true;

            sleep(1);
            header('Location: ?step=4');
            exit;
        } catch (Exception $e) {
            $error = "Installation failed: " . $e->getMessage();
        }
    }
}

/**
 * Step 4: Create Admin Account
 */
if ($step == 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = $_SESSION['db_config'];

    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
            $config['username'],
            $config['password']
        );

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $company = $_POST['company'];

        // Update tenant
        $pdo->exec("UPDATE tenants SET name = " . $pdo->quote($company) . " WHERE id = 1");

        // Update admin user
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = 1");
        $stmt->execute([$username, $email, $password]);

        // Mark as installed
        file_put_contents(ROOT_DIR . '/.installed', date('Y-m-d H:i:s'));

        header('Location: ?step=5');
        exit;
    } catch (PDOException $e) {
        $error = "Failed to create admin: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installer v2.0</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .installer-card { max-width: 700px; margin: 50px auto; background: white; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 10px; position: relative; }
        .step.active { color: #667eea; font-weight: bold; }
        .step.completed { color: #28a745; }
        .progress-bar { background: #667eea; }
    </style>
</head>
<body>
    <div class="container">
        <div class="installer-card p-5">
            <h1 class="text-center mb-4">🌊 Nautilus Installer</h1>

            <!-- Progress Steps -->
            <div class="step-indicator">
                <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1. Requirements</div>
                <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2. Database</div>
                <div class="step <?= $step >= 3 ? 'active' : '' ?> <?= $step > 3 ? 'completed' : '' ?>">3. Install</div>
                <div class="step <?= $step >= 4 ? 'active' : '' ?> <?= $step > 4 ? 'completed' : '' ?>">4. Admin</div>
                <div class="step <?= $step >= 5 ? 'active' : '' ?>">5. Done</div>
            </div>

            <div class="progress mb-4" style="height: 5px;">
                <div class="progress-bar" style="width: <?= ($step / 5) * 100 ?>%"></div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <h3>System Requirements</h3>
                <table class="table">
                    <?php foreach ($checks as $name => $passed): ?>
                        <tr>
                            <td><?= $name ?></td>
                            <td><?= $passed ? '<span class="text-success">✓ Pass</span>' : '<span class="text-danger">✗ Fail</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php if ($all_passed): ?>
                    <a href="?step=2" class="btn btn-primary btn-lg w-100">Continue →</a>
                <?php else: ?>
                    <div class="alert alert-warning">Please fix the failed requirements before continuing.</div>
                <?php endif; ?>

            <?php elseif ($step == 2): ?>
                <h3>Database Configuration</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label>Database Host</label>
                        <input type="text" name="db_host" class="form-control"
                               value="<?= gethostbyname('database') !== 'database' ? 'database' : 'localhost' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Port</label>
                        <input type="number" name="db_port" class="form-control" value="3306" required>
                    </div>
                    <div class="mb-3">
                        <label>Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="nautilus" required>
                    </div>
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="db_user" class="form-control" value="root" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">Test Connection & Continue →</button>
                </form>

            <?php elseif ($step == 3): ?>
                <h3>Install Database</h3>
                <p>Ready to install the Nautilus database. This will process 107 migration files and may take 30-60 seconds.</p>
                <form method="POST">
                    <button type="submit" class="btn btn-success btn-lg w-100">🚀 Install Database</button>
                </form>

            <?php elseif ($step == 4): ?>
                <h3>Create Administrator Account</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label>Company Name</label>
                        <input type="text" name="company" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Admin Username</label>
                        <input type="text" name="username" class="form-control" value="admin" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg w-100">Create Account & Finish</button>
                </form>

            <?php elseif ($step == 5): ?>
                <div class="text-center">
                    <h2 class="text-success mb-4">🎉 Installation Complete!</h2>
                    <p class="lead">Nautilus has been successfully installed.</p>
                    <a href="/admin/login.php" class="btn btn-primary btn-lg">Login to Dashboard →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
