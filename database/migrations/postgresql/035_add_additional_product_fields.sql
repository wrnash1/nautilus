-- Add additional product fields requested in error.txt

ALTER TABLE "products"
ADD COLUMN IF NOT EXISTS "qr_code" VARCHAR(255) AFTER "barcode",
ADD COLUMN IF NOT EXISTS "color" VARCHAR(100) AFTER "dimensions",
ADD COLUMN IF NOT EXISTS "material" VARCHAR(100) AFTER "color",
ADD COLUMN IF NOT EXISTS "manufacturer" VARCHAR(255) AFTER "material",
ADD COLUMN IF NOT EXISTS "warranty_info" TEXT AFTER "manufacturer",
ADD COLUMN IF NOT EXISTS "location_in_store" VARCHAR(255) AFTER "warranty_info",
ADD COLUMN IF NOT EXISTS "supplier_info" TEXT AFTER "location_in_store",
ADD COLUMN IF NOT EXISTS "expiration_date" DATE AFTER "supplier_info";

-- Add indexes if they don't exist
SET @index_qr = (SELECT COUNT(*) FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_qr_code');

SET @index_exp = (SELECT COUNT(*) FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_expiration_date');

SET @sql_qr = IF(@index_qr = 0, 'ALTER TABLE products ADD INDEX idx_qr_code (qr_code)', 'SELECT "idx_qr_code exists"');
SET @sql_exp = IF(@index_exp = 0, 'ALTER TABLE products ADD INDEX idx_expiration_date (expiration_date)', 'SELECT "idx_expiration_date exists"');

PREPARE stmt FROM @sql_qr; EXECUTE stmt; DEALLOCATE PREPARE stmt;
PREPARE stmt FROM @sql_exp; EXECUTE stmt; DEALLOCATE PREPARE stmt;
