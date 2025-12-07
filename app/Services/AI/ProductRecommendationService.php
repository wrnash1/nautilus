<?php

namespace App\Services\AI;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * AI Product Recommendation Service
 *
 * Machine learning-based product recommendations using collaborative filtering
 */
class ProductRecommendationService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Get personalized product recommendations for customer
     */
    public function getRecommendationsForCustomer(int $customerId, int $limit = 10): array
    {
        try {
            // Get customer's purchase history
            $purchaseHistory = $this->getCustomerPurchaseHistory($customerId);

            // Get customer's browsing history
            $browsingHistory = $this->getCustomerBrowsingHistory($customerId);

            // Build recommendation score for each product
            $recommendations = [];

            // 1. Collaborative Filtering - find similar customers
            $similarCustomers = $this->findSimilarCustomers($customerId, $purchaseHistory);
            $collaborativeRecs = $this->getCollaborativeRecommendations($similarCustomers, $purchaseHistory);

            // 2. Content-Based Filtering - based on past purchases
            $contentRecs = $this->getContentBasedRecommendations($purchaseHistory);

            // 3. Trending Products
            $trendingRecs = $this->getTrendingProducts();

            // 4. Frequently Bought Together
            $bundleRecs = $this->getFrequentlyBoughtTogether($purchaseHistory);

            // Combine and score recommendations
            $recommendations = $this->combineRecommendations([
                'collaborative' => $collaborativeRecs,
                'content' => $contentRecs,
                'trending' => $trendingRecs,
                'bundles' => $bundleRecs
            ]);

            // Sort by score and limit
            usort($recommendations, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            $topRecommendations = array_slice($recommendations, 0, $limit);

            return [
                'success' => true,
                'customer_id' => $customerId,
                'recommendations' => $topRecommendations,
                'total_available' => count($recommendations)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get customer recommendations failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get recommendations for anonymous/guest users
     */
    public function getGuestRecommendations(array $context = [], int $limit = 10): array
    {
        try {
            $recommendations = [];

            // Use context to provide relevant recommendations
            if (!empty($context['viewed_products'])) {
                $similarProducts = $this->getSimilarProducts($context['viewed_products']);
                $recommendations = array_merge($recommendations, $similarProducts);
            }

            if (!empty($context['cart_items'])) {
                $complementaryProducts = $this->getComplementaryProducts($context['cart_items']);
                $recommendations = array_merge($recommendations, $complementaryProducts);
            }

            // Default to trending products
            if (empty($recommendations)) {
                $recommendations = $this->getTrendingProducts();
            }

            // Remove duplicates and limit
            $recommendations = $this->deduplicateRecommendations($recommendations);
            $topRecommendations = array_slice($recommendations, 0, $limit);

            return [
                'success' => true,
                'recommendations' => $topRecommendations
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get guest recommendations failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get recommendations for specific product page
     */
    public function getSimilarProducts(int $productId, int $limit = 6): array
    {
        try {
            $product = TenantDatabase::fetchOneTenant(
                "SELECT * FROM products WHERE id = ?",
                [$productId]
            );

            if (!$product) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            $recommendations = [];

            // 1. Same category products
            $categoryProducts = TenantDatabase::fetchAllTenant(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE p.category_id = ?
                 AND p.id != ?
                 AND p.is_active = 1
                 ORDER BY RAND()
                 LIMIT ?",
                [$product['category_id'], $productId, $limit]
            ) ?? [];

            foreach ($categoryProducts as $prod) {
                $recommendations[] = [
                    'product_id' => $prod['id'],
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'image' => $prod['image_url'] ?? null,
                    'score' => 0.7,
                    'reason' => 'Similar category: ' . $prod['category_name']
                ];
            }

            // 2. Similar price range
            $minPrice = $product['price'] * 0.7;
            $maxPrice = $product['price'] * 1.3;

            $priceRangeProducts = TenantDatabase::fetchAllTenant(
                "SELECT p.*
                 FROM products p
                 WHERE p.price BETWEEN ? AND ?
                 AND p.id != ?
                 AND p.is_active = 1
                 ORDER BY ABS(p.price - ?) ASC
                 LIMIT ?",
                [$minPrice, $maxPrice, $productId, $product['price'], 3]
            ) ?? [];

            foreach ($priceRangeProducts as $prod) {
                $recommendations[] = [
                    'product_id' => $prod['id'],
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'image' => $prod['image_url'] ?? null,
                    'score' => 0.6,
                    'reason' => 'Similar price range'
                ];
            }

            // 3. Frequently bought with this product
            $frequentlyBought = $this->getFrequentlyBoughtWith($productId);
            $recommendations = array_merge($recommendations, $frequentlyBought);

            // Deduplicate and sort
            $recommendations = $this->deduplicateRecommendations($recommendations);
            usort($recommendations, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            return [
                'success' => true,
                'product_id' => $productId,
                'product_name' => $product['name'],
                'recommendations' => array_slice($recommendations, 0, $limit)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get similar products failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get customer purchase history
     */
    private function getCustomerPurchaseHistory(int $customerId): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT ti.product_id, p.name, p.category_id, COUNT(*) as purchase_count
             FROM pos_transaction_items ti
             JOIN pos_transactions t ON ti.transaction_id = t.id
             JOIN products p ON ti.product_id = p.id
             WHERE t.customer_id = ?
             AND t.status = 'completed'
             GROUP BY ti.product_id
             ORDER BY purchase_count DESC",
            [$customerId]
        ) ?? [];
    }

    /**
     * Get customer browsing history
     */
    private function getCustomerBrowsingHistory(int $customerId): array
    {
        // This would come from a product_views tracking table
        return TenantDatabase::fetchAllTenant(
            "SELECT product_id, COUNT(*) as view_count
             FROM product_views
             WHERE customer_id = ?
             AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY product_id
             ORDER BY view_count DESC
             LIMIT 50",
            [$customerId]
        ) ?? [];
    }

    /**
     * Find customers with similar purchase patterns
     */
    private function findSimilarCustomers(int $customerId, array $purchaseHistory): array
    {
        if (empty($purchaseHistory)) {
            return [];
        }

        $productIds = array_column($purchaseHistory, 'product_id');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $similarCustomers = TenantDatabase::fetchAllTenant(
            "SELECT t.customer_id, COUNT(DISTINCT ti.product_id) as common_products
             FROM pos_transaction_items ti
             JOIN pos_transactions t ON ti.transaction_id = t.id
             WHERE ti.product_id IN ({$placeholders})
             AND t.customer_id != ?
             AND t.status = 'completed'
             GROUP BY t.customer_id
             HAVING common_products >= 2
             ORDER BY common_products DESC
             LIMIT 10",
            array_merge($productIds, [$customerId])
        ) ?? [];

        return $similarCustomers;
    }

    /**
     * Get collaborative filtering recommendations
     */
    private function getCollaborativeRecommendations(array $similarCustomers, array $alreadyPurchased): array
    {
        if (empty($similarCustomers)) {
            return [];
        }

        $customerIds = array_column($similarCustomers, 'customer_id');
        $placeholders = implode(',', array_fill(0, count($customerIds), '?'));
        $excludeProductIds = array_column($alreadyPurchased, 'product_id');

        $whereClause = "t.customer_id IN ({$placeholders})";
        $params = $customerIds;

        if (!empty($excludeProductIds)) {
            $excludePlaceholders = implode(',', array_fill(0, count($excludeProductIds), '?'));
            $whereClause .= " AND ti.product_id NOT IN ({$excludePlaceholders})";
            $params = array_merge($params, $excludeProductIds);
        }

        $recommendations = TenantDatabase::fetchAllTenant(
            "SELECT ti.product_id, p.name, p.price, p.image_url,
                    COUNT(*) as recommendation_strength
             FROM pos_transaction_items ti
             JOIN pos_transactions t ON ti.transaction_id = t.id
             JOIN products p ON ti.product_id = p.id
             WHERE {$whereClause}
             AND t.status = 'completed'
             AND p.is_active = 1
             GROUP BY ti.product_id
             ORDER BY recommendation_strength DESC
             LIMIT 20",
            $params
        ) ?? [];

        return array_map(function($rec) {
            return [
                'product_id' => $rec['product_id'],
                'name' => $rec['name'],
                'price' => $rec['price'],
                'image' => $rec['image_url'],
                'score' => 0.8,
                'reason' => 'Customers like you also bought this'
            ];
        }, $recommendations);
    }

    /**
     * Get content-based recommendations
     */
    private function getContentBasedRecommendations(array $purchaseHistory): array
    {
        if (empty($purchaseHistory)) {
            return [];
        }

        // Get categories of purchased products
        $categories = array_unique(array_column($purchaseHistory, 'category_id'));

        if (empty($categories)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $excludeProductIds = array_column($purchaseHistory, 'product_id');

        $whereClause = "p.category_id IN ({$placeholders})";
        $params = $categories;

        if (!empty($excludeProductIds)) {
            $excludePlaceholders = implode(',', array_fill(0, count($excludeProductIds), '?'));
            $whereClause .= " AND p.id NOT IN ({$excludePlaceholders})";
            $params = array_merge($params, $excludeProductIds);
        }

        $recommendations = TenantDatabase::fetchAllTenant(
            "SELECT p.id, p.name, p.price, p.image_url, pc.name as category_name
             FROM products p
             LEFT JOIN product_categories pc ON p.category_id = pc.id
             WHERE {$whereClause}
             AND p.is_active = 1
             ORDER BY p.created_at DESC
             LIMIT 15",
            $params
        ) ?? [];

        return array_map(function($rec) {
            return [
                'product_id' => $rec['id'],
                'name' => $rec['name'],
                'price' => $rec['price'],
                'image' => $rec['image_url'],
                'score' => 0.7,
                'reason' => 'Based on your interest in ' . $rec['category_name']
            ];
        }, $recommendations);
    }

    /**
     * Get trending products
     */
    /**
     * Get trending products based on recent sales
     */
    public function getTrendingProducts(int $limit = 10): array
    {
        try {
            $recommendations = TenantDatabase::fetchAllTenant(
                "SELECT p.id, p.name, p.price, p.image_url,
                        COUNT(ti.id) as sales_count
                 FROM products p
                 JOIN pos_transaction_items ti ON p.id = ti.product_id
                 JOIN pos_transactions t ON ti.transaction_id = t.id
                 WHERE t.transaction_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 AND t.status = 'completed'
                 AND p.is_active = 1
                 GROUP BY p.id
                 ORDER BY sales_count DESC
                 LIMIT ?",
                [$limit]
            ) ?? [];

            return array_map(function($rec) {
                return [
                    'product_id' => $rec['id'],
                    'name' => $rec['name'],
                    'price' => $rec['price'],
                    'image' => $rec['image_url'],
                    'score' => 0.6,
                    'reason' => 'Trending this week'
                ];
            }, $recommendations);
        } catch (\Exception $e) {
            // If tables don't exist yet or other error, return empty array
            return [];
        }
    }

    /**
     * Get frequently bought together products
     */
    private function getFrequentlyBoughtTogether(array $purchaseHistory): array
    {
        if (empty($purchaseHistory)) {
            return [];
        }

        $productIds = array_column($purchaseHistory, 'product_id');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $bundleProducts = TenantDatabase::fetchAllTenant(
            "SELECT p.id, p.name, p.price, p.image_url, COUNT(*) as bundle_frequency
             FROM pos_transaction_items ti1
             JOIN pos_transaction_items ti2 ON ti1.transaction_id = ti2.transaction_id
             JOIN products p ON ti2.product_id = p.id
             WHERE ti1.product_id IN ({$placeholders})
             AND ti2.product_id NOT IN ({$placeholders})
             AND p.is_active = 1
             GROUP BY p.id
             ORDER BY bundle_frequency DESC
             LIMIT 10",
            array_merge($productIds, $productIds)
        ) ?? [];

        return array_map(function($rec) {
            return [
                'product_id' => $rec['id'],
                'name' => $rec['name'],
                'price' => $rec['price'],
                'image' => $rec['image_url'],
                'score' => 0.75,
                'reason' => 'Frequently bought together'
            ];
        }, $bundleProducts);
    }

    /**
     * Get products frequently bought with specific product
     */
    private function getFrequentlyBoughtWith(int $productId): array
    {
        $bundleProducts = TenantDatabase::fetchAllTenant(
            "SELECT p.id, p.name, p.price, p.image_url, COUNT(*) as bundle_frequency
             FROM pos_transaction_items ti1
             JOIN pos_transaction_items ti2 ON ti1.transaction_id = ti2.transaction_id
             JOIN products p ON ti2.product_id = p.id
             WHERE ti1.product_id = ?
             AND ti2.product_id != ?
             AND p.is_active = 1
             GROUP BY p.id
             ORDER BY bundle_frequency DESC
             LIMIT 5",
            [$productId, $productId]
        ) ?? [];

        return array_map(function($rec) {
            return [
                'product_id' => $rec['id'],
                'name' => $rec['name'],
                'price' => $rec['price'],
                'image' => $rec['image_url'],
                'score' => 0.8,
                'reason' => 'Frequently bought together'
            ];
        }, $bundleProducts);
    }

    /**
     * Get complementary products for cart items
     */
    private function getComplementaryProducts(array $cartItems): array
    {
        $productIds = array_column($cartItems, 'product_id');

        $recommendations = [];
        foreach ($productIds as $productId) {
            $similar = $this->getFrequentlyBoughtWith($productId);
            $recommendations = array_merge($recommendations, $similar);
        }

        return $recommendations;
    }

    /**
     * Combine recommendations from multiple sources
     */
    private function combineRecommendations(array $sources): array
    {
        $combined = [];
        $scores = [];

        foreach ($sources as $sourceName => $recommendations) {
            foreach ($recommendations as $rec) {
                $productId = $rec['product_id'];

                if (!isset($combined[$productId])) {
                    $combined[$productId] = $rec;
                    $scores[$productId] = 0;
                }

                // Accumulate scores from different sources
                $scores[$productId] += $rec['score'];
            }
        }

        // Update final scores
        foreach ($combined as $productId => &$rec) {
            $rec['score'] = $scores[$productId];
        }

        return array_values($combined);
    }

    /**
     * Remove duplicate recommendations
     */
    private function deduplicateRecommendations(array $recommendations): array
    {
        $seen = [];
        $unique = [];

        foreach ($recommendations as $rec) {
            if (!isset($seen[$rec['product_id']])) {
                $seen[$rec['product_id']] = true;
                $unique[] = $rec;
            }
        }

        return $unique;
    }
}
