<?php
/**
 * Backend migration processor - streams progress updates
 * REFACTORED: Uses PDO and fixes installation completion logic
 */
session_start();
@ini_set('output_buffering', 'Off');
@ini_set('implicit_flush', 1);
@ini_set('zlib.output_compression', 0);
set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M');
header("Content-Type: text/plain");
header("X-Accel-Buffering: no"); // Disable nginx buffering
header("Cache-Control: no-cache, must-revalidate"); // Disable browser caching
ob_implicit_flush(true);

while (ob_get_level()) ob_end_clean(); // Clean any existing buffers

// Logging Helper
$rootDir = dirname(__DIR__);
$logCwd = is_dir($rootDir . '/storage/logs') ? $rootDir . '/storage/logs' : '/tmp';

function logMsg($msg) {
    global $logCwd;
    $date = date('Y-m-d H:i:s');
    file_put_contents($logCwd . '/install_debug.log', "[$date] $msg\n", FILE_APPEND);
}

logMsg("Backend started. Session ID: " . session_id());

// Check if this is a quick install from streamlined installer
$isQuickInstall = isset($_GET['quick_install']) || isset($_SESSION['install_data']);

if ($isQuickInstall && isset($_SESSION['install_data'])) {
    $config = $_SESSION['install_data'];
    logMsg("Loaded config from session.");
} else {
    // Fallback or Env config
    $config = [
        "db_host" => getenv("DB_HOST") ?: "nautilus-db",
        "db_port" => getenv("DB_PORT") ?: "3306",
        "db_name" => getenv("DB_DATABASE") ?: "nautilus",
        "db_user" => getenv("DB_USERNAME") ?: "root",
        "db_pass" => getenv("DB_PASSWORD") ?: "Frogman09!"
    ];
    logMsg("Loaded config from defaults/env.");
}

// Normalize config
if (!isset($config['db_host']) && isset($config['host'])) {
    $config['db_host'] = $config['host'];
    $config['db_port'] = $config['port'];
    $config['db_name'] = $config['database'];
    $config['db_user'] = $config['username'];
    $config['db_pass'] = $config['password'];
}

// Get Migration Files
$files = glob($rootDir . "/database/migrations/*.sql");
if ($files === false) {
    logMsg("Glob failed for: " . $rootDir . "/database/migrations/*.sql");
    echo "ERROR:Glob failed\n";
    exit;
}
sort($files);

$total = count($files);
echo "TOTAL:$total\n";
flush();

// Connect to DB via PDO
try {
    $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Important for multiple statements in one file
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); 
} catch (PDOException $e) {
    logMsg("Connection failed: " . $e->getMessage());
    echo "ERROR:Connection failed: " . $e->getMessage() . "\n";
    exit;
}

$processed = 0;

foreach ($files as $file) {
    $filename = basename($file);
    
    // Friendly Name Logic
    $friendlyName = ucwords(str_replace(['_', '.sql'], [' ', ''], substr($filename, 4)));
    echo "START:$friendlyName\n";
    flush();

    $sql = file_get_contents($file);
    if (trim($sql) === '') {
        logMsg("Skipping empty file: $filename");
        continue;
    }

    try {
        $pdo->exec($sql);
        $processed++;
        echo "PROGRESS:$processed\n";
        flush();
        logMsg("Executed: $filename");
    } catch (PDOException $e) {
        $error = "Migration failed ($filename): " . $e->getMessage();
        logMsg($error);
        echo "ERROR:$error\n";
        exit;
    }
    
    usleep(50000); // Visual delay
}

echo "COMPLETE\n";
$_SESSION["db_installed"] = true;

// Finalize Installation (Create .env and .installed)
if ($isQuickInstall) {
    logMsg("Finalizing Quick Install...");
    
    // 1. Create .env
    $envContent = "APP_NAME=\"Nautilus Dive Shop\"\n";
    $envContent .= "APP_ENV=development\n";
    $envContent .= "APP_DEBUG=true\n";
    $envContent .= "APP_URL=http://localhost:8080\n";
    $envContent .= "APP_TIMEZONE=America/New_York\n\n";
    $envContent .= "DB_HOST={$config['db_host']}\n";
    $envContent .= "DB_PORT={$config['db_port']}\n";
    $envContent .= "DB_DATABASE={$config['db_name']}\n";
    $envContent .= "DB_USERNAME={$config['db_user']}\n";
    $envContent .= "DB_PASSWORD={$config['db_pass']}\n";

    if (file_put_contents($rootDir . '/.env', $envContent) !== false) {
        logMsg("Created .env file.");
    } else {
        logMsg("Failed to create .env file!");
    }

    // 2. Update Admin Account
    try {
        // Update tenant
        $stmt = $pdo->prepare("UPDATE tenants SET name = ? WHERE id = 1");
        $stmt->execute([$config['company'] ?? 'Nautilus']);

        // Update admin user
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password_hash = ? WHERE id = 1");
        $stmt->execute([
            $config['username'] ?? 'admin',
            $config['email'] ?? 'admin@nautilus.local',
            $config['password'] ?? password_hash('password', PASSWORD_DEFAULT)
        ]);
        logMsg("Updated admin account.");
    } catch (PDOException $e) {
        logMsg("Failed to update admin/tenant: " . $e->getMessage());
    }

    // 3. Create .installed file
    if (file_put_contents($rootDir . '/.installed', date('Y-m-d H:i:s')) !== false) {
        logMsg("Created .installed file. Setup Complete.");
    } else {
        logMsg("Failed to create .installed file!");
    }
}
?>
