<?php

namespace App\Jobs;

use App\Core\Logger;

/**
 * Database Backup Job
 *
 * Creates automated database backups
 * Run daily at 2 AM
 *
 * Cron: 0 2 * * * php /path/to/nautilus/app/Jobs/DatabaseBackupJob.php
 */
class DatabaseBackupJob
{
    private Logger $logger;
    private string $backupDir;
    private int $retentionDays = 30;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->backupDir = __DIR__ . '/../../storage/backups';

        // Create backup directory if it doesn't exist
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function execute(): void
    {
        $this->logger->info('Starting database backup job');
        $startTime = microtime(true);

        try {
            // Get database credentials
            $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
            $dbPort = $_ENV['DB_PORT'] ?? '3306';
            $dbName = $_ENV['DB_DATABASE'] ?? 'nautilus';
            $dbUser = $_ENV['DB_USERNAME'] ?? 'root';
            $dbPassword = $_ENV['DB_PASSWORD'] ?? '';

            // Generate backup filename
            $timestamp = date('Y-m-d_H-i-s');
            $backupFile = $this->backupDir . "/nautilus_backup_{$timestamp}.sql";
            $compressedFile = $backupFile . '.gz';

            echo "Creating database backup...\n";
            echo "Database: {$dbName}\n";
            echo "Backup file: {$backupFile}\n";

            // Build mysqldump command
            $command = sprintf(
                "mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s 2>&1",
                escapeshellarg($dbHost),
                escapeshellarg($dbPort),
                escapeshellarg($dbUser),
                escapeshellarg($dbPassword),
                escapeshellarg($dbName),
                escapeshellarg($backupFile)
            );

            // Execute backup
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception("Backup failed with exit code {$returnCode}: " . implode("\n", $output));
            }

            // Verify backup file exists and has content
            if (!file_exists($backupFile) || filesize($backupFile) == 0) {
                throw new \Exception("Backup file is empty or doesn't exist");
            }

            $backupSize = filesize($backupFile);
            echo "Backup created: " . $this->formatBytes($backupSize) . "\n";

            // Compress the backup
            echo "Compressing backup...\n";
            exec("gzip " . escapeshellarg($backupFile), $gzipOutput, $gzipReturn);

            if ($gzipReturn === 0 && file_exists($compressedFile)) {
                $compressedSize = filesize($compressedFile);
                echo "Compressed to: " . $this->formatBytes($compressedSize) . "\n";
                $finalFile = $compressedFile;
            } else {
                echo "Compression failed, keeping uncompressed backup\n";
                $finalFile = $backupFile;
            }

            // Cleanup old backups
            $this->cleanupOldBackups();

            $duration = round(microtime(true) - $startTime, 2);

            $this->logger->info('Database backup completed', [
                'duration' => $duration,
                'backup_file' => basename($finalFile),
                'size' => filesize($finalFile)
            ]);

            echo "\n==================================================\n";
            echo "Database Backup Summary\n";
            echo "==================================================\n";
            echo "Status: SUCCESS\n";
            echo "Backup File: " . basename($finalFile) . "\n";
            echo "Size: " . $this->formatBytes(filesize($finalFile)) . "\n";
            echo "Execution Time: {$duration}s\n";
            echo "==================================================\n";

        } catch (\Exception $e) {
            $this->logger->error('Database backup failed', [
                'error' => $e->getMessage()
            ]);
            echo "ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Cleanup old backup files
     */
    private function cleanupOldBackups(): void
    {
        echo "\nCleaning up old backups (retention: {$this->retentionDays} days)...\n";

        $files = glob($this->backupDir . '/nautilus_backup_*.sql*');
        $deleted = 0;
        $retained = 0;

        foreach ($files as $file) {
            $fileAge = (time() - filemtime($file)) / 86400; // Age in days

            if ($fileAge > $this->retentionDays) {
                if (unlink($file)) {
                    $deleted++;
                    echo "Deleted old backup: " . basename($file) . " (age: " . round($fileAge, 1) . " days)\n";
                }
            } else {
                $retained++;
            }
        }

        echo "Cleanup complete: {$deleted} deleted, {$retained} retained\n";
    }

    /**
     * Format bytes to human-readable size
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Allow running from command line
if (php_sapi_name() === 'cli') {
    require_once __DIR__ . '/../../vendor/autoload.php';

    if (file_exists(__DIR__ . '/../../.env')) {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
    }

    $job = new DatabaseBackupJob();
    $job->execute();
}
