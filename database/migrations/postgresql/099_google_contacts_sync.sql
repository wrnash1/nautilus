-- =====================================================
-- Google Contacts Synchronization
-- Two-way sync between Nautilus customers and Google Contacts
-- =====================================================

-- Google Contacts Sync Configuration
CREATE TABLE IF NOT EXISTS "google_contacts_sync_config" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    
    -- OAuth Credentials
    "google_client_id" VARCHAR(255) NULL,
    "google_client_secret" VARCHAR(255) NULL COMMENT 'Encrypted',
    "access_token" TEXT NULL COMMENT 'Encrypted',
    "refresh_token" TEXT NULL COMMENT 'Encrypted',
    "token_expires_at" TIMESTAMP NULL,
    "authorized_at" TIMESTAMP NULL,
    "authorized_by" INTEGER NULL,
    
    -- Sync Settings
    "sync_enabled" BOOLEAN DEFAULT FALSE,
    "sync_direction" ENUM('two_way', 'export_only', 'import_only') DEFAULT 'two_way',
    "sync_frequency_minutes" INT DEFAULT 15 COMMENT 'Auto-sync interval',
    "last_sync_at" TIMESTAMP NULL,
    "last_full_sync_at" TIMESTAMP NULL,
    "next_sync_at" TIMESTAMP NULL,
    
    -- Sync Filters
    "sync_customer_types" JSON NULL COMMENT 'B2C, B2B - null means all',
    "sync_only_active" BOOLEAN DEFAULT TRUE,
    "exclude_tags" JSON NULL COMMENT 'Customer tags to exclude',
    "min_lifetime_value" DECIMAL(10,2) NULL COMMENT 'Only sync customers above this LTV',
    
    -- Conflict Resolution
    "conflict_strategy" ENUM('last_modified_wins', 'google_wins', 'nautilus_wins', 'manual') DEFAULT 'last_modified_wins',
    "auto_resolve_conflicts" BOOLEAN DEFAULT TRUE,
    
    -- Google Contacts Configuration
    "google_contact_group" VARCHAR(255) NULL COMMENT 'Sync to specific contact group',
    "label_prefix" VARCHAR(50) DEFAULT 'Nautilus' COMMENT 'Prefix for custom labels',
    
    -- Performance
    "batch_size" INT DEFAULT 200 COMMENT 'Contacts per batch',
    "rate_limit_per_second" INT DEFAULT 10,
    
    -- Status
    "sync_status" ENUM('idle', 'syncing', 'error', 'paused') DEFAULT 'idle',
    "last_error_message" TEXT NULL,
    "last_error_at" TIMESTAMP NULL,
    "consecutive_errors" INT DEFAULT 0,
    
    -- Statistics
    "total_syncs" BIGINT DEFAULT 0,
    "total_exports" BIGINT DEFAULT 0,
    "total_imports" BIGINT DEFAULT 0,
    "total_conflicts" BIGINT DEFAULT 0,
    
    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("authorized_by") REFERENCES "users"("id") ON DELETE SET NULL,
    UNIQUE KEY "unique_tenant" ("tenant_id"),
    INDEX idx_sync_enabled ("sync_enabled"),
    INDEX idx_next_sync ("next_sync_at")
);

-- Customer to Google Contact Mapping
CREATE TABLE IF NOT EXISTS "google_contacts_sync_mapping" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,
    
    -- Google Contact Identifiers
    "google_resource_name" VARCHAR(255) NOT NULL COMMENT 'people/c1234567890',
    "google_etag" VARCHAR(255) NULL COMMENT 'For optimistic locking',
    
    -- Sync Tracking
    "sync_status" ENUM('synced', 'pending', 'conflict', 'error') DEFAULT 'synced',
    "last_synced_at" TIMESTAMP NULL,
    "last_sync_direction" ENUM('to_google', 'from_google', 'two_way') NULL,
    
    -- Change Tracking
    "nautilus_last_modified" TIMESTAMP NULL,
    "google_last_modified" TIMESTAMP NULL,
    "hash_nautilus" VARCHAR(64) NULL COMMENT 'MD5 hash of customer data',
    "hash_google" VARCHAR(64) NULL COMMENT 'MD5 hash of Google contact data',
    
    -- Conflict Information
    "has_conflict" BOOLEAN DEFAULT FALSE,
    "conflict_detected_at" TIMESTAMP NULL,
    "conflict_fields" JSON NULL COMMENT 'Fields with conflicts',
    "conflict_resolved_at" TIMESTAMP NULL,
    "conflict_resolution" VARCHAR(50) NULL,
    
    -- Error Tracking
    "error_message" TEXT NULL,
    "error_count" INT DEFAULT 0,
    "last_error_at" TIMESTAMP NULL,
    
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    UNIQUE KEY "unique_customer" ("tenant_id", "customer_id"),
    UNIQUE KEY "unique_google_resource" ("tenant_id", "google_resource_name"),
    INDEX idx_sync_status ("sync_status"),
    INDEX idx_conflict ("has_conflict"),
    INDEX idx_last_synced ("last_synced_at")
);

