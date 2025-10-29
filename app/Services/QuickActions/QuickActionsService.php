<?php

namespace App\Services\QuickActions;

use App\Core\Database;
use App\Core\Auth;

/**
 * Quick Actions Service
 * Provides keyboard shortcuts and quick actions for staff
 */
class QuickActionsService
{
    /**
     * Get available quick actions based on user permissions
     */
    public static function getActions(): array
    {
        return [
            // Customer actions
            [
                'id' => 'new_customer',
                'label' => 'New Customer',
                'icon' => 'user-plus',
                'url' => '/store/customers/create',
                'shortcut' => 'Alt+C',
                'category' => 'Customers',
                'permission' => 'customers.create'
            ],
            [
                'id' => 'search_customer',
                'label' => 'Search Customer',
                'icon' => 'search',
                'action' => 'focusGlobalSearch',
                'shortcut' => 'Ctrl+K',
                'category' => 'General',
                'permission' => null
            ],

            // Sales actions
            [
                'id' => 'new_sale',
                'label' => 'New Sale (POS)',
                'icon' => 'cash-register',
                'url' => '/store/pos',
                'shortcut' => 'Alt+P',
                'category' => 'Sales',
                'permission' => 'pos.access'
            ],
            [
                'id' => 'new_order',
                'label' => 'New Order',
                'icon' => 'shopping-cart',
                'url' => '/store/orders/create',
                'shortcut' => 'Alt+O',
                'category' => 'Sales',
                'permission' => 'orders.create'
            ],

            // Inventory actions
            [
                'id' => 'new_product',
                'label' => 'New Product',
                'icon' => 'box',
                'url' => '/store/products/create',
                'shortcut' => 'Alt+N',
                'category' => 'Inventory',
                'permission' => 'products.create'
            ],
            [
                'id' => 'low_stock',
                'label' => 'Low Stock Report',
                'icon' => 'exclamation-triangle',
                'url' => '/store/reports/low-stock',
                'shortcut' => 'Alt+L',
                'category' => 'Inventory',
                'permission' => 'reports.view'
            ],

            // Rentals actions
            [
                'id' => 'new_rental',
                'label' => 'New Rental',
                'icon' => 'life-ring',
                'url' => '/store/rentals/reservations/create',
                'shortcut' => 'Alt+R',
                'category' => 'Rentals',
                'permission' => 'rentals.create'
            ],

            // Courses actions
            [
                'id' => 'new_enrollment',
                'label' => 'New Course Enrollment',
                'icon' => 'graduation-cap',
                'url' => '/store/courses/enrollments/create',
                'shortcut' => 'Alt+E',
                'category' => 'Courses',
                'permission' => 'courses.enroll'
            ],

            // Appointments
            [
                'id' => 'new_appointment',
                'label' => 'New Appointment',
                'icon' => 'calendar-plus',
                'url' => '/store/appointments/create',
                'shortcut' => 'Alt+A',
                'category' => 'Appointments',
                'permission' => 'appointments.create'
            ],

            // Documents
            [
                'id' => 'upload_document',
                'label' => 'Upload Document',
                'icon' => 'upload',
                'url' => '/store/documents/create',
                'shortcut' => 'Alt+U',
                'category' => 'Documents',
                'permission' => 'documents.upload'
            ],

            // Reports
            [
                'id' => 'reports_dashboard',
                'label' => 'Reports Dashboard',
                'icon' => 'chart-bar',
                'url' => '/store/reports/dashboard',
                'shortcut' => 'Alt+D',
                'category' => 'Reports',
                'permission' => 'reports.view'
            ],

            // Navigation
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => 'home',
                'url' => '/store/dashboard',
                'shortcut' => 'Alt+H',
                'category' => 'Navigation',
                'permission' => null
            ],
        ];
    }

    /**
     * Get actions filtered by user permissions
     */
    public static function getAvailableActions(): array
    {
        $allActions = self::getActions();
        $available = [];

        foreach ($allActions as $action) {
            // If no permission required or user has permission
            if ($action['permission'] === null || Auth::hasPermission($action['permission'])) {
                $available[] = $action;
            }
        }

        return $available;
    }

    /**
     * Get actions grouped by category
     */
    public static function getGroupedActions(): array
    {
        $actions = self::getAvailableActions();
        $grouped = [];

        foreach ($actions as $action) {
            $category = $action['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $action;
        }

        return $grouped;
    }

    /**
     * Get recent actions for user (from activity log)
     */
    public static function getRecentActions(int $userId, int $limit = 5): array
    {
        $sql = "SELECT DISTINCT entity_type, action
                FROM audit_logs
                WHERE user_id = ?
                AND entity_type IS NOT NULL
                AND action IN ('create', 'update', 'view')
                ORDER BY created_at DESC
                LIMIT ?";

        $results = Database::fetchAll($sql, [$userId, $limit * 2]) ?? [];

        // Convert to action format
        $actions = [];
        foreach ($results as $row) {
            $label = ucfirst($row['action']) . ' ' . ucfirst($row['entity_type']);
            $url = '/store/' . strtolower($row['entity_type']) . 's';

            $actions[] = [
                'label' => $label,
                'url' => $url,
                'icon' => self::getIconForEntity($row['entity_type']),
                'recent' => true
            ];

            if (count($actions) >= $limit) break;
        }

        return $actions;
    }

    /**
     * Get icon for entity type
     */
    private static function getIconForEntity(string $entityType): string
    {
        $icons = [
            'customer' => 'user',
            'product' => 'box',
            'order' => 'shopping-bag',
            'course' => 'graduation-cap',
            'trip' => 'ship',
            'rental' => 'life-ring',
            'document' => 'file',
            'appointment' => 'calendar'
        ];

        return $icons[$entityType] ?? 'circle';
    }

    /**
     * Get favorite actions for user (from user preferences)
     */
    public static function getFavoriteActions(int $userId): array
    {
        // TODO: Implement user preferences storage
        // For now, return default favorites
        return [
            'new_sale',
            'new_customer',
            'search_customer',
            'reports_dashboard'
        ];
    }
}
