-- Migration 015: Add Encryption Support and Audit Logging for Settings
-- This migration enhances security by:
-- 1. Creating an audit log table for sensitive settings access
-- 2. Marking sensitive settings as encrypted type
-- 3. Adding indexes for performance

-- ============================================================================
-- PART 1: Create Settings Audit Log Table
-- ============================================================================

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `settings_audit`;
DROP TABLE IF EXISTS `system_metadata`;

CREATE TABLE IF NOT EXISTS `settings_audit` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(150) NOT NULL COMMENT 'Full key including category (e.g., payment.stripe_secret_key)',
  `action` ENUM('read', 'update', 'delete') NOT NULL,
  `user_id` BIGINT UNSIGNED NULL COMMENT 'User who accessed the setting',
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
SET `type` = 'encrypted'
WHERE `key` IN ('stripe_secret_key', 'stripe_webhook_secret')
  AND `category` = 'payment';

UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('square_access_token')
  AND `category` = 'payment';

UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('btcpay_api_key')
  AND `category` = 'payment';

-- Communication Secrets
UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('twilio_auth_token')
  AND `category` = 'integrations';

-- Email Configuration
UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('smtp_password')
  AND `category` = 'email';

-- Integration API Keys
UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('padi_api_key', 'padi_api_secret', 'ssi_api_key')
  AND `category` = 'integrations';

-- Shipping API Credentials
UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('ups_password', 'fedex_secret_key')
  AND `category` = 'shipping';

-- Other third-party integrations
UPDATE `settings`
SET `type` = 'encrypted'
WHERE `key` IN ('wave_access_token')
  AND `category` = 'integrations';

-- ============================================================================
-- PART 3: Add Performance Indexes to Settings Table
-- ============================================================================

-- Check if indexes don't already exist and create them
-- These improve query performance for settings lookups

-- Composite index for category + key lookups (most common query pattern)
ALTER TABLE `settings`
ADD INDEX IF NOT EXISTS `idx_category_key` (`category`, `key`);

-- Index for filtering by type (useful for finding all encrypted settings)
ALTER TABLE `settings`
ADD INDEX IF NOT EXISTS `idx_type` (`type`);

-- Index for finding recently updated settings
ALTER TABLE `settings`
ADD INDEX IF NOT EXISTS `idx_updated_at` (`updated_at`);

-- ============================================================================
-- PART 4: Add Description Column for Settings Documentation (if not exists)
-- ============================================================================

-- Check if description column exists in settings table
-- This helps document what each setting does
-- Using simple ALTER TABLE with IF NOT EXISTS

ALTER TABLE `settings`
ADD COLUMN IF NOT EXISTS `description` TEXT NULL COMMENT 'Human-readable description of the setting' AFTER `type`;

-- ============================================================================
-- PART 5: Insert Default Encrypted Settings Documentation
-- ============================================================================

-- Insert or update descriptions for encrypted settings
-- This uses INSERT ... ON DUPLICATE KEY UPDATE to avoid errors if settings don't exist

INSERT INTO `settings` (`category`, `key`, `value`, `type`, `description`, `updated_at`)
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
  `type` = VALUES(`type`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();

-- ============================================================================
-- PART 6: Add Security Notes to Database
-- ============================================================================

-- Create a metadata table for storing important security information
CREATE TABLE IF NOT EXISTS `system_metadata` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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

SET FOREIGN_KEY_CHECKS=1;
