DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` VARCHAR(20) DEFAULT 'string',
    `description` TEXT,
    `category` VARCHAR(50) DEFAULT 'general',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`) VALUES
('business_name', 'Nautilus Dive Shop', 'string', 'general'),
('business_email', 'info@nautilus.local', 'string', 'general'),
('business_hours', 'Mon-Fri: 9am - 6pm\nSat: 10am - 4pm\nSun: Closed', 'string', 'general'),
('brand_primary_color', '#0066cc', 'string', 'branding'),
('brand_secondary_color', '#003366', 'string', 'branding'),
('business_country', 'US', 'string', 'general'),
('currency', 'USD', 'string', 'regional'),
('timezone', 'America/New_York', 'string', 'regional');
