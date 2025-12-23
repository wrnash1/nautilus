-- Enterprise SaaS Features Migration
-- Adds tables for SSO, multi-currency, subscriptions, white-label, and monitoring

-- SSO Configuration
CREATE TABLE IF NOT EXISTS sso_configurations (
    id INTEGER  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'saml, azure, google, okta, onelogin',
    enabled BOOLEAN DEFAULT TRUE,
    configuration TEXT COMMENT 'JSON configuration',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sso_tenant (tenant_id),
    INDEX idx_sso_provider (provider)
);

-- SAML Requests
CREATE TABLE IF NOT EXISTS saml_requests (
    id INTEGER  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    request_id VARCHAR(255) NOT NULL UNIQUE,
    issued_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, completed, expired',
    completed_at TIMESTAMP NULL,
    INDEX idx_saml_tenant (tenant_id),
    INDEX idx_saml_request (request_id),
    INDEX idx_saml_status (status)
);

-- OAuth States
CREATE TABLE IF NOT EXISTS oauth_states (
    id INTEGER  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    state VARCHAR(255) NOT NULL UNIQUE,
    nonce VARCHAR(255) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_oauth_tenant (tenant_id),
    INDEX idx_oauth_state (state)
);

-- Exchange Rates
CREATE TABLE IF NOT EXISTS exchange_rates (
    id INTEGER  PRIMARY KEY,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(18, 8) NOT NULL,
    source VARCHAR(50) DEFAULT 'manual' COMMENT 'manual, api',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_currency_pair (from_currency, to_currency),
    INDEX idx_updated (updated_at)
);

-- Tax Nexus
CREATE TABLE IF NOT EXISTS tax_nexus (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    country VARCHAR(2) NOT NULL,
    state VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,
    registration_number VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nexus_tenant (tenant_id),
    INDEX idx_nexus_location (country, state)
);

-- Tax Rates
CREATE TABLE IF NOT EXISTS tax_rates (
    id INT  PRIMARY KEY,
    country VARCHAR(2) NOT NULL,
    state VARCHAR(50) NULL,
    zip_code VARCHAR(10) NULL,
    rate DECIMAL(8, 4) NOT NULL,
    tax_type VARCHAR(20) DEFAULT 'sales' COMMENT 'sales, vat, gst, pst',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tax_location (country, state, zip_code)
);

-- Subscription Plans
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT  PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    billing_period VARCHAR(20) DEFAULT 'month' COMMENT 'month, year',
    trial_days INT DEFAULT 0,
    features TEXT NULL COMMENT 'JSON array of features',
    is_active BOOLEAN DEFAULT TRUE,
    max_users INT NULL,
    max_products INT NULL,
    max_storage_mb INT NULL,
    api_rate_limit INT DEFAULT 1000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_plan_active (is_active)
);

-- Tenant Subscriptions
CREATE TABLE IF NOT EXISTS tenant_subscriptions (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    plan_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'active' COMMENT 'active, past_due, canceled, incomplete',
    current_period_start DATE NOT NULL,
    current_period_end DATE NOT NULL,
    trial_end DATE NULL,
    quantity INT DEFAULT 1,
    cancel_at_period_end BOOLEAN DEFAULT FALSE,
    canceled_at TIMESTAMP NULL,
    ends_at DATE NULL,
    last_billing_date DATE NULL,
    failed_payment_count INT DEFAULT 0,
    last_payment_error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subscription_tenant (tenant_id),
    INDEX idx_subscription_status (status),
    INDEX idx_subscription_period_end (current_period_end)
);

-- Subscription Invoices
CREATE TABLE IF NOT EXISTS subscription_invoices (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    subscription_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status VARCHAR(20) DEFAULT 'pending' COMMENT 'pending, paid, failed, refunded',
    paid_at TIMESTAMP NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    invoice_pdf_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_invoice_tenant (tenant_id),
    INDEX idx_invoice_subscription (subscription_id),
    INDEX idx_invoice_status (status)
);

