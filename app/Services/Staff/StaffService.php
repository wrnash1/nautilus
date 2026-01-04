<?php

namespace App\Services\Staff;

use App\Core\Database;

class StaffService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all staff members
     * 
     * @return array
     */
    public function getAllStaff()
    {
        $stmt = $this->db->query("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.role_id IN (SELECT id FROM roles WHERE name != 'customer')
            ORDER BY u.first_name, u.last_name
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get staff member by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getStaffById($id)
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get staff performance metrics
     * 
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getPerformanceMetrics($staffId, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-01');
        }
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }

        $stmt = $this->db->prepare("
            SELECT * FROM staff_performance_metrics
            WHERE staff_id = ? AND metric_date BETWEEN ? AND ?
            ORDER BY metric_date DESC
        ");
        $stmt->execute([$staffId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get staff sales summary
     *
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSalesSummary($staffId, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-01');
        }
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }

        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as avg_transaction
            FROM transactions
            WHERE cashier_id = ? AND transaction_date BETWEEN ? AND ?
        ");
        $stmt->execute([$staffId, $startDate, $endDate]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all available roles (non-customer roles)
     *
     * @return array
     */
    public function getAvailableRoles()
    {
        $stmt = $this->db->query("
            SELECT id, name, display_name, description
            FROM roles
            WHERE name != 'customer'
            ORDER BY display_name
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new staff member
     *
     * @param array $data
     * @return int|false Staff ID or false on failure
     */
    public function createStaff($data)
    {
        try {
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO users (
                    first_name, last_name, email, phone, password,
                    role_id, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $result = $stmt->execute([
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'] ?? null,
                $hashedPassword,
                $data['role_id'],
                $data['is_active'] ?? 1
            ]);

            if ($result) {
                return (int)$this->db->lastInsertId();
            }

            return false;
        } catch (\Exception $e) {
            error_log("Error creating staff: " . $e->getMessage());
            return false;
        }
    }
}
