<?php
/**
 * Bootstrap Migrations
 * Creates the migrations table directly to resolve the chicken-and-egg problem.
 */

$host = '127.0.0.1'; // Force TCP
$db   = 'nautilus';
$user = 'nautilus';
$passwords = ['nautilus123', 'password'];

$pdo = null;
foreach ($passwords as $p) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $p);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected with password: " . substr($p, 0, 3) . "***\n";
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$pdo) {
    die("Connection Failed: All passwords failed.");
}

// 1. Create Migrations Table
$sql = "CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$pdo->exec($sql);
echo "Migrations table created (or already exists).\n";

// 2. Mark early migrations as complete if they likely ran
// We know 000_CORE_SCHEMA ran because we have tables.
$migrated = [
    '000_CORE_SCHEMA.sql',
    '001_create_migrations_table.sql'
];

foreach ($migrated as $file) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO migrations (filename, status, executed_at) VALUES (?, 'completed', NOW())");
    $stmt->execute([$file]);
    echo "Marked $file as completed.\n";
}

echo "Bootstrap Complete.\n";
