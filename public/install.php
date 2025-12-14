<?php
/**
 * Nautilus Streamlined Installer
 * Auto-detects configuration and only asks for admin account
 */

session_start();
define('ROOT_DIR', dirname(__DIR__));

// Check if already installed - BOTH files must exist and be non-empty for complete installation
$envExists = file_exists(ROOT_DIR . '/.env');
$installedExists = file_exists(ROOT_DIR . '/.installed');

// Check if valid install (files exist and have content)
$isInstalled = $envExists && $installedExists && filesize(ROOT_DIR . '/.installed') > 0;

if ($isInstalled) {
    // Fully installed, redirect to homepage
    header('Location: /');
    exit;
}

// Ensure files are writable if they exist
if ($envExists && !is_writable(ROOT_DIR . '/.env')) {
    $error = ".env file is not writable. Please run: chown www-data:www-data .env && chmod 664 .env";
}
if ($installedExists && !is_writable(ROOT_DIR . '/.installed')) {
    $error = ".installed file is not writable. Please run: chown www-data:www-data .installed && chmod 664 .installed";
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents('/tmp/install_post_debug.log', "POST received. allPassed: " . ($allPassed?1:0) . ", dbOk: " . ($dbOk?1:0) . "\n", FILE_APPEND);
    
    if ($allPassed && $dbOk) {
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
        session_write_close(); // Ensure session is saved before redirect
        header('Location: run_migrations.php?quick_install=1');
        exit;
    }
}
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Install Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; overflow-x: hidden; font-family: 'Inter', sans-serif; height: 100vh; }
        .installer-container { display: flex; height: 100vh; width: 100vw; }
        .side-panel { flex: 1; position: relative; overflow: hidden; display: none; }
        .side-panel img { width: 100%; height: 100%; object-fit: cover; }
        .main-panel { flex: 0 0 100%; padding: 40px; display: flex; flex-direction: column; justify-content: center; background: white; z-index: 10; overflow-y: auto; }
        
        @media (min-width: 992px) {
            .side-panel { display: block; flex: 0 0 25%; }
            .main-panel { flex: 0 0 50%; box-shadow: 0 0 50px rgba(0,0,0,0.1); }
        }

        h1 { font-family: 'Cinzel', serif; color: #1a365d; font-weight: 600; margin-bottom: 30px; }
        .form-label { font-weight: 600; color: #4a5568; }
        .form-control { border-radius: 8px; padding: 12px; border: 1px solid #e2e8f0; }
        .form-control:focus { border-color: #3182ce; box-shadow: 0 0 0 3px rgba(49,130,206,0.1); }
        .btn-primary { background: #3182ce; border: none; padding: 14px; border-radius: 8px; font-weight: 600; transition: all 0.2s; }
        .btn-primary:hover { background: #2c5282; transform: translateY(-1px); }
        .status-dot { height: 10px; width: 10px; background-color: #48bb78; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.5)); }
    </style>
</head>
<body>
    <div class="installer-container">
        <!-- Left Panel: Poseidon -->
        <div class="side-panel">
            <img src="/images/poseidon.png" alt="Poseidon">
            <div class="overlay"></div>
        </div>

        <!-- Center Panel: Form -->
        <div class="main-panel">
            <div style="max-width: 500px; margin: 0 auto; width: 100%;">
                <div class="text-center mb-5">
                    <h1 class="display-5">NAUTILUS</h1>
                    <p class="text-muted">Premium Dive Shop Management</p>
                </div>

                <?php if (!$allPassed): ?>
                    <div class="alert alert-danger shadow-sm border-0">
                        <h5 class="alert-heading mb-3">System Requirements</h5>
                        <?php foreach ($requirements as $name => $pass): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= $name ?></span>
                                <span><?= $pass ? '✅' : '❌' ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php elseif (!$dbOk): ?>
                    <div class="alert alert-danger shadow-sm border-0">
                        <h5 class="alert-heading">Connection Failed</h5>
                        <p class="mb-0">Could not connect to database at <strong><?= $dbHost ?></strong>.</p>
                        <hr>
                        <small class="text-muted"><?= htmlspecialchars($dbError) ?></small>
                    </div>

                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger shadow-sm border-0 mb-4"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company" class="form-control" placeholder="e.g. Scuba World" required
                                   value="<?= htmlspecialchars($_POST['company'] ?? '') ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Administrator Email</label>
                            <input type="email" name="email" class="form-control" placeholder="admin@example.com" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="admin">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Min. 8 chars" minlength="8" required>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-4 text-muted small">
                            <span class="status-dot"></span> System Ready (<?= $isDocker ? 'Docker' : 'Local' ?>)
                        </div>

                        <button class="btn btn-primary w-100 shadow-sm">Initialize System</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel: Sealife -->
        <div class="side-panel">
            <img src="/images/sealife.png" alt="Sealife">
            <div class="overlay"></div>
        </div>
    </div>
</body>
</html>
