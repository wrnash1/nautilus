<?php

namespace App\Controllers\Storefront;

use App\Core\Controller;
use App\Core\TenantDatabase;
use App\Services\Tenant\WhiteLabelService;
use App\Services\Ecommerce\StorefrontService;
use App\Services\AI\ProductRecommendationService;

class ModernStorefrontController extends Controller
{
    private WhiteLabelService $whiteLabel;
    private StorefrontService $storefront;
    private ProductRecommendationService $recommendations;

    public function __construct()
    {
        parent::__construct();
        $this->whiteLabel = new WhiteLabelService();
        $this->storefront = new StorefrontService();
        $this->recommendations = new ProductRecommendationService();
    }

    /**
     * Storefront Home Page
     */
    public function index(): void
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        // Get branding
        $branding = $this->whiteLabel->getBranding($tenantId);

        // Get store settings
        $storeSettings = $this->getStoreSettings();

        // Get featured products
        $featuredProducts = $this->getFeaturedProducts(8);

        // Get categories
        $categories = $this->getCategories();

        // Get hero banners
        $heroBanners = $this->getHeroBanners();

        // Get trending products
        $trendingProducts = $this->recommendations->getTrendingProducts(6);

        $data = [
            'branding' => $branding,
            'store_settings' => $storeSettings,
            'featured_products' => $featuredProducts,
            'categories' => $categories,
            'hero_banners' => $heroBanners,
            'trending_products' => $trendingProducts,
            'page_title' => $storeSettings['store_name'] ?? 'Welcome'
        ];

