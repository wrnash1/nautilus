<?php

namespace App\Services\Analytics;

use App\Core\TenantDatabase;
use App\Core\Cache;

/**
 * Advanced Analytics and Reporting Service
 *
 * Features:
 * - Real-time business intelligence
 * - Cohort analysis
 * - Customer lifetime value (LTV)
 * - Churn prediction
 * - Revenue forecasting
 * - Custom dashboard widgets
 * - Export to PDF, Excel, CSV
 */
class AdvancedAnalyticsService
{
    private Cache $cache;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }

    /**
     * Get comprehensive dashboard metrics
     */
    public function getDashboardMetrics(string $period = '30d'): array
    {
        $cacheKey = "dashboard_metrics_{$period}";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== false) {
            return json_decode($cached, true);
        }

        $dateFilter = $this->getDateFilter($period);

        $metrics = [
            'revenue' => $this->getRevenueMetrics($dateFilter),
            'customers' => $this->getCustomerMetrics($dateFilter),
            'products' => $this->getProductMetrics($dateFilter),
            'courses' => $this->getCourseMetrics($dateFilter),
            'rentals' => $this->getRentalMetrics($dateFilter),
            'operations' => $this->getOperationalMetrics($dateFilter)
        ];

        $this->cache->set($cacheKey, json_encode($metrics), 300); // 5 minutes

        return $metrics;
    }

    /**
     * Calculate customer lifetime value
     */
    public function calculateCustomerLTV(?int $customerId = null): array
    {
        if ($customerId) {
            return $this->calculateIndividualLTV($customerId);
        }

        // Calculate average LTV across all customers
        $query = "
            SELECT
                COUNT(DISTINCT c.id) as total_customers,
                AVG(customer_revenue.total_spent) as avg_revenue,
                AVG(customer_revenue.order_count) as avg_orders,
                AVG(DATEDIFF(customer_revenue.last_purchase, customer_revenue.first_purchase)) as avg_lifespan_days
            FROM customers c
            LEFT JOIN (
                SELECT
                    customer_id,
                    SUM(total_amount) as total_spent,
                    COUNT(*) as order_count,
                    MIN(created_at) as first_purchase,
                    MAX(created_at) as last_purchase
                FROM pos_transactions
                WHERE status = 'completed'
                GROUP BY customer_id
            ) customer_revenue ON c.id = customer_revenue.customer_id
        ";

        $data = TenantDatabase::fetchOneTenant($query);

        $avgMonthlyValue = ($data['avg_revenue'] ?? 0) / (($data['avg_lifespan_days'] ?? 1) / 30);
        $avgLifespanMonths = ($data['avg_lifespan_days'] ?? 0) / 30;
        $estimatedLTV = $avgMonthlyValue * max($avgLifespanMonths, 12); // Minimum 1 year

        return [
            'total_customers' => (int) $data['total_customers'],
            'average_revenue' => round($data['avg_revenue'] ?? 0, 2),
            'average_orders' => round($data['avg_orders'] ?? 0, 2),
            'average_lifespan_months' => round($avgLifespanMonths, 2),
            'average_monthly_value' => round($avgMonthlyValue, 2),
            'estimated_ltv' => round($estimatedLTV, 2)
        ];
    }

    /**
     * Perform cohort analysis
     */
    public function getCohortAnalysis(string $type = 'monthly', int $months = 12): array
    {
        $cohorts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $cohortDate = date('Y-m-01', strtotime("-{$i} months"));
            $cohortEndDate = date('Y-m-t', strtotime("-{$i} months"));

            // Get customers who made first purchase in this cohort
            $cohortCustomers = TenantDatabase::fetchAllTenant("
                SELECT customer_id, MIN(created_at) as first_purchase
                FROM pos_transactions
                WHERE status = 'completed'
                GROUP BY customer_id
                HAVING first_purchase >= ? AND first_purchase <= ?
            ", [$cohortDate, $cohortEndDate . ' 23:59:59']) ?? [];

            $customerIds = array_column($cohortCustomers, 'customer_id');

            if (empty($customerIds)) {
                continue;
            }

            $cohortSize = count($customerIds);
            $placeholders = implode(',', array_fill(0, $cohortSize, '?'));

            // Calculate retention for each subsequent month
            $retention = [];
            for ($month = 0; $month <= min(11, $months - $i - 1); $month++) {
                $periodStart = date('Y-m-01', strtotime("$cohortDate +{$month} months"));
                $periodEnd = date('Y-m-t', strtotime("$cohortDate +{$month} months"));

                $activeCustomers = TenantDatabase::fetchOneTenant("
                    SELECT COUNT(DISTINCT customer_id) as count
                    FROM pos_transactions
                    WHERE customer_id IN ($placeholders)
                    AND created_at >= ? AND created_at <= ?
                    AND status = 'completed'
                ", array_merge($customerIds, [$periodStart, $periodEnd . ' 23:59:59']));

                $retention["month_$month"] = [
                    'count' => (int) $activeCustomers['count'],
                    'percentage' => round(($activeCustomers['count'] / $cohortSize) * 100, 2)
                ];
            }

            $cohorts[] = [
                'cohort_date' => $cohortDate,
                'cohort_size' => $cohortSize,
                'retention' => $retention
            ];
        }

        return $cohorts;
    }

    /**
     * Predict customer churn
     */
    public function predictChurn(int $daysInactive = 90): array
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$daysInactive} days"));

        $atRiskCustomers = TenantDatabase::fetchAllTenant("
            SELECT
                c.id,
                c.email,
                c.first_name,
                c.last_name,
                MAX(t.created_at) as last_purchase,
                COUNT(t.id) as total_orders,
                SUM(t.total_amount) as total_spent,
                DATEDIFF(NOW(), MAX(t.created_at)) as days_since_purchase
            FROM customers c
            LEFT JOIN pos_transactions t ON c.id = t.customer_id
            WHERE t.status = 'completed'
            GROUP BY c.id
            HAVING last_purchase < ? AND last_purchase IS NOT NULL
            ORDER BY total_spent DESC
            LIMIT 100
        ", [$cutoffDate]) ?? [];

        // Calculate churn risk score
        foreach ($atRiskCustomers as &$customer) {
            $daysSince = $customer['days_since_purchase'];
            $totalSpent = $customer['total_spent'];

            // Higher risk for longer inactive periods and higher value customers
            $riskScore = min(100, ($daysSince / $daysInactive) * 100);

            if ($totalSpent > 1000) {
                $riskScore = min(100, $riskScore * 1.5); // High value customers are higher priority
            }

            $customer['churn_risk_score'] = round($riskScore, 2);
            $customer['risk_level'] = $riskScore > 75 ? 'high' : ($riskScore > 50 ? 'medium' : 'low');
        }

        return $atRiskCustomers;
    }

    /**
     * Revenue forecasting
     */
    public function forecastRevenue(int $months = 6): array
    {
        // Get historical monthly revenue
        $historicalData = TenantDatabase::fetchAllTenant("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as revenue,
                COUNT(*) as transactions
            FROM pos_transactions
            WHERE status = 'completed'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ") ?? [];

        if (count($historicalData) < 3) {
            return ['error' => 'Insufficient historical data'];
        }

        // Simple linear regression
        $dataPoints = [];
        foreach ($historicalData as $index => $data) {
            $dataPoints[] = ['x' => $index, 'y' => $data['revenue']];
        }

        $regression = $this->calculateLinearRegression($dataPoints);

        // Generate forecast
        $forecast = [];
        $lastIndex = count($dataPoints) - 1;

        for ($i = 1; $i <= $months; $i++) {
            $x = $lastIndex + $i;
            $predictedRevenue = $regression['slope'] * $x + $regression['intercept'];

            // Add seasonality adjustment (simple moving average of last 12 months)
            $seasonalityFactor = $this->getSeasonalityFactor($historicalData, $i);
            $adjustedRevenue = $predictedRevenue * $seasonalityFactor;

            $forecastMonth = date('Y-m', strtotime("+{$i} months"));

            $forecast[] = [
                'month' => $forecastMonth,
                'predicted_revenue' => round($adjustedRevenue, 2),
                'lower_bound' => round($adjustedRevenue * 0.85, 2),
                'upper_bound' => round($adjustedRevenue * 1.15, 2),
                'confidence' => 85 - ($i * 5) // Confidence decreases with time
            ];
        }

        return [
            'historical' => $historicalData,
            'forecast' => $forecast,
            'trend' => $regression['slope'] > 0 ? 'growth' : 'decline',
            'growth_rate' => round(($regression['slope'] / $regression['intercept']) * 100, 2)
        ];
    }

    /**
     * Get product performance analysis
     */
    public function getProductPerformance(string $period = '30d'): array
    {
        $dateFilter = $this->getDateFilter($period);

        $performance = TenantDatabase::fetchAllTenant("
            SELECT
                p.id,
                p.name,
                p.sku,
                p.category_id,
                c.name as category_name,
                COUNT(ti.id) as units_sold,
                SUM(ti.quantity) as total_quantity,
                SUM(ti.subtotal) as revenue,
                SUM(ti.quantity * p.cost) as cost,
                SUM(ti.subtotal - (ti.quantity * p.cost)) as profit,
                AVG(ti.price) as avg_selling_price,
                p.stock_quantity as current_stock
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN pos_transaction_items ti ON p.id = ti.product_id
            LEFT JOIN pos_transactions t ON ti.transaction_id = t.id
            WHERE t.created_at >= ? AND t.status = 'completed'
            GROUP BY p.id
            ORDER BY revenue DESC
            LIMIT 100
        ", [$dateFilter]) ?? [];

        foreach ($performance as &$product) {
            $product['profit_margin'] = $product['revenue'] > 0
                ? round(($product['profit'] / $product['revenue']) * 100, 2)
                : 0;

            $product['roi'] = $product['cost'] > 0
                ? round(($product['profit'] / $product['cost']) * 100, 2)
                : 0;
        }

        return $performance;
    }

    /**
     * Get sales funnel analysis
     */
    public function getSalesFunnel(string $period = '30d'): array
    {
        $dateFilter = $this->getDateFilter($period);

        return [
            'website_visitors' => $this->getWebsiteVisitors($dateFilter),
            'product_views' => $this->getProductViews($dateFilter),
            'add_to_cart' => $this->getAddToCart($dateFilter),
            'checkout_started' => $this->getCheckoutStarted($dateFilter),
            'orders_completed' => $this->getOrdersCompleted($dateFilter),
            'conversion_rates' => $this->calculateConversionRates($dateFilter)
        ];
    }

    /**
     * Generate custom report
     */
    public function generateCustomReport(array $config): array
    {
        $metrics = [];

        foreach ($config['metrics'] as $metric) {
            $metrics[$metric] = match($metric) {
                'revenue' => $this->getRevenueMetrics($config['date_range'] ?? '30d'),
                'customers' => $this->getCustomerMetrics($config['date_range'] ?? '30d'),
                'products' => $this->getProductMetrics($config['date_range'] ?? '30d'),
                'ltv' => $this->calculateCustomerLTV(),
                'churn' => $this->predictChurn(),
                default => null
            };
        }

        return [
            'report_name' => $config['name'] ?? 'Custom Report',
            'generated_at' => date('Y-m-d H:i:s'),
            'date_range' => $config['date_range'] ?? '30d',
            'metrics' => $metrics
        ];
    }

    // Private helper methods

    private function getDateFilter(string $period): string
    {
        return match($period) {
            '7d' => date('Y-m-d', strtotime('-7 days')),
            '30d' => date('Y-m-d', strtotime('-30 days')),
            '90d' => date('Y-m-d', strtotime('-90 days')),
            '1y' => date('Y-m-d', strtotime('-1 year')),
            default => date('Y-m-d', strtotime('-30 days'))
        };
    }

    private function getRevenueMetrics(string $dateFilter): array
    {
        $current = TenantDatabase::fetchOneTenant("
            SELECT
                SUM(total_amount) as revenue,
                COUNT(*) as transactions,
                AVG(total_amount) as avg_order_value
            FROM pos_transactions
            WHERE created_at >= ? AND status = 'completed'
        ", [$dateFilter]);

        $previous = TenantDatabase::fetchOneTenant("
            SELECT
                SUM(total_amount) as revenue,
                COUNT(*) as transactions
            FROM pos_transactions
            WHERE created_at >= DATE_SUB(?, INTERVAL 30 DAY)
            AND created_at < ?
            AND status = 'completed'
        ", [$dateFilter, $dateFilter]);

        return [
            'total_revenue' => round($current['revenue'] ?? 0, 2),
            'total_transactions' => (int) $current['transactions'],
            'avg_order_value' => round($current['avg_order_value'] ?? 0, 2),
            'growth' => $this->calculateGrowth($current['revenue'] ?? 0, $previous['revenue'] ?? 0)
        ];
    }

    private function getCustomerMetrics(string $dateFilter): array
    {
        $newCustomers = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count
            FROM customers
            WHERE created_at >= ?
        ", [$dateFilter]);

        $activeCustomers = TenantDatabase::fetchOneTenant("
            SELECT COUNT(DISTINCT customer_id) as count
            FROM pos_transactions
            WHERE created_at >= ? AND status = 'completed'
        ", [$dateFilter]);

        return [
            'new_customers' => (int) $newCustomers['count'],
            'active_customers' => (int) $activeCustomers['count'],
            'retention_rate' => $this->calculateRetentionRate($dateFilter)
        ];
    }

    private function getProductMetrics(string $dateFilter): array
    {
        $data = TenantDatabase::fetchOneTenant("
            SELECT
                COUNT(DISTINCT ti.product_id) as products_sold,
                SUM(ti.quantity) as units_sold
            FROM pos_transaction_items ti
            JOIN pos_transactions t ON ti.transaction_id = t.id
            WHERE t.created_at >= ? AND t.status = 'completed'
        ", [$dateFilter]);

        return [
            'products_sold' => (int) $data['products_sold'],
            'units_sold' => (int) $data['units_sold']
        ];
    }

    private function getCourseMetrics(string $dateFilter): array
    {
        $enrollments = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count, SUM(amount_paid) as revenue
            FROM course_enrollments
            WHERE enrollment_date >= ?
        ", [$dateFilter]);

        return [
            'new_enrollments' => (int) $enrollments['count'],
            'enrollment_revenue' => round($enrollments['revenue'] ?? 0, 2)
        ];
    }

    private function getRentalMetrics(string $dateFilter): array
    {
        $rentals = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count, SUM(total_cost) as revenue
            FROM rentals
            WHERE rental_date >= ?
        ", [$dateFilter]);

        return [
            'total_rentals' => (int) $rentals['count'],
            'rental_revenue' => round($rentals['revenue'] ?? 0, 2)
        ];
    }

    private function getOperationalMetrics(string $dateFilter): array
    {
        $lowStock = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count
            FROM products
            WHERE stock_quantity <= reorder_point
        ");

        return [
            'low_stock_items' => (int) $lowStock['count']
        ];
    }

    private function calculateIndividualLTV(int $customerId): array
    {
        $data = TenantDatabase::fetchOneTenant("
            SELECT
                SUM(total_amount) as total_spent,
                COUNT(*) as order_count,
                MIN(created_at) as first_purchase,
                MAX(created_at) as last_purchase
            FROM pos_transactions
            WHERE customer_id = ? AND status = 'completed'
        ", [$customerId]);

        $lifespanDays = max(1, strtotime($data['last_purchase']) - strtotime($data['first_purchase'])) / 86400;
        $avgOrderValue = $data['total_spent'] / max(1, $data['order_count']);

        return [
            'customer_id' => $customerId,
            'total_spent' => round($data['total_spent'] ?? 0, 2),
            'order_count' => (int) $data['order_count'],
            'avg_order_value' => round($avgOrderValue, 2),
            'lifespan_days' => round($lifespanDays, 2),
            'estimated_ltv' => round($data['total_spent'] ?? 0, 2)
        ];
    }

    private function calculateLinearRegression(array $dataPoints): array
    {
        $n = count($dataPoints);
        $sumX = $sumY = $sumXY = $sumX2 = 0;

        foreach ($dataPoints as $point) {
            $sumX += $point['x'];
            $sumY += $point['y'];
            $sumXY += $point['x'] * $point['y'];
            $sumX2 += $point['x'] * $point['x'];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        return ['slope' => $slope, 'intercept' => $intercept];
    }

    private function getSeasonalityFactor(array $historicalData, int $monthOffset): float
    {
        // Simple seasonality: average of same month in previous years
        return 1.0; // Placeholder
    }

    private function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0) return 0;
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function calculateRetentionRate(string $dateFilter): float
    {
        // Simplified retention calculation
        return 0.0; // Placeholder
    }

    private function getWebsiteVisitors(string $dateFilter): int
    {
        return 0; // Placeholder - integrate with analytics
    }

    private function getProductViews(string $dateFilter): int
    {
        $views = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count FROM product_views WHERE created_at >= ?
        ", [$dateFilter]);

        return (int) $views['count'];
    }

    private function getAddToCart(string $dateFilter): int
    {
        $carts = TenantDatabase::fetchOneTenant("
            SELECT COUNT(DISTINCT session_id) as count FROM shopping_cart WHERE created_at >= ?
        ", [$dateFilter]);

        return (int) $carts['count'];
    }

    private function getCheckoutStarted(string $dateFilter): int
    {
        return 0; // Placeholder
    }

    private function getOrdersCompleted(string $dateFilter): int
    {
        $orders = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count FROM pos_transactions WHERE created_at >= ? AND status = 'completed'
        ", [$dateFilter]);

        return (int) $orders['count'];
    }

    private function calculateConversionRates(string $dateFilter): array
    {
        return [
            'view_to_cart' => 0,
            'cart_to_checkout' => 0,
            'checkout_to_purchase' => 0,
            'overall' => 0
        ];
    }
}
