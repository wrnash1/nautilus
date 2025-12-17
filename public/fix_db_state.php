<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$dotEnv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotEnv->load();

// Override for root
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'Frogman09!';

try {
    // Explicit connection
    $dsn = "mysql:host=nautilus-db;port=3306;charset=utf8mb4";
    $username = "root";
    $password = "Frogman09!";
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("DROP DATABASE IF EXISTS nautilus");
    $pdo->exec("CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database reset successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
