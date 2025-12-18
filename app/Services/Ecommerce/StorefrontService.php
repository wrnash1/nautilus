<?php

namespace App\Services\Ecommerce;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;
use App\Services\AI\ProductRecommendationService;

/**
 * E-commerce Storefront Service
 *
 * Complete online store functionality with cart, checkout, and order management
 */
class StorefrontService
{
    private Logger $logger;
    private ProductRecommendationService $recommendationService;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->recommendationService = new ProductRecommendationService();
    }

    /**
     * Get storefront homepage data
     */
    public function getHomepage(): array
    {
        try {
            return [
                'success' => true,
                'featured_products' => $this->getFeaturedProducts(),
                'trending_products' => $this->getTrendingProducts(),
                'new_arrivals' => $this->getNewArrivals(),
                'categories' => $this->getCategories(),
                'hero_banners' => $this->getHeroBanners(),
                'testimonials' => $this->getTestimonials()
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get homepage failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get product catalog with filtering and sorting
     */
    public function getCatalog(array $filters = []): array
    {
        try {
            $where = ["p.is_active = 1", "p.is_web_visible = 1"];
            $params = [];

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

            // Search query
            if (!empty($filters['search'])) {
                $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            // In stock only
            if (!empty($filters['in_stock_only'])) {
                $where[] = "p.stock_quantity > 0";
            }

            $whereClause = implode(' AND ', $where);

            // Sorting
            $orderBy = match($filters['sort'] ?? 'name') {
                'price_low' => 'p.price ASC',
                'price_high' => 'p.price DESC',
                'newest' => 'p.created_at DESC',
                'popular' => 'p.view_count DESC',
                default => 'p.name ASC'
            };

            $limit = $filters['limit'] ?? 24;
            $offset = $filters['offset'] ?? 0;

            $products = TenantDatabase::fetchAllTenant(
                "SELECT p.*, pc.name as category_name,
                        (SELECT pi.image_url FROM product_images pi
                         WHERE pi.product_id = p.id AND pi.is_primary = 1
                         LIMIT 1) as primary_image
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE {$whereClause}
                 ORDER BY {$orderBy}
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            ) ?? [];

            // Get total count
            $totalResult = TenantDatabase::fetchOneTenant(
                "SELECT COUNT(*) as total FROM products p WHERE {$whereClause}",
                $params
            );

            return [
                'success' => true,
                'products' => $products,
                'total' => $totalResult['total'] ?? 0,
                'limit' => $limit,
                'offset' => $offset,
                'filters_applied' => $filters
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get catalog failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get single product details
     */
    public function getProduct(int $productId, ?int $customerId = null): array
    {
        try {
            $product = TenantDatabase::fetchOneTenant(
                "SELECT p.*, pc.name as category_name
                 FROM products p
                 LEFT JOIN product_categories pc ON p.category_id = pc.id
                 WHERE p.id = ? AND p.is_active = 1",
                [$productId]
            );

            if (!$product) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            // Get product images
            $images = TenantDatabase::fetchAllTenant(
                "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order",
                [$productId]
            ) ?? [];

            // Get product variants/options
            $variants = TenantDatabase::fetchAllTenant(
                "SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1",
                [$productId]
            ) ?? [];

            // Get reviews and ratings
            $reviews = TenantDatabase::fetchAllTenant(
                "SELECT cr.*, CONCAT(c.first_name, ' ', c.last_name) as customer_name
                 FROM customer_reviews cr
                 JOIN customers c ON cr.customer_id = c.id
                 WHERE cr.product_id = ? AND cr.is_approved = 1
                 ORDER BY cr.created_at DESC
                 LIMIT 10",
                [$productId]
            ) ?? [];

            $ratingStats = TenantDatabase::fetchOneTenant(
                "SELECT COUNT(*) as review_count,
                        AVG(rating) as average_rating,
                        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                 FROM customer_reviews
                 WHERE product_id = ? AND is_approved = 1",
                [$productId]
            );

            // Get recommendations
            $recommendations = $this->recommendationService->getSimilarProducts($productId, 6);

            // Increment view count
            TenantDatabase::updateTenant('products', [
                'view_count' => $product['view_count'] + 1
            ], 'id = ?', [$productId]);

            // Track view for customer if logged in
            if ($customerId) {
                $this->trackProductView($customerId, $productId);
            }

            return [
                'success' => true,
                'product' => $product,
                'images' => $images,
                'variants' => $variants,
                'reviews' => $reviews,
                'rating_stats' => $ratingStats,
                'recommendations' => $recommendations['recommendations'] ?? []
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get product failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Add item to shopping cart
     */
    public function addToCart(string $sessionId, int $productId, int $quantity = 1, ?int $customerId = null): array
    {
        try {
            // Verify product availability
            $product = TenantDatabase::fetchOneTenant(
                "SELECT * FROM products WHERE id = ? AND is_active = 1",
                [$productId]
            );

            if (!$product) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            if ($product['stock_quantity'] < $quantity) {
                return [
                    'success' => false,
                    'error' => 'Insufficient stock',
                    'available_quantity' => $product['stock_quantity']
                ];
            }

            // Check if item already in cart
            $existingItem = TenantDatabase::fetchOneTenant(
                "SELECT * FROM shopping_cart WHERE session_id = ? AND product_id = ?",
                [$sessionId, $productId]
            );

            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $quantity;

                TenantDatabase::updateTenant('shopping_cart', [
                    'quantity' => $newQuantity,
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$existingItem['id']]);

                $cartItemId = $existingItem['id'];
            } else {
                // Add new item
                $cartItemId = TenantDatabase::insertTenant('shopping_cart', [
                    'session_id' => $sessionId,
                    'customer_id' => $customerId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product['price'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Get updated cart
            $cart = $this->getCart($sessionId);

            return [
                'success' => true,
                'cart_item_id' => $cartItemId,
                'message' => 'Product added to cart',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            $this->logger->error('Add to cart failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get shopping cart
     */
    public function getCart(string $sessionId): array
    {
        try {
            $items = TenantDatabase::fetchAllTenant(
                "SELECT sc.*, p.name, p.sku, p.image_url, p.stock_quantity,
                        (sc.quantity * sc.price) as line_total
                 FROM shopping_cart sc
                 JOIN products p ON sc.product_id = p.id
                 WHERE sc.session_id = ?
                 ORDER BY sc.created_at DESC",
                [$sessionId]
            ) ?? [];

            $subtotal = array_sum(array_column($items, 'line_total'));
            $taxRate = 0.08; // 8% tax
            $tax = $subtotal * $taxRate;
            $total = $subtotal + $tax;

            return [
                'success' => true,
                'items' => $items,
                'item_count' => array_sum(array_column($items, 'quantity')),
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'total' => round($total, 2)
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get cart failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(int $cartItemId, int $quantity): array
    {
        try {
            if ($quantity <= 0) {
                return $this->removeFromCart($cartItemId);
            }

            $item = TenantDatabase::fetchOneTenant(
                "SELECT sc.*, p.stock_quantity
                 FROM shopping_cart sc
                 JOIN products p ON sc.product_id = p.id
                 WHERE sc.id = ?",
                [$cartItemId]
            );

            if (!$item) {
                return ['success' => false, 'error' => 'Cart item not found'];
            }

            if ($item['stock_quantity'] < $quantity) {
                return [
                    'success' => false,
                    'error' => 'Insufficient stock',
                    'available_quantity' => $item['stock_quantity']
                ];
            }

            TenantDatabase::updateTenant('shopping_cart', [
                'quantity' => $quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$cartItemId]);

            $cart = $this->getCart($item['session_id']);

            return [
                'success' => true,
                'message' => 'Cart updated',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            $this->logger->error('Update cart item failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $cartItemId): array
    {
        try {
            $item = TenantDatabase::fetchOneTenant(
                "SELECT session_id FROM shopping_cart WHERE id = ?",
                [$cartItemId]
            );

            if (!$item) {
                return ['success' => false, 'error' => 'Cart item not found'];
            }

            TenantDatabase::queryTenant(
                "DELETE FROM shopping_cart WHERE id = ?",
                [$cartItemId]
            );

            $cart = $this->getCart($item['session_id']);

            return [
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => $cart
            ];

        } catch (\Exception $e) {
            $this->logger->error('Remove from cart failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Clear entire cart
     */
    public function clearCart(string $sessionId): array
    {
        try {
            TenantDatabase::queryTenant(
                "DELETE FROM shopping_cart WHERE session_id = ?",
                [$sessionId]
            );

            return [
                'success' => true,
                'message' => 'Cart cleared'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Clear cart failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get checkout information
     */
    public function getCheckout(string $sessionId, ?int $customerId = null): array
    {
        try {
            $cart = $this->getCart($sessionId);

            if (empty($cart['items'])) {
                return ['success' => false, 'error' => 'Cart is empty'];
            }

            $checkoutData = [
                'cart' => $cart,
                'shipping_methods' => $this->getShippingMethods($cart['subtotal']),
                'payment_methods' => $this->getPaymentMethods()
            ];

            // Get customer info if logged in
            if ($customerId) {
                $customer = TenantDatabase::fetchOneTenant(
                    "SELECT * FROM customers WHERE id = ?",
                    [$customerId]
                );

                $checkoutData['customer'] = $customer;

                // Get saved addresses
                $addresses = TenantDatabase::fetchAllTenant(
                    "SELECT * FROM customer_addresses
                     WHERE customer_id = ?
                     ORDER BY is_default DESC, created_at DESC",
                    [$customerId]
                ) ?? [];

                $checkoutData['saved_addresses'] = $addresses;
            }

            return [
                'success' => true,
                'checkout' => $checkoutData
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get checkout failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Helper methods

    private function getFeaturedProducts(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
             FROM products p
             WHERE p.is_featured = 1 AND p.is_active = 1 AND p.stock_quantity > 0
             ORDER BY p.featured_order
             LIMIT 8",
            []
        ) ?? [];
    }

    private function getTrendingProducts(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT p.*, COUNT(ti.id) as sales_count,
                    (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
             FROM products p
             JOIN transaction_items ti ON p.id = ti.product_id
             JOIN transactions t ON ti.transaction_id = t.id
             WHERE t.transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             AND t.status = 'completed'
             AND p.is_active = 1
             GROUP BY p.id
             ORDER BY sales_count DESC
             LIMIT 8",
            []
        ) ?? [];
    }

    private function getNewArrivals(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
             FROM products p
             WHERE p.is_active = 1
             ORDER BY p.created_at DESC
             LIMIT 8",
            []
        ) ?? [];
    }

    private function getCategories(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT pc.*, COUNT(p.id) as product_count
             FROM product_categories pc
             LEFT JOIN products p ON pc.id = p.category_id AND p.is_active = 1
             WHERE pc.is_active = 1
             GROUP BY pc.id
             ORDER BY pc.sort_order, pc.name",
            []
        ) ?? [];
    }

    private function getHeroBanners(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT * FROM hero_banners
             WHERE is_active = 1
             AND (start_date IS NULL OR start_date <= CURDATE())
             AND (end_date IS NULL OR end_date >= CURDATE())
             ORDER BY sort_order
             LIMIT 5",
            []
        ) ?? [];
    }

    private function getTestimonials(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT cr.*, CONCAT(c.first_name, ' ', SUBSTRING(c.last_name, 1, 1), '.') as customer_name,
                    p.name as product_name
             FROM customer_reviews cr
             JOIN customers c ON cr.customer_id = c.id
             LEFT JOIN products p ON cr.product_id = p.id
             WHERE cr.is_approved = 1
             AND cr.rating >= 4
             AND cr.review_text IS NOT NULL
             ORDER BY RAND()
             LIMIT 6",
            []
        ) ?? [];
    }

    private function getShippingMethods(float $subtotal): array
    {
        $methods = [
            [
                'id' => 'standard',
                'name' => 'Standard Shipping',
                'description' => '5-7 business days',
                'cost' => $subtotal >= 100 ? 0 : 9.99,
                'estimated_days' => 7
            ],
            [
                'id' => 'express',
                'name' => 'Express Shipping',
                'description' => '2-3 business days',
                'cost' => 19.99,
                'estimated_days' => 3
            ],
            [
                'id' => 'overnight',
                'name' => 'Overnight',
                'description' => 'Next business day',
                'cost' => 39.99,
                'estimated_days' => 1
            ],
            [
                'id' => 'pickup',
                'name' => 'Store Pickup',
                'description' => 'Free - Ready in 2 hours',
                'cost' => 0,
                'estimated_days' => 0
            ]
        ];

        return $methods;
    }

    private function getPaymentMethods(): array
    {
        return [
            ['id' => 'credit_card', 'name' => 'Credit/Debit Card', 'enabled' => true],
            ['id' => 'paypal', 'name' => 'PayPal', 'enabled' => true],
            ['id' => 'stripe', 'name' => 'Stripe', 'enabled' => true],
            ['id' => 'cash_on_delivery', 'name' => 'Cash on Delivery', 'enabled' => false]
        ];
    }

    private function trackProductView(int $customerId, int $productId): void
    {
        try {
            TenantDatabase::insertTenant('product_views', [
                'customer_id' => $customerId,
                'product_id' => $productId,
                'viewed_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Silent fail - view tracking is not critical
        }
    }
}
