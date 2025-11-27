-- Company Settings Table
-- Stores business information for each tenant

CREATE TABLE IF NOT EXISTS company_settings (
    id INT  PRIMARY KEY,
    tenant_id INT NOT NULL,

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
);

-- Create index for faster lookups
CREATE INDEX idx_company_tenant ON company_settings(tenant_id);
