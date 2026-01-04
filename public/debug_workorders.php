<?php
/**
 * Debug Work Orders - Identify PHP Errors
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__DIR__));

echo "<h1>Work Orders Debug</h1>\n";
echo "<pre>\n";

try {
    // Load autoloader
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "✓ Autoloader loaded\n";

    // Load .env
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        echo "✓ .env loaded\n";
    } catch (\Exception $e) {
        echo "⚠ .env failed: " . $e->getMessage() . "\n";
    }

    // Load helpers
    require __DIR__ . '/../app/helpers.php';
    echo "✓ Helpers loaded\n";

    // Init database
    \App\Core\Database::init();
    echo "✓ Database initialized\n";

    // Start session
    session_save_path(sys_get_temp_dir());
    session_start();

    // Check user session
    echo "\nSession check:\n";
    echo "- User in session: " . (isset($_SESSION['user']) ? 'Yes' : 'No') . "\n";
    if (isset($_SESSION['user'])) {
        echo "- User email: " . ($_SESSION['user']['email'] ?? 'N/A') . "\n";
        echo "- User role: " . ($_SESSION['user']['role'] ?? 'N/A') . "\n";
    }

    // Check permission function
    echo "\nPermission check:\n";
    echo "- hasPermission function exists: " . (function_exists('hasPermission') ? 'Yes' : 'No') . "\n";
    if (function_exists('hasPermission')) {
        echo "- hasPermission('workorders.view'): ";
        var_dump(hasPermission('workorders.view'));
    }

    // Check work_orders table
    echo "\nDatabase check:\n";
    $sql = "SHOW TABLES LIKE 'work_orders'";
    $result = \App\Core\Database::fetchAll($sql);
    echo "- work_orders table exists: " . (!empty($result) ? 'Yes' : 'No') . "\n";

    if (!empty($result)) {
        $sql = "DESCRIBE work_orders";
        $cols = \App\Core\Database::fetchAll($sql);
        echo "- Columns: " . count($cols) . "\n";

        $sql = "SELECT COUNT(*) as cnt FROM work_orders";
        $count = \App\Core\Database::fetchOne($sql);
        echo "- Row count: " . ($count['cnt'] ?? 0) . "\n";
    } else {
        echo "\n⚠ WORK_ORDERS TABLE DOES NOT EXIST!\n";
        echo "This is the root cause of the ERR_FAILED error.\n";
    }

    // Test service
    echo "\nService test:\n";
    $service = new \App\Services\WorkOrders\WorkOrderService();
    echo "- WorkOrderService instantiated\n";
    $list = $service->getWorkOrderList([]);
    echo "- getWorkOrderList() returned " . count($list) . " items\n";

    echo "\n=== SUCCESS: All checks passed ===\n";

} catch (\Throwable $e) {
    echo "\n=== ERROR ===\n";
    echo "Exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>\n";
