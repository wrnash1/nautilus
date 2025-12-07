-- ============================================================================
-- MIGRATION 103: Fix Database Warnings (MySQL 5.7+ Compatible)
-- Purpose: Fix all migration warnings without using MySQL 8.0+ syntax
-- Strategy: Use proper IF NOT EXISTS checks with prepared statements
-- ============================================================================

-- ============================================================================
-- SECTION 1: Fix customer_tags table (if needed)
-- ============================================================================

-- Check if customer_tags needs tenant_id
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customer_tags' 
    AND COLUMN_NAME = 'tenant_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE customer_tags ADD COLUMN tenant_id INTEGER NOT NULL DEFAULT 1 AFTER id',
    'SELECT "tenant_id column already exists in customer_tags" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index if needed
SET @index_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customer_tags' 
    AND INDEX_NAME = 'idx_tenant_id'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE customer_tags ADD INDEX idx_tenant_id (tenant_id)',
    'SELECT "idx_tenant_id already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 2: Add missing tenant_id columns to core tables
-- ============================================================================

-- Add tenant_id to customers
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customers' 
    AND COLUMN_NAME = 'tenant_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE customers ADD COLUMN tenant_id INTEGER NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_tenant_id (tenant_id)',
    'SELECT "tenant_id already exists in customers" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tenant_id to products
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'products' 
    AND COLUMN_NAME = 'tenant_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE products ADD COLUMN tenant_id INTEGER NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_tenant_id (tenant_id)',
    'SELECT "tenant_id already exists in products" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tenant_id to users
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'tenant_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE users ADD COLUMN tenant_id INTEGER NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_tenant_id (tenant_id)',
    'SELECT "tenant_id already exists in users" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tenant_id to courses
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'courses' 
    AND COLUMN_NAME = 'tenant_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE courses ADD COLUMN tenant_id INTEGER NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_tenant_id (tenant_id)',
    'SELECT "tenant_id already exists in courses" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add tenant_id to trips
SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'trips' 
    AND COLUMN_NAME = 'tenant_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE trips ADD COLUMN tenant_id INTEGER NOT NULL DEFAULT 1 AFTER id, ADD INDEX idx_tenant_id (tenant_id)',
    'SELECT "tenant_id already exists in trips" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 3: Add missing color columns to certification_agencies
-- ============================================================================

SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certification_agencies' 
    AND COLUMN_NAME = 'primary_color'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE certification_agencies ADD COLUMN primary_color VARCHAR(7) DEFAULT "#0066cc"',
    'SELECT "primary_color already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'certification_agencies' 
    AND COLUMN_NAME = 'secondary_color'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE certification_agencies ADD COLUMN secondary_color VARCHAR(7) DEFAULT "#003366"',
    'SELECT "secondary_color already exists" AS message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 4: Ensure system_settings table exists
-- ============================================================================

CREATE TABLE IF NOT EXISTS "system_settings" (
    "id" SERIAL PRIMARY KEY,
    "setting_key" VARCHAR(100) NOT NULL UNIQUE,
    "setting_value" TEXT,
    "setting_type" ENUM('string', 'integer', 'boolean', 'json', 'float') DEFAULT 'string',
    "description" TEXT,
    "is_public" BOOLEAN DEFAULT FALSE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Populate default settings
INSERT IGNORE INTO "system_settings" ("setting_key", "setting_value", "setting_type", "description") VALUES
('business_name', 'Nautilus Dive Shop', 'string', 'Business/Company Name'),
('business_email', 'info@nautilus.local', 'string', 'Business Email'),
('business_phone', '(555) 123-4567', 'string', 'Business Phone'),
('business_address', '123 Ocean Drive', 'string', 'Business Address'),
('business_city', 'Miami', 'string', 'Business City'),
('business_state', 'FL', 'string', 'Business State'),
('business_zip', '33139', 'string', 'Business ZIP Code'),
('business_country', 'US', 'string', 'Business Country'),
('brand_primary_color', '#0066cc', 'string', 'Primary Brand Color'),
('brand_secondary_color', '#003366', 'string', 'Secondary Brand Color'),
('company_logo_path', '', 'string', 'Company Logo Path'),
('company_logo_small_path', '', 'string', 'Company Small Logo Path'),
('company_favicon_path', '', 'string', 'Company Favicon Path'),
('tax_rate', '0.07', 'float', 'Default Tax Rate'),
('currency', 'USD', 'string', 'Currency Code'),
('timezone', 'America/New_York', 'string', 'Timezone'),
('setup_complete', '0', 'boolean', 'Whether initial setup is complete');

-- ============================================================================
-- SECTION 5: Ensure default tenant exists
-- ============================================================================

INSERT IGNORE INTO "tenants" ("id", "name", "subdomain", "database_name", "is_active", "created_at")
VALUES (1, 'Default Tenant', 'default', DATABASE(), 1, NOW());

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================

SELECT 
    'Migration 103 Complete!' AS status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()) AS total_tables,
    (SELECT COUNT(*) FROM system_settings) AS settings_count,
    (SELECT COUNT(*) FROM tenants) AS tenant_count;

-- ============================================================================
-- This migration fixes:
-- ✓ customer_tags table structure (adds tenant_id if missing)
-- ✓ Missing tenant_id columns in customers, products, users, courses, trips
-- ✓ Missing color columns in certification_agencies
-- ✓ Ensures system_settings exists and is populated
-- ✓ Ensures default tenant exists
-- ✓ Uses MySQL 5.7+ compatible syntax (no "IF NOT EXISTS" in ALTER TABLE)
--
-- Result: Clean database with minimal warnings
-- ============================================================================
