<?php

namespace App\Services\Marketing;

use App\Core\Database;

class LoyaltyService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all loyalty programs
     *
     * @return array
     */
    public function getAllPrograms()
    {
        try {
            $stmt = $this->db->query("
                SELECT * FROM loyalty_programs
                ORDER BY created_at DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get loyalty program by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getProgramById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM loyalty_programs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create new loyalty program
     * 
     * @param array $data
     * @return int|false Program ID or false on failure
     */
    public function createProgram($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_programs 
            (name, description, points_per_dollar, points_expiry_days, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['points_per_dollar'],
            $data['points_expiry_days'],
            $data['is_active']
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update loyalty program
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProgram($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE loyalty_programs 
            SET name = ?, description = ?, points_per_dollar = ?, 
                points_expiry_days = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['points_per_dollar'],
            $data['points_expiry_days'],
            $data['is_active'],
            $id
        ]);
    }

    /**
     * Delete loyalty program
     * 
     * @param int $id
     * @return bool
     */
    public function deleteProgram($id)
    {
        $stmt = $this->db->prepare("DELETE FROM loyalty_programs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get tiers for a loyalty program
     * 
     * @param int $programId
     * @return array
     */
    public function getTiersByProgram($programId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM loyalty_tiers 
            WHERE program_id = ? 
            ORDER BY points_required ASC
        ");
        $stmt->execute([$programId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer points balance
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerPoints($customerId)
    {
        $stmt = $this->db->prepare("
            SELECT lp.*, c.first_name, c.last_name
            FROM loyalty_points lp
            JOIN customers c ON lp.customer_id = c.id
            WHERE lp.customer_id = ? AND lp.is_active = 1
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer points history
     * 
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getPointsHistory($customerId, $limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM loyalty_points 
            WHERE customer_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$customerId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Award points to customer
     * 
     * @param int $customerId
     * @param int $programId
     * @param int $points
     * @param string $reason
     * @param string $type
     * @return bool
     */
    public function awardPoints($customerId, $programId, $points, $reason, $type = 'purchase')
    {
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_points 
            (customer_id, program_id, points, points_type, reason, transaction_type, is_active, earned_at)
            VALUES (?, ?, ?, 'earned', ?, ?, 1, NOW())
        ");
        
        return $stmt->execute([$customerId, $programId, $points, $reason, $type]);
    }

    /**
     * Redeem customer points
     * 
     * @param int $customerId
     * @param int $programId
     * @param int $points
     * @param string $reason
     * @return bool
     */
    public function redeemPoints($customerId, $programId, $points, $reason)
    {
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_points 
            (customer_id, program_id, points, points_type, reason, transaction_type, is_active, earned_at)
            VALUES (?, ?, ?, 'redeemed', ?, 'redemption', 1, NOW())
        ");
        
        return $stmt->execute([$customerId, $programId, -abs($points), $reason]);
    }

    /**
     * Manually adjust customer points
     * 
     * @param int $customerId
     * @param int $points Can be positive or negative
     * @param string $reason
     * @param string $type
     * @return bool
     */
    public function adjustPoints($customerId, $points, $reason, $type = 'manual_adjustment')
    {
        $pointsType = $points >= 0 ? 'earned' : 'redeemed';
        
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_points 
            (customer_id, program_id, points, points_type, reason, transaction_type, is_active, earned_at)
            VALUES (?, (SELECT id FROM loyalty_programs WHERE is_active = 1 LIMIT 1), ?, ?, ?, ?, 1, NOW())
        ");
        
        return $stmt->execute([$customerId, $points, $pointsType, $reason, $type]);
    }

    /**
     * Calculate points for purchase amount
     * 
     * @param int $programId
     * @param float $amount
     * @return int
     */
    public function calculatePointsForPurchase($programId, $amount)
    {
        $program = $this->getProgramById($programId);
        if (!$program) {
            return 0;
        }

        return floor($amount * $program['points_per_dollar']);
    }

    /**
     * Get active loyalty program
     * 
     * @return array|null
     */
    public function getActiveProgram()
    {
        $stmt = $this->db->query("
            SELECT * FROM loyalty_programs 
            WHERE is_active = 1 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
