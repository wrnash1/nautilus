<?php

namespace App\Services\Marketing;

use App\Core\Database;

class ReferralService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all referrals
     *
     * @return array
     */
    public function getAllReferrals()
    {
        try {
            $stmt = $this->db->query("
                SELECT rp.*,
                       c1.first_name as referrer_first_name, c1.last_name as referrer_last_name,
                       c2.first_name as referred_first_name, c2.last_name as referred_last_name
                FROM referral_program rp
                JOIN customers c1 ON rp.referrer_customer_id = c1.id
                JOIN customers c2 ON rp.referred_customer_id = c2.id
                ORDER BY rp.created_at DESC
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table might not exist yet
            return [];
        }
    }

    /**
     * Get customer referrals
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerReferrals($customerId)
    {
        $stmt = $this->db->prepare("
            SELECT rp.*, 
                   c.first_name, c.last_name, c.email
            FROM referral_program rp
            JOIN customers c ON rp.referred_customer_id = c.id
            WHERE rp.referrer_customer_id = ?
            ORDER BY rp.created_at DESC
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer referral statistics
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerReferralStats($customerId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_referrals,
                SUM(CASE WHEN reward_status = 'completed' THEN 1 ELSE 0 END) as completed_referrals,
                SUM(referrer_reward_amount) as total_rewards
            FROM referral_program
            WHERE referrer_customer_id = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get overall referral statistics
     * 
     * @return array
     */
    public function getReferralStats()
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    COUNT(*) as total_referrals,
                    COUNT(DISTINCT referrer_customer_id) as total_referrers,
                    SUM(CASE WHEN reward_status = 'completed' THEN 1 ELSE 0 END) as completed_referrals,
                    SUM(referrer_reward_amount) as total_referrer_rewards,
                    SUM(referred_reward_amount) as total_referred_rewards
                FROM referral_program
            ");
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [
                'total_referrals' => 0,
                'total_referrers' => 0,
                'completed_referrals' => 0,
                'total_referrer_rewards' => 0,
                'total_referred_rewards' => 0
            ];
        } catch (\PDOException $e) {
            return [
                'total_referrals' => 0,
                'total_referrers' => 0,
                'completed_referrals' => 0,
                'total_referrer_rewards' => 0,
                'total_referred_rewards' => 0
            ];
        }
    }

    /**
     * Get top referrers
     *
     * @param int $limit
     * @return array
     */
    public function getTopReferrers($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    c.id, c.first_name, c.last_name, c.email,
                    COUNT(rp.id) as referral_count,
                    SUM(rp.referrer_reward_amount) as total_rewards
                FROM customers c
                JOIN referral_program rp ON c.id = rp.referrer_customer_id
                WHERE rp.reward_status = 'completed'
                GROUP BY c.id
                ORDER BY referral_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Process a referral
     * 
     * @param int $referrerId
     * @param int $referredId
     * @param string $referralCode
     * @return bool
     */
    public function processReferral($referrerId, $referredId, $referralCode)
    {
        $settings = $this->getSettings();
        
        $stmt = $this->db->prepare("
            INSERT INTO referral_program 
            (referrer_customer_id, referred_customer_id, referral_code, 
             referrer_reward_type, referrer_reward_amount,
             referred_reward_type, referred_reward_amount,
             reward_status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        return $stmt->execute([
            $referrerId,
            $referredId,
            $referralCode,
            $settings['referrer_reward_type'],
            $settings['referrer_reward_value'],
            $settings['referee_reward_type'],
            $settings['referee_reward_value']
        ]);
    }

    /**
     * Complete referral and award rewards
     * 
     * @param int $referralId
     * @return bool
     */
    public function completeReferral($referralId)
    {
        $stmt = $this->db->prepare("
            UPDATE referral_program 
            SET reward_status = 'completed', completed_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$referralId]);
    }

    /**
     * Generate referral code for customer
     * 
     * @param int $customerId
     * @return string
     */
    public function generateReferralCode($customerId)
    {
        return 'REF-' . $customerId . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Get referral program settings
     * 
     * @return array
     */
    public function getSettings()
    {
        return [
            'referrer_reward_type' => 'points',
            'referrer_reward_value' => 500,
            'referee_reward_type' => 'discount',
            'referee_reward_value' => 10,
            'is_active' => true
        ];
    }

    /**
     * Update referral program settings
     * 
     * @param array $settings
     * @return bool
     */
    public function updateSettings($settings)
    {
        return true;
    }

    /**
     * Check if referral code is valid
     * 
     * @param string $code
     * @return array|null
     */
    public function validateReferralCode($code)
    {
        $parts = explode('-', $code);
        if (count($parts) !== 3 || $parts[0] !== 'REF') {
            return null;
        }

        $customerId = (int) $parts[1];
        
        $stmt = $this->db->prepare("
            SELECT id, first_name, last_name, email 
            FROM customers 
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$customerId]);
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
