SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `shipping_carriers`;
DROP TABLE IF EXISTS `product_locations`;
DROP TABLE IF EXISTS `ai_scan_log`;
DROP TABLE IF EXISTS `inventory_count_items`;
DROP TABLE IF EXISTS `inventory_counts`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `shipping_carriers`;
DROP TABLE IF EXISTS `product_locations`;
DROP TABLE IF EXISTS `ai_scan_log`;
DROP TABLE IF EXISTS `inventory_count_items`;
DROP TABLE IF EXISTS `inventory_counts`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `shipping_carriers`;
DROP TABLE IF EXISTS `product_locations`;
DROP TABLE IF EXISTS `ai_scan_log`;
DROP TABLE IF EXISTS `inventory_count_items`;
DROP TABLE IF EXISTS `inventory_counts`;

-- Migration 105: Enhanced Inventory System with Shipping, AI, and Multi-Image Support
-- Adds comprehensive e-commerce shipping fields, AI enrichment tracking, inventory counting

-- Add shipping and AI enrichment fields to products table
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'length');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN length DECIMAL(8,2) AFTER dimensions', 
    'SELECT "Column length already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'width');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN width DECIMAL(8,2) AFTER length', 
    'SELECT "Column width already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'height');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN height DECIMAL(8,2) AFTER width', 
    'SELECT "Column height already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'dimension_unit');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN dimension_unit ENUM(''in'', ''cm'', ''ft'', ''m'') DEFAULT ''in'' AFTER height', 
    'SELECT "Column dimension_unit already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'shipping_class');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN shipping_class VARCHAR(50) DEFAULT ''standard'' AFTER dimension_unit', 
    'SELECT "Column shipping_class already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'is_hazmat');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN is_hazmat BOOLEAN DEFAULT FALSE AFTER shipping_class', 
    'SELECT "Column is_hazmat already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'hazmat_class');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN hazmat_class VARCHAR(50) AFTER is_hazmat', 
    'SELECT "Column hazmat_class already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'country_of_origin');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN country_of_origin VARCHAR(2) DEFAULT ''US'' AFTER hazmat_class', 
    'SELECT "Column country_of_origin already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'hs_code');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN hs_code VARCHAR(20) AFTER country_of_origin', 
    'SELECT "Column hs_code already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'tariff_code');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN tariff_code VARCHAR(20) AFTER hs_code', 
    'SELECT "Column tariff_code already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'requires_signature');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN requires_signature BOOLEAN DEFAULT FALSE AFTER tariff_code', 
    'SELECT "Column requires_signature already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'fragile');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN fragile BOOLEAN DEFAULT FALSE AFTER requires_signature', 
    'SELECT "Column fragile already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- AI enrichment tracking
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'ai_enriched');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN ai_enriched BOOLEAN DEFAULT FALSE AFTER fragile', 
    'SELECT "Column ai_enriched already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'ai_enriched_at');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN ai_enriched_at TIMESTAMP NULL AFTER ai_enriched', 
    'SELECT "Column ai_enriched_at already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'ai_confidence_score');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN ai_confidence_score DECIMAL(3,2) AFTER ai_enriched_at', 
    'SELECT "Column ai_confidence_score already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND column_name = 'ai_suggested_category');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE products ADD COLUMN ai_suggested_category BIGINT UNSIGNED AFTER ai_confidence_score', 
    'SELECT "Column ai_suggested_category already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Create inventory count/audit tables
CREATE TABLE IF NOT EXISTS `inventory_counts` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED DEFAULT 1,
  `count_number` VARCHAR(50) NOT NULL UNIQUE,
  `count_type` ENUM('full', 'partial', 'cycle') DEFAULT 'partial',
  `location` VARCHAR(100),
  `status` ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
  `started_at` TIMESTAMP NULL,
  `completed_at` TIMESTAMP NULL,
  `started_by` BIGINT UNSIGNED,
  `completed_by` BIGINT UNSIGNED,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`started_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`completed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_count_number` (`count_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_count_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `count_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `variant_id` BIGINT UNSIGNED,
  `expected_quantity` INT DEFAULT 0,
  `counted_quantity` INT,
  `difference` INT,
  `notes` TEXT,
  `counted_by` BIGINT UNSIGNED,
  `counted_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`count_id`) REFERENCES `inventory_counts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`counted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_count_id` (`count_id`),
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create AI scan log for tracking image/barcode scans
CREATE TABLE IF NOT EXISTS `ai_scan_log` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `scan_type` ENUM('barcode', 'image', 'product_photo', 'document') NOT NULL,
  `scan_data` TEXT NOT NULL,
  `product_id` BIGINT UNSIGNED,
  `recognized_text` TEXT,
  `confidence_score` DECIMAL(3,2),
  `ai_model_used` VARCHAR(100),
  `processing_time_ms` INT,
  `result_data` JSON,
  `user_id` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_scan_type` (`scan_type`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create product location tracking for multi-location inventory
CREATE TABLE IF NOT EXISTS `product_locations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `variant_id` BIGINT UNSIGNED,
  `location_name` VARCHAR(100) NOT NULL,
  `location_type` ENUM('warehouse', 'retail_floor', 'storage', 'backroom', 'other') DEFAULT 'retail_floor',
  `aisle` VARCHAR(20),
  `shelf` VARCHAR(20),
  `bin` VARCHAR(20),
  `quantity` INT DEFAULT 0,
  `last_counted_at` TIMESTAMP NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_location` (`product_id`, `variant_id`, `location_name`),
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_location_type` (`location_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add shipping carrier integration settings
CREATE TABLE IF NOT EXISTS `shipping_carriers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED DEFAULT 1,
  `carrier_name` VARCHAR(50) NOT NULL,
  `carrier_code` VARCHAR(20) NOT NULL,
  `api_enabled` BOOLEAN DEFAULT FALSE,
  `api_key` VARCHAR(255),
  `api_secret` VARCHAR(255),
  `account_number` VARCHAR(100),
  `test_mode` BOOLEAN DEFAULT TRUE,
  `is_active` BOOLEAN DEFAULT TRUE,
  `settings` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE RESTRICT,
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_carrier_code` (`carrier_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for AI enrichment queries
SET @index_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE table_schema = DATABASE() AND table_name = 'products' AND index_name = 'idx_ai_enriched');
SET @sql = IF(@index_exists = 0,
    'ALTER TABLE products ADD INDEX idx_ai_enriched (ai_enriched, ai_enriched_at)',
    'SELECT "Index idx_ai_enriched already exists"');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Insert default shipping carriers
INSERT IGNORE INTO shipping_carriers (carrier_name, carrier_code, is_active, test_mode) VALUES
('USPS', 'usps', TRUE, TRUE),
('UPS', 'ups', TRUE, TRUE),
('FedEx', 'fedex', TRUE, TRUE),
('DHL', 'dhl', FALSE, TRUE);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;