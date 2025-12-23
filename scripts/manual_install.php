<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Install\InstallService;
use Dotenv\Dotenv;

// Load environment if exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$service = new InstallService();

$config = [
    'app_name' => 'Nautilus',
    'app_url' => 'https://nautilus.local',
    'app_timezone' => 'America/Chicago',
    'db_host' => 'database',
    'db_port' => '3306',
    'db_database' => 'nautilus',
    'db_username' => 'root',
    'db_password' => 'Frogman09!',
    'admin_email' => 'admin@nautilus.local',
    'admin_password' => 'AdminNautilus123!',
    'admin_first_name' => 'Admin',
    'admin_last_name' => 'User',
    'install_demo_data' => true,
    'company_name' => 'Nautilus Dive Shop',
    'company_email' => 'admin@nautilus.local',
    'company_phone' => '555-0199',
    'company_address' => '123 Ocean Drive',
    'company_city' => 'Miami',
    'company_state' => 'FL',
    'company_zip' => '33101',
    'company_country' => 'US',
];

echo "Starting manual installation...\n";

try {
    $result = $service->runInstallation($config);
    
    if ($result['success']) {
        file_put_contents('install_log.txt', "Installation successful!\n" . print_r($result, true));
    } else {
        file_put_contents('install_log.txt', "Installation failed: " . $result['message'] . "\n");
    }
} catch (Exception $e) {
    file_put_contents('install_log.txt', "Exception: " . $e->getMessage() . "\n");
}
