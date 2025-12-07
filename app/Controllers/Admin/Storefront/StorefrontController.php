<?php

namespace App\Controllers\Admin\Storefront;

use App\Services\Storefront\ThemeEngine;
use App\Services\Storefront\StorefrontSettingsService;
use App\Services\FileUploadService;

class StorefrontController
{
    private $themeEngine;
    private $settingsService;
    private $fileUpload;

    public function __construct()
    {
        $this->themeEngine = new ThemeEngine();
        $this->settingsService = new StorefrontSettingsService();
        $this->fileUpload = new FileUploadService();
    }

    /**
     * Main storefront configuration dashboard
     */
    public function index()
    {
        $activeTheme = $this->themeEngine->getActiveTheme();
        $allSettings = $this->settingsService->getAll();
        $categories = $this->settingsService->getCategories();

        require __DIR__ . '/../../../Views/admin/storefront/index.php';
    }

    /**
     * Theme designer page
     */
    public function themeDesigner()
    {
        $activeTheme = $this->themeEngine->getActiveTheme();
        $allThemes = $this->themeEngine->getAllThemes();

        require __DIR__ . '/../../../Views/admin/storefront/theme-designer.php';
    }

    /**
     * Get theme data as JSON (for AJAX requests)
     */
    public function getTheme()
    {
        $themeId = $_GET['theme_id'] ?? null;

        if ($themeId) {
            $theme = $this->themeEngine->getThemeById((int)$themeId);
        } else {
            $theme = $this->themeEngine->getActiveTheme();
        }

        header('Content-Type: application/json');
        echo json_encode($theme);
    }

    /**
     * Update theme configuration
     */
    public function updateTheme()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $themeId = $_POST['theme_id'] ?? null;
        if (!$themeId) {
            echo json_encode(['success' => false, 'message' => 'Theme ID required']);
            return;
        }

        $data = $_POST;
        unset($data['theme_id'], $data['csrf_token']);