-- Payment Methods
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    type VARCHAR(20) NOT NULL COMMENT 'card, bank_account, paypal',
    last_four VARCHAR(4) NULL,
    exp_month INT NULL,
    exp_year INT NULL,
    gateway_token VARCHAR(255) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_payment_tenant (tenant_id)
);

-- Usage Meters
CREATE TABLE IF NOT EXISTS usage_meters (
    id INT  PRIMARY KEY,
    subscription_id INT NOT NULL,
    tenant_id INT NOT NULL,
    meter_type VARCHAR(50) NOT NULL COMMENT 'api_calls, storage, users, transactions',
    quantity DECIMAL(18, 6) NOT NULL,
    metadata TEXT NULL COMMENT 'JSON metadata',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    billed BOOLEAN DEFAULT FALSE,
    billed_at TIMESTAMP NULL,
    INDEX idx_usage_subscription (subscription_id),
    INDEX idx_usage_tenant (tenant_id),
    INDEX idx_usage_meter (meter_type),
    INDEX idx_usage_billed (billed)
);

-- Subscription Plan Meters
CREATE TABLE IF NOT EXISTS subscription_plan_meters (
    id INT  PRIMARY KEY,
    plan_id INT NOT NULL,
    meter_type VARCHAR(50) NOT NULL,
    price_per_unit DECIMAL(10, 6) NOT NULL,
    included_quantity DECIMAL(18, 6) DEFAULT 0,
    INDEX idx_plan_meter (plan_id, meter_type)
);

-- Tenant Branding
CREATE TABLE IF NOT EXISTS tenant_branding (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL UNIQUE,
    company_name VARCHAR(255) NULL,
    logo_url VARCHAR(255) NULL,
    favicon_url VARCHAR(255) NULL,
    primary_color VARCHAR(7) DEFAULT '#1976d2',
    secondary_color VARCHAR(7) DEFAULT '#dc004e',
    accent_color VARCHAR(7) DEFAULT '#f50057',
    theme_mode VARCHAR(10) DEFAULT 'light' COMMENT 'light, dark',
    custom_css TEXT NULL,
    custom_domain VARCHAR(255) NULL UNIQUE,
    domain_verified BOOLEAN DEFAULT FALSE,
    domain_verification_token VARCHAR(255) NULL,
    domain_verified_at TIMESTAMP NULL,
    theme_settings TEXT NULL COMMENT 'JSON theme configuration',
    email_settings TEXT NULL COMMENT 'JSON email settings',
    custom_terminology TEXT NULL COMMENT 'JSON custom terms',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_branding_tenant (tenant_id),
    INDEX idx_branding_domain (custom_domain)
);

-- Email Templates
CREATE TABLE IF NOT EXISTS email_templates (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tenant_template (tenant_id, template_name),
    INDEX idx_template_tenant (tenant_id)
);

-- Onboarding Steps
CREATE TABLE IF NOT EXISTS onboarding_steps (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    step VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    "order" INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_onboarding_tenant (tenant_id),
    INDEX idx_onboarding_completed (completed)
);

-- WebSocket Queue (for polling fallback)
CREATE TABLE IF NOT EXISTS websocket_queue (
    id BIGINT  PRIMARY KEY,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed BOOLEAN DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    INDEX idx_ws_processed (processed),
    INDEX idx_ws_created (created_at)
);

-- Chat Messages
CREATE TABLE IF NOT EXISTS chat_messages (
    id BIGINT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    from_user_id BIGINT NOT NULL,
    to_user_id BIGINT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chat_tenant (tenant_id),
    INDEX idx_chat_to_user (to_user_id),
    INDEX idx_chat_from_user (from_user_id),
    INDEX idx_chat_read (is_read)
);

