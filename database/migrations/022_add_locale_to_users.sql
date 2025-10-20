-- ================================================
-- Nautilus V6 - Add Locale Support to Users
-- Migration: 022_add_locale_to_users.sql
-- Description: Add locale column for multi-language support
-- ================================================

-- Add locale column to users table
ALTER TABLE users
ADD COLUMN locale VARCHAR(5) DEFAULT 'en' AFTER email,
ADD INDEX idx_locale (locale);

-- Update existing users to default locale if not set
UPDATE users SET locale = 'en' WHERE locale IS NULL;

COMMENT ON COLUMN users.locale IS 'User preferred language locale (en, es, fr, de, pt, it, ja, zh)';
