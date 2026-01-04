<?php

namespace App\Services\Staff;

use App\Core\Database;

class TimeClockService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Clock in
     * 
     * @param int $staffId
     * @return bool
     */
    public function clockIn($staffId)
    {
        $currentShift = $this->getCurrentShift($staffId);
        if ($currentShift) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO time_clock_entries (user_id, clock_in, created_at)
            VALUES (?, NOW(), NOW())
        ");
        
        return $stmt->execute([$staffId]);
    }

    /**
     * Clock out
     * 
     * @param int $staffId
     * @return bool
     */
    public function clockOut($staffId)
    {
        $currentShift = $this->getCurrentShift($staffId);
        if (!$currentShift) {
            return false;
        }

        $clockIn = new \DateTime($currentShift['clock_in']);
        $clockOut = new \DateTime();
        $interval = $clockIn->diff($clockOut);
        $hours = $interval->h + ($interval->days * 24) + ($interval->i / 60);

        $stmt = $this->db->prepare("
            UPDATE time_clock_entries 
            SET clock_out = NOW(), total_hours = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([round($hours, 2), $currentShift['id']]);
    }

    /**
     * Get current open shift for staff member
     * 
     * @param int $staffId
     * @return array|null
     */
    public function getCurrentShift($staffId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM time_clock_entries
            WHERE user_id = ? AND clock_out IS NULL
            ORDER BY clock_in DESC
            LIMIT 1
        ");
        $stmt->execute([$staffId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get recent time clock entries
     * 
     * @param int $staffId
     * @param int $limit
     * @return array
     */
    public function getRecentEntries($staffId, $limit = 10)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM time_clock_entries
            WHERE user_id = ?
            ORDER BY clock_in DESC
            LIMIT ?
        ");
        $stmt->execute([$staffId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get timesheet report
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int|null $staffId
     * @return array
     */
    public function getTimesheetReport($startDate, $endDate, $staffId = null)
    {
        $sql = "
            SELECT tc.*, u.first_name, u.last_name
            FROM time_clock_entries tc
            JOIN users u ON tc.user_id = u.id
            WHERE DATE(tc.clock_in) BETWEEN ? AND ?
        ";
        
        $params = [$startDate, $endDate];
        
        if ($staffId) {
            $sql .= " AND tc.user_id = ?";
            $params[] = $staffId;
        }
        
        $sql .= " ORDER BY tc.clock_in DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calculate total hours for period
     * 
     * @param int $staffId
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function calculateTotalHours($staffId, $startDate, $endDate)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(total_hours) as total
            FROM time_clock_entries
            WHERE user_id = ? AND DATE(clock_in) BETWEEN ? AND ?
        ");
        $stmt->execute([$staffId, $startDate, $endDate]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }
}
