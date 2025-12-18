<?php
$host = 'database';
$db   = 'nautilus';
$user = 'nautilus';
$pass = 'nautilus123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Time Check:\n";
    echo "PHP: " . date('Y-m-d H:i:s') . "\n";
    echo "DB:  " . $pdo->query("SELECT NOW()")->fetchColumn() . "\n";
    
    echo "\nLatest 5 Transactions:\n";
    $stmt = $pdo->query("SELECT id, total, status, created_at, transaction_type FROM transactions ORDER BY id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
