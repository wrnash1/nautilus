SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `company_settings`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `company_settings`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `company_settings`;

-- Company Settings Table
-- Stores business information for each tenant

CREATE TABLE IF NOT EXISTS company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,

    -- Company Information
    company_name VARCHAR(255),
    legal_name VARCHAR(255),
    tax_id VARCHAR(100),

    -- Address
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(2) DEFAULT 'US',

    -- Contact Information
    phone VARCHAR(50),
    fax VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),

    -- Branding
    logo_url VARCHAR(255),

    -- Business Settings
    business_hours TEXT,
    timezone VARCHAR(100) DEFAULT 'America/New_York',
    currency VARCHAR(3) DEFAULT 'USD',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster lookups
CREATE INDEX idx_company_tenant ON company_settings(tenant_id);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;