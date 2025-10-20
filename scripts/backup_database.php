#!/usr/bin/env php
<?php
/**
 * Automatic Database Backup Script
 * Run this via cron for automatic backups
 *
 * Example cron (daily at 2 AM):
 * 0 2 * * * cd /path/to/nautilus-v6 && php scripts/backup_database.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define BASE_PATH constant
define('BASE_PATH', dirname(__DIR__));

use App\Services\Admin\BackupService;

try {
    $backupService = new BackupService();

    echo "Starting automatic database backup...\n";

    $result = $backupService->createBackup('automatic');

    if ($result['success']) {
        echo "✓ Backup created successfully!\n";
        echo "  Filename: {$result['filename']}\n";
        echo "  Size: {$result['size_formatted']}\n";
        echo "  Backup ID: {$result['backup_id']}\n";

        // Clean old backups (keep last 30)
        echo "\nCleaning old backups...\n";
        $cleanResult = $backupService->cleanOldBackups(30);

        if ($cleanResult['success']) {
            echo "✓ Cleaned {$cleanResult['deleted_count']} old backup(s)\n";
        }

        exit(0);
    } else {
        echo "✗ Backup failed: {$result['error']}\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
