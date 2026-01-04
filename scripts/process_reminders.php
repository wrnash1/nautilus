<?php
/**
 * Process Pending Service Reminders
 *
 * This script processes all pending service reminders that are due to be sent today.
 * Run this daily via cron (recommended: 8am daily)
 *
 * Cron entry:
 * 0 8 * * * cd /path/to/nautilus && php scripts/process_reminders.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Reminders\ServiceReminderService;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$reminderService = new ServiceReminderService();

echo "===========================================\n";
echo "Processing Service Reminders\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $results = $reminderService->processPendingReminders();

    $sentCount = 0;
    $failedCount = 0;

    foreach ($results as $result) {
        if ($result['sent']) {
            $sentCount++;
            echo "✓ Sent reminder #{$result['reminder_id']} to customer #{$result['customer_id']} ({$result['type']})\n";
        } else {
            $failedCount++;
            echo "✗ Failed reminder #{$result['reminder_id']}: " . implode('; ', $result['errors']) . "\n";
        }
    }

    echo "\n===========================================\n";
    echo "Summary:\n";
    echo "  Total Processed: " . count($results) . "\n";
    echo "  Successfully Sent: {$sentCount}\n";
    echo "  Failed: {$failedCount}\n";
    echo "===========================================\n";

    exit(0);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
