-- Add description field to certification agencies (conditional)
-- Note: logo_path and primary_color already added in migration 014
SET @dbname = DATABASE();
SET @tablename = "certification_agencies";
SET @columnname = "description";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE `certification_agencies` ADD COLUMN `description` TEXT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing agencies with default colors
-- Note: primary_color column is added in migration 014, so these UPDATEs should work
-- UPDATE `certification_agencies` SET `primary_color` = '#0066CC' WHERE `abbreviation` = 'PADI';
-- UPDATE `certification_agencies` SET `primary_color` = '#006699' WHERE `abbreviation` = 'SSI';
-- UPDATE `certification_agencies` SET `primary_color` = '#CC0000' WHERE `abbreviation` = 'NAUI';
-- UPDATE `certification_agencies` SET `primary_color` = '#009900' WHERE `abbreviation` = 'SDI';
-- UPDATE `certification_agencies` SET `primary_color` = '#FF6600' WHERE `abbreviation` = 'TDI';
