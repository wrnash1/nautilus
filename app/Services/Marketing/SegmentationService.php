<?php

namespace App\Services\Marketing;

use PDO;

/**
 * Customer Segmentation Service
 * Build and manage dynamic customer segments
 */
class SegmentationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new customer segment
     */
    public function createSegment(array $segmentData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO customer_segments (
                tenant_id, name, description, segment_type, criteria, logic,
                auto_refresh, refresh_frequency
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $segmentData['tenant_id'],
            $segmentData['name'],
            $segmentData['description'] ?? null,
            $segmentData['segment_type'] ?? 'dynamic',
            json_encode($segmentData['criteria']),
            $segmentData['logic'] ?? 'AND',
            $segmentData['auto_refresh'] ?? true,
            $segmentData['refresh_frequency'] ?? 'daily'
        ]);

        $segmentId = $this->db->lastInsertId();

        // Immediately populate the segment
        $this->refreshSegment($segmentId);

        return [
            'success' => true,
            'segment_id' => $segmentId,
            'message' => 'Segment created and populated successfully'
        ];
    }

    /**
     * Refresh segment membership based on criteria
     */
    public function refreshSegment(int $segmentId): array
    {
        // Get segment details
        $stmt = $this->db->prepare("
            SELECT * FROM customer_segments WHERE id = ?
        ");
        $stmt->execute([$segmentId]);
        $segment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$segment) {
            return ['success' => false, 'error' => 'Segment not found'];
        }

        $criteria = json_decode($segment['criteria'], true);

        // Build SQL query from criteria
        $sql = $this->buildSegmentQuery($criteria, $segment['logic'], $segment['tenant_id']);

        // Execute query to get matching customers
        $stmt = $this->db->query($sql);
        $matchingCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Clear existing members
        $this->db->prepare("
            UPDATE segment_members
            SET is_active = FALSE, removed_at = NOW()
            WHERE segment_id = ? AND is_active = TRUE
        ")->execute([$segmentId]);

        // Add new members
        $addedCount = 0;
        foreach ($matchingCustomers as $customer) {
            $this->addCustomerToSegment(
                $segmentId,
                $customer['id'],
                $segment['tenant_id'],
                $criteria
            );
            $addedCount++;
        }

        // Update segment metadata
        $this->db->prepare("
            UPDATE customer_segments
            SET current_member_count = ?,
                last_refreshed_at = NOW(),
                next_refresh_at = ?
            WHERE id = ?
        ")->execute([
            $addedCount,
            $this->calculateNextRefresh($segment['refresh_frequency']),
            $segmentId
        ]);

        return [
            'success' => true,
            'members_added' => $addedCount,
            'total_members' => $addedCount
        ];
    }

    /**
     * Build SQL query from segment criteria
     */
    private function buildSegmentQuery(array $criteria, string $logic, int $tenantId): string
    {
        $conditions = [];

        foreach ($criteria['rules'] ?? [] as $rule) {
            $field = $rule['field'];
            $operator = $rule['operator'];
            $value = $rule['value'];

            switch ($operator) {
                case 'equals':
                    $conditions[] = "$field = " . $this->db->quote($value);
                    break;
                case 'not_equals':
                    $conditions[] = "$field != " . $this->db->quote($value);
                    break;
                case 'greater_than':
                    // Handle date functions vs regular values
                    if (strpos($value, 'DATE_SUB') !== false || strpos($value, 'NOW()') !== false) {
                        $conditions[] = "$field > $value";
                    } else {
                        $conditions[] = "$field > " . $this->db->quote($value);
                    }
                    break;
                case 'less_than':
                    if (strpos($value, 'DATE_SUB') !== false || strpos($value, 'NOW()') !== false) {
                        $conditions[] = "$field < $value";
                    } else {
                        $conditions[] = "$field < " . $this->db->quote($value);
                    }
                    break;
                case 'contains':
                    $conditions[] = "$field LIKE " . $this->db->quote("%$value%");
                    break;
                case 'not_contains':
                    $conditions[] = "$field NOT LIKE " . $this->db->quote("%$value%");
                    break;
                case 'between':
                    if (is_array($value) && count($value) == 2) {
                        $conditions[] = "$field BETWEEN {$value[0]} AND {$value[1]}";
                    }
                    break;
                case 'is_null':
                    $conditions[] = "$field IS NULL";
                    break;
                case 'is_not_null':
                    $conditions[] = "$field IS NOT NULL";
                    break;
            }
        }

        $logicOperator = $logic === 'OR' ? ' OR ' : ' AND ';
        $whereClause = implode($logicOperator, $conditions);

        return "
            SELECT
                c.id,
                c.email,
                c.first_name,
                c.last_name,
                COALESCE(SUM(o.total_amount), 0) as lifetime_value,
                COUNT(DISTINCT b.id) as total_bookings,
                MAX(b.booking_date) as last_purchase_date
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            LEFT JOIN bookings b ON c.id = b.customer_id
            WHERE c.tenant_id = $tenantId
              AND ($whereClause)
            GROUP BY c.id
        ";
    }

    /**
     * Add customer to segment
     */
    private function addCustomerToSegment(int $segmentId, int $customerId, int $tenantId, array $criteria): bool
    {
        // Get customer LTV and stats
        $stmt = $this->db->prepare("
            SELECT
                COALESCE(SUM(o.total_amount), 0) as ltv,
                COUNT(DISTINCT b.id) as total_bookings,
                MAX(b.booking_date) as last_purchase
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            LEFT JOIN bookings b ON c.id = b.customer_id
            WHERE c.id = ?
        ");
        $stmt->execute([$customerId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if already exists and is active
        $existing = $this->db->prepare("
            SELECT id FROM segment_members
            WHERE segment_id = ? AND customer_id = ? AND is_active = TRUE
        ");
        $existing->execute([$segmentId, $customerId]);

        if ($existing->fetch()) {
            return true; // Already a member
        }

        // Insert or reactivate
        $stmt = $this->db->prepare("
            INSERT INTO segment_members (
                segment_id, customer_id, tenant_id, matched_criteria,
                customer_ltv, total_bookings, last_purchase_date, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)
            ON DUPLICATE KEY UPDATE
                is_active = TRUE,
                removed_at = NULL,
                added_at = NOW(),
                matched_criteria = VALUES(matched_criteria),
                customer_ltv = VALUES(customer_ltv),
                total_bookings = VALUES(total_bookings)
        ");

        return $stmt->execute([
            $segmentId,
            $customerId,
            $tenantId,
            json_encode($criteria),
            $stats['ltv'] ?? 0,
            $stats['total_bookings'] ?? 0,
            $stats['last_purchase'] ?? null
        ]);
    }

    /**
     * Calculate RFM scores for all customers
     */
    public function calculateRFMScores(int $tenantId): array
    {
        $analysisDate = date('Y-m-d');

        // Get customer purchase data
        $stmt = $this->db->prepare("
            SELECT
                c.id as customer_id,
                DATEDIFF(?, MAX(b.booking_date)) as recency_days,
                COUNT(DISTINCT b.id) as frequency_count,
                COALESCE(SUM(o.total_amount), 0) as monetary_value
            FROM customers c
            LEFT JOIN bookings b ON c.id = b.customer_id
            LEFT JOIN orders o ON c.id = o.customer_id
            WHERE c.tenant_id = ?
              AND b.booking_date IS NOT NULL
            GROUP BY c.id
            HAVING recency_days IS NOT NULL
        ");

        $stmt->execute([$analysisDate, $tenantId]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($customers)) {
            return ['success' => true, 'processed' => 0];
        }

        // Calculate quintiles for scoring
        $recencyValues = array_column($customers, 'recency_days');
        $frequencyValues = array_column($customers, 'frequency_count');
        $monetaryValues = array_column($customers, 'monetary_value');

        $recencyQuintiles = $this->calculateQuintiles($recencyValues);
        $frequencyQuintiles = $this->calculateQuintiles($frequencyValues);
        $monetaryQuintiles = $this->calculateQuintiles($monetaryValues);

        // Score each customer
        $processedCount = 0;
        foreach ($customers as $customer) {
            $recencyScore = $this->getQuintileScore($customer['recency_days'], $recencyQuintiles, true); // Reverse for recency
            $frequencyScore = $this->getQuintileScore($customer['frequency_count'], $frequencyQuintiles);
            $monetaryScore = $this->getQuintileScore($customer['monetary_value'], $monetaryQuintiles);

            $rfmScore = $recencyScore . $frequencyScore . $monetaryScore;
            $rfmSegment = $this->getRFMSegment($recencyScore, $frequencyScore, $monetaryScore);
            $customerValue = $this->getCustomerValue($rfmScore);
            $churnRisk = $this->getChurnRisk($recencyScore, $frequencyScore);

            // Save RFM scores
            $this->db->prepare("
                INSERT INTO customer_rfm_scores (
                    customer_id, tenant_id, analysis_date,
                    recency_days, recency_score,
                    frequency_count, frequency_score,
                    monetary_value, monetary_score,
                    rfm_score, rfm_segment, customer_value, churn_risk
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    recency_days = VALUES(recency_days),
                    recency_score = VALUES(recency_score),
                    frequency_count = VALUES(frequency_count),
                    frequency_score = VALUES(frequency_score),
                    monetary_value = VALUES(monetary_value),
                    monetary_score = VALUES(monetary_score),
                    rfm_score = VALUES(rfm_score),
                    rfm_segment = VALUES(rfm_segment),
                    customer_value = VALUES(customer_value),
                    churn_risk = VALUES(churn_risk)
            ")->execute([
                $customer['customer_id'],
                $tenantId,
                $analysisDate,
                $customer['recency_days'],
                $recencyScore,
                $customer['frequency_count'],
                $frequencyScore,
                $customer['monetary_value'],
                $monetaryScore,
                $rfmScore,
                $rfmSegment,
                $customerValue,
                $churnRisk
            ]);

            $processedCount++;
        }

        return [
            'success' => true,
            'processed' => $processedCount
        ];
    }

    /**
     * Calculate quintiles for RFM scoring
     */
    private function calculateQuintiles(array $values): array
    {
        sort($values);
        $count = count($values);

        return [
            20 => $values[intval($count * 0.2)] ?? 0,
            40 => $values[intval($count * 0.4)] ?? 0,
            60 => $values[intval($count * 0.6)] ?? 0,
            80 => $values[intval($count * 0.8)] ?? 0,
        ];
    }

    /**
     * Get quintile score (1-5)
     */
    private function getQuintileScore(float $value, array $quintiles, bool $reverse = false): int
    {
        $score = 1;
        if ($value > $quintiles[80]) $score = 5;
        elseif ($value > $quintiles[60]) $score = 4;
        elseif ($value > $quintiles[40]) $score = 3;
        elseif ($value > $quintiles[20]) $score = 2;

        return $reverse ? (6 - $score) : $score;
    }

    /**
     * Determine RFM segment name
     */
    private function getRFMSegment(int $r, int $f, int $m): string
    {
        // Champions: High R, F, M
        if ($r >= 4 && $f >= 4 && $m >= 4) return 'Champions';

        // Loyal Customers: High F
        if ($f >= 4) return 'Loyal Customers';

        // Potential Loyalists: Recent but low frequency
        if ($r >= 4 && $f < 4) return 'Potential Loyalists';

        // New Customers: High recency, low frequency
        if ($r >= 4 && $f <= 2) return 'New Customers';

        // Promising: Recent moderate spenders
        if ($r >= 3 && $f >= 2 && $m >= 3) return 'Promising';

        // Need Attention: Average scores
        if ($r >= 3 && $f >= 3) return 'Need Attention';

        // At Risk: Was good, declining
        if ($r <= 2 && $f >= 3 && $m >= 3) return 'At Risk';

        // Can't Lose Them: Best customers going dormant
        if ($r <= 2 && $f >= 4 && $m >= 4) return 'Can\'t Lose Them';

        // Hibernating: Low engagement
        if ($r <= 2 && $f <= 2) return 'Hibernating';

        // Lost: Very low scores
        if ($r == 1) return 'Lost';

        return 'Other';
    }

    /**
     * Determine customer value tier
     */
    private function getCustomerValue(string $rfmScore): string
    {
        $total = array_sum(str_split($rfmScore));

        if ($total >= 12) return 'high';
        if ($total >= 8) return 'medium';
        return 'low';
    }

    /**
     * Determine churn risk
     */
    private function getChurnRisk(int $recencyScore, int $frequencyScore): string
    {
        if ($recencyScore <= 1) return 'critical';
        if ($recencyScore == 2 && $frequencyScore <= 2) return 'high';
        if ($recencyScore == 2 || $frequencyScore <= 2) return 'medium';
        return 'low';
    }

    /**
     * Calculate next refresh time
     */
    private function calculateNextRefresh(string $frequency): string
    {
        $intervals = [
            'hourly' => '+1 hour',
            'daily' => '+1 day',
            'weekly' => '+1 week',
            'realtime' => '+5 minutes'
        ];

        return date('Y-m-d H:i:s', strtotime($intervals[$frequency] ?? '+1 day'));
    }

    /**
     * Get segment members
     */
    public function getSegmentMembers(int $segmentId, int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT
                sm.*,
                c.first_name,
                c.last_name,
                c.email,
                c.phone
            FROM segment_members sm
            JOIN customers c ON sm.customer_id = c.id
            WHERE sm.segment_id = ?
              AND sm.is_active = TRUE
            ORDER BY sm.added_at DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$segmentId, $limit, $offset]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'members' => $members,
            'count' => count($members)
        ];
    }

    /**
     * Get all segments for a tenant
     */
    public function getAllSegments(int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM customer_segments
            WHERE tenant_id = ?
              AND status = 'active'
            ORDER BY current_member_count DESC
        ");

        $stmt->execute([$tenantId]);
        $segments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'segments' => $segments
        ];
    }
}
