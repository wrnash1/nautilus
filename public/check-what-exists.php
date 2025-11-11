<?php
/**
 * Check What Tables Actually Exist
 * Run this after installation to see what was created
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

    echo "=== DATABASE ANALYSIS ===\n\n";
    echo "Database: $database\n\n";

    // Get all tables
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    sort($allTables);

    echo "Total tables: " . count($allTables) . "\n\n";

    // Critical tables that MUST exist
    $criticalTables = [
        'tenants' => 'migration 000',
        'roles' => 'migration 000',
        'permissions' => 'migration 001',
        'role_permissions' => 'migration 001',
        'users' => 'migration 001',
        'customers' => 'migration 002',
        'products' => 'migration 003',
        'categories' => 'migration 003',
        'transactions' => 'migration 004',
    ];

    echo "=== CRITICAL TABLES ===\n";
    $missingCritical = [];
    foreach ($criticalTables as $table => $source) {
        $exists = in_array($table, $allTables);
        echo ($exists ? '✓' : '✗') . " $table ($source)\n";
        if (!$exists) {
            $missingCritical[] = $table;
        }
    }

    if (!empty($missingCritical)) {
        echo "\n⚠ WARNING: Missing " . count($missingCritical) . " critical tables!\n";
        echo "This will cause cascading failures in other migrations.\n\n";
    }

    // Show all tables
    echo "\n=== ALL TABLES (" . count($allTables) . ") ===\n";
    foreach ($allTables as $table) {
        // Get row count
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
            echo "- $table ($count rows)\n";
        } catch (Exception $e) {
            echo "- $table (error counting)\n";
        }
    }

    // Check migrations table
    echo "\n=== MIGRATIONS ===\n";
    if (in_array('migrations', $allTables)) {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM migrations");
        $migCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "Total migrations recorded: $migCount\n";

        $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY id LIMIT 10");
        $firstTen = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "\nFirst 10 migrations:\n";
        foreach ($firstTen as $mig) {
            echo "  - $mig\n";
        }
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
