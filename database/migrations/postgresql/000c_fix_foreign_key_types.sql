-- ==========================================
-- Fix Foreign Key Type Mismatches
-- Ensures all FK columns use INTEGER consistently
-- ==========================================

-- This migration ensures tables that will be referenced by later migrations
-- have the correct data types to avoid FK constraint errors

-- Ensure customers table has tenant_id column
ALTER TABLE "customers" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "customers" ADD INDEX IF NOT EXISTS idx_tenant_id ("tenant_id");

-- Ensure products table has tenant_id column
ALTER TABLE "products" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "products" ADD INDEX IF NOT EXISTS idx_tenant_id ("tenant_id");

-- Ensure courses table has tenant_id column
ALTER TABLE "courses" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "courses" ADD INDEX IF NOT EXISTS idx_tenant_id ("tenant_id");

-- Ensure users table has tenant_id column
ALTER TABLE "users" ADD COLUMN IF NOT EXISTS "tenant_id" INTEGER AFTER "id";
ALTER TABLE "users" ADD INDEX IF NOT EXISTS idx_tenant_id ("tenant_id");

-- Ensure products has is_featured column for homepage
ALTER TABLE "products" ADD COLUMN IF NOT EXISTS "is_featured" BOOLEAN DEFAULT FALSE AFTER "is_active";
ALTER TABLE "products" ADD INDEX IF NOT EXISTS idx_is_featured ("is_featured");
