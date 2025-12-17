<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
require_once BASE_PATH . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // 1. Update Diver Dave Password
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE email = 'diverdave@example.com'");
    $stmt->execute([$hash]);
    echo "PASSWORD UPDATED: " . ($stmt->rowCount() > 0 ? "YES" : "NO (User might not exist or same password)") . "\n";
    
    // 2. Check Transactions
    echo "\n--- RECENT TRANSACTIONS ---\n";
    $stmt = $conn->query("SELECT id, total, status, created_at FROM transactions ORDER BY created_at DESC LIMIT 5");
    $txns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($txns)) {
        echo "No transactions found.\n";
    } else {
        foreach ($txns as $t) {
            echo "ID: {$t['id']} | Total: {$t['total']} | Status: {$t['status']} | Date: {$t['created_at']}\n";
        }
    }
    
    // 3. Time Check
    echo "\n--- TIME DEBUG ---\n";
    $stmt = $conn->query("SELECT NOW() as db_now, CURDATE() as db_date, @@global.time_zone as tz");
    $time = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "DB NOW: {$time['db_now']}\n";
    echo "DB DATE: {$time['db_date']}\n";
    echo "DB TZ: {$time['tz']}\n";
    echo "PHP TIME: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
