-- Add tax_rate to system_settings
INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`) 
VALUES ('tax_rate', '0.08', 'float', 'pos', 'Default sales tax rate (e.g., 0.08 for 8%)');
