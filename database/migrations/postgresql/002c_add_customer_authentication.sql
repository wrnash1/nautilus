-- Add authentication columns to customers table for customer portal login
-- Simplified to avoid PDO buffering issues

-- Add password column for customer portal authentication
ALTER TABLE customers
ADD COLUMN IF NOT EXISTS password VARCHAR(255) NULL AFTER email;

-- Add email verification timestamp
ALTER TABLE customers
ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL AFTER password;

-- Add remember token for "remember me" functionality
ALTER TABLE customers
ADD COLUMN IF NOT EXISTS remember_token VARCHAR(100) NULL AFTER email_verified_at;

-- Add unique index on email to prevent duplicates and speed up login queries
-- Note: Using CREATE INDEX IF NOT EXISTS syntax
CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_email ON customers(email);
