<?php

namespace App\Services\Loyalty;

use PDO;

/**
 * Customer Loyalty and Rewards Service
 *
 * Manages loyalty points, rewards, tiers, and redemption
 */
class LoyaltyService
{
    private PDO $db;
    private array $config;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
        $this->loadConfig();
    }

    /**
     * Load loyalty program configuration
     */
    private function loadConfig(): void
    {
        $this->config = [
            'points_per_dollar' => 10, // 10 points per $1 spent
            'dollar_value_per_point' => 0.01, // Each point worth $0.01
            'point_expiry_days' => 365,
            'referral_bonus_points' => 500,
            'birthday_bonus_points' => 250,
            'review_bonus_points' => 50,
            'tiers' => [
                'bronze' => ['min_points' => 0, 'multiplier' => 1.0, 'name' => 'Bronze Diver'],
                'silver' => ['min_points' => 1000, 'multiplier' => 1.25, 'name' => 'Silver Diver'],
                'gold' => ['min_points' => 5000, 'multiplier' => 1.5, 'name' => 'Gold Diver'],
                'platinum' => ['min_points' => 10000, 'multiplier' => 2.0, 'name' => 'Platinum Diver']
            ]
        ];
    }

    /**
     * Award points for purchase
     */
    public function awardPurchasePoints(int $customerId, float $amount, int $orderId): int
    {
        $tier = $this->getCustomerTier($customerId);
        $multiplier = $this->config['tiers'][$tier]['multiplier'];

        $basePoints = (int)($amount * $this->config['points_per_dollar']);
        $bonusPoints = (int)($basePoints * ($multiplier - 1));
        $totalPoints = $basePoints + $bonusPoints;

        return $this->addPoints(
            $customerId,
            $totalPoints,
            'purchase',
            "Purchase #$orderId",
            $orderId,
            $bonusPoints
        );
    }

    /**
     * Award referral bonus
     */
    public function awardReferralBonus(int $referrerId, int $referredId): int
    {
        $points = $this->config['referral_bonus_points'];

        return $this->addPoints(
            $referrerId,
            $points,
            'referral',
            "Referred customer #$referredId"
        );
    }

    /**
     * Award birthday bonus
     */
    public function awardBirthdayBonus(int $customerId): int
    {
        // Check if already awarded this year
        $stmt = $this->db->prepare(
            "SELECT id FROM loyalty_transactions
             WHERE customer_id = ?
             AND transaction_type = 'birthday'
             AND YEAR(created_at) = YEAR(CURDATE())"
        );
        $stmt->execute([$customerId]);

        if ($stmt->fetch()) {
            return 0; // Already awarded this year
        }

        $points = $this->config['birthday_bonus_points'];

        return $this->addPoints(
            $customerId,
            $points,
            'birthday',
            'Birthday bonus'
        );
    }

    /**
     * Award review bonus
     */
    public function awardReviewBonus(int $customerId, int $reviewId): int
    {
        $points = $this->config['review_bonus_points'];

        return $this->addPoints(
            $customerId,
            $points,
            'review',
            "Product review #$reviewId",
            $reviewId
        );
    }

    /**
     * Redeem points for discount
     */
    public function redeemPoints(int $customerId, int $points, int $orderId = null): bool
    {
        $balance = $this->getPointsBalance($customerId);

        if ($balance < $points) {
            return false;
        }

        $this->addPoints(
            $customerId,
            -$points,
            'redemption',
            $orderId ? "Redeemed on order #$orderId" : 'Points redeemed',
            $orderId
        );

        return true;
    }

    /**
     * Get customer points balance
     */
    public function getPointsBalance(int $customerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(points), 0) as balance
             FROM loyalty_transactions
             WHERE customer_id = ?
             AND (expiry_date IS NULL OR expiry_date > CURDATE())"
        );
        $stmt->execute([$customerId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['balance'] ?? 0);
    }

    /**
     * Get customer lifetime points (including redeemed)
     */
    public function getLifetimePoints(int $customerId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(points), 0) as total
             FROM loyalty_transactions
             WHERE customer_id = ? AND points > 0"
        );
        $stmt->execute([$customerId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get customer tier
     */
    public function getCustomerTier(int $customerId): string
    {
        $lifetimePoints = $this->getLifetimePoints($customerId);

        $tier = 'bronze';
        foreach ($this->config['tiers'] as $tierName => $tierData) {
            if ($lifetimePoints >= $tierData['min_points']) {
                $tier = $tierName;
            }
        }

        return $tier;
    }

    /**
     * Get tier details
     */
    public function getTierDetails(int $customerId): array
    {
        $currentTier = $this->getCustomerTier($customerId);
        $lifetimePoints = $this->getLifetimePoints($customerId);
        $currentBalance = $this->getPointsBalance($customerId);

        $tierData = $this->config['tiers'][$currentTier];

        // Find next tier
        $nextTier = null;
        $pointsToNextTier = null;

        foreach ($this->config['tiers'] as $tierName => $data) {
            if ($data['min_points'] > $lifetimePoints) {
                $nextTier = $tierName;
                $pointsToNextTier = $data['min_points'] - $lifetimePoints;
                break;
            }
        }

        return [
            'current_tier' => $currentTier,
            'tier_name' => $tierData['name'],
            'multiplier' => $tierData['multiplier'],
            'lifetime_points' => $lifetimePoints,
            'current_balance' => $currentBalance,
            'next_tier' => $nextTier,
            'points_to_next_tier' => $pointsToNextTier,
            'progress_percentage' => $nextTier ? (($lifetimePoints - $tierData['min_points']) / ($pointsToNextTier + ($lifetimePoints - $tierData['min_points']))) * 100 : 100
        ];
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $customerId, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM loyalty_transactions
             WHERE customer_id = ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$customerId, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get available rewards
     */
    public function getAvailableRewards(int $customerId): array
    {
        $balance = $this->getPointsBalance($customerId);

        $stmt = $this->db->query(
            "SELECT * FROM loyalty_rewards
             WHERE is_active = 1
             AND (start_date IS NULL OR start_date <= CURDATE())
             AND (end_date IS NULL OR end_date >= CURDATE())
             ORDER BY points_required ASC"
        );

        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add affordability flag
        foreach ($rewards as &$reward) {
            $reward['can_afford'] = $balance >= $reward['points_required'];
            $reward['points_short'] = max(0, $reward['points_required'] - $balance);
        }

        return $rewards;
    }

    /**
     * Claim reward
     */
    public function claimReward(int $customerId, int $rewardId): ?string
    {
        try {
            $this->db->beginTransaction();

            // Get reward details
            $stmt = $this->db->prepare(
                "SELECT * FROM loyalty_rewards WHERE id = ? AND is_active = 1"
            );
            $stmt->execute([$rewardId]);
            $reward = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reward) {
                throw new \Exception("Reward not found");
            }

            // Check balance
            $balance = $this->getPointsBalance($customerId);
            if ($balance < $reward['points_required']) {
                throw new \Exception("Insufficient points");
            }

            // Check quantity (if limited)
            if ($reward['max_quantity'] && $reward['claimed_quantity'] >= $reward['max_quantity']) {
                throw new \Exception("Reward no longer available");
            }

            // Redeem points
            $this->redeemPoints($customerId, $reward['points_required']);

            // Generate reward code
            $rewardCode = $this->generateRewardCode();

            // Create reward claim record
            $stmt = $this->db->prepare(
                "INSERT INTO loyalty_reward_claims
                 (customer_id, reward_id, points_used, reward_code, status, claimed_at)
                 VALUES (?, ?, ?, ?, 'pending', NOW())"
            );
            $stmt->execute([
                $customerId,
                $rewardId,
                $reward['points_required'],
                $rewardCode
            ]);

            // Update claimed quantity
            $stmt = $this->db->prepare(
                "UPDATE loyalty_rewards
                 SET claimed_quantity = claimed_quantity + 1
                 WHERE id = ?"
            );
            $stmt->execute([$rewardId]);

            $this->db->commit();
            return $rewardCode;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to claim reward: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get expiring points
     */
    public function getExpiringPoints(int $customerId, int $days = 30): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM loyalty_transactions
             WHERE customer_id = ?
             AND points > 0
             AND expiry_date IS NOT NULL
             AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY expiry_date ASC"
        );
        $stmt->execute([$customerId, $days]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate points value in currency
     */
    public function pointsToCurrency(int $points): float
    {
        return $points * $this->config['dollar_value_per_point'];
    }

    /**
     * Calculate currency to points
     */
    public function currencyToPoints(float $amount): int
    {
        return (int)($amount / $this->config['dollar_value_per_point']);
    }

    /**
     * Get loyalty statistics
     */
    public function getStatistics(): array
    {
        // Total active members
        $stmt = $this->db->query(
            "SELECT COUNT(DISTINCT customer_id) as count
             FROM loyalty_transactions"
        );
        $activeMembers = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Points issued vs redeemed
        $stmt = $this->db->query(
            "SELECT
                SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as issued,
                ABS(SUM(CASE WHEN points < 0 THEN points ELSE 0 END)) as redeemed
             FROM loyalty_transactions"
        );
        $pointsData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Tier distribution
        $stmt = $this->db->query(
            "SELECT customer_id FROM loyalty_transactions GROUP BY customer_id"
        );
        $customers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $tierDistribution = ['bronze' => 0, 'silver' => 0, 'gold' => 0, 'platinum' => 0];
        foreach ($customers as $customerId) {
            $tier = $this->getCustomerTier($customerId);
            $tierDistribution[$tier]++;
        }

        return [
            'active_members' => $activeMembers,
            'points_issued' => (int)($pointsData['issued'] ?? 0),
            'points_redeemed' => (int)($pointsData['redeemed'] ?? 0),
            'points_outstanding' => (int)($pointsData['issued'] ?? 0) - (int)($pointsData['redeemed'] ?? 0),
            'tier_distribution' => $tierDistribution,
            'liability_value' => $this->pointsToCurrency((int)($pointsData['issued'] ?? 0) - (int)($pointsData['redeemed'] ?? 0))
        ];
    }

    /**
     * Add points transaction
     */
    private function addPoints(
        int $customerId,
        int $points,
        string $type,
        string $description,
        ?int $referenceId = null,
        int $bonusPoints = 0
    ): int {
        $expiryDate = $points > 0 && $this->config['point_expiry_days']
            ? date('Y-m-d', strtotime("+{$this->config['point_expiry_days']} days"))
            : null;

        $stmt = $this->db->prepare(
            "INSERT INTO loyalty_transactions
             (customer_id, points, bonus_points, transaction_type, description,
              reference_id, expiry_date, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $customerId,
            $points,
            $bonusPoints,
            $type,
            $description,
            $referenceId,
            $expiryDate
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Generate unique reward code
     */
    private function generateRewardCode(): string
    {
        return 'RWD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }

    /**
     * Expire old points (run via cron)
     */
    public function expireOldPoints(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE loyalty_transactions
             SET points = 0, description = CONCAT(description, ' [EXPIRED]')
             WHERE expiry_date < CURDATE()
             AND points > 0"
        );

        $stmt->execute();
        return $stmt->rowCount();
    }
}
