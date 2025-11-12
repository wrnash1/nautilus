<?php

namespace App\Services\Storefront;

use App\Core\Database;

class ThemeEngine
{
    private $db;
    private $activeTheme = null;
    private $cachedSettings = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get the active theme configuration
     */
    public function getActiveTheme(): ?array
    {
        if ($this->activeTheme !== null) {
            return $this->activeTheme;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM theme_config
                WHERE is_active = TRUE
                LIMIT 1
            ");
            $stmt->execute();
            $this->activeTheme = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // If theme_config table doesn't exist, return default theme data
            if ($e->getCode() === '42S02') {
                $this->activeTheme = $this->getDefaultTheme();
            } else {
                throw $e;
            }
        }

        return $this->activeTheme;
    }

    /**
     * Get default theme configuration when table doesn't exist
     */
    private function getDefaultTheme(): array
    {
        return [
            'id' => 0,
            'theme_name' => 'Default',
            'is_active' => true,
            'is_default' => true,
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'accent_color' => '#0dcaf0',
            'success_color' => '#198754',
            'danger_color' => '#dc3545',
            'warning_color' => '#ffc107',
            'info_color' => '#0dcaf0',
            'dark_color' => '#212529',
            'light_color' => '#f8f9fa',
            'body_bg_color' => '#ffffff',
            'header_bg_color' => '#ffffff',
            'footer_bg_color' => '#212529',
            'hero_bg_color' => '#01012e',
            'text_color' => '#212529',
            'heading_color' => '#000000',
            'link_color' => '#0d6efd',
            'link_hover_color' => '#0a58ca',
            'font_family_primary' => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'font_family_heading' => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
            'font_size_base' => '16px',
            'font_size_heading_1' => '2.5rem',
            'font_size_heading_2' => '2rem',
            'font_size_heading_3' => '1.75rem',
            'line_height' => '1.5',
            'container_max_width' => '1200px',
            'border_radius' => '0.375rem',
            'spacing_unit' => '1rem',
            'header_style' => 'solid',
            'nav_position' => 'top',
            'show_search_bar' => true,
            'show_cart_icon' => true,
            'show_account_icon' => true,
            'hero_style' => 'image',
            'hero_height' => '500px',
            'hero_overlay_opacity' => '0.5',
            'show_hero_cta' => true,
            'hero_cta_text' => 'Shop Now',
            'hero_cta_url' => '/shop',
            'products_per_row' => 4,
            'product_card_style' => 'classic',
            'show_product_ratings' => true,
            'show_product_quick_view' => true,
            'show_add_to_cart_button' => true,
            'show_wishlist_button' => true,
            'footer_style' => 'detailed',
            'show_newsletter_signup' => true,
            'show_social_links' => true,
            'show_payment_icons' => true,
            'custom_css' => '',
            'custom_js' => '',
            'custom_head_html' => '',
        ];
    }

