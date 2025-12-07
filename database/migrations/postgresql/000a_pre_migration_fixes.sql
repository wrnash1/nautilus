-- ==========================================
-- Pre-Migration Fixes for MariaDB/MySQL Compatibility
-- This migration runs FIRST to set up prerequisites
-- ==========================================

-- Ensure customer_tags table exists before any migration tries to reference it
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
    "created_by" INT UNSIGNED,
    INDEX idx_active (is_active),
    INDEX idx_slug (slug),
    INDEX idx_tenant (tenant_id)
);

-- Ensure system_settings table exists for SSO and other system settings
CREATE TABLE IF NOT EXISTS "system_settings" (
    "id" SERIAL PRIMARY KEY,
    "setting_key" VARCHAR(100) NOT NULL UNIQUE,
    "setting_value" TEXT,
    "setting_type" VARCHAR(50) DEFAULT 'string',
    "is_encrypted" BOOLEAN DEFAULT FALSE,
    "description" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
);

-- Ensure certification_agencies has all needed columns
-- First create if not exists, then add columns
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

-- Ensure settings table exists for the Settings class
CREATE TABLE IF NOT EXISTS "settings" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,
    "setting_key" VARCHAR(255) NOT NULL,
    "setting_value" TEXT,
    "setting_group" VARCHAR(100) DEFAULT 'general',
    "is_sensitive" BOOLEAN DEFAULT FALSE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_key (tenant_id, setting_key),
    INDEX idx_group (setting_group)
);

-- Ensure company_settings table exists
CREATE TABLE IF NOT EXISTS "company_settings" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INT UNSIGNED,
    "setting_key" VARCHAR(100) NOT NULL,
    "setting_value" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tenant_key (tenant_id, setting_key),
    INDEX idx_tenant (tenant_id)
);
