<?php
$host = 'localhost';
$db   = 'nautilus';
$user = 'root';
$pass = 'Frogman09!';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables in database '$db':\n";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
