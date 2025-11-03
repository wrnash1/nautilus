-- Add additional product fields requested in error.txt

ALTER TABLE `products`
ADD COLUMN `qr_code` VARCHAR(255) AFTER `barcode`,
ADD COLUMN `color` VARCHAR(100) AFTER `dimensions`,
ADD COLUMN `material` VARCHAR(100) AFTER `color`,
ADD COLUMN `manufacturer` VARCHAR(255) AFTER `material`,
ADD COLUMN `warranty_info` TEXT AFTER `manufacturer`,
ADD COLUMN `location_in_store` VARCHAR(255) AFTER `warranty_info`,
ADD COLUMN `supplier_info` TEXT AFTER `location_in_store`,
ADD COLUMN `expiration_date` DATE AFTER `supplier_info`,
ADD INDEX `idx_qr_code` (`qr_code`),
ADD INDEX `idx_expiration_date` (`expiration_date`);
