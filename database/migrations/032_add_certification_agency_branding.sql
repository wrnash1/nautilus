-- Add branding fields to certification agencies
ALTER TABLE `certification_agencies`
ADD COLUMN `logo_path` VARCHAR(255) NULL AFTER `abbreviation`,
ADD COLUMN `primary_color` VARCHAR(7) DEFAULT '#0066CC' AFTER `logo_path`,
ADD COLUMN `description` TEXT NULL AFTER `primary_color`;

-- Update existing agencies with default colors
UPDATE `certification_agencies` SET `primary_color` = '#0066CC' WHERE `abbreviation` = 'PADI';
UPDATE `certification_agencies` SET `primary_color` = '#006699' WHERE `abbreviation` = 'SSI';
UPDATE `certification_agencies` SET `primary_color` = '#CC0000' WHERE `abbreviation` = 'NAUI';
UPDATE `certification_agencies` SET `primary_color` = '#009900' WHERE `abbreviation` = 'SDI';
UPDATE `certification_agencies` SET `primary_color` = '#FF6600' WHERE `abbreviation` = 'TDI';
