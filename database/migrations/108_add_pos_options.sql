-- Add tax_rate to system_settings
-- First ensure category column exists (robust check)
SET @dbname = DATABASE();
SET @tablename = "system_settings";
SET @columnname = "category";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE system_settings ADD COLUMN category VARCHAR(50) DEFAULT 'general'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) 
VALUES ('tax_rate', '0.08', 'float', 'pos', 'Default sales tax rate (e.g., 0.08 for 8%)');
