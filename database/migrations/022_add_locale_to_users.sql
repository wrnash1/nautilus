-- ================================================
-- Nautilus V6 - Add Locale Support to Users
-- Migration: 022_add_locale_to_users.sql
-- Description: Add locale column for multi-language support
-- ================================================

-- Add locale column to users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS locale VARCHAR(5) DEFAULT 'en' COMMENT 'User preferred language locale (en, es, fr, de, pt, it, ja, zh)' AFTER email;

-- Add index if it doesn't exist
SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'users'
AND INDEX_NAME = 'idx_locale');

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE users ADD INDEX idx_locale (locale)',
    'SELECT "Index already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing users to default locale if not set
UPDATE users SET locale = 'en' WHERE locale IS NULL;
