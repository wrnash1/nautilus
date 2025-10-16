<?php

namespace App\Services\Staff;

use App\Core\Database;

class CommissionService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all commissions
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getAllCommissions($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-01');
        }
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }

        $stmt = $this->db->prepare("
            SELECT c.*, u.first_name, u.last_name, t.transaction_date
            FROM commissions c
            JOIN users u ON c.staff_id = u.id
            LEFT JOIN transactions t ON c.transaction_id = t.id
            WHERE c.commission_date BETWEEN ? AND ?
            ORDER BY c.commission_date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get staff commissions
     * 
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getStaffCommissions($staffId, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-01');
        }
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }

        $stmt = $this->db->prepare("
            SELECT c.*, t.transaction_date, t.total_amount
            FROM commissions c
            LEFT JOIN transactions t ON c.transaction_id = t.id
            WHERE c.staff_id = ? AND c.commission_date BETWEEN ? AND ?
            ORDER BY c.commission_date DESC
        ");
        $stmt->execute([$staffId, $startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get commission summary for staff member
     * 
     * @param int $staffId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getCommissionSummary($staffId, $startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = date('Y-m-01');
        }
        if (!$endDate) {
            $endDate = date('Y-m-t');
        }

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as commission_count,
                SUM(sale_amount) as total_sales,
                SUM(commission_amount) as total_commissions,
                AVG(commission_rate) as avg_commission_rate
            FROM commissions
            WHERE staff_id = ? AND commission_date BETWEEN ? AND ?
        ");
        $stmt->execute([$staffId, $startDate, $endDate]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Calculate commission for transaction
     * 
     * @param int $transactionId
     * @param int $staffId
     * @param float $saleAmount
     * @param float $commissionRate
     * @return bool
     */
    public function calculateCommission($transactionId, $staffId, $saleAmount, $commissionRate)
    {
        $commissionAmount = $saleAmount * ($commissionRate / 100);
        
        $stmt = $this->db->prepare("
            INSERT INTO commissions 
            (staff_id, transaction_id, sale_amount, commission_rate, commission_amount, commission_date, created_at)
            VALUES (?, ?, ?, ?, ?, CURDATE(), NOW())
        ");
        
        return $stmt->execute([$staffId, $transactionId, $saleAmount, $commissionRate, $commissionAmount]);
    }

    /**
     * Get commission report
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCommissionReport($startDate, $endDate)
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.id as staff_id,
                u.first_name,
                u.last_name,
                COUNT(c.id) as commission_count,
                SUM(c.sale_amount) as total_sales,
                SUM(c.commission_amount) as total_commissions,
                AVG(c.commission_rate) as avg_commission_rate
            FROM users u
            LEFT JOIN commissions c ON u.id = c.staff_id 
                AND c.commission_date BETWEEN ? AND ?
            WHERE u.role_id IN (SELECT id FROM roles WHERE name != 'customer')
            GROUP BY u.id
            ORDER BY total_commissions DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
