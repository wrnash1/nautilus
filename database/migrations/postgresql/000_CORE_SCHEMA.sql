-- ============================================================================
-- NAUTILUS DIVE SHOP - CORE SCHEMA
-- Essential tables for application to function
-- This replaces the complex 100+ migration files for initial setup
-- ============================================================================

-- Database creation is handled by the installer or setup script

-- ============================================================================
-- MULTI-TENANT & AUTHENTICATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS "tenants" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(255) NOT NULL,
    "subdomain" VARCHAR(100) UNIQUE,
    "custom_domain" VARCHAR(255),
    "status" ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    "settings" JSON,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_subdomain" ("subdomain"),
    INDEX "idx_status" ("status")
);

-- Insert default tenant
INSERT INTO "tenants" ("id", "name", "subdomain", "status") VALUES (1, 'Default Tenant', 'default', 'active');

CREATE TABLE IF NOT EXISTS "roles" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL UNIQUE,
    "description" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO "roles" ("name", "description") VALUES
('Super Admin', 'Full system access'),
('Admin', 'Store administrator'),
('Manager', 'Store manager'),
('Staff', 'Store staff'),
('Instructor', 'Diving instructor');

CREATE TABLE IF NOT EXISTS "permissions" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(100) NOT NULL UNIQUE,
    "description" TEXT,
    "category" VARCHAR(50),
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert core permissions
INSERT INTO "permissions" ("name", "description", "category") VALUES
('dashboard.view', 'View dashboard', 'Dashboard'),
('customers.view', 'View customers', 'Customers'),
('customers.create', 'Create customers', 'Customers'),
('customers.edit', 'Edit customers', 'Customers'),
('customers.delete', 'Delete customers', 'Customers'),
('products.view', 'View products', 'Products'),
('products.create', 'Create products', 'Products'),
('products.edit', 'Edit products', 'Products'),
('products.delete', 'Delete products', 'Products'),
('pos.view', 'View POS', 'POS'),
('pos.access', 'Access POS', 'POS'),
('reports.view', 'View reports', 'Reports'),
('settings.view', 'View settings', 'Settings'),
('settings.edit', 'Edit settings', 'Settings'),
('admin.users', 'Manage users', 'Admin'),
('admin.roles', 'Manage roles', 'Admin');

CREATE TABLE IF NOT EXISTS "role_permissions" (
    "role_id" INTEGER NOT NULL,
    "permission_id" INTEGER NOT NULL,
    "permission_code" VARCHAR(100),
    PRIMARY KEY ("role_id", "permission_id"),
    FOREIGN KEY ("role_id") REFERENCES "roles"("id") ON DELETE CASCADE,
    FOREIGN KEY ("permission_id") REFERENCES "permissions"("id") ON DELETE CASCADE
);

-- Grant all permissions to Super Admin
INSERT INTO "role_permissions" ("role_id", "permission_id")
SELECT 1, id FROM "permissions";

CREATE TABLE IF NOT EXISTS "users" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "username" VARCHAR(100) UNIQUE,
    "email" VARCHAR(255) NOT NULL UNIQUE,
    "password_hash" VARCHAR(255) NOT NULL,
    "first_name" VARCHAR(100),
    "last_name" VARCHAR(100),
    "is_active" SMALLINT DEFAULT 1,
    "two_factor_enabled" SMALLINT DEFAULT 0,
    "two_factor_secret" VARCHAR(255),
    "last_login_at" TIMESTAMP NULL,
    "password_changed_at" TIMESTAMP NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_email" ("email"),
    INDEX "idx_is_active" ("is_active"),
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS "user_roles" (
    "id" SERIAL PRIMARY KEY,
    "user_id" INTEGER NOT NULL,
    "role_id" INTEGER NOT NULL,
    "assigned_by" INT UNSIGNED,
    "expires_at" TIMESTAMP NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY "unique_user_role" ("user_id", "role_id"),
    FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
    FOREIGN KEY ("role_id") REFERENCES "roles"("id") ON DELETE CASCADE
);

-- Create default admin user (password: admin123)
INSERT INTO "users" ("tenant_id", "username", "email", "password_hash", "first_name", "last_name") VALUES
(1, 'admin', 'admin@nautilus.local', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5Bf/6H/T3CQLC', 'Admin', 'User');

-- Assign Super Admin role to default user
INSERT INTO "user_roles" ("user_id", "role_id") VALUES (1, 1);

CREATE TABLE IF NOT EXISTS "sessions" (
    "id" VARCHAR(255) PRIMARY KEY,
    "user_id" INT UNSIGNED,
    "payload" TEXT,
    "last_activity" INT,
    "ip_address" VARCHAR(45),
    "user_agent" TEXT,
    INDEX "idx_user_id" ("user_id"),
    INDEX "idx_last_activity" ("last_activity")
);

CREATE TABLE IF NOT EXISTS "password_resets" (
    "id" SERIAL PRIMARY KEY,
    "email" VARCHAR(255) NOT NULL,
    "token" VARCHAR(255) NOT NULL,
    "expires_at" TIMESTAMP NOT NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_email" ("email"),
    INDEX "idx_token" ("token"),
    INDEX "idx_expires_at" ("expires_at")
);

-- ============================================================================
-- CUSTOMERS & CRM
-- ============================================================================

CREATE TABLE IF NOT EXISTS "customers" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "customer_type" ENUM('B2C', 'B2B') DEFAULT 'B2C',
    "first_name" VARCHAR(100),
    "last_name" VARCHAR(100),
    "email" VARCHAR(255),
    "phone" VARCHAR(50),
    "mobile" VARCHAR(50),
    "company_name" VARCHAR(255),
    "birth_date" DATE,
    "emergency_contact_name" VARCHAR(200),
    "emergency_contact_phone" VARCHAR(50),
    "tax_exempt" SMALLINT DEFAULT 0,
    "tax_exempt_number" VARCHAR(100),
    "credit_limit" DECIMAL(10,2) DEFAULT 0,
    "credit_terms" VARCHAR(50),
    "notes" TEXT,
    "photo_path" VARCHAR(500),
    "password" VARCHAR(255) COMMENT 'Hashed password for customer portal login',
    "is_active" SMALLINT DEFAULT 1,
    "status" ENUM('active', 'inactive') DEFAULT 'active',
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_email" ("email"),
    INDEX "idx_last_name" ("last_name"),
    INDEX "idx_status" ("status"),
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS "customer_addresses" (
    "id" SERIAL PRIMARY KEY,
    "customer_id" INTEGER NOT NULL,
    "address_type" ENUM('billing', 'shipping', 'both') DEFAULT 'both',
    "address_line1" VARCHAR(255),
    "address_line2" VARCHAR(255),
    "city" VARCHAR(100),
    "state" VARCHAR(50),
    "postal_code" VARCHAR(20),
    "country" VARCHAR(100) DEFAULT 'US',
    "is_default" SMALLINT DEFAULT 0,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_customer_id" ("customer_id"),
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "customer_tags" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "name" VARCHAR(100) NOT NULL,
    "slug" VARCHAR(100),
    "color" VARCHAR(20),
    "icon" VARCHAR(50),
    "description" TEXT,
    "is_active" SMALLINT DEFAULT 1,
    "display_order" INT DEFAULT 0,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY "unique_name_tenant" ("name", "tenant_id")
);

CREATE TABLE IF NOT EXISTS "customer_tag_assignments" (
    "customer_id" INTEGER NOT NULL,
    "tag_id" INTEGER NOT NULL,
    "assigned_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("customer_id", "tag_id"),
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tag_id") REFERENCES "customer_tags"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "customer_notes" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "user_id" INT UNSIGNED,
  "note_type" ENUM('general', 'important', 'interaction', 'issue') DEFAULT 'general',
  "content" TEXT NOT NULL,
  "is_pinned" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_created_at" ("created_at")
);

CREATE TABLE IF NOT EXISTS "customer_documents" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "document_type" ENUM('c-card', 'id', 'insurance', 'medical', 'waiver', 'other') NOT NULL,
  "file_path" VARCHAR(255) NOT NULL,
  "file_name" VARCHAR(255) NOT NULL,
  "file_size" INT UNSIGNED,
  "mime_type" VARCHAR(100),
  "ocr_data" JSON,
  "expiry_date" DATE,
  "uploaded_by" INT UNSIGNED,
  "uploaded_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("uploaded_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_document_type" ("document_type")
);

