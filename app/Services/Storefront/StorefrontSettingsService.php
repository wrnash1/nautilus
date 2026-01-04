<?php

namespace App\Services\Storefront;

use App\Core\Database;
use App\Core\Cache;

class StorefrontSettingsService
{
    private $db;
    private $cache;
    private const CACHE_KEY_PREFIX = 'storefront_setting_';
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
    }

    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $key;
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $stmt = $this->db->prepare("SELECT setting_value, setting_type FROM storefront_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return $default;
            }

            $value = $this->castValue($result['setting_value'], $result['setting_type']);
            $this->cache->set($cacheKey, $value, self::CACHE_TTL);

            return $value;
        } catch (\PDOException $e) {
            // If storefront_settings table doesn't exist, return default value
            if ($e->getCode() === '42S02') {
                return $default;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get multiple settings at once
     */
    public function getMany(array $keys): array
    {
        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value, setting_type
            FROM storefront_settings
            WHERE setting_key IN ($placeholders)
        ");
        $stmt->execute($keys);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $this->castValue($row['setting_value'], $row['setting_type']);
        }

        return $settings;
    }

    /**
     * Get all settings by category
     */
    public function getByCategory(string $category): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT setting_key, setting_value, setting_type, description
                FROM storefront_settings
                WHERE category = ?
                ORDER BY setting_key ASC
            ");
            $stmt->execute([$category]);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = [
                    'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                    'type' => $row['setting_type'],
                    'description' => $row['description']
                ];
            }

            return $settings;
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get store statistics for frontend display
     */
    public function getStoreStats(): array
    {
        $stats = [
            'certified_divers' => 0,
            'years_experience' => 0,
            'customer_rating' => 5.0,
            'dive_destinations' => 0
        ];

        try {
            // Certified Divers
            $res = Database::fetchOne("SELECT COUNT(*) as c FROM customer_certifications");
            $stats['certified_divers'] = ($res['c'] ?? 0);

            // Years Experience
            $foundingYear = $this->get('founding_year', 'general');
            if ($foundingYear) {
                $stats['years_experience'] = date('Y') - (int) $foundingYear;
            } else {
                $stats['years_experience'] = 10;
            }

            // Customer Rating
            $rating = $this->get('social_google_rating', 'social');
            $stats['customer_rating'] = $rating ? (float) $rating : 5.0;

            // Dive Trips
            $currentYear = date('Y');
            $res = Database::fetchOne("SELECT COUNT(*) as c FROM trip_schedules WHERE strftime('%Y', start_date) = ?", [$currentYear]);
            $stats['dive_destinations'] = ($res['c'] ?? 0);

        } catch (\Throwable $e) {
            // Keep defaults on error
        }

        return $stats;
    }

    /**
     * Get all settings
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT setting_key, setting_value, setting_type, category, description
                FROM storefront_settings
                ORDER BY category, setting_key
            ");
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $settings = [];
            foreach ($results as $row) {
                if (!isset($settings[$row['category']])) {
                    $settings[$row['category']] = [];
                }

                $settings[$row['category']][$row['setting_key']] = [
                    'value' => $this->castValue($row['setting_value'], $row['setting_type']),
                    'type' => $row['setting_type'],
                    'description' => $row['description']
                ];
            }

            return $settings;
        } catch (\PDOException $e) {
            // Table might not exist yet
            return [];
        }
    }

    /**
     * Set a setting value
     */
    public function set(string $key, $value, ?string $type = null): bool
    {
        if ($type === null) {
            $type = $this->detectType($value);
        }

        // Convert boolean/number to string for storage
        $storedValue = $this->prepareValue($value, $type);

        $stmt = $this->db->prepare("
            INSERT INTO storefront_settings (setting_key, setting_value, setting_type, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                setting_value = VALUES(setting_value),
                setting_type = VALUES(setting_type),
                updated_at = NOW()
        ");

        $result = $stmt->execute([$key, $storedValue, $type]);

        // Clear cache
        $this->cache->delete(self::CACHE_KEY_PREFIX . $key);

        return $result;
    }

    /**
     * Set multiple settings at once
     */
    public function setMany(array $settings): bool
    {
        $this->db->beginTransaction();

        try {
            foreach ($settings as $key => $value) {
                $type = $this->detectType($value);
                $this->set($key, $value, $type);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Update setting metadata
     */
    public function updateMeta(string $key, array $metadata): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = ['category', 'description', 'is_public'];

        foreach ($allowedFields as $field) {
            if (isset($metadata[$field])) {
                $fields[] = "$field = ?";
                $values[] = $metadata[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $key;
        $sql = "UPDATE storefront_settings SET " . implode(', ', $fields) . " WHERE setting_key = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Delete a setting
     */
    public function delete(string $key): bool
    {
        $stmt = $this->db->prepare("DELETE FROM storefront_settings WHERE setting_key = ?");
        $result = $stmt->execute([$key]);

        $this->cache->delete(self::CACHE_KEY_PREFIX . $key);

        return $result;
    }

    /**
     * Get all setting categories
     */
    public function getCategories(): array
    {
        return [
            'general' => [
                'name' => 'General Settings',
                'icon' => 'bi-gear',
                'description' => 'Store name, contact information, and general configuration'
            ],
            'seo' => [
                'name' => 'SEO Settings',
                'icon' => 'bi-search',
                'description' => 'Search engine optimization and meta tags'
            ],
            'features' => [
                'name' => 'Feature Toggles',
                'icon' => 'bi-toggle-on',
                'description' => 'Enable or disable store features'
            ],
            'checkout' => [
                'name' => 'Checkout Settings',
                'icon' => 'bi-cart-check',
                'description' => 'Checkout process and payment options'
            ],
            'shipping' => [
                'name' => 'Shipping Settings',
                'icon' => 'bi-box-seam',
                'description' => 'Shipping methods and rates'
            ],
            'social' => [
                'name' => 'Social Media',
                'icon' => 'bi-share',
                'description' => 'Social media links and integration'
            ],
            'integrations' => [
                'name' => 'Integrations',
                'icon' => 'bi-plugin',
                'description' => 'Third-party services and tracking codes'
            ]
        ];
    }

    /**
     * Get navigation menus
     */
    public function getNavigationMenu(string $location): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM navigation_menus
                WHERE menu_location = ? AND is_active = TRUE
                ORDER BY display_order ASC
            ");
            $stmt->execute([$location]);
            $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Build nested menu structure
            return $this->buildMenuTree($items);
        } catch (\PDOException $e) {
            // If navigation_menus table doesn't exist, return default menu
            if ($e->getCode() === '42S02') {
                return $this->getDefaultMenu($location);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get default navigation menu when table doesn't exist
     */
    private function getDefaultMenu(string $location): array
    {
        if ($location === 'header') {
            return [
                ['label' => 'Home', 'url' => '/', 'link_type' => 'custom', 'link_target' => '_self', 'icon_class' => null, 'children' => []],
                ['label' => 'Shop', 'url' => '/shop', 'link_type' => 'shop', 'link_target' => '_self', 'icon_class' => null, 'children' => []],
                ['label' => 'About', 'url' => '/about', 'link_type' => 'custom', 'link_target' => '_self', 'icon_class' => null, 'children' => []],
                ['label' => 'Contact', 'url' => '/contact', 'link_type' => 'custom', 'link_target' => '_self', 'icon_class' => null, 'children' => []],
            ];
        } elseif ($location === 'footer') {
            return [
                ['label' => 'Privacy Policy', 'url' => '/privacy', 'link_type' => 'custom', 'link_target' => '_self', 'icon_class' => null, 'children' => []],
                ['label' => 'Terms of Service', 'url' => '/terms', 'link_type' => 'custom', 'link_target' => '_self', 'icon_class' => null, 'children' => []],
            ];
        }
        return [];
    }

    /**
     * Build hierarchical menu tree
     */
    private function buildMenuTree(array $items, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildMenuTree($items, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $branch[] = $item;
            }
        }

        return $branch;
    }

    /**
     * Create navigation menu item
     */
    public function createMenuItem(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO navigation_menus (
                menu_location, display_order, parent_id, label, url,
                link_type, link_target, icon_class, is_active,
                requires_auth, visible_to
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['menu_location'],
            $data['display_order'] ?? 0,
            $data['parent_id'] ?? null,
            $data['label'],
            $data['url'] ?? null,
            $data['link_type'] ?? 'custom',
            $data['link_target'] ?? '_self',
            $data['icon_class'] ?? null,
            $data['is_active'] ?? true,
            $data['requires_auth'] ?? false,
            $data['visible_to'] ?? 'all'
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update navigation menu item
     */
    public function updateMenuItem(int $menuId, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'menu_location',
            'display_order',
            'parent_id',
            'label',
            'url',
            'link_type',
            'link_target',
            'icon_class',
            'is_active',
            'requires_auth',
            'visible_to'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $menuId;
        $sql = "UPDATE navigation_menus SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Delete navigation menu item
     */
    public function deleteMenuItem(int $menuId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM navigation_menus WHERE id = ? OR parent_id = ?");
        return $stmt->execute([$menuId, $menuId]);
    }

    /**
     * Get active promotional banners
     */
    public function getActiveBanners(?string $bannerType = null, ?string $page = null): array
    {
        try {
            $sql = "
                SELECT * FROM promotional_banners
                WHERE is_active = TRUE
                AND (start_date IS NULL OR start_date <= NOW())
                AND (end_date IS NULL OR end_date >= NOW())
            ";
            $params = [];

            if ($bannerType) {
                $sql .= " AND banner_type = ?";
                $params[] = $bannerType;
            }

            $sql .= " ORDER BY display_order ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $banners = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Filter by page if specified
            if ($page) {
                $banners = array_filter($banners, function ($banner) use ($page) {
                    if (empty($banner['show_on_pages'])) {
                        return true;
                    }
                    $pages = json_decode($banner['show_on_pages'], true);
                    return in_array($page, $pages) || in_array('all', $pages);
                });
            }

            return $banners;
        } catch (\Throwable $e) {
            // If table missing or query error, return empty list to prevent crash
            return [];
        }
    }

    /**
     * Increment banner view count
     */
    public function incrementBannerViews(int $bannerId): bool
    {
        $stmt = $this->db->prepare("UPDATE promotional_banners SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$bannerId]);
    }

    /**
     * Increment banner click count
     */
    public function incrementBannerClicks(int $bannerId): bool
    {
        $stmt = $this->db->prepare("UPDATE promotional_banners SET click_count = click_count + 1 WHERE id = ?");
        return $stmt->execute([$bannerId]);
    }

    /**
     * Get all banners (for admin)
     */
    public function getAllBanners(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM promotional_banners ORDER BY created_at DESC");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    /**
     * Get banner by ID
     */
    public function getBanner(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM promotional_banners WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create banner
     */
    public function createBanner(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO promotional_banners (
                banner_type, title, content, button_text, button_url,
                is_active, start_date, end_date, show_on_pages, display_order, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['banner_type'] ?? 'info',
            $data['title'] ?? null,
            $data['content'],
            $data['button_text'] ?? null,
            $data['button_url'] ?? null,
            $data['is_active'] ?? true,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            isset($data['show_on_pages']) ? json_encode($data['show_on_pages']) : null,
            $data['display_order'] ?? 0,
            $_SESSION['user_id'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update banner
     */
    public function updateBanner(int $id, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'banner_type',
            'title',
            'content',
            'button_text',
            'button_url',
            'is_active',
            'start_date',
            'end_date',
            'show_on_pages',
            'display_order'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                if ($field === 'show_on_pages' && is_array($data[$field])) {
                    $values[] = json_encode($data[$field]);
                } else {
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $id;
        $sql = "UPDATE promotional_banners SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Delete banner
     */
    public function deleteBanner(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM promotional_banners WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Cast setting value based on type
     */
    private function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Prepare value for storage
     */
    private function prepareValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? 'true' : 'false';
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Auto-detect value type
     */
    private function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_array($value)) {
            return 'json';
        } elseif (strlen($value) > 255) {
            return 'textarea';
        } else {
            return 'text';
        }
    }

    /**
     * Clear all cached settings
     */
    public function clearCache(): void
    {
        // This would clear all storefront settings from cache
        // Implementation depends on your cache system
    }
}
