-- ==========================================
-- Final Migration: Ensure All Critical Tables Exist
-- This runs LAST to fix any missing tables from failed migrations
-- ==========================================

-- Ensure customer_tags exists (referenced by multiple migrations)
CREATE TABLE IF NOT EXISTS "customer_tags" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,
    "name" VARCHAR(50) NOT NULL,
    "slug" VARCHAR(50),
    "color" VARCHAR(7) DEFAULT '#3b82f6',
    "icon" VARCHAR(50),
    "description" VARCHAR(255),
    "is_active" BOOLEAN DEFAULT TRUE,
    "display_order" INT DEFAULT 0,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "created_by" INTEGER );

-- Ensure customer_tag_assignments exists
CREATE TABLE IF NOT EXISTS "customer_tag_assignments" (
    "customer_id" INTEGER NOT NULL,
    "tag_id" INTEGER NOT NULL,
    "assigned_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "assigned_by" INT UNSIGNED,
    "notes" VARCHAR(255),
    PRIMARY KEY ("customer_id", "tag_id")
);

-- Ensure system_settings exists
CREATE TABLE IF NOT EXISTS "system_settings" (
    "id" SERIAL PRIMARY KEY,
    "setting_key" VARCHAR(100) NOT NULL,
    "setting_value" TEXT,
    "setting_type" VARCHAR(50) DEFAULT 'string',
    "is_encrypted" BOOLEAN DEFAULT FALSE,
    "description" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_key (setting_key)
);

-- Ensure settings exists
CREATE TABLE IF NOT EXISTS "settings" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,
    "setting_key" VARCHAR(255) NOT NULL,
    "setting_value" TEXT,
    "setting_group" VARCHAR(100) DEFAULT 'general',
    "is_sensitive" BOOLEAN DEFAULT FALSE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_key (tenant_id, setting_key)
);

-- Ensure cash_drawers exists
CREATE TABLE IF NOT EXISTS "cash_drawers" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL,
    "location" VARCHAR(100),
    "drawer_number" VARCHAR(20),
    "current_balance" DECIMAL(10,2) DEFAULT 0.00,
    "starting_float" DECIMAL(10,2) DEFAULT 200.00,
    "is_active" BOOLEAN DEFAULT TRUE,
    "requires_count_in" BOOLEAN DEFAULT TRUE,
    "requires_count_out" BOOLEAN DEFAULT TRUE,
    "notes" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "created_by" INT UNSIGNED,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ensure cash_drawer_sessions exists
CREATE TABLE IF NOT EXISTS "cash_drawer_sessions" (
    "id" SERIAL PRIMARY KEY,
    "session_number" VARCHAR(50),
    "drawer_id" INT UNSIGNED,
    "user_id" INT UNSIGNED,
    "opened_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "starting_balance" DECIMAL(10,2),
    "closed_at" TIMESTAMP NULL,
    "ending_balance" DECIMAL(10,2) NULL,
    "expected_balance" DECIMAL(10,2) NULL,
    "total_sales" DECIMAL(10,2) DEFAULT 0.00,
    "total_refunds" DECIMAL(10,2) DEFAULT 0.00,
    "difference" DECIMAL(10,2) NULL,
    "difference_reason" TEXT,
    "status" ENUM('open', 'closed', 'balanced', 'over', 'short') DEFAULT 'open',
    "closed_by" INT UNSIGNED,
    "approved_by" INT UNSIGNED,
    "approved_at" TIMESTAMP NULL
);

-- Ensure company_settings exists
CREATE TABLE IF NOT EXISTS "company_settings" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INT UNSIGNED,
    "setting_key" VARCHAR(100) NOT NULL,
    "setting_value" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_key (tenant_id, setting_key)
);

-- Ensure certification_agencies has all columns
CREATE TABLE IF NOT EXISTS "certification_agencies" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL,
    "abbreviation" VARCHAR(20) NOT NULL,
    "logo_path" VARCHAR(255),
    "primary_color" VARCHAR(7) DEFAULT '#0066CC',
    "website" VARCHAR(255),
    "country" VARCHAR(100),
    "api_endpoint" VARCHAR(255),
    "api_key_encrypted" VARCHAR(255),
    "verification_enabled" BOOLEAN DEFAULT FALSE,
    "verification_url" VARCHAR(255),
    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_name (name)
);

-- Add missing columns to existing tables
ALTER TABLE "customers" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "products" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "products" ADD COLUMN IF NOT EXISTS "is_featured" BOOLEAN DEFAULT FALSE AFTER "is_active";
ALTER TABLE "courses" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "users" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";

-- Insert default customer tags if table is empty
INSERT IGNORE INTO "customer_tags" (name, slug, color, icon, description, display_order, is_active) VALUES
('VIP', 'vip', '#f39c12', 'bi-star-fill', 'VIP Customer', 1, TRUE),
('Wholesale', 'wholesale', '#3498db', 'bi-briefcase-fill', 'Wholesale Customer', 2, TRUE),
('Instructor', 'instructor', '#2ecc71', 'bi-mortarboard-fill', 'Certified Instructor', 3, TRUE),
('Regular', 'regular', '#95a5a6', 'bi-person-check-fill', 'Regular Customer', 4, TRUE);

-- Insert default cash drawer if table is empty
INSERT IGNORE INTO "cash_drawers" (id, name, location, drawer_number, starting_float, is_active, created_at)
VALUES (1, 'Main Register', 'Front Counter', '001', 200.00, TRUE, NOW());
