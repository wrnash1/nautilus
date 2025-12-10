<?php

namespace App\Controllers\Reports;

use App\Core\Database;
use App\Core\Auth;

class ReportsDashboardController
{
    /**
     * Main reports dashboard
     */
    public function index()
    {
        // Get date range from filters
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

        // Get comprehensive metrics
        $metrics = [
            'revenue' => $this->getRevenueMetrics($startDate, $endDate),
            'sales' => $this->getSalesMetrics($startDate, $endDate),
            'customers' => $this->getCustomerMetrics($startDate, $endDate),
            'inventory' => $this->getInventoryMetrics(),
            'rentals' => $this->getRentalMetrics($startDate, $endDate),
            'courses' => $this->getCourseMetrics($startDate, $endDate),
            'trips' => $this->getTripMetrics($startDate, $endDate),
        ];

        // Get chart data
        $charts = [
            'revenue_trend' => $this->getRevenueTrend($startDate, $endDate),
            'sales_by_category' => $this->getSalesByCategory($startDate, $endDate),
            'top_products' => $this->getTopProducts($startDate, $endDate, 10),
            'customer_acquisition' => $this->getCustomerAcquisition($startDate, $endDate),
        ];

        require __DIR__ . '/../../Views/reports/dashboard.php';
    }

    /**
     * Get revenue metrics
     */
    private function getRevenueMetrics(string $startDate, string $endDate): array
    {
        // Total revenue from all sources
        $retail = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status = 'completed'",
            [$startDate, $endDate]
        );

        $rentals = Database::fetchOne(
            "SELECT COALESCE(SUM(total_cost), 0) as total
             FROM rental_reservations
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status IN ('picked_up', 'returned')",
            [$startDate, $endDate]
        );

        $courses = Database::fetchOne(
            "SELECT COALESCE(SUM(ce.amount_paid), 0) as total
             FROM course_enrollments ce
             JOIN course_schedules cs ON ce.schedule_id = cs.id
             WHERE DATE(cs.start_date) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $trips = Database::fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total
             FROM trip_bookings
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status IN ('confirmed', 'completed')",
            [$startDate, $endDate]
        );

        $airFills = Database::fetchOne(
            "SELECT COALESCE(SUM(cost), 0) as total
             FROM air_fills
             WHERE DATE(created_at) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $totalRevenue = (float)($retail['total'] ?? 0) +
                       (float)($rentals['total'] ?? 0) +
                       (float)($courses['total'] ?? 0) +
                       (float)($trips['total'] ?? 0) +
                       (float)($airFills['total'] ?? 0);

        return [
            'total' => $totalRevenue,
            'retail' => (float)($retail['total'] ?? 0),
            'rentals' => (float)($rentals['total'] ?? 0),
            'courses' => (float)($courses['total'] ?? 0),
            'trips' => (float)($trips['total'] ?? 0),
            'air_fills' => (float)($airFills['total'] ?? 0),
        ];
    }

    /**
     * Get sales metrics
     */
    private function getSalesMetrics(string $startDate, string $endDate): array
    {
        $transactions = Database::fetchOne(
            "SELECT
                COUNT(*) as count,
                COALESCE(AVG(total), 0) as average,
                COALESCE(MAX(total), 0) as highest
             FROM transactions
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status = 'completed'",
            [$startDate, $endDate]
        );

        $items = Database::fetchOne(
            "SELECT COALESCE(SUM(ti.quantity), 0) as total_items
             FROM transaction_items ti
             JOIN transactions t ON ti.transaction_id = t.id
             WHERE DATE(t.created_at) BETWEEN ? AND ?
             AND t.status = 'completed'",
            [$startDate, $endDate]
        );

        return [
            'transaction_count' => (int)($transactions['count'] ?? 0),
            'average_sale' => (float)($transactions['average'] ?? 0),
            'highest_sale' => (float)($transactions['highest'] ?? 0),
            'items_sold' => (int)($items['total_items'] ?? 0),
        ];
    }

    /**
     * Get customer metrics
     */
    private function getCustomerMetrics(string $startDate, string $endDate): array
    {
        $newCustomers = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM customers
             WHERE DATE(created_at) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $activeCustomers = Database::fetchOne(
            "SELECT COUNT(DISTINCT customer_id) as count
             FROM transactions
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status = 'completed'",
            [$startDate, $endDate]
        );

        $totalCustomers = Database::fetchOne(
            "SELECT COUNT(*) as count FROM customers WHERE is_active = 1"
        );

        // Customer lifetime value (top 10 customers)
        $topCustomers = Database::fetchAll(
            "SELECT c.id,
                    CONCAT(c.first_name, ' ', c.last_name) as name,
                    COALESCE(SUM(t.total), 0) as lifetime_value,
                    COUNT(t.id) as transaction_count
             FROM customers c
             LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
             WHERE c.is_active = 1
             GROUP BY c.id, c.first_name, c.last_name
             ORDER BY lifetime_value DESC
             LIMIT 10"
        );

        return [
            'new_customers' => (int)($newCustomers['count'] ?? 0),
            'active_customers' => (int)($activeCustomers['count'] ?? 0),
            'total_customers' => (int)($totalCustomers['count'] ?? 0),
            'top_customers' => $topCustomers ?? [],
        ];
    }

