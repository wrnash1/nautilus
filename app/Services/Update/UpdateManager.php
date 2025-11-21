<?php

namespace App\Services\Update;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Update Manager Service
 * 
 * Manages the complete update process including:
 * - Downloading updates
 * - Verifying integrity
 * - Creating backups
 * - Running migrations
 * - Rollback on failure
 */
class UpdateManager
{
    private $db;
    private $backupManager;
    private $migrationRunner;
    private $maintenanceMode;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->backupManager = new BackupManager();
        $this->migrationRunner = new MigrationRunner();
        $this->maintenanceMode = new MaintenanceMode();
    }
    
    /**
     * Get current system version
     */
    public function getCurrentVersion(): string
    {
        $stmt = $this->db->prepare("
            SELECT version FROM system_version 
            WHERE is_current = 1 
            ORDER BY installed_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['version'] ?? '1.0.0';
    }
    
    /**
     * Check if update is available
     */
    public function checkForUpdates(): ?array
    {
        // TODO: Implement actual update checking from update server or GitHub
        // For now, return null (no updates)
        
        // Example implementation:
        // $currentVersion = $this->getCurrentVersion();
        // $latestVersion = $this->fetchLatestVersion();
        // 
        // if (version_compare($latestVersion, $currentVersion, '>')) {
        //     return [
        //         'version' => $latestVersion,
        //         'changelog' => $this->fetchChangelog($latestVersion),
        //         'download_url' => $this->getDownloadUrl($latestVersion),
        //         'size' => $this->getPackageSize($latestVersion),
        //         'checksum' => $this->getPackageChecksum($latestVersion)
        //     ];
        // }
        
        return null;
    }
    
    /**
     * Start update process
     * 
     * @param array $updateInfo Update information
     * @param int $userId User performing the update
     * @return int Update ID
     */
    public function startUpdate(array $updateInfo, int $userId): int
    {
        try {
            // Create update record
            $stmt = $this->db->prepare("
                INSERT INTO system_updates 
                (version, previous_version, status, update_package_checksum, changelog, updated_by)
                VALUES (?, ?, 'pending', ?, ?, ?)
            ");
            
            $currentVersion = $this->getCurrentVersion();
            
            $stmt->execute([
                $updateInfo['version'],
                $currentVersion,
                $updateInfo['checksum'] ?? null,
                $updateInfo['changelog'] ?? null,
                $userId
            ]);
            
            return (int) $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Failed to start update: " . $e->getMessage());
            throw new Exception("Failed to start update: " . $e->getMessage());
        }
    }
    
    /**
     * Perform update
     * 
     * @param int $updateId Update ID
     * @param string $packagePath Path to update package
     * @return bool Success status
     */
    public function performUpdate(int $updateId, string $packagePath): bool
    {
        try {
            // Update status to in_progress
            $this->updateStatus($updateId, 'in_progress');
            
            // Step 1: Enable maintenance mode
            $this->maintenanceMode->enable('System update in progress');
            
            // Step 2: Create backup
            $backupId = $this->backupManager->createFullBackup('pre_update', $updateId);
            
            // Link backup to update
            $this->linkBackup($updateId, $backupId);
            
            // Step 3: Extract update package
            $extractPath = $this->extractPackage($packagePath);
            
            // Step 4: Verify package integrity
            if (!$this->verifyPackage($extractPath)) {
                throw new Exception("Package integrity verification failed");
            }
            
            // Step 5: Copy files
            $this->copyFiles($extractPath);
            
            // Step 6: Run migrations
            $this->migrationRunner->runPendingMigrations();
            
            // Step 7: Clear cache
            $this->clearCache();
            
            // Step 8: Update version
            $this->updateVersion($updateId);
            
            // Step 9: Disable maintenance mode
            $this->maintenanceMode->disable();
            
            // Step 10: Mark update as completed
            $this->updateStatus($updateId, 'completed');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Update failed: " . $e->getMessage());
            
            // Rollback
            $this->rollback($updateId);
            
            // Update status to failed
            $this->updateStatus($updateId, 'failed', $e->getMessage());
            
            // Disable maintenance mode
            $this->maintenanceMode->disable();
            
            return false;
        }
    }
    
    /**
     * Rollback update
     */
    public function rollback(int $updateId): bool
    {
        try {
            // Get backup ID
            $stmt = $this->db->prepare("SELECT backup_id FROM system_updates WHERE id = ?");
            $stmt->execute([$updateId]);
            $update = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$update || !$update['backup_id']) {
                throw new Exception("No backup found for rollback");
            }
            
            // Restore from backup
            $this->backupManager->restore($update['backup_id']);
            
            // Update status
            $this->updateStatus($updateId, 'rolled_back');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Rollback failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update status
     */
    private function updateStatus(int $updateId, string $status, ?string $errorMessage = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE system_updates 
            SET status = ?, 
                error_message = ?,
                " . ($status === 'in_progress' ? 'started_at = NOW(),' : '') . "
                " . ($status === 'completed' ? 'completed_at = NOW(),' : '') . "
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$status, $errorMessage, $updateId]);
    }
    
    /**
     * Link backup to update
     */
    private function linkBackup(int $updateId, int $backupId): void
    {
        $stmt = $this->db->prepare("UPDATE system_updates SET backup_id = ? WHERE id = ?");
        $stmt->execute([$backupId, $updateId]);
    }
    
    /**
     * Extract update package
     */
    private function extractPackage(string $packagePath): string
    {
        $extractPath = BASE_PATH . '/storage/updates/extracted_' . time();
        
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }
        
        // Extract ZIP file
        $zip = new \ZipArchive();
        if ($zip->open($packagePath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
        } else {
            throw new Exception("Failed to extract update package");
        }
        
        return $extractPath;
    }
    
    /**
     * Verify package integrity
     */
    private function verifyPackage(string $extractPath): bool
    {
        // Check for required files
        $requiredFiles = ['version.json', 'files.json'];
        
        foreach ($requiredFiles as $file) {
            if (!file_exists($extractPath . '/' . $file)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Copy files from update package
     */
    private function copyFiles(string $extractPath): void
    {
        $filesJson = file_get_contents($extractPath . '/files.json');
        $files = json_decode($filesJson, true);
        
        foreach ($files as $file) {
            $source = $extractPath . '/' . $file['path'];
            $destination = BASE_PATH . '/' . $file['path'];
            
            // Create directory if needed
            $dir = dirname($destination);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            // Copy file
            if (!copy($source, $destination)) {
                throw new Exception("Failed to copy file: " . $file['path']);
            }
        }
    }
    
    /**
     * Clear application cache
     */
    private function clearCache(): void
    {
        // Clear various caches
        $cacheDirs = [
            BASE_PATH . '/storage/cache',
            BASE_PATH . '/storage/views',
        ];
        
        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $this->deleteDirectory($dir);
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Update system version
     */
    private function updateVersion(int $updateId): void
    {
        // Get update info
        $stmt = $this->db->prepare("SELECT version FROM system_updates WHERE id = ?");
        $stmt->execute([$updateId]);
        $update = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$update) {
            return;
        }
        
        // Mark all versions as not current
        $this->db->exec("UPDATE system_version SET is_current = 0");
        
        // Insert new version
        $stmt = $this->db->prepare("
            INSERT INTO system_version (version, is_current, installed_by)
            VALUES (?, 1, (SELECT updated_by FROM system_updates WHERE id = ?))
        ");
        $stmt->execute([$update['version'], $updateId]);
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
     * Get update history
     */
    public function getUpdateHistory(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.*,
                v.version as current_version,
                usr.name as updated_by_name
            FROM system_updates u
            LEFT JOIN system_version v ON v.is_current = 1
            LEFT JOIN users usr ON u.updated_by = usr.id
            ORDER BY u.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
