SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `tenant_onboarding`;
DROP TABLE IF EXISTS `tenant_activity_log`;
DROP TABLE IF EXISTS `tenant_billing`;
DROP TABLE IF EXISTS `tenant_usage`;
DROP TABLE IF EXISTS `tenant_api_keys`;
DROP TABLE IF EXISTS `tenant_settings`;
DROP TABLE IF EXISTS `tenant_invitations`;
DROP TABLE IF EXISTS `tenant_users`;
DROP TABLE IF EXISTS `subscription_plans`;
DROP TABLE IF EXISTS `tenants`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `tenant_onboarding`;
DROP TABLE IF EXISTS `tenant_activity_log`;
DROP TABLE IF EXISTS `tenant_billing`;
DROP TABLE IF EXISTS `tenant_usage`;
DROP TABLE IF EXISTS `tenant_api_keys`;
DROP TABLE IF EXISTS `tenant_settings`;
DROP TABLE IF EXISTS `tenant_invitations`;
DROP TABLE IF EXISTS `tenant_users`;
DROP TABLE IF EXISTS `subscription_plans`;
DROP TABLE IF EXISTS `tenants`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `tenant_onboarding`;
DROP TABLE IF EXISTS `tenant_activity_log`;
DROP TABLE IF EXISTS `tenant_billing`;
DROP TABLE IF EXISTS `tenant_usage`;
DROP TABLE IF EXISTS `tenant_api_keys`;
DROP TABLE IF EXISTS `tenant_settings`;
DROP TABLE IF EXISTS `tenant_invitations`;
DROP TABLE IF EXISTS `tenant_users`;
DROP TABLE IF EXISTS `subscription_plans`;
DROP TABLE IF EXISTS `tenants`;

-- Multi-Tenant Architecture
-- Creates infrastructure for supporting multiple companies/tenants
-- Each tenant has isolated data while sharing the same codebase

