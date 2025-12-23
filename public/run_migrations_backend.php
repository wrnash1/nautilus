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
// Check for stale session data (root user in Docker) and clear it
if (isset($_SESSION['install_data']['db_user']) && 
    $_SESSION['install_data']['db_user'] === 'root' && 
    gethostbyname('database') !== 'database') {
    unset($_SESSION['install_data']);
}

// Force fallback to environment variables if available (fixes Docker networking issues where session might be stale)
if (getenv("DB_HOST")) {
    $dbHost = getenv("DB_HOST");
    $_SESSION['install_data'] = [
        'db_host' => $dbHost,
        'db_name' => getenv("DB_DATABASE") ?: "nautilus",
        'db_user' => getenv("DB_USERNAME") ?: "nautilus",
        'db_pass' => getenv("DB_PASSWORD") ?: "nautilus123",
        'db_port' => getenv("DB_PORT") ?: "3306",
        'company' => getenv("APP_NAME") ?: "Nautilus",
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

$processed = 0;
foreach ($files as $file) {
    $name = basename($file);
    stream_msg("START", $name);
    
    $sql = file_get_contents($file);
    
    // Split by semicolon, but be careful (basic split for now, robust enough for our dumps usually)
    // Actually, running the whole file is better if it doesn't contain delimiters that PDO dislikes.
    // PDO can run multiple statements if emulation is on (default).
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        // If it's "table already exists", ignore it?
        // Ideally we check migration table, but for fresh install we might just proceed.
        if (strpos($e->getMessage(), "already exists") === false) {
             stream_msg("ERROR", "Migration $name failed: " . $e->getMessage());
             exit;
        }
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

// 6. Mark as Installed
file_put_contents(dirname(__DIR__) . '/.installed', date('Y-m-d H:i:s'));

stream_msg("COMPLETE", "Installation finished successfully.");
?>
