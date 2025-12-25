<?php
// QA Schema Check with Logging

$logFile = __DIR__ . '/qa_schema.log';
file_put_contents($logFile, "Starting Schema Check...\n");

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, $msg, FILE_APPEND);
}

// 1. Get Configuration from .env or ENV vars
$envPath = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}

$dbHost = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'database');
$dbPort = getenv('DB_PORT') ?: ($env['DB_PORT'] ?? '3306');
$dbName = getenv('DB_DATABASE') ?: ($env['DB_DATABASE'] ?? 'nautilus');
$dbUser = getenv('DB_USERNAME') ?: ($env['DB_USERNAME'] ?? 'nautilus');
$dbPass = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? 'nautilus123');

try {
    // Try external first
    $dsn = "mysql:host=127.0.0.1;port=3307;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
} catch (PDOException $e) {
    // Try internal/standard
    try {
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";
        $pdo = new PDO($dsn, $dbUser, $dbPass);
    } catch (PDOException $e2) {
        logMsg("Connection failed: " . $e2->getMessage() . "\n");
        exit;
    }
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

logMsg("Connected successfully.\n");

logMsg("Schema for 'roles' table:\n");
try {
    $stmt = $pdo->query("DESCRIBE roles");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        logMsg($col['Field'] . " (" . $col['Type'] . ")\n");
    }
} catch (Exception $e) {
    logMsg("Error describing roles: " . $e->getMessage() . "\n");
}

logMsg("\nCheck if any roles exist:\n");
try {
    $stmt = $pdo->query("SELECT * FROM roles LIMIT 5");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($roles)) {
        logMsg("No roles found.\n");
    } else {
        foreach ($roles as $r) {
            logMsg(print_r($r, true));
        }
    }
} catch (Exception $e) {
    logMsg("Error selecting roles: " . $e->getMessage() . "\n");
}
