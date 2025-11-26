-- Fix products table schema to match migration 003
-- Rename columns if they exist in old format
SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'products' AND column_name = 'quantity_in_stock' AND table_schema = DATABASE());
SET @sql := IF(@exist > 0, 'ALTER TABLE products CHANGE COLUMN quantity_in_stock stock_quantity INT DEFAULT 0', 'SELECT "Column quantity_in_stock not found"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'products' AND column_name = 'cost' AND table_schema = DATABASE());
SET @sql := IF(@exist > 0, 'ALTER TABLE products CHANGE COLUMN cost cost_price DECIMAL(10,2) DEFAULT 0.00', 'SELECT "Column cost not found"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'products' AND column_name = 'price' AND table_schema = DATABASE());
SET @sql := IF(@exist > 0, 'ALTER TABLE products CHANGE COLUMN price retail_price DECIMAL(10,2) NOT NULL', 'SELECT "Column price not found"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'products' AND column_name = 'reorder_level' AND table_schema = DATABASE());
SET @sql := IF(@exist > 0, 'ALTER TABLE products CHANGE COLUMN reorder_level low_stock_threshold INT DEFAULT 5', 'SELECT "Column reorder_level not found"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add missing columns
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS product_type ENUM('simple', 'variable', 'service', 'digital') DEFAULT 'simple',
ADD COLUMN IF NOT EXISTS sale_price DECIMAL(10,2),
ADD COLUMN IF NOT EXISTS wholesale_price DECIMAL(10,2),
ADD COLUMN IF NOT EXISTS tax_class VARCHAR(50) DEFAULT 'standard',
ADD COLUMN IF NOT EXISTS weight DECIMAL(8,2),
ADD COLUMN IF NOT EXISTS weight_unit ENUM('lb', 'kg', 'oz', 'g') DEFAULT 'lb',
ADD COLUMN IF NOT EXISTS dimensions VARCHAR(100),
ADD COLUMN IF NOT EXISTS track_inventory BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS allow_backorders BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS meta_title VARCHAR(255),
ADD COLUMN IF NOT EXISTS meta_description VARCHAR(500),
ADD COLUMN IF NOT EXISTS meta_keywords VARCHAR(255),
ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0;
