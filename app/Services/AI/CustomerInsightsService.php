<?php

namespace App\Services\AI;

use PDO;

/**
 * AI-Powered Customer Insights Service
 * Analyzes customer behavior and predicts churn, LTV, next purchase
 */
class CustomerInsightsService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Generate comprehensive AI insights for a customer
     */
    public function analyzeCustomer(int $customerId): array
    {
        $customer = $this->getCustomerData($customerId);

        if (!$customer) {
            return [
                'success' => false,
                'error' => 'Customer not found'
            ];
        }

        // Run various AI analyses
        $churnPrediction = $this->predictChurn($customer);
        $ltvPrediction = $this->predictLifetimeValue($customer);
        $nextPurchase = $this->predictNextPurchase($customer);
        $courseRecommendations = $this->recommendCourses($customer);
        $engagementScore = $this->calculateEngagementScore($customer);
        $segment = $this->identifyCustomerSegment($customer);

        // Store insights
        $this->storeInsights($customerId, [
            'churn' => $churnPrediction,
            'ltv' => $ltvPrediction,
            'next_purchase' => $nextPurchase,
            'courses' => $courseRecommendations,
            'engagement' => $engagementScore,
            'segment' => $segment
        ]);

        return [
            'success' => true,
            'customer_id' => $customerId,
            'churn_prediction' => $churnPrediction,
            'lifetime_value' => $ltvPrediction,
            'next_purchase' => $nextPurchase,
            'recommended_courses' => $courseRecommendations,
            'engagement' => $engagementScore,
            'segment' => $segment,
            'recommended_actions' => $this->generateRecommendedActions($churnPrediction, $engagementScore)
        ];
    }

    /**
     * Predict customer churn probability
     */
    private function predictChurn(array $customer): array
    {
        // Churn indicators
        $daysSinceLastPurchase = $customer['days_since_last_purchase'] ?? 9999;
        $daysSinceLastDive = $customer['days_since_last_dive'] ?? 9999;
        $totalPurchases = $customer['total_purchases'] ?? 0;
        $totalDives = $customer['total_dives'] ?? 0;
        $avgPurchaseInterval = $customer['avg_purchase_interval'] ?? 90;

        // Calculate churn probability (0-1)
        $churnScore = 0;

        // No recent activity
        if ($daysSinceLastPurchase > 180) $churnScore += 0.4;
        elseif ($daysSinceLastPurchase > 90) $churnScore += 0.2;

        if ($daysSinceLastDive > 180) $churnScore += 0.3;
        elseif ($daysSinceLastDive > 90) $churnScore += 0.15;

        // Low engagement
        if ($totalPurchases < 2) $churnScore += 0.2;
        if ($totalDives < 5) $churnScore += 0.1;

        // Overdue for next purchase
        if ($daysSinceLastPurchase > $avgPurchaseInterval * 1.5) $churnScore += 0.2;

        $churnScore = min(1.0, $churnScore);

        // Determine risk level
        if ($churnScore >= 0.7) $riskLevel = 'critical';
        elseif ($churnScore >= 0.5) $riskLevel = 'high';
        elseif ($churnScore >= 0.3) $riskLevel = 'medium';
        else $riskLevel = 'low';

        // Predict churn date
        $predictedChurnDate = null;
        if ($churnScore > 0.5) {
            $daysUntilChurn = (int) (90 * (1 - $churnScore));
            $predictedChurnDate = date('Y-m-d', strtotime("+{$daysUntilChurn} days"));
        }

        // Identify churn factors
        $factors = [];
        if ($daysSinceLastPurchase > 90) $factors[] = 'No recent purchases';
        if ($daysSinceLastDive > 90) $factors[] = 'No recent dives';
        if ($totalDives < 5) $factors[] = 'Low dive activity';
        if ($daysSinceLastPurchase > $avgPurchaseInterval * 1.5) $factors[] = 'Overdue for next purchase';

        return [
            'probability' => round($churnScore, 4),
            'risk_level' => $riskLevel,
            'predicted_churn_date' => $predictedChurnDate,
            'factors' => $factors,
            'confidence' => 0.75
        ];
    }

    /**
     * Predict customer lifetime value
     */
    private function predictLifetimeValue(array $customer): array
    {
        $totalRevenue = $customer['total_revenue'] ?? 0;
        $totalPurchases = $customer['total_purchases'] ?? 0;
        $monthsActive = max(1, $customer['months_active'] ?? 1);
        $avgOrderValue = $totalPurchases > 0 ? $totalRevenue / $totalPurchases : 0;

        // Calculate purchase frequency
        $purchaseFrequency = $totalPurchases / $monthsActive;

        // Predict future behavior (next 12 months)
        $predictedPurchases = $purchaseFrequency * 12;
        $predictedLTV = $totalRevenue + ($predictedPurchases * $avgOrderValue);

        // Adjust for churn risk (not calculated here, would use result from predictChurn)
        $churnAdjustment = 0.8; // Assume some attrition

        return [
            'current_value' => round($totalRevenue, 2),
            'predicted_12_month_value' => round($predictedPurchases * $avgOrderValue * $churnAdjustment, 2),
            'predicted_lifetime_value' => round($predictedLTV * $churnAdjustment, 2),
            'avg_order_value' => round($avgOrderValue, 2),
            'purchase_frequency_per_month' => round($purchaseFrequency, 2),
            'confidence' => 0.70
        ];
    }

    /**
     * Predict next purchase
     */
    private function predictNextPurchase(array $customer): array
    {
        $avgPurchaseInterval = $customer['avg_purchase_interval'] ?? 90;
        $daysSinceLastPurchase = $customer['days_since_last_purchase'] ?? 0;

        // Calculate probability of purchase in next 30 days
        $daysOverdue = max(0, $daysSinceLastPurchase - $avgPurchaseInterval);
        $probability = min(0.95, 0.3 + ($daysOverdue / $avgPurchaseInterval) * 0.65);

        // Predict date
        $predictedDate = date('Y-m-d', strtotime("+{$avgPurchaseInterval} days", strtotime($customer['last_purchase_date'] ?? 'now')));

        // Predict category based on history
        $topCategory = $customer['top_purchase_category'] ?? 'Equipment';
        $avgCategoryValue = $customer['avg_category_value'] ?? 150;

        return [
            'probability_30_days' => round($probability, 4),
            'predicted_date' => $predictedDate,
            'predicted_category' => $topCategory,
            'predicted_value' => round($avgCategoryValue, 2),
            'days_since_last_purchase' => $daysSinceLastPurchase,
            'avg_interval_days' => $avgPurchaseInterval
        ];
    }

    /**
     * Recommend courses based on customer profile
     */
    private function recommendCourses(array $customer): array
    {
        $currentCertLevel = $customer['certification_level'] ?? 'None';
        $totalDives = $customer['total_dives'] ?? 0;

        $recommendations = [];

        // Progression path
        if ($currentCertLevel === 'None' || empty($currentCertLevel)) {
            $recommendations[] = [
                'course' => 'Open Water Diver',
                'score' => 0.95,
                'reason' => 'Perfect starting point for diving'
            ];
        } elseif ($currentCertLevel === 'Open Water' && $totalDives >= 5) {
            $recommendations[] = [
                'course' => 'Advanced Open Water',
                'score' => 0.90,
                'reason' => 'Natural progression with sufficient experience'
            ];
            $recommendations[] = [
                'course' => 'Enriched Air (Nitrox)',
                'score' => 0.75,
                'reason' => 'Extend dive times and bottom times'
            ];
        } elseif ($currentCertLevel === 'Advanced' && $totalDives >= 20) {
            $recommendations[] = [
                'course' => 'Rescue Diver',
                'score' => 0.85,
                'reason' => 'Critical safety skills and potential leadership path'
            ];
        }

        // Interest-based (simplified - would analyze purchase history)
        if ($totalDives >= 10) {
            $recommendations[] = [
                'course' => 'Underwater Photography',
                'score' => 0.70,
                'reason' => 'Popular among experienced divers'
            ];
        }

        return $recommendations;
    }

    /**
     * Calculate customer engagement score
     */
    private function calculateEngagementScore(array $customer): array
    {
        $score = 0;

        // Recent activity (40 points)
        $daysSinceLastPurchase = $customer['days_since_last_purchase'] ?? 9999;
        if ($daysSinceLastPurchase <= 30) $score += 40;
        elseif ($daysSinceLastPurchase <= 60) $score += 30;
        elseif ($daysSinceLastPurchase <= 90) $score += 20;
        elseif ($daysSinceLastPurchase <= 180) $score += 10;

        // Purchase frequency (30 points)
        $totalPurchases = $customer['total_purchases'] ?? 0;
        if ($totalPurchases >= 10) $score += 30;
        elseif ($totalPurchases >= 5) $score += 20;
        elseif ($totalPurchases >= 2) $score += 10;

        // Dive activity (20 points)
        $totalDives = $customer['total_dives'] ?? 0;
        if ($totalDives >= 50) $score += 20;
        elseif ($totalDives >= 20) $score += 15;
        elseif ($totalDives >= 10) $score += 10;
        elseif ($totalDives >= 1) $score += 5;

        // Certification progress (10 points)
        $certLevel = $customer['certification_level'] ?? 'None';
        if ($certLevel === 'Divemaster' || $certLevel === 'Instructor') $score += 10;
        elseif ($certLevel === 'Rescue') $score += 8;
        elseif ($certLevel === 'Advanced') $score += 6;
        elseif ($certLevel === 'Open Water') $score += 4;

        // Determine trend
        $lastMonthScore = 0; // Would calculate from historical data
        $trend = 'stable'; // Would compare to historical

        return [
            'score' => min(100, $score),
            'level' => $this->getEngagementLevel($score),
            'trend' => $trend
        ];
    }

    /**
     * Identify customer segment
     */
    private function identifyCustomerSegment(array $customer): array
    {
        $totalRevenue = $customer['total_revenue'] ?? 0;
        $totalPurchases = $customer['total_purchases'] ?? 0;
        $totalDives = $customer['total_dives'] ?? 0;

        // Segment identification logic
        if ($totalRevenue > 5000 && $totalDives > 50) {
            $segment = 'VIP Enthusiast';
            $characteristics = ['High spending', 'Frequent diver', 'Loyal customer'];
        } elseif ($totalDives > 20 && $totalPurchases >= 5) {
            $segment = 'Active Diver';
            $characteristics = ['Regular activity', 'Good engagement'];
        } elseif ($totalPurchases >= 1 && $totalDives < 5) {
            $segment = 'Beginner';
            $characteristics = ['New to diving', 'Learning phase'];
        } elseif ($totalRevenue > 1000 && $totalDives < 10) {
            $segment = 'Equipment Buyer';
            $characteristics = ['Gear focused', 'Less dive activity'];
        } else {
            $segment = 'Casual Customer';
            $characteristics = ['Occasional purchases'];
        }

        return [
            'segment' => $segment,
            'characteristics' => $characteristics,
            'confidence' => 0.80
        ];
    }

    /**
     * Get customer data with calculated metrics
     */
    private function getCustomerData(int $customerId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                COALESCE(cert.certification_level, 'None') as certification_level,
                COALESCE(stats.total_dives, 0) as total_dives,
                COALESCE(DATEDIFF(CURDATE(), stats.last_dive_date), 9999) as days_since_last_dive,
                (SELECT COUNT(*) FROM transactions WHERE customer_id = c.id) as total_purchases,
                (SELECT SUM(total) FROM transactions WHERE customer_id = c.id) as total_revenue,
                (SELECT MAX(created_at) FROM transactions WHERE customer_id = c.id) as last_purchase_date,
                COALESCE(DATEDIFF(CURDATE(), (SELECT MAX(created_at) FROM transactions WHERE customer_id = c.id)), 9999) as days_since_last_purchase,
                TIMESTAMPDIFF(MONTH, c.created_at, CURDATE()) as months_active
            FROM customers c
            LEFT JOIN customer_certifications cert ON c.id = cert.customer_id AND cert.is_current = 1
            LEFT JOIN dive_statistics stats ON c.id = stats.customer_id
            WHERE c.id = ?
            LIMIT 1
        ");

        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Store insights in database
     */
    private function storeInsights(int $customerId, array $insights): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO customer_ai_insights (
                customer_id,
                churn_probability,
                churn_risk_level,
                predicted_churn_date,
                predicted_ltv,
                next_purchase_probability,
                predicted_next_purchase_date,
                recommended_courses,
                engagement_score,
                customer_segment,
                last_analyzed_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                churn_probability = VALUES(churn_probability),
                churn_risk_level = VALUES(churn_risk_level),
                predicted_ltv = VALUES(predicted_ltv),
                engagement_score = VALUES(engagement_score),
                last_analyzed_at = NOW()
        ");

        $stmt->execute([
            $customerId,
            $insights['churn']['probability'],
            $insights['churn']['risk_level'],
            $insights['churn']['predicted_churn_date'],
            $insights['ltv']['predicted_lifetime_value'],
            $insights['next_purchase']['probability_30_days'],
            $insights['next_purchase']['predicted_date'],
            json_encode($insights['courses']),
            $insights['engagement']['score'],
            $insights['segment']['segment']
        ]);
    }

    /**
     * Generate recommended actions
     */
    private function generateRecommendedActions(array $churn, array $engagement): array
    {
        $actions = [];

        if ($churn['risk_level'] === 'high' || $churn['risk_level'] === 'critical') {
            $actions[] = [
                'priority' => 'urgent',
                'action' => 'Send re-engagement email campaign',
                'reason' => 'High churn risk detected'
            ];
            $actions[] = [
                'priority' => 'high',
                'action' => 'Offer exclusive discount on next course',
                'reason' => 'Win back at-risk customer'
            ];
        }

        if ($engagement['score'] < 30) {
            $actions[] = [
                'priority' => 'medium',
                'action' => 'Invite to upcoming dive trip',
                'reason' => 'Low engagement - needs reactivation'
            ];
        }

        return $actions;
    }

    private function getEngagementLevel(int $score): string
    {
        if ($score >= 80) return 'Very High';
        if ($score >= 60) return 'High';
        if ($score >= 40) return 'Medium';
        if ($score >= 20) return 'Low';
        return 'Very Low';
    }
}
