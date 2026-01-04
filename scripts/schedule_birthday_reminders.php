<?php
/**
 * Auto-Schedule Birthday Reminders
 *
 * Scans for customer birthdays in the next 7 days and schedules
 * birthday wishes.
 *
 * Run daily via cron (recommended: 1am daily)
 *
 * Cron entry:
 * 0 1 * * * cd /path/to/nautilus && php scripts/schedule_birthday_reminders.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Reminders\ServiceReminderService;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$reminderService = new ServiceReminderService();

echo "===========================================\n";
echo "Auto-Scheduling Birthday Reminders\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $results = $reminderService->scheduleBirthdayReminders();

    $scheduledCount = 0;
    $errorCount = 0;

    foreach ($results as $result) {
        if ($result['status'] === 'scheduled') {
            $scheduledCount++;
            echo "✓ Scheduled birthday reminder for customer #{$result['customer_id']}\n";
        } else {
            $errorCount++;
            echo "✗ Error for customer #{$result['customer_id']}: {$result['error']}\n";
        }
    }

    echo "\n===========================================\n";
    echo "Summary:\n";
    echo "  Total Processed: " . count($results) . "\n";
    echo "  Successfully Scheduled: {$scheduledCount}\n";
    echo "  Errors: {$errorCount}\n";
    echo "===========================================\n";

    exit(0);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