    /**
     * Get inventory metrics
     */
    private function getInventoryMetrics(): array
    {
        $totalProducts = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products WHERE is_active = 1"
        );

        $lowStock = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM products
             WHERE track_inventory = 1
             AND stock_quantity <= low_stock_threshold
             AND is_active = 1"
        );

        $outOfStock = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM products
             WHERE track_inventory = 1
             AND stock_quantity = 0
             AND is_active = 1"
        );

        $totalValue = Database::fetchOne(
            "SELECT COALESCE(SUM(cost * stock_quantity), 0) as total
             FROM products
             WHERE track_inventory = 1
             AND is_active = 1"
        );

        return [
            'total_products' => (int)($totalProducts['count'] ?? 0),
            'low_stock_count' => (int)($lowStock['count'] ?? 0),
            'out_of_stock_count' => (int)($outOfStock['count'] ?? 0),
            'inventory_value' => (float)($totalValue['total'] ?? 0),
        ];
    }

    /**
     * Get rental metrics
     */
    private function getRentalMetrics(string $startDate, string $endDate): array
    {
        $rentals = Database::fetchOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'picked_up' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as completed
             FROM rental_reservations
             WHERE DATE(created_at) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $equipment = Database::fetchOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance
             FROM rental_equipment"
        );

        return [
            'total_rentals' => (int)($rentals['total'] ?? 0),
            'active_rentals' => (int)($rentals['active'] ?? 0),
            'completed_rentals' => (int)($rentals['completed'] ?? 0),
            'total_equipment' => (int)($equipment['total'] ?? 0),
            'available_equipment' => (int)($equipment['available'] ?? 0),
            'maintenance_equipment' => (int)($equipment['maintenance'] ?? 0),
        ];
    }

    /**
     * Get course metrics
     */
    private function getCourseMetrics(string $startDate, string $endDate): array
    {
        $enrollments = Database::fetchOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
             FROM course_enrollments ce
             JOIN course_schedules cs ON ce.schedule_id = cs.id
             WHERE DATE(cs.start_date) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $upcoming = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM course_schedules
             WHERE status = 'scheduled'
             AND start_date >= CURDATE()"
        );

        return [
            'total_enrollments' => (int)($enrollments['total'] ?? 0),
            'completed' => (int)($enrollments['completed'] ?? 0),
            'in_progress' => (int)($enrollments['in_progress'] ?? 0),
            'upcoming_courses' => (int)($upcoming['count'] ?? 0),
        ];
    }

    /**
     * Get trip metrics
     */
    private function getTripMetrics(string $startDate, string $endDate): array
    {
        $bookings = Database::fetchOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM trip_bookings
             WHERE DATE(created_at) BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        $upcoming = Database::fetchOne(
            "SELECT COUNT(*) as count
             FROM trip_schedules
             WHERE status IN ('scheduled', 'confirmed')
             AND departure_date >= CURDATE()"
        );

        return [
            'total_bookings' => (int)($bookings['total'] ?? 0),
            'confirmed' => (int)($bookings['confirmed'] ?? 0),
            'completed' => (int)($bookings['completed'] ?? 0),
            'upcoming_trips' => (int)($upcoming['count'] ?? 0),
        ];
    }

    /**
     * Get revenue trend over time
     */
    private function getRevenueTrend(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT DATE(created_at) as date,
                    COALESCE(SUM(total), 0) as revenue
             FROM transactions
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status = 'completed'
             GROUP BY DATE(created_at)
             ORDER BY date",
            [$startDate, $endDate]
        ) ?? [];
    }

    /**
     * Get sales by category
     */
    private function getSalesByCategory(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT c.name as category,
                    COALESCE(SUM(ti.total), 0) as total
             FROM transaction_items ti
             JOIN transactions t ON ti.transaction_id = t.id
             JOIN products p ON ti.product_id = p.id
             JOIN categories c ON p.category_id = c.id
             WHERE DATE(t.created_at) BETWEEN ? AND ?
             AND t.status = 'completed'
             GROUP BY c.id, c.name
             ORDER BY total DESC",
            [$startDate, $endDate]
        ) ?? [];
    }

    /**
     * Get top selling products
     */
    private function getTopProducts(string $startDate, string $endDate, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT p.name,
                    p.sku,
                    SUM(ti.quantity) as units_sold,
                    COALESCE(SUM(ti.total), 0) as revenue
             FROM transaction_items ti
             JOIN transactions t ON ti.transaction_id = t.id
             JOIN products p ON ti.product_id = p.id
             WHERE DATE(t.created_at) BETWEEN ? AND ?
             AND t.status = 'completed'
             GROUP BY p.id, p.name, p.sku
             ORDER BY revenue DESC
             LIMIT ?",
            [$startDate, $endDate, $limit]
        ) ?? [];
    }

    /**
     * Get customer acquisition over time
     */
    private function getCustomerAcquisition(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT DATE(created_at) as date,
                    COUNT(*) as new_customers
             FROM customers
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY date",
            [$startDate, $endDate]
        ) ?? [];
    }

    /**
     * Export report data as CSV
     */
    public function export()
    {
        $type = $_GET['type'] ?? 'overview';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $filename = "nautilus_report_{$type}_" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        switch ($type) {
            case 'sales':
                $this->exportSalesReport($output, $startDate, $endDate);
                break;
            case 'customers':
                $this->exportCustomersReport($output, $startDate, $endDate);
                break;
            case 'products':
                $this->exportProductsReport($output, $startDate, $endDate);
                break;
            default:
                $this->exportOverviewReport($output, $startDate, $endDate);
        }

        fclose($output);
        exit;
    }

    private function exportOverviewReport($output, string $startDate, string $endDate): void
    {
        fputcsv($output, ['Nautilus Dive Shop - Overview Report']);
        fputcsv($output, ['Period', "$startDate to $endDate"]);
        fputcsv($output, []);

        $metrics = $this->getRevenueMetrics($startDate, $endDate);

        fputcsv($output, ['Revenue Breakdown']);
        fputcsv($output, ['Source', 'Amount']);
        fputcsv($output, ['Retail Sales', number_format($metrics['retail'], 2)]);
        fputcsv($output, ['Rentals', number_format($metrics['rentals'], 2)]);
        fputcsv($output, ['Courses', number_format($metrics['courses'], 2)]);
        fputcsv($output, ['Trips', number_format($metrics['trips'], 2)]);
        fputcsv($output, ['Air Fills', number_format($metrics['air_fills'], 2)]);
        fputcsv($output, ['Total Revenue', number_format($metrics['total'], 2)]);
    }

    private function exportSalesReport($output, string $startDate, string $endDate): void
    {
        fputcsv($output, ['Date', 'Transaction ID', 'Customer', 'Items', 'Total', 'Payment Method', 'Status']);

        $sales = Database::fetchAll(
            "SELECT t.*,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    (SELECT COUNT(*) FROM transaction_items WHERE transaction_id = t.id) as item_count
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             WHERE DATE(t.created_at) BETWEEN ? AND ?
             ORDER BY t.created_at DESC",
            [$startDate, $endDate]
        );

        foreach ($sales as $sale) {
            fputcsv($output, [
                $sale['created_at'],
                $sale['id'],
                $sale['customer_name'] ?? 'Walk-in',
                $sale['item_count'],
                number_format($sale['total'], 2),
                $sale['payment_method'] ?? '',
                $sale['status']
            ]);
        }
    }

    private function exportCustomersReport($output, string $startDate, string $endDate): void
    {
        fputcsv($output, ['Customer Name', 'Email', 'Phone', 'Total Purchases', 'Lifetime Value', 'Last Purchase']);

        $customers = Database::fetchAll(
            "SELECT c.*,
                    COUNT(t.id) as purchase_count,
                    COALESCE(SUM(t.total), 0) as lifetime_value,
                    MAX(t.created_at) as last_purchase
             FROM customers c
             LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY lifetime_value DESC"
        );

        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['first_name'] . ' ' . $customer['last_name'],
                $customer['email'],
                $customer['phone'] ?? '',
                $customer['purchase_count'],
                number_format($customer['lifetime_value'], 2),
                $customer['last_purchase'] ?? 'Never'
            ]);
        }
    }

    private function exportProductsReport($output, string $startDate, string $endDate): void
    {
        fputcsv($output, ['Product', 'SKU', 'Category', 'Units Sold', 'Revenue', 'Current Stock']);

        $products = $this->getTopProducts($startDate, $endDate, 100);

        foreach ($products as $product) {
            fputcsv($output, [
                $product['name'],
                $product['sku'],
                $product['category'] ?? '',
                $product['units_sold'],
                number_format($product['revenue'], 2),
                $product['stock_quantity'] ?? 'N/A'
            ]);
        }
    }
}
