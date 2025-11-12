<?php

namespace App\Services\Dashboard;

use PDO;

/**
 * Dashboard Widget Service
 *
 * Manages customizable dashboard widgets with drag-and-drop layout
 */
class WidgetService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    /**
     * Get available widget definitions
     */
    public function getAvailableWidgets(): array
    {
        return [
            'sales_today' => [
                'id' => 'sales_today',
                'name' => 'Today\'s Sales',
                'description' => 'Display today\'s sales revenue',
                'icon' => 'currency-dollar',
                'category' => 'sales',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ],
            'sales_chart' => [
                'id' => 'sales_chart',
                'name' => 'Sales Chart',
                'description' => '7-day sales trend chart',
                'icon' => 'graph-up',
                'category' => 'sales',
                'default_size' => 'large',
                'refreshable' => true,
                'configurable' => true,
                'config_options' => ['days' => [7, 14, 30]]
            ],
            'revenue_breakdown' => [
                'id' => 'revenue_breakdown',
                'name' => 'Revenue Breakdown',
                'description' => 'Revenue by category (pie chart)',
                'icon' => 'pie-chart',
                'category' => 'sales',
                'default_size' => 'medium',
                'refreshable' => true,
                'configurable' => false
            ],
            'customers_total' => [
                'id' => 'customers_total',
                'name' => 'Total Customers',
                'description' => 'Active customer count',
                'icon' => 'people',
                'category' => 'customers',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ],
            'recent_transactions' => [
                'id' => 'recent_transactions',
                'name' => 'Recent Transactions',
                'description' => 'Latest completed transactions',
                'icon' => 'clock-history',
                'category' => 'sales',
                'default_size' => 'large',
                'refreshable' => true,
                'configurable' => true,
                'config_options' => ['limit' => [5, 10, 15, 20]]
            ],
            'upcoming_events' => [
                'id' => 'upcoming_events',
                'name' => 'Upcoming Events',
                'description' => 'Courses and trips schedule',
                'icon' => 'calendar-event',
                'category' => 'events',
                'default_size' => 'large',
                'refreshable' => true,
                'configurable' => true,
                'config_options' => ['limit' => [5, 8, 10, 15]]
            ],
            'active_rentals' => [
                'id' => 'active_rentals',
                'name' => 'Active Rentals',
                'description' => 'Current rental count',
                'icon' => 'gear',
                'category' => 'rentals',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ],
            'equipment_status' => [
                'id' => 'equipment_status',
                'name' => 'Equipment Status',
                'description' => 'Equipment availability chart',
                'icon' => 'gear-wide-connected',
                'category' => 'rentals',
                'default_size' => 'medium',
                'refreshable' => true,
                'configurable' => false
            ],
            'low_stock_alert' => [
                'id' => 'low_stock_alert',
                'name' => 'Low Stock Alert',
                'description' => 'Products running low',
                'icon' => 'exclamation-triangle',
                'category' => 'inventory',
                'default_size' => 'medium',
                'refreshable' => true,
                'configurable' => true,
                'config_options' => ['limit' => [5, 10, 15]]
            ],
            'top_products' => [
                'id' => 'top_products',
                'name' => 'Top Products',
                'description' => 'Best selling products',
                'icon' => 'star-fill',
                'category' => 'inventory',
                'default_size' => 'medium',
                'refreshable' => true,
                'configurable' => true,
                'config_options' => ['limit' => [5, 10, 15], 'days' => [7, 14, 30]]
            ],
            'alerts' => [
                'id' => 'alerts',
                'name' => 'System Alerts',
                'description' => 'Important notifications',
                'icon' => 'bell',
                'category' => 'system',
                'default_size' => 'medium',
                'refreshable' => true,
                'configurable' => false
            ],
            'upcoming_courses' => [
                'id' => 'upcoming_courses',
                'name' => 'Upcoming Courses',
                'description' => 'Scheduled courses',
                'icon' => 'book',
                'category' => 'events',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ],
            'upcoming_trips' => [
                'id' => 'upcoming_trips',
                'name' => 'Upcoming Trips',
                'description' => 'Scheduled dive trips',
                'icon' => 'airplane',
                'category' => 'events',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ],
            'pending_certifications' => [
                'id' => 'pending_certifications',
                'name' => 'Pending Certifications',
                'description' => 'Certifications to be issued',
                'icon' => 'award',
                'category' => 'courses',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ],
            'air_fills_today' => [
                'id' => 'air_fills_today',
                'name' => 'Air Fills Today',
                'description' => 'Today\'s air fill count',
                'icon' => 'wind',
                'category' => 'services',
                'default_size' => 'small',
                'refreshable' => true,
                'configurable' => false
            ]
        ];
    }

    /**
     * Get user's dashboard layout
     */
    public function getUserLayout(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT widget_id, position, size, config
             FROM dashboard_widgets
             WHERE user_id = ?
             ORDER BY position"
        );
        $stmt->execute([$userId]);

        $widgets = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $widgets[] = [
                'widget_id' => $row['widget_id'],
                'position' => (int)$row['position'],
                'size' => $row['size'],
                'config' => json_decode($row['config'] ?? '{}', true)
            ];
        }

        // If no layout exists, return default layout
        if (empty($widgets)) {
            return $this->getDefaultLayout();
        }

        return $widgets;
    }

    /**
     * Get default dashboard layout
     */
    public function getDefaultLayout(): array
    {
        return [
            ['widget_id' => 'sales_today', 'position' => 0, 'size' => 'small', 'config' => []],
            ['widget_id' => 'customers_total', 'position' => 1, 'size' => 'small', 'config' => []],
            ['widget_id' => 'active_rentals', 'position' => 2, 'size' => 'small', 'config' => []],
            ['widget_id' => 'upcoming_courses', 'position' => 3, 'size' => 'small', 'config' => []],
            ['widget_id' => 'sales_chart', 'position' => 4, 'size' => 'large', 'config' => ['days' => 7]],
            ['widget_id' => 'revenue_breakdown', 'position' => 5, 'size' => 'medium', 'config' => []],
            ['widget_id' => 'upcoming_events', 'position' => 6, 'size' => 'large', 'config' => ['limit' => 8]],
            ['widget_id' => 'alerts', 'position' => 7, 'size' => 'medium', 'config' => []]
        ];
    }

    /**
     * Save user's dashboard layout
     */
    public function saveUserLayout(int $userId, array $widgets): bool
    {
        try {
            $this->db->beginTransaction();

            // Delete existing layout
            $stmt = $this->db->prepare("DELETE FROM dashboard_widgets WHERE user_id = ?");
            $stmt->execute([$userId]);

            // Insert new layout
            $stmt = $this->db->prepare(
                "INSERT INTO dashboard_widgets (user_id, widget_id, position, size, config)
                 VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($widgets as $widget) {
                $stmt->execute([
                    $userId,
                    $widget['widget_id'],
                    $widget['position'],
                    $widget['size'],
                    json_encode($widget['config'] ?? [])
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Failed to save dashboard layout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reset user's layout to default
     */
    public function resetToDefault(int $userId): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM dashboard_widgets WHERE user_id = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to reset dashboard layout: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add widget to user's dashboard
     */
    public function addWidget(int $userId, string $widgetId, ?int $position = null): bool
    {
        $availableWidgets = $this->getAvailableWidgets();

        if (!isset($availableWidgets[$widgetId])) {
            return false;
        }

        // Get current max position
        if ($position === null) {
            $stmt = $this->db->prepare(
                "SELECT MAX(position) as max_pos FROM dashboard_widgets WHERE user_id = ?"
            );
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $position = ((int)($result['max_pos'] ?? -1)) + 1;
        }

        try {
            $widget = $availableWidgets[$widgetId];
            $stmt = $this->db->prepare(
                "INSERT INTO dashboard_widgets (user_id, widget_id, position, size, config)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $userId,
                $widgetId,
                $position,
                $widget['default_size'],
                json_encode([])
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to add widget: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove widget from user's dashboard
     */
    public function removeWidget(int $userId, string $widgetId): bool
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM dashboard_widgets WHERE user_id = ? AND widget_id = ?"
            );
            $stmt->execute([$userId, $widgetId]);

            // Reorder remaining widgets
            $this->reorderWidgets($userId);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to remove widget: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update widget configuration
     */
    public function updateWidgetConfig(int $userId, string $widgetId, array $config): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE dashboard_widgets SET config = ? WHERE user_id = ? AND widget_id = ?"
            );
            $stmt->execute([
                json_encode($config),
                $userId,
                $widgetId
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update widget config: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reorder widgets after removal
     */
    private function reorderWidgets(int $userId): void
    {
        $stmt = $this->db->prepare(
            "SELECT widget_id FROM dashboard_widgets WHERE user_id = ? ORDER BY position"
        );
        $stmt->execute([$userId]);
        $widgets = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $updateStmt = $this->db->prepare(
            "UPDATE dashboard_widgets SET position = ? WHERE user_id = ? AND widget_id = ?"
        );

        foreach ($widgets as $index => $widgetId) {
            $updateStmt->execute([$index, $userId, $widgetId]);
        }
    }

    /**
     * Get widget categories
     */
    public function getCategories(): array
    {
        return [
            'sales' => ['name' => 'Sales & Revenue', 'icon' => 'currency-dollar'],
            'customers' => ['name' => 'Customers', 'icon' => 'people'],
            'inventory' => ['name' => 'Inventory', 'icon' => 'box-seam'],
            'rentals' => ['name' => 'Rentals', 'icon' => 'gear'],
            'events' => ['name' => 'Events', 'icon' => 'calendar'],
            'courses' => ['name' => 'Courses', 'icon' => 'book'],
            'services' => ['name' => 'Services', 'icon' => 'wrench'],
            'system' => ['name' => 'System', 'icon' => 'gear-fill']
        ];
    }
}
