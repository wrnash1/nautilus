<?php
ini_set('display_errors', 0);
ini_set('output_buffering', 0);
ini_set('implicit_flush', 1);
ini_set('implicit_flush', 1);
ob_implicit_flush(1);

session_save_path(sys_get_temp_dir());
session_start();

// Helper to stream output
function stream_msg($type, $msg)
{
    file_put_contents(__DIR__ . '/install_debug.log', date('Y-m-d H:i:s') . " [$type] $msg\n", FILE_APPEND);
    echo "$type:$msg\n";
    if (ob_get_level() > 0)
        ob_flush();
    flush();
}
// Start Logging
file_put_contents(__DIR__ . '/install_debug.log', "--- Migration Backend Started ---\nSession ID: " . session_id() . "\n");
if (isset($_SESSION['install_data'])) {
    file_put_contents(__DIR__ . '/install_debug.log', "Session Data Found: Yes\n", FILE_APPEND);
} else {
    file_put_contents(__DIR__ . '/install_debug.log', "Session Data Found: NO\n", FILE_APPEND);
}

// Check for Reset Flag
$doReset = isset($_GET['reset']) && $_GET['reset'] == '1';
if ($doReset) {
    stream_msg("INFO", "Reset mode active: Existing data will be cleared.");
}

// 1. Get Configuration
// Use Dotenv if available for robust parsing
$envVars = [];
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
            $envVars = $dotenv->safeLoad();
        } catch (Exception $e) {
            // Ignore error if env file invalid, we will try to proceed
        }
    }
}

// Fallback to manual parsing if needed or merge
// Manual parsing excluded for brevity as Dotenv is standard, but keeping getenv checks
$dbHost = getenv("DB_HOST") ?: ($_ENV['DB_HOST'] ?? 'localhost');
$dbName = getenv("DB_DATABASE") ?: ($_ENV['DB_DATABASE'] ?? 'nautilus');
$dbUser = getenv("DB_USERNAME") ?: ($_ENV['DB_USERNAME'] ?? 'nautilus');
$dbPass = getenv("DB_PASSWORD") ?: ($_ENV['DB_PASSWORD'] ?? 'nautilus123');
$dbPort = getenv("DB_PORT") ?: ($_ENV['DB_PORT'] ?? '3306');
$company = getenv("APP_NAME") ?: ($_ENV['APP_NAME'] ?? 'Nautilus');

// Check for POST JSON Input first (Most Reliable)
$postInput = file_get_contents('php://input');
$postData = json_decode($postInput, true);

if (!empty($postData) && isset($postData['db_host'])) {
    file_put_contents(__DIR__ . '/install_debug.log', "Config Source: POST JSON\n", FILE_APPEND);
    $data = $postData;
    $dbHost = $data['db_host'];
    $dbName = $data['db_name'];
    $dbUser = $data['db_user'];
    $dbPass = $data['db_pass'];
    $dbPort = $data['db_port'] ?? 3306;
} elseif (!$doReset && isset($_SESSION['install_data'])) {
    file_put_contents(__DIR__ . '/install_debug.log', "Config Source: SESSION\n", FILE_APPEND);
    $data = $_SESSION['install_data'];
    $dbHost = $data['db_host'];
    $dbName = $data['db_name'];
    $dbUser = $data['db_user'];
    $dbPass = $data['db_pass'];
    $dbPort = $data['db_port'];
} elseif (!$doReset && empty($_SESSION['install_data'])) {
    // If not resetting and no install data, we might be re-running migrations manually
    // Just proceed with Environment variables found above
    file_put_contents(__DIR__ . '/install_debug.log', "Config Source: ENV (Fallback)\n", FILE_APPEND);
}

// 2. Connect to Database
try {
    stream_msg("INFO", "Connecting to database at $dbHost:$dbPort...");
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort", $dbUser, $dbPass, [
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB if not exists (redundant but safe)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");

    // RESET LOGIC: Drop all tables if requested
    if ($doReset) {
        stream_msg("INFO", "Dropping existing tables...");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            // stream_msg("INFO", "Dropped $table"); 
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        stream_msg("INFO", "Tables dropped. Removing install flag.");

        $installedFile = dirname(__DIR__) . '/.installed';
        if (file_exists($installedFile)) {
            unlink($installedFile);
        }
    }

} catch (PDOException $e) {
    stream_msg("ERROR", "Database connection failed: " . $e->getMessage());
    exit;
}