-- Sync Operation Logs
CREATE TABLE IF NOT EXISTS "google_contacts_sync_log" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    
    -- Sync Session
    "sync_type" ENUM('full', 'incremental', 'manual', 'single_customer') NOT NULL,
    "sync_direction" ENUM('to_google', 'from_google', 'two_way') NOT NULL,
    "triggered_by" ENUM('scheduler', 'user', 'webhook', 'api') DEFAULT 'scheduler',
    "triggered_by_user_id" INTEGER NULL,
    
    -- Timing
    "started_at" TIMESTAMP NOT NULL,
    "completed_at" TIMESTAMP NULL,
    "duration_seconds" INT NULL,
    
    -- Results
    "status" ENUM('in_progress', 'completed', 'partial', 'failed') DEFAULT 'in_progress',
    "customers_processed" INT DEFAULT 0,
    "customers_exported" INT DEFAULT 0,
    "customers_imported" INT DEFAULT 0,
    "customers_updated" INT DEFAULT 0,
    "customers_skipped" INT DEFAULT 0,
    "conflicts_detected" INT DEFAULT 0,
    "conflicts_resolved" INT DEFAULT 0,
    "errors_count" INT DEFAULT 0,
    
    -- Details
    "error_message" TEXT NULL,
    "error_details" JSON NULL,
    "summary" JSON NULL COMMENT 'Additional stats and info',
    
    -- Performance
    "api_calls_made" INT DEFAULT 0,
    "rate_limit_hits" INT DEFAULT 0,
    
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("triggered_by_user_id") REFERENCES "users"("id") ON DELETE SET NULL,
    INDEX idx_tenant_date ("tenant_id", "started_at"),
    INDEX idx_status ("status"),
    INDEX idx_sync_type ("sync_type")
);

-- Field Mapping Configuration
CREATE TABLE IF NOT EXISTS "google_contacts_field_mapping" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    
    -- Field Configuration
    "nautilus_field" VARCHAR(100) NOT NULL COMMENT 'Field name in customers table',
    "google_field" VARCHAR(100) NOT NULL COMMENT 'Google People API field path',
    "field_type" ENUM('name', 'phone', 'email', 'address', 'date', 'text', 'custom') NOT NULL,
    
    -- Sync Behavior
    "sync_enabled" BOOLEAN DEFAULT TRUE,
    "sync_direction" ENUM('both', 'to_google', 'from_google') DEFAULT 'both',
    "is_required" BOOLEAN DEFAULT FALSE,
    
    -- Transformation
    "transform_function" VARCHAR(100) NULL COMMENT 'PHP function to transform value',
    "default_value" VARCHAR(255) NULL,
    "validation_rule" VARCHAR(255) NULL,
    
    -- Priority
    "priority_order" INT DEFAULT 100,
    
    -- Metadata
    "description" TEXT NULL,
    "is_custom_field" BOOLEAN DEFAULT FALSE,
    
    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    UNIQUE KEY "unique_mapping" ("tenant_id", "nautilus_field"),
    INDEX idx_sync_enabled ("sync_enabled")
);

-- =====================================================
-- Pre-seeded Default Field Mappings
-- =====================================================

INSERT INTO "google_contacts_field_mapping" (
    "tenant_id", "nautilus_field", "google_field", "field_type", "sync_direction", "is_required", "priority_order"
) VALUES
-- Names
(1, 'first_name', 'names[0].givenName', 'name', 'both', TRUE, 1),
(1, 'last_name', 'names[0].familyName', 'name', 'both', TRUE, 2),
(1, 'company_name', 'organizations[0].name', 'text', 'both', FALSE, 3),

-- Contact Info
(1, 'email', 'emailAddresses[0].value', 'email', 'both', TRUE, 10),
(1, 'phone', 'phoneNumbers[0].value', 'phone', 'both', FALSE, 11),
(1, 'mobile', 'phoneNumbers[1].value', 'phone', 'both', FALSE, 12),

-- Address
(1, 'address_line1', 'addresses[0].streetAddress', 'address', 'both', FALSE, 20),
(1, 'city', 'addresses[0].city', 'address', 'both', FALSE, 21),
(1, 'state', 'addresses[0].region', 'address', 'both', FALSE, 22),
(1, 'postal_code', 'addresses[0].postalCode', 'address', 'both', FALSE, 23),

-- Other Fields
(1, 'birth_date', 'birthdays[0].date', 'date', 'both', FALSE, 30),
(1, 'notes', 'biographies[0].value', 'text', 'both', FALSE, 40),

-- Custom Fields (stored in notes or custom fields)
(1, 'customer_since', 'userDefined[0].value', 'custom', 'to_google', FALSE, 50),
(1, 'loyalty_tier', 'userDefined[1].value', 'custom', 'to_google', FALSE, 51),
(1, 'total_purchases', 'userDefined[2].value', 'custom', 'to_google', FALSE, 52);

-- Sample sync log entry
INSERT INTO "google_contacts_sync_log" (
    "tenant_id", "sync_type", "sync_direction", "triggered_by",
    "started_at", "completed_at", "status", "customers_processed"
) VALUES
(1, 'full', 'two_way', 'user', NOW(), NOW(), 'completed', 0);
