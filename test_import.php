<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
// require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/Models/Product.php';
require_once __DIR__ . '/app/Services/Inventory/ProductService.php';
require_once __DIR__ . '/app/Services/Import/ProductImportService.php';

// Mock helper functions
if (!function_exists('currentUser')) {
    function currentUser() {
        return ['id' => 1];
    }
}

if (!function_exists('logActivity')) {
    function logActivity($action, $module, $id = null) {
        echo "Logged activity: $action on $module ($id)\n";
    }
}

use App\Services\Import\ProductImportService;
use App\Core\Database;

// Initialize Database
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DATABASE'] = 'nautilus';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'Frogman09!';

try {
    echo "Starting test...\n";
    $importService = new ProductImportService();
    $filePath = __DIR__ . '/inventory_sample.csv';
    
    if (!file_exists($filePath)) {
        throw new Exception("Sample CSV not found at $filePath");
    }
    
    echo "Reading headers...\n";
    $headers = $importService->parseCSVHeaders($filePath);
    
    echo "Detecting mapping...\n";
    $mapping = $importService->autoDetectFieldMapping($headers);
    print_r($mapping);
    
    echo "Creating import job...\n";
    $jobId = $importService->createImportJob(
        [
            'file_path' => $filePath, // Changed from $csvFile to $filePath to match existing variable
            'file_size' => filesize($filePath), // Changed from $csvFile to $filePath to match existing variable
            'import_type' => 'csv'
        ],
        [
            'job_name' => 'Test Import',
            'match_field' => 'sku',
            'update_existing' => true,
            'skip_duplicates' => false,
            'auto_create_categories' => true,
            'auto_create_vendors' => true
        ]
    );
    echo "Job ID: $jobId\n"; // This line was present in the original code and should remain.
    
    echo "Saving mapping...\n";
    $importService->saveFieldMapping($jobId, $mapping);
    
    echo "Executing import...\n";
    $results = $importService->executeImport($jobId);
    print_r($results);
    
    echo "Verifying database...\n";
    $products = Database::fetchAll("SELECT * FROM products ORDER BY id DESC LIMIT 5");
    foreach ($products as $product) {
        echo "Product: {$product['name']} (SKU: {$product['sku']})\n";
        echo "Model: {$product['model']}\n";
        echo "Attributes: {$product['attributes']}\n";
        echo "-------------------\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
