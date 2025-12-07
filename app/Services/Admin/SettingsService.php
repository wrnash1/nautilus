<?php

namespace App\Services\Admin;

use App\Core\Database;
use App\Core\Encryption;

class SettingsService
{
    /**
     * List of setting keys that should be encrypted in the database
     */
    private const ENCRYPTED_SETTINGS = [
        'stripe_secret_key',
        'stripe_webhook_secret',
        'square_access_token',
        'btcpay_api_key',
        'twilio_auth_token',
        'smtp_password',
        'padi_api_secret',
        'padi_api_key',
        'ssi_api_key',
        'ups_password',
        'fedex_secret_key',
        'wave_access_token'
    ];
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
            "SELECT `key`, `value`, `type`
             FROM settings
             WHERE category = ?
             ORDER BY `key`",
            [$category]
        );

        $settings = [];
        foreach ($results as $row) {
            $value = $row['value'];

            // Decode based on type
            switch ($row['type']) {
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
                    // Decrypt sensitive values
                    try {
                        $value = !empty($value) ? Encryption::decrypt($value) : '';
                    } catch (\Exception $e) {
                        // Log decryption error but don't expose it
                        error_log("Decryption failed for setting: {$row['key']} - " . $e->getMessage());
                        $value = '';
                    }
                    break;
            }

            $settings[$row['key']] = $value;
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
            "SELECT `value`, `type` FROM settings
             WHERE category = ? AND `key` = ?",
            [$category, $key]
        );

        if (!$result) {
            return $default;
        }

        $value = $result['value'];

        // Decode based on type
        switch ($result['type']) {
            case 'boolean':
                return $value === '1' || $value === 'true';
            case 'integer':
                return (int)$value;
            case 'json':
                return json_decode($value, true) ?? [];
            case 'encrypted':
                try {
                    return !empty($value) ? Encryption::decrypt($value) : '';
                } catch (\Exception $e) {
                    error_log("Decryption failed for setting: {$key} - " . $e->getMessage());
                    return '';
                }
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

        // Check if this setting should be encrypted
        if (in_array($key, self::ENCRYPTED_SETTINGS)) {
            $type = 'encrypted';
            // Encrypt the value before storing
            try {
                $value = !empty($value) ? Encryption::encrypt($value) : '';
            } catch (\Exception $e) {
                error_log("Encryption failed for setting: {$key} - " . $e->getMessage());
                return false;
            }
        } elseif (is_bool($value)) {
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
            "SELECT id FROM settings WHERE category = ? AND `key` = ?",
            [$category, $key]
        );

        $result = false;
        if ($exists) {
            // Update
            $result = Database::execute(
                "UPDATE settings
                 SET `value` = ?, `type` = ?, updated_at = NOW(), updated_by = ?
                 WHERE category = ? AND `key` = ?",
                [$value, $type, currentUser()['id'], $category, $key]
            );
        } else {
            // Insert
            $result = Database::execute(
                "INSERT INTO settings (category, `key`, `value`, `type`, updated_at, updated_by)
                 VALUES (?, ?, ?, ?, NOW(), ?)",
                [$category, $key, $value, $type, currentUser()['id']]
            );
        }

        // Log the update for audit trail
        if ($result) {
            $this->logSettingAccess('update', $category, $key);
        }

        return $result;
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
        $results = Database::fetchAll("SELECT * FROM settings ORDER BY category, `key`");

        $settings = [];
        foreach ($results as $row) {
            if (!isset($settings[$row['category']])) {
                $settings[$row['category']] = [];
            }
            $settings[$row['category']][$row['key']] = $row['value'];
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

    /**
     * Get a masked version of a setting value (for display purposes)
     * Shows only last 4 characters for sensitive values
     */
    public function getMaskedSetting(string $category, string $key): string
    {
        $value = $this->getSetting($category, $key, '');

        if (empty($value)) {
            return '';
        }

        // Only mask encrypted settings
        if (in_array($key, self::ENCRYPTED_SETTINGS)) {
            return Encryption::mask($value, 4);
        }

        return $value;
    }

    /**
     * Check if a setting key should be encrypted
     */
    public function isEncryptedSetting(string $key): bool
    {
        return in_array($key, self::ENCRYPTED_SETTINGS);
    }

    /**
     * Log setting access/modification for audit trail
     */
    private function logSettingAccess(string $action, string $category, string $key): void
    {
        // Only log access to sensitive (encrypted) settings
        if (!in_array($key, self::ENCRYPTED_SETTINGS)) {
            return;
        }

        $userId = currentUser()['id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        try {
            Database::execute(
                "INSERT INTO settings_audit (setting_key, action, user_id, ip_address, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                ["{$category}.{$key}", $action, $userId, $ipAddress]
            );
        } catch (\Exception $e) {
            // Log error but don't fail the operation
            error_log("Failed to log setting access: " . $e->getMessage());
        }
    }
}
