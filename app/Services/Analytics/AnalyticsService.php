<?php

namespace App\Services\Analytics;

use PDO;

/**
 * Advanced Analytics Service
 *
 * Provides comprehensive business analytics and reporting
 */
class AnalyticsService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    /**
     * Get comprehensive dashboard metrics
     */
    public function getDashboardMetrics(string $startDate, string $endDate): array
    {
        return [
            'sales' => $this->getSalesMetrics($startDate, $endDate),
            'customers' => $this->getCustomerMetrics($startDate, $endDate),
            'products' => $this->getProductMetrics($startDate, $endDate),
            'courses' => $this->getCourseMetrics($startDate, $endDate),
            'trips' => $this->getTripMetrics($startDate, $endDate),
            'rentals' => $this->getRentalMetrics($startDate, $endDate)
        ];
    }

    /**
     * Get sales analytics
     */
    public function getSalesMetrics(string $startDate, string $endDate): array
    {
        // Total sales and orders
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_orders,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value,
                SUM(total) / COUNT(DISTINCT DATE(created_at)) as avg_daily_revenue
             FROM transactions
             WHERE status = 'completed'
             AND DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $salesData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Compare to previous period
        $days = (strtotime($endDate) - strtotime($startDate)) / 86400;
        $prevStartDate = date('Y-m-d', strtotime($startDate . " -$days days"));
        $prevEndDate = date('Y-m-d', strtotime($endDate . " -$days days"));

        $stmt->execute([$prevStartDate, $prevEndDate]);
        $prevSalesData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate growth
        $revenueGrowth = $this->calculateGrowth(
            (float)($salesData['total_revenue'] ?? 0),
            (float)($prevSalesData['total_revenue'] ?? 0)
        );

        $ordersGrowth = $this->calculateGrowth(
            (int)($salesData['total_orders'] ?? 0),
            (int)($prevSalesData['total_orders'] ?? 0)
        );

        // Daily breakdown
        $stmt = $this->db->prepare(
            "SELECT
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total) as revenue
             FROM transactions
             WHERE status = 'completed'
             AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date"
        );
        $stmt->execute([$startDate, $endDate]);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sales by payment method
        $stmt = $this->db->prepare(
            "SELECT
                payment_method,
                COUNT(*) as count,
                SUM(total) as amount
             FROM transactions
             WHERE status = 'completed'
             AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY payment_method"
        );
        $stmt->execute([$startDate, $endDate]);
        $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_revenue' => (float)($salesData['total_revenue'] ?? 0),
            'total_orders' => (int)($salesData['total_orders'] ?? 0),
            'average_order_value' => (float)($salesData['average_order_value'] ?? 0),
            'avg_daily_revenue' => (float)($salesData['avg_daily_revenue'] ?? 0),
            'revenue_growth' => $revenueGrowth,
            'orders_growth' => $ordersGrowth,
            'daily_breakdown' => $dailyData,
            'payment_methods' => $paymentMethods
        ];
    }

    /**
     * Get customer analytics
     */
    public function getCustomerMetrics(string $startDate, string $endDate): array
    {
        // New customers
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count
             FROM customers
             WHERE DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $newCustomers = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Active customers (made purchase)
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT customer_id) as count
             FROM transactions
             WHERE status = 'completed'
             AND DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $activeCustomers = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Repeat customers
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as count
             FROM (
                 SELECT customer_id, COUNT(*) as order_count
                 FROM transactions
                 WHERE status = 'completed'
                 AND DATE(created_at) BETWEEN ? AND ?
                 GROUP BY customer_id
                 HAVING order_count > 1
             ) repeat_customers"
        );
        $stmt->execute([$startDate, $endDate]);
        $repeatCustomers = (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

        // Customer lifetime value (top 10)
        $stmt = $this->db->prepare(
            "SELECT
                c.id,
                c.first_name,
                c.last_name,
                COUNT(t.id) as total_orders,
                SUM(t.total) as lifetime_value
             FROM customers c
             JOIN transactions t ON c.id = t.customer_id
             WHERE t.status = 'completed'
             GROUP BY c.id, c.first_name, c.last_name
             ORDER BY lifetime_value DESC
             LIMIT 10"
        );
        $stmt->execute();
        $topCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Customer acquisition by source
        $stmt = $this->db->prepare(
            "SELECT
                acquisition_source,
                COUNT(*) as count
             FROM customers
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND acquisition_source IS NOT NULL
             GROUP BY acquisition_source"
        );
        $stmt->execute([$startDate, $endDate]);
        $acquisitionSources = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
            'repeat_customers' => $repeatCustomers,
            'repeat_rate' => $activeCustomers > 0 ? ($repeatCustomers / $activeCustomers) * 100 : 0,
            'top_customers' => $topCustomers,
            'acquisition_sources' => $acquisitionSources
        ];
    }

    /**
     * Get product analytics
     */
    public function getProductMetrics(string $startDate, string $endDate): array
    {
        // Best sellers
        $stmt = $this->db->prepare(
            "SELECT
                p.id,
                p.name,
                p.sku,
                SUM(ti.quantity) as units_sold,
                SUM(ti.total) as revenue,
                AVG(ti.unit_price) as avg_price
             FROM products p
             JOIN transaction_items ti ON p.id = ti.product_id
             JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.status = 'completed'
             AND DATE(t.created_at) BETWEEN ? AND ?
             GROUP BY p.id, p.name, p.sku
             ORDER BY revenue DESC
             LIMIT 20"
        );
        $stmt->execute([$startDate, $endDate]);
        $bestSellers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Category performance
        $stmt = $this->db->prepare(
            "SELECT
                pc.name as category,
                COUNT(DISTINCT p.id) as product_count,
                SUM(ti.quantity) as units_sold,
                SUM(ti.total) as revenue
             FROM product_categories pc
             JOIN products p ON pc.id = p.category_id
             JOIN transaction_items ti ON p.id = ti.product_id
             JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.status = 'completed'
             AND DATE(t.created_at) BETWEEN ? AND ?
             GROUP BY pc.id, pc.name
             ORDER BY revenue DESC"
        );
        $stmt->execute([$startDate, $endDate]);
        $categoryPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Profit margins
        $stmt = $this->db->prepare(
            "SELECT
                SUM(ti.total) as total_revenue,
                SUM(ti.quantity * p.cost_price) as total_cost
             FROM transaction_items ti
             JOIN products p ON ti.product_id = p.id
             JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.status = 'completed'
             AND DATE(t.created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $profitData = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalRevenue = (float)($profitData['total_revenue'] ?? 0);
        $totalCost = (float)($profitData['total_cost'] ?? 0);
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'best_sellers' => $bestSellers,
            'category_performance' => $categoryPerformance,
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin
        ];
    }

    /**
     * Get course analytics
     */
    public function getCourseMetrics(string $startDate, string $endDate): array
    {
        // Course enrollments
        $stmt = $this->db->prepare(
            "SELECT
                c.name as course_name,
                COUNT(ce.id) as enrollments,
                SUM(ce.amount_paid) as revenue,
                AVG(ce.amount_paid) as avg_price
             FROM courses c
             JOIN course_enrollments ce ON c.id = ce.course_id
             WHERE DATE(ce.enrollment_date) BETWEEN ? AND ?
             GROUP BY c.id, c.name
             ORDER BY enrollments DESC"
        );
        $stmt->execute([$startDate, $endDate]);
        $courseEnrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Completion rates
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
             FROM course_enrollments
             WHERE DATE(enrollment_date) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $completionData = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalEnrollments = (int)($completionData['total_enrollments'] ?? 0);
        $completionRate = $totalEnrollments > 0
            ? ((int)($completionData['completed'] ?? 0) / $totalEnrollments) * 100
            : 0;

        return [
            'course_enrollments' => $courseEnrollments,
            'total_enrollments' => $totalEnrollments,
            'completed' => (int)($completionData['completed'] ?? 0),
            'in_progress' => (int)($completionData['in_progress'] ?? 0),
            'cancelled' => (int)($completionData['cancelled'] ?? 0),
            'completion_rate' => $completionRate
        ];
    }

    /**
     * Get trip analytics
     */
    public function getTripMetrics(string $startDate, string $endDate): array
    {
        // Trip bookings
        $stmt = $this->db->prepare(
            "SELECT
                t.name as trip_name,
                t.destination,
                COUNT(tb.id) as bookings,
                SUM(tb.total_amount) as revenue,
                AVG(tb.total_amount) as avg_price
             FROM trips t
             JOIN trip_bookings tb ON t.id = tb.trip_id
             WHERE DATE(tb.booking_date) BETWEEN ? AND ?
             GROUP BY t.id, t.name, t.destination
             ORDER BY revenue DESC"
        );
        $stmt->execute([$startDate, $endDate]);
        $tripBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Popular destinations
        $stmt = $this->db->prepare(
            "SELECT
                t.destination,
                COUNT(tb.id) as booking_count,
                SUM(tb.total_amount) as revenue
             FROM trips t
             JOIN trip_bookings tb ON t.id = tb.trip_id
             WHERE DATE(tb.booking_date) BETWEEN ? AND ?
             GROUP BY t.destination
             ORDER BY booking_count DESC
             LIMIT 10"
        );
        $stmt->execute([$startDate, $endDate]);
        $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'trip_bookings' => $tripBookings,
            'popular_destinations' => $destinations
        ];
    }

    /**
     * Get rental analytics
     */
    public function getRentalMetrics(string $startDate, string $endDate): array
    {
        // Rental performance
        $stmt = $this->db->prepare(
            "SELECT
                ret.name as equipment_type,
                COUNT(rr.id) as rental_count,
                SUM(rr.total_cost) as revenue,
                AVG(rr.total_cost) as avg_rental_price
             FROM rental_equipment_types ret
             JOIN rental_equipment re ON ret.id = re.equipment_type_id
             JOIN rental_reservations rr ON re.id = rr.equipment_id
             WHERE DATE(rr.rental_date) BETWEEN ? AND ?
             GROUP BY ret.id, ret.name
             ORDER BY revenue DESC"
        );
        $stmt->execute([$startDate, $endDate]);
        $rentalPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Utilization rate
        $stmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT re.id) as total_equipment,
                COUNT(DISTINCT CASE WHEN rr.status = 'picked_up' THEN re.id END) as rented_equipment
             FROM rental_equipment re
             LEFT JOIN rental_reservations rr ON re.id = rr.equipment_id
             WHERE DATE(rr.rental_date) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $utilizationData = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalEquipment = (int)($utilizationData['total_equipment'] ?? 1);
        $utilizationRate = ($utilizationData['rented_equipment'] ?? 0) / $totalEquipment * 100;

        return [
            'rental_performance' => $rentalPerformance,
            'utilization_rate' => $utilizationRate
        ];
    }

    /**
     * Get revenue breakdown by source
     */
    public function getRevenueBreakdown(string $startDate, string $endDate): array
    {
        $breakdown = [
            'retail' => 0,
            'courses' => 0,
            'trips' => 0,
            'rentals' => 0,
            'air_fills' => 0
        ];

        // Retail sales
        $stmt = $this->db->prepare(
            "SELECT SUM(total) as amount FROM transactions
             WHERE status = 'completed' AND transaction_type = 'sale'
             AND DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $breakdown['retail'] = (float)($stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0);

        // Course revenue
        $stmt = $this->db->prepare(
            "SELECT SUM(amount_paid) as amount FROM course_enrollments
             WHERE DATE(enrollment_date) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $breakdown['courses'] = (float)($stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0);

        // Trip revenue
        $stmt = $this->db->prepare(
            "SELECT SUM(total_amount) as amount FROM trip_bookings
             WHERE status IN ('confirmed', 'completed')
             AND DATE(booking_date) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $breakdown['trips'] = (float)($stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0);

        // Rental revenue
        $stmt = $this->db->prepare(
            "SELECT SUM(total_cost) as amount FROM rental_reservations
             WHERE status IN ('picked_up', 'returned')
             AND DATE(rental_date) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $breakdown['rentals'] = (float)($stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0);

        // Air fills revenue
        $stmt = $this->db->prepare(
            "SELECT SUM(cost) as amount FROM air_fills
             WHERE DATE(created_at) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $breakdown['air_fills'] = (float)($stmt->fetch(PDO::FETCH_ASSOC)['amount'] ?? 0);

        $total = array_sum($breakdown);

        return [
            'breakdown' => $breakdown,
            'total' => $total,
            'percentages' => [
                'retail' => $total > 0 ? ($breakdown['retail'] / $total) * 100 : 0,
                'courses' => $total > 0 ? ($breakdown['courses'] / $total) * 100 : 0,
                'trips' => $total > 0 ? ($breakdown['trips'] / $total) * 100 : 0,
                'rentals' => $total > 0 ? ($breakdown['rentals'] / $total) * 100 : 0,
                'air_fills' => $total > 0 ? ($breakdown['air_fills'] / $total) * 100 : 0
            ]
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Get hourly sales pattern
     */
    public function getHourlySalesPattern(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                HOUR(created_at) as hour,
                COUNT(*) as order_count,
                SUM(total) as revenue
             FROM transactions
             WHERE status = 'completed'
             AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY HOUR(created_at)
             ORDER BY hour"
        );
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get day of week performance
     */
    public function getDayOfWeekPerformance(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                DAYNAME(created_at) as day_name,
                DAYOFWEEK(created_at) as day_number,
                COUNT(*) as order_count,
                SUM(total) as revenue,
                AVG(total) as avg_order_value
             FROM transactions
             WHERE status = 'completed'
             AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DAYNAME(created_at), DAYOFWEEK(created_at)
             ORDER BY day_number"
        );
        $stmt->execute([$startDate, $endDate]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
