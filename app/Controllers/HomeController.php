<?php

namespace App\Controllers;

use App\Core\Database;
use App\Services\Storefront\ThemeEngine;
use App\Services\Storefront\StorefrontSettingsService;
use App\Services\Inventory\ProductService;
use App\Services\Courses\CourseService;
use App\Services\Trips\TripService;

class HomeController
{
    private $themeEngine;
    private $settings;
    private $productService;
    private $courseService;
    private $tripService;
    private $db;

    public function __construct()
    {
        $this->themeEngine = new ThemeEngine();
        $this->settings = new StorefrontSettingsService();
        $this->productService = new ProductService();
        $this->courseService = new CourseService();
        $this->tripService = new TripService();
        $this->db = Database::getInstance();
    }

    /**
     * Homepage - Public storefront
     */
    public function index()
    {
        // Check if storefront tables exist
        try {
            $this->db->query("SELECT 1 FROM theme_config LIMIT 1");
        } catch (\PDOException $e) {
            // Tables don't exist - redirect to installation
            redirect('/install');
            return;
        }

        // Get active theme configuration
        $theme = $this->themeEngine->getActiveTheme();
        $sections = $this->themeEngine->getHomepageSections();

        // Get store settings
        $storeName = $this->settings->get('store_name', 'Nautilus Dive Shop');
        $storeTagline = $this->settings->get('store_tagline', '');
        $metaDescription = $this->settings->get('seo_meta_description', '');

        // Get navigation
        $headerMenu = $this->settings->getNavigationMenu('header');
        $footerMenu = $this->settings->getNavigationMenu('footer');

        // Get promotional banners
        $topBanners = $this->settings->getActiveBanners('top_bar', 'home');

        // Load data for each section
        $sectionData = $this->loadSectionData($sections);

        // Get theme assets
        $logo = $this->themeEngine->getPrimaryAsset('logo');
        $favicon = $this->themeEngine->getPrimaryAsset('favicon');
        $heroImage = $this->themeEngine->getPrimaryAsset('hero_image');

        // Social links
        $socialLinks = [
            'facebook' => $this->settings->get('facebook_url'),
            'instagram' => $this->settings->get('instagram_url'),
            'twitter' => $this->settings->get('twitter_url'),
            'youtube' => $this->settings->get('youtube_url')
        ];

        // Make service objects available to views
        $settings = $this->settings;
        $themeEngine = $this->themeEngine;

        require __DIR__ . '/../Views/storefront/home.php';
    }

    /**
     * Load data for homepage sections
     */
    private function loadSectionData(array $sections): array
    {
        $data = [];

        foreach ($sections as $section) {
            $sectionId = $section['id'];
            $config = $section['config'] ?? [];

            switch ($section['section_type']) {
                case 'featured_products':
                    $limit = $config['limit'] ?? 8;
                    $filter = $config['filter'] ?? 'featured';
                    $data[$sectionId] = $this->getFeaturedProducts($limit, $filter);
                    break;

                case 'categories':
                case 'featured_categories':
                    $limit = $config['limit'] ?? null;
                    $data[$sectionId] = $this->getCategories($limit);
                    break;

                case 'courses':
                    $limit = $config['limit'] ?? 6;
                    $data[$sectionId] = $this->getUpcomingCourses($limit);
                    break;

                case 'trips':
                    $limit = $config['limit'] ?? 3;
                    $data[$sectionId] = $this->getUpcomingTrips($limit);
                    break;

                case 'testimonials':
                    $limit = $config['limit'] ?? 5;
                    $data[$sectionId] = $this->getTestimonials($limit);
                    break;

                case 'blog_posts':
                    $limit = $config['limit'] ?? 3;
                    $data[$sectionId] = $this->getBlogPosts($limit);
                    break;

                case 'brands':
                    $data[$sectionId] = $this->getBrands();
                    break;
            }
        }

        return $data;
    }

