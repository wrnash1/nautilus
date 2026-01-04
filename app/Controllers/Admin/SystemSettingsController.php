<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Services\Settings\SettingsService;

class SystemSettingsController
{
    private SettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new SettingsService();
    }

    /**
     * Display settings management page
     */
    public function index()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.settings')) {
            $_SESSION['flash_error'] = 'You do not have permission to manage settings';
            header('Location: /store/dashboard');
            exit;
        }

        $category = $_GET['category'] ?? 'general';
        $categories = $this->settingsService->getCategories();
        $settings = $this->settingsService->getAll();

        require __DIR__ . '/../../Views/admin/settings/index.php';
    }

    /**
     * Update settings
     */
    public function update()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.settings')) {
            $_SESSION['flash_error'] = 'You do not have permission to manage settings';
            header('Location: /store/dashboard');
            exit;
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            header('Location: /store/admin/settings');
            exit;
        }

        try {
            $category = $_POST['category'] ?? 'general';
            $userId = Auth::userId();

            // Process each setting in the POST data
            foreach ($_POST as $key => $value) {
                // Skip non-setting fields
                if (in_array($key, ['csrf_token', 'category', 'submit'])) {
                    continue;
                }

                // Determine type
                $type = $_POST["type_{$key}"] ?? 'string';

                // Handle boolean checkboxes (unchecked boxes don't send POST data)
                if ($type === 'boolean' && !isset($_POST[$key])) {
                    $value = false;
                } elseif ($type === 'boolean') {
                    $value = true;
                }

                $this->settingsService->set($key, $value, $category, $type, null, $userId);
            }

            $_SESSION['flash_success'] = 'Settings updated successfully';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error updating settings: ' . $e->getMessage();
        }

        header('Location: /store/admin/settings?category=' . urlencode($category));
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.settings')) {
            $_SESSION['flash_error'] = 'You do not have permission to manage settings';
            header('Location: /store/dashboard');
            exit;
        }

        $this->settingsService->initializeDefaults(Auth::userId());

        $_SESSION['flash_success'] = 'Default settings initialized';
        header('Location: /store/admin/settings');
    }

    /**
     * Test email configuration
     */
    public function testEmail()
    {
        // Check admin permission
        if (!Auth::hasPermission('admin.settings')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }

        $emailService = new \App\Services\Email\EmailService();
        $result = $emailService->testConnection();

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
