-- Migration 015: Add Encryption Support and Audit Logging for Settings
-- This migration enhances security by:
-- 1. Creating an audit log table for sensitive settings access
-- 2. Marking sensitive settings as encrypted type
-- 3. Adding indexes for performance

-- ============================================================================
-- PART 1: Create Settings Audit Log Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `settings_audit` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(150) NOT NULL COMMENT 'Full key including category (e.g., payment.stripe_secret_key)',
  `action` ENUM('read', 'update', 'delete') NOT NULL,
  `user_id` INT UNSIGNED NULL COMMENT 'User who accessed the setting',
  `ip_address` VARCHAR(45) NOT NULL COMMENT 'IP address of the requester',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_setting_key` (`setting_key`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_action` (`action`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit log for tracking access to sensitive settings';

-- ============================================================================
-- PART 2: Mark Sensitive Settings as Encrypted Type
-- ============================================================================

-- Update existing sensitive settings to use encrypted type
-- Only updates settings that already exist in the database

-- Payment Gateway Secrets
UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('stripe_secret_key', 'stripe_webhook_secret')
  AND `category` = 'payment';

UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('square_access_token')
  AND `category` = 'payment';

UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('btcpay_api_key')
  AND `category` = 'payment';

-- Communication Secrets
UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('twilio_auth_token')
  AND `category` = 'integrations';

-- Email Configuration
UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('smtp_password')
  AND `category` = 'email';

-- Integration API Keys
UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('padi_api_key', 'padi_api_secret', 'ssi_api_key')
  AND `category` = 'integrations';

-- Shipping API Credentials
UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('ups_password', 'fedex_secret_key')
  AND `category` = 'shipping';

-- Other third-party integrations
UPDATE `settings`
SET `setting_type` = 'encrypted'
WHERE `setting_key` IN ('wave_access_token')
  AND `category` = 'integrations';

-- ============================================================================
-- PART 3: Add Performance Indexes to Settings Table
-- ============================================================================

-- Check if indexes don't already exist and create them
-- These improve query performance for settings lookups

-- Composite index for category + key lookups (most common query pattern)
ALTER TABLE `settings`
ADD INDEX IF NOT EXISTS `idx_category_key` (`category`, `setting_key`);

-- Index for filtering by type (useful for finding all encrypted settings)
ALTER TABLE `settings`
ADD INDEX IF NOT EXISTS `idx_setting_type` (`setting_type`);

-- Index for finding recently updated settings
ALTER TABLE `settings`
ADD INDEX IF NOT EXISTS `idx_updated_at` (`updated_at`);

-- ============================================================================
-- PART 4: Add Description Column for Settings Documentation (if not exists)
-- ============================================================================

-- Check if description column exists in settings table
-- This helps document what each setting does

SET @column_exists = (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'settings'
    AND COLUMN_NAME = 'description'
);

SET @sql = IF(
  @column_exists = 0,
  'ALTER TABLE `settings` ADD COLUMN `description` TEXT NULL COMMENT ''Human-readable description of the setting'' AFTER `setting_type`',
  'SELECT ''Column description already exists'' AS message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================================
-- PART 5: Insert Default Encrypted Settings Documentation
-- ============================================================================

-- Insert or update descriptions for encrypted settings
-- This uses INSERT ... ON DUPLICATE KEY UPDATE to avoid errors if settings don't exist

INSERT INTO `settings` (`category`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`)
VALUES
  ('payment', 'stripe_secret_key', '', 'encrypted', 'Stripe Secret API Key (sk_...)', NOW()),
  ('payment', 'stripe_webhook_secret', '', 'encrypted', 'Stripe Webhook Signing Secret', NOW()),
  ('payment', 'square_access_token', '', 'encrypted', 'Square Access Token', NOW()),
  ('payment', 'btcpay_api_key', '', 'encrypted', 'BTCPay Server API Key', NOW()),
  ('integrations', 'twilio_auth_token', '', 'encrypted', 'Twilio Authentication Token', NOW()),
  ('email', 'smtp_password', '', 'encrypted', 'SMTP Server Password', NOW()),
  ('integrations', 'padi_api_key', '', 'encrypted', 'PADI API Key', NOW()),
  ('integrations', 'padi_api_secret', '', 'encrypted', 'PADI API Secret', NOW()),
  ('integrations', 'ssi_api_key', '', 'encrypted', 'SSI API Key', NOW())
ON DUPLICATE KEY UPDATE
  `setting_type` = VALUES(`setting_type`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();

-- ============================================================================
-- PART 6: Add Security Notes to Database
-- ============================================================================

-- Create a metadata table for storing important security information
CREATE TABLE IF NOT EXISTS `system_metadata` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `meta_key` VARCHAR(100) UNIQUE NOT NULL,
  `meta_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System-level metadata and configuration';

-- Record migration information
INSERT INTO `system_metadata` (`meta_key`, `meta_value`, `updated_at`)
VALUES
  ('encryption_enabled', 'true', NOW()),
  ('encryption_cipher', 'AES-256-CBC', NOW()),
  ('last_security_migration', '015_add_settings_encryption_and_audit', NOW())
ON DUPLICATE KEY UPDATE
  `meta_value` = VALUES(`meta_value`),
  `updated_at` = NOW();

-- ============================================================================
-- Migration Complete
-- ============================================================================

-- Note: After running this migration, you must:
-- 1. Ensure APP_KEY is set in .env (at least 32 characters)
-- 2. Re-enter all sensitive API keys via the admin panel
-- 3. Old plaintext values will be re-encrypted when updated