CREATE TABLE IF NOT EXISTS "customer_communications" (
  "id" BIGSERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "communication_type" ENUM('email', 'sms', 'phone', 'in-person') NOT NULL,
  "direction" ENUM('inbound', 'outbound') NOT NULL,
  "subject" VARCHAR(255),
  "content" TEXT,
  "status" ENUM('sent', 'delivered', 'failed', 'bounced') DEFAULT 'sent',
  "user_id" INT UNSIGNED,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_created_at" ("created_at")
);

CREATE TABLE IF NOT EXISTS "b2b_contacts" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "first_name" VARCHAR(100) NOT NULL,
  "last_name" VARCHAR(100) NOT NULL,
  "email" VARCHAR(255) NOT NULL,
  "phone" VARCHAR(20),
  "title" VARCHAR(100),
  "is_primary" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id")
);

-- ============================================================================
-- PRODUCTS & INVENTORY
-- ============================================================================

CREATE TABLE IF NOT EXISTS "categories" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "parent_id" INT UNSIGNED,
    "name" VARCHAR(255) NOT NULL,
    "slug" VARCHAR(255),
    "description" TEXT,
    "image_path" VARCHAR(500),
    "display_order" INT DEFAULT 0,
    "is_active" SMALLINT DEFAULT 1,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_parent_id" ("parent_id"),
    INDEX "idx_slug" ("slug"),
    FOREIGN KEY ("parent_id") REFERENCES "categories"("id") ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS "vendors" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "name" VARCHAR(255) NOT NULL,
    "code" VARCHAR(50),
    "email" VARCHAR(255),
    "phone" VARCHAR(50),
    "website" VARCHAR(500),
    "contact_person" VARCHAR(200),
    "notes" TEXT,
    "is_active" SMALLINT DEFAULT 1,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_code" ("code")
);

CREATE TABLE IF NOT EXISTS "products" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "category_id" INT UNSIGNED,
    "vendor_id" INT UNSIGNED,
    "sku" VARCHAR(100),
    "name" VARCHAR(255) NOT NULL,
    "description" TEXT,
    "cost" DECIMAL(10,2) DEFAULT 0,
    "price" DECIMAL(10,2) NOT NULL,
    "quantity_in_stock" INT DEFAULT 0,
    "reorder_level" INT DEFAULT 0,
    "barcode" VARCHAR(100),
    "image_path" VARCHAR(500),
    "is_active" SMALLINT DEFAULT 1,
    "is_taxable" SMALLINT DEFAULT 1,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_category_id" ("category_id"),
    INDEX "idx_vendor_id" ("vendor_id"),
    INDEX "idx_sku" ("sku"),
    INDEX "idx_barcode" ("barcode"),
    FOREIGN KEY ("category_id") REFERENCES "categories"("id") ON DELETE SET NULL,
    FOREIGN KEY ("vendor_id") REFERENCES "vendors"("id") ON DELETE SET NULL
);

-- ============================================================================
-- POS & TRANSACTIONS
-- ============================================================================

CREATE TABLE IF NOT EXISTS "transactions" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "customer_id" INT UNSIGNED,
    "user_id" INT UNSIGNED,
    "transaction_number" VARCHAR(50) UNIQUE,
    "subtotal" DECIMAL(10,2) DEFAULT 0,
    "tax_amount" DECIMAL(10,2) DEFAULT 0,
    "discount_amount" DECIMAL(10,2) DEFAULT 0,
    "total_amount" DECIMAL(10,2) NOT NULL,
    "amount_paid" DECIMAL(10,2) DEFAULT 0,
    "change_given" DECIMAL(10,2) DEFAULT 0,
    "status" ENUM('pending', 'completed', 'voided', 'refunded') DEFAULT 'completed',
    "payment_method" VARCHAR(50),
    "notes" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_customer_id" ("customer_id"),
    INDEX "idx_user_id" ("user_id"),
    INDEX "idx_transaction_number" ("transaction_number"),
    INDEX "idx_created_at" ("created_at"),
    INDEX "idx_status" ("status"),
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS "transaction_items" (
    "id" BIGSERIAL PRIMARY KEY,
    "transaction_id" BIGINT NOT NULL,
    "product_id" INT UNSIGNED,
    "description" VARCHAR(500),
    "quantity" INT NOT NULL DEFAULT 1,
    "unit_price" DECIMAL(10,2) NOT NULL,
    "discount" DECIMAL(10,2) DEFAULT 0,
    "tax_rate" DECIMAL(5,2) DEFAULT 0,
    "line_total" DECIMAL(10,2) NOT NULL,
    INDEX "idx_transaction_id" ("transaction_id"),
    INDEX "idx_product_id" ("product_id"),
    FOREIGN KEY ("transaction_id") REFERENCES "transactions"("id") ON DELETE CASCADE,
    FOREIGN KEY ("product_id") REFERENCES "products"("id") ON DELETE SET NULL
);

