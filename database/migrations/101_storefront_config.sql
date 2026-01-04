SET FOREIGN_KEY_CHECKS=0;

-- Migration: 101 Storefront Config

-- Storefront Configuration, Announcements, and Navigation
-- Migration: 101_storefront_config.sql

-- Storefront Settings (Key-Value Store)
CREATE TABLE IF NOT EXISTS `storefront_settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT NULL,
    `setting_type` ENUM('text', 'textarea', 'boolean', 'number', 'json', 'image') DEFAULT 'text',
    `category` VARCHAR(50) DEFAULT 'general',
    `description` VARCHAR(255) NULL,
    `is_public` BOOLEAN DEFAULT FALSE,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promotional Banners (Announcements)
CREATE TABLE IF NOT EXISTS `promotional_banners` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `banner_type` ENUM('info', 'warning', 'success', 'danger', 'promotion', 'top_bar', 'hero', 'sidebar', 'popup', 'footer') DEFAULT 'info',
    `title` VARCHAR(255) NULL,
    `content` TEXT NOT NULL,
    `button_text` VARCHAR(100) NULL,
    `button_url` VARCHAR(255) NULL,
    
    -- Display Rules
    `is_active` BOOLEAN DEFAULT TRUE,
    `start_date` DATETIME NULL,
    `end_date` DATETIME NULL,
    `show_on_pages` JSON NULL COMMENT '["all"] or ["/","/shop"]',
    `display_order` INT DEFAULT 0,
    
    -- Stats
    `view_count` INT DEFAULT 0,
    `click_count` INT DEFAULT 0,
    `created_by` BIGINT UNSIGNED NULL,
    
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Navigation Menus
CREATE TABLE IF NOT EXISTS `navigation_menus` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `menu_location` ENUM('header', 'footer', 'sidebar', 'mobile') NOT NULL,
    `parent_id` BIGINT UNSIGNED NULL,
    `label` VARCHAR(100) NOT NULL,
    `url` VARCHAR(255) NULL,
    
    -- Link Properties
    `link_type` ENUM('custom', 'page', 'category', 'product', 'route') DEFAULT 'custom',
    `link_target` ENUM('_self', '_blank') DEFAULT '_self',
    `icon_class` VARCHAR(50) NULL,
    
    -- Visibility
    `is_active` BOOLEAN DEFAULT TRUE,
    `requires_auth` BOOLEAN DEFAULT FALSE,
    `visible_to` ENUM('all', 'guest', 'customer', 'staff') DEFAULT 'all',
    `display_order` INT DEFAULT 0,
    
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`parent_id`) REFERENCES `navigation_menus`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Settings
INSERT IGNORE INTO `storefront_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`) VALUES
('business_name', 'Nautilus Dive Shop', 'text', 'general', 'Name of the business displayed on the site', TRUE),
('social_facebook', 'https://facebook.com', 'text', 'social', 'Facebook Page URL', TRUE),
('social_instagram', 'https://instagram.com', 'text', 'social', 'Instagram Profile URL', TRUE),
('contact_email', 'info@nautilusdive.com', 'text', 'general', 'Public contact email', TRUE),
('contact_phone', '(555) 123-4567', 'text', 'general', 'Public contact phone', TRUE);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;