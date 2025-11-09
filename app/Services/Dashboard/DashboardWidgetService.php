<?php

namespace App\Services\Dashboard;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Dashboard Widget Service
 *
 * Configurable dashboard widgets and data visualization
 */
class DashboardWidgetService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Get all widgets for user's dashboard
     */
    public function getUserDashboard(int $userId): array
    {
        try {
            // Get user's widget configuration
            $widgets = TenantDatabase::fetchAllTenant(
                "SELECT w.*, dw.position, dw.size, dw.settings
                 FROM dashboard_widgets dw
                 JOIN widget_types w ON dw.widget_type_id = w.id
                 WHERE dw.user_id = ?
                 AND dw.is_active = 1
                 ORDER BY dw.position",
                [$userId]
            ) ?? [];

            // Load data for each widget
            foreach ($widgets as &$widget) {
                $widget['data'] = $this->getWidgetData(
                    $widget['widget_code'],
                    json_decode($widget['settings'] ?? '{}', true) ?? []
                );
            }

            return [
                'success' => true,
                'widgets' => $widgets
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get user dashboard failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get data for specific widget type
     */
    private function getWidgetData(string $widgetCode, array $settings): array
    {
        switch ($widgetCode) {
            case 'sales_today':
                return $this->getSalesTodayData();

            case 'sales_chart':
                return $this->getSalesChartData($settings);

            case 'top_products':
                return $this->getTopProductsData($settings);

            case 'low_stock_alerts':
                return $this->getLowStockAlertsData($settings);

            case 'recent_transactions':
                return $this->getRecentTransactionsData($settings);

            case 'customer_stats':
                return $this->getCustomerStatsData($settings);

            case 'revenue_by_category':
                return $this->getRevenueByCategoryData($settings);

            case 'upcoming_courses':
                return $this->getUpcomingCoursesData($settings);

            case 'active_rentals':
                return $this->getActiveRentalsData($settings);

            case 'pending_orders':
                return $this->getPendingOrdersData($settings);

            case 'inventory_value':
                return $this->getInventoryValueData();

            case 'monthly_comparison':
                return $this->getMonthlyComparisonData($settings);

            default:
                return [];
        }
    }

    /**
     * Sales Today Widget
     */
    private function getSalesTodayData(): array
    {
        $today = date('Y-m-d');

        $todayStats = TenantDatabase::fetchOneTenant(
            "SELECT
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(AVG(total_amount), 0) as average_sale
             FROM pos_transactions
             WHERE DATE(transaction_date) = ?
             AND status = 'completed'",
            [$today]
        );

        // Compare to yesterday
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yesterdayStats = TenantDatabase::fetchOneTenant(
            "SELECT COALESCE(SUM(total_amount), 0) as total_sales
             FROM pos_transactions
             WHERE DATE(transaction_date) = ?
             AND status = 'completed'",
            [$yesterday]
        );

        $percentChange = 0;
        if ($yesterdayStats['total_sales'] > 0) {
            $percentChange = (($todayStats['total_sales'] - $yesterdayStats['total_sales'])
                / $yesterdayStats['total_sales']) * 100;
        }

        return [
            'total_sales' => round($todayStats['total_sales'], 2),
            'transaction_count' => $todayStats['transaction_count'],
            'average_sale' => round($todayStats['average_sale'], 2),
            'percent_change' => round($percentChange, 1),
            'trend' => $percentChange >= 0 ? 'up' : 'down'
        ];
    }

    /**
     * Sales Chart Widget (last N days)
     */
    private function getSalesChartData(array $settings): array
    {
        $days = $settings['days'] ?? 30;
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        $endDate = date('Y-m-d');

        $salesData = TenantDatabase::fetchAllTenant(
            "SELECT
                DATE(transaction_date) as date,
                COUNT(*) as transaction_count,
                COALESCE(SUM(total_amount), 0) as total_sales,
                COALESCE(SUM(total_amount - tax_amount), 0) as subtotal
             FROM pos_transactions
             WHERE transaction_date BETWEEN ? AND ?
             AND status = 'completed'
             GROUP BY DATE(transaction_date)
             ORDER BY date",
            [$startDate, $endDate]
        );

        return [
            'labels' => array_column($salesData, 'date'),
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => array_column($salesData, 'total_sales'),
                    'color' => '#3b82f6'
                ],
                [
                    'label' => 'Transactions',
                    'data' => array_column($salesData, 'transaction_count'),
                    'color' => '#10b981'
                ]
            ]
        ];
    }

    /**
     * Top Products Widget
     */
    private function getTopProductsData(array $settings): array
    {
        $days = $settings['days'] ?? 30;
        $limit = $settings['limit'] ?? 10;
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        $endDate = date('Y-m-d');

        $topProducts = TenantDatabase::fetchAllTenant(
            "SELECT
                p.id,
                p.name,
                p.sku,
                SUM(ti.quantity) as units_sold,
                SUM(ti.subtotal) as revenue,
                COUNT(DISTINCT ti.transaction_id) as transaction_count
             FROM pos_transaction_items ti
             JOIN pos_transactions t ON ti.transaction_id = t.id
             JOIN products p ON ti.product_id = p.id
             WHERE t.transaction_date BETWEEN ? AND ?
             AND t.status = 'completed'
             GROUP BY p.id
             ORDER BY units_sold DESC
             LIMIT ?",
            [$startDate, $endDate, $limit]
        );

        return [
            'period_days' => $days,
            'products' => $topProducts
        ];
    }

    /**
     * Low Stock Alerts Widget
     */
    private function getLowStockAlertsData(array $settings): array
    {
        $limit = $settings['limit'] ?? 20;

        $lowStockProducts = TenantDatabase::fetchAllTenant(
            "SELECT
                id,
                sku,
                name,
                stock_quantity,
                low_stock_threshold,
                (low_stock_threshold - stock_quantity) as units_below_threshold,
                cost,
                (stock_quantity * cost) as current_value
             FROM products
             WHERE stock_quantity <= low_stock_threshold
             AND is_active = 1
             ORDER BY (low_stock_threshold - stock_quantity) DESC
             LIMIT ?",
            [$limit]
        );

        return [
            'alert_count' => count($lowStockProducts),
            'products' => $lowStockProducts
        ];
    }

    /**
     * Recent Transactions Widget
     */
    private function getRecentTransactionsData(array $settings): array
    {
        $limit = $settings['limit'] ?? 10;

        $transactions = TenantDatabase::fetchAllTenant(
            "SELECT
                t.id,
                t.transaction_number,
                t.transaction_date,
                t.total_amount,
                t.payment_method,
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                CONCAT(u.first_name, ' ', u.last_name) as cashier_name
             FROM pos_transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE t.status = 'completed'
             ORDER BY t.transaction_date DESC
             LIMIT ?",
            [$limit]
        );

        return [
            'transactions' => $transactions
        ];
    }

    /**
     * Customer Stats Widget
     */
    private function getCustomerStatsData(array $settings): array
    {
        $days = $settings['days'] ?? 30;
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $stats = TenantDatabase::fetchOneTenant(
            "SELECT
                COUNT(DISTINCT c.id) as total_customers,
                COUNT(DISTINCT CASE
                    WHEN c.created_at >= ? THEN c.id
                END) as new_customers,
                COUNT(DISTINCT CASE
                    WHEN t.transaction_date >= ? THEN t.customer_id
                END) as active_customers
             FROM customers c
             LEFT JOIN pos_transactions t ON c.id = t.customer_id
                AND t.status = 'completed'",
            [$startDate, $startDate]
        );

        // Top customers by revenue
        $topCustomers = TenantDatabase::fetchAllTenant(
            "SELECT
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                COUNT(t.id) as transaction_count,
                SUM(t.total_amount) as total_spent
             FROM customers c
             JOIN pos_transactions t ON c.id = t.customer_id
             WHERE t.transaction_date >= ?
             AND t.status = 'completed'
             GROUP BY c.id
             ORDER BY total_spent DESC
             LIMIT 5",
            [$startDate]
        );

        return [
            'total_customers' => $stats['total_customers'],
            'new_customers' => $stats['new_customers'],
            'active_customers' => $stats['active_customers'],
            'period_days' => $days,
            'top_customers' => $topCustomers
        ];
    }

    /**
     * Revenue by Category Widget
     */
    private function getRevenueByCategoryData(array $settings): array
    {
        $days = $settings['days'] ?? 30;
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $categoryRevenue = TenantDatabase::fetchAllTenant(
            "SELECT
                COALESCE(pc.name, 'Uncategorized') as category,
                COUNT(DISTINCT ti.transaction_id) as transaction_count,
                SUM(ti.quantity) as units_sold,
                SUM(ti.subtotal) as revenue
             FROM pos_transaction_items ti
             JOIN pos_transactions t ON ti.transaction_id = t.id
             JOIN products p ON ti.product_id = p.id
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE t.transaction_date >= ?
             AND t.status = 'completed'
             GROUP BY COALESCE(pc.name, 'Uncategorized')
             ORDER BY revenue DESC",
            [$startDate]
        );

        return [
            'period_days' => $days,
            'labels' => array_column($categoryRevenue, 'category'),
            'revenue' => array_column($categoryRevenue, 'revenue'),
            'units_sold' => array_column($categoryRevenue, 'units_sold')
        ];
    }

    /**
     * Upcoming Courses Widget
     */
    private function getUpcomingCoursesData(array $settings): array
    {
        $limit = $settings['limit'] ?? 10;

        $courses = TenantDatabase::fetchAllTenant(
            "SELECT
                c.id,
                c.title,
                c.start_date,
                c.end_date,
                c.max_participants,
                COUNT(e.id) as enrolled_count,
                (c.max_participants - COUNT(e.id)) as spots_remaining,
                CONCAT(u.first_name, ' ', u.last_name) as instructor_name
             FROM courses c
             LEFT JOIN course_enrollments e ON c.id = e.course_id
                AND e.status = 'enrolled'
             LEFT JOIN users u ON c.instructor_id = u.id
             WHERE c.start_date >= CURDATE()
             AND c.is_active = 1
             GROUP BY c.id
             ORDER BY c.start_date
             LIMIT ?",
            [$limit]
        );

        return [
            'courses' => $courses
        ];
    }

    /**
     * Active Rentals Widget
     */
    private function getActiveRentalsData(array $settings): array
    {
        $rentals = TenantDatabase::fetchAllTenant(
            "SELECT
                r.id,
                r.rental_number,
                r.rental_date,
                r.expected_return_date,
                DATEDIFF(r.expected_return_date, CURDATE()) as days_remaining,
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                e.serial_number,
                e.name as equipment_name,
                r.total_amount
             FROM equipment_rentals r
             JOIN customers c ON r.customer_id = c.id
             JOIN equipment e ON r.equipment_id = e.id
             WHERE r.status = 'active'
             ORDER BY r.expected_return_date",
            []
        );

        // Count overdue
        $overdueCount = 0;
        foreach ($rentals as $rental) {
            if ($rental['days_remaining'] < 0) {
                $overdueCount++;
            }
        }

        return [
            'total_active' => count($rentals),
            'overdue_count' => $overdueCount,
            'rentals' => $rentals
        ];
    }

    /**
     * Pending Orders Widget
     */
    private function getPendingOrdersData(array $settings): array
    {
        $orders = TenantDatabase::fetchAllTenant(
            "SELECT
                po.id,
                po.po_number,
                po.order_date,
                po.expected_delivery_date,
                po.status,
                po.total,
                v.vendor_name,
                COUNT(poi.id) as item_count
             FROM purchase_orders po
             LEFT JOIN vendors v ON po.vendor_id = v.id
             LEFT JOIN purchase_order_items poi ON po.id = poi.purchase_order_id
             WHERE po.status IN ('submitted', 'approved', 'ordered')
             GROUP BY po.id
             ORDER BY po.expected_delivery_date",
            []
        );

        return [
            'order_count' => count($orders),
            'orders' => $orders
        ];
    }

    /**
     * Inventory Value Widget
     */
    private function getInventoryValueData(): array
    {
        $stats = TenantDatabase::fetchOneTenant(
            "SELECT
                COUNT(*) as total_products,
                SUM(stock_quantity) as total_units,
                SUM(stock_quantity * cost) as total_value,
                SUM(stock_quantity * price) as retail_value,
                COUNT(CASE WHEN stock_quantity <= low_stock_threshold THEN 1 END) as low_stock_count,
                COUNT(CASE WHEN stock_quantity = 0 THEN 1 END) as out_of_stock_count
             FROM products
             WHERE is_active = 1",
            []
        );

        return [
            'total_products' => $stats['total_products'],
            'total_units' => $stats['total_units'],
            'cost_value' => round($stats['total_value'], 2),
            'retail_value' => round($stats['retail_value'], 2),
            'potential_margin' => round($stats['retail_value'] - $stats['total_value'], 2),
            'low_stock_count' => $stats['low_stock_count'],
            'out_of_stock_count' => $stats['out_of_stock_count']
        ];
    }

    /**
     * Monthly Comparison Widget
     */
    private function getMonthlyComparisonData(array $settings): array
    {
        $months = $settings['months'] ?? 6;

        $monthlyData = TenantDatabase::fetchAllTenant(
            "SELECT
                DATE_FORMAT(transaction_date, '%Y-%m') as month,
                COUNT(*) as transaction_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as average_sale,
                COUNT(DISTINCT customer_id) as unique_customers
             FROM pos_transactions
             WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             AND status = 'completed'
             GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
             ORDER BY month",
            [$months]
        );

        return [
            'months' => $months,
            'labels' => array_column($monthlyData, 'month'),
            'sales' => array_column($monthlyData, 'total_sales'),
            'transactions' => array_column($monthlyData, 'transaction_count'),
            'customers' => array_column($monthlyData, 'unique_customers')
        ];
    }

    /**
     * Add widget to user dashboard
     */
    public function addWidget(int $userId, string $widgetCode, array $settings = []): array
    {
        try {
            // Get widget type
            $widgetType = TenantDatabase::fetchOneTenant(
                "SELECT id FROM widget_types WHERE widget_code = ?",
                [$widgetCode]
            );

            if (!$widgetType) {
                return ['success' => false, 'error' => 'Widget type not found'];
            }

            // Get next position
            $maxPosition = TenantDatabase::fetchOneTenant(
                "SELECT MAX(position) as max_pos FROM dashboard_widgets WHERE user_id = ?",
                [$userId]
            );

            $position = ($maxPosition['max_pos'] ?? 0) + 1;

            // Add widget
            $widgetId = TenantDatabase::insertTenant('dashboard_widgets', [
                'user_id' => $userId,
                'widget_type_id' => $widgetType['id'],
                'position' => $position,
                'size' => $settings['size'] ?? 'medium',
                'settings' => json_encode($settings),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'widget_id' => $widgetId,
                'message' => 'Widget added successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Add widget failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update widget settings
     */
    public function updateWidget(int $widgetId, array $settings): array
    {
        try {
            TenantDatabase::updateTenant('dashboard_widgets', [
                'settings' => json_encode($settings),
                'size' => $settings['size'] ?? 'medium',
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$widgetId]);

            return [
                'success' => true,
                'message' => 'Widget updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Update widget failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove widget from dashboard
     */
    public function removeWidget(int $widgetId, int $userId): array
    {
        try {
            TenantDatabase::queryTenant(
                "DELETE FROM dashboard_widgets WHERE id = ? AND user_id = ?",
                [$widgetId, $userId]
            );

            return [
                'success' => true,
                'message' => 'Widget removed successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Remove widget failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Reorder widgets
     */
    public function reorderWidgets(int $userId, array $widgetOrder): array
    {
        try {
            foreach ($widgetOrder as $position => $widgetId) {
                TenantDatabase::updateTenant('dashboard_widgets', [
                    'position' => $position + 1
                ], 'id = ? AND user_id = ?', [$widgetId, $userId]);
            }

            return [
                'success' => true,
                'message' => 'Widgets reordered successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Reorder widgets failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
