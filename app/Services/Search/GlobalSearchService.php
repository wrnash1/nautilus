<?php

namespace App\Services\Search;

use App\Core\Database;
use PDO;

/**
 * Global Search Service
 * Provides unified search across all modules
 */
class GlobalSearchService
{
    private PDO $db;
    private int $maxResults = 100;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Perform global search
     */
    public function search(string $query, array $modules = [], ?int $limit = null): array
    {
        $limit = $limit ?? $this->maxResults;
        $results = [];

        // If no specific modules specified, search all
        if (empty($modules)) {
            $modules = ['customers', 'products', 'orders', 'courses', 'trips', 'documents', 'rentals'];
        }

        foreach ($modules as $module) {
            $method = 'search' . ucfirst($module);
            if (method_exists($this, $method)) {
                $results[$module] = $this->$method($query, $limit);
            }
        }

        return $results;
    }

    /**
     * Search customers
     */
    private function searchCustomers(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name,
                       email, phone, 'customer' as type
                FROM customers
                WHERE (first_name LIKE ? OR last_name LIKE ?
                       OR email LIKE ? OR phone LIKE ?)
                AND is_active = 1
                ORDER BY first_name, last_name
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Add metadata
        foreach ($results as &$result) {
            $result['url'] = '/store/customers/' . $result['id'];
            $result['icon'] = 'user';
            $result['module'] = 'customers';
        }

        return $results;
    }

    /**
     * Search products
     */
    private function searchProducts(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT p.id, p.name, p.sku, p.price,
                       c.name as category, 'product' as type
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)
                AND p.is_active = 1
                ORDER BY p.name
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $result['url'] = '/store/products/' . $result['id'];
            $result['icon'] = 'box';
            $result['module'] = 'products';
            $result['subtitle'] = $result['sku'] . ' - $' . number_format($result['price'], 2);
        }

        return $results;
    }

    /**
     * Search orders
     */
    private function searchOrders(string $query, int $limit): array
    {
        // Search by order ID or customer name
        $sql = "SELECT o.id, o.total, o.status, o.created_at,
                       CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                       'order' as type
                FROM orders o
                LEFT JOIN customers c ON o.customer_id = c.id
                WHERE o.id LIKE ? OR CONCAT(c.first_name, ' ', c.last_name) LIKE ?
                ORDER BY o.created_at DESC
                LIMIT ?";

        $searchTerm = '%' . $query . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $result['name'] = 'Order #' . $result['id'];
            $result['url'] = '/store/orders/' . $result['id'];
            $result['icon'] = 'shopping-bag';
            $result['module'] = 'orders';
            $result['subtitle'] = $result['customer_name'] . ' - $' . number_format($result['total'], 2);
        }

        return $results;
    }

    /**
     * Search courses
     */
    private function searchCourses(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT id, name, course_code, description, 'course' as type
                FROM courses
                WHERE name LIKE ? OR course_code LIKE ? OR description LIKE ?
                AND is_active = 1
                ORDER BY name
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $result['url'] = '/store/courses/' . $result['id'];
            $result['icon'] = 'graduation-cap';
            $result['module'] = 'courses';
            $result['subtitle'] = $result['course_code'];
        }

        return $results;
    }

    /**
     * Search trips
     */
    private function searchTrips(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT id, name, destination, description, 'trip' as type
                FROM trips
                WHERE name LIKE ? OR destination LIKE ? OR description LIKE ?
                AND is_active = 1
                ORDER BY name
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $result['url'] = '/store/trips/' . $result['id'];
            $result['icon'] = 'ship';
            $result['module'] = 'trips';
            $result['subtitle'] = $result['destination'];
        }

        return $results;
    }

    /**
     * Search documents
     */
    private function searchDocuments(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT id, title, document_type, file_name, created_at, 'document' as type
                FROM documents
                WHERE (title LIKE ? OR description LIKE ? OR file_name LIKE ?)
                AND is_active = 1
                ORDER BY created_at DESC
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $result['name'] = $result['title'];
            $result['url'] = '/store/documents/' . $result['id'];
            $result['icon'] = 'file';
            $result['module'] = 'documents';
            $result['subtitle'] = ucfirst($result['document_type']) . ' - ' . $result['file_name'];
        }

        return $results;
    }

    /**
     * Search rentals
     */
    private function searchRentals(string $query, int $limit): array
    {
        $searchTerm = '%' . $query . '%';

        $sql = "SELECT re.id, re.name, re.equipment_code, re.status,
                       'rental_equipment' as type
                FROM rental_equipment re
                WHERE re.name LIKE ? OR re.equipment_code LIKE ?
                ORDER BY re.name
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $limit]);

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            $result['url'] = '/store/rentals/equipment/' . $result['id'];
            $result['icon'] = 'life-ring';
            $result['module'] = 'rentals';
            $result['subtitle'] = $result['equipment_code'] . ' - ' . ucfirst($result['status']);
        }

        return $results;
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        $suggestions = [];

        // Customer names
        $stmt = $this->db->prepare(
            "SELECT CONCAT(first_name, ' ', last_name) as suggestion, 'customer' as type
             FROM customers
             WHERE CONCAT(first_name, ' ', last_name) LIKE ?
             AND is_active = 1
             LIMIT ?"
        );
        $stmt->execute(['%' . $query . '%', $limit]);
        $suggestions = array_merge($suggestions, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        // Product names
        $stmt = $this->db->prepare(
            "SELECT name as suggestion, 'product' as type
             FROM products
             WHERE name LIKE ?
             AND is_active = 1
             LIMIT ?"
        );
        $stmt->execute(['%' . $query . '%', $limit]);
        $suggestions = array_merge($suggestions, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        // Course names
        $stmt = $this->db->prepare(
            "SELECT name as suggestion, 'course' as type
             FROM courses
             WHERE name LIKE ?
             AND is_active = 1
             LIMIT ?"
        );
        $stmt->execute(['%' . $query . '%', $limit]);
        $suggestions = array_merge($suggestions, $stmt->fetchAll(\PDO::FETCH_ASSOC));

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Get total result count
     */
    public function getResultCount(array $results): int
    {
        $count = 0;
        foreach ($results as $moduleResults) {
            $count += count($moduleResults);
        }
        return $count;
    }

    /**
     * Search by entity ID (quick find)
     */
    public function quickFind(string $entityType, $id): ?array
    {
        switch ($entityType) {
            case 'customer':
                $sql = "SELECT *, 'customer' as type FROM customers WHERE id = ?";
                break;
            case 'order':
                $sql = "SELECT *, 'order' as type FROM orders WHERE id = ?";
                break;
            case 'product':
                $sql = "SELECT *, 'product' as type FROM products WHERE id = ? OR sku = ?";
                break;
            default:
                return null;
        }

        $stmt = $this->db->prepare($sql);
        if ($entityType === 'product') {
            $stmt->execute([$id, $id]);
        } else {
            $stmt->execute([$id]);
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
