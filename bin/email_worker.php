#!/usr/bin/env php
<?php

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

use App\Core\Database;
use App\Services\Email\EmailQueueService;

// Ensure we're running in CLI
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

echo "[" . date('Y-m-d H:i:s') . "] Starting email worker...\n";

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();
    
    // Initialize service
    $emailQueueService = new EmailQueueService($db);
    
    // Process queue
    echo "Processing queue...\n";
    $stats = $emailQueueService->processQueue(50);
    
    echo "Worker finished:\n";
    echo "- Processed: {$stats['processed']}\n";
    echo "- Sent: {$stats['sent']}\n";
    echo "- Failed: {$stats['failed']}\n";
    
    if (!empty($stats['errors'])) {
        echo "- Errors encountered:\n";
        foreach ($stats['errors'] as $error) {
            echo "  * Email ID {$error['email_id']} ({$error['to']}): {$error['error']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
