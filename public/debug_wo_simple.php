<?php
/**
 * Work Orders Simple Test
 * This is a minimal version to debug the crash
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(__DIR__));

// Load app
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
}

require __DIR__ . '/../app/helpers.php';
\App\Core\Database::init();

// Start session
session_save_path(sys_get_temp_dir());
session_start();

// Simulate login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user'] = ['id' => 1, 'email' => 'admin@admin.com', 'role' => 'admin'];
}

// Test permission
echo "<h2>Permission Check</h2>";
echo "hasPermission('workorders.view'): ";
var_dump(hasPermission('workorders.view'));

// Get work orders
echo "<h2>Work Orders</h2>";
$service = new \App\Services\WorkOrders\WorkOrderService();
$workOrders = $service->getWorkOrderList([]);
echo "Count: " . count($workOrders) . "<br>";

// Render simple table
echo "<table border='1'>";
echo "<tr><th>ID</th><th>WO#</th><th>Status</th></tr>";
foreach ($workOrders as $wo) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($wo['id'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($wo['work_order_number'] ?? 'N/A') . "</td>";
    echo "<td>" . htmlspecialchars($wo['status'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Now testing admin layout include...</h2>";

// The real test - load admin layout
$pageTitle = 'Work Orders Test';
$activeMenu = 'workorders';
$user = $_SESSION['user'] ?? [];
$content = "<p>This is a test content block</p>";

echo "About to require admin.php layout...<br>";
flush();
ob_flush();

require BASE_PATH . '/app/Views/layouts/admin.php';

echo "SUCCESS - Layout loaded!";
