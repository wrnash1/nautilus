<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Services\Admin\SettingsService;

class SettingsController
{
    private SettingsService $service;

    public function __construct()
    {
        $this->service = new SettingsService();
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
     * Integration settings
     */
    public function integrations()
    {
        $settings = $this->service->getSettingsByCategory('integrations');

        require __DIR__ . '/../../Views/admin/settings/integrations.php';
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
}
