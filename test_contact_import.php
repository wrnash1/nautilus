<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Models/Customer.php';
require_once __DIR__ . '/app/Services/Import/CustomerImportService.php';

use App\Services\Import\CustomerImportService;
use App\Core\Database;

// Mock helper functions
if (!function_exists('currentUser')) {
    function currentUser() {
        return ['id' => 1];
    }
}

if (!function_exists('logActivity')) {
    function logActivity($action, $module, $id = null) {
        // echo "Logged activity: $action on $module ($id)\n";
    }
}

// Initialize Database
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_DATABASE'] = 'nautilus';
$_ENV['DB_USERNAME'] = 'root';
$_ENV['DB_PASSWORD'] = 'Frogman09!';

// File to import
$file = __DIR__ . '/contact_list_1755117222.csv';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

echo "Starting import test for: $file\n";

try {
    $service = new CustomerImportService();

    // 1. Auto-detect mapping
    echo "Detecting field mapping...\n";
    $mapping = $service->autoDetectFieldMapping($file);
    // echo "Mapping detected:\n";
    // print_r($mapping);

    // 2. Create Import Job
    echo "Creating import job...\n";
    $fileData = [
        'file_path' => $file,
        'file_size' => filesize($file),
        'original_name' => basename($file)
    ];
    $settings = [
        'job_name' => 'Test Contact Import',
        'update_existing' => 1,
        'skip_duplicates' => 0,
        'match_field' => 'email'
    ];

    $jobId = $service->createImportJob($fileData, $settings);
    echo "Job created with ID: $jobId\n";

    // 3. Save Mapping
    $service->saveFieldMapping($jobId, $mapping);

    // 4. Execute Import
    echo "Executing import...\n";
    $stats = $service->executeImportRefactored($jobId);
    echo "Import completed!\n";
    print_r($stats);

    // 5. Verify Data
    echo "Verifying data...\n";
    $count = Database::fetchOne("SELECT COUNT(*) as c FROM customers")['c'];
    echo "Total customers in DB: $count\n";

    $addrCount = Database::fetchOne("SELECT COUNT(*) as c FROM customer_addresses")['c'];
    echo "Total addresses in DB: $addrCount\n";

    $sample = Database::fetchOne("SELECT * FROM customers ORDER BY id DESC LIMIT 1");
    if ($sample) {
        echo "Sample customer:\n";
        print_r($sample);

        $address = Database::fetchOne("SELECT * FROM customer_addresses WHERE customer_id = ?", [$sample['id']]);
        echo "Sample address:\n";
        print_r($address);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
