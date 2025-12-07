<?php

namespace App\Services\Analytics;

use PDO;

/**
 * Customer Analytics Service
 * Customer segmentation, RFM analysis, lifetime value, and behavior tracking
 */
class CustomerAnalyticsService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate customer analytics for all customers
     */
    public function calculateAllCustomerAnalytics(int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT id FROM customers
            WHERE tenant_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$tenantId]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0;
        foreach ($customers as $customer) {
            $this->calculateCustomerAnalytics($customer['id'], $tenantId);
            $updated++;
        }

        return [
            'success' => true,
            'customers_updated' => $updated
        ];
    }

    /**
     * Calculate analytics for a single customer
     */
    public function calculateCustomerAnalytics(int $customerId, int $tenantId): array
    {
        // Get booking data
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_bookings,
                SUM(total_amount) as total_revenue,
                AVG(total_amount) as avg_booking_value,
                MIN(booking_date) as first_booking,
                MAX(booking_date) as last_booking,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
            FROM bookings
            WHERE customer_id = ? AND tenant_id = ?
        ");
        $stmt->execute([$customerId, $tenantId]);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate derived metrics
        $totalBookings = $metrics['total_bookings'] ?? 0;
        $totalRevenue = $metrics['total_revenue'] ?? 0;
        $avgBookingValue = $metrics['avg_booking_value'] ?? 0;
        $lifetimeValue = $totalRevenue;

        $firstBooking = $metrics['first_booking'];
        $lastBooking = $metrics['last_booking'];
        $daysSinceLastBooking = $lastBooking ?
            (int) ((time() - strtotime($lastBooking)) / 86400) : null;

        // Calculate booking frequency (bookings per month)
        $bookingFrequency = 0;
        if ($firstBooking && $lastBooking) {
            $monthsBetween = max(1, (strtotime($lastBooking) - strtotime($firstBooking)) / (30 * 86400));
            $bookingFrequency = $totalBookings / $monthsBetween;
        }

        // Cancellation rate
        $cancellationRate = $totalBookings > 0 ?
            (($metrics['cancelled_bookings'] / $totalBookings) * 100) : 0;

        // Get booking preferences
        $preferences = $this->getCustomerPreferences($customerId, $tenantId);

        // Calculate RFM score
        $rfmScore = $this->calculateRFMScore($customerId, $tenantId, [
            'last_booking' => $lastBooking,
            'total_bookings' => $totalBookings,
            'total_revenue' => $totalRevenue
        ]);

        // Determine customer segment
        $segment = $this->determineCustomerSegment($rfmScore, $daysSinceLastBooking, $totalBookings);

        // Calculate churn risk
        $churnRisk = $this->calculateChurnRisk($daysSinceLastBooking, $bookingFrequency, $cancellationRate);

        // Save analytics
        $this->db->prepare("
            INSERT INTO customer_analytics (
                tenant_id, customer_id,
                total_bookings, total_revenue, average_booking_value, lifetime_value,
                first_booking_date, last_booking_date, days_since_last_booking, booking_frequency,
                preferred_booking_method, preferred_payment_method,
                cancellation_rate,
                customer_segment, rfm_score, churn_risk_score,
                favorite_activities, favorite_instructors,
                calculated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                total_bookings = VALUES(total_bookings),
                total_revenue = VALUES(total_revenue),
                average_booking_value = VALUES(average_booking_value),
                lifetime_value = VALUES(lifetime_value),
                first_booking_date = VALUES(first_booking_date),
                last_booking_date = VALUES(last_booking_date),
                days_since_last_booking = VALUES(days_since_last_booking),
                booking_frequency = VALUES(booking_frequency),
                preferred_booking_method = VALUES(preferred_booking_method),
                preferred_payment_method = VALUES(preferred_payment_method),
                cancellation_rate = VALUES(cancellation_rate),
                customer_segment = VALUES(customer_segment),
                rfm_score = VALUES(rfm_score),
                churn_risk_score = VALUES(churn_risk_score),
                favorite_activities = VALUES(favorite_activities),
                favorite_instructors = VALUES(favorite_instructors),
                calculated_at = NOW()
        ")->execute([
            $tenantId, $customerId,
            $totalBookings, $totalRevenue, $avgBookingValue, $lifetimeValue,
            $firstBooking, $lastBooking, $daysSinceLastBooking, $bookingFrequency,
            $preferences['booking_method'] ?? null,
            $preferences['payment_method'] ?? null,
            $cancellationRate,
            $segment, $rfmScore, $churnRisk,
            json_encode($preferences['activities'] ?? []),
            json_encode($preferences['instructors'] ?? [])
        ]);

        return [
            'success' => true,
            'analytics' => [
                'total_bookings' => $totalBookings,
                'total_revenue' => $totalRevenue,
                'lifetime_value' => $lifetimeValue,
                'segment' => $segment,
                'rfm_score' => $rfmScore,
                'churn_risk' => $churnRisk
            ]
        ];
    }

    /**
     * Get customer preferences
     */
    private function getCustomerPreferences(int $customerId, int $tenantId): array
    {
        // Most common booking method
        $stmt = $this->db->prepare("
            SELECT booking_source, COUNT(*) as cnt
            FROM bookings
            WHERE customer_id = ? AND tenant_id = ?
            GROUP BY booking_source
            ORDER BY cnt DESC
            LIMIT 1
        ");
        $stmt->execute([$customerId, $tenantId]);
        $bookingMethod = $stmt->fetch(PDO::FETCH_ASSOC);

        // Most common payment method
        $stmt = $this->db->prepare("
            SELECT payment_method, COUNT(*) as cnt
            FROM payments
            WHERE customer_id = ? AND tenant_id = ?
            GROUP BY payment_method
            ORDER BY cnt DESC
            LIMIT 1
        ");
        $stmt->execute([$customerId, $tenantId]);
        $paymentMethod = $stmt->fetch(PDO::FETCH_ASSOC);

        // Favorite activities (courses, trips, etc.)
        $stmt = $this->db->prepare("
            SELECT course_id, COUNT(*) as cnt
            FROM bookings
            WHERE customer_id = ? AND tenant_id = ? AND course_id IS NOT NULL
            GROUP BY course_id
            ORDER BY cnt DESC
            LIMIT 5
        ");
        $stmt->execute([$customerId, $tenantId]);
        $activities = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        return [
            'booking_method' => $bookingMethod['booking_source'] ?? null,
            'payment_method' => $paymentMethod['payment_method'] ?? null,
            'activities' => $activities
        ];
    }

    /**
     * Calculate RFM (Recency, Frequency, Monetary) Score
     */
    private function calculateRFMScore(int $customerId, int $tenantId, array $metrics): string
    {
        // Get all customers for comparison
        $stmt = $this->db->prepare("
            SELECT
                customer_id,
                DATEDIFF(CURDATE(), MAX(booking_date)) as recency,
                COUNT(*) as frequency,
                SUM(total_amount) as monetary
            FROM bookings
            WHERE tenant_id = ?
            GROUP BY customer_id
        ");
        $stmt->execute([$tenantId]);
        $allCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate percentiles
        $recencies = array_column($allCustomers, 'recency');
        $frequencies = array_column($allCustomers, 'frequency');
        $monetaries = array_column($allCustomers, 'monetary');

        sort($recencies);
        sort($frequencies);
        rsort($monetaries);

        // Find customer's position
        $customerRecency = $metrics['last_booking'] ?
            (int) ((time() - strtotime($metrics['last_booking'])) / 86400) : 999;
        $customerFrequency = $metrics['total_bookings'];
        $customerMonetary = $metrics['total_revenue'];

        // Score 1-5 (5 is best)
        $rScore = $this->calculatePercentileScore($customerRecency, $recencies, true);
        $fScore = $this->calculatePercentileScore($customerFrequency, $frequencies, false);
        $mScore = $this->calculatePercentileScore($customerMonetary, $monetaries, false);

        return "{$rScore}{$fScore}{$mScore}";
    }

    /**
     * Calculate percentile score (1-5)
     */
    private function calculatePercentileScore(float $value, array $dataset, bool $reverse = false): int
    {
        $count = count($dataset);
        if ($count === 0) return 3;

        $position = array_search($value, $dataset);
        if ($position === false) {
            // Find closest value
            foreach ($dataset as $i => $val) {
                if ($val >= $value) {
                    $position = $i;
                    break;
                }
            }
            if ($position === false) $position = $count - 1;
        }

        $percentile = ($position / $count) * 100;

        if ($reverse) {
            $percentile = 100 - $percentile;
        }

        if ($percentile >= 80) return 5;
        if ($percentile >= 60) return 4;
        if ($percentile >= 40) return 3;
        if ($percentile >= 20) return 2;
        return 1;
    }

    /**
     * Determine customer segment based on behavior
     */
    private function determineCustomerSegment(string $rfmScore, ?int $daysSince, int $totalBookings): string
    {
        $score = (int) substr($rfmScore, 0, 1); // Recency score

        if ($totalBookings === 0) {
            return 'new';
        }

        if ($totalBookings >= 10 && $score >= 4) {
            return 'vip';
        }

        if ($totalBookings >= 5 && $score >= 4) {
            return 'loyal';
        }

        if ($daysSince !== null && $daysSince > 180 && $score <= 2) {
            return 'lost';
        }

        if ($daysSince !== null && $daysSince > 90 && $score <= 3) {
            return 'at_risk';
        }

        if ($totalBookings >= 3) {
            return 'regular';
        }

        return 'occasional';
    }

    /**
     * Calculate churn risk score (0-100)
     */
    private function calculateChurnRisk(?int $daysSince, float $frequency, float $cancellationRate): float
    {
        $risk = 0;

        // Recency risk (40% weight)
        if ($daysSince === null) {
            $risk += 40;
        } elseif ($daysSince > 365) {
            $risk += 40;
        } elseif ($daysSince > 180) {
            $risk += 30;
        } elseif ($daysSince > 90) {
            $risk += 20;
        } elseif ($daysSince > 60) {
            $risk += 10;
        }

        // Frequency risk (30% weight)
        if ($frequency < 0.5) { // Less than 1 booking every 2 months
            $risk += 30;
        } elseif ($frequency < 1) {
            $risk += 20;
        } elseif ($frequency < 2) {
            $risk += 10;
        }

        // Cancellation risk (30% weight)
        if ($cancellationRate > 50) {
            $risk += 30;
        } elseif ($cancellationRate > 30) {
            $risk += 20;
        } elseif ($cancellationRate > 10) {
            $risk += 10;
        }

        return min(100, $risk);
    }

    /**
     * Get customer segments distribution
     */
    public function getSegmentDistribution(int $tenantId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                customer_segment,
                COUNT(*) as customer_count,
                SUM(total_revenue) as segment_revenue,
                AVG(lifetime_value) as avg_ltv,
                AVG(churn_risk_score) as avg_churn_risk
            FROM customer_analytics
            WHERE tenant_id = ?
            GROUP BY customer_segment
            ORDER BY segment_revenue DESC
        ");
        $stmt->execute([$tenantId]);
        $segments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'segments' => $segments
        ];
    }

    /**
     * Get high-value customers
     */
    public function getHighValueCustomers(int $tenantId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT
                ca.*,
                c.first_name,
                c.last_name,
                c.email,
                c.phone
            FROM customer_analytics ca
            JOIN customers c ON ca.customer_id = c.id
            WHERE ca.tenant_id = ?
            ORDER BY ca.lifetime_value DESC
            LIMIT ?
        ");
        $stmt->execute([$tenantId, $limit]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'customers' => $customers,
            'count' => count($customers)
        ];
    }

    /**
     * Get at-risk customers
     */
    public function getAtRiskCustomers(int $tenantId, float $minChurnRisk = 60): array
    {
        $stmt = $this->db->prepare("
            SELECT
                ca.*,
                c.first_name,
                c.last_name,
                c.email,
                c.phone
            FROM customer_analytics ca
            JOIN customers c ON ca.customer_id = c.id
            WHERE ca.tenant_id = ?
              AND ca.churn_risk_score >= ?
              AND ca.customer_segment IN ('at_risk', 'lost')
            ORDER BY ca.churn_risk_score DESC, ca.lifetime_value DESC
        ");
        $stmt->execute([$tenantId, $minChurnRisk]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'customers' => $customers,
            'count' => count($customers)
        ];
    }

    /**
     * Get customer cohort analysis
     */
    public function getCohortAnalysis(int $tenantId, string $cohortType = 'month'): array
    {
        $dateFormat = $cohortType === 'month' ? '%Y-%m' : '%Y-Q%q';

        $stmt = $this->db->prepare("
            SELECT
                DATE_FORMAT(first_booking_date, ?) as cohort,
                COUNT(*) as cohort_size,
                AVG(total_bookings) as avg_bookings,
                AVG(lifetime_value) as avg_ltv,
                SUM(total_revenue) as total_revenue
            FROM customer_analytics
            WHERE tenant_id = ?
              AND first_booking_date IS NOT NULL
            GROUP BY cohort
            ORDER BY cohort DESC
        ");
        $stmt->execute([$dateFormat, $tenantId]);
        $cohorts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'cohorts' => $cohorts
        ];
    }
}
