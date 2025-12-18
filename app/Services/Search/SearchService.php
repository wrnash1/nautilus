<?php

namespace App\Services\Search;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Search Service
 *
 * Universal search and advanced filtering across all entities
 */
class SearchService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Universal search across all entities
     */
    public function universalSearch(string $query, array $options = []): array
    {
        try {
            $limit = $options['limit'] ?? 50;
            $entities = $options['entities'] ?? ['products', 'customers', 'transactions', 'courses'];

            $results = [];

            if (in_array('products', $entities)) {
                $results['products'] = $this->searchProducts($query, ['limit' => $limit]);
            }

            if (in_array('customers', $entities)) {
                $results['customers'] = $this->searchCustomers($query, ['limit' => $limit]);
            }

            if (in_array('transactions', $entities)) {
                $results['transactions'] = $this->searchTransactions($query, ['limit' => $limit]);
            }

            if (in_array('courses', $entities)) {
                $results['courses'] = $this->searchCourses($query, ['limit' => $limit]);
            }

            if (in_array('equipment', $entities)) {
                $results['equipment'] = $this->searchEquipment($query, ['limit' => $limit]);
            }

            // Calculate total results
            $totalResults = 0;
            foreach ($results as $entity => $data) {
                $totalResults += count($data);
            }

            return [
                'success' => true,
                'query' => $query,
                'total_results' => $totalResults,
                'results' => $results
            ];

        } catch (\Exception $e) {
            $this->logger->error('Universal search failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Search products with filters
     */
    public function searchProducts(string $query, array $filters = []): array
    {
        $where = ["p.is_active = 1"];
        $params = [];

        // Text search
        if (!empty($query)) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        // Price range
        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }

        // Stock status
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'in_stock':
                    $where[] = "p.stock_quantity > 0";
                    break;
                case 'low_stock':
                    $where[] = "p.stock_quantity <= p.low_stock_threshold AND p.stock_quantity > 0";
                    break;
                case 'out_of_stock':
                    $where[] = "p.stock_quantity = 0";
                    break;
            }
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;
        $orderBy = $filters['order_by'] ?? 'p.name';
        $orderDir = $filters['order_dir'] ?? 'ASC';

        $products = TenantDatabase::fetchAllTenant(
            "SELECT p.*, pc.name as category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE {$whereClause}
             ORDER BY {$orderBy} {$orderDir}
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        ) ?? [];

        return $products;
    }

    /**
     * Search customers with filters
     */
    public function searchCustomers(string $query, array $filters = []): array
    {
        $where = ["1=1"];
        $params = [];

        // Text search
        if (!empty($query)) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Certification level
        if (!empty($filters['certification_level'])) {
            $where[] = "c.certification_level = ?";
            $params[] = $filters['certification_level'];
        }

        // Date range (registration date)
        if (!empty($filters['registered_from'])) {
            $where[] = "c.created_at >= ?";
            $params[] = $filters['registered_from'];
        }
        if (!empty($filters['registered_to'])) {
            $where[] = "c.created_at <= ?";
            $params[] = $filters['registered_to'];
        }

        // Has email
        if (isset($filters['has_email'])) {
            if ($filters['has_email']) {
                $where[] = "c.email IS NOT NULL AND c.email != ''";
            } else {
                $where[] = "(c.email IS NULL OR c.email = '')";
            }
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        $customers = TenantDatabase::fetchAllTenant(
            "SELECT c.*,
                    COUNT(DISTINCT t.id) as transaction_count,
                    COALESCE(SUM(t.total_amount), 0) as lifetime_value
             FROM customers c
             LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
             WHERE {$whereClause}
             GROUP BY c.id
             ORDER BY c.last_name, c.first_name
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        ) ?? [];

        return $customers;
    }

    /**
     * Search transactions with filters
     */
    public function searchTransactions(string $query, array $filters = []): array
    {
        $where = ["t.status = 'completed'"];
        $params = [];

        // Text search (transaction number, customer name)
        if (!empty($query)) {
            $where[] = "(t.transaction_number LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Date range
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(t.transaction_date) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(t.transaction_date) <= ?";
            $params[] = $filters['date_to'];
        }

        // Amount range
        if (!empty($filters['min_amount'])) {
            $where[] = "t.total_amount >= ?";
            $params[] = $filters['min_amount'];
        }
        if (!empty($filters['max_amount'])) {
            $where[] = "t.total_amount <= ?";
            $params[] = $filters['max_amount'];
        }

        // Payment method
        if (!empty($filters['payment_method'])) {
            $where[] = "t.payment_method = ?";
            $params[] = $filters['payment_method'];
        }

        // Customer
        if (!empty($filters['customer_id'])) {
            $where[] = "t.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        // Cashier
        if (!empty($filters['user_id'])) {
            $where[] = "t.user_id = ?";
            $params[] = $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        $transactions = TenantDatabase::fetchAllTenant(
            "SELECT t.*,
                    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                    CONCAT(u.first_name, ' ', u.last_name) as cashier_name
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE {$whereClause}
             ORDER BY t.transaction_date DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        ) ?? [];

        return $transactions;
    }

    /**
     * Search courses with filters
     */
    public function searchCourses(string $query, array $filters = []): array
    {
        $where = ["c.is_active = 1"];
        $params = [];

        // Text search
        if (!empty($query)) {
            $where[] = "(c.title LIKE ? OR c.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Date range
        if (!empty($filters['start_from'])) {
            $where[] = "c.start_date >= ?";
            $params[] = $filters['start_from'];
        }
        if (!empty($filters['start_to'])) {
            $where[] = "c.start_date <= ?";
            $params[] = $filters['start_to'];
        }

        // Instructor
        if (!empty($filters['instructor_id'])) {
            $where[] = "c.instructor_id = ?";
            $params[] = $filters['instructor_id'];
        }

        // Enrollment status
        if (!empty($filters['enrollment_status'])) {
            switch ($filters['enrollment_status']) {
                case 'open':
                    $where[] = "c.max_participants > (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id AND status = 'enrolled')";
                    break;
                case 'full':
                    $where[] = "c.max_participants <= (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id AND status = 'enrolled')";
                    break;
            }
        }

        // Upcoming only
        if (!empty($filters['upcoming_only'])) {
            $where[] = "c.start_date >= CURDATE()";
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        $courses = TenantDatabase::fetchAllTenant(
            "SELECT c.*,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                    COUNT(DISTINCT e.id) as enrolled_count,
                    (c.max_participants - COUNT(DISTINCT e.id)) as spots_remaining
             FROM courses c
             LEFT JOIN users u ON c.instructor_id = u.id
             LEFT JOIN course_enrollments e ON c.id = e.course_id AND e.status = 'enrolled'
             WHERE {$whereClause}
             GROUP BY c.id
             ORDER BY c.start_date
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        ) ?? [];

        return $courses;
    }

    /**
     * Search equipment with filters
     */
    public function searchEquipment(string $query, array $filters = []): array
    {
        $where = ["e.is_active = 1"];
        $params = [];

        // Text search
        if (!empty($query)) {
            $where[] = "(e.name LIKE ? OR e.serial_number LIKE ? OR e.description LIKE ?)";
            $searchTerm = "%{$query}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Equipment type
        if (!empty($filters['equipment_type'])) {
            $where[] = "e.equipment_type = ?";
            $params[] = $filters['equipment_type'];
        }

        // Availability status
        if (!empty($filters['status'])) {
            $where[] = "e.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);
        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        $equipment = TenantDatabase::fetchAllTenant(
            "SELECT e.*
             FROM equipment e
             WHERE {$whereClause}
             ORDER BY e.name
             LIMIT ? OFFSET ?",
            array_merge($params, [$limit, $offset])
        ) ?? [];

        return $equipment;
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function getSearchSuggestions(string $query, string $entity = 'products', int $limit = 10): array
    {
        try {
            $suggestions = [];

            switch ($entity) {
                case 'products':
                    $suggestions = TenantDatabase::fetchAllTenant(
                        "SELECT id, name, sku, price
                         FROM products
                         WHERE (name LIKE ? OR sku LIKE ?)
                         AND is_active = 1
                         ORDER BY name
                         LIMIT ?",
                        ["%{$query}%", "%{$query}%", $limit]
                    ) ?? [];
                    break;

                case 'customers':
                    $suggestions = TenantDatabase::fetchAllTenant(
                        "SELECT id, CONCAT(first_name, ' ', last_name) as name, email, phone
                         FROM customers
                         WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)
                         ORDER BY last_name, first_name
                         LIMIT ?",
                        ["%{$query}%", "%{$query}%", "%{$query}%", "%{$query}%", $limit]
                    ) ?? [];
                    break;

                case 'courses':
                    $suggestions = TenantDatabase::fetchAllTenant(
                        "SELECT id, title, start_date
                         FROM courses
                         WHERE title LIKE ?
                         AND is_active = 1
                         ORDER BY start_date
                         LIMIT ?",
                        ["%{$query}%", $limit]
                    ) ?? [];
                    break;
            }

            return [
                'success' => true,
                'suggestions' => $suggestions
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get search suggestions failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Save search query for analytics
     */
    public function saveSearchQuery(int $userId, string $query, string $entity, int $resultCount): void
    {
        try {
            TenantDatabase::insertTenant('search_history', [
                'user_id' => $userId,
                'search_query' => $query,
                'entity_type' => $entity,
                'result_count' => $resultCount,
                'searched_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Save search query failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get popular searches
     */
    public function getPopularSearches(int $days = 30, int $limit = 10): array
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));

            $popularSearches = TenantDatabase::fetchAllTenant(
                "SELECT search_query,
                        entity_type,
                        COUNT(*) as search_count,
                        AVG(result_count) as avg_results
                 FROM search_history
                 WHERE searched_at >= ?
                 GROUP BY search_query, entity_type
                 ORDER BY search_count DESC
                 LIMIT ?",
                [$startDate, $limit]
            ) ?? [];

            return [
                'success' => true,
                'period_days' => $days,
                'searches' => $popularSearches
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get popular searches failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user's recent searches
     */
    public function getRecentSearches(int $userId, int $limit = 10): array
    {
        try {
            $recentSearches = TenantDatabase::fetchAllTenant(
                "SELECT DISTINCT search_query, entity_type, searched_at
                 FROM search_history
                 WHERE user_id = ?
                 ORDER BY searched_at DESC
                 LIMIT ?",
                [$userId, $limit]
            ) ?? [];

            return [
                'success' => true,
                'searches' => $recentSearches
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get recent searches failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build dynamic filter query
     */
    public function buildFilterQuery(string $baseTable, array $filters): array
    {
        $where = [];
        $params = [];
        $joins = [];

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                // Handle IN operator
                if (isset($value['in'])) {
                    $placeholders = implode(',', array_fill(0, count($value['in']), '?'));
                    $where[] = "{$field} IN ({$placeholders})";
                    $params = array_merge($params, $value['in']);
                }
                // Handle range
                elseif (isset($value['min']) || isset($value['max'])) {
                    if (isset($value['min'])) {
                        $where[] = "{$field} >= ?";
                        $params[] = $value['min'];
                    }
                    if (isset($value['max'])) {
                        $where[] = "{$field} <= ?";
                        $params[] = $value['max'];
                    }
                }
            } else {
                $where[] = "{$field} = ?";
                $params[] = $value;
            }
        }

        return [
            'where' => $where,
            'params' => $params,
            'joins' => $joins
        ];
    }
}
