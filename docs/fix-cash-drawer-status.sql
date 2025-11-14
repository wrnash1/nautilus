-- Fix missing status column in cash_drawer_sessions table
-- This adds the status column if it doesn't exist

USE nautilus;

-- Add status column if not exists
ALTER TABLE cash_drawer_sessions
ADD COLUMN IF NOT EXISTS status ENUM('open', 'closed', 'balanced', 'over', 'short') DEFAULT 'open' AFTER difference_reason;

-- Add index on status if not exists
ALTER TABLE cash_drawer_sessions
ADD INDEX IF NOT EXISTS idx_status (status);

-- Verify the column exists
SELECT 'Status column check:' as '';
SELECT COUNT(*) as status_column_exists
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'nautilus'
  AND TABLE_NAME = 'cash_drawer_sessions'
  AND COLUMN_NAME = 'status';

-- Show final table structure
SELECT 'Final table structure:' as '';
DESCRIBE cash_drawer_sessions;
