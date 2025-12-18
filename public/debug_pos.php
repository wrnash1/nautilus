<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

// Verify Transaction Count for Dashboard
$sql = "SELECT DATE(created_at) as date, status, COUNT(*) as count, SUM(total) as revenue 
        FROM transactions 
        WHERE created_at >= CURDATE() 
        GROUP BY DATE(created_at), status";
$stats = Database::fetchAll($sql);

echo "--- Recent Transactions Stats ---\n";
print_r($stats);

// Check Settings
$settings = Database::fetchAll("SELECT * FROM system_settings WHERE setting_key LIKE '%bitcoin%' OR setting_key LIKE '%crypto%'");
echo "\n--- Bitcoin Settings ---\n";
print_r($settings);

// Check if 'transactions' table has recent logic
$recent = Database::fetchAll("SELECT id, created_at, status, transaction_type, total FROM transactions ORDER BY id DESC LIMIT 5");
echo "\n--- Last 5 Transactions ---\n";
print_r($recent);
