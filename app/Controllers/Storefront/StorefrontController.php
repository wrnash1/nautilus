<?php

namespace App\Controllers\Storefront;

use App\Core\Controller;
use App\Core\Database;

use App\Services\Storefront\StorefrontSettingsService;

class StorefrontController extends Controller
{
    private StorefrontSettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new StorefrontSettingsService();
    }
    /**
     * Homepage - Modern storefront with carousel
     */
    public function index(): void
    {
        // Get carousel slides from database (if configured)
        $carouselSlides = Database::fetchAll("
            SELECT * FROM storefront_carousel_slides
            WHERE is_active = 1
            ORDER BY display_order ASC
        ") ?? [];

        // Get service boxes from database (if configured)
        $serviceBoxes = Database::fetchAll("
            SELECT * FROM storefront_service_boxes
            WHERE is_active = 1
            ORDER BY display_order ASC
        ") ?? [];

        $data = [
            'carousel_slides' => $carouselSlides,
            'service_boxes' => !empty($serviceBoxes) ? $serviceBoxes : null // null triggers defaults in view
        ];

        $this->renderStorefront('storefront/index', $data);
    }

    protected function renderStorefront(string $view, array $data = []): void
    {
        // Global Storefront Data
        $data['active_announcements'] = $this->settingsService->getActiveBanners();
        $socialSettings = $this->settingsService->getByCategory('social');
        $socialLinks = [];
        foreach ($socialSettings as $key => $val) {
            $socialLinks[$key] = $val['value'];
        }
        $data['social_links'] = $socialLinks;

        $data['store_stats'] = $this->settingsService->getStoreStats(); // Get dynamic stats

        // Pass settings service for direct access
        $data['settings'] = $this->settingsService;

        // Load business info from Settings class
        $settings = \App\Core\Settings::getInstance();
        $companyInfo = $settings->getCompanyInfo();

        // Pass flattened theme settings (using general category as fallback source)
        $generalSettings = $this->settingsService->getByCategory('general');
        $theme = [];
        foreach ($generalSettings as $key => $val) {
            $theme[$key] = $val['value'];
        }

        // Add business contact info to theme array
        $theme['business_name'] = $companyInfo['name'] ?? 'Nautilus Dive Shop';
        $theme['business_phone'] = $companyInfo['phone'] ?? '817-406-4080';
        $theme['business_email'] = $companyInfo['email'] ?? 'info@nautilus.local';
        $theme['business_address'] = $companyInfo['address'] ?? '';
        $theme['business_city'] = $companyInfo['city'] ?? '';
        $theme['business_state'] = $companyInfo['state'] ?? '';
        $theme['business_zip'] = $companyInfo['zip'] ?? '';
        $theme['logo_path'] = $companyInfo['logo'] ?? '';

        // Add stats from system settings
        $theme['stats_certified_divers'] = $settings->get('stats_certified_divers', '5000');
        $theme['stats_years_experience'] = $settings->get('stats_years_experience', '25');
        $theme['stats_dive_destinations'] = $settings->get('stats_dive_destinations', '100');
        $theme['stats_customer_rating'] = $settings->get('stats_customer_rating', '4.9');

        // Add certification organization settings
        $theme['primary_certification_org'] = $settings->get('primary_certification_org', 'PADI');
        $theme['certification_level'] = $settings->get('certification_level', '5-Star Center');
        $theme['secondary_certifications'] = $settings->get('secondary_certifications', 'SSI,NAUI');

        $data['theme'] = $theme;

        extract($data);
        require __DIR__ . '/../../Views/' . $view . '.php';
    }

    /**
     * Shop page
     */
    public function shop(): void
    {
        // Get featured products
        $products = Database::fetchAll("
            SELECT * FROM products
            WHERE is_active = 1
            ORDER BY created_at DESC
            LIMIT 12
        ") ?? [];

        $data = [
            'products' => $products,
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/shop', $data);
    }

    /**
     * Courses page
     */
    public function courses(): void
    {
        // Get active courses
        $courses = Database::fetchAll("
            SELECT * FROM courses 
            WHERE is_active = 1 
            ORDER BY name ASC
        ") ?? [];

        $data = [
            'courses' => $courses,
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/courses', $data);
    }

    /**
     * Course Detail page
     */
    public function courseDetail($id): void
    {
        // Get course details
        $course = Database::fetchOne("
            SELECT * FROM courses 
            WHERE id = ? AND is_active = 1
        ", [$id]);

        if (!$course) {
            http_response_code(404);
            echo "Course not found"; // In a real app, render 404 view
            return;
        }

        // Get upcoming schedules for this course
        $schedules = Database::fetchAll("
            SELECT cs.*, u.first_name as instructor_name 
            FROM course_schedules cs
            LEFT JOIN users u ON cs.instructor_id = u.id
            WHERE cs.course_id = ? AND cs.start_date >= CURDATE()
            ORDER BY cs.start_date ASC
        ", [$id]) ?? [];

        $data = [
            'course' => $course,
            'schedules' => $schedules,
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/course_detail', $data);
    }

    /**
     * Dive trips page
     */
    public function trips(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/trips', $data);
    }

    /**
     * About page
     */
    public function about(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/about', $data);
    }

    /**
     * Contact page
     */
    public function contact(): void
    {
        // Load business info from Settings class
        $settings = \App\Core\Settings::getInstance();
        $companyInfo = $settings->getCompanyInfo();

        $data = [
            'business_name' => $this->getBusinessName(),
            'business_phone' => $companyInfo['business_phone'] ?? '817-406-4080',
            'business_email' => $companyInfo['business_email'] ?? 'info@nautilus.local',
            'business_address' => $companyInfo['business_address'] ?? '149 W main street',
            'business_city' => $companyInfo['business_city'] ?? 'Azle',
            'business_state' => $companyInfo['business_state'] ?? 'Texas',
            'business_zip' => $companyInfo['business_zip'] ?? '76020',
        ];

        $this->renderStorefront('storefront/contact', $data);
    }

    /**
     * Liveaboard page
     */
    public function liveaboard(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/liveaboard', $data);
    }

    /**
     * Resorts page
     */
    public function resorts(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/resorts', $data);
    }

    /**
     * Repair Services page
     */
    public function repair(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/services/repair', $data);
    }

    /**
     * Air Fills Services page
     */
    public function fills(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/services/fills', $data);
    }

    /**
     * Rentals page
     */
    public function rentals(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/rentals', $data);
    }

    /**
     * First Aid Course page
     */
    public function firstAid(): void
    {
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/courses/first_aid', $data);
    }

    /**
     * Helper to get business name
     */
    private function getBusinessName(): string
    {
        $settings = Database::fetchOne("SELECT setting_value FROM company_settings WHERE setting_key = 'business_name'");
        return ($settings ?? [])['setting_value'] ?? 'Nautilus Dive Shop';
    }
}
