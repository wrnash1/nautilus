<?php

namespace App\Services\Appointments;

use App\Core\Database;
use PDO;
use App\Core\Logger;

/**
 * Appointment Service
 * Handles appointment scheduling and management
 */
class AppointmentService
{
    private PDO $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getPdo();
        $this->logger = new Logger();
    }

    /**
     * Create a new appointment
     */
    public function create(array $data): int
    {
        try {
            $sql = "INSERT INTO appointments (
                        customer_id, appointment_type, start_time, end_time,
                        assigned_to, location, status, notes, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['customer_id'],
                $data['appointment_type'],
                $data['start_time'],
                $data['end_time'],
                $data['assigned_to'] ?? null,
                $data['location'] ?? null,
                $data['status'] ?? 'scheduled',
                $data['notes'] ?? null
            ]);

            $appointmentId = (int)$this->db->lastInsertId();

            $this->logger->info('Appointment created', [
                'appointment_id' => $appointmentId,
                'customer_id' => $data['customer_id']
            ]);

            return $appointmentId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create appointment', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update an appointment
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            $values = [];

            $allowedFields = [
                'appointment_type', 'start_time', 'end_time',
                'assigned_to', 'location', 'status', 'notes'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $values[] = $id;

            $sql = "UPDATE appointments SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            $this->logger->info('Appointment updated', ['appointment_id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to update appointment', [
                'appointment_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get appointment by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT a.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN users u ON a.assigned_to = u.id
                WHERE a.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Get all appointments with filters
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT a.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN users u ON a.assigned_to = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND a.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND a.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['appointment_type'])) {
            $sql .= " AND a.appointment_type = ?";
            $params[] = $filters['appointment_type'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(a.start_time) >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(a.start_time) <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY a.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcoming(int $limit = 10, ?int $assignedTo = null): array
    {
        $sql = "SELECT a.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN users u ON a.assigned_to = u.id
                WHERE a.start_time >= NOW()
                AND a.status IN ('scheduled', 'confirmed')";

        $params = [];

        if ($assignedTo !== null) {
            $sql .= " AND a.assigned_to = ?";
            $params[] = $assignedTo;
        }

        $sql .= " ORDER BY a.start_time ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get appointments for a specific date
     */
    public function getByDate(string $date, ?int $assignedTo = null): array
    {
        $sql = "SELECT a.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                LEFT JOIN users u ON a.assigned_to = u.id
                WHERE DATE(a.start_time) = ?";

        $params = [$date];

        if ($assignedTo !== null) {
            $sql .= " AND a.assigned_to = ?";
            $params[] = $assignedTo;
        }

        $sql .= " ORDER BY a.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check for scheduling conflicts
     */
    public function hasConflict(string $startTime, string $endTime, ?int $assignedTo = null, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count
                FROM appointments
                WHERE status NOT IN ('cancelled', 'no_show')
                AND (
                    (start_time < ? AND end_time > ?) OR
                    (start_time < ? AND end_time > ?) OR
                    (start_time >= ? AND end_time <= ?)
                )";

        $params = [$endTime, $startTime, $endTime, $endTime, $startTime, $endTime];

        if ($assignedTo !== null) {
            $sql .= " AND assigned_to = ?";
            $params[] = $assignedTo;
        }

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return (int)$result['count'] > 0;
    }

    /**
     * Cancel an appointment
     */
    public function cancel(int $id): bool
    {
        return $this->update($id, ['status' => 'cancelled']);
    }

    /**
     * Confirm an appointment
     */
    public function confirm(int $id): bool
    {
        return $this->update($id, ['status' => 'confirmed']);
    }

    /**
     * Mark appointment as completed
     */
    public function complete(int $id): bool
    {
        return $this->update($id, ['status' => 'completed']);
    }

    /**
     * Mark appointment as no-show
     */
    public function markNoShow(int $id): bool
    {
        return $this->update($id, ['status' => 'no_show']);
    }

    /**
     * Delete an appointment
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM appointments WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $this->logger->info('Appointment deleted', ['appointment_id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete appointment', [
                'appointment_id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send appointment reminder
     */
    public function sendReminder(int $id): bool
    {
        $appointment = $this->getById($id);

        if (!$appointment) {
            return false;
        }

        // TODO: Implement email sending
        // For now, just mark as reminded
        $sql = "UPDATE appointments SET reminder_sent = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        $this->logger->info('Appointment reminder sent', ['appointment_id' => $id]);

        return true;
    }

    /**
     * Get appointments needing reminders (24 hours before)
     */
    public function getNeedingReminders(): array
    {
        $sql = "SELECT a.*,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       c.email as customer_email,
                       c.phone as customer_phone
                FROM appointments a
                JOIN customers c ON a.customer_id = c.id
                WHERE a.status IN ('scheduled', 'confirmed')
                AND a.reminder_sent = 0
                AND a.start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                ORDER BY a.start_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get appointment statistics
     */
    public function getStatistics(string $startDate, string $endDate): array
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_show,
                    appointment_type,
                    COUNT(*) as type_count
                FROM appointments
                WHERE DATE(start_time) BETWEEN ? AND ?
                GROUP BY appointment_type";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
