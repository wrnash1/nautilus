<?php

// require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';

// Initialize Database
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DATABASE'] = 'nautilus';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'Frogman09!';

try {
    echo "Testing Database...\n";
    $db = \App\Core\Database::getInstance();
    echo "Database instance created.\n";
    $conn = $db->getConnection();
    echo "Connection established.\n";
    
    require_once __DIR__ . '/app/Models/Product.php';
    echo "Product model included.\n";
    
    // Mock helper functions needed by ProductService
    if (!function_exists('logActivity')) {
        function logActivity($action, $module, $id = null) {
            echo "Logged activity: $action on $module ($id)\n";
        }
    }
    
    require_once __DIR__ . '/app/Services/Inventory/ProductService.php';
    echo "ProductService included.\n";
    $productService = new \App\Services\Inventory\ProductService();
    echo "ProductService instantiated.\n";
    
    // Mock helper functions needed by ProductImportService
    if (!function_exists('currentUser')) {
        function currentUser() {
            return ['id' => 1];
        }
    }
    
    require_once __DIR__ . '/app/Services/Import/ProductImportService.php';
    echo "ProductImportService included.\n";
    $importService = new \App\Services\Import\ProductImportService();
    echo "ProductImportService instantiated.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
