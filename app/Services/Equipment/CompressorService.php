<?php

namespace App\Services\Equipment;

use App\Core\Database;
use App\Services\Notifications\NotificationService;
use App\Core\Logger;

/**
 * Compressor Service
 * Manages air compressor tracking, maintenance, and alerts
 */
class CompressorService
{
    private NotificationService $notificationService;
    private Logger $logger;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->logger = new Logger();
    }

    /**
     * Quick add hours to compressor (main use case)
     */
    public function addHours(int $compressorId, float $hoursToAdd, array $data = []): int
    {
        $compressor = $this->getCompressor($compressorId);
        if (!$compressor) {
            throw new \Exception('Compressor not found');
        }

        $hoursBefore = $compressor['current_hours'];
        $hoursAfter = $hoursBefore + $hoursToAdd;

        // Create log entry
        Database::execute(
            "INSERT INTO compressor_logs
            (compressor_id, log_type, hours_before, hours_added, hours_after,
             service_description, performed_by, logged_by, log_date, notes)
            VALUES (?, 'hours_logged', ?, ?, ?, ?, ?, ?, CURDATE(), ?)",
            [
                $compressorId,
                $hoursBefore,
                $hoursToAdd,
                $hoursAfter,
                $data['description'] ?? "Added {$hoursToAdd} hours",
                $data['performed_by'] ?? null,
                $_SESSION['user_id'] ?? 1,
                $data['notes'] ?? null
            ]
        );

        $logId = Database::lastInsertId();

        // Update compressor hours
        Database::execute(
            "UPDATE compressors
            SET current_hours = ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE id = ?",
            [$hoursAfter, $_SESSION['user_id'] ?? 1, $compressorId]
        );

        // Check if maintenance is due
        $this->checkMaintenanceDue($compressorId);

        $this->logger->info('Compressor hours added', [
            'compressor_id' => $compressorId,
            'hours_added' => $hoursToAdd,
            'new_total' => $hoursAfter
        ]);

        return $logId;
    }

    /**
     * Log oil change
     */
    public function logOilChange(int $compressorId, array $data): int
    {
        $compressor = $this->getCompressor($compressorId);
        $currentHours = $compressor['current_hours'];

        // Create log entry
        Database::execute(
            "INSERT INTO compressor_logs
            (compressor_id, log_type, hours_before, hours_after,
             service_description, parts_used, cost, performed_by, logged_by, log_date, notes)
            VALUES (?, 'oil_change', ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $compressorId,
                $currentHours,
                $currentHours,
                $data['description'] ?? 'Oil change performed',
                $data['parts_used'] ?? null,
                $data['cost'] ?? 0,
                $data['performed_by'] ?? null,
                $_SESSION['user_id'] ?? 1,
                $data['log_date'] ?? date('Y-m-d'),
                $data['notes'] ?? null
            ]
        );

        $logId = Database::lastInsertId();

        // Update compressor
        $nextOilChange = $currentHours + $compressor['oil_change_interval_hours'];

        Database::execute(
            "UPDATE compressors
            SET last_oil_change_hours = ?,
                next_oil_change_due_hours = ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE id = ?",
            [$currentHours, $nextOilChange, $_SESSION['user_id'] ?? 1, $compressorId]
        );

        // Dismiss oil change alerts
        $this->dismissAlerts($compressorId, 'oil_change_due');

        $this->logger->info('Compressor oil change logged', [
            'compressor_id' => $compressorId,
            'current_hours' => $currentHours
        ]);

        return $logId;
    }

    /**
     * Log filter change
     */
    public function logFilterChange(int $compressorId, array $data): int
    {
        $compressor = $this->getCompressor($compressorId);
        $currentHours = $compressor['current_hours'];

        Database::execute(
            "INSERT INTO compressor_logs
            (compressor_id, log_type, hours_before, hours_after,
             service_description, parts_used, cost, performed_by, logged_by, log_date, notes)
            VALUES (?, 'filter_change', ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $compressorId,
                $currentHours,
                $currentHours,
                $data['description'] ?? 'Filter change performed',
                $data['parts_used'] ?? null,
                $data['cost'] ?? 0,
                $data['performed_by'] ?? null,
                $_SESSION['user_id'] ?? 1,
                $data['log_date'] ?? date('Y-m-d'),
                $data['notes'] ?? null
            ]
        );

        $logId = Database::lastInsertId();

        // Update next filter change due
        $nextFilterChange = $currentHours + $compressor['filter_change_interval_hours'];

        Database::execute(
            "UPDATE compressors
            SET next_filter_change_due_hours = ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE id = ?",
            [$nextFilterChange, $_SESSION['user_id'] ?? 1, $compressorId]
        );

        $this->dismissAlerts($compressorId, 'filter_change_due');

        return $logId;
    }

    /**
     * Log major service
     */
    public function logMajorService(int $compressorId, array $data): int
    {
        $compressor = $this->getCompressor($compressorId);
        $currentHours = $compressor['current_hours'];

        Database::execute(
            "INSERT INTO compressor_logs
            (compressor_id, log_type, hours_before, hours_after,
             service_description, parts_used, cost, performed_by, logged_by, log_date, notes)
            VALUES (?, 'major_service', ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $compressorId,
                $currentHours,
                $currentHours,
                $data['description'] ?? 'Major service performed',
                $data['parts_used'] ?? null,
                $data['cost'] ?? 0,
                $data['performed_by'] ?? null,
                $_SESSION['user_id'] ?? 1,
                $data['log_date'] ?? date('Y-m-d'),
                $data['notes'] ?? null
            ]
        );

        $logId = Database::lastInsertId();

        // Update service tracking
        $nextService = $currentHours + $compressor['major_service_interval_hours'];

        Database::execute(
            "UPDATE compressors
            SET last_service_date = ?,
                next_service_due_hours = ?,
                updated_at = NOW(),
                updated_by = ?
            WHERE id = ?",
            [$data['log_date'] ?? date('Y-m-d'), $nextService, $_SESSION['user_id'] ?? 1, $compressorId]
        );

        $this->dismissAlerts($compressorId, 'service_due');

        return $logId;
    }

    /**
     * Check if maintenance is due and create alerts
     */
    public function checkMaintenanceDue(int $compressorId): void
    {
        $compressor = $this->getCompressor($compressorId);
        if (!$compressor) {
            return;
        }

        $currentHours = $compressor['current_hours'];

        // Check oil change
        if ($currentHours >= $compressor['next_oil_change_due_hours']) {
            $this->createAlert($compressorId, [
                'alert_type' => 'oil_change_due',
                'severity' => 'warning',
                'message' => "Oil change is due at {$compressor['next_oil_change_due_hours']} hours (current: {$currentHours} hours)"
            ]);
        }

        // Check filter change
        if ($currentHours >= $compressor['next_filter_change_due_hours']) {
            $this->createAlert($compressorId, [
                'alert_type' => 'filter_change_due',
                'severity' => 'warning',
                'message' => "Filter change is due at {$compressor['next_filter_change_due_hours']} hours (current: {$currentHours} hours)"
            ]);
        }

        // Check major service
        if ($currentHours >= $compressor['next_service_due_hours']) {
            $this->createAlert($compressorId, [
                'alert_type' => 'service_due',
                'severity' => 'critical',
                'message' => "Major service is due at {$compressor['next_service_due_hours']} hours (current: {$currentHours} hours)"
            ]);
        }
    }

    /**
     * Create alert
     */
    public function createAlert(int $compressorId, array $alertData): int
    {
        // Check if alert already exists
        $existing = Database::fetchOne(
            "SELECT id FROM compressor_alerts
            WHERE compressor_id = ? AND alert_type = ? AND is_active = TRUE",
            [$compressorId, $alertData['alert_type']]
        );

        if ($existing) {
            return $existing['id']; // Already have active alert
        }

        Database::execute(
            "INSERT INTO compressor_alerts
            (compressor_id, alert_type, severity, message, is_active)
            VALUES (?, ?, ?, ?, TRUE)",
            [
                $compressorId,
                $alertData['alert_type'],
                $alertData['severity'] ?? 'warning',
                $alertData['message']
            ]
        );

        $alertId = Database::lastInsertId();

        // Notify relevant users
        $this->notifyMaintenanceUsers($compressorId, $alertData['message']);

        return $alertId;
    }

    /**
     * Dismiss alerts of a specific type
     */
    private function dismissAlerts(int $compressorId, string $alertType): void
    {
        Database::execute(
            "UPDATE compressor_alerts
            SET is_active = FALSE,
                is_acknowledged = TRUE,
                acknowledged_by = ?,
                acknowledged_at = NOW()
            WHERE compressor_id = ? AND alert_type = ? AND is_active = TRUE",
            [$_SESSION['user_id'] ?? 1, $compressorId, $alertType]
        );
    }

    /**
     * Get compressor by ID
     */
    public function getCompressor(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM compressors WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get all compressors
     */
    public function getAllCompressors(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM compressor_status_dashboard";
        if ($activeOnly) {
            $sql .= " WHERE is_active = TRUE";
        }
        $sql .= " ORDER BY name";

        return Database::fetchAll($sql) ?? [];
    }

    /**
     * Get compressor logs
     */
    public function getCompressorLogs(int $compressorId, int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT cl.*, CONCAT(u.first_name, ' ', u.last_name) as logged_by_name
            FROM compressor_logs cl
            LEFT JOIN users u ON cl.logged_by = u.id
            WHERE cl.compressor_id = ?
            ORDER BY cl.log_date DESC, cl.logged_at DESC
            LIMIT ?",
            [$compressorId, $limit]
        ) ?? [];
    }

    /**
     * Get active alerts
     */
    public function getActiveAlerts(?int $compressorId = null): array
    {
        $sql = "SELECT ca.*, c.name as compressor_name
                FROM compressor_alerts ca
                LEFT JOIN compressors c ON ca.compressor_id = c.id
                WHERE ca.is_active = TRUE";

        $params = [];
        if ($compressorId) {
            $sql .= " AND ca.compressor_id = ?";
            $params[] = $compressorId;
        }

        $sql .= " ORDER BY ca.severity DESC, ca.created_at DESC";

        return Database::fetchAll($sql, $params) ?? [];
    }

    /**
     * Get compressors needing maintenance
     */
    public function getCompressorsNeedingMaintenance(): array
    {
        return Database::fetchAll(
            "SELECT * FROM compressor_status_dashboard
            WHERE (oil_change_status IN ('Overdue', 'Due Soon')
                   OR hours_until_filter_change <= 10
                   OR days_until_service <= 7)
              AND is_active = TRUE
              AND is_operational = TRUE
            ORDER BY oil_change_status DESC, hours_until_oil_change ASC"
        ) ?? [];
    }

    /**
     * Notify maintenance users
     */
    private function notifyMaintenanceUsers(int $compressorId, string $message): void
    {
        $compressor = $this->getCompressor($compressorId);

        // Get admin and manager users
        $users = Database::fetchAll(
            "SELECT id FROM users
            WHERE role IN ('admin', 'manager') AND is_active = TRUE"
        ) ?? [];

        foreach ($users as $user) {
            $this->notificationService->create(
                $user['id'],
                'Compressor Maintenance Alert',
                $compressor['name'] . ': ' . $message,
                'warning',
                '/equipment/compressors/' . $compressorId
            );
        }
    }

    /**
     * Get maintenance summary for dashboard
     */
    public function getMaintenanceSummary(): array
    {
        $compressors = $this->getAllCompressors();
        $alerts = $this->getActiveAlerts();

        return [
            'total_compressors' => count($compressors),
            'operational' => count(array_filter($compressors, fn($c) => $c['is_operational'])),
            'active_alerts' => count($alerts),
            'critical_alerts' => count(array_filter($alerts, fn($a) => $a['severity'] === 'critical')),
            'needing_maintenance' => count($this->getCompressorsNeedingMaintenance())
        ];
    }
}
