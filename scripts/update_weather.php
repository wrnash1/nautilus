#!/usr/bin/env php
<?php
/**
 * Update Weather Data for All Dive Sites
 * Run this via cron for automatic weather updates
 *
 * Example cron (every 6 hours):
 * 0 *\/6 * * * cd /path/to/nautilus-v6 && php scripts/update_weather.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define BASE_PATH constant
define('BASE_PATH', dirname(__DIR__));

use App\Services\DiveSites\WeatherService;

try {
    $weatherService = new WeatherService();

    echo "Starting weather update for all dive sites...\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

    $results = $weatherService->updateAllSites();

    echo "Weather update completed!\n";
    echo "Updated: {$results['updated']}\n";
    echo "Failed: {$results['failed']}\n";

    if (!empty($results['errors'])) {
        echo "\nErrors:\n";
        foreach ($results['errors'] as $error) {
            echo "  - {$error}\n";
        }
    }

    exit(0);

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
