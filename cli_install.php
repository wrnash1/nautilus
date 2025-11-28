<?php

$logFile = __DIR__ . '/install_log.txt';
file_put_contents($logFile, "Starting installation...\n");

function logMsg($msg) {
    global $logFile;
    echo $msg;
    file_put_contents($logFile, $msg, FILE_APPEND);
}

$host = 'localhost'; // Try localhost first
$port = '3306';
$username = 'root';
$password = ''; 
$database = 'nautilus';

// Try to read .env manually
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'DB_HOST=') === 0) $host = trim(substr($line, 8));
        if (strpos($line, 'DB_PORT=') === 0) $port = trim(substr($line, 8));
        if (strpos($line, 'DB_DATABASE=') === 0) $database = trim(substr($line, 12));
        if (strpos($line, 'DB_USERNAME=') === 0) $username = trim(substr($line, 12));
        if (strpos($line, 'DB_PASSWORD=') === 0) $password = trim(substr($line, 12));
    }
}

logMsg("Connecting to $host:$port as $username...\n");

try {
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    logMsg("Dropping database '$database' if exists...\n");
    $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    logMsg("Creating database '$database' if not exists...\n");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    $migrationsDir = __DIR__ . '/database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);
    
    logMsg("Found " . count($files) . " migration files.\n");
    
    foreach ($files as $file) {
        $filename = basename($file);
        logMsg("Running $filename... ");
        
        $sql = file_get_contents($file);
        
        try {
            // Split by semicolon and newline to handle multiple statements roughly
            // This is fragile but better than nothing for simple migrations
            $statements = array_filter(array_map('trim', explode(";\n", $sql)));
            
            // If explode didn't work well (e.g. one big line), try just executing the whole thing
            if (count($statements) <= 1) {
                 $stmt = $pdo->query($sql);
                 if ($stmt) $stmt->closeCursor();
            } else {
                foreach ($statements as $stmtSql) {
                    if (!empty($stmtSql)) {
                        $stmt = $pdo->query($stmtSql);
                        if ($stmt) $stmt->closeCursor();
                    }
                }
            }
            logMsg("OK\n");
        } catch (PDOException $e) {
            logMsg("FAILED\n");
            logMsg("Error: " . $e->getMessage() . "\n");
            // Stop on error to allow fixing
            exit(1);
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    logMsg("\nAll migrations completed successfully!\n");
    
} catch (PDOException $e) {
    logMsg("Database Connection Error: " . $e->getMessage() . "\n");
    exit(1);
}
