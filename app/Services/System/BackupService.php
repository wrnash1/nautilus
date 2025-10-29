<?php

namespace App\Services\System;

use PDO;
use ZipArchive;

/**
 * Backup and Restore Service
 *
 * Handles database backups, restoration, and backup management
 */
class BackupService
{
    private PDO $db;
    private string $backupPath;
    private string $databasePath;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
        $this->backupPath = dirname(__DIR__, 3) . '/storage/backups';
        $this->databasePath = dirname(__DIR__, 3) . '/database/nautilus.db';

        // Ensure backup directory exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create a full database backup
     */
    public function createBackup(string $description = '', bool $includeDocuments = false): ?string
    {
        try {
            $timestamp = date('Y-m-d_His');
            $filename = "nautilus_backup_{$timestamp}.zip";
            $zipPath = $this->backupPath . '/' . $filename;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                throw new \Exception("Failed to create ZIP archive");
            }

            // Add database file
            $zip->addFile($this->databasePath, 'database/nautilus.db');

            // Add documents if requested
            if ($includeDocuments) {
                $documentsPath = dirname(__DIR__, 3) . '/storage/documents';
                if (is_dir($documentsPath)) {
                    $this->addDirectoryToZip($zip, $documentsPath, 'documents');
                }
            }

            // Add metadata
            $metadata = [
                'created_at' => date('Y-m-d H:i:s'),
                'description' => $description,
                'version' => '1.0',
                'includes_documents' => $includeDocuments,
                'database_size' => filesize($this->databasePath),
                'php_version' => PHP_VERSION,
                'app_version' => '1.0.0'
            ];
            $zip->addFromString('metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));

            $zip->close();

            // Record backup in database
            $this->recordBackup($filename, $description, filesize($zipPath), $includeDocuments);

            return $filename;
        } catch (\Exception $e) {
            error_log("Backup failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup(string $filename): bool
    {
        try {
            $zipPath = $this->backupPath . '/' . $filename;

            if (!file_exists($zipPath)) {
                throw new \Exception("Backup file not found");
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new \Exception("Failed to open backup archive");
            }

            // Extract to temporary directory
            $tempPath = $this->backupPath . '/temp_restore_' . time();
            mkdir($tempPath, 0755, true);

            $zip->extractTo($tempPath);
            $zip->close();

            // Backup current database before restoring
            $currentBackup = $this->createBackup('Pre-restore backup', false);

            // Close all database connections
            $this->db = null;
            \App\Core\Database::getInstance()->disconnect();

            // Replace database file
            $restoredDbPath = $tempPath . '/database/nautilus.db';
            if (!file_exists($restoredDbPath)) {
                throw new \Exception("Database file not found in backup");
            }

            copy($restoredDbPath, $this->databasePath);

            // Restore documents if they exist
            $documentsBackupPath = $tempPath . '/documents';
            if (is_dir($documentsBackupPath)) {
                $documentsPath = dirname(__DIR__, 3) . '/storage/documents';
                $this->copyDirectory($documentsBackupPath, $documentsPath);
            }

            // Clean up temporary directory
            $this->deleteDirectory($tempPath);

            // Reconnect to database
            $this->db = \App\Core\Database::getInstance()->getConnection();

            // Log restoration
            $this->logRestoration($filename);

            return true;
        } catch (\Exception $e) {
            error_log("Restore failed: " . $e->getMessage());

            // Clean up temporary directory if it exists
            if (isset($tempPath) && is_dir($tempPath)) {
                $this->deleteDirectory($tempPath);
            }

            return false;
        }
    }

    /**
     * Get list of available backups
     */
    public function getBackups(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM backups ORDER BY created_at DESC LIMIT 100"
        );

        $backups = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $filePath = $this->backupPath . '/' . $row['filename'];
            $row['file_exists'] = file_exists($filePath);
            $row['file_size_formatted'] = $this->formatBytes((int)$row['file_size']);
            $backups[] = $row;
        }

        return $backups;
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(int $backupId): bool
    {
        try {
            // Get backup info
            $stmt = $this->db->prepare("SELECT filename FROM backups WHERE id = ?");
            $stmt->execute([$backupId]);
            $backup = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$backup) {
                return false;
            }

            // Delete file
            $filePath = $this->backupPath . '/' . $backup['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete record
            $stmt = $this->db->prepare("DELETE FROM backups WHERE id = ?");
            $stmt->execute([$backupId]);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to delete backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Export backup (download)
     */
    public function downloadBackup(string $filename): void
    {
        $filePath = $this->backupPath . '/' . $filename;

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "Backup file not found";
            exit;
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Get backup statistics
     */
    public function getStatistics(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) as total_backups,
                SUM(file_size) as total_size,
                MAX(created_at) as last_backup,
                MIN(created_at) as oldest_backup
             FROM backups"
        );

        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_backups' => (int)($stats['total_backups'] ?? 0),
            'total_size' => (int)($stats['total_size'] ?? 0),
            'total_size_formatted' => $this->formatBytes((int)($stats['total_size'] ?? 0)),
            'last_backup' => $stats['last_backup'],
            'oldest_backup' => $stats['oldest_backup'],
            'backup_directory' => $this->backupPath,
            'database_size' => filesize($this->databasePath),
            'database_size_formatted' => $this->formatBytes(filesize($this->databasePath))
        ];
    }

    /**
     * Clean old backups (keep only N most recent)
     */
    public function cleanOldBackups(int $keepCount = 10): int
    {
        $stmt = $this->db->query(
            "SELECT id, filename FROM backups ORDER BY created_at DESC"
        );

        $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $deletedCount = 0;

        foreach (array_slice($backups, $keepCount) as $backup) {
            if ($this->deleteBackup($backup['id'])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Schedule automatic backup
     */
    public function scheduleBackup(string $frequency, string $time, bool $includeDocuments = false): bool
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO backup_schedules (frequency, time, include_documents, is_active, created_at)
                 VALUES (?, ?, ?, 1, datetime('now'))
                 ON CONFLICT(id) DO UPDATE SET
                     frequency = excluded.frequency,
                     time = excluded.time,
                     include_documents = excluded.include_documents,
                     updated_at = datetime('now')"
            );

            $stmt->execute([$frequency, $time, $includeDocuments ? 1 : 0]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to schedule backup: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record backup in database
     */
    private function recordBackup(string $filename, string $description, int $fileSize, bool $includesDocs): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO backups (filename, description, file_size, includes_documents, created_at)
             VALUES (?, ?, ?, ?, datetime('now'))"
        );

        $stmt->execute([
            $filename,
            $description,
            $fileSize,
            $includesDocs ? 1 : 0
        ]);
    }

    /**
     * Log backup restoration
     */
    private function logRestoration(string $filename): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO backup_restorations (backup_filename, restored_at)
             VALUES (?, datetime('now'))"
        );

        $stmt->execute([$filename]);
    }

    /**
     * Add directory to ZIP archive recursively
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipPath): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($dirPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory(string $source, string $dest): void
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $targetPath = $dest . '/' . substr($file->getRealPath(), strlen($source) + 1);

            if ($file->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($file->getRealPath(), $targetPath);
            }
        }
    }

    /**
     * Delete directory recursively
     */
    private function deleteDirectory(string $dirPath): void
    {
        if (!is_dir($dirPath)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dirPath);
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
