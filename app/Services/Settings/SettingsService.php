<?php

namespace App\Services\Settings;

use App\Core\Database;
use PDO;
use App\Core\Logger;

/**
 * Settings Service
 * Manages application settings
 */
class SettingsService
{
    private PDO $db;
    private Logger $logger;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Get a setting value
     */
    public function get(string $key, string $category = 'general', $default = null)
    {
        $cacheKey = "{$category}.{$key}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $sql = "SELECT value, type FROM settings WHERE category = ? AND `key` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category, $key]);

        $setting = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$setting) {
            return $default;
        }

        $value = $this->castValue($setting['value'], $setting['type']);
        self::$cache[$cacheKey] = $value;

        return $value;
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, string $category = 'general', string $type = 'string', ?string $description = null, ?int $updatedBy = null): bool
    {
        try {
            // Convert value to string for storage
            $stringValue = $this->valueToString($value, $type);

            $sql = "INSERT INTO settings (`category`, `key`, `value`, `type`, `description`, `updated_by`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    value = VALUES(value),
                    type = VALUES(type),
                    description = VALUES(description),
                    updated_by = VALUES(updated_by),
                    updated_at = NOW()";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$category, $key, $stringValue, $type, $description, $updatedBy]);

            // Clear cache
            $cacheKey = "{$category}.{$key}";
            unset(self::$cache[$cacheKey]);

            $this->logger->info('Setting updated', [
                'category' => $category,
                'key' => $key,
                'updated_by' => $updatedBy
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to set setting', [
                'category' => $category,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all settings in a category
     */
    public function getCategory(string $category): array
    {
        $sql = "SELECT `key`, `value`, `type`, `description` FROM settings WHERE category = ? ORDER BY `key`";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);

        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $settings[$row['key']] = [
                'value' => $this->castValue($row['value'], $row['type']),
                'type' => $row['type'],
                'description' => $row['description']
            ];
        }

        return $settings;
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM settings ORDER BY category";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'category');
    }

    /**
     * Get all settings
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM settings ORDER BY category, `key`";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $settings = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (!isset($settings[$row['category']])) {
                $settings[$row['category']] = [];
            }

            $settings[$row['category']][$row['key']] = [
                'value' => $this->castValue($row['value'], $row['type']),
                'type' => $row['type'],
                'description' => $row['description'],
                'updated_at' => $row['updated_at']
            ];
        }

        return $settings;
    }

    /**
     * Delete a setting
     */
    public function delete(string $key, string $category = 'general'): bool
    {
        try {
            $sql = "DELETE FROM settings WHERE category = ? AND `key` = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$category, $key]);

            // Clear cache
            $cacheKey = "{$category}.{$key}";
            unset(self::$cache[$cacheKey]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete setting', [
                'category' => $category,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cast value based on type
     */
    private function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'integer':
                return (int)$value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true);
            case 'encrypted':
                // TODO: Implement encryption/decryption
                return $value;
            default:
                return $value;
        }
    }

    /**
     * Convert value to string for storage
     */
    private function valueToString($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return json_encode($value);
            case 'integer':
                return (string)$value;
            default:
                return (string)$value;
        }
    }

    /**
     * Clear all cached settings
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Initialize default settings
     */
    public function initializeDefaults(?int $userId = null): void
    {
        $defaults = [
            // General
            ['category' => 'general', 'key' => 'site_name', 'value' => 'Nautilus Dive Shop', 'type' => 'string', 'description' => 'Site name'],
            ['category' => 'general', 'key' => 'site_tagline', 'value' => 'Professional Dive Shop', 'type' => 'string', 'description' => 'Site tagline'],
            ['category' => 'general', 'key' => 'timezone', 'value' => 'America/New_York', 'type' => 'string', 'description' => 'Default timezone'],
            ['category' => 'general', 'key' => 'currency', 'value' => 'USD', 'type' => 'string', 'description' => 'Default currency'],
            ['category' => 'general', 'key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'description' => 'Date format'],

            // Business
            ['category' => 'business', 'key' => 'business_name', 'value' => 'Nautilus Dive Shop', 'type' => 'string', 'description' => 'Legal business name'],
            ['category' => 'business', 'key' => 'phone', 'value' => '', 'type' => 'string', 'description' => 'Business phone number'],
            ['category' => 'business', 'key' => 'email', 'value' => 'info@nautilus.local', 'type' => 'string', 'description' => 'Business email'],
            ['category' => 'business', 'key' => 'address', 'value' => '', 'type' => 'string', 'description' => 'Business address'],
            ['category' => 'business', 'key' => 'tax_rate', 'value' => '0.00', 'type' => 'string', 'description' => 'Default tax rate (percentage)'],

            // Email
            ['category' => 'email', 'key' => 'smtp_enabled', 'value' => '0', 'type' => 'boolean', 'description' => 'Enable SMTP'],
            ['category' => 'email', 'key' => 'smtp_host', 'value' => 'localhost', 'type' => 'string', 'description' => 'SMTP host'],
            ['category' => 'email', 'key' => 'smtp_port', 'value' => '587', 'type' => 'integer', 'description' => 'SMTP port'],
            ['category' => 'email', 'key' => 'smtp_username', 'value' => '', 'type' => 'string', 'description' => 'SMTP username'],
            ['category' => 'email', 'key' => 'from_email', 'value' => 'noreply@nautilus.local', 'type' => 'string', 'description' => 'From email address'],
            ['category' => 'email', 'key' => 'from_name', 'value' => 'Nautilus Dive Shop', 'type' => 'string', 'description' => 'From name'],

            // Inventory
            ['category' => 'inventory', 'key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer', 'description' => 'Default low stock threshold'],
            ['category' => 'inventory', 'key' => 'track_inventory_default', 'value' => '1', 'type' => 'boolean', 'description' => 'Track inventory by default'],
            ['category' => 'inventory', 'key' => 'allow_negative_stock', 'value' => '0', 'type' => 'boolean', 'description' => 'Allow negative stock quantities'],

            // Notifications
            ['category' => 'notifications', 'key' => 'low_stock_alerts', 'value' => '1', 'type' => 'boolean', 'description' => 'Enable low stock alerts'],
            ['category' => 'notifications', 'key' => 'equipment_due_alerts', 'value' => '1', 'type' => 'boolean', 'description' => 'Enable equipment due alerts'],
            ['category' => 'notifications', 'key' => 'appointment_reminders', 'value' => '1', 'type' => 'boolean', 'description' => 'Enable appointment reminders'],

            // Security
            ['category' => 'security', 'key' => 'session_timeout', 'value' => '3600', 'type' => 'integer', 'description' => 'Session timeout in seconds'],
            ['category' => 'security', 'key' => 'require_2fa', 'value' => '0', 'type' => 'boolean', 'description' => 'Require two-factor authentication'],
            ['category' => 'security', 'key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'description' => 'Minimum password length'],
        ];

        foreach ($defaults as $setting) {
            // Check if setting already exists
            $existing = $this->get($setting['key'], $setting['category']);
            if ($existing === null) {
                $this->set(
                    $setting['key'],
                    $setting['value'],
                    $setting['category'],
                    $setting['type'],
                    $setting['description'],
                    $userId
                );
            }
        }
    }
}
