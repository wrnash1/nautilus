<?php

namespace App\Services\Equipment;

use PDO;

/**
 * Equipment Maintenance Service
 *
 * Manages equipment maintenance schedules, service history, and inspections
 */
class MaintenanceService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    /**
     * Get equipment requiring maintenance
     */
    public function getEquipmentNeedingMaintenance(): array
    {
        $stmt = $this->db->query(
            "SELECT re.*, ret.name as equipment_type,
                    CASE
                        WHEN re.next_inspection_due < date('now') THEN 'overdue'
                        WHEN re.next_inspection_due <= date('now', '+7 days') THEN 'due_soon'
                        ELSE 'scheduled'
                    END as urgency
             FROM rental_equipment re
             LEFT JOIN rental_equipment_types ret ON re.equipment_type_id = ret.id
             WHERE re.status != 'retired'
             AND (
                 re.next_inspection_due IS NOT NULL
                 AND re.next_inspection_due <= date('now', '+30 days')
             )
             ORDER BY re.next_inspection_due ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get maintenance history for equipment
     */
    public function getMaintenanceHistory(int $equipmentId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT em.*, u.first_name, u.last_name
             FROM equipment_maintenance em
             LEFT JOIN users u ON em.performed_by = u.id
             WHERE em.equipment_id = ?
             ORDER BY em.maintenance_date DESC
             LIMIT ?"
        );
        $stmt->execute([$equipmentId, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record maintenance activity
     */
    public function recordMaintenance(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO equipment_maintenance
             (equipment_id, maintenance_type, maintenance_date, performed_by, description,
              parts_replaced, cost, next_service_date, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))"
        );

        $stmt->execute([
            $data['equipment_id'],
            $data['maintenance_type'],
            $data['maintenance_date'],
            $data['performed_by'],
            $data['description'] ?? null,
            $data['parts_replaced'] ?? null,
            $data['cost'] ?? 0,
            $data['next_service_date'] ?? null
        ]);

        $maintenanceId = (int)$this->db->lastInsertId();

        // Update equipment status and next inspection date
        $this->updateEquipmentAfterMaintenance(
            $data['equipment_id'],
            $data['next_service_date'] ?? null,
            $data['maintenance_type']
        );

        return $maintenanceId;
    }

    /**
     * Schedule maintenance
     */
    public function scheduleMaintenance(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO maintenance_schedules
             (equipment_id, scheduled_date, maintenance_type, assigned_to, notes, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'scheduled', datetime('now'))"
        );

        $stmt->execute([
            $data['equipment_id'],
            $data['scheduled_date'],
            $data['maintenance_type'],
            $data['assigned_to'] ?? null,
            $data['notes'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get scheduled maintenance
     */
    public function getScheduledMaintenance(??string $status = null, ??int $limit = null): array
    {
        $sql = "SELECT ms.*, re.name as equipment_name, re.equipment_code,
                       u.first_name, u.last_name
                FROM maintenance_schedules ms
                JOIN rental_equipment re ON ms.equipment_id = re.id
                LEFT JOIN users u ON ms.assigned_to = u.id";

        $params = [];

        if ($status) {
            $sql .= " WHERE ms.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ms.scheduled_date ASC";

        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Complete scheduled maintenance
     */
    public function completeScheduledMaintenance(int $scheduleId, array $maintenanceData): bool
    {
        try {
            $this->db->beginTransaction();

            // Get schedule details
            $stmt = $this->db->prepare("SELECT * FROM maintenance_schedules WHERE id = ?");
            $stmt->execute([$scheduleId]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$schedule) {
                throw new \Exception("Schedule not found");
            }

            // Record maintenance
            $maintenanceData['equipment_id'] = $schedule['equipment_id'];
            $maintenanceData['maintenance_type'] = $schedule['maintenance_type'];
            $maintenanceId = $this->recordMaintenance($maintenanceData);

            // Update schedule status
            $stmt = $this->db->prepare(
                "UPDATE maintenance_schedules
                 SET status = 'completed', completed_at = datetime('now'), maintenance_id = ?
                 WHERE id = ?"
            );
            $stmt->execute([$maintenanceId, $scheduleId]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to complete maintenance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get maintenance statistics
     */
    public function getStatistics(): array
    {
        // Overdue inspections
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM rental_equipment
             WHERE status != 'retired'
             AND next_inspection_due < date('now')"
        );
        $overdue = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Due soon (within 7 days)
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM rental_equipment
             WHERE status != 'retired'
             AND next_inspection_due >= date('now')
             AND next_inspection_due <= date('now', '+7 days')"
        );
        $dueSoon = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Equipment in maintenance
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM rental_equipment
             WHERE status = 'maintenance'"
        );
        $inMaintenance = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Scheduled maintenance
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count FROM maintenance_schedules
             WHERE status = 'scheduled'"
        );
        $scheduled = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Total maintenance this month
        $stmt = $this->db->query(
            "SELECT COUNT(*) as count, COALESCE(SUM(cost), 0) as total_cost
             FROM equipment_maintenance
             WHERE strftime('%Y-%m', maintenance_date) = strftime('%Y-%m', 'now')"
        );
        $thisMonth = $stmt->fetch(PDO::FETCH_ASSOC);

        // Equipment by status
        $stmt = $this->db->query(
            "SELECT status, COUNT(*) as count
             FROM rental_equipment
             WHERE status != 'retired'
             GROUP BY status"
        );
        $byStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'in_maintenance' => $inMaintenance,
            'scheduled' => $scheduled,
            'maintenance_this_month' => (int)($thisMonth['count'] ?? 0),
            'cost_this_month' => (float)($thisMonth['total_cost'] ?? 0),
            'equipment_by_status' => $byStatus
        ];
    }

    /**
     * Get maintenance cost analysis
     */
    public function getCostAnalysis(string $startDate, string $endDate): array
    {
        // Total cost by equipment
        $stmt = $this->db->prepare(
            "SELECT re.id, re.name, re.equipment_code,
                    COUNT(em.id) as maintenance_count,
                    COALESCE(SUM(em.cost), 0) as total_cost
             FROM rental_equipment re
             LEFT JOIN equipment_maintenance em ON re.id = em.equipment_id
                AND em.maintenance_date BETWEEN ? AND ?
             GROUP BY re.id
             HAVING maintenance_count > 0
             ORDER BY total_cost DESC
             LIMIT 10"
        );
        $stmt->execute([$startDate, $endDate]);
        $topCostEquipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cost by maintenance type
        $stmt = $this->db->prepare(
            "SELECT maintenance_type, COUNT(*) as count, COALESCE(SUM(cost), 0) as total_cost
             FROM equipment_maintenance
             WHERE maintenance_date BETWEEN ? AND ?
             GROUP BY maintenance_type
             ORDER BY total_cost DESC"
        );
        $stmt->execute([$startDate, $endDate]);
        $byType = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Total cost
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total_services, COALESCE(SUM(cost), 0) as total_cost
             FROM equipment_maintenance
             WHERE maintenance_date BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'top_cost_equipment' => $topCostEquipment,
            'by_type' => $byType,
            'total_services' => (int)($totals['total_services'] ?? 0),
            'total_cost' => (float)($totals['total_cost'] ?? 0)
        ];
    }

    /**
     * Update equipment after maintenance
     */
    private function updateEquipmentAfterMaintenance(int $equipmentId, ?string $nextServiceDate, string $maintenanceType): void
    {
        $updates = [];
        $params = [];

        // Update next inspection date
        if ($nextServiceDate) {
            $updates[] = 'next_inspection_due = ?';
            $params[] = $nextServiceDate;
        }

        // Update last service date
        $updates[] = 'last_service_date = date(\'now\')';

        // If it was in maintenance, set back to available
        if ($maintenanceType === 'repair' || $maintenanceType === 'inspection') {
            $updates[] = 'status = \'available\'';
        }

        if (empty($updates)) {
            return;
        }

        $params[] = $equipmentId;

        $stmt = $this->db->prepare(
            "UPDATE rental_equipment SET " . implode(', ', $updates) . " WHERE id = ?"
        );

        $stmt->execute($params);
    }

    /**
     * Get upcoming inspections
     */
    public function getUpcomingInspections(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT re.*, ret.name as equipment_type,
                    julianday(re.next_inspection_due) - julianday('now') as days_until
             FROM rental_equipment re
             LEFT JOIN rental_equipment_types ret ON re.equipment_type_id = ret.id
             WHERE re.status != 'retired'
             AND re.next_inspection_due IS NOT NULL
             AND re.next_inspection_due BETWEEN date('now') AND date('now', '+' || ? || ' days')
             ORDER BY re.next_inspection_due ASC"
        );
        $stmt->execute([$days]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get maintenance types
     */
    public function getMaintenanceTypes(): array
    {
        return [
            'inspection' => 'Regular Inspection',
            'repair' => 'Repair',
            'cleaning' => 'Cleaning',
            'calibration' => 'Calibration',
            'parts_replacement' => 'Parts Replacement',
            'servicing' => 'General Servicing',
            'safety_check' => 'Safety Check',
            'pressure_test' => 'Pressure Test',
            'other' => 'Other'
        ];
    }

    /**
     * Cancel scheduled maintenance
     */
    public function cancelScheduledMaintenance(int $scheduleId, string $reason = ''): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE maintenance_schedules
                 SET status = 'cancelled', cancelled_at = datetime('now'), cancellation_reason = ?
                 WHERE id = ?"
            );

            return $stmt->execute([$reason, $scheduleId]);
        } catch (\Exception $e) {
            error_log("Failed to cancel maintenance: " . $e->getMessage());
            return false;
        }
    }
}
