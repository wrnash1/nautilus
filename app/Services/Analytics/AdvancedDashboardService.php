<?php

namespace App\Services\Analytics;

use App\Core\Database;
use App\Services\Reports\ReportService;

/**
 * Advanced Dashboard Service
 *
 * Provides comprehensive business analytics and KPI metrics
 * for executive dashboards and business intelligence
 */
class AdvancedDashboardService
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    /**
     * Get comprehensive dashboard overview
     */
    public function getDashboardOverview(string $startDate, string $endDate): array
    {
        return [
            'sales_metrics' => $this->getSalesMetrics($startDate, $endDate),
            'customer_metrics' => $this->getCustomerMetrics($startDate, $endDate),
            'product_metrics' => $this->getProductMetrics($startDate, $endDate),
            'inventory_metrics' => $this->getInventoryMetrics(),
            'course_metrics' => $this->getCourseMetrics($startDate, $endDate),
            'rental_metrics' => $this->getRentalMetrics($startDate, $endDate),
            'trends' => $this->getTrendAnalysis($startDate, $endDate),
            'performance' => $this->getPerformanceIndicators($startDate, $endDate)
        ];
    }

    /**
     * Advanced sales metrics with comparisons
     */
    public function getSalesMetrics(string $startDate, string $endDate): array
    {
        // Current period metrics
        $current = $this->reportService->getSalesMetrics($startDate, $endDate);

        // Calculate previous period for comparison
        $days = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $prevStart = date('Y-m-d', strtotime($startDate . " -$days days"));
        $prevEnd = date('Y-m-d', strtotime($startDate . " -1 day"));
        $previous = $this->reportService->getSalesMetrics($prevStart, $prevEnd);

        // Calculate growth rates
        $revenueGrowth = $this->calculateGrowthRate(
            $previous['total_revenue'] ?? 0,
            $current['total_revenue'] ?? 0
        );

        $transactionGrowth = $this->calculateGrowthRate(
            $previous['total_transactions'] ?? 0,
            $current['total_transactions'] ?? 0
        );

        return [
            'current' => $current,
            'previous' => $previous,
            'revenue_growth' => $revenueGrowth,
            'transaction_growth' => $transactionGrowth,
            'avg_daily_revenue' => $current['total_revenue'] / max($days, 1),
            'avg_transaction_value' => $current['avg_order_value'] ?? 0
        ];
    }

    /**
     * Customer analytics and metrics
     */
    public function getCustomerMetrics(string $startDate, string $endDate): array
    {
        // New customers in period
        $newCustomers = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM customers
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND is_active = 1",
            [$startDate, $endDate]
        );

        // Repeat customer rate
        $repeatCustomers = Database::fetchOne(
            "SELECT COUNT(DISTINCT customer_id) as count
             FROM (
                 SELECT customer_id, COUNT(*) as purchase_count
                 FROM transactions
                 WHERE DATE(transaction_date) BETWEEN ? AND ?
                 AND status = 'completed'
                 GROUP BY customer_id
                 HAVING purchase_count > 1
             ) repeat_purchasers",
            [$startDate, $endDate]
        );

        // Customer lifetime value (CLV)
        $clv = Database::fetchOne(
            "SELECT AVG(total_spent) as avg_clv
             FROM (
                 SELECT customer_id, SUM(total) as total_spent
                 FROM transactions
                 WHERE status = 'completed'
                 GROUP BY customer_id
             ) customer_totals"
        );

        // Customer retention rate
        $retention = $this->calculateCustomerRetention($startDate, $endDate);

        return [
            'new_customers' => $newCustomers['count'] ?? 0,
            'repeat_customers' => $repeatCustomers['count'] ?? 0,
            'avg_customer_lifetime_value' => $clv['avg_clv'] ?? 0,
            'retention_rate' => $retention,
            'top_customers' => $this->reportService->getTopCustomers(10, $startDate, $endDate)
        ];
    }

    /**
     * Product performance metrics
     */
    public function getProductMetrics(string $startDate, string $endDate): array
    {
        // Best selling products
        $bestSellers = $this->reportService->getBestSellingProducts(10, $startDate, $endDate);

        // Product category performance
        $categoryRevenue = $this->reportService->getRevenueByCategory($startDate, $endDate);

        // Product velocity (how fast products sell)
        $velocity = Database::fetchAll(
            "SELECT p.id, p.name, p.sku,
                    COUNT(ti.id) as order_frequency,
                    SUM(ti.quantity) as total_sold,
                    AVG(p.stock_quantity) as avg_stock
             FROM products p
             LEFT JOIN transaction_items ti ON p.id = ti.product_id
             LEFT JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.status = 'completed'
             AND DATE(t.transaction_date) BETWEEN ? AND ?
             GROUP BY p.id
             HAVING total_sold > 0
             ORDER BY order_frequency DESC
             LIMIT 20",
            [$startDate, $endDate]
        );

        return [
            'best_sellers' => $bestSellers,
            'category_performance' => $categoryRevenue,
            'product_velocity' => $velocity ?? []
        ];
    }

    /**
     * Inventory health metrics
     */
    public function getInventoryMetrics(): array
    {
        // Low stock items
        $lowStock = Database::fetchAll(
            "SELECT COUNT(*) as count
             FROM products
             WHERE stock_quantity <= low_stock_threshold
             AND is_active = 1"
        );

        // Out of stock items
        $outOfStock = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM products
             WHERE stock_quantity = 0
             AND is_active = 1"
        );

        // Total inventory value
        $inventoryValue = Database::fetchOne(
            "SELECT
                SUM(stock_quantity * cost) as total_cost_value,
                SUM(stock_quantity * retail_price) as total_retail_value,
                COUNT(*) as total_products
             FROM products
             WHERE is_active = 1"
        );

        // Inventory turnover ratio (last 30 days)
        $turnover = Database::fetchOne(
            "SELECT
                SUM(ti.quantity * p.cost) as cogs,
                AVG(p.stock_quantity * p.cost) as avg_inventory_value
             FROM transaction_items ti
             INNER JOIN products p ON ti.product_id = p.id
             INNER JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.status = 'completed'
             AND t.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );

        $turnoverRatio = 0;
        if (($turnover['avg_inventory_value'] ?? 0) > 0) {
            $turnoverRatio = ($turnover['cogs'] ?? 0) / $turnover['avg_inventory_value'];
        }

        return [
            'low_stock_count' => $lowStock[0]['count'] ?? 0,
            'out_of_stock_count' => $outOfStock['count'] ?? 0,
            'total_inventory_cost' => $inventoryValue['total_cost_value'] ?? 0,
            'total_inventory_retail' => $inventoryValue['total_retail_value'] ?? 0,
            'total_products' => $inventoryValue['total_products'] ?? 0,
            'turnover_ratio_30d' => round($turnoverRatio, 2)
        ];
    }

    /**
     * Course and training metrics
     */
    public function getCourseMetrics(string $startDate, string $endDate): array
    {
        // Total enrollments
        $enrollments = Database::fetchOne(
            "SELECT COUNT(*) as total,
                    COUNT(DISTINCT customer_id) as unique_students,
                    SUM(amount_paid) as total_revenue
             FROM course_enrollments
             WHERE DATE(enrollment_date) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        // Completion rate
        $completionRate = Database::fetchOne(
            "SELECT
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM course_enrollments
             WHERE DATE(enrollment_date) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $rate = 0;
        if (($completionRate['total_enrollments'] ?? 0) > 0) {
            $rate = ($completionRate['completed'] / $completionRate['total_enrollments']) * 100;
        }

        // Popular courses
        $popularCourses = Database::fetchAll(
            "SELECT c.id, c.name, c.course_code,
                    COUNT(ce.id) as enrollment_count,
                    SUM(ce.amount_paid) as revenue
             FROM courses c
             INNER JOIN course_enrollments ce ON c.id = ce.course_id
             WHERE DATE(ce.enrollment_date) BETWEEN ? AND ?
             GROUP BY c.id
             ORDER BY enrollment_count DESC
             LIMIT 10",
            [$startDate, $endDate]
        );

        return [
            'total_enrollments' => $enrollments['total'] ?? 0,
            'unique_students' => $enrollments['unique_students'] ?? 0,
            'course_revenue' => $enrollments['total_revenue'] ?? 0,
            'completion_rate' => round($rate, 2),
            'popular_courses' => $popularCourses ?? []
        ];
    }

    /**
     * Equipment rental metrics
     */
    public function getRentalMetrics(string $startDate, string $endDate): array
    {
        // Total rentals and revenue
        $rentals = Database::fetchOne(
            "SELECT COUNT(*) as total_rentals,
                    SUM(total_cost) as total_revenue,
                    AVG(total_cost) as avg_rental_value
             FROM rental_transactions
             WHERE DATE(rental_date) BETWEEN ? AND ?
             AND status != 'cancelled'",
            [$startDate, $endDate]
        );

        // Equipment utilization rate
        $utilization = Database::fetchAll(
            "SELECT ret.name as equipment_type,
                    COUNT(DISTINCT re.id) as total_equipment,
                    COUNT(rt.id) as rental_count,
                    SUM(rt.total_cost) as revenue
             FROM rental_equipment_types ret
             LEFT JOIN rental_equipment re ON ret.id = re.equipment_type_id
             LEFT JOIN rental_transactions rt ON re.id = rt.equipment_id
                AND DATE(rt.rental_date) BETWEEN ? AND ?
                AND rt.status != 'cancelled'
             GROUP BY ret.id
             ORDER BY rental_count DESC",
            [$startDate, $endDate]
        );

        return [
            'total_rentals' => $rentals['total_rentals'] ?? 0,
            'rental_revenue' => $rentals['total_revenue'] ?? 0,
            'avg_rental_value' => $rentals['avg_rental_value'] ?? 0,
            'equipment_utilization' => $utilization ?? []
        ];
    }

    /**
     * Trend analysis across key metrics
     */
    public function getTrendAnalysis(string $startDate, string $endDate): array
    {
        // Daily sales trend
        $dailySales = $this->reportService->getSalesByDateRange($startDate, $endDate);

        // Calculate trend direction
        $trend = 'stable';
        if (count($dailySales) >= 2) {
            $firstWeek = array_slice($dailySales, 0, min(7, count($dailySales)));
            $lastWeek = array_slice($dailySales, -min(7, count($dailySales)));

            $firstAvg = array_sum(array_column($firstWeek, 'total')) / count($firstWeek);
            $lastAvg = array_sum(array_column($lastWeek, 'total')) / count($lastWeek);

            if ($lastAvg > $firstAvg * 1.1) {
                $trend = 'increasing';
            } elseif ($lastAvg < $firstAvg * 0.9) {
                $trend = 'decreasing';
            }
        }

        return [
            'daily_sales' => $dailySales,
            'overall_trend' => $trend,
            'payment_methods' => $this->reportService->getPaymentMethodBreakdown($startDate, $endDate)
        ];
    }

    /**
     * Key Performance Indicators
     */
    public function getPerformanceIndicators(string $startDate, string $endDate): array
    {
        // Calculate various KPIs
        $days = max(1, (strtotime($endDate) - strtotime($startDate)) / 86400);

        $sales = $this->reportService->getSalesMetrics($startDate, $endDate);

        return [
            'revenue_per_day' => ($sales['total_revenue'] ?? 0) / $days,
            'transactions_per_day' => ($sales['total_transactions'] ?? 0) / $days,
            'conversion_rate' => $this->calculateConversionRate($startDate, $endDate),
            'average_order_value' => $sales['avg_order_value'] ?? 0,
            'gross_profit_margin' => $this->calculateGrossMargin($startDate, $endDate)
        ];
    }

    /**
     * Calculate growth rate between two values
     */
    private function calculateGrowthRate(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Calculate customer retention rate
     */
    private function calculateCustomerRetention(string $startDate, string $endDate): float
    {
        // Customers who made purchases in both this period and previous period
        $days = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $prevStart = date('Y-m-d', strtotime($startDate . " -$days days"));
        $prevEnd = date('Y-m-d', strtotime($startDate . " -1 day"));

        $retained = Database::fetchOne(
            "SELECT COUNT(DISTINCT t1.customer_id) as count
             FROM transactions t1
             INNER JOIN transactions t2 ON t1.customer_id = t2.customer_id
             WHERE DATE(t1.transaction_date) BETWEEN ? AND ?
             AND DATE(t2.transaction_date) BETWEEN ? AND ?
             AND t1.status = 'completed'
             AND t2.status = 'completed'",
            [$prevStart, $prevEnd, $startDate, $endDate]
        );

        $previousCustomers = Database::fetchOne(
            "SELECT COUNT(DISTINCT customer_id) as count
             FROM transactions
             WHERE DATE(transaction_date) BETWEEN ? AND ?
             AND status = 'completed'",
            [$prevStart, $prevEnd]
        );

        if (($previousCustomers['count'] ?? 0) == 0) {
            return 0;
        }

        return round(($retained['count'] / $previousCustomers['count']) * 100, 2);
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate(string $startDate, string $endDate): float
    {
        // This would need actual visit/session tracking
        // For now, return a placeholder
        return 0;
    }

    /**
     * Calculate gross profit margin
     */
    private function calculateGrossMargin(string $startDate, string $endDate): float
    {
        $margin = Database::fetchOne(
            "SELECT
                SUM(ti.total) as revenue,
                SUM(ti.quantity * p.cost) as cogs
             FROM transaction_items ti
             INNER JOIN products p ON ti.product_id = p.id
             INNER JOIN transactions t ON ti.transaction_id = t.id
             WHERE DATE(t.transaction_date) BETWEEN ? AND ?
             AND t.status = 'completed'",
            [$startDate, $endDate]
        );

        $revenue = $margin['revenue'] ?? 0;
        $cogs = $margin['cogs'] ?? 0;

        if ($revenue == 0) {
            return 0;
        }

        return round((($revenue - $cogs) / $revenue) * 100, 2);
    }
}
