<?php

namespace App\Services;

use App\Core\Database;

/**
 * CustomerRiskService
 * 
 * Calculates and manages customer risk scores:
 * - Return risk: likelihood customer will return items
 * - Purchase likelihood: probability of making a purchase
 * - Churn risk: risk of customer not returning
 */
class CustomerRiskService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get risk scores for a customer
     */
    public function getCustomerRiskScore(int $customerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM customer_risk_scores 
            WHERE customer_id = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Calculate and update all risk scores for a customer
     */
    public function calculateRiskScores(int $customerId): array
    {
        $returnRisk = $this->calculateReturnRisk($customerId);
        $purchaseLikelihood = $this->calculatePurchaseLikelihood($customerId);
        $churnRisk = $this->calculateChurnRisk($customerId);

        // Update or insert scores
        $stmt = $this->db->prepare("
            INSERT INTO customer_risk_scores 
                (customer_id, return_risk_score, purchase_likelihood_score, predicted_churn_risk, calculated_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                return_risk_score = VALUES(return_risk_score),
                purchase_likelihood_score = VALUES(purchase_likelihood_score),
                predicted_churn_risk = VALUES(predicted_churn_risk),
                calculated_at = NOW()
        ");
        $stmt->execute([$customerId, $returnRisk, $purchaseLikelihood, $churnRisk]);

        return [
            'customer_id' => $customerId,
            'return_risk' => $returnRisk,
            'purchase_likelihood' => $purchaseLikelihood,
            'churn_risk' => $churnRisk
        ];
    }

    /**
     * Calculate return risk (0-100)
     * Higher = more likely to return items
     */
    public function calculateReturnRisk(int $customerId): float
    {
        // Get transaction and return history
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT t.id) as total_purchases,
                COALESCE(SUM(t.total), 0) as total_spent
            FROM transactions t
            WHERE t.customer_id = ? AND t.status = 'completed'
        ");
        $stmt->execute([$customerId]);
        $purchases = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as return_count,
                COALESCE(SUM(refund_amount), 0) as return_value
            FROM customer_returns
            WHERE customer_id = ?
        ");
        $stmt->execute([$customerId]);
        $returns = $stmt->fetch(\PDO::FETCH_ASSOC);

        $totalPurchases = (int) ($purchases['total_purchases'] ?? 0);
        $returnCount = (int) ($returns['return_count'] ?? 0);

        if ($totalPurchases === 0) {
            return 0; // New customer, no risk data
        }

        // Calculate return rate (returns per purchase)
        $returnRate = $returnCount / $totalPurchases;

        // Base score from return rate (up to 60 points)
        $baseScore = min($returnRate * 100, 60);

        // Recent returns weighted higher (up to 20 points)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM customer_returns 
            WHERE customer_id = ? AND returned_at > DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute([$customerId]);
        $recentReturns = (int) $stmt->fetchColumn();
        $recencyScore = min($recentReturns * 10, 20);

        // High-value returns (up to 20 points)
        $avgReturnValue = $returnCount > 0 ?
            ($returns['return_value'] / $returnCount) : 0;
        $valueScore = min($avgReturnValue / 50, 20); // $50 avg = 20 pts

        return min($baseScore + $recencyScore + $valueScore, 100);
    }

    /**
     * Calculate purchase likelihood (0-100)
     * Higher = more likely to make a purchase
     */
    public function calculatePurchaseLikelihood(int $customerId): float
    {
        // Get customer purchase history
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as purchase_count,
                COALESCE(SUM(total), 0) as lifetime_value,
                COALESCE(AVG(total), 0) as avg_order_value,
                MAX(created_at) as last_purchase,
                MIN(created_at) as first_purchase
            FROM transactions
            WHERE customer_id = ? AND status = 'completed'
        ");
        $stmt->execute([$customerId]);
        $history = $stmt->fetch(\PDO::FETCH_ASSOC);

        $purchaseCount = (int) ($history['purchase_count'] ?? 0);

        if ($purchaseCount === 0) {
            return 30; // New prospect, moderate likelihood
        }

        // Recency score (up to 40 points)
        $daysSinceLastPurchase = 999;
        if ($history['last_purchase']) {
            $daysSinceLastPurchase = (time() - strtotime($history['last_purchase'])) / 86400;
        }
        $recencyScore = max(40 - ($daysSinceLastPurchase / 7), 0); // Lose ~5 pts/week

        // Frequency score (up to 30 points)
        $frequencyScore = min($purchaseCount * 3, 30);

        // Value score (up to 20 points)
        $avgOrder = (float) ($history['avg_order_value'] ?? 0);
        $valueScore = min($avgOrder / 25, 20); // $500 AOV = 20 pts

        // Engagement bonus (up to 10 points)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM customer_certifications 
            WHERE customer_id = ?
        ");
        $stmt->execute([$customerId]);
        $certs = (int) $stmt->fetchColumn();
        $engagementScore = min($certs * 2, 10);

        return min($recencyScore + $frequencyScore + $valueScore + $engagementScore, 100);
    }

    /**
     * Calculate churn risk (0-100)
     * Higher = more likely to not return
     */
    public function calculateChurnRisk(int $customerId): float
    {
        $stmt = $this->db->prepare("
            SELECT 
                MAX(created_at) as last_purchase,
                COUNT(*) as purchase_count,
                AVG(DATEDIFF(t2.created_at, t1.created_at)) as avg_gap
            FROM transactions t1
            LEFT JOIN transactions t2 ON t1.customer_id = t2.customer_id 
                AND t2.created_at > t1.created_at
            WHERE t1.customer_id = ? AND t1.status = 'completed'
        ");
        $stmt->execute([$customerId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        $daysSince = 999;
        if ($data['last_purchase']) {
            $daysSince = (time() - strtotime($data['last_purchase'])) / 86400;
        }

        // Higher days since = higher churn risk
        $churnRisk = min(($daysSince / 365) * 100, 100);

        // Reduce risk if frequent purchaser
        if ((int) $data['purchase_count'] > 5) {
            $churnRisk *= 0.7;
        }

        return $churnRisk;
    }

    /**
     * Flag a customer manually
     */
    public function flagCustomer(int $customerId, string $reason, int $flaggedBy): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO customer_risk_scores (customer_id, is_flagged, flag_reason, flagged_by, flagged_at)
            VALUES (?, 1, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                is_flagged = 1,
                flag_reason = VALUES(flag_reason),
                flagged_by = VALUES(flagged_by),
                flagged_at = NOW()
        ");
        return $stmt->execute([$customerId, $reason, $flaggedBy]);
    }

    /**
     * Unflag a customer
     */
    public function unflagCustomer(int $customerId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE customer_risk_scores 
            SET is_flagged = 0, flag_reason = NULL, flagged_by = NULL, flagged_at = NULL
            WHERE customer_id = ?
        ");
        return $stmt->execute([$customerId]);
    }

    /**
     * Get high-risk customers for returns
     */
    public function getHighReturnRiskCustomers(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT crs.*, c.first_name, c.last_name, c.email
            FROM customer_risk_scores crs
            JOIN customers c ON crs.customer_id = c.id
            WHERE crs.return_risk_score >= 50
            ORDER BY crs.return_risk_score DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get likely buyers (hot leads)
     */
    public function getLikelyBuyers(int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT crs.*, c.first_name, c.last_name, c.email
            FROM customer_risk_scores crs
            JOIN customers c ON crs.customer_id = c.id
            WHERE crs.purchase_likelihood_score >= 70
            ORDER BY crs.purchase_likelihood_score DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer risk summary for POS display
     */
    public function getCustomerRiskSummary(int $customerId): array
    {
        $scores = $this->getCustomerRiskScore($customerId);

        if (!$scores) {
            // Calculate on first access
            return $this->calculateRiskScores($customerId);
        }

        // Check if scores are stale (older than 24 hours)
        $lastCalc = strtotime($scores['calculated_at'] ?? 'now');
        if ((time() - $lastCalc) > 86400) {
            return $this->calculateRiskScores($customerId);
        }

        return [
            'return_risk' => (float) ($scores['return_risk_score'] ?? 0),
            'return_risk_label' => $this->getRiskLabel($scores['return_risk_score'] ?? 0),
            'purchase_likelihood' => (float) ($scores['purchase_likelihood_score'] ?? 50),
            'purchase_likelihood_label' => $this->getLikelihoodLabel($scores['purchase_likelihood_score'] ?? 50),
            'churn_risk' => (float) ($scores['predicted_churn_risk'] ?? 0),
            'is_flagged' => (bool) ($scores['is_flagged'] ?? false),
            'flag_reason' => $scores['flag_reason'] ?? null,
            'lifetime_value' => (float) ($scores['lifetime_value'] ?? 0),
            'return_count' => (int) ($scores['return_count'] ?? 0)
        ];
    }

    private function getRiskLabel(float $score): string
    {
        if ($score >= 70)
            return 'High Risk';
        if ($score >= 40)
            return 'Medium Risk';
        if ($score >= 20)
            return 'Low Risk';
        return 'Minimal';
    }

    private function getLikelihoodLabel(float $score): string
    {
        if ($score >= 80)
            return 'Very Likely';
        if ($score >= 60)
            return 'Likely';
        if ($score >= 40)
            return 'Moderate';
        if ($score >= 20)
            return 'Unlikely';
        return 'Very Unlikely';
    }
}
