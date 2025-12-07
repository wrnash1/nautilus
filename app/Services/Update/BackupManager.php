<?php

namespace App\Services\Update;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Backup Manager Service
 * 
 * Handles creation and restoration of system backups
 * Supports database backups, file backups, and full system backups
 */
class BackupManager
{
    private $db;
    private $backupDir;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->backupDir = BASE_PATH . '/storage/backups';
        
        // Ensure backup directory exists
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Create full backup (database + critical files)
     * 
     * @param string $type Backup type (full, database, files, pre_update)
     * @param int|null $updateId Related update ID
     * @return int Backup ID
     */
    public function createFullBackup(string $type = 'full', ?int $updateId = null): int
    {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupName = "backup_{$type}_{$timestamp}";
            
            // Create database backup
            $dbBackupPath = $this->createDatabaseBackup($backupName);
            
            // Create files backup (if full backup)
            $filesBackupPath = null;
            if ($type === 'full' || $type === 'pre_update') {
                $filesBackupPath = $this->createFilesBackup($backupName);
            }
            
            // Combine into single archive
            $finalBackupPath = $this->combineBackups($backupName, $dbBackupPath, $filesBackupPath);
            
            // Calculate file size and checksum
            $fileSize = filesize($finalBackupPath);
            $checksum = hash_file('sha256', $finalBackupPath);
            
            // Record backup in database
            $backupId = $this->recordBackup([
                'backup_type' => $type,
                'file_path' => $finalBackupPath,
                'file_size' => $fileSize,
                'checksum' => $checksum,
                'compression_type' => 'gzip',
                'notes' => $updateId ? "Pre-update backup for update ID: {$updateId}" : null
            ]);
            
            // Clean up temporary files
            if (file_exists($dbBackupPath)) unlink($dbBackupPath);
            if ($filesBackupPath && file_exists($filesBackupPath)) unlink($filesBackupPath);
            
            return $backupId;
            
        } catch (Exception $e) {
            error_log("Backup creation failed: " . $e->getMessage());
            throw new Exception("Failed to create backup: " . $e->getMessage());
        }
    }
    
    /**
     * Create database backup
     */
    private function createDatabaseBackup(string $backupName): string
    {
        $backupFile = $this->backupDir . "/{$backupName}_db.sql";
        
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'nautilus';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        
        // Use mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($dbname),
            escapeshellarg($backupFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Database backup failed: " . implode("\n", $output));
        }
        
        // Compress the SQL file
        $compressedFile = $backupFile . '.gz';
        $this->compressFile($backupFile, $compressedFile);
        
        return $compressedFile;
    }
    
    /**
     * Create files backup (critical application files)
     */
    private function createFilesBackup(string $backupName): string
    {
        $backupFile = $this->backupDir . "/{$backupName}_files.tar.gz";
        
        // Critical directories to backup
        $criticalDirs = [
            'app',
            'config',
            'public',
            'routes',
            'database/migrations',
            '.env'
        ];
        
        // Create tar archive
        $files = [];
        foreach ($criticalDirs as $dir) {
            $path = BASE_PATH . '/' . $dir;
            if (file_exists($path)) {
                $files[] = $dir;
            }
        }
        
        if (empty($files)) {
            throw new Exception("No files to backup");
        }
        
        // Create tar.gz archive
        $command = sprintf(
            'cd %s && tar -czf %s %s 2>&1',
            escapeshellarg(BASE_PATH),
            escapeshellarg($backupFile),
            implode(' ', array_map('escapeshellarg', $files))
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Files backup failed: " . implode("\n", $output));
        }
        
        return $backupFile;
    }
    
    /**
     * Combine database and files backups into single archive
     */
    private function combineBackups(string $backupName, string $dbBackup, ?string $filesBackup): string
    {
        $finalBackup = $this->backupDir . "/{$backupName}_complete.tar.gz";
        
        $files = [$dbBackup];
        if ($filesBackup) {
            $files[] = $filesBackup;
        }
        
        // Create combined archive
        $command = sprintf(
            'tar -czf %s -C %s %s 2>&1',
            escapeshellarg($finalBackup),
            escapeshellarg($this->backupDir),
            implode(' ', array_map(function($f) {
                return escapeshellarg(basename($f));
            }, $files))
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Failed to combine backups: " . implode("\n", $output));
        }
        
        return $finalBackup;
    }
    
    /**
     * Compress file using gzip
     */
    private function compressFile(string $source, string $destination): void
    {
        $sourceHandle = fopen($source, 'rb');
        $destHandle = gzopen($destination, 'wb9');
        
        if (!$sourceHandle || !$destHandle) {
            throw new Exception("Failed to open files for compression");
        }
        
        while (!feof($sourceHandle)) {
            gzwrite($destHandle, fread($sourceHandle, 1024 * 512));
        }
        
        fclose($sourceHandle);
        gzclose($destHandle);
        
        // Remove original file
        unlink($source);
    }
    
    /**
     * Record backup in database
     */
    private function recordBackup(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO system_backups 
            (backup_type, file_path, file_size, checksum, compression_type, created_by, notes, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
        ");
        
        $userId = $_SESSION['user_id'] ?? null;
        
        $stmt->execute([
            $data['backup_type'],
            $data['file_path'],
            $data['file_size'],
            $data['checksum'],
            $data['compression_type'] ?? 'gzip',
            $userId,
            $data['notes'] ?? null
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Restore from backup
     */
    public function restore(int $backupId): bool
    {
        try {
            // Get backup info
            $stmt = $this->db->prepare("SELECT * FROM system_backups WHERE id = ?");
            $stmt->execute([$backupId]);
            $backup = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$backup) {
                throw new Exception("Backup not found");
            }
            
            if (!file_exists($backup['file_path'])) {
                throw new Exception("Backup file not found: " . $backup['file_path']);
            }
            
            // Extract backup
            $extractDir = $this->backupDir . '/restore_' . time();
            mkdir($extractDir, 0755, true);
            
            $command = sprintf(
                'tar -xzf %s -C %s 2>&1',
                escapeshellarg($backup['file_path']),
                escapeshellarg($extractDir)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new Exception("Failed to extract backup: " . implode("\n", $output));
            }
            
            // Restore database
            $this->restoreDatabase($extractDir);
            
            // Restore files (if full backup)
            if ($backup['backup_type'] === 'full' || $backup['backup_type'] === 'pre_update') {
                $this->restoreFiles($extractDir);
            }
            
            // Update backup record
            $stmt = $this->db->prepare("UPDATE system_backups SET restored_at = NOW() WHERE id = ?");
            $stmt->execute([$backupId]);
            
            // Clean up
            $this->deleteDirectory($extractDir);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Restore failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Restore database from backup
     */
    private function restoreDatabase(string $extractDir): void
    {
        // Find database backup file
        $files = glob($extractDir . '/*_db.sql.gz');
        
        if (empty($files)) {
            throw new Exception("Database backup file not found");
        }
        
        $dbBackupFile = $files[0];
        
        // Decompress
        $sqlFile = str_replace('.gz', '', $dbBackupFile);
        $this->decompressFile($dbBackupFile, $sqlFile);
        
        // Restore database
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'nautilus';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($dbname),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Database restore failed: " . implode("\n", $output));
        }
    }
    
    /**
     * Restore files from backup
     */
    private function restoreFiles(string $extractDir): void
    {
        // Find files backup
        $files = glob($extractDir . '/*_files.tar.gz');
        
        if (empty($files)) {
            return; // No files backup
        }
        
        $filesBackup = $files[0];
        
        // Extract to application root
        $command = sprintf(
            'tar -xzf %s -C %s 2>&1',
            escapeshellarg($filesBackup),
            escapeshellarg(BASE_PATH)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("Files restore failed: " . implode("\n", $output));
        }
    }
    
    /**
     * Decompress gzip file
     */
    private function decompressFile(string $source, string $destination): void
    {
        $sourceHandle = gzopen($source, 'rb');
        $destHandle = fopen($destination, 'wb');
        
        if (!$sourceHandle || !$destHandle) {
            throw new Exception("Failed to open files for decompression");
        }
        
        while (!gzeof($sourceHandle)) {
            fwrite($destHandle, gzread($sourceHandle, 1024 * 512));
        }
        
        gzclose($sourceHandle);
        fclose($destHandle);
    }
    
    /**
     * Delete directory recursively
     */
    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        
        return rmdir($dir);
    }
    
    /**
     * Get all backups
     */
    public function getAllBackups(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                b.*,
                u.name as created_by_name
            FROM system_backups b
            LEFT JOIN users u ON b.created_by = u.id
            ORDER BY b.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete old backups
     */
    public function deleteExpiredBackups(): int
    {
        $stmt = $this->db->prepare("
            SELECT id, file_path FROM system_backups 
            WHERE expires_at < NOW() AND restored_at IS NULL
        ");
        $stmt->execute();
        $expiredBackups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $deleted = 0;
        foreach ($expiredBackups as $backup) {
            if (file_exists($backup['file_path'])) {
                unlink($backup['file_path']);
            }
            
            $deleteStmt = $this->db->prepare("DELETE FROM system_backups WHERE id = ?");
            $deleteStmt->execute([$backup['id']]);
            $deleted++;
        }
        
        return $deleted;
    }
}
