<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/nautilus/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/nautilus');
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? 3306;
$database = $_ENV['DB_DATABASE'] ?? 'nautilus';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Checking cash_drawer_sessions table structure ===\n\n";

    $stmt = $pdo->query("DESCRIBE cash_drawer_sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns in cash_drawer_sessions table:\n";
    foreach ($columns as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }

    echo "\n=== Checking if table exists ===\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'cash_drawer_sessions'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "✓ Table exists\n";
    } else {
        echo "✗ Table does NOT exist\n";
    }

    echo "\n=== Checking migrations table for migration 041 ===\n";
    $stmt = $pdo->query("SELECT * FROM migrations WHERE migration LIKE '%041%'");
    $migration = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($migration) {
        echo "✓ Migration 041 was executed:\n";
        print_r($migration);
    } else {
        echo "✗ Migration 041 was NOT executed\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
