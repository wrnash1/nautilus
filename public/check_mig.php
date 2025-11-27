<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'];

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Connected to database: $db\n";
    
    $stmt = $pdo->query("SELECT * FROM migrations");
    $migrations = $stmt->fetchAll();
    
    echo "Found " . count($migrations) . " migrations:\n";
    foreach ($migrations as $m) {
        echo " - " . $m['migration'] . " (Batch: " . $m['batch'] . ")\n";
    }
    
} catch (\PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
