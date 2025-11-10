<?php

namespace App\Services\Admin;

use App\Core\Database;
use PDO;
use App\Core\Logger;
use Exception;

/**
 * Database Backup and Recovery Service
 * Handles automated backups, restoration, and backup management
 */
class BackupService
{
    private PDO $db;
    private Logger $logger;
    private string $backupPath;
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPass;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
        $this->backupPath = BASE_PATH . '/storage/backups';
        $this->dbHost = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbName = $_ENV['DB_DATABASE'] ?? 'nautilus';
        $this->dbUser = $_ENV['DB_USERNAME'] ?? 'root';
        $this->dbPass = $_ENV['DB_PASSWORD'] ?? '';

        // Ensure backup directory exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a new database backup
     */
    public function createBackup(string $type = 'manual', ??int $userId = null): array
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "nautilus_backup_{$timestamp}.sql";
            $filepath = $this->backupPath . '/' . $filename;

            // Execute mysqldump
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
                escapeshellarg($this->dbHost),
                escapeshellarg($this->dbUser),
                escapeshellarg($this->dbPass),
                escapeshellarg($this->dbName),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new Exception('Mysqldump failed: ' . implode("\n", $output));
            }

            // Compress the backup
            $compressedFile = $filepath . '.gz';
            $this->compressFile($filepath, $compressedFile);

            // Remove uncompressed file
            unlink($filepath);

            // Get file size
            $fileSize = filesize($compressedFile);

            // Record backup in database
            $backupId = $this->recordBackup([
                'filename' => basename($compressedFile),
                'filepath' => $compressedFile,
                'file_size' => $fileSize,
                'type' => $type,
                'created_by' => $userId,
                'status' => 'completed'
            ]);

            $this->logger->info("Database backup created successfully", [
                'backup_id' => $backupId,
                'filename' => basename($compressedFile),
                'size' => $this->formatBytes($fileSize)
            ]);

            return [
                'success' => true,
                'backup_id' => $backupId,
                'filename' => basename($compressedFile),
                'size' => $fileSize,
                'size_formatted' => $this->formatBytes($fileSize)
            ];

        } catch (Exception $e) {
            $this->logger->error("Backup creation failed", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup(int $backupId, ??int $userId = null): array
    {
        try {
            // Get backup details
            $backup = $this->getBackupById($backupId);

            if (!$backup) {
                throw new Exception('Backup not found');
            }

            if (!file_exists($backup['filepath'])) {
                throw new Exception('Backup file not found on disk');
            }

            // Decompress if needed
            $sqlFile = $backup['filepath'];
            if (pathinfo($sqlFile, PATHINFO_EXTENSION) === 'gz') {
                $sqlFile = str_replace('.gz', '', $sqlFile);
                $this->decompressFile($backup['filepath'], $sqlFile);
            }

            // Create a pre-restore backup
            $preRestoreBackup = $this->createBackup('pre_restore', $userId);

            if (!$preRestoreBackup['success']) {
                throw new Exception('Failed to create pre-restore backup');
            }

            // Execute restore
            $command = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s 2>&1',
                escapeshellarg($this->dbHost),
                escapeshellarg($this->dbUser),
                escapeshellarg($this->dbPass),
                escapeshellarg($this->dbName),
                escapeshellarg($sqlFile)
            );

            exec($command, $output, $returnCode);

            // Clean up decompressed file
            if (file_exists($sqlFile) && $sqlFile !== $backup['filepath']) {
                unlink($sqlFile);
            }

            if ($returnCode !== 0) {
                throw new Exception('MySQL restore failed: ' . implode("\n", $output));
            }

            // Update backup record
            $this->updateBackupStatus($backupId, 'restored');

            $this->logger->info("Database restored successfully", [
                'backup_id' => $backupId,
                'filename' => $backup['filename'],
                'restored_by' => $userId
            ]);

            return [
                'success' => true,
                'backup_id' => $backupId,
                'pre_restore_backup_id' => $preRestoreBackup['backup_id']
            ];

        } catch (Exception $e) {
            $this->logger->error("Backup restoration failed", [
                'backup_id' => $backupId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all backups
     */
    public function getAllBackups(int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT * FROM database_backups
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get backup by ID
     */
    public function getBackupById(int $id): ?array
    {
        $sql = "SELECT * FROM database_backups WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(int $backupId): array
    {
        try {
            $backup = $this->getBackupById($backupId);

            if (!$backup) {
                throw new Exception('Backup not found');
            }

            // Delete file from disk
            if (file_exists($backup['filepath'])) {
                unlink($backup['filepath']);
            }

            // Delete from database
            $sql = "DELETE FROM database_backups WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$backupId]);

            $this->logger->info("Backup deleted", ['backup_id' => $backupId]);

            return ['success' => true];

        } catch (Exception $e) {
            $this->logger->error("Backup deletion failed", [
                'backup_id' => $backupId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clean old backups (keep last N backups)
     */
    public function cleanOldBackups(int $keepCount = 10): array
    {
        try {
            // Get backups older than the keep count
            $sql = "SELECT * FROM database_backups
                    WHERE type IN ('automatic', 'manual')
                    ORDER BY created_at DESC
                    LIMIT 999 OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$keepCount]);
            $oldBackups = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $deletedCount = 0;

            foreach ($oldBackups as $backup) {
                $result = $this->deleteBackup($backup['id']);
                if ($result['success']) {
                    $deletedCount++;
                }
            }

            return [
                'success' => true,
                'deleted_count' => $deletedCount
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup(int $backupId): void
    {
        $backup = $this->getBackupById($backupId);

        if (!$backup || !file_exists($backup['filepath'])) {
            header('HTTP/1.0 404 Not Found');
            echo 'Backup not found';
            exit;
        }

        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $backup['filename'] . '"');
        header('Content-Length: ' . filesize($backup['filepath']));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        readfile($backup['filepath']);
        exit;
    }

    /**
     * Record backup in database
     */
    private function recordBackup(array $data): int
    {
        $sql = "INSERT INTO database_backups
                (filename, filepath, file_size, type, created_by, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['filename'],
            $data['filepath'],
            $data['file_size'],
            $data['type'],
            $data['created_by'],
            $data['status']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update backup status
     */
    private function updateBackupStatus(int $backupId, string $status): void
    {
        $sql = "UPDATE database_backups SET status = ?, restored_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status, $backupId]);
    }

    /**
     * Compress file using gzip
     */
    private function compressFile(string $source, string $destination): void
    {
        $fp = fopen($source, 'rb');
        $gz = gzopen($destination, 'wb9');

        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 1024 * 512));
        }

        fclose($fp);
        gzclose($gz);
    }

    /**
     * Decompress gzip file
     */
    private function decompressFile(string $source, string $destination): void
    {
        $gz = gzopen($source, 'rb');
        $fp = fopen($destination, 'wb');

        while (!gzeof($gz)) {
            fwrite($fp, gzread($gz, 1024 * 512));
        }

        gzclose($gz);
        fclose($fp);
    }

    /**
     * Format bytes to human-readable size
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