-- ============================================================================
-- CERTIFICATIONS
-- ============================================================================

CREATE TABLE IF NOT EXISTS "certification_agencies" (
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(200) NOT NULL,
    "code" VARCHAR(50) UNIQUE,
    "logo_path" VARCHAR(500),
    "primary_color" VARCHAR(20) DEFAULT '#0066CC',
    "website" VARCHAR(500),
    "country" VARCHAR(100),
    "is_active" SMALLINT DEFAULT 1,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert major certification agencies
INSERT INTO "certification_agencies" ("name", "code", "primary_color") VALUES
('PADI', 'PADI', '#0066CC'),
('SSI', 'SSI', '#FF6600'),
('NAUI', 'NAUI', '#004D99'),
('SDI', 'SDI', '#CC0000'),
('TDI', 'TDI', '#990000');

CREATE TABLE IF NOT EXISTS "customer_certifications" (
    "id" SERIAL PRIMARY KEY,
    "customer_id" INTEGER NOT NULL,
    "certification_agency_id" INT UNSIGNED,
    "certification_level" VARCHAR(200),
    "certification_number" VARCHAR(100),
    "issue_date" DATE,
    "expiration_date" DATE,
    "instructor_name" VARCHAR(200),
    "notes" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_customer_id" ("customer_id"),
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("certification_agency_id") REFERENCES "certification_agencies"("id") ON DELETE SET NULL
);

-- ============================================================================
-- SETTINGS & CONFIGURATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS "settings" (
    "id" SERIAL PRIMARY KEY,
    "category" VARCHAR(100) NOT NULL,
    "setting_key" VARCHAR(150) NOT NULL,
    "setting_value" TEXT,
    "type" ENUM('string', 'number', 'boolean', 'json', 'encrypted') DEFAULT 'string',
    "description" TEXT,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY "unique_category_key" ("category", "setting_key")
);

-- Insert default settings
INSERT INTO "settings" ("category", "setting_key", "setting_value", "type", "description") VALUES
('general', 'business_name', 'Nautilus Dive Shop', 'string', 'Business name'),
('general', 'business_email', 'info@nautilus.local', 'string', 'Business email'),
('general', 'business_phone', '(555) 123-4567', 'string', 'Business phone'),
('general', 'timezone', 'America/New_York', 'string', 'Default timezone'),
('general', 'currency', 'USD', 'string', 'Default currency'),
('tax', 'default_tax_rate', '7.5', 'number', 'Default sales tax rate');

CREATE TABLE IF NOT EXISTS "company_settings" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "business_name" VARCHAR(255),
    "business_email" VARCHAR(255),
    "business_phone" VARCHAR(50),
    "address_line1" VARCHAR(255),
    "address_line2" VARCHAR(255),
    "city" VARCHAR(100),
    "state" VARCHAR(50),
    "postal_code" VARCHAR(20),
    "country" VARCHAR(100) DEFAULT 'US',
    "website" VARCHAR(500),
    "logo_path" VARCHAR(500),
    "favicon_path" VARCHAR(500),
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default company settings
INSERT INTO "company_settings" ("tenant_id", "business_name") VALUES (1, 'Nautilus Dive Shop');

-- ============================================================================
-- AUDIT & LOGGING
-- ============================================================================

CREATE TABLE IF NOT EXISTS "audit_logs" (
    "id" BIGSERIAL PRIMARY KEY,
    "user_id" INT UNSIGNED,
    "action" VARCHAR(100),
    "entity_type" VARCHAR(100),
    "entity_id" INT UNSIGNED,
    "changes" JSON,
    "ip_address" VARCHAR(45),
    "user_agent" TEXT,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_user_id" ("user_id"),
    INDEX "idx_action" ("action"),
    INDEX "idx_entity" ("entity_type", "entity_id"),
    INDEX "idx_created_at" ("created_at")
);

-- ============================================================================
-- STOREFRONT CONFIGURATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS "storefront_carousel_slides" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "title" VARCHAR(255) NOT NULL,
    "description" TEXT,
    "image_url" VARCHAR(500) NOT NULL,
    "button_text" VARCHAR(100),
    "button_link" VARCHAR(500),
    "display_order" INT DEFAULT 0,
    "is_active" SMALLINT DEFAULT 1,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_display_order" ("display_order"),
    INDEX "idx_is_active" ("is_active"),
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS "storefront_service_boxes" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "icon" VARCHAR(100) NOT NULL COMMENT 'FontAwesome icon class',
    "title" VARCHAR(255) NOT NULL,
    "description" TEXT,
    "image" VARCHAR(500) NOT NULL,
    "link" VARCHAR(500) NOT NULL,
    "display_order" INT DEFAULT 0,
    "is_active" SMALLINT DEFAULT 1,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_display_order" ("display_order"),
    INDEX "idx_is_active" ("is_active"),
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE RESTRICT
);

