<?php

namespace App\Services\Backup;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Backup Service
 *
 * Automated database and file backup system with retention policies
 */
class BackupService
{
    private Logger $logger;
    private string $backupPath;
    private array $config;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->backupPath = __DIR__ . '/../../../storage/backups';

        // Ensure backup directory exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }

        $this->config = [
            'retention_days' => (int)($_ENV['BACKUP_RETENTION_DAYS'] ?? 30),
            'compress' => true,
            'include_files' => true
        ];
    }

    /**
     * Create full database backup
     */
    public function createDatabaseBackup(??int $tenantId = null): array
    {
        try {
            $tenantId = $tenantId ?? TenantMiddleware::getCurrentTenantId();
            $timestamp = date('Y-m-d_H-i-s');
            $filename = $tenantId
                ? "tenant_{$tenantId}_db_backup_{$timestamp}.sql"
                : "full_db_backup_{$timestamp}.sql";

            $filepath = $this->backupPath . '/' . $filename;

            // Get database credentials
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? 'nautilus';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            // Build mysqldump command
            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s %s %s > %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                $tenantId ? $this->getTenantTablesFilter($tenantId) : '',
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute backup
            exec($cmd . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database backup failed: ' . implode("\n", $output));
            }

            // Compress if enabled
            if ($this->config['compress']) {
                $compressedPath = $this->compressFile($filepath);
                unlink($filepath); // Remove uncompressed version
                $filepath = $compressedPath;
                $filename = basename($compressedPath);
            }

            // Get file size
            $filesize = filesize($filepath);

            // Log backup
            $this->logBackup([
                'tenant_id' => $tenantId,
                'backup_type' => 'database',
                'filename' => $filename,
                'filepath' => $filepath,
                'filesize' => $filesize,
                'status' => 'completed'
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'filesize' => $filesize,
                'filesize_human' => $this->formatBytes($filesize)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Database backup failed', ['error' => $e->getMessage()]);

            $this->logBackup([
                'tenant_id' => $tenantId ?? null,
                'backup_type' => 'database',
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create files backup (uploads, etc.)
     */
    public function createFilesBackup(??int $tenantId = null): array
    {
        try {
            $tenantId = $tenantId ?? TenantMiddleware::getCurrentTenantId();
            $timestamp = date('Y-m-d_H-i-s');
            $filename = $tenantId
                ? "tenant_{$tenantId}_files_backup_{$timestamp}.zip"
                : "full_files_backup_{$timestamp}.zip";

            $filepath = $this->backupPath . '/' . $filename;

            // Directories to backup
            $directories = $this->getBackupDirectories($tenantId);

            if (empty($directories)) {
                return [
                    'success' => true,
                    'message' => 'No files to backup',
                    'filesize' => 0
                ];
            }

            // Create ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Cannot create ZIP file');
            }

            $fileCount = 0;
            foreach ($directories as $dir) {
                if (is_dir($dir)) {
                    $fileCount += $this->addDirectoryToZip($zip, $dir, basename($dir));
                }
            }

            $zip->close();

            $filesize = filesize($filepath);

            // Log backup
            $this->logBackup([
                'tenant_id' => $tenantId,
                'backup_type' => 'files',
                'filename' => $filename,
                'filepath' => $filepath,
                'filesize' => $filesize,
                'status' => 'completed'
            ]);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'files_count' => $fileCount,
                'filesize' => $filesize,
                'filesize_human' => $this->formatBytes($filesize)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Files backup failed', ['error' => $e->getMessage()]);

            $this->logBackup([
                'tenant_id' => $tenantId ?? null,
                'backup_type' => 'files',
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create complete backup (database + files)
     */
    public function createCompleteBackup(??int $tenantId = null): array
    {
        try {
            $dbBackup = $this->createDatabaseBackup($tenantId);
            $filesBackup = $this->createFilesBackup($tenantId);

            return [
                'success' => $dbBackup['success'] && $filesBackup['success'],
                'database_backup' => $dbBackup,
                'files_backup' => $filesBackup,
                'total_size' => ($dbBackup['filesize'] ?? 0) + ($filesBackup['filesize'] ?? 0)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Complete backup failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreDatabase(string $backupFile): array
    {
        try {
            $filepath = $this->backupPath . '/' . basename($backupFile);

            if (!file_exists($filepath)) {
                throw new \Exception('Backup file not found');
            }

            // Decompress if needed
            if (pathinfo($filepath, PATHINFO_EXTENSION) === 'gz') {
                $decompressed = $this->decompressFile($filepath);
                $filepath = $decompressed;
            }

            // Get database credentials
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? 'nautilus';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            // Build mysql import command
            $cmd = sprintf(
                'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            // Execute restore
            exec($cmd . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Database restore failed: ' . implode("\n", $output));
            }

            // Clean up decompressed file if it was created
            if (isset($decompressed) && file_exists($decompressed)) {
                unlink($decompressed);
            }

            $this->logger->info('Database restored successfully', ['backup_file' => $backupFile]);

            return [
                'success' => true,
                'message' => 'Database restored successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Database restore failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clean up old backups based on retention policy
     */
    public function cleanupOldBackups(): array
    {
        try {
            $cutoffDate = strtotime("-{$this->config['retention_days']} days");
            $deleted = 0;
            $freedSpace = 0;

            $files = glob($this->backupPath . '/*');

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffDate) {
                    $filesize = filesize($file);
                    if (unlink($file)) {
                        $deleted++;
                        $freedSpace += $filesize;

                        $this->logger->info('Deleted old backup', [
                            'filename' => basename($file),
                            'age_days' => floor((time() - filemtime($file)) / 86400)
                        ]);
                    }
                }
            }

            return [
                'success' => true,
                'deleted_count' => $deleted,
                'freed_space' => $freedSpace,
                'freed_space_human' => $this->formatBytes($freedSpace)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Backup cleanup failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * List all available backups
     */
    public function listBackups(??int $tenantId = null): array
    {
        try {
            $pattern = $tenantId
                ? $this->backupPath . "/tenant_{$tenantId}_*"
                : $this->backupPath . '/*';

            $files = glob($pattern);
            $backups = [];

            foreach ($files as $file) {
                if (is_file($file)) {
                    $backups[] = [
                        'filename' => basename($file),
                        'filepath' => $file,
                        'filesize' => filesize($file),
                        'filesize_human' => $this->formatBytes(filesize($file)),
                        'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                        'age_days' => floor((time() - filemtime($file)) / 86400),
                        'type' => $this->detectBackupType(basename($file))
                    ];
                }
            }

            // Sort by creation date (newest first)
            usort($backups, function($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            return [
                'success' => true,
                'backups' => $backups,
                'total_count' => count($backups),
                'total_size' => array_sum(array_column($backups, 'filesize'))
            ];

        } catch (\Exception $e) {
            $this->logger->error('List backups failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup(string $filename): void
    {
        $filepath = $this->backupPath . '/' . basename($filename);

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo json_encode(['error' => 'Backup file not found']);
            return;
        }

        // Determine content type
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $contentType = $extension === 'zip' ? 'application/zip' : 'application/gzip';

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filepath));

        readfile($filepath);
        exit;
    }

    /**
     * Compress file using gzip
     */
    private function compressFile(string $filepath): string
    {
        $compressedPath = $filepath . '.gz';

        $input = fopen($filepath, 'rb');
        $output = gzopen($compressedPath, 'wb9'); // Maximum compression

        while (!feof($input)) {
            gzwrite($output, fread($input, 8192));
        }

        fclose($input);
        gzclose($output);

        return $compressedPath;
    }

    /**
     * Decompress gzip file
     */
    private function decompressFile(string $filepath): string
    {
        $decompressedPath = str_replace('.gz', '', $filepath);

        $input = gzopen($filepath, 'rb');
        $output = fopen($decompressedPath, 'wb');

        while (!gzeof($input)) {
            fwrite($output, gzread($input, 8192));
        }

        gzclose($input);
        fclose($output);

        return $decompressedPath;
    }

    /**
     * Add directory to ZIP archive recursively
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $directory, string $localPath = ''): int
    {
        $count = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $localPath . '/' . substr($filePath, strlen($directory) + 1);
                $zip->addFile($filePath, $relativePath);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get directories to backup
     */
    private function getBackupDirectories(?int $tenantId): array
    {
        $basePath = __DIR__ . '/../../../';

        if ($tenantId) {
            // Tenant-specific directories
            return [
                $basePath . 'public/uploads/tenant_' . $tenantId,
                $basePath . 'storage/tenant_' . $tenantId
            ];
        } else {
            // All uploads
            return [
                $basePath . 'public/uploads',
                $basePath . 'storage/uploads'
            ];
        }
    }

    /**
     * Get tenant tables filter for mysqldump
     */
    private function getTenantTablesFilter(int $tenantId): string
    {
        // This would filter tables to only include tenant-specific data
        // For now, we'll backup everything and filter during restore if needed
        return "--where=\"tenant_id={$tenantId}\"";
    }

    /**
     * Detect backup type from filename
     */
    private function detectBackupType(string $filename): string
    {
        if (strpos($filename, '_db_') !== false) {
            return 'database';
        } elseif (strpos($filename, '_files_') !== false) {
            return 'files';
        } else {
            return 'unknown';
        }
    }

    /**
     * Format bytes to human readable size
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Log backup to database
     */
    private function logBackup(array $data): void
    {
        try {
            TenantDatabase::insertTenant('backup_log', [
                'backup_type' => $data['backup_type'],
                'filename' => $data['filename'] ?? null,
                'filepath' => $data['filepath'] ?? null,
                'filesize' => $data['filesize'] ?? null,
                'status' => $data['status'],
                'error_message' => $data['error_message'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Log to file if database insert fails
            $this->logger->error('Failed to log backup', ['error' => $e->getMessage()]);
        }
    }
}
