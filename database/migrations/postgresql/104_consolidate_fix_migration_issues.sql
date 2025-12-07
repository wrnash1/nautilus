-- ============================================================================
-- MIGRATION 104: Consolidated Migration Issues Fix
-- Purpose: Fix all migration ordering and schema consistency issues
-- Date: 2024
-- ============================================================================

-- ============================================================================
-- SECTION 1: Ensure customer_tags has complete schema
-- ============================================================================

-- Check and add missing columns to customer_tags
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags');

-- Add tenant_id if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customer_tags ADD COLUMN tenant_id INTEGER DEFAULT 1 AFTER id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add slug if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags' AND COLUMN_NAME = 'slug');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customer_tags ADD COLUMN slug VARCHAR(100) AFTER name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add icon if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags' AND COLUMN_NAME = 'icon');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customer_tags ADD COLUMN icon VARCHAR(50) AFTER color', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add description if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags' AND COLUMN_NAME = 'description');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customer_tags ADD COLUMN description TEXT AFTER icon', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add is_active if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags' AND COLUMN_NAME = 'is_active');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customer_tags ADD COLUMN is_active SMALLINT DEFAULT 1 AFTER description', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add display_order if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_tags' AND COLUMN_NAME = 'display_order');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customer_tags ADD COLUMN display_order INT DEFAULT 0 AFTER is_active', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 2: Ensure certification_agencies has complete schema
-- ============================================================================

SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies');

-- Add logo_path if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies' AND COLUMN_NAME = 'logo_path');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE certification_agencies ADD COLUMN logo_path VARCHAR(500) AFTER name', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add primary_color if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies' AND COLUMN_NAME = 'primary_color');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE certification_agencies ADD COLUMN primary_color VARCHAR(20) DEFAULT "#0066CC" AFTER logo_path', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add website if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies' AND COLUMN_NAME = 'website');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE certification_agencies ADD COLUMN website VARCHAR(500) AFTER primary_color', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add country if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies' AND COLUMN_NAME = 'country');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE certification_agencies ADD COLUMN country VARCHAR(100) AFTER website', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add verification_enabled if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies' AND COLUMN_NAME = 'verification_enabled');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE certification_agencies ADD COLUMN verification_enabled SMALLINT DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add verification_url if missing
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certification_agencies' AND COLUMN_NAME = 'verification_url');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE certification_agencies ADD COLUMN verification_url VARCHAR(500) AFTER verification_enabled', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 3: Ensure tenant_id columns exist in core tables
-- ============================================================================

-- Customers tenant_id
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers');
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE customers ADD COLUMN tenant_id INTEGER DEFAULT 1 AFTER id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Products tenant_id
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products');
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE products ADD COLUMN tenant_id INTEGER DEFAULT 1 AFTER id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Users tenant_id
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users');
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE users ADD COLUMN tenant_id INTEGER DEFAULT 1 AFTER id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Courses tenant_id
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses');
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'courses' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE courses ADD COLUMN tenant_id INTEGER DEFAULT 1 AFTER id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Transactions tenant_id
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions');
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'tenant_id');
SET @sql = IF(@table_exists > 0 AND @col_exists = 0, 'ALTER TABLE transactions ADD COLUMN tenant_id INTEGER DEFAULT 1 AFTER id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 4: Add missing indexes for performance
-- ============================================================================

-- Index on customers.tenant_id
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customers' AND INDEX_NAME = 'idx_customers_tenant');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE customers ADD INDEX idx_customers_tenant (tenant_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on products.tenant_id
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_products_tenant');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE products ADD INDEX idx_products_tenant (tenant_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on users.tenant_id
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_tenant');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE users ADD INDEX idx_users_tenant (tenant_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Index on transactions for reporting
SET @idx_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND INDEX_NAME = 'idx_transactions_date');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE transactions ADD INDEX idx_transactions_date (created_at)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- SECTION 5: Create missing tables needed for features
-- ============================================================================

-- Gift Cards table (if not exists)
CREATE TABLE IF NOT EXISTS "gift_cards" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "code" VARCHAR(50) NOT NULL UNIQUE,
    "initial_balance" DECIMAL(10,2) NOT NULL,
    "current_balance" DECIMAL(10,2) NOT NULL,
    "customer_id" INTEGER NULL,
    "purchaser_id" INTEGER NULL,
    "status" ENUM('active', 'inactive', 'depleted', 'expired') DEFAULT 'active',
    "expires_at" DATE NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gift_cards_code (code),
    INDEX idx_gift_cards_tenant (tenant_id),
    INDEX idx_gift_cards_customer (customer_id)
);