-- ============================================================================
-- FEEDBACK & FEATURE REQUESTS
-- ============================================================================

CREATE TABLE IF NOT EXISTS "feedback" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER DEFAULT 1,
    "type" ENUM('bug', 'feature', 'improvement', 'question', 'other') DEFAULT 'feature',
    "priority" ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    "status" ENUM('new', 'in_progress', 'completed', 'rejected', 'duplicate') DEFAULT 'new',
    "title" VARCHAR(255) NOT NULL,
    "description" TEXT NOT NULL,
    "submitted_by_type" ENUM('customer', 'staff') NOT NULL,
    "submitted_by_id" INT UNSIGNED,
    "submitted_by_name" VARCHAR(255),
    "submitted_by_email" VARCHAR(255),
    "category" VARCHAR(100) COMMENT 'e.g., POS, Inventory, Customer Portal, etc.',
    "browser_info" TEXT COMMENT 'Browser/device information',
    "url" VARCHAR(500) COMMENT 'URL where issue occurred',
    "admin_notes" TEXT COMMENT 'Internal admin notes',
    "assigned_to" INTEGER COMMENT 'Staff member ID',
    "completed_at" TIMESTAMP NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_tenant_id" ("tenant_id"),
    INDEX "idx_type" ("type"),
    INDEX "idx_status" ("status"),
    INDEX "idx_priority" ("priority"),
    INDEX "idx_submitted_by" ("submitted_by_type", "submitted_by_id"),
    INDEX "idx_created_at" ("created_at"),
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE RESTRICT,
    FOREIGN KEY ("assigned_to") REFERENCES "users"("id") ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS "feedback_attachments" (
    "id" SERIAL PRIMARY KEY,
    "feedback_id" INTEGER NOT NULL,
    "filename" VARCHAR(255) NOT NULL,
    "filepath" VARCHAR(500) NOT NULL,
    "filesize" INT UNSIGNED,
    "mime_type" VARCHAR(100),
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_feedback_id" ("feedback_id"),
    FOREIGN KEY ("feedback_id") REFERENCES "feedback"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "feedback_comments" (
    "id" SERIAL PRIMARY KEY,
    "feedback_id" INTEGER NOT NULL,
    "user_id" INT UNSIGNED,
    "comment" TEXT NOT NULL,
    "is_internal" SMALLINT DEFAULT 0 COMMENT 'Internal admin comment',
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_feedback_id" ("feedback_id"),
    INDEX "idx_created_at" ("created_at"),
    FOREIGN KEY ("feedback_id") REFERENCES "feedback"("id") ON DELETE CASCADE,
    FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL
);

-- ============================================================================
-- MIGRATIONS TRACKING
-- ============================================================================

CREATE TABLE IF NOT EXISTS "migrations" (
    "id" SERIAL PRIMARY KEY,
    "migration" VARCHAR(255) NOT NULL UNIQUE,
    "batch" INT NOT NULL,
    "executed_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_batch" ("batch")
);

-- Record this core schema as migration batch 1
INSERT INTO "migrations" ("migration", "batch") VALUES ('000_CORE_SCHEMA.sql', 1);

-- ============================================================================
-- COMPLETE
-- ============================================================================
SELECT 'Core schema installation complete!' as status;
