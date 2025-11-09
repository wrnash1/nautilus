<?php

namespace App\Jobs;

use App\Core\Database;
use App\Core\Logger;

/**
 * Cleanup Old Data Job
 *
 * Removes old logs, temporary data, and archived records
 * Run weekly on Sunday at 3 AM
 *
 * Cron: 0 3 * * 0 php /path/to/nautilus/app/Jobs/CleanupOldDataJob.php
 */
class CleanupOldDataJob
{
    private Logger $logger;
    private array $results = [];

    // Retention periods (in days)
    private int $logRetentionDays = 90;
    private int $notificationLogRetentionDays = 180;
    private int $analyticsEventRetentionDays = 365;
    private int $sessionRetentionDays = 30;
    private int $cacheRetentionDays = 7;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function execute(): void
    {
        $this->logger->info('Starting data cleanup job');
        $startTime = microtime(true);

        try {
            $this->cleanupNotificationLogs();
            $this->cleanupAnalyticsEvents();
            $this->cleanupExpiredSessions();
            $this->cleanupOldCache();
            $this->cleanupScheduledNotifications();
            $this->cleanupLogFiles();
            $this->optimizeTables();

            $duration = round(microtime(true) - $startTime, 2);
            $this->logger->info('Data cleanup completed', [
                'duration' => $duration,
                'results' => $this->results
            ]);

            $this->outputSummary($duration);

        } catch (\Exception $e) {
            $this->logger->error('Data cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Cleanup old notification logs
     */
    private function cleanupNotificationLogs(): void
    {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$this->notificationLogRetentionDays} days"));

            $result = Database::query(
                "DELETE FROM notification_log
                 WHERE sent_at < ?
                 AND status IN ('sent', 'failed')",
                [$cutoffDate]
            );

            $deleted = $result->rowCount();
            $this->results['notification_logs'] = $deleted;
            $this->logger->info("Deleted {$deleted} old notification log entries");

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup notification logs', ['error' => $e->getMessage()]);
            $this->results['notification_logs'] = 'failed';
        }
    }

    /**
     * Cleanup old analytics events
     */
    private function cleanupAnalyticsEvents(): void
    {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$this->analyticsEventRetentionDays} days"));

            $result = Database::query(
                "DELETE FROM analytics_events
                 WHERE event_timestamp < ?",
                [$cutoffDate]
            );

            $deleted = $result->rowCount();
            $this->results['analytics_events'] = $deleted;
            $this->logger->info("Deleted {$deleted} old analytics events");

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup analytics events', ['error' => $e->getMessage()]);
            $this->results['analytics_events'] = 'failed';
        }
    }

    /**
     * Cleanup expired sessions
     */
    private function cleanupExpiredSessions(): void
    {
        try {
            // Cleanup database sessions if using database driver
            $cutoffDate = date('Y-m-d', strtotime("-{$this->sessionRetentionDays} days"));

            // Also cleanup file-based sessions
            $sessionPath = __DIR__ . '/../../storage/sessions';
            if (is_dir($sessionPath)) {
                $files = glob($sessionPath . '/sess_*');
                $deleted = 0;

                foreach ($files as $file) {
                    if (filemtime($file) < strtotime("-{$this->sessionRetentionDays} days")) {
                        if (unlink($file)) {
                            $deleted++;
                        }
                    }
                }

                $this->results['sessions'] = $deleted;
                $this->logger->info("Deleted {$deleted} expired session files");
            } else {
                $this->results['sessions'] = 'no session directory';
            }

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup sessions', ['error' => $e->getMessage()]);
            $this->results['sessions'] = 'failed';
        }
    }

    /**
     * Cleanup old cache entries
     */
    private function cleanupOldCache(): void
    {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$this->cacheRetentionDays} days"));

            $result = Database::query(
                "DELETE FROM dashboard_metrics_cache
                 WHERE last_calculated_at < ?",
                [$cutoffDate]
            );

            $deleted = $result->rowCount();
            $this->results['cache_entries'] = $deleted;
            $this->logger->info("Deleted {$deleted} old cache entries");

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup cache', ['error' => $e->getMessage()]);
            $this->results['cache_entries'] = 'failed';
        }
    }

    /**
     * Cleanup processed scheduled notifications
     */
    private function cleanupScheduledNotifications(): void
    {
        try {
            // Delete sent notifications older than 30 days
            $cutoffDate = date('Y-m-d', strtotime('-30 days'));

            $result = Database::query(
                "DELETE FROM scheduled_notifications
                 WHERE status IN ('sent', 'cancelled')
                 AND updated_at < ?",
                [$cutoffDate]
            );

            $deleted = $result->rowCount();
            $this->results['scheduled_notifications'] = $deleted;
            $this->logger->info("Deleted {$deleted} processed scheduled notifications");

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup scheduled notifications', ['error' => $e->getMessage()]);
            $this->results['scheduled_notifications'] = 'failed';
        }
    }

    /**
     * Cleanup old log files
     */
    private function cleanupLogFiles(): void
    {
        try {
            $logPath = __DIR__ . '/../../storage/logs';
            if (!is_dir($logPath)) {
                $this->results['log_files'] = 'no log directory';
                return;
            }

            $files = glob($logPath . '/*.log');
            $deleted = 0;
            $archived = 0;

            foreach ($files as $file) {
                $fileAge = (time() - filemtime($file)) / 86400; // Age in days

                // Delete logs older than retention period
                if ($fileAge > $this->logRetentionDays) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
                // Archive large log files (>10MB)
                elseif (filesize($file) > 10 * 1024 * 1024) {
                    $archiveName = $file . '.' . date('Y-m-d', filemtime($file)) . '.gz';
                    exec("gzip -c " . escapeshellarg($file) . " > " . escapeshellarg($archiveName));

                    if (file_exists($archiveName)) {
                        file_put_contents($file, ''); // Clear the original log
                        $archived++;
                    }
                }
            }

            $this->results['log_files'] = "deleted: {$deleted}, archived: {$archived}";
            $this->logger->info("Log cleanup: {$deleted} deleted, {$archived} archived");

        } catch (\Exception $e) {
            $this->logger->error('Failed to cleanup log files', ['error' => $e->getMessage()]);
            $this->results['log_files'] = 'failed';
        }
    }

    /**
     * Optimize database tables
     */
    private function optimizeTables(): void
    {
        try {
            $tables = [
                'notification_log',
                'analytics_events',
                'dashboard_metrics_cache',
                'scheduled_notifications',
                'business_kpis',
                'sales_trends',
                'customer_analytics',
                'product_analytics'
            ];

            $optimized = 0;
            foreach ($tables as $table) {
                try {
                    Database::query("OPTIMIZE TABLE {$table}");
                    $optimized++;
                } catch (\Exception $e) {
                    // Table may not exist, continue
                    continue;
                }
            }

            $this->results['table_optimization'] = $optimized;
            $this->logger->info("Optimized {$optimized} database tables");

        } catch (\Exception $e) {
            $this->logger->error('Failed to optimize tables', ['error' => $e->getMessage()]);
            $this->results['table_optimization'] = 'failed';
        }
    }

    /**
     * Output summary
     */
    private function outputSummary(float $duration): void
    {
        echo "==================================================\n";
        echo "Data Cleanup Summary\n";
        echo "==================================================\n";
        echo "Execution Time: {$duration}s\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

        echo "Retention Periods:\n";
        echo "  - Logs: {$this->logRetentionDays} days\n";
        echo "  - Notification Logs: {$this->notificationLogRetentionDays} days\n";
        echo "  - Analytics Events: {$this->analyticsEventRetentionDays} days\n";
        echo "  - Sessions: {$this->sessionRetentionDays} days\n";
        echo "  - Cache: {$this->cacheRetentionDays} days\n\n";

        echo "Cleanup Results:\n";
        foreach ($this->results as $type => $result) {
            $displayName = ucwords(str_replace('_', ' ', $type));
            echo "  {$displayName}: {$result}\n";
        }

        echo "\n==================================================\n";
    }
}

// Allow running from command line
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../vendor/autoload.php';

    if (file_exists(__DIR__ . '/../../.env')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
    }

    $job = new CleanupOldDataJob();
    $job->execute();
}
