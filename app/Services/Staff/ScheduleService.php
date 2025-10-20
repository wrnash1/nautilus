<?php

namespace App\Services\Staff;

use App\Core\Database;

class ScheduleService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all schedules
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getAllSchedules($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d');
        }
        if (!$endDate) {
            $endDate = date('Y-m-d', strtotime('+30 days'));
        }

        $stmt = $this->db->prepare("
            SELECT ss.*, u.first_name, u.last_name
            FROM staff_schedules ss
            JOIN users u ON ss.staff_id = u.id
            WHERE ss.shift_date BETWEEN ? AND ?
            ORDER BY ss.shift_date, ss.shift_start
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get staff schedule
     * 
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getStaffSchedule($staffId, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-d');
        }
        if (!$endDate) {
            $endDate = date('Y-m-d', strtotime('+30 days'));
        }

        $stmt = $this->db->prepare("
            SELECT * FROM staff_schedules
            WHERE staff_id = ? AND shift_date BETWEEN ? AND ?
            ORDER BY shift_date, shift_start
        ");
        $stmt->execute([$staffId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create schedule
     * 
     * @param array $data
     * @return int|false Schedule ID or false on failure
     */
    public function createSchedule($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO staff_schedules 
            (staff_id, shift_date, shift_start, shift_end, role, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['staff_id'],
            $data['shift_date'],
            $data['shift_start'],
            $data['shift_end'],
            $data['role'],
            $data['notes']
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update schedule
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateSchedule($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE staff_schedules 
            SET staff_id = ?, shift_date = ?, shift_start = ?, shift_end = ?,
                role = ?, notes = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['staff_id'],
            $data['shift_date'],
            $data['shift_start'],
            $data['shift_end'],
            $data['role'],
            $data['notes'],
            $id
        ]);
    }

    /**
     * Delete schedule
     * 
     * @param int $id
     * @return bool
     */
    public function deleteSchedule($id)
    {
        $stmt = $this->db->prepare("DELETE FROM staff_schedules WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Check for schedule conflicts
     * 
     * @param int $staffId
     * @param string $shiftDate
     * @param string $shiftStart
     * @param string $shiftEnd
     * @param int|null $excludeId
     * @return bool
     */
    public function hasConflict($staffId, $shiftDate, $shiftStart, $shiftEnd, $excludeId = null)
    {
        $sql = "
            SELECT COUNT(*) FROM staff_schedules
            WHERE staff_id = ? 
            AND shift_date = ?
            AND (
                (shift_start <= ? AND shift_end > ?)
                OR (shift_start < ? AND shift_end >= ?)
                OR (shift_start >= ? AND shift_end <= ?)
            )
        ";
        
        $params = [$staffId, $shiftDate, $shiftStart, $shiftStart, $shiftEnd, $shiftEnd, $shiftStart, $shiftEnd];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
}
