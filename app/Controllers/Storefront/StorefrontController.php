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
        // Get business name from settings
        $settings = Database::fetchOne("SELECT setting_value FROM company_settings WHERE setting_key = 'business_name'");
        $businessName = $settings['setting_value'] ?? 'Nautilus Dive Shop';

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
            'business_name' => $businessName,
            'carousel_slides' => $carouselSlides,
            'service_boxes' => !empty($serviceBoxes) ? $serviceBoxes : null // null triggers defaults in view
        ];

        $this->renderStorefront('storefront/index', $data);
    }

    /**
     * Render storefront view (no admin sidebar)
     */
    private function renderStorefront(string $view, array $data = []): void
    {
        // Global Storefront Data
        $data['active_announcements'] = $this->settingsService->getActiveBanners();
        $data['social_links'] = $this->settingsService->getByCategory('social'); // Get social links
        $data['store_stats'] = $this->settingsService->getStoreStats(); // Get dynamic stats
        
        // Pass settings service for direct access
        $data['settings'] = $this->settingsService;

        // Pass flattened theme settings (using general category as fallback source)
        $generalSettings = $this->settingsService->getByCategory('general');
        $theme = [];
        foreach ($generalSettings as $key => $val) {
             $theme[$key] = $val['value'];
        }
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
        $data = [
            'business_name' => $this->getBusinessName()
        ];

        $this->renderStorefront('storefront/courses', $data);
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
        $data = [
            'business_name' => $this->getBusinessName()
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
        return $settings['setting_value'] ?? 'Nautilus Dive Shop';
    }
}