-- API Usage Tracking
CREATE TABLE IF NOT EXISTS api_usage (
    id BIGINT  PRIMARY KEY,
    tenant_id INT NOT NULL,
    endpoint VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    response_time INT NULL COMMENT 'milliseconds',
    status_code INT NULL,
    INDEX idx_usage_tenant (tenant_id),
    INDEX idx_usage_timestamp (timestamp),
    INDEX idx_usage_endpoint (endpoint)
);

-- Alter existing tenants table
ALTER TABLE tenants
ADD COLUMN IF NOT EXISTS api_rate_limit INT DEFAULT 1000 AFTER plan_id,
ADD COLUMN IF NOT EXISTS api_burst_limit INT DEFAULT 50 AFTER api_rate_limit;

-- Alter users table for SSO and presence
ALTER TABLE users
ADD COLUMN IF NOT EXISTS sso_provider VARCHAR(50) NULL AFTER password,
ADD COLUMN IF NOT EXISTS sso_provider_id VARCHAR(255) NULL AFTER sso_provider,
ADD COLUMN IF NOT EXISTS online_status VARCHAR(20) DEFAULT 'offline' AFTER sso_provider_id,
ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL AFTER online_status;

-- Alter products table for multi-currency
ALTER TABLE products
ADD COLUMN IF NOT EXISTS currency VARCHAR(3) DEFAULT 'USD' AFTER price,
ADD COLUMN IF NOT EXISTS is_taxable BOOLEAN DEFAULT TRUE AFTER currency,
ADD COLUMN IF NOT EXISTS tax_code VARCHAR(50) NULL AFTER is_taxable;

-- Insert default subscription plans
INSERT IGNORE INTO subscription_plans (name, description, amount, billing_period, trial_days, features, max_users, max_products, api_rate_limit) VALUES
('Starter', 'Perfect for small dive shops', 29.99, 'month', 14, '["POS System", "Basic Inventory", "Customer Management", "5 Users"]', 5, 500, 500),
('Professional', 'For growing dive operations', 79.99, 'month', 14, '["Everything in Starter", "Rental Management", "Course Scheduling", "20 Users", "Advanced Reporting"]', 20, 2000, 1000),
('Enterprise', 'Full-featured for large operations', 199.99, 'month', 30, '["Everything in Professional", "Multi-location", "API Access", "Unlimited Users", "White-label", "Priority Support"]', NULL, NULL, 5000),
('Starter Annual', 'Annual Starter plan (2 months free)', 299.99, 'year', 14, '["POS System", "Basic Inventory", "Customer Management", "5 Users"]', 5, 500, 500),
('Professional Annual', 'Annual Professional plan (2 months free)', 799.99, 'year', 14, '["Everything in Starter", "Rental Management", "Course Scheduling", "20 Users", "Advanced Reporting"]', 20, 2000, 1000),
('Enterprise Annual', 'Annual Enterprise plan (2 months free)', 1999.99, 'year', 30, '["Everything in Professional", "Multi-location", "API Access", "Unlimited Users", "White-label", "Priority Support"]', NULL, NULL, 5000);

-- Insert default tax rates (US states)
INSERT IGNORE INTO tax_rates (country, state, rate, tax_type) VALUES
('US', 'CA', 7.25, 'sales'),
('US', 'FL', 6.00, 'sales'),
('US', 'TX', 6.25, 'sales'),
('US', 'NY', 4.00, 'sales'),
('US', 'IL', 6.25, 'sales');

-- Insert default exchange rates (will be updated by API)
INSERT IGNORE INTO exchange_rates (from_currency, to_currency, rate, source) VALUES
('USD', 'EUR', 0.92, 'manual'),
('EUR', 'USD', 1.09, 'manual'),
('USD', 'GBP', 0.79, 'manual'),
('GBP', 'USD', 1.27, 'manual'),
('USD', 'CAD', 1.36, 'manual'),
('CAD', 'USD', 0.74, 'manual');
