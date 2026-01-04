-- ================================================
-- Fix Base Tables That Failed During Initial Migration
-- This migration ensures all base tables from 001 are properly created
-- Run this after 000_multi_tenant_base.sql if foreign key errors occurred
-- ================================================

-- Disable foreign key checks temporarily to allow recreation
SET FOREIGN_KEY_CHECKS=0;

-- Ensure permissions table exists
CREATE TABLE IF NOT EXISTS "permissions" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(100) NOT NULL UNIQUE,
  "display_name" VARCHAR(150) NOT NULL,
  "module" VARCHAR(50) NOT NULL,
  "description" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX "idx_module" ("module")
);

-- Drop and recreate role_permissions to fix foreign keys
DROP TABLE IF EXISTS "role_permissions";
CREATE TABLE "role_permissions" (
  "role_id" INTEGER NOT NULL,
  "permission_id" INTEGER NOT NULL,
  PRIMARY KEY ("role_id", "permission_id"),
  FOREIGN KEY ("role_id") REFERENCES "roles"("id") ON DELETE CASCADE,
  FOREIGN KEY ("permission_id") REFERENCES "permissions"("id") ON DELETE CASCADE
);

-- Ensure users table exists with proper foreign keys
CREATE TABLE IF NOT EXISTS "users" (
  "id" SERIAL PRIMARY KEY,
  "tenant_id" INTEGER NOT NULL,
  "role_id" INTEGER NOT NULL,
  "email" VARCHAR(255) NOT NULL UNIQUE,
  "password_hash" VARCHAR(255) NOT NULL,
  "first_name" VARCHAR(100) NOT NULL,
  "last_name" VARCHAR(100) NOT NULL,
  "phone" VARCHAR(20),
  "google_id" VARCHAR(255) UNIQUE,
  "two_factor_secret" VARCHAR(255),
  "two_factor_enabled" BOOLEAN DEFAULT FALSE,
  "last_login_at" TIMESTAMP NULL,
  "password_changed_at" TIMESTAMP NULL,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
  FOREIGN KEY ("role_id") REFERENCES "roles"("id"),
  INDEX "idx_tenant_id" ("tenant_id"),
  INDEX "idx_email" ("email"),
  INDEX "idx_google_id" ("google_id"),
  INDEX "idx_is_active" ("is_active")
);

-- Ensure password_resets table exists
CREATE TABLE IF NOT EXISTS "password_resets" (
  "id" SERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "token" VARCHAR(255) NOT NULL UNIQUE,
  "expires_at" TIMESTAMP NOT NULL,
  "used_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  INDEX "idx_token" ("token"),
  INDEX "idx_expires_at" ("expires_at")
);

-- Ensure sessions table exists
CREATE TABLE IF NOT EXISTS "sessions" (
  "id" VARCHAR(255) PRIMARY KEY,
  "user_id" INT UNSIGNED,
  "ip_address" VARCHAR(45),
  "user_agent" TEXT,
  "payload" TEXT NOT NULL,
  "last_activity" INT NOT NULL,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_last_activity" ("last_activity")
);

-- Ensure audit_logs table exists
CREATE TABLE IF NOT EXISTS "audit_logs" (
  "id" BIGSERIAL PRIMARY KEY,
  "user_id" INT UNSIGNED,
  "action" VARCHAR(100) NOT NULL,
  "module" VARCHAR(50) NOT NULL,
  "entity_type" VARCHAR(50),
  "entity_id" INT UNSIGNED,
  "old_values" JSON,
  "new_values" JSON,
  "ip_address" VARCHAR(45),
  "user_agent" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_action" ("action"),
  INDEX "idx_module" ("module"),
  INDEX "idx_created_at" ("created_at")
);

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- ================================================
-- Base tables fix complete
-- ================================================
