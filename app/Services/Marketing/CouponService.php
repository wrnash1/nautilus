<?php

namespace App\Services\Marketing;

use App\Core\Database;

class CouponService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all coupons
     *
     * @return array
     */
    public function getAllCoupons()
    {
        try {
            $stmt = $this->db->query("
                SELECT c.*,
                       COUNT(cu.id) as times_used,
                       SUM(cu.discount_amount) as total_discount
                FROM coupons c
                LEFT JOIN coupon_usage cu ON c.id = cu.coupon_id
                GROUP BY c.id
                ORDER BY c.created_at DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get coupon by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getCouponById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get coupon by code
     * 
     * @param string $code
     * @return array|null
     */
    public function getCouponByCode($code)
    {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([strtoupper($code)]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create new coupon
     * 
     * @param array $data
     * @return int|false Coupon ID or false on failure
     */
    public function createCoupon($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO coupons 
            (code, description, discount_type, discount_value, min_purchase_amount, 
             max_discount_amount, usage_limit, usage_limit_per_customer, 
             valid_from, valid_until, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['code'],
            $data['description'],
            $data['discount_type'],
            $data['discount_value'],
            $data['min_purchase_amount'],
            $data['max_discount_amount'],
            $data['usage_limit'],
            $data['usage_limit_per_customer'],
            $data['valid_from'],
            $data['valid_until'],
            $data['is_active']
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update coupon
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCoupon($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE coupons 
            SET code = ?, description = ?, discount_type = ?, discount_value = ?,
                min_purchase_amount = ?, max_discount_amount = ?, usage_limit = ?,
                usage_limit_per_customer = ?, valid_from = ?, valid_until = ?,
                is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['code'],
            $data['description'],
            $data['discount_type'],
            $data['discount_value'],
            $data['min_purchase_amount'],
            $data['max_discount_amount'],
            $data['usage_limit'],
            $data['usage_limit_per_customer'],
            $data['valid_from'],
            $data['valid_until'],
            $data['is_active'],
            $id
        ]);
    }

    /**
     * Delete coupon
     * 
     * @param int $id
     * @return bool
     */
    public function deleteCoupon($id)
    {
        $stmt = $this->db->prepare("DELETE FROM coupons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Validate coupon for use
     * 
     * @param string $code
     * @param int|null $customerId
     * @param float $cartTotal
     * @return array ['valid' => bool, 'discount' => float, 'message' => string]
     */
    public function validateCoupon($code, $customerId = null, $cartTotal = 0)
    {
        $coupon = $this->getCouponByCode($code);

        if (!$coupon) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Invalid coupon code'];
        }

        if (!$coupon['is_active']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Coupon is not active'];
        }

        $now = date('Y-m-d H:i:s');
        if ($coupon['valid_from'] && $now < $coupon['valid_from']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Coupon is not yet valid'];
        }

        if ($coupon['valid_until'] && $now > $coupon['valid_until']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Coupon has expired'];
        }

        if ($coupon['min_purchase_amount'] && $cartTotal < $coupon['min_purchase_amount']) {
            return ['valid' => false, 'discount' => 0, 'message' => 'Minimum purchase amount not met'];
        }

        if ($coupon['usage_limit']) {
            $usageCount = $this->getCouponUsageCount($coupon['id']);
            if ($usageCount >= $coupon['usage_limit']) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Coupon usage limit reached'];
            }
        }

        if ($customerId && $coupon['usage_limit_per_customer']) {
            $customerUsageCount = $this->getCustomerCouponUsageCount($coupon['id'], $customerId);
            if ($customerUsageCount >= $coupon['usage_limit_per_customer']) {
                return ['valid' => false, 'discount' => 0, 'message' => 'Coupon usage limit per customer reached'];
            }
        }

        $discount = $this->calculateDiscount($coupon, $cartTotal);

        return [
            'valid' => true,
            'discount' => $discount,
            'message' => 'Coupon is valid',
            'coupon_id' => $coupon['id']
        ];
    }

    /**
     * Calculate discount amount
     * 
     * @param array $coupon
     * @param float $amount
     * @return float
     */
    private function calculateDiscount($coupon, $amount)
    {
        if ($coupon['discount_type'] === 'percentage') {
            $discount = $amount * ($coupon['discount_value'] / 100);
        } else {
            $discount = $coupon['discount_value'];
        }

        if ($coupon['max_discount_amount'] && $discount > $coupon['max_discount_amount']) {
            $discount = $coupon['max_discount_amount'];
        }

        return round($discount, 2);
    }

    /**
     * Record coupon usage
     * 
     * @param int $couponId
     * @param int|null $customerId
     * @param int|null $orderId
     * @param float $discountAmount
     * @return bool
     */
    public function recordUsage($couponId, $customerId, $orderId, $discountAmount)
    {
        $stmt = $this->db->prepare("
            INSERT INTO coupon_usage 
            (coupon_id, customer_id, order_id, discount_amount, used_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$couponId, $customerId, $orderId, $discountAmount]);
    }

    /**
     * Get coupon usage count
     * 
     * @param int $couponId
     * @return int
     */
    private function getCouponUsageCount($couponId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ?");
        $stmt->execute([$couponId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get customer coupon usage count
     * 
     * @param int $couponId
     * @param int $customerId
     * @return int
     */
    private function getCustomerCouponUsageCount($couponId, $customerId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM coupon_usage 
            WHERE coupon_id = ? AND customer_id = ?
        ");
        $stmt->execute([$couponId, $customerId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get coupon usage history
     * 
     * @param int $couponId
     * @return array
     */
    public function getCouponUsage($couponId)
    {
        $stmt = $this->db->prepare("
            SELECT cu.*, c.first_name, c.last_name, c.email
            FROM coupon_usage cu
            LEFT JOIN customers c ON cu.customer_id = c.id
            WHERE cu.coupon_id = ?
            ORDER BY cu.used_at DESC
        ");
        $stmt->execute([$couponId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Generate bulk coupon codes
     * 
     * @param int $count
     * @param string $prefix
     * @param array $baseData
     * @return array Generated coupon codes
     */
    public function generateBulkCoupons($count, $prefix, $baseData)
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            $code = $prefix . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $data = array_merge($baseData, ['code' => $code]);
            
            if ($this->createCoupon($data)) {
                $codes[] = $code;
            }
        }
        
        return $codes;
    }
}
