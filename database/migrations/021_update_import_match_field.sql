ALTER TABLE `product_import_jobs` MODIFY COLUMN `match_field` ENUM('sku', 'barcode', 'vendor_sku', 'email', 'external_id') DEFAULT 'sku';
