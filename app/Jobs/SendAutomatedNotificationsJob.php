<?php

namespace App\Jobs;

use App\Services\Notifications\AutomatedNotificationService;
use App\Core\Database;
use App\Core\Logger;

/**
 * Send Automated Notifications Job
 *
 * This job should be run via cron to send automated notifications
 * Recommended schedule: Every hour or every 30 minutes
 *
 * Cron example:
 * 0 * * * * php /path/to/nautilus/app/Jobs/SendAutomatedNotificationsJob.php
 */
class SendAutomatedNotificationsJob
{
    private AutomatedNotificationService $notificationService;
    private Logger $logger;
    private array $results = [];

    public function __construct()
    {
        $this->notificationService = new AutomatedNotificationService();
        $this->logger = new Logger();
    }

    /**
     * Execute the job
     */
    public function execute(): void
    {
        $this->logger->info('Starting automated notifications job');
        $startTime = microtime(true);

        try {
            // Send low stock alerts (once per day in the morning)
            if ($this->shouldRunDaily('low_stock_alert')) {
                $this->runLowStockAlerts();
            }

            // Send maintenance due alerts (once per day in the morning)
            if ($this->shouldRunDaily('maintenance_alert')) {
                $this->runMaintenanceDueAlerts();
            }

            // Send rental reminders (check every hour)
            $this->runRentalReminders();

            // Process scheduled notifications queue
            $this->processScheduledNotifications();

            // Check for customer milestones (once per day)
            if ($this->shouldRunDaily('milestone_check')) {
                $this->checkCustomerMilestones();
            }

            // Log results
            $duration = round(microtime(true) - $startTime, 2);
            $this->logger->info('Automated notifications job completed', [
                'duration' => $duration,
                'results' => $this->results
            ]);

            // Output summary
            $this->outputSummary($duration);

        } catch (\Exception $e) {
            $this->logger->error('Automated notifications job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Run low stock alerts
     */
    private function runLowStockAlerts(): void
    {
        try {
            $count = $this->notificationService->sendLowStockAlert();
            $this->results['low_stock_alerts'] = $count;
            $this->logger->info("Sent low stock alert", ['items' => $count]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send low stock alerts', ['error' => $e->getMessage()]);
            $this->results['low_stock_alerts'] = 'failed';
        }
    }

    /**
     * Run maintenance due alerts
     */
    private function runMaintenanceDueAlerts(): void
    {
        try {
            $count = $this->notificationService->sendMaintenanceDueAlerts();
            $this->results['maintenance_alerts'] = $count;
            $this->logger->info("Sent maintenance due alerts", ['items' => $count]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send maintenance alerts', ['error' => $e->getMessage()]);
            $this->results['maintenance_alerts'] = 'failed';
        }
    }

    /**
     * Run rental reminders
     */
    private function runRentalReminders(): void
    {
        try {
            // Find rentals due within 24 hours that haven't had reminders sent
            $rentals = Database::fetchAll(
                "SELECT rt.id
                 FROM rental_transactions rt
                 WHERE rt.status = 'active'
                 AND rt.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                 AND (rt.reminder_sent_at IS NULL OR rt.reminder_sent_at < DATE_SUB(NOW(), INTERVAL 12 HOUR))
                 LIMIT 50"
            );

            $sent = 0;
            foreach ($rentals as $rental) {
                if ($this->notificationService->sendRentalReminder($rental['id'], 1)) {
                    $sent++;
                    // Update reminder timestamp
                    Database::query(
                        "UPDATE rental_transactions SET reminder_sent_at = NOW() WHERE id = ?",
                        [$rental['id']]
                    );
                }
            }

            $this->results['rental_reminders'] = $sent;
            $this->logger->info("Sent rental reminders", ['count' => $sent]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send rental reminders', ['error' => $e->getMessage()]);
            $this->results['rental_reminders'] = 'failed';
        }
    }

    /**
     * Process scheduled notifications queue
     */
    private function processScheduledNotifications(): void
    {
        try {
            // Get pending notifications that are due
            $notifications = Database::fetchAll(
                "SELECT *
                 FROM scheduled_notifications
                 WHERE status = 'pending'
                 AND scheduled_for <= NOW()
                 AND attempts < max_attempts
                 ORDER BY priority DESC, scheduled_for ASC
                 LIMIT 100"
            );

            $processed = 0;
            $failed = 0;

            foreach ($notifications as $notification) {
                // Mark as processing
                Database::query(
                    "UPDATE scheduled_notifications
                     SET status = 'processing', updated_at = NOW()
                     WHERE id = ?",
                    [$notification['id']]
                );

                try {
                    // Send the notification
                    $emailService = new \App\Services\Email\EmailService();
                    $result = $emailService->send(
                        $notification['recipient'],
                        $notification['subject'],
                        $notification['body'],
                        ['is_html' => true]
                    );

                    if ($result) {
                        // Mark as sent
                        Database::query(
                            "UPDATE scheduled_notifications
                             SET status = 'sent', sent_at = NOW(), updated_at = NOW()
                             WHERE id = ?",
                            [$notification['id']]
                        );
                        $processed++;
                    } else {
                        throw new \Exception('Email service returned false');
                    }
                } catch (\Exception $e) {
                    // Mark as failed or retry
                    $attempts = $notification['attempts'] + 1;
                    $status = $attempts >= $notification['max_attempts'] ? 'failed' : 'pending';

                    Database::query(
                        "UPDATE scheduled_notifications
                         SET status = ?, attempts = ?, last_error = ?, updated_at = NOW()
                         WHERE id = ?",
                        [$status, $attempts, $e->getMessage(), $notification['id']]
                    );
                    $failed++;

                    $this->logger->warning('Failed to send scheduled notification', [
                        'id' => $notification['id'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $this->results['scheduled_notifications'] = [
                'processed' => $processed,
                'failed' => $failed
            ];

            $this->logger->info("Processed scheduled notifications", [
                'processed' => $processed,
                'failed' => $failed
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to process scheduled notifications', ['error' => $e->getMessage()]);
            $this->results['scheduled_notifications'] = 'failed';
        }
    }

    /**
     * Check for customer milestones and send celebration emails
     */
    private function checkCustomerMilestones(): void
    {
        try {
            $sent = 0;

            // Check for purchase count milestones (10th, 25th, 50th, 100th purchase)
            $milestones = [10, 25, 50, 100];

            foreach ($milestones as $milestone) {
                $customers = Database::fetchAll(
                    "SELECT c.id, COUNT(t.id) as purchase_count
                     FROM customers c
                     INNER JOIN transactions t ON c.id = t.customer_id
                     WHERE t.status = 'completed'
                     AND t.transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     GROUP BY c.id
                     HAVING purchase_count = ?",
                    [$milestone]
                );

                foreach ($customers as $customer) {
                    // Check if we haven't already sent this milestone notification
                    $alreadySent = Database::fetchOne(
                        "SELECT id FROM notification_log
                         WHERE notification_type = 'milestone_purchase_count'
                         AND recipient = (SELECT email FROM customers WHERE id = ?)
                         AND reference_id = ?
                         AND sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                        [$customer['id'], $milestone]
                    );

                    if (!$alreadySent) {
                        if ($this->notificationService->sendMilestoneEmail(
                            $customer['id'],
                            'purchase_count',
                            ['count' => $milestone]
                        )) {
                            $sent++;
                        }
                    }
                }
            }

            // Check for birthdays (customers with birthday today)
            $birthdayCustomers = Database::fetchAll(
                "SELECT id
                 FROM customers
                 WHERE MONTH(date_of_birth) = MONTH(NOW())
                 AND DAY(date_of_birth) = DAY(NOW())
                 AND is_active = 1"
            );

            foreach ($birthdayCustomers as $customer) {
                // Check if we haven't sent birthday email this year
                $alreadySent = Database::fetchOne(
                    "SELECT id FROM notification_log
                     WHERE notification_type = 'milestone_birthday'
                     AND recipient = (SELECT email FROM customers WHERE id = ?)
                     AND YEAR(sent_at) = YEAR(NOW())",
                    [$customer['id']]
                );

                if (!$alreadySent) {
                    if ($this->notificationService->sendMilestoneEmail(
                        $customer['id'],
                        'birthday',
                        []
                    )) {
                        $sent++;
                    }
                }
            }

            $this->results['milestone_emails'] = $sent;
            $this->logger->info("Sent milestone emails", ['count' => $sent]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to check customer milestones', ['error' => $e->getMessage()]);
            $this->results['milestone_emails'] = 'failed';
        }
    }

    /**
     * Check if a task should run daily (runs between 8 AM and 9 AM)
     */
    private function shouldRunDaily(string $taskKey): bool
    {
        $currentHour = (int)date('G');

        // Run daily tasks between 8 AM and 9 AM
        if ($currentHour < 8 || $currentHour >= 9) {
            return false;
        }

        // Check if we've already run today
        $lastRun = Database::fetchOne(
            "SELECT id FROM notification_log
             WHERE notification_type = ?
             AND DATE(sent_at) = CURDATE()
             LIMIT 1",
            [$taskKey]
        );

        return $lastRun === null;
    }

    /**
     * Output summary of job execution
     */
    private function outputSummary(float $duration): void
    {
        echo "==================================================\n";
        echo "Automated Notifications Job Summary\n";
        echo "==================================================\n";
        echo "Execution Time: {$duration}s\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($this->results as $type => $result) {
            $displayName = ucwords(str_replace('_', ' ', $type));
            if (is_array($result)) {
                echo "{$displayName}:\n";
                foreach ($result as $key => $value) {
                    echo "  " . ucfirst($key) . ": {$value}\n";
                }
            } else {
                echo "{$displayName}: {$result}\n";
            }
        }

        echo "\n==================================================\n";
    }
}

// Allow running from command line
if (php_sapi_name() === 'cli') {
    // Load environment and bootstrap
    require_once __DIR__ . '/../../vendor/autoload.php';

    // Load environment variables
    if (file_exists(__DIR__ . '/../../.env')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
    }

    // Run the job
    $job = new SendAutomatedNotificationsJob();
    $job->execute();
}
