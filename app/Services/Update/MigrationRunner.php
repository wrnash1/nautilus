<?php

namespace App\Services\Update;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Migration Runner Service
 * 
 * Automatically detects and runs pending database migrations
 */
class MigrationRunner
{
    private $db;
    private $migrationDir;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationDir = BASE_PATH . '/database/migrations';
    }
    
    /**
     * Get all migration files
     */
    private function getAllMigrationFiles(): array
    {
        if (!is_dir($this->migrationDir)) {
            return [];
        }
        
        $files = scandir($this->migrationDir);
        $migrations = [];
        
        foreach ($files as $file) {
            if (preg_match('/^\d+_.*\.sql$/', $file)) {
                $migrations[] = $file;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Get pending migrations
     */
    public function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $completedMigrations = $this->getCompletedMigrations();
        
        return array_diff($allMigrations, $completedMigrations);
    }
    
    /**
     * Get completed migrations
     */
    private function getCompletedMigrations(): array
    {
        $stmt = $this->db->query("
            SELECT filename FROM migrations 
            WHERE status = 'completed'
        ");
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Run all pending migrations
     */
    public function runPendingMigrations(): array
    {
        $pending = $this->getPendingMigrations();
        $results = [];
        
        foreach ($pending as $migration) {
            $results[$migration] = $this->runMigration($migration);
        }
        
        return $results;
    }
    
    /**
     * Run single migration
     */
    public function runMigration(string $filename): bool
    {
        try {
            $filePath = $this->migrationDir . '/' . $filename;
            
            if (!file_exists($filePath)) {
                throw new Exception("Migration file not found: {$filename}");
            }
            
            // Read SQL file
            $sql = file_get_contents($filePath);
            
            // Execute SQL
            $this->db->exec($sql);
            
            // Record migration
            $this->recordMigration($filename, 'completed');
            
            return true;
            
        } catch (Exception $e) {
            error_log("Migration failed ({$filename}): " . $e->getMessage());
            $this->recordMigration($filename, 'failed', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record migration status
     */
    private function recordMigration(string $filename, string $status, ?string $errorMessage = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO migrations (filename, status, error_message, executed_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status),
                error_message = VALUES(error_message),
                executed_at = NOW()
        ");
        
        $stmt->execute([$filename, $status, $errorMessage]);
    }
}
