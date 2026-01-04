-- ============================================================================
-- UPDATE SYSTEM TABLES - Migration 102
-- Migration: 102_create_update_system_tables.sql
-- Purpose: Create tables for enterprise update system
-- Features: Version tracking, backups, update history, rollback capability
-- ============================================================================

-- ============================================================================
-- TABLE: system_updates
-- Purpose: Track all system updates (pending, completed, failed, rolled back)
-- ============================================================================

CREATE TABLE IF NOT EXISTS "system_updates" (
    "id" SERIAL PRIMARY KEY,
    "version" VARCHAR(20) NOT NULL,
    "previous_version" VARCHAR(20),
    "status" ENUM('pending', 'downloading', 'in_progress', 'completed', 'failed', 'rolled_back') DEFAULT 'pending',
    "backup_id" INTEGER NULL,
    "update_package_path" VARCHAR(255),
    "update_package_size" BIGINT UNSIGNED,
    "update_package_checksum" VARCHAR(64),
    "changelog" TEXT,
    "started_at" TIMESTAMP NULL,
    "completed_at" TIMESTAMP NULL,
    "error_message" TEXT,
    "updated_by" INT UNSIGNED,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_version" ("version"),
    INDEX "idx_status" ("status"),
    INDEX "idx_created_at" ("created_at"),
    FOREIGN KEY ("updated_by") REFERENCES "users"("id") ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks all system updates and their status';

-- ============================================================================
-- TABLE: system_backups
-- Purpose: Track all system backups (database, files, full)
-- ============================================================================

CREATE TABLE IF NOT EXISTS "system_backups" (
    "id" SERIAL PRIMARY KEY,
    "backup_type" ENUM('full', 'database', 'files', 'pre_update') DEFAULT 'full',
    "file_path" VARCHAR(255) NOT NULL,
    "file_size" BIGINT UNSIGNED,
    "compression_type" ENUM('none', 'gzip', 'zip') DEFAULT 'gzip',
    "checksum" VARCHAR(64),
    "is_encrypted" BOOLEAN DEFAULT FALSE,
    "created_by" INT UNSIGNED,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "expires_at" TIMESTAMP NULL,
    "restored_at" TIMESTAMP NULL,
    "notes" TEXT,
    INDEX "idx_backup_type" ("backup_type"),
    INDEX "idx_created_at" ("created_at"),
    INDEX "idx_expires_at" ("expires_at"),
    FOREIGN KEY ("created_by") REFERENCES "users"("id") ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks all system backups for disaster recovery';

-- ============================================================================
-- TABLE: system_version
-- Purpose: Track current system version and metadata
-- ============================================================================

CREATE TABLE IF NOT EXISTS "system_version" (
    "id" SERIAL PRIMARY KEY,
    "version" VARCHAR(20) NOT NULL,
    "build_number" VARCHAR(50),
    "release_date" DATE,
    "is_current" BOOLEAN DEFAULT TRUE,
    "installed_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "installed_by" INT UNSIGNED,
    "notes" TEXT,
    INDEX "idx_version" ("version"),
    INDEX "idx_is_current" ("is_current"),
    FOREIGN KEY ("installed_by") REFERENCES "users"("id") ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks system version history';

-- ============================================================================
-- TABLE: update_notifications
-- Purpose: Store update notifications for admins
-- ============================================================================

CREATE TABLE IF NOT EXISTS "update_notifications" (
    "id" SERIAL PRIMARY KEY,
    "version" VARCHAR(20) NOT NULL,
    "title" VARCHAR(255) NOT NULL,
    "message" TEXT,
    "severity" ENUM('info', 'warning', 'critical') DEFAULT 'info',
    "is_read" BOOLEAN DEFAULT FALSE,
    "read_at" TIMESTAMP NULL,
    "read_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_is_read" ("is_read"),
    INDEX "idx_severity" ("severity"),
    INDEX "idx_created_at" ("created_at"),
    FOREIGN KEY ("read_by") REFERENCES "users"("id") ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Update notifications for administrators';

-- ============================================================================
-- TABLE: maintenance_mode
-- Purpose: Track maintenance mode status
-- ============================================================================

CREATE TABLE IF NOT EXISTS "maintenance_mode" (
    "id" SERIAL PRIMARY KEY,
    "is_enabled" BOOLEAN DEFAULT FALSE,
    "message" TEXT,
    "allowed_ips" TEXT COMMENT 'JSON array of allowed IP addresses',
    "enabled_at" TIMESTAMP NULL,
    "enabled_by" INTEGER NULL,
    "disabled_at" TIMESTAMP NULL,
    "disabled_by" INTEGER NULL,
    "reason" VARCHAR(255),
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX "idx_is_enabled" ("is_enabled"),
    FOREIGN KEY ("enabled_by") REFERENCES "users"("id") ON DELETE SET NULL,
    FOREIGN KEY ("disabled_by") REFERENCES "users"("id") ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Maintenance mode status and configuration';

-- ============================================================================
-- Insert default system version (1.0.0)
-- ============================================================================

INSERT IGNORE INTO "system_version" ("version", "build_number", "release_date", "is_current", "notes")
VALUES ('1.0.0', '20251120', '2025-11-20', TRUE, 'Initial release with core features');

-- ============================================================================
-- Insert default maintenance mode record
-- ============================================================================

INSERT IGNORE INTO "maintenance_mode" ("is_enabled", "message", "reason")
VALUES (FALSE, 'System is currently under maintenance. Please check back soon.', 'Initial setup');

-- ============================================================================
-- Add foreign key to system_updates for backup_id
-- ============================================================================

ALTER TABLE "system_updates"
ADD CONSTRAINT "fk_system_updates_backup"
FOREIGN KEY ("backup_id") REFERENCES "system_backups"("id") ON DELETE SET NULL;

-- ============================================================================
-- COMPLETION MESSAGE
-- ============================================================================

SELECT 
    'Migration 102 Complete!' AS status,
    (SELECT COUNT(*) FROM system_version) AS version_records,
    (SELECT COUNT(*) FROM maintenance_mode) AS maintenance_records,
    'Update system tables created successfully' AS message;

-- ============================================================================
-- This migration creates:
-- ✓ system_updates - Track update history and status
-- ✓ system_backups - Track all backups for rollback
-- ✓ system_version - Track version history
-- ✓ update_notifications - Notify admins of updates
-- ✓ maintenance_mode - Manage maintenance mode
--
-- Result: Enterprise-ready update system infrastructure
-- ============================================================================
