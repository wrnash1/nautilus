<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\Admin\SettingsService;
use App\Services\FileUploadService;

class SettingsController
{
    private SettingsService $service;
    private FileUploadService $uploadService;

    public function __construct()
    {
        $this->service = new SettingsService();
        $this->uploadService = new FileUploadService();
    }

    /**
     * Display settings dashboard
     */
    public function index()
    {
        $categories = $this->service->getSettingCategories();

        require __DIR__ . '/../../Views/admin/settings/index.php';
    }

    /**
     * Show settings by category
     */
    public function category(string $category)
    {
        $settings = $this->service->getSettingsByCategory($category);
        $categoryName = ucwords(str_replace('_', ' ', $category));

        require __DIR__ . '/../../Views/admin/settings/category.php';
    }

    /**
     * Update settings
     */
    public function update()
    {
        try {
            $category = $_POST['category'] ?? 'general';
            $settings = $_POST['settings'] ?? [];

            foreach ($settings as $key => $value) {
                $this->service->updateSetting($category, $key, $value);
            }

            setFlashMessage('success', 'Settings updated successfully!');
            header('Location: /admin/settings/' . $category);
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to update settings: ' . $e->getMessage());
            header('Location: /admin/settings');
            exit;
        }
    }

    /**
     * General business settings
     */
    public function general()
    {
        $settings = $this->service->getSettingsByCategory('general');

        require __DIR__ . '/../../Views/admin/settings/general.php';
    }

    /**
     * Upload company logo and branding
     */
    public function uploadLogo()
    {
        try {
            $updated = false;

            // Handle main company logo upload
            if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $result = $this->uploadService->upload($_FILES['company_logo'], 'logo', 'company_logo');

                if ($result['success']) {
                    // Delete old logo if exists
                    $oldLogo = $this->service->getSetting('general', 'company_logo_path');
                    if ($oldLogo && $oldLogo !== $result['path']) {
                        $this->uploadService->delete($oldLogo);
                    }

                    $this->service->updateSetting('general', 'company_logo_path', $result['path']);
                    $updated = true;
                } else {
                    setFlashMessage('error', 'Logo upload failed: ' . $result['error']);
                    header('Location: /admin/settings/general');
                    exit;
                }
            }

            // Handle small logo/icon upload
            if (isset($_FILES['company_logo_small']) && $_FILES['company_logo_small']['error'] !== UPLOAD_ERR_NO_FILE) {
                $result = $this->uploadService->upload($_FILES['company_logo_small'], 'logo', 'company_logo_small');

                if ($result['success']) {
                    // Delete old logo if exists
                    $oldLogo = $this->service->getSetting('general', 'company_logo_small_path');
                    if ($oldLogo && $oldLogo !== $result['path']) {
                        $this->uploadService->delete($oldLogo);
                    }

                    $this->service->updateSetting('general', 'company_logo_small_path', $result['path']);
                    $updated = true;
                } else {
                    setFlashMessage('error', 'Icon upload failed: ' . $result['error']);
                    header('Location: /admin/settings/general');
                    exit;
                }
            }

            // Update branding settings (tagline, colors)
            if (isset($_POST['company_tagline'])) {
                $this->service->updateSetting('general', 'company_tagline', $_POST['company_tagline']);
                $updated = true;
            }

            if (isset($_POST['brand_primary_color'])) {
                $this->service->updateSetting('general', 'brand_primary_color', $_POST['brand_primary_color']);
                $updated = true;
            }

            if (isset($_POST['brand_secondary_color'])) {
                $this->service->updateSetting('general', 'brand_secondary_color', $_POST['brand_secondary_color']);
                $updated = true;
            }

            // Mark logo setup as completed
            if ($updated) {
                $this->service->updateSetting('general', 'logo_setup_completed', true);
                setFlashMessage('success', 'Company branding updated successfully!');
            } else {
                setFlashMessage('info', 'No changes were made.');
            }

            header('Location: /admin/settings/general');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to update branding: ' . $e->getMessage());
            header('Location: /admin/settings/general');
            exit;
        }
    }

    /**
     * Tax settings
     */
    public function tax()
    {
        $taxRates = Database::fetchAll(
            "SELECT * FROM tax_rates WHERE is_active = 1 ORDER BY name"
        );

        $settings = $this->service->getSettingsByCategory('tax');

        require __DIR__ . '/../../Views/admin/settings/tax.php';
    }

    /**
     * Store new tax rate
     */
    public function storeTaxRate()
    {
        try {
            $data = [
                'name' => $_POST['name'],
                'rate' => $_POST['rate'],
                'type' => $_POST['type'] ?? 'percentage',
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];

            // If this is default, unset other defaults
            if ($data['is_default']) {
                Database::execute("UPDATE tax_rates SET is_default = 0");
            }

            Database::execute(
                "INSERT INTO tax_rates (name, rate, type, is_default, is_active, created_at)
                 VALUES (?, ?, ?, ?, 1, NOW())",
                [$data['name'], $data['rate'], $data['type'], $data['is_default']]
            );

            setFlashMessage('success', 'Tax rate added successfully!');
            header('Location: /admin/settings/tax');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to add tax rate: ' . $e->getMessage());
            header('Location: /admin/settings/tax');
            exit;
        }
    }

    /**
     * Update tax rate
     */
    public function updateTaxRate(int $id)
    {
        try {
            $data = [
                'name' => $_POST['name'],
                'rate' => $_POST['rate'],
                'type' => $_POST['type'] ?? 'percentage',
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ];

            // If this is default, unset other defaults
            if ($data['is_default']) {
                Database::execute("UPDATE tax_rates SET is_default = 0 WHERE id != ?", [$id]);
            }

            Database::execute(
                "UPDATE tax_rates SET name = ?, rate = ?, type = ?, is_default = ? WHERE id = ?",
                [$data['name'], $data['rate'], $data['type'], $data['is_default'], $id]
            );

            setFlashMessage('success', 'Tax rate updated successfully!');
            header('Location: /admin/settings/tax');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to update tax rate: ' . $e->getMessage());
            header('Location: /admin/settings/tax');
            exit;
        }
    }

    /**
     * Delete tax rate
     */
    public function deleteTaxRate(int $id)
    {
        try {
            Database::execute("UPDATE tax_rates SET is_active = 0 WHERE id = ?", [$id]);

            setFlashMessage('success', 'Tax rate deleted successfully!');
            header('Location: /admin/settings/tax');
            exit;

        } catch (\Exception $e) {
            setFlashMessage('error', 'Failed to delete tax rate: ' . $e->getMessage());
            header('Location: /admin/settings/tax');
            exit;
        }
    }

    /**
     * Email settings
     */
    public function email()
    {
        $settings = $this->service->getSettingsByCategory('email');

        require __DIR__ . '/../../Views/admin/settings/email.php';
    }

    /**
     * Payment settings
     */
    public function payment()
    {
        $settings = $this->service->getSettingsByCategory('payment');

        require __DIR__ . '/../../Views/admin/settings/payment.php';
    }

    /**
     * Rental settings
     */
    public function rental()
    {
        $settings = $this->service->getSettingsByCategory('rental');

        require __DIR__ . '/../../Views/admin/settings/rental.php';
    }

    /**
     * Air fill pricing settings
     */
    public function airFills()
    {
        $settings = $this->service->getSettingsByCategory('air_fills');

        require __DIR__ . '/../../Views/admin/settings/air-fills.php';
    }

    /**
     * Integration settings (Wave Apps, AI, etc.)
     */
    public function integrations()
    {
        // Get all integration settings from system_settings table
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $integrationSettings = Database::fetchAll(
            "SELECT * FROM system_settings WHERE tenant_id = ? AND category IN ('integrations', 'ai') ORDER BY category, display_order",
            [$tenantId]
        );

        // Organize by category
        $settings = [
            'integrations' => [],
            'ai' => []
        ];

        foreach ($integrationSettings as $setting) {
            $settings[$setting['category']][$setting['setting_key']] = $setting;
        }

        require __DIR__ . '/../../Views/admin/settings/integrations.php';
    }

    /**
     * Update integration settings (Wave, AI, etc.)
     */
    public function updateIntegrations()
    {
        try {
            $tenantId = $_SESSION['tenant_id'] ?? 1;

            foreach ($_POST as $key => $value) {
                if ($key === 'csrf_token') continue;

                // Update or insert setting
                Database::query(
                    "INSERT INTO system_settings (tenant_id, category, setting_key, setting_value)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                    [$tenantId, $this->getSettingCategory($key), $key, $value]
                );
            }

            $_SESSION['flash_success'] = 'Integration settings updated successfully!';
            header('Location: /store/admin/settings/integrations');
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to update settings: ' . $e->getMessage();
            header('Location: /store/admin/settings/integrations');
            exit;
        }
    }

    /**
     * Determine setting category from key name
     */
    private function getSettingCategory(string $key): string
    {
        if (strpos($key, 'wave_') === 0) return 'integrations';
        if (strpos($key, 'ai_') === 0) return 'ai';
        if (strpos($key, 'email_') === 0 || strpos($key, 'smtp_') === 0) return 'email';
        return 'general';
    }

    /**
     * Test email configuration
     */
    public function testEmail()
    {
        try {
            $to = $_POST['email'] ?? currentUser()['email'];

            // Get email settings
            $settings = $this->service->getSettingsByCategory('email');

            // Send test email
            $sent = $this->service->sendTestEmail($to, $settings);

            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Test email sent to ' . $to]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to send email']);
            }

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * API endpoint to get current tax rate
     */
    public function getTaxRate()
    {
        header('Content-Type: application/json');

        try {
            // Get default tax rate from settings or tax_rates table
            $db = Database::getInstance();

            // First, try to get from settings table
            $setting = $db->prepare("SELECT value FROM settings WHERE category = 'tax' AND `key` = 'default_rate' LIMIT 1");
            $setting->execute();
            $result = $setting->fetch();

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'tax_rate' => floatval($result['value'])
                ]);
                return;
            }

            // Fall back to tax_rates table
            $taxRate = $db->prepare("SELECT rate FROM tax_rates WHERE is_default = 1 LIMIT 1");
            $taxRate->execute();
            $rate = $taxRate->fetch();

            if ($rate) {
                echo json_encode([
                    'success' => true,
                    'tax_rate' => floatval($rate['rate']) / 100 // Convert percentage to decimal
                ]);
            } else {
                // Default to 8% if no rate configured
                echo json_encode([
                    'success' => true,
                    'tax_rate' => 0.08
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'tax_rate' => 0.08 // Fallback
            ]);
        }
    }
}
