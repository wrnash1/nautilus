<?php

namespace App\Services\Audit;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Audit Trail Service
 *
 * Complete audit logging and trail viewing system
 */
class AuditTrailService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Log an audit event
     */
    public function logEvent(array $data): array
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();
            $userId = $_SESSION['user_id'] ?? null;

            $auditId = TenantDatabase::insertTenant('audit_log', [
                'user_id' => $userId,
                'action' => $data['action'],
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'] ?? null,
                'old_values' => isset($data['old_values']) ? json_encode($data['old_values']) : null,
                'new_values' => isset($data['new_values']) ? json_encode($data['new_values']) : null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'additional_data' => isset($data['additional_data']) ? json_encode($data['additional_data']) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'audit_id' => $auditId
            ];

        } catch (\Exception $e) {
            $this->logger->error('Log audit event failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get audit trail with filters
     */
    public function getAuditTrail(array $filters = []): array
    {
        try {
            $where = ["1=1"];
            $params = [];

            // User filter
            if (!empty($filters['user_id'])) {
                $where[] = "al.user_id = ?";
                $params[] = $filters['user_id'];
            }

            // Action filter
            if (!empty($filters['action'])) {
                $where[] = "al.action = ?";
                $params[] = $filters['action'];
            }

            // Entity type filter
            if (!empty($filters['entity_type'])) {
                $where[] = "al.entity_type = ?";
                $params[] = $filters['entity_type'];
            }

            // Entity ID filter
            if (!empty($filters['entity_id'])) {
                $where[] = "al.entity_id = ?";
                $params[] = $filters['entity_id'];
            }

            // Date range
            if (!empty($filters['date_from'])) {
                $where[] = "DATE(al.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "DATE(al.created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            // IP address filter
            if (!empty($filters['ip_address'])) {
                $where[] = "al.ip_address = ?";
                $params[] = $filters['ip_address'];
            }

            $whereClause = implode(' AND ', $where);
            $limit = $filters['limit'] ?? 100;
            $offset = $filters['offset'] ?? 0;

            $auditLogs = TenantDatabase::fetchAllTenant(
                "SELECT
                    al.*,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    u.email as user_email
                 FROM audit_log al
                 LEFT JOIN users u ON al.user_id = u.id
                 WHERE {$whereClause}
                 ORDER BY al.created_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            ) ?? [];

            // Get total count
            $totalResult = TenantDatabase::fetchOneTenant(
                "SELECT COUNT(*) as total FROM audit_log al WHERE {$whereClause}",
                $params
            );

            // Parse JSON fields
            foreach ($auditLogs as &$log) {
                $log['old_values'] = json_decode($log['old_values'] ?? '{}', true);
                $log['new_values'] = json_decode($log['new_values'] ?? '{}', true);
                $log['additional_data'] = json_decode($log['additional_data'] ?? '{}', true);
            }

            return [
                'success' => true,
                'audit_logs' => $auditLogs,
                'total' => $totalResult['total'] ?? 0,
                'limit' => $limit,
                'offset' => $offset
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get audit trail failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get audit trail for specific entity
     */
    public function getEntityAuditTrail(string $entityType, int $entityId): array
    {
        return $this->getAuditTrail([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);
    }

    /**
     * Get audit statistics
     */
    public function getAuditStatistics(array $filters = []): array
    {
        try {
            $days = $filters['days'] ?? 30;
            $startDate = date('Y-m-d', strtotime("-{$days} days"));

            // Total events
            $totalEvents = TenantDatabase::fetchOneTenant(
                "SELECT COUNT(*) as total FROM audit_log WHERE created_at >= ?",
                [$startDate]
            );

            // Events by action
            $eventsByAction = TenantDatabase::fetchAllTenant(
                "SELECT action, COUNT(*) as count
                 FROM audit_log
                 WHERE created_at >= ?
                 GROUP BY action
                 ORDER BY count DESC",
                [$startDate]
            ) ?? [];

            // Events by user
            $eventsByUser = TenantDatabase::fetchAllTenant(
                "SELECT
                    al.user_id,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    COUNT(*) as event_count
                 FROM audit_log al
                 LEFT JOIN users u ON al.user_id = u.id
                 WHERE al.created_at >= ?
                 GROUP BY al.user_id
                 ORDER BY event_count DESC
                 LIMIT 10",
                [$startDate]
            ) ?? [];

            // Events by entity type
            $eventsByEntity = TenantDatabase::fetchAllTenant(
                "SELECT entity_type, COUNT(*) as count
                 FROM audit_log
                 WHERE created_at >= ?
                 GROUP BY entity_type
                 ORDER BY count DESC",
                [$startDate]
            ) ?? [];

            // Events over time (daily)
            $eventsOverTime = TenantDatabase::fetchAllTenant(
                "SELECT
                    DATE(created_at) as date,
                    COUNT(*) as event_count
                 FROM audit_log
                 WHERE created_at >= ?
                 GROUP BY DATE(created_at)
                 ORDER BY date",
                [$startDate]
            ) ?? [];

            return [
                'success' => true,
                'period_days' => $days,
                'total_events' => $totalEvents['total'],
                'events_by_action' => $eventsByAction,
                'events_by_user' => $eventsByUser,
                'events_by_entity' => $eventsByEntity,
                'events_over_time' => $eventsOverTime
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get audit statistics failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get security events (logins, password changes, permission changes)
     */
    public function getSecurityEvents(array $filters = []): array
    {
        $securityActions = [
            'user_login',
            'user_logout',
            'login_failed',
            'password_changed',
            'password_reset_requested',
            'user_created',
            'user_deleted',
            'role_assigned',
            'role_removed',
            'permission_granted',
            'permission_revoked',
            'api_key_created',
            'api_key_revoked'
        ];

        $filters['action'] = $securityActions;

        $where = ["al.action IN (" . implode(',', array_fill(0, count($securityActions), '?')) . ")"];
        $params = $securityActions;

        // Date range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        // User filter
        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 100;
        $offset = $filters['offset'] ?? 0;

        try {
            $events = TenantDatabase::fetchAllTenant(
                "SELECT
                    al.*,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name
                 FROM audit_log al
                 LEFT JOIN users u ON al.user_id = u.id
                 WHERE {$whereClause}
                 ORDER BY al.created_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            ) ?? [];

            return [
                'success' => true,
                'events' => $events
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get security events failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get failed login attempts
     */
    public function getFailedLoginAttempts(int $hours = 24): array
    {
        try {
            $startTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

            $attempts = TenantDatabase::fetchAllTenant(
                "SELECT
                    al.ip_address,
                    al.additional_data,
                    COUNT(*) as attempt_count,
                    MAX(al.created_at) as last_attempt
                 FROM audit_log al
                 WHERE al.action = 'login_failed'
                 AND al.created_at >= ?
                 GROUP BY al.ip_address
                 ORDER BY attempt_count DESC",
                [$startTime]
            ) ?? [];

            return [
                'success' => true,
                'hours' => $hours,
                'attempts' => $attempts
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get failed login attempts failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(int $userId, int $days = 30): array
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));

            $activity = TenantDatabase::fetchAllTenant(
                "SELECT
                    DATE(created_at) as date,
                    action,
                    COUNT(*) as count
                 FROM audit_log
                 WHERE user_id = ?
                 AND created_at >= ?
                 GROUP BY DATE(created_at), action
                 ORDER BY date DESC, count DESC",
                [$userId, $startDate]
            ) ?? [];

            // Get most common actions
            $topActions = TenantDatabase::fetchAllTenant(
                "SELECT action, COUNT(*) as count
                 FROM audit_log
                 WHERE user_id = ?
                 AND created_at >= ?
                 GROUP BY action
                 ORDER BY count DESC
                 LIMIT 10",
                [$userId, $startDate]
            ) ?? [];

            return [
                'success' => true,
                'period_days' => $days,
                'activity' => $activity,
                'top_actions' => $topActions
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get user activity summary failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Compare entity values (for viewing changes)
     */
    public function compareValues(array $oldValues, array $newValues): array
    {
        $changes = [];

        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        foreach ($allKeys as $key) {
            $oldValue = $oldValues[$key] ?? null;
            $newValue = $newValues[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'changed' => true
                ];
            }
        }

        return $changes;
    }

    /**
     * Export audit trail to CSV
     */
    public function exportAuditTrail(array $filters = []): array
    {
        try {
            $result = $this->getAuditTrail(array_merge($filters, ['limit' => 10000]));

            if (!$result['success']) {
                return $result;
            }

            $filename = 'audit_trail_' . date('Y-m-d_His') . '.csv';
            $filepath = sys_get_temp_dir() . '/' . $filename;

            $file = fopen($filepath, 'w');

            // Write header
            fputcsv($file, [
                'Date/Time',
                'User',
                'Action',
                'Entity Type',
                'Entity ID',
                'IP Address',
                'Changes'
            ]);

            // Write data
            foreach ($result['audit_logs'] as $log) {
                $changes = '';
                if (!empty($log['old_values']) && !empty($log['new_values'])) {
                    $diff = $this->compareValues($log['old_values'], $log['new_values']);
                    $changesList = [];
                    foreach ($diff as $field => $change) {
                        $changesList[] = "{$field}: {$change['old']} → {$change['new']}";
                    }
                    $changes = implode('; ', $changesList);
                }

                fputcsv($file, [
                    $log['created_at'],
                    $log['user_name'] ?? 'System',
                    $log['action'],
                    $log['entity_type'],
                    $log['entity_id'] ?? '',
                    $log['ip_address'] ?? '',
                    $changes
                ]);
            }

            fclose($file);

            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => $filename
            ];

        } catch (\Exception $e) {
            $this->logger->error('Export audit trail failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete old audit logs (data retention)
     */
    public function cleanupOldLogs(int $retentionDays = 365): array
    {
        try {
            $cutoffDate = date('Y-m-d', strtotime("-{$retentionDays} days"));

            $result = TenantDatabase::queryTenant(
                "DELETE FROM audit_log WHERE created_at < ?",
                [$cutoffDate]
            );

            $deletedCount = $result->rowCount();

            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate,
                'message' => "Deleted {$deletedCount} audit logs older than {$retentionDays} days"
            ];

        } catch (\Exception $e) {
            $this->logger->error('Cleanup old logs failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
