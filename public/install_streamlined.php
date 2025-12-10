<?php
/**
 * Nautilus Streamlined Installer
 * Auto-detects configuration and only asks for admin account
 */

session_start();
define('ROOT_DIR', dirname(__DIR__));

// Check if already installed - BOTH files must exist for complete installation
$envExists = file_exists(ROOT_DIR . '/.env');
$installedExists = file_exists(ROOT_DIR . '/.installed');

if ($envExists && $installedExists) {
    // Fully installed, redirect to homepage
    header('Location: /');
    exit;
}

// If partially installed (only one file exists), clean up and start fresh
if ($envExists || $installedExists) {
    @unlink(ROOT_DIR . '/.env');
    @unlink(ROOT_DIR . '/.installed');
}

$error = null;

// Auto-detect environment
$isDocker = gethostbyname('database') !== 'database';
$dbHost = $isDocker ? 'database' : 'localhost';
$dbPort = 3306;
$dbName = 'nautilus';
$dbUser = $isDocker ? 'nautilus' : 'root';
$dbPass = $isDocker ? 'nautilus123' : 'Frogman09!';

// Auto-check requirements
$requirements = [
    'PHP >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'PDO MySQL' => extension_loaded('pdo_mysql'),
    'Storage Writable' => is_writable(ROOT_DIR . '/storage') || @mkdir(ROOT_DIR . '/storage', 0775, true),
];

$allPassed = !in_array(false, $requirements, true);

// Auto-test database
$dbOk = false;
$dbError = null;
if ($allPassed) {
    try {
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort}", $dbUser, $dbPass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $dbOk = true;
    } catch (PDOException $e) {
        $dbError = $e->getMessage();
    }
}

// Handle installation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $allPassed && $dbOk) {
    $company = trim($_POST['company'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($company) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $_SESSION['install_data'] = [
            'company' => $company,
            'username' => $_POST['username'] ?? 'admin',
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'db_host' => $dbHost,
            'db_port' => $dbPort,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_pass' => $dbPass,
        ];
        header('Location: run_migrations.php?quick_install=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Install Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .card { max-width: 600px; margin: 0 auto; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body p-5">
                <h1 class="text-center mb-4">🌊 Install Nautilus</h1>

                <?php if (!$allPassed): ?>
                    <div class="alert alert-danger">Requirements not met. Please fix and refresh.</div>
                    <?php foreach ($requirements as $name => $pass): ?>
                        <div><?= $name ?>: <?= $pass ? '✓' : '✗' ?></div>
                    <?php endforeach; ?>

                <?php elseif (!$dbOk): ?>
                    <div class="alert alert-danger">Database connection failed: <?= htmlspecialchars($dbError) ?></div>
                    <p>Auto-detected: <?= $dbHost ?>:<?= $dbPort ?>/<?= $dbName ?></p>

                <?php else: ?>
                    <div class="alert alert-success">✓ System Ready (<?= $isDocker ? 'Docker' : 'Local' ?>)</div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <h5>Create Admin Account</h5>

                        <div class="mb-3">
                            <label>Company Name *</label>
                            <input type="text" name="company" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" value="admin">
                            </div>
                            <div class="col-6">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" minlength="8" required>
                            </div>
                        </div>

                        <div class="alert alert-info small mt-3">
                            Next: Database install (~30 sec) → Login
                        </div>

                        <button class="btn btn-success btn-lg w-100 mt-3">🚀 Install Now</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