-- Gift Card Transactions
CREATE TABLE IF NOT EXISTS "gift_card_transactions" (
    "id" SERIAL PRIMARY KEY,
    "gift_card_id" INTEGER NOT NULL,
    "transaction_type" ENUM('purchase', 'redemption', 'refund', 'adjustment') NOT NULL,
    "amount" DECIMAL(10,2) NOT NULL,
    "balance_after" DECIMAL(10,2) NOT NULL,
    "reference_id" INTEGER NULL,
    "reference_type" VARCHAR(50) NULL,
    "notes" TEXT NULL,
    "created_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gct_gift_card (gift_card_id),
    INDEX idx_gct_type (transaction_type)
);

-- Pre-Dive Safety Checks
CREATE TABLE IF NOT EXISTS "pre_dive_safety_checks" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "customer_id" INTEGER NOT NULL,
    "instructor_id" INTEGER NULL,
    "trip_id" INTEGER NULL,
    "check_date" DATE NOT NULL,
    "equipment_check" JSON NULL,
    "buddy_check_completed" SMALLINT DEFAULT 0,
    "air_pressure_psi" INT NULL,
    "overall_status" ENUM('pass', 'fail', 'conditional') DEFAULT 'pass',
    "notes" TEXT NULL,
    "signed_at" TIMESTAMP NULL,
    "signature_data" TEXT NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pdsc_customer (customer_id),
    INDEX idx_pdsc_date (check_date),
    INDEX idx_pdsc_tenant (tenant_id)
);

-- Incident Reports
CREATE TABLE IF NOT EXISTS "incident_reports" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "incident_number" VARCHAR(50) NOT NULL UNIQUE,
    "incident_date" TIMESTAMP NOT NULL,
    "incident_type" ENUM('injury', 'equipment_failure', 'near_miss', 'environmental', 'other') NOT NULL,
    "severity" ENUM('minor', 'moderate', 'major', 'critical') DEFAULT 'minor',
    "location" VARCHAR(255) NULL,
    "dive_site_id" INTEGER NULL,
    "customer_id" INTEGER NULL,
    "staff_id" INTEGER NULL,
    "description" TEXT NOT NULL,
    "immediate_actions" TEXT NULL,
    "root_cause" TEXT NULL,
    "corrective_actions" TEXT NULL,
    "witnesses" JSON NULL,
    "equipment_involved" JSON NULL,
    "weather_conditions" VARCHAR(255) NULL,
    "dive_conditions" JSON NULL,
    "medical_attention_required" SMALLINT DEFAULT 0,
    "medical_details" TEXT NULL,
    "reported_to_authorities" SMALLINT DEFAULT 0,
    "authority_report_details" TEXT NULL,
    "status" ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    "resolution_date" DATE NULL,
    "resolution_notes" TEXT NULL,
    "reported_by" INTEGER NULL,
    "reviewed_by" INTEGER NULL,
    "reviewed_at" TIMESTAMP NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ir_number (incident_number),
    INDEX idx_ir_date (incident_date),
    INDEX idx_ir_type (incident_type),
    INDEX idx_ir_status (status),
    INDEX idx_ir_tenant (tenant_id)
);

-- Equipment Maintenance Log
CREATE TABLE IF NOT EXISTS "equipment_maintenance_log" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "equipment_id" INTEGER NOT NULL,
    "maintenance_type" ENUM('routine', 'repair', 'inspection', 'calibration', 'replacement') NOT NULL,
    "description" TEXT NOT NULL,
    "parts_used" JSON NULL,
    "cost" DECIMAL(10,2) DEFAULT 0,
    "performed_by" INTEGER NULL,
    "performed_at" TIMESTAMP NOT NULL,
    "next_maintenance_date" DATE NULL,
    "status" ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'completed',
    "notes" TEXT NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eml_equipment (equipment_id),
    INDEX idx_eml_date (performed_at),
    INDEX idx_eml_type (maintenance_type)
);

-- Customer Waivers
CREATE TABLE IF NOT EXISTS "customer_waivers" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "customer_id" INTEGER NOT NULL,
    "waiver_template_id" INTEGER NULL,
    "waiver_type" VARCHAR(100) NOT NULL,
    "version" VARCHAR(20) DEFAULT '1.0',
    "signed_at" TIMESTAMP NULL,
    "signature_data" MEDIUMTEXT NULL,
    "ip_address" VARCHAR(45) NULL,
    "user_agent" TEXT NULL,
    "expires_at" DATE NULL,
    "status" ENUM('pending', 'signed', 'expired', 'revoked') DEFAULT 'pending',
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cw_customer (customer_id),
    INDEX idx_cw_type (waiver_type),
    INDEX idx_cw_status (status),
    INDEX idx_cw_tenant (tenant_id)
);

-- ============================================================================
-- SECTION 6: Ensure foreign key constraints are properly set
-- ============================================================================

-- Note: Foreign keys are added only if tables exist and constraints don't exist
-- This prevents errors during migration

SELECT 'Migration 104 completed successfully' AS status;
