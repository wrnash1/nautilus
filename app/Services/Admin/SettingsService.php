<?php

namespace App\Services\Admin;

use App\Core\Database;

class SettingsService
{
    /**
     * Get all setting categories
     */
    public function getSettingCategories(): array
    {
        return [
            'general' => [
                'name' => 'General Settings',
                'icon' => 'bi-gear',
                'description' => 'Business name, address, timezone, and general configuration'
            ],
            'tax' => [
                'name' => 'Tax Settings',
                'icon' => 'bi-calculator',
                'description' => 'Tax rates and tax calculation settings'
            ],
            'email' => [
                'name' => 'Email Settings',
                'icon' => 'bi-envelope',
                'description' => 'SMTP configuration and email templates'
            ],
            'payment' => [
                'name' => 'Payment Settings',
                'icon' => 'bi-credit-card',
                'description' => 'Payment gateway configuration and accepted methods'
            ],
            'rental' => [
                'name' => 'Rental Settings',
                'icon' => 'bi-briefcase',
                'description' => 'Rental policies, deposit requirements, and pricing'
            ],
            'air_fills' => [
                'name' => 'Air Fill Pricing',
                'icon' => 'bi-wind',
                'description' => 'Pricing for air, nitrox, trimix, and oxygen fills'
            ],
            'integrations' => [
                'name' => 'Integrations',
                'icon' => 'bi-plugin',
                'description' => 'PADI, SSI, Stripe, and other third-party integrations'
            ]
        ];
    }

    /**
     * Get settings by category
     */
    public function getSettingsByCategory(string $category): array
    {
        $results = Database::fetchAll(
            "SELECT setting_key, setting_value, setting_type
             FROM settings
             WHERE category = ?
             ORDER BY setting_key",
            [$category]
        );

        $settings = [];
        foreach ($results as $row) {
            $value = $row['setting_value'];

            // Decode based on type
            switch ($row['setting_type']) {
                case 'boolean':
                    $value = $value === '1' || $value === 'true';
                    break;
                case 'integer':
                    $value = (int)$value;
                    break;
                case 'json':
                    $value = json_decode($value, true) ?? [];
                    break;
                case 'encrypted':
                    // Decrypt if needed
                    break;
            }

            $settings[$row['setting_key']] = $value;
        }

        // Return defaults if no settings exist
        return array_merge($this->getDefaultSettings($category), $settings);
    }

    /**
     * Get a single setting value
     */
    public function getSetting(string $category, string $key, $default = null)
    {
        $result = Database::fetchOne(
            "SELECT setting_value, setting_type FROM settings
             WHERE category = ? AND setting_key = ?",
            [$category, $key]
        );

        if (!$result) {
            return $default;
        }

        $value = $result['setting_value'];

        // Decode based on type
        switch ($result['setting_type']) {
            case 'boolean':
                return $value === '1' || $value === 'true';
            case 'integer':
                return (int)$value;
            case 'json':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }

    /**
     * Update or create a setting
     */
    public function updateSetting(string $category, string $key, $value): bool
    {
        // Determine type
        $type = 'string';
        if (is_bool($value)) {
            $type = 'boolean';
            $value = $value ? '1' : '0';
        } elseif (is_int($value)) {
            $type = 'integer';
        } elseif (is_array($value)) {
            $type = 'json';
            $value = json_encode($value);
        }

        // Check if exists
        $exists = Database::fetchOne(
            "SELECT id FROM settings WHERE category = ? AND setting_key = ?",
            [$category, $key]
        );

        if ($exists) {
            // Update
            return Database::execute(
                "UPDATE settings
                 SET setting_value = ?, setting_type = ?, updated_at = NOW(), updated_by = ?
                 WHERE category = ? AND setting_key = ?",
                [$value, $type, currentUser()['id'], $category, $key]
            );
        } else {
            // Insert
            return Database::execute(
                "INSERT INTO settings (category, setting_key, setting_value, setting_type, created_at, updated_at, updated_by)
                 VALUES (?, ?, ?, ?, NOW(), NOW(), ?)",
                [$category, $key, $value, $type, currentUser()['id']]
            );
        }
    }

    /**
     * Get default settings for a category
     */
    private function getDefaultSettings(string $category): array
    {
        $defaults = [
            'general' => [
                'business_name' => 'Nautilus Dive Shop',
                'business_email' => 'info@nautilusdiveshop.com',
                'business_phone' => '',
                'business_address' => '',
                'business_city' => '',
                'business_state' => '',
                'business_zip' => '',
                'business_country' => 'USA',
                'timezone' => 'America/New_York',
                'currency' => 'USD',
                'date_format' => 'Y-m-d',
                'time_format' => 'g:i A'
            ],
            'tax' => [
                'tax_enabled' => true,
                'default_tax_rate' => 0,
                'tax_inclusive' => false,
                'tax_label' => 'Tax'
            ],
            'email' => [
                'smtp_host' => '',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'from_email' => '',
                'from_name' => 'Nautilus Dive Shop'
            ],
            'payment' => [
                'stripe_enabled' => false,
                'stripe_publishable_key' => '',
                'stripe_secret_key' => '',
                'square_enabled' => false,
                'square_access_token' => '',
                'cash_enabled' => true,
                'check_enabled' => true,
                'credit_card_enabled' => true
            ],
            'rental' => [
                'deposit_required' => true,
                'deposit_percentage' => 50,
                'late_fee_enabled' => true,
                'late_fee_amount' => 10.00,
                'late_fee_type' => 'per_day',
                'damage_fee_enabled' => true,
                'inspection_reminder_days' => 30
            ],
            'air_fills' => [
                'air_price' => 8.00,
                'air_pressure' => 3000,
                'nitrox_price' => 12.00,
                'nitrox_pressure' => 3000,
                'trimix_price' => 25.00,
                'trimix_pressure' => 3000,
                'oxygen_price' => 15.00,
                'oxygen_pressure' => 3000
            ],
            'integrations' => [
                'padi_enabled' => false,
                'padi_api_key' => '',
                'padi_api_endpoint' => '',
                'ssi_enabled' => false,
                'ssi_api_key' => '',
                'ssi_api_endpoint' => '',
                'twiliotwilio_enabled' => false,
                'twilio_account_sid' => '',
                'twilio_auth_token' => '',
                'twilio_from_number' => ''
            ]
        ];

        return $defaults[$category] ?? [];
    }

    /**
     * Send test email
     */
    public function sendTestEmail(string $to, array $settings): bool
    {
        // This would use PHPMailer or similar
        // For now, return true as placeholder
        return true;
    }

    /**
     * Get all settings (for export/backup)
     */
    public function getAllSettings(): array
    {
        $results = Database::fetchAll("SELECT * FROM settings ORDER BY category, setting_key");

        $settings = [];
        foreach ($results as $row) {
            if (!isset($settings[$row['category']])) {
                $settings[$row['category']] = [];
            }
            $settings[$row['category']][$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Bulk import settings
     */
    public function importSettings(array $settings): int
    {
        $count = 0;

        foreach ($settings as $category => $categorySettings) {
            foreach ($categorySettings as $key => $value) {
                if ($this->updateSetting($category, $key, $value)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
