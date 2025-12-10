<?php
/**
 * Auto-Schedule Equipment Service Reminders
 *
 * Scans equipment service history for upcoming due dates and automatically
 * schedules reminders for customers.
 *
 * Run daily via cron (recommended: 2am daily)
 *
 * Cron entry:
 * 0 2 * * * cd /path/to/nautilus && php scripts/schedule_equipment_reminders.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Reminders\ServiceReminderService;

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$reminderService = new ServiceReminderService();

echo "===========================================\n";
echo "Auto-Scheduling Equipment Service Reminders\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $results = $reminderService->autoScheduleEquipmentReminders();

    $scheduledCount = 0;
    $errorCount = 0;

    foreach ($results as $result) {
        if ($result['status'] === 'scheduled') {
            $scheduledCount++;
            echo "✓ Scheduled {$result['equipment']} reminder for customer #{$result['customer_id']}\n";
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
