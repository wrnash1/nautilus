<?php

namespace App\Services\Update;

use App\Core\Database;
use PDO;

/**
 * Maintenance Mode Service
 * 
 * Manages application maintenance mode during updates
 */
class MaintenanceMode
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Enable maintenance mode
     */
    public function enable(string $message = 'System is under maintenance', string $reason = 'update'): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $stmt = $this->db->prepare("
            UPDATE maintenance_mode 
            SET is_enabled = 1,
                message = ?,
                reason = ?,
                enabled_at = NOW(),
                enabled_by = ?,
                updated_at = NOW()
            WHERE id = 1
        ");
        
        $stmt->execute([$message, $reason, $userId]);
        
        // Create maintenance flag file
        file_put_contents(BASE_PATH . '/.maintenance', json_encode([
            'enabled_at' => date('Y-m-d H:i:s'),
            'message' => $message,
            'reason' => $reason
        ]));
    }
    
    /**
     * Disable maintenance mode
     */
    public function disable(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        $stmt = $this->db->prepare("
            UPDATE maintenance_mode 
            SET is_enabled = 0,
                disabled_at = NOW(),
                disabled_by = ?,
                updated_at = NOW()
            WHERE id = 1
        ");
        
        $stmt->execute([$userId]);
        
        // Remove maintenance flag file
        $maintenanceFile = BASE_PATH . '/.maintenance';
        if (file_exists($maintenanceFile)) {
            unlink($maintenanceFile);
        }
    }
    
    /**
     * Check if maintenance mode is enabled
     */
    public function isEnabled(): bool
    {
        // Check file first (faster)
        if (file_exists(BASE_PATH . '/.maintenance')) {
            return true;
        }
        
        // Check database
        $stmt = $this->db->query("SELECT is_enabled FROM maintenance_mode WHERE id = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['is_enabled'];
    }
    
    /**
     * Get maintenance mode info
     */
    public function getInfo(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM maintenance_mode WHERE id = 1");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Check if IP is allowed during maintenance
     */
    public function isIpAllowed(string $ip): bool
    {
        $info = $this->getInfo();
        
        if (!$info || !$info['allowed_ips']) {
            return false;
        }
        
        $allowedIps = json_decode($info['allowed_ips'], true) ?: [];
        return in_array($ip, $allowedIps);
    }
    
    /**
     * Add allowed IP
     */
    public function addAllowedIp(string $ip): void
    {
        $info = $this->getInfo();
        $allowedIps = $info && $info['allowed_ips'] ? json_decode($info['allowed_ips'], true) : [];
        
        if (!in_array($ip, $allowedIps)) {
            $allowedIps[] = $ip;
        }
        
        $stmt = $this->db->prepare("
            UPDATE maintenance_mode 
            SET allowed_ips = ?,
                updated_at = NOW()
            WHERE id = 1
        ");
        
        $stmt->execute([json_encode($allowedIps)]);
    }
}
