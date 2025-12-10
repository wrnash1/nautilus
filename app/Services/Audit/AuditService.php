<?php

namespace App\Services\Audit;

use App\Core\Database;
use PDO;
use App\Core\Logger;

/**
 * Audit Service
 * Handles activity logging and audit trails
 */
class AuditService
{
    private PDO $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getPdo();
        $this->logger = new Logger();
    }

    /**
     * Log an activity
     */
    public function log(
        int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): int {
        try {
            $sql = "INSERT INTO audit_logs (
                        user_id, action, entity_type, entity_id,
                        old_values, new_values, ip_address, user_agent, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $action,
                $entityType,
                $entityId,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null,
                $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null,
                $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            $this->logger->error('Failed to create audit log', [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get audit logs with filters
     */
    public function getLogs(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT al.*,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.email as user_email
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND al.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['entity_id'])) {
            $sql .= " AND al.entity_id = ?";
            $params[] = $filters['entity_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(al.created_at) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(al.created_at) <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Parse JSON values
        foreach ($logs as &$log) {
            if ($log['old_values']) {
                $log['old_values'] = json_decode($log['old_values'], true);
            }
            if ($log['new_values']) {
                $log['new_values'] = json_decode($log['new_values'], true);
            }
        }

        return $logs;
    }

    /**
     * Get total count of logs matching filters
     */
    public function getCount(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM audit_logs WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['entity_type'])) {
            $sql .= " AND entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Get audit log by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT al.*,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name,
                       u.email as user_email
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $log = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($log) {
            if ($log['old_values']) {
                $log['old_values'] = json_decode($log['old_values'], true);
            }
            if ($log['new_values']) {
                $log['new_values'] = json_decode($log['new_values'], true);
            }
        }

        return $log ?: null;
    }

    /**
     * Get audit history for a specific entity
     */
    public function getEntityHistory(string $entityType, int $entityId, int $limit = 50): array
    {
        $sql = "SELECT al.*,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity_type = ? AND al.entity_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$entityType, $entityId, $limit]);

        $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            if ($log['old_values']) {
                $log['old_values'] = json_decode($log['old_values'], true);
            }
            if ($log['new_values']) {
                $log['new_values'] = json_decode($log['new_values'], true);
            }
        }

        return $logs;
    }

    /**
     * Get unique actions
     */
    public function getActions(): array
    {
        $sql = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'action');
    }

    /**
     * Get unique entity types
     */
    public function getEntityTypes(): array
    {
        $sql = "SELECT DISTINCT entity_type FROM audit_logs ORDER BY entity_type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'entity_type');
    }

    /**
     * Get activity summary by user
     */
    public function getUserActivity(string $startDate, string $endDate): array
    {
        $sql = "SELECT
                    u.id,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    COUNT(*) as activity_count,
                    COUNT(DISTINCT DATE(al.created_at)) as active_days,
                    MAX(al.created_at) as last_activity
                FROM audit_logs al
                JOIN users u ON al.user_id = u.id
                WHERE DATE(al.created_at) BETWEEN ? AND ?
                GROUP BY u.id, u.first_name, u.last_name
                ORDER BY activity_count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get activity summary by action
     */
    public function getActionSummary(string $startDate, string $endDate): array
    {
        $sql = "SELECT
                    action,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM audit_logs
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY action
                ORDER BY count DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete old audit logs (data retention)
     */
    public function deleteOldLogs(int $daysOld = 365): int
    {
        try {
            $sql = "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$daysOld]);

            $count = $stmt->rowCount();

            $this->logger->info('Old audit logs deleted', ['count' => $count, 'days_old' => $daysOld]);

            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete old logs', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Helper methods for common actions
     */

    public function logCreate(int $userId, string $entityType, int $entityId, array $values): int
    {
        return $this->log($userId, 'create', $entityType, $entityId, null, $values);
    }

    public function logUpdate(int $userId, string $entityType, int $entityId, array $oldValues, array $newValues): int
    {
        return $this->log($userId, 'update', $entityType, $entityId, $oldValues, $newValues);
    }

    public function logDelete(int $userId, string $entityType, int $entityId, array $oldValues): int
    {
        return $this->log($userId, 'delete', $entityType, $entityId, $oldValues, null);
    }

    public function logLogin(int $userId): int
    {
        return $this->log($userId, 'login', 'auth', null);
    }

    public function logLogout(int $userId): int
    {
        return $this->log($userId, 'logout', 'auth', null);
    }

    public function logView(int $userId, string $entityType, int $entityId): int
    {
        return $this->log($userId, 'view', $entityType, $entityId);
    }

    public function logExport(int $userId, string $entityType, array $filters = []): int
    {
        return $this->log($userId, 'export', $entityType, null, null, $filters);
    }
}
