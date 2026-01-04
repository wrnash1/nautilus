<?php

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Settings;
use PDO;

/**
 * Settings Controller
 * 
 * Manages application settings and configuration
 */
class SettingsController
{
    private $db;
    private $settings;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->settings = Settings::getInstance();
    }

    /**
     * Settings dashboard
     */
    public function index()
    {
        $company = getCompanyInfo();
        $allSettings = $this->settings->all();
        
        require BASE_PATH . '/app/Views/admin/settings/index.php';
    }

    /**
     * General settings page
     */
    public function general()
    {
        $company = getCompanyInfo();
        
        require BASE_PATH . '/app/Views/admin/settings/general.php';
    }

    /**
     * Update general settings
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/settings');
        }

        try {
            // Update company information
            $this->settings->set('business_name', $_POST['business_name'] ?? '');
            $this->settings->set('business_email', $_POST['business_email'] ?? '');
            $this->settings->set('business_phone', $_POST['business_phone'] ?? '');
            $this->settings->set('business_address', $_POST['business_address'] ?? '');
            $this->settings->set('business_city', $_POST['business_city'] ?? '');
            $this->settings->set('business_state', $_POST['business_state'] ?? '');
            $this->settings->set('business_zip', $_POST['business_zip'] ?? '');
            $this->settings->set('business_country', $_POST['business_country'] ?? 'US');
            $this->settings->set('business_hours', $_POST['business_hours'] ?? '');
            
            // Regional Settings
            $this->settings->set('timezone', $_POST['timezone'] ?? 'America/New_York');
            $this->settings->set('currency', $_POST['currency'] ?? 'USD');
            $this->settings->set('date_format', $_POST['date_format'] ?? 'Y-m-d');
            $this->settings->set('time_format', $_POST['time_format'] ?? 'g:i A');
            
            // Update branding
            $this->settings->set('brand_primary_color', $_POST['brand_primary_color'] ?? '#0066cc');
            $this->settings->set('brand_secondary_color', $_POST['brand_secondary_color'] ?? '#003366');
            
            // Reload settings
            $this->settings->reload();
            
            $_SESSION['flash_success'] = 'Settings updated successfully!';
            redirect('/store/admin/settings');
            
        } catch (\Exception $e) {
            error_log("Settings update error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to update settings: ' . $e->getMessage();
            redirect('/store/admin/settings');
        }
    }


    /**
     * Upload logo
     */
    public function uploadLogo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/settings');
        }

        try {
            $uploadDir = BASE_PATH . '/public/uploads/branding/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
                    $this->settings->set('company_logo_path', '/uploads/branding/' . $filename);
                }
            }

            // Handle small logo upload
            if (isset($_FILES['logo_small']) && $_FILES['logo_small']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['logo_small']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_small_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['logo_small']['tmp_name'], $filepath)) {
                    $this->settings->set('company_logo_small_path', '/uploads/branding/' . $filename);
                }
            }

            // Handle favicon upload
            if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
                $filename = 'favicon_' . time() . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['favicon']['tmp_name'], $filepath)) {
                    $this->settings->set('company_favicon_path', '/uploads/branding/' . $filename);
                }
            }

            $this->settings->reload();
            
            $_SESSION['flash_success'] = 'Logo uploaded successfully!';
            
        } catch (\Exception $e) {
            error_log("Logo upload error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to upload logo: ' . $e->getMessage();
        }

        redirect('/store/admin/settings');
    }

    /**
     * Update Tax Settings
     */
    public function updateTax()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/settings/tax');
        }

        try {
            $this->settings->set('tax_enabled', isset($_POST['tax_enabled']));
            // Store as decimal (e.g. 7.5 -> 0.075)
            $rate = floatval($_POST['tax_rate'] ?? 0);
            $this->settings->set('tax_rate', $rate / 100);
            
            $this->settings->set('tax_inclusive', isset($_POST['tax_inclusive']));
            $this->settings->set('tax_label', $_POST['tax_label'] ?? 'Tax');
            
            $this->settings->reload();
            $_SESSION['flash_success'] = 'Tax settings updated successfully!';
            
        } catch (\Exception $e) {
            error_log("Tax update error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to update tax settings';
        }
        
        redirect('/store/admin/settings/tax');
    }

    /**
     * Update Email Settings
     */
    public function updateEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/admin/settings/email');
        }

        try {
            $this->settings->set('smtp_host', $_POST['smtp_host'] ?? '');
            $this->settings->set('smtp_port', (int)($_POST['smtp_port'] ?? 587));
            $this->settings->set('smtp_username', $_POST['smtp_username'] ?? '');
            
            // Only update password if provided
            if (!empty($_POST['smtp_password'])) {
                $this->settings->set('smtp_password', $_POST['smtp_password']);
            }
            
            $this->settings->set('smtp_encryption', $_POST['smtp_encryption'] ?? 'tls');
            $this->settings->set('from_email', $_POST['from_email'] ?? '');
            $this->settings->set('from_name', $_POST['from_name'] ?? '');
            
            $this->settings->reload();
            $_SESSION['flash_success'] = 'Email settings updated successfully!';
            
        } catch (\Exception $e) {
            error_log("Email update error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to update email settings';
        }
        
        redirect('/store/admin/settings/email');
    }

    /**
     * Tax settings
     */
    public function tax()
    {
        $company = getCompanyInfo();
        $taxRate = $this->settings->get('tax_rate', 0.07);
        
        require BASE_PATH . '/app/Views/admin/settings/tax.php';
    }

    /**
     * Email settings
     */
    public function email()
    {
        $company = getCompanyInfo();
        
        require BASE_PATH . '/app/Views/admin/settings/email.php';
    }

    /**
     * Payment settings
     */
    public function payment()
    {
        $company = getCompanyInfo();
        
        require BASE_PATH . '/app/Views/admin/settings/payment.php';
    }

    /**
     * Rental settings
     */
    public function rental()
    {
        $company = getCompanyInfo();
        
        require BASE_PATH . '/app/Views/admin/settings/rental.php';
    }

    /**
     * Air fills settings
     */
    public function airFills()
    {
        $company = getCompanyInfo();
        
        require BASE_PATH . '/app/Views/admin/settings/air-fills.php';
    }

    /**
     * Integrations settings
     */
    public function integrations()
    {
        $company = getCompanyInfo();
        
        require BASE_PATH . '/app/Views/admin/settings/integrations.php';
    }

    /**
     * Updates page
     */
    public function updates()
    {
        if (!hasPermission('settings.edit')) {
            redirect('/store/dashboard');
        }

        $company = getCompanyInfo();
        $updateService = new \App\Services\System\UpdateService();
        
        $current = $updateService->getCurrentVersion();
        $updates = $updateService->checkForUpdates();
        
        require BASE_PATH . '/app/Views/admin/settings/updates.php';
    }

    /**
     * Run system update
     */
    public function runUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/settings/updates');
        }

        if (!hasPermission('settings.edit')) {
            $_SESSION['flash_error'] = 'Access denied. Super Admin required.';
            redirect('/store/dashboard');
        }

        try {
            $updateService = new \App\Services\System\UpdateService();
            $result = $updateService->performUpdate();
            
            if ($result['success']) {
                $_SESSION['flash_success'] = 'System updated successfully! ' . substr($result['message'], 0, 100) . '...';
            } else {
                $_SESSION['flash_error'] = 'Update failed: ' . $result['message'];
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'An unexpected error occurred: ' . $e->getMessage();
        }
        
        redirect('/admin/settings/updates');
    }
}
