SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `credential_rotation_log`;
DROP TABLE IF EXISTS `tenant_secrets`;
DROP TABLE IF EXISTS `tenant_database_credentials`;
DROP TABLE IF EXISTS `environment_settings`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `credential_rotation_log`;
DROP TABLE IF EXISTS `tenant_secrets`;
DROP TABLE IF EXISTS `tenant_database_credentials`;
DROP TABLE IF EXISTS `environment_settings`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `credential_rotation_log`;
DROP TABLE IF EXISTS `tenant_secrets`;
DROP TABLE IF EXISTS `tenant_database_credentials`;
DROP TABLE IF EXISTS `environment_settings`;

-- Migration: Environment Configuration and Tenant Database Credentials
-- Purpose: Store environment-specific settings and per-tenant database credentials
-- Security: Credentials should be encrypted at application level before storing

-- ============================================================================
-- Environment Settings Table
-- Stores application-wide settings per environment (dev, staging, production)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `environment_settings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `environment` ENUM('development', 'staging', 'production') NOT NULL,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    `is_encrypted` TINYINT(1) DEFAULT 0,
    `is_sensitive` TINYINT(1) DEFAULT 0,
    `description` VARCHAR(255),
    `last_updated_by` BIGINT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `env_key_unique` (`environment`, `setting_key`),
    INDEX `idx_environment` (`environment`),
    INDEX `idx_sensitive` (`is_sensitive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Tenant Database Credentials Table
-- Stores dedicated database credentials for enterprise tenants
-- SECURITY: Values in db_password should be encrypted before storage
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tenant_database_credentials` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `environment` ENUM('development', 'staging', 'production') NOT NULL DEFAULT 'production',
    `use_dedicated_db` TINYINT(1) DEFAULT 0,
    `db_host` VARCHAR(255),
    `db_port` INT DEFAULT 3306,
    `db_database` VARCHAR(100),
    `db_username` VARCHAR(100),
    `db_password` TEXT,  -- Should be encrypted
    `db_driver` VARCHAR(20) DEFAULT 'mysql',
    `connection_options` JSON,  -- Additional PDO options
    `is_active` TINYINT(1) DEFAULT 1,
    `max_connections` INT DEFAULT 100,
    `connection_timeout` INT DEFAULT 30,
    `read_only` TINYINT(1) DEFAULT 0,
    `last_connection_test` TIMESTAMP NULL,
    `last_connection_status` ENUM('success', 'failed', 'untested') DEFAULT 'untested',
    `last_connection_error` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `tenant_env_unique` (`tenant_id`, `environment`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX `idx_environment` (`environment`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- API Keys and Secrets Table
-- Stores encrypted API keys, webhooks, and other secrets per tenant
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tenant_secrets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `environment` ENUM('development', 'staging', 'production') NOT NULL,
    `service_name` VARCHAR(100) NOT NULL,  -- e.g., 'stripe', 'aws', 'mailgun'
    `key_name` VARCHAR(100) NOT NULL,      -- e.g., 'api_key', 'secret_key', 'webhook_secret'
    `key_value` TEXT NOT NULL,             -- Encrypted value
    `is_encrypted` TINYINT(1) DEFAULT 1,
    `key_type` ENUM('api_key', 'secret_key', 'access_token', 'webhook_secret', 'password', 'certificate') DEFAULT 'api_key',
    `expires_at` TIMESTAMP NULL,
    `last_rotated_at` TIMESTAMP NULL,
    `rotation_days` INT DEFAULT 90,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_by` BIGINT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `tenant_env_service_key` (`tenant_id`, `environment`, `service_name`, `key_name`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX `idx_service` (`service_name`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Credential Rotation Audit Log
-- Tracks when credentials are rotated for compliance
-- ============================================================================
CREATE TABLE IF NOT EXISTS `credential_rotation_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED,
    `credential_type` ENUM('database', 'api_key', 'secret', 'password') NOT NULL,
    `credential_id` BIGINT UNSIGNED,
    `environment` ENUM('development', 'staging', 'production') NOT NULL,
    `rotation_reason` VARCHAR(255),
    `rotated_by` BIGINT UNSIGNED,
    `old_value_hash` VARCHAR(64),  -- SHA256 hash for verification (not the actual value)
    `new_value_hash` VARCHAR(64),
    `rotation_status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
    `error_message` TEXT,
    `rotated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE SET NULL,
    INDEX `idx_tenant` (`tenant_id`),
    INDEX `idx_credential_type` (`credential_type`),
    INDEX `idx_rotated_at` (`rotated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Insert Default Environment Settings
-- ============================================================================
INSERT INTO `environment_settings` (`environment`, `setting_key`, `setting_value`, `is_sensitive`, `description`) VALUES
('development', 'db_connection_pool_size', '10', 0, 'Maximum database connections for development'),
('development', 'session_lifetime', '7200', 0, 'Session lifetime in seconds'),
('development', 'enable_query_logging', 'true', 0, 'Enable SQL query logging'),
('development', 'rate_limit_enabled', 'false', 0, 'Enable rate limiting'),

('staging', 'db_connection_pool_size', '25', 0, 'Maximum database connections for staging'),
('staging', 'session_lifetime', '3600', 0, 'Session lifetime in seconds'),
('staging', 'enable_query_logging', 'true', 0, 'Enable SQL query logging'),
('staging', 'rate_limit_enabled', 'true', 0, 'Enable rate limiting'),

('production', 'db_connection_pool_size', '100', 0, 'Maximum database connections for production'),
('production', 'session_lifetime', '3600', 0, 'Session lifetime in seconds'),
('production', 'enable_query_logging', 'false', 0, 'Enable SQL query logging'),
('production', 'rate_limit_enabled', 'true', 0, 'Enable rate limiting'),
('production', 'force_https', 'true', 0, 'Force HTTPS connections'),
('production', 'enable_csrf_protection', 'true', 0, 'Enable CSRF token validation'),
('production', 'backup_encryption_enabled', 'true', 1, 'Encrypt database backups');

-- ============================================================================
-- Add Example Tenant Database Config (for reference - update for your needs)
-- ============================================================================
-- For the default tenant, keep using shared database
-- INSERT INTO `tenant_database_credentials`
--     (`tenant_id`, `environment`, `use_dedicated_db`, `db_host`, `db_database`, `db_username`, `db_password`)
-- VALUES
--     (1, 'development', 0, 'database', 'nautilus', 'nautilus', 'nautilus123')
-- ON DUPLICATE KEY UPDATE
--     `use_dedicated_db` = 0,
--     `db_host` = 'database';

-- ============================================================================
-- Add Helpful Views
-- ============================================================================
CREATE OR REPLACE VIEW `v_tenant_db_config` AS
SELECT
    t.id as tenant_id,
    t.name as tenant_name,
    t.status as tenant_status,
    tdc.environment,
    tdc.use_dedicated_db,
    tdc.db_host,
    tdc.db_port,
    tdc.db_database,
    tdc.db_username,
    '***HIDDEN***' as db_password,
    tdc.is_active,
    tdc.last_connection_status,
    tdc.last_connection_test
FROM tenants t
LEFT JOIN tenant_database_credentials tdc ON t.id = tdc.tenant_id
WHERE t.status = 'active';


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;