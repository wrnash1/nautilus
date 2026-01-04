-- Add model and attributes columns to products table
ALTER TABLE `products`
ADD COLUMN `model` VARCHAR(100) AFTER `sku`,
ADD COLUMN `attributes` JSON AFTER `is_taxable`;

-- Add index for model
CREATE INDEX `idx_model` ON `products` (`model`);

-- Record migration
-- Migration tracking handled by installer