    /**
     * Get theme by ID
     */
    public function getThemeById(int $themeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM theme_config WHERE id = ?");
        $stmt->execute([$themeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all themes
     */
    public function getAllThemes(): array
    {
        $stmt = $this->db->query("SELECT * FROM theme_config ORDER BY is_default DESC, theme_name ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new theme
     */
    public function createTheme(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO theme_config (
                theme_name, primary_color, secondary_color, accent_color,
                body_bg_color, header_bg_color, footer_bg_color,
                font_family_primary, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['theme_name'] ?? 'New Theme',
            $data['primary_color'] ?? '#0d6efd',
            $data['secondary_color'] ?? '#6c757d',
            $data['accent_color'] ?? '#0dcaf0',
            $data['body_bg_color'] ?? '#ffffff',
            $data['header_bg_color'] ?? '#ffffff',
            $data['footer_bg_color'] ?? '#212529',
            $data['font_family_primary'] ?? 'system-ui, sans-serif',
            $data['created_by'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update theme configuration
     */
    public function updateTheme(int $themeId, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'theme_name', 'primary_color', 'secondary_color', 'accent_color',
            'success_color', 'danger_color', 'warning_color', 'info_color',
            'dark_color', 'light_color', 'body_bg_color', 'header_bg_color',
            'footer_bg_color', 'hero_bg_color', 'text_color', 'heading_color',
            'link_color', 'link_hover_color', 'font_family_primary',
            'font_family_heading', 'font_size_base', 'font_size_heading_1',
            'font_size_heading_2', 'font_size_heading_3', 'line_height',
            'container_max_width', 'border_radius', 'spacing_unit',
            'header_style', 'nav_position', 'show_search_bar', 'show_cart_icon',
            'show_account_icon', 'hero_style', 'hero_height', 'hero_overlay_opacity',
            'show_hero_cta', 'hero_cta_text', 'hero_cta_url', 'products_per_row',
            'product_card_style', 'show_product_ratings', 'show_product_quick_view',
            'show_add_to_cart_button', 'show_wishlist_button', 'footer_style',
            'show_newsletter_signup', 'show_social_links', 'show_payment_icons',
            'custom_css', 'custom_js', 'custom_head_html'
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

        $values[] = $themeId;
        $sql = "UPDATE theme_config SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Set theme as active
     */
    public function setActiveTheme(int $themeId): bool
    {
        $this->db->beginTransaction();

        try {
            // Deactivate all themes
            $stmt = $this->db->prepare("UPDATE theme_config SET is_active = FALSE");
            $stmt->execute();

            // Activate selected theme
            $stmt = $this->db->prepare("UPDATE theme_config SET is_active = TRUE WHERE id = ?");
            $stmt->execute([$themeId]);

            $this->db->commit();
            $this->activeTheme = null; // Clear cache
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete a theme
     */
    public function deleteTheme(int $themeId): bool
    {
        // Prevent deleting active or default theme
        $theme = $this->getThemeById($themeId);
        if ($theme && ($theme['is_active'] || $theme['is_default'])) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM theme_config WHERE id = ?");
        return $stmt->execute([$themeId]);
    }

    /**
     * Generate CSS variables from theme config
     */
    public function generateThemeCSS(?array $theme = null): string
    {
        if ($theme === null) {
            $theme = $this->getActiveTheme();
        }

        if (!$theme) {
            return '';
        }

        $css = ":root {\n";
        $css .= "  /* Colors */\n";
        $css .= "  --primary-color: {$theme['primary_color']};\n";
        $css .= "  --secondary-color: {$theme['secondary_color']};\n";
        $css .= "  --accent-color: {$theme['accent_color']};\n";
        $css .= "  --success-color: {$theme['success_color']};\n";
        $css .= "  --danger-color: {$theme['danger_color']};\n";
        $css .= "  --warning-color: {$theme['warning_color']};\n";
        $css .= "  --info-color: {$theme['info_color']};\n";
        $css .= "  --dark-color: {$theme['dark_color']};\n";
        $css .= "  --light-color: {$theme['light_color']};\n";
        $css .= "\n  /* Backgrounds */\n";
        $css .= "  --body-bg: {$theme['body_bg_color']};\n";
        $css .= "  --header-bg: {$theme['header_bg_color']};\n";
        $css .= "  --footer-bg: {$theme['footer_bg_color']};\n";
        $css .= "  --hero-bg: {$theme['hero_bg_color']};\n";
        $css .= "\n  /* Text */\n";
        $css .= "  --text-color: {$theme['text_color']};\n";
        $css .= "  --heading-color: {$theme['heading_color']};\n";
        $css .= "  --link-color: {$theme['link_color']};\n";
        $css .= "  --link-hover-color: {$theme['link_hover_color']};\n";
        $css .= "\n  /* Typography */\n";
        $css .= "  --font-primary: {$theme['font_family_primary']};\n";
        $css .= "  --font-heading: {$theme['font_family_heading']};\n";
        $css .= "  --font-size-base: {$theme['font_size_base']};\n";
        $css .= "  --font-size-h1: {$theme['font_size_heading_1']};\n";
        $css .= "  --font-size-h2: {$theme['font_size_heading_2']};\n";
        $css .= "  --font-size-h3: {$theme['font_size_heading_3']};\n";
        $css .= "  --line-height: {$theme['line_height']};\n";
        $css .= "\n  /* Layout */\n";
        $css .= "  --container-max-width: {$theme['container_max_width']};\n";
        $css .= "  --border-radius: {$theme['border_radius']};\n";
        $css .= "  --spacing-unit: {$theme['spacing_unit']};\n";
        $css .= "  --hero-height: {$theme['hero_height']};\n";
        $css .= "}\n\n";

        // Add custom CSS if present
        if (!empty($theme['custom_css'])) {
            $css .= "\n/* Custom CSS */\n";
            $css .= $theme['custom_css'] . "\n";
        }

        return $css;
    }

    /**
     * Get theme assets for a specific theme
     */
    public function getThemeAssets(int $themeId, ?string $assetType = null): array
    {
        $sql = "SELECT * FROM theme_assets WHERE theme_id = ?";
        $params = [$themeId];

        if ($assetType) {
            $sql .= " AND asset_type = ?";
            $params[] = $assetType;
        }

        $sql .= " ORDER BY is_primary DESC, asset_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get primary asset of a specific type for active theme
     */
    public function getPrimaryAsset(string $assetType): ?array
    {
        $theme = $this->getActiveTheme();
        if (!$theme) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM theme_assets
                WHERE theme_id = ? AND asset_type = ? AND is_primary = TRUE
                LIMIT 1
            ");
            $stmt->execute([$theme['id'], $assetType]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            // If theme_assets table doesn't exist, return null
            if ($e->getCode() === '42S02') {
                return null;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Upload theme asset
     */
    public function uploadAsset(int $themeId, string $assetType, string $filePath, array $metadata = []): int
    {
        // If setting as primary, unset other primary assets of same type
        if (isset($metadata['is_primary']) && $metadata['is_primary']) {
            $stmt = $this->db->prepare("
                UPDATE theme_assets
                SET is_primary = FALSE
                WHERE theme_id = ? AND asset_type = ?
            ");
            $stmt->execute([$themeId, $assetType]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO theme_assets (
                theme_id, asset_type, asset_name, file_path,
                file_size, mime_type, alt_text, is_primary, uploaded_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $themeId,
            $assetType,
            $metadata['asset_name'] ?? basename($filePath),
            $filePath,
            $metadata['file_size'] ?? null,
            $metadata['mime_type'] ?? null,
            $metadata['alt_text'] ?? null,
            $metadata['is_primary'] ?? false,
            $metadata['uploaded_by'] ?? null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete theme asset
     */
    public function deleteAsset(int $assetId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM theme_assets WHERE id = ?");
        return $stmt->execute([$assetId]);
    }

    /**
     * Get homepage sections for active theme
     */
    public function getHomepageSections(?int $themeId = null): array
    {
        if ($themeId === null) {
            $theme = $this->getActiveTheme();
            $themeId = $theme['id'] ?? null;
        }

        if (!$themeId) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM homepage_sections
                WHERE theme_id = ? AND is_active = TRUE
                ORDER BY display_order ASC
            ");
            $stmt->execute([$themeId]);
            $sections = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Decode JSON config
            foreach ($sections as &$section) {
                if (!empty($section['config'])) {
                    $section['config'] = json_decode($section['config'], true);
                }
            }

            return $sections;
        } catch (\PDOException $e) {
            // If homepage_sections table doesn't exist, return empty array
            if ($e->getCode() === '42S02') {
                return [];
            } else {
                throw $e;
            }
        }
    }

    /**
     * Update homepage section
     */
    public function updateHomepageSection(int $sectionId, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'section_title', 'section_subtitle', 'display_order', 'is_active',
            'config', 'background_color', 'text_color', 'padding_top',
            'padding_bottom', 'background_image'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                // Encode config as JSON if it's an array
                $values[] = ($field === 'config' && is_array($data[$field]))
                    ? json_encode($data[$field])
                    : $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $sectionId;
        $sql = "UPDATE homepage_sections SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute($values);
    }

    /**
     * Create homepage section
     */
    public function createHomepageSection(int $themeId, array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO homepage_sections (
                theme_id, section_type, section_title, section_subtitle,
                display_order, is_active, config
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $themeId,
            $data['section_type'],
            $data['section_title'] ?? null,
            $data['section_subtitle'] ?? null,
            $data['display_order'] ?? 0,
            $data['is_active'] ?? true,
            isset($data['config']) ? json_encode($data['config']) : null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete homepage section
     */
    public function deleteHomepageSection(int $sectionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM homepage_sections WHERE id = ?");
        return $stmt->execute([$sectionId]);
    }

    /**
     * Reorder homepage sections
     */
    public function reorderSections(array $sectionOrder): bool
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("UPDATE homepage_sections SET display_order = ? WHERE id = ?");

            foreach ($sectionOrder as $order => $sectionId) {
                $stmt->execute([$order, $sectionId]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