    /**
     * Get featured products
     */
    private function getFeaturedProducts(int $limit = 8, string $filter = 'featured'): array
    {
        $sql = "
            SELECT p.*, pi.file_path as image_path
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = TRUE
            WHERE p.is_active = TRUE AND p.stock_quantity > 0
        ";

        if ($filter === 'featured') {
            $sql .= " AND p.is_featured = TRUE";
        } elseif ($filter === 'new') {
            $sql .= " ORDER BY p.created_at DESC";
        } elseif ($filter === 'bestseller') {
            // Would need sales data - simplified for now
            $sql .= " ORDER BY p.id DESC";
        }

        $sql .= " LIMIT ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get product categories
     */
    private function getCategories(?int $limit = null): array
    {
        $sql = "
            SELECT c.*, COUNT(p.id) as product_count
            FROM product_categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = TRUE
            WHERE c.is_active = TRUE
            GROUP BY c.id
            ORDER BY c.name ASC
        ";

        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming courses
     */
    private function getUpcomingCourses(int $limit = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, cs.start_date, cs.end_date, cs.max_students, cs.current_enrollment,
                   (cs.max_students - cs.current_enrollment) as spots_available
            FROM courses c
            INNER JOIN course_schedules cs ON c.id = cs.course_id
            WHERE c.is_active = TRUE
              AND cs.status = 'scheduled'
              AND cs.start_date > NOW()
            ORDER BY cs.start_date ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming dive trips
     */
    private function getUpcomingTrips(int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT ts.*, t.name, t.destination, t.description, t.duration_days, t.price,
                   (ts.max_participants - (SELECT COUNT(*) FROM trip_bookings WHERE schedule_id = ts.id AND status IN ('confirmed', 'pending'))) as spots_available
            FROM trip_schedules ts
            JOIN trips t ON ts.trip_id = t.id
            WHERE ts.status IN ('scheduled', 'confirmed')
              AND ts.departure_date > NOW()
              AND t.is_active = TRUE
            ORDER BY ts.departure_date ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer testimonials/reviews
     */
    private function getTestimonials(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT pr.*, p.name as product_name, c.first_name, c.last_name
            FROM product_reviews pr
            INNER JOIN products p ON pr.product_id = p.id
            LEFT JOIN customers c ON pr.customer_id = c.id
            WHERE pr.is_approved = TRUE AND pr.rating >= 4
            ORDER BY pr.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get blog posts
     */
    private function getBlogPosts(int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM blog_posts
            WHERE status = 'published'
              AND published_at <= NOW()
            ORDER BY published_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get brand logos
     */
    private function getBrands(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT v.id, v.name, v.website
            FROM vendors v
            INNER JOIN products p ON v.id = p.vendor_id
            WHERE v.is_active = TRUE
            ORDER BY v.name ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * About page
     */
    public function about()
    {
        $theme = $this->themeEngine->getActiveTheme();
        $headerMenu = $this->settings->getNavigationMenu('header');
        $footerMenu = $this->settings->getNavigationMenu('footer');

        // Make service objects available to views
        $settings = $this->settings;
        $themeEngine = $this->themeEngine;

        require __DIR__ . '/../Views/storefront/about.php';
    }

    /**
     * Contact page
     */
    public function contact()
    {
        $theme = $this->themeEngine->getActiveTheme();
        $headerMenu = $this->settings->getNavigationMenu('header');
        $footerMenu = $this->settings->getNavigationMenu('footer');

        $contactEmail = $this->settings->get('contact_email');
        $contactPhone = $this->settings->get('contact_phone');
        $storeAddress = $this->settings->get('store_address');

        // Make service objects available to views
        $settings = $this->settings;
        $themeEngine = $this->themeEngine;

        require __DIR__ . '/../Views/storefront/contact.php';
    }

    /**
     * Handle contact form submission
     */
    public function submitContact()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        // Basic validation
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';

        if (empty($name) || empty($email) || empty($message)) {
            $_SESSION['error'] = 'Please fill in all required fields.';
            header('Location: /contact');
            return;
        }

        // TODO: Send email using PHPMailer or queue for later
        // For now, just save to database or log

        $_SESSION['success'] = 'Thank you for your message. We will get back to you soon!';
        header('Location: /contact');
    }
}