// 3. Write .env file
$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath)) {
    $appUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $envContent = <<<EOT
APP_ENV=production
APP_DEBUG=false
APP_URL={$appUrl}

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOT;
    if (file_put_contents($envPath, $envContent) === false) {
        stream_msg("ERROR", "Failed to write .env file. Check permissions.");
        file_put_contents(__DIR__ . '/install_debug.log', "ERROR: Failed to write .env\n", FILE_APPEND);
    } else {
        stream_msg("INFO", "Created configuration file (Minimal .env).");
        file_put_contents(__DIR__ . '/install_debug.log', "SUCCESS: Wrote .env file\n", FILE_APPEND);
    }

    // seed Mail text to DB
    try {
        $pdo->exec("INSERT IGNORE INTO system_settings (category, setting_key, setting_value) VALUES 
            ('mail', 'mail_mailer', 'log'),
            ('mail', 'mail_host', 'localhost'),
            ('mail', 'mail_port', '1025'),
            ('mail', 'mail_username', ''),
            ('mail', 'mail_password', ''),
            ('mail', 'mail_encryption', 'null'),
            ('mail', 'mail_from_address', 'no-reply@localhost'),
            ('mail', 'mail_from_name', '{$data['company']}')
        ");
        stream_msg("INFO", "Seeded default Mail settings to DB.");
    } catch (Exception $e) {
        stream_msg("INFO", "Skipped seeding mail settings: " . $e->getMessage());
    }
} else {
    file_put_contents(__DIR__ . '/install_debug.log', "INFO: .env already exists, skipping write.\n", FILE_APPEND);
}

// 4. Run Migrations
$migrationDir = dirname(__DIR__) . '/database/migrations';
$files = glob($migrationDir . '/*.sql');
sort($files);

$total = count($files);
stream_msg("TOTAL", $total);

// Ensure migrations table exists first
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) UNIQUE NOT NULL,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        error_message TEXT,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {
    // If this fails, we can't track anything, but let's try to proceed carefully
    stream_msg("ERROR", "Could not ensure migrations table exists: " . $e->getMessage());
    exit;
}

$processed = 0;
foreach ($files as $file) {
    $name = basename($file);
    stream_msg("START", $name);

    // Check if already completed
    $stmt = $pdo->prepare("SELECT status FROM migrations WHERE filename = ?");
    $stmt->execute([$name]);
    $migration = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($migration && $migration['status'] === 'completed') {
        stream_msg("SKIP", "Already completed");
        $processed++;
        stream_msg("PROGRESS", $processed);
        continue;
    }

    $sql = file_get_contents($file);

    try {
        // Record attempt
        $stmt = $pdo->prepare("INSERT INTO migrations (filename, status) VALUES (?, 'pending') ON DUPLICATE KEY UPDATE status='pending'");
        $stmt->execute([$name]);

        // Execute SQL
        $pdo->exec($sql);

        // Mark complete
        $stmt = $pdo->prepare("UPDATE migrations SET status='completed', error_message=NULL WHERE filename=?");
        $stmt->execute([$name]);

    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();

        // Log failure
        try {
            $stmt = $pdo->prepare("UPDATE migrations SET status='failed', error_message=? WHERE filename=?");
            $stmt->execute([$errorMsg, $name]);
        } catch (Exception $logEx) {
            // Should not happen if DB is reachable
        }

        // Special handling for "table exists" if we want to be lenient, 
        // BUT strict mode is safer. Let's fail and let user retry/fix.
        // Identify if it's a "create table" error on a table that exists, maybe we mark it as done?
        // No, safer to stop.

        stream_msg("ERROR", "Migration $name failed: " . $errorMsg);
        exit;
    }

    $processed++;
    stream_msg("PROGRESS", $processed);
}

// 5. Create Admin User
$adminUser = $data['username'];
$adminEmail = $data['email'];
$adminPass = $data['password'];

try {
    // Check if user exists by username OR email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$adminUser, $adminEmail]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $userId = $existing['id'];
        // Update password and details for existing user
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, email = ?, username = ? WHERE id = ?");
        $stmt->execute([$adminPass, $adminEmail, $adminUser, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$adminUser, $adminEmail, $adminPass]);
        $userId = $pdo->lastInsertId();
    }

    // Assign Admin Role (Idempotent)
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1");
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($role) {
        // Remove existing roles to ensure clean state
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $role['id']]);
    }

} catch (PDOException $e) {
    stream_msg("ERROR", "Failed to create/update admin user: " . $e->getMessage());
    exit;
}

// 5a. Update System Settings (Business Name)
try {
    // Check if system_settings table exists (created by migrations)
    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($stmt->fetch()) {
        $companyName = $data['company'];

        // Update or Insert 'business_name'
        $stmt = $pdo->prepare("SELECT id FROM system_settings WHERE setting_key = 'business_name'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $update = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'business_name'");
            $update->execute([$companyName]);
        } else {
            $insert = $pdo->prepare("INSERT INTO system_settings (category, setting_key, setting_value) VALUES ('general', 'business_name', ?)");
            $insert->execute([$companyName]);
        }
        stream_msg("INFO", "Updated Business Name in Database Settings.");
    }
} catch (Exception $e) {
    stream_msg("INFO", "Skipping settings update: " . $e->getMessage());
}

// 5b. Create Test Users (Instructor, Customer)
try {
    $testUsers = [
        ['email' => 'instructor2@nautilus.local', 'user' => 'Jane', 'role' => 'Instructor'],
        ['email' => 'diver@nautilus.local', 'user' => 'Dave', 'role' => 'Customer']
    ];

    foreach ($testUsers as $tu) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$tu['email']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$tu['user'], $tu['email'], $adminPass]); // Use same password
            $userId = $pdo->lastInsertId();

            // Assign Role
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE naming_convention = ? OR name = ? LIMIT 1");
            $stmt->execute([$tu['role'], $tu['role']]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($role) {
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                $stmt->execute([$userId, $role['id']]);
                stream_msg("INFO", "Created test user: " . $tu['role']);
            }
        }
    }
} catch (Exception $e) {
    stream_msg("INFO", "Skipping test users: " . $e->getMessage());
}

stream_msg("COMPLETE", "Installation finished successfully.");
?>