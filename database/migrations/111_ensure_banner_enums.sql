SET FOREIGN_KEY_CHECKS=0;

-- Ensure banner_type has all required values
-- This covers cases where 101 ran before the update
SET @dbname = DATABASE();
SET @tablename = "promotional_banners";
SET @columnname = "banner_type";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "ALTER TABLE promotional_banners MODIFY COLUMN banner_type ENUM('info', 'warning', 'success', 'danger', 'promotion', 'top_bar', 'hero', 'sidebar', 'popup', 'footer') DEFAULT 'info';",
  "SELECT 1"
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

SET FOREIGN_KEY_CHECKS=1;
