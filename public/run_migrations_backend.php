<?php
ini_set('display_errors', 0);
ini_set('output_buffering', 0);
ini_set('implicit_flush', 1);
ob_implicit_flush(1);

session_start();

// Helper to stream output
function stream_msg($type, $msg) {
    echo "$type:$msg\n";
    flush();
}

// 1. Get Configuration
// Helper to parse .env file
function parseEnv($path) {
    if (!file_exists($path)) return [];
    $env = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
    }
    return $env;
}

// Force fallback to environment variables if available 
// OR try to read from .env file directly (important for CLI usage)
$envPath = dirname(__DIR__) . '/.env';
$envVars = parseEnv($envPath);

// Merge real env vars with file env vars (real env takes precedence)
$dbHost = getenv("DB_HOST") ?: ($envVars['DB_HOST'] ?? null);

if ($dbHost) {
    $_SESSION['install_data'] = [
        'db_host' => $dbHost,
        'db_name' => getenv("DB_DATABASE") ?: ($envVars['DB_DATABASE'] ?? "nautilus"),
        'db_user' => getenv("DB_USERNAME") ?: ($envVars['DB_USERNAME'] ?? "nautilus"),
        'db_pass' => getenv("DB_PASSWORD") ?: ($envVars['DB_PASSWORD'] ?? "nautilus123"),
        'db_port' => getenv("DB_PORT") ?: ($envVars['DB_PORT'] ?? "3306"),
        'company' => getenv("APP_NAME") ?: ($envVars['APP_NAME'] ?? "Nautilus"),
        'username' => 'admin',
        'email' => 'admin@localhost',
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ];
}

if (empty($_SESSION['install_data'])) {
    stream_msg("ERROR", "No installation data found. Please restart.");
    exit;
}

$data = $_SESSION['install_data'];
$dbHost = $data['db_host'];
$dbName = $data['db_name'];
$dbUser = $data['db_user'];
$dbPass = $data['db_pass'];
$dbPort = $data['db_port'];

// 2. Connect to Database
try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Create DB if not exists (redundant but safe)
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
} catch (PDOException $e) {
    stream_msg("ERROR", "Database connection failed: " . $e->getMessage());
    exit;
}

// 3. Write .env file
$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath)) {
    $appUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $envContent = <<<EOT
APP_NAME="{$data['company']}"
APP_ENV=production
APP_DEBUG=false
APP_URL={$appUrl}

DB_CONNECTION=mysql
DB_HOST={$dbHost}
DB_PORT={$dbPort}
DB_DATABASE={$dbName}
DB_USERNAME={$dbUser}
DB_PASSWORD={$dbPass}

MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@localhost"
MAIL_FROM_NAME="\${APP_NAME}"

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOT;
    file_put_contents($envPath, $envContent);
    stream_msg("INFO", "Created configuration file.");
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
$adminPass = $data['password']; // Already hashed

// Get Role ID for Admin (assuming it's 1 or we need to find it)
// We need to ensure roles exist. Assuming 000_CORE_SCHEMA.sql creates them.
try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$adminUser, $adminEmail, $adminPass]);
        $userId = $pdo->lastInsertId();
        
        // Assign Admin Role (assuming role_id 1 is Admin, or look it up)
        // Let's look up 'Admin' or 'Administrator'
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name IN ('Admin', 'Administrator') LIMIT 1");
        $stmt->execute();
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($role) {
             $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
             $stmt->execute([$userId, $role['id']]);
        }
    }
} catch (PDOException $e) {
    stream_msg("ERROR", "Failed to create admin user: " . $e->getMessage());
    exit;
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

// 6. Mark as Installed
file_put_contents(dirname(__DIR__) . '/.installed', date('Y-m-d H:i:s'));

stream_msg("COMPLETE", "Installation finished successfully.");
?>
