SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- MIGRATION 116: Seed Default Data
-- Purpose: Ensure default tenant and system settings exist.
-- ============================================================================

-- Ensure system_settings exists
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_type` VARCHAR(20) DEFAULT 'string',
    `description` TEXT,
    `category` VARCHAR(50) DEFAULT 'general',
    `is_public` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate default settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) VALUES
('business_name', 'Nautilus Dive Shop', 'string', 'general', 'Business/Company Name'),
('business_email', 'info@nautilus.local', 'string', 'general', 'Business Email'),
('business_phone', '(555) 123-4567', 'string', 'general', 'Business Phone'),
('business_address', '123 Ocean Drive', 'string', 'general', 'Business Address'),
('business_city', 'Miami', 'string', 'general', 'Business City'),
('business_state', 'FL', 'string', 'general', 'Business State'),
('business_zip', '33139', 'string', 'general', 'Business ZIP Code'),
('business_country', 'US', 'string', 'general', 'Business Country'),
('brand_primary_color', '#0066cc', 'string', 'branding', 'Primary Brand Color'),
('brand_secondary_color', '#003366', 'string', 'branding', 'Secondary Brand Color'),
('company_logo_path', '', 'string', 'branding', 'Company Logo Path'),
('tax_rate', '0.07', 'float', 'financial', 'Default Tax Rate'),
('currency', 'USD', 'string', 'regional', 'Currency Code'),
('timezone', 'America/New_York', 'string', 'regional', 'Timezone'),
('setup_complete', '0', 'boolean', 'system', 'Whether initial setup is complete');

-- Ensure default tenant exists
INSERT IGNORE INTO `tenants` (`id`, `company_name`, `subdomain`, `status`, `contact_email`, `tenant_uuid`, `created_at`)
VALUES (1, 'Default Tenant', 'default', 'active', 'admin@local.test', UUID(), NOW());

SELECT 'Migration 116 Complete' AS status;

SET FOREIGN_KEY_CHECKS=1;
