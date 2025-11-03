<?php
/**
 * Simple Direct Installer
 * Bypasses routing system - works even if routes are broken
 * Visit: https://yourdomain.com/simple-install.php
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if already installed by looking for a specific table with data
function isAlreadyInstalled($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        return $count > 0; // If users exist, it's installed
    } catch (Exception $e) {
        return false; // Table doesn't exist = not installed
    }
}

// Load dependencies
require __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../.env')) {
    die('ERROR: .env file not found. Copy .env.example to .env and configure it.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('ERROR: Database connection failed: ' . $e->getMessage());
}

// Check if already installed
if (isAlreadyInstalled($pdo)) {
    echo '<h1>Already Installed</h1>';
    echo '<p>Nautilus is already installed on this system.</p>';
    echo '<p><a href="/store/login">Go to Login</a></p>';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {

    // Validate passwords match
    if ($_POST['admin_password'] !== $_POST['admin_password_confirm']) {
        $error = "Passwords do not match. Please try again.";
    } elseif (strlen($_POST['admin_password']) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        echo '<!DOCTYPE html><html><head><title>Installing...</title><style>
            body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}
            .progress{background:#f0f0f0;padding:20px;border-radius:5px;margin:20px 0;}
            .step{padding:10px;margin:5px 0;}
            .success{color:green;} .error{color:red;} .info{color:blue;}
        </style></head><body>';

        echo '<h1>Installing Nautilus...</h1><div class="progress">';

        // Load install service
        require __DIR__ . '/../app/Services/Install/InstallService.php';

        $config = [
        'app_name' => $_POST['app_name'] ?? 'Nautilus',
        'app_url' => $_POST['app_url'] ?? $_ENV['APP_URL'],
        'app_timezone' => $_POST['timezone'] ?? 'America/Chicago',
        'db_host' => $_ENV['DB_HOST'],
        'db_port' => $_ENV['DB_PORT'] ?? 3306,
        'db_database' => $_ENV['DB_DATABASE'],
        'db_username' => $_ENV['DB_USERNAME'],
        'db_password' => $_ENV['DB_PASSWORD'],
        'admin_email' => $_POST['admin_email'],
        'admin_password' => $_POST['admin_password'],
        'admin_first_name' => $_POST['admin_first_name'],
        'admin_last_name' => $_POST['admin_last_name'],
        'install_demo_data' => false,
    ];

    $service = new App\Services\Install\InstallService();

    echo '<div class="step info">Starting installation...</div>';
    flush();

    $result = $service->runInstallation($config);

    if ($result['success']) {
        echo '<div class="step success">✓ Installation completed successfully!</div>';
        echo '</div>';
        echo '<h2>Success!</h2>';
        echo '<p>Your Nautilus system is now installed and ready to use.</p>';
        echo '<p><strong>Admin Email:</strong> ' . htmlspecialchars($config['admin_email']) . '</p>';
        echo '<p><a href="/store/login" style="display:inline-block;padding:10px 20px;background:#28a745;color:white;text-decoration:none;border-radius:5px;">Go to Login</a></p>';
        echo '<hr><p><small>Remember to delete this file: public/simple-install.php</small></p>';
    } else {
        echo '<div class="step error">✗ Installation failed: ' . htmlspecialchars($result['message'] ?? 'Unknown error') . '</div>';
        echo '</div>';
        echo '<p><a href="/simple-install.php">Try Again</a></p>';
    }

        echo '</body></html>';
        exit;
    }
}

// Show installation form
$error = $error ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nautilus Installation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #333; }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .required { color: red; }
        button {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover { background: #5568d3; }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .db-info {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .db-info strong { display: inline-block; width: 120px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌊 Nautilus Installation</h1>
        <p class="subtitle">Simple Direct Installer</p>

        <?php if (isset($error)): ?>
        <div style="background:#f8d7da;border-left:4px solid #dc3545;padding:15px;margin-bottom:20px;border-radius:4px;color:#721c24;">
            <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>ℹ️ Database Connection</strong><br>
            Using configuration from .env file
        </div>

        <div class="db-info">
            <strong>Database:</strong> <?= htmlspecialchars($_ENV['DB_DATABASE']) ?><br>
            <strong>Host:</strong> <?= htmlspecialchars($_ENV['DB_HOST']) ?><br>
            <strong>Username:</strong> <?= htmlspecialchars($_ENV['DB_USERNAME']) ?>
        </div>

        <form method="POST">

            <h3 style="margin: 30px 0 15px 0; color: #333;">Company Information</h3>

            <div class="form-group">
                <label>Business Name <span class="required">*</span></label>
                <input type="text" name="app_name" required placeholder="Your Dive Shop Name">
            </div>

            <div class="form-group">
                <label>Application URL <span class="required">*</span></label>
                <input type="url" name="app_url" required value="<?= htmlspecialchars($_ENV['APP_URL'] ?? 'https://') ?>" placeholder="https://yourdomain.com">
            </div>

            <div class="form-group">
                <label>Timezone <span class="required">*</span></label>
                <select name="timezone" required>
                    <option value="America/New_York">Eastern (America/New_York)</option>
                    <option value="America/Chicago">Central (America/Chicago)</option>
                    <option value="America/Denver">Mountain (America/Denver)</option>
                    <option value="America/Los_Angeles">Pacific (America/Los_Angeles)</option>
                    <option value="America/Phoenix">Arizona (America/Phoenix)</option>
                    <option value="Pacific/Honolulu">Hawaii (Pacific/Honolulu)</option>
                    <option value="America/Anchorage">Alaska (America/Anchorage)</option>
                    <option value="UTC">UTC</option>
                </select>
            </div>

            <h3 style="margin: 30px 0 15px 0; color: #333;">Administrator Account</h3>

            <div class="form-group">
                <label>First Name <span class="required">*</span></label>
                <input type="text" name="admin_first_name" required placeholder="John">
            </div>

            <div class="form-group">
                <label>Last Name <span class="required">*</span></label>
                <input type="text" name="admin_last_name" required placeholder="Doe">
            </div>

            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" name="admin_email" required placeholder="admin@yourdomain.com">
            </div>

            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="admin_password" required minlength="8" placeholder="Minimum 8 characters" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label>Confirm Password <span class="required">*</span></label>
                <input type="password" name="admin_password_confirm" required minlength="8" placeholder="Re-enter password" autocomplete="new-password" onpaste="return false;">
            </div>

            <button type="submit" name="install">Install Nautilus</button>

        </form>

        <p style="margin-top: 20px; text-align: center; color: #666; font-size: 13px;">
            This will run all migrations and seed default data
        </p>
    </div>
</body>
</html>
<?php
// Close connection
$pdo = null;
?>