        $success = $this->themeEngine->updateTheme((int)$themeId, $data);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Theme updated successfully' : 'Failed to update theme'
        ]);
    }

    /**
     * Create new theme
     */
    public function createTheme()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        // Add current user as creator
        if (isset($_SESSION['user_id'])) {
            $data['created_by'] = $_SESSION['user_id'];
        }

        $themeId = $this->themeEngine->createTheme($data);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $themeId > 0,
            'theme_id' => $themeId,
            'message' => $themeId > 0 ? 'Theme created successfully' : 'Failed to create theme'
        ]);
    }

    /**
     * Set active theme
     */
    public function setActiveTheme()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $themeId = $_POST['theme_id'] ?? null;
        if (!$themeId) {
            echo json_encode(['success' => false, 'message' => 'Theme ID required']);
            return;
        }

        $success = $this->themeEngine->setActiveTheme((int)$themeId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Theme activated successfully' : 'Failed to activate theme'
        ]);
    }

    /**
     * Delete theme
     */
    public function deleteTheme()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $themeId = $_POST['theme_id'] ?? null;
        if (!$themeId) {
            echo json_encode(['success' => false, 'message' => 'Theme ID required']);
            return;
        }

        $success = $this->themeEngine->deleteTheme((int)$themeId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Theme deleted successfully' : 'Cannot delete active or default theme'
        ]);
    }

    /**
     * Homepage section manager
     */
    public function homepageBuilder()
    {
        $activeTheme = $this->themeEngine->getActiveTheme();
        $sections = $this->themeEngine->getHomepageSections($activeTheme['id'] ?? null);

        $availableSectionTypes = [
            'hero' => 'Hero Section',
            'featured_products' => 'Featured Products',
            'categories' => 'All Categories',
            'featured_categories' => 'Featured Categories',
            'courses' => 'Courses',
            'trips' => 'Dive Trips',
            'testimonials' => 'Customer Testimonials',
            'blog_posts' => 'Blog Posts',
            'brands' => 'Brand Logos',
            'newsletter' => 'Newsletter Signup',
            'video' => 'Video Section',
            'image_banner' => 'Image Banner',
            'custom_html' => 'Custom HTML',
            'countdown_timer' => 'Countdown Timer'
        ];

        require __DIR__ . '/../../../Views/admin/storefront/homepage-builder.php';
    }

    /**
     * Visual page builder (Craft CMS style)
     */
    public function visualBuilder()
    {
        require __DIR__ . '/../../../Views/admin/storefront/builder.php';
    }

    /**
     * Save builder data
     */
    public function saveBuilder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }

        // Save to database or file
        $savedData = json_encode($data, JSON_PRETTY_PRINT);
        $savePath = __DIR__ . '/../../../../storage/storefront-builder.json';

        $success = file_put_contents($savePath, $savedData) !== false;

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Storefront saved successfully' : 'Failed to save storefront'
        ]);
    }

    /**
     * Update homepage section
     */
    public function updateSection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $sectionId = $_POST['section_id'] ?? null;
        if (!$sectionId) {
            echo json_encode(['success' => false, 'message' => 'Section ID required']);
            return;
        }

        $data = $_POST;
        unset($data['section_id'], $data['csrf_token']);

        // Parse config JSON if present
        if (isset($data['config']) && is_string($data['config'])) {
            $data['config'] = json_decode($data['config'], true);
        }

        $success = $this->themeEngine->updateHomepageSection((int)$sectionId, $data);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Section updated successfully' : 'Failed to update section'
        ]);
    }

    /**
     * Create homepage section
     */
    public function createSection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $activeTheme = $this->themeEngine->getActiveTheme();
        if (!$activeTheme) {
            echo json_encode(['success' => false, 'message' => 'No active theme found']);
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $sectionId = $this->themeEngine->createHomepageSection($activeTheme['id'], $data);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $sectionId > 0,
            'section_id' => $sectionId,
            'message' => $sectionId > 0 ? 'Section created successfully' : 'Failed to create section'
        ]);
    }

    /**
     * Delete homepage section
     */
    public function deleteSection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $sectionId = $_POST['section_id'] ?? null;
        if (!$sectionId) {
            echo json_encode(['success' => false, 'message' => 'Section ID required']);
            return;
        }

        $success = $this->themeEngine->deleteHomepageSection((int)$sectionId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Section deleted successfully' : 'Failed to delete section'
        ]);
    }

    /**
     * Reorder homepage sections
     */
    public function reorderSections()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $sectionOrder = $_POST['section_order'] ?? null;
        if (!$sectionOrder || !is_array($sectionOrder)) {
            echo json_encode(['success' => false, 'message' => 'Section order array required']);
            return;
        }

        $success = $this->themeEngine->reorderSections($sectionOrder);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Sections reordered successfully' : 'Failed to reorder sections'
        ]);
    }

    /**
     * Settings management
     */
    public function settings()
    {
        $category = $_GET['category'] ?? 'general';
        $categories = $this->settingsService->getCategories();

        if (!isset($categories[$category])) {
            $category = 'general';
        }

        $settings = $this->settingsService->getByCategory($category);

        require __DIR__ . '/../../../Views/admin/storefront/settings.php';
    }

    /**
     * Update settings
     */
    public function updateSettings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $settings = $_POST;
        unset($settings['csrf_token']);

        $success = $this->settingsService->setMany($settings);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Settings updated successfully' : 'Failed to update settings'
        ]);
    }

    /**
     * Navigation menu manager
     */
    public function navigationManager()
    {
        $location = $_GET['location'] ?? 'header';
        $headerMenu = $this->settingsService->getNavigationMenu('header');
        $footerMenu = $this->settingsService->getNavigationMenu('footer');

        require __DIR__ . '/../../../Views/admin/storefront/navigation.php';
    }

    /**
     * Upload theme asset (logo, images, etc.)
     */
    public function uploadAsset()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $themeId = $_POST['theme_id'] ?? null;
        $assetType = $_POST['asset_type'] ?? null;

        if (!$themeId || !$assetType) {
            echo json_encode(['success' => false, 'message' => 'Theme ID and asset type required']);
            return;
        }

        if (!isset($_FILES['file'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            return;
        }

        // Upload file using FileUploadService
        $uploadResult = $this->fileUpload->upload($_FILES['file'], 'theme_assets');

        if (!$uploadResult['success']) {
            echo json_encode($uploadResult);
            return;
        }

        // Save asset to database
        $assetId = $this->themeEngine->uploadAsset(
            (int)$themeId,
            $assetType,
            $uploadResult['file_path'],
            [
                'asset_name' => $uploadResult['original_name'] ?? basename($uploadResult['file_path']),
                'file_size' => $uploadResult['file_size'] ?? null,
                'mime_type' => $uploadResult['mime_type'] ?? null,
                'is_primary' => $_POST['is_primary'] ?? true,
                'uploaded_by' => $_SESSION['user_id'] ?? null
            ]
        );

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $assetId > 0,
            'asset_id' => $assetId,
            'file_path' => $uploadResult['file_path'],
            'message' => $assetId > 0 ? 'Asset uploaded successfully' : 'Failed to save asset'
        ]);
    }

    /**
     * Preview theme
     */
    public function previewTheme()
    {
        $themeId = $_GET['theme_id'] ?? null;

        if ($themeId) {
            $theme = $this->themeEngine->getThemeById((int)$themeId);
        } else {
            $theme = $this->themeEngine->getActiveTheme();
        }

        // Load preview template with theme
        require __DIR__ . '/../../../Views/admin/storefront/theme-preview.php';
    }
}
