<?php
/**
 * Database Structure Checker
 * Diagnoses which tables were created and which failed
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

    echo "=== DATABASE STRUCTURE CHECK ===\n\n";
    echo "Database: $database\n\n";

    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Total tables created: " . count($tables) . "\n\n";

    // Check critical tables
    $criticalTables = [
        'tenants',
        'roles',
        'permissions',
        'role_permissions',
        'users',
        'customers',
        'products',
        'transactions',
        'settings',
    ];

    echo "=== CRITICAL TABLES STATUS ===\n\n";
    foreach ($criticalTables as $table) {
        $exists = in_array($table, $tables);
        echo ($exists ? '✓' : '✗') . " $table\n";

        if ($exists) {
            // Count rows
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            echo "  → $count rows\n";
        }
    }

    echo "\n=== ALL TABLES ===\n\n";
    sort($tables);
    foreach ($tables as $table) {
        echo "- $table\n";
    }

    // Check migrations
    echo "\n=== MIGRATIONS STATUS ===\n\n";
    if (in_array('migrations', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM migrations");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "Migrations executed: $count\n\n";

        $stmt = $pdo->query("SELECT migration, executed_at FROM migrations ORDER BY id DESC LIMIT 10");
        $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Last 10 migrations:\n";
        foreach ($recent as $mig) {
            echo "  - " . $mig['migration'] . " (" . $mig['executed_at'] . ")\n";
        }
    } else {
        echo "✗ Migrations table doesn't exist\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
