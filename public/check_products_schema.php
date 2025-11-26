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
    $stmt = $pdo->query("DESCRIBE products");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Columns in 'products' table:\n";
    foreach ($columns as $col) {
        echo "- $col\n";
    }
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