        $this->view('storefront/modern/index', $data);
    }

    /**
     * Shop - Product Listing
     */
    public function shop(): void
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        // Get filters
        $categoryId = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? null;
        $sortBy = $_GET['sort'] ?? 'name';
        $page = $_GET['page'] ?? 1;
        $perPage = 12;

        // Get products
        $products = $this->getProducts($categoryId, $search, $sortBy, $page, $perPage);

        // Get categories for filter
        $categories = $this->getCategories();

        // Get branding
        $branding = $this->whiteLabel->getBranding($tenantId);

        $data = [
            'branding' => $branding,
            'products' => $products['items'],
            'categories' => $categories,
            'pagination' => $products['pagination'],
            'current_category' => $categoryId,
            'current_search' => $search,
            'current_sort' => $sortBy,
            'page_title' => 'Shop'
        ];

        $this->view('storefront/modern/shop', $data);
    }

    /**
     * Product Detail Page
     */
    public function product(int $productId): void
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        // Get product details
        $product = TenantDatabase::fetchOneTenant("
            SELECT p.*, c.name as category_name,
                   (SELECT GROUP_CONCAT(image_url) FROM product_images WHERE product_id = p.id) as images
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ? AND p.is_active = 1 AND p.is_web_visible = 1
        ", [$productId]);

        if (!$product) {
            $this->redirect('/shop?error=Product not found');
            return;
        }

        // Track product view
        $this->trackProductView($productId);

        // Get related products
        $relatedProducts = $this->recommendations->getRelatedProducts($productId, 4);

        // Get product reviews
        $reviews = $this->getProductReviews($productId);

        // Get branding
        $branding = $this->whiteLabel->getBranding($tenantId);

        $data = [
            'branding' => $branding,
            'product' => $product,
            'related_products' => $relatedProducts,
            'reviews' => $reviews,
            'page_title' => $product['name']
        ];

        $this->view('storefront/modern/product', $data);
    }

    /**
     * Shopping Cart
     */
    public function cart(): void
    {
        $sessionId = session_id();
        $customerId = $_SESSION['customer_id'] ?? null;

        $cart = $this->storefront->getCart($sessionId, $customerId);
        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $data = [
            'branding' => $branding,
            'cart' => $cart,
            'page_title' => 'Shopping Cart'
        ];

        $this->view('storefront/modern/cart', $data);
    }

    /**
     * Add to Cart (AJAX)
     */
    public function addToCart(): void
    {
        header('Content-Type: application/json');

        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        $sessionId = session_id();
        $customerId = $_SESSION['customer_id'] ?? null;

        $result = $this->storefront->addToCart($sessionId, $productId, $quantity, $customerId);

        echo json_encode($result);
    }

    /**
     * Update Cart (AJAX)
     */
    public function updateCart(): void
    {
        header('Content-Type: application/json');

        $cartItemId = $_POST['cart_item_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;

        $result = $this->storefront->updateCartItem($cartItemId, $quantity);

        echo json_encode($result);
    }

    /**
     * Remove from Cart (AJAX)
     */
    public function removeFromCart(): void
    {
        header('Content-Type: application/json');

        $cartItemId = $_POST['cart_item_id'] ?? 0;

        $result = $this->storefront->removeFromCart($cartItemId);

        echo json_encode($result);
    }

    /**
     * Checkout Page
     */
    public function checkout(): void
    {
        $sessionId = session_id();
        $customerId = $_SESSION['customer_id'] ?? null;

        $cart = $this->storefront->getCart($sessionId, $customerId);

        if (empty($cart['items'])) {
            $this->redirect('/cart?error=Cart is empty');
            return;
        }

        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        // Get customer info if logged in
        $customer = null;
        if ($customerId) {
            $customer = TenantDatabase::fetchOneTenant(
                "SELECT * FROM customers WHERE id = ?",
                [$customerId]
            );
        }

        $data = [
            'branding' => $branding,
            'cart' => $cart,
            'customer' => $customer,
            'page_title' => 'Checkout'
        ];

        $this->view('storefront/modern/checkout', $data);
    }

    /**
     * Process Checkout
     */
    public function processCheckout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
            return;
        }

        $sessionId = session_id();
        $customerId = $_SESSION['customer_id'] ?? null;

        $orderData = [
            'customer_email' => $_POST['email'],
            'customer_name' => $_POST['first_name'] . ' ' . $_POST['last_name'],
            'billing_address' => [
                'address' => $_POST['address'],
                'city' => $_POST['city'],
                'state' => $_POST['state'],
                'zip_code' => $_POST['zip_code'],
                'country' => $_POST['country'] ?? 'US'
            ],
            'payment_method' => $_POST['payment_method']
        ];

        $result = $this->storefront->processCheckout($sessionId, $customerId, $orderData);

        if ($result['success']) {
            $this->redirect('/checkout/success?order=' . $result['order_id']);
        } else {
            $this->redirect('/checkout?error=' . urlencode($result['error']));
        }
    }

    /**
     * Checkout Success
     */
    public function checkoutSuccess(): void
    {
        $orderId = $_GET['order'] ?? 0;

        $order = TenantDatabase::fetchOneTenant(
            "SELECT * FROM online_orders WHERE id = ?",
            [$orderId]
        );

        if (!$order) {
            $this->redirect('/');
            return;
        }

        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $data = [
            'branding' => $branding,
            'order' => $order,
            'page_title' => 'Order Confirmation'
        ];

        $this->view('storefront/modern/success', $data);
    }

    /**
     * Courses Page
     */
    public function courses(): void
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $courses = TenantDatabase::fetchAllTenant("
            SELECT c.*,
                   (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrolled_count
            FROM courses c
            WHERE c.is_active = 1
            ORDER BY c.name ASC
        ") ?? [];

        $branding = $this->whiteLabel->getBranding($tenantId);

        $data = [
            'branding' => $branding,
            'courses' => $courses,
            'page_title' => 'Dive Courses'
        ];

        $this->view('storefront/modern/courses', $data);
    }

    /**
     * Course Detail
     */
    public function courseDetail(int $courseId): void
    {
        $course = TenantDatabase::fetchOneTenant("
            SELECT * FROM courses WHERE id = ? AND is_active = 1
        ", [$courseId]);

        if (!$course) {
            $this->redirect('/courses?error=Course not found');
            return;
        }

        $branding = $this->whiteLabel->getBranding($_SESSION['tenant_id'] ?? 1);

        $data = [
            'branding' => $branding,
            'course' => $course,
            'page_title' => $course['name']
        ];

        $this->view('storefront/modern/course-detail', $data);
    }

    // Private helper methods

    private function getStoreSettings(): array
    {
        $settings = TenantDatabase::fetchAllTenant(
            "SELECT * FROM storefront_settings WHERE tenant_id = ?",
            [$_SESSION['tenant_id'] ?? 1]
        ) ?? [];

        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['setting_key']] = $setting['setting_value'];
        }

        return $settingsArray;
    }

    private function getFeaturedProducts(int $limit = 8): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT p.*,
                   (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image
            FROM products p
            WHERE p.is_active = 1
            AND p.is_web_visible = 1
            AND p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT ?
        ", [$limit]) ?? [];
    }

    private function getCategories(): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 AND p.is_web_visible = 1
            WHERE c.type = 'product'
            GROUP BY c.id
            ORDER BY c.name ASC
        ") ?? [];
    }

    private function getHeroBanners(): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT * FROM hero_banners
            WHERE is_active = 1
            ORDER BY display_order ASC
            LIMIT 3
        ") ?? [];
    }

    private function getProducts(?int $categoryId, ?string $search, string $sortBy, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ["p.is_active = 1", "p.is_web_visible = 1"];
        $params = [];

        if ($categoryId) {
            $where[] = "p.category_id = ?";
            $params[] = $categoryId;
        }

        if ($search) {
            $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(' AND ', $where);

        $orderBy = match($sortBy) {
            'price_asc' => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest' => 'p.created_at DESC',
            default => 'p.name ASC'
        };

        $products = TenantDatabase::fetchAllTenant("
            SELECT p.*,
                   (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image,
                   c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereClause
            ORDER BY $orderBy
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset])) ?? [];

        $total = TenantDatabase::fetchOneTenant("
            SELECT COUNT(*) as count FROM products p WHERE $whereClause
        ", $params);

        return [
            'items' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total['count'],
                'total_pages' => ceil($total['count'] / $perPage)
            ]
        ];
    }

    private function trackProductView(int $productId): void
    {
        TenantDatabase::insertTenant('product_views', [
            'product_id' => $productId,
            'customer_id' => $_SESSION['customer_id'] ?? null,
            'session_id' => session_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Increment view count
        TenantDatabase::query("
            UPDATE products SET view_count = view_count + 1 WHERE id = ?
        ", [$productId]);
    }

    private function getProductReviews(int $productId): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT r.*, c.first_name, c.last_name
            FROM product_reviews r
            LEFT JOIN customers c ON r.customer_id = c.id
            WHERE r.product_id = ? AND r.is_approved = 1
            ORDER BY r.created_at DESC
            LIMIT 10
        ", [$productId]) ?? [];
    }
}