-- Tenants/Companies table
CREATE TABLE IF NOT EXISTS tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_uuid VARCHAR(36) UNIQUE NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100) UNIQUE,
    custom_domain VARCHAR(255) UNIQUE,
    status ENUM('active', 'suspended', 'trial', 'cancelled') DEFAULT 'active',
    trial_ends_at TIMESTAMP NULL,
    subscription_status ENUM('active', 'past_due', 'cancelled', 'trialing') DEFAULT 'trialing',

    -- Subscription details
    plan_id INT,
    billing_cycle ENUM('monthly', 'yearly', 'lifetime') DEFAULT 'monthly',
    monthly_price DECIMAL(10, 2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'USD',

    -- Contact information
    contact_name VARCHAR(255),
    contact_email VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(50),

    -- Company details
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),

    -- Settings
    timezone VARCHAR(50) DEFAULT 'UTC',
    locale VARCHAR(10) DEFAULT 'en_US',
    date_format VARCHAR(20) DEFAULT 'Y-m-d',
    time_format VARCHAR(20) DEFAULT 'H:i:s',

    -- Limits and quotas
    max_users INT DEFAULT 10,
    max_storage_mb INT DEFAULT 1000,
    max_products INT DEFAULT 500,
    max_transactions_per_month INT DEFAULT 1000,

    -- White-label settings
    logo_url VARCHAR(500),
    favicon_url VARCHAR(500),
    primary_color VARCHAR(7) DEFAULT '#0066cc',
    secondary_color VARCHAR(7) DEFAULT '#004999',

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_tenant_uuid (tenant_uuid),
    INDEX idx_subdomain (subdomain),
    INDEX idx_custom_domain (custom_domain),
    INDEX idx_status (status),
    INDEX idx_subscription_status (subscription_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure plan_id exists if table was created by 000_CORE_SCHEMA
ALTER TABLE tenants ADD COLUMN IF NOT EXISTS plan_id BIGINT UNSIGNED;

-- Subscription plans
CREATE TABLE IF NOT EXISTS subscription_plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    plan_code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,

    -- Pricing
    monthly_price DECIMAL(10, 2) NOT NULL,
    yearly_price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',

    -- Features and limits
    max_users INT DEFAULT 10,
    max_storage_mb INT DEFAULT 1000,
    max_products INT DEFAULT 500,
    max_transactions_per_month INT DEFAULT 1000,

    -- Feature flags
    features JSON, -- {"analytics": true, "reports": true, "api_access": true}

    -- Display
    is_popular BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_plan_code (plan_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT IGNORE INTO subscription_plans (plan_name, plan_code, description, monthly_price, yearly_price, max_users, max_storage_mb, max_products, max_transactions_per_month, features, is_popular) VALUES
('Starter', 'starter', 'Perfect for small dive shops just getting started', 29.00, 290.00, 5, 500, 250, 500, '{"analytics": true, "reports": false, "api_access": false, "advanced_dashboard": false}', FALSE),
('Professional', 'professional', 'For growing dive shops with advanced needs', 79.00, 790.00, 15, 2000, 1000, 2500, '{"analytics": true, "reports": true, "api_access": true, "advanced_dashboard": true, "white_label": false}', TRUE),
('Enterprise', 'enterprise', 'Full-featured solution for large operations', 199.00, 1990.00, 50, 10000, 5000, 10000, '{"analytics": true, "reports": true, "api_access": true, "advanced_dashboard": true, "white_label": true, "priority_support": true}', FALSE);

-- Tenant users relationship (multi-tenant user mapping)
CREATE TABLE IF NOT EXISTS tenant_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    is_owner BOOLEAN DEFAULT FALSE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_user (tenant_id, user_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant invitations
CREATE TABLE IF NOT EXISTS tenant_invitations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    invited_by BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'accepted', 'expired', 'cancelled') DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant settings (key-value storage for tenant-specific settings)
CREATE TABLE IF NOT EXISTS tenant_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'string', -- string, number, boolean, json
    is_public BOOLEAN DEFAULT FALSE, -- can be accessed by frontend
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_setting (tenant_id, setting_key),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant API keys (for programmatic access)
CREATE TABLE IF NOT EXISTS tenant_api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    api_secret VARCHAR(128), -- Hashed
    permissions JSON, -- {"read": true, "write": false, "delete": false}
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_api_key (api_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usage tracking (for billing and quotas)
CREATE TABLE IF NOT EXISTS tenant_usage (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    usage_date DATE NOT NULL,

    -- User metrics
    active_users INT DEFAULT 0,

    -- Transaction metrics
    transactions_count INT DEFAULT 0,
    transactions_value DECIMAL(12, 2) DEFAULT 0.00,

    -- Storage metrics
    storage_used_mb INT DEFAULT 0,

    -- Product metrics
    products_count INT DEFAULT 0,

    -- Customer metrics
    customers_count INT DEFAULT 0,

    -- API metrics
    api_calls INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_date (tenant_id, usage_date),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_usage_date (usage_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant billing history
CREATE TABLE IF NOT EXISTS tenant_billing (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    billing_period_start DATE NOT NULL,
    billing_period_end DATE NOT NULL,

    -- Amounts
    subtotal DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) DEFAULT 0.00,
    discount DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',

    -- Payment
    status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50), -- stripe, paypal, manual
    payment_reference VARCHAR(255), -- external transaction ID
    paid_at TIMESTAMP NULL,

    -- Invoice details
    line_items JSON, -- Array of items with description, quantity, price
    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_status (status),
    INDEX idx_billing_period (billing_period_start, billing_period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant activity log (audit trail)
CREATE TABLE IF NOT EXISTS tenant_activity_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED,
    activity_type VARCHAR(50) NOT NULL, -- login, logout, create, update, delete, etc.
    entity_type VARCHAR(50), -- product, customer, transaction, etc.
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add tenant_id to existing tables for data isolation
-- customers table
ALTER TABLE customers ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON customers(tenant_id);

-- products table
ALTER TABLE products ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON products(tenant_id);

-- product_categories table
ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON product_categories(tenant_id);

-- pos_transactions table
ALTER TABLE pos_transactions ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON pos_transactions(tenant_id);

-- courses table
ALTER TABLE courses ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON courses(tenant_id);

-- equipment table
ALTER TABLE equipment ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON equipment(tenant_id);

-- equipment_rentals table
ALTER TABLE equipment_rentals ADD COLUMN IF NOT EXISTS tenant_id BIGINT UNSIGNED NULL;
CREATE INDEX IF NOT EXISTS idx_tenant_id ON equipment_rentals(tenant_id);

-- Tenant onboarding tracking
CREATE TABLE IF NOT EXISTS tenant_onboarding (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED NOT NULL,

    -- Onboarding steps
    step_company_info BOOLEAN DEFAULT FALSE,
    step_users_invited BOOLEAN DEFAULT FALSE,
    step_products_added BOOLEAN DEFAULT FALSE,
    step_payment_setup BOOLEAN DEFAULT FALSE,
    step_customization BOOLEAN DEFAULT FALSE,
    step_completed BOOLEAN DEFAULT FALSE,

    -- Progress
    completion_percentage INT DEFAULT 0,
    completed_at TIMESTAMP NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;