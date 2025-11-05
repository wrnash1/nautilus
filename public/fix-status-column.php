<?php
/**
 * Fix missing status column in cash_drawer_sessions table
 * This script adds the status column if it's missing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? 3306;
$database = $_ENV['DB_DATABASE'] ?? 'nautilus';
$username = $_ENV['DB_USERNAME'] ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== Fixing cash_drawer_sessions table ===\n\n";

    // Check if status column exists
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '$database'
          AND TABLE_NAME = 'cash_drawer_sessions'
          AND COLUMN_NAME = 'status'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $statusExists = $result['count'] > 0;

    if ($statusExists) {
        echo "✓ Status column already exists\n";
    } else {
        echo "✗ Status column is missing - adding it now...\n";

        // Add status column
        $pdo->exec("
            ALTER TABLE cash_drawer_sessions
            ADD COLUMN status ENUM('open', 'closed', 'balanced', 'over', 'short') DEFAULT 'open'
        ");

        echo "✓ Status column added successfully\n";
    }

    // Check if index exists
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = '$database'
          AND TABLE_NAME = 'cash_drawer_sessions'
          AND INDEX_NAME = 'idx_status'
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $indexExists = $result['count'] > 0;

    if (!$indexExists) {
        echo "Adding index on status column...\n";
        $pdo->exec("
            ALTER TABLE cash_drawer_sessions
            ADD INDEX idx_status (status)
        ");
        echo "✓ Index added successfully\n";
    } else {
        echo "✓ Index already exists\n";
    }

    // Verify fix
    echo "\n=== Verification ===\n";
    $stmt = $pdo->query("
        SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '$database'
          AND TABLE_NAME = 'cash_drawer_sessions'
          AND COLUMN_NAME = 'status'
    ");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($column) {
        echo "✓ Status column verified:\n";
        echo "  Name: " . $column['COLUMN_NAME'] . "\n";
        echo "  Type: " . $column['COLUMN_TYPE'] . "\n";
        echo "  Default: " . ($column['COLUMN_DEFAULT'] ?? 'NULL') . "\n";
        echo "\n✓ Fix completed successfully!\n";
        echo "\nYou can now refresh the dashboard and it should work.\n";
    } else {
        echo "✗ Status column still missing - please check database manually\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
