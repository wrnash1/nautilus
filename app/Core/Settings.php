<?php

namespace App\Core;

use PDO;

/**
 * Settings Manager
 * 
 * Centralized settings management for the application
 */
class Settings
{
    private static $instance = null;
    private $settings = [];
    private $db;
    private $loaded = false;

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get a setting value
     */
    public function get(string $key, $default = null)
    {
        if (!$this->loaded) {
            $this->load();
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value): bool
    {
        try {
            // Check if setting exists
            $stmt = $this->db->prepare("
                SELECT id FROM system_settings WHERE setting_key = ?
            ");
            $stmt->execute([$key]);
            
            if ($stmt->fetch()) {
                // Update existing
                $stmt = $this->db->prepare("
                    UPDATE system_settings 
                    SET setting_value = ?, updated_at = NOW()
                    WHERE setting_key = ?
                ");
                $stmt->execute([$value, $key]);
            } else {
                // Insert new
                $stmt = $this->db->prepare("
                    INSERT INTO system_settings (setting_key, setting_value, created_at)
                    VALUES (?, ?, NOW())
                ");
                $stmt->execute([$key, $value]);
            }

            // Update cache
            $this->settings[$key] = $value;
            return true;

        } catch (\Exception $e) {
            error_log("Settings::set error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load all settings from database
     */
    private function load(): void
    {
        try {
            $stmt = $this->db->query("
                SELECT setting_key, setting_value, setting_type 
                FROM system_settings
            ");

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $value = $row['setting_value'];

                // Cast to appropriate type
                switch ($row['setting_type']) {
                    case 'boolean':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        break;
                    case 'integer':
                        $value = (int)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'json':
                        $value = json_decode($value, true);
                        break;
                }

                $this->settings[$row['setting_key']] = $value;
            }

            $this->loaded = true;

        } catch (\Exception $e) {
            error_log("Settings::load error: " . $e->getMessage());
            $this->loaded = true; // Prevent infinite loop
        }
    }

    /**
     * Get company/business information
     */
    public function getCompanyInfo(): array
    {
        return [
            'name' => $this->get('business_name', 'Nautilus Dive Shop'),
            'email' => $this->get('business_email', ''),
            'phone' => $this->get('business_phone', ''),
            'address' => $this->get('business_address', ''),
            'city' => $this->get('business_city', ''),
            'state' => $this->get('business_state', ''),
            'zip' => $this->get('business_zip', ''),
            'country' => $this->get('business_country', 'US'),
            'logo' => $this->get('company_logo_path', ''),
            'logo_small' => $this->get('company_logo_small_path', ''),
            'favicon' => $this->get('company_favicon_path', ''),
            'primary_color' => $this->get('brand_primary_color', '#0066cc'),
            'secondary_color' => $this->get('brand_secondary_color', '#003366'),
        ];
    }

    /**
     * Get all settings
     */
    public function all(): array
    {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->settings;
    }

    /**
     * Reload settings from database
     */
    public function reload(): void
    {
        $this->settings = [];
        $this->loaded = false;
        $this->load();
    }
}
