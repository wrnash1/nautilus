<?php
/**
 * Backend migration processor - streams progress updates
 * Modified to use PDO and dynamic paths
 */
session_start();
set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M');
header("Content-Type: text/plain");
ob_implicit_flush(true);

$rootDir = dirname(__DIR__);
$logCwd = is_dir($rootDir . '/storage/logs') ? $rootDir . '/storage/logs' : '/tmp';

function logMsg($msg) {
    global $logCwd;
    file_put_contents($logCwd . '/install_debug.log', $msg . "\n", FILE_APPEND);
}

logMsg("Backend started. Session ID: " . session_id());

// Configuration
$dbHost = 'nautilus-db';
$dbPort = getenv("DB_PORT") ?: "3306";
$dbName = 'nautilus';
$dbUser = 'root';
$dbPass = 'Frogman09!';

// Get files
$targetFile = $_GET['file'] ?? null;
if ($targetFile) {
    $targetFile = basename($targetFile);
    $files = glob($rootDir . "/database/migrations/" . $targetFile);
} else {
    $files = glob($rootDir . "/database/migrations/*.sql");
}

if ($files) {
    sort($files);
} else {
    $files = [];
    logMsg("No migration files found in " . $rootDir . "/database/migrations/");
}

$total = count($files);
echo "TOTAL:$total\n";
flush();

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Enable multi-statement
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
} catch (PDOException $e) {
    logMsg("Connection failed: " . $e->getMessage());
    echo "ERROR:Connection failed: " . $e->getMessage() . "\n";
    exit;
}

$processed = 0;

foreach ($files as $file) {
    $filename = basename($file);
    logMsg("Processing: $filename");
    echo "START:$filename\n";
    flush();

    $sql = file_get_contents($file);
    if (trim($sql) === '') {
        continue;
    }

    try {
        $pdo->exec($sql);
        $processed++;
        echo "PROGRESS:$processed\n";
        logMsg("Executed: $filename");
    } catch (PDOException $e) {
        $error = "Migration failed ($filename): " . $e->getMessage();
        logMsg($error);
        echo "ERROR:$error\n";
        exit;
    }
    flush();
}

echo "COMPLETE\n";
$_SESSION["db_installed"] = true;

// Create .installed file
file_put_contents($rootDir . '/.installed', date('Y-m-d H:i:s'));
logMsg("Installation marked complete.");
?>
