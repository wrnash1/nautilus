-- Migration 017: RMA System and Product Import Infrastructure
-- Creates comprehensive RMA and CSV import functionality

-- ============================================================================
-- PART 1: RMA (Return Merchandise Authorization) System
-- ============================================================================

-- RMA Requests Table
CREATE TABLE IF NOT EXISTS `rma_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rma_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'RMA-YYYYMMDD-XXXX format',
  `customer_id` INT UNSIGNED NOT NULL,
  `transaction_id` INT UNSIGNED COMMENT 'Original sale transaction',
  `vendor_id` INT UNSIGNED COMMENT 'If returning to vendor',
  `rma_type` ENUM('customer_return', 'vendor_return', 'warranty_claim', 'defective_exchange') DEFAULT 'customer_return',
  `status` ENUM('pending', 'approved', 'rejected', 'received', 'refunded', 'exchanged', 'vendor_sent', 'vendor_received', 'completed', 'cancelled') DEFAULT 'pending',
  `reason` ENUM('defective', 'wrong_item', 'not_as_described', 'damaged_shipping', 'buyer_remorse', 'warranty_repair', 'other') NOT NULL,
  `reason_notes` TEXT,
  `requested_resolution` ENUM('refund', 'exchange', 'repair', 'credit') DEFAULT 'refund',
  `total_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Total value of return',
  `refund_amount` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Actual refund amount',
  `restocking_fee` DECIMAL(10,2) DEFAULT 0.00,
  `shipping_cost` DECIMAL(10,2) DEFAULT 0.00,

  -- Tracking
  `requested_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `approved_date` TIMESTAMP NULL,
  `received_date` TIMESTAMP NULL,
  `completed_date` TIMESTAMP NULL,
  `approved_by` INT UNSIGNED COMMENT 'Staff member who approved',
  `processed_by` INT UNSIGNED COMMENT 'Staff member who processed',

  -- Shipping info for return
  `return_tracking_number` VARCHAR(100),
  `return_carrier` VARCHAR(50),
  `return_label_cost` DECIMAL(10,2),

  -- Vendor RMA info
  `vendor_rma_number` VARCHAR(100) COMMENT 'Vendor\'s RMA number',
  `vendor_authorization_date` TIMESTAMP NULL,
  `vendor_credit_amount` DECIMAL(10,2),

  -- Notes and attachments
  `internal_notes` TEXT,
  `customer_notes` TEXT,
  `requires_inspection` BOOLEAN DEFAULT TRUE,
  `inspection_notes` TEXT,
  `inspection_photos` JSON COMMENT 'Array of photo URLs',

  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`),
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`),
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`),
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`),

  INDEX `idx_rma_number` (`rma_number`),
  INDEX `idx_customer` (`customer_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_rma_type` (`rma_type`),
  INDEX `idx_requested_date` (`requested_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='RMA request tracking for customer and vendor returns';

-- RMA Items Table (individual products being returned)
CREATE TABLE IF NOT EXISTS `rma_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rma_request_id` INT UNSIGNED NOT NULL,
  `transaction_item_id` INT UNSIGNED COMMENT 'Original transaction item',
  `product_id` INT UNSIGNED NOT NULL,
  `variant_id` INT UNSIGNED,
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `condition_received` ENUM('unopened', 'opened_unused', 'used_good', 'used_fair', 'damaged', 'defective') COMMENT 'Condition when received',
  `disposition` ENUM('restock', 'vendor_return', 'scrap', 'repair', 'pending') DEFAULT 'pending',
  `restocked` BOOLEAN DEFAULT FALSE,
  `restocked_date` TIMESTAMP NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`rma_request_id`) REFERENCES `rma_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`),

  INDEX `idx_rma_request` (`rma_request_id`),
  INDEX `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual items in an RMA request';

-- RMA Status History (audit trail)
CREATE TABLE IF NOT EXISTS `rma_status_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rma_request_id` INT UNSIGNED NOT NULL,
  `old_status` VARCHAR(50),
  `new_status` VARCHAR(50) NOT NULL,
  `changed_by` INT UNSIGNED,
  `notes` TEXT,
  `changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`rma_request_id`) REFERENCES `rma_requests`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`),

  INDEX `idx_rma_request` (`rma_request_id`),
  INDEX `idx_changed_at` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Audit trail for RMA status changes';

-- ============================================================================
-- PART 2: Product Import System
-- ============================================================================

-- Product Import Jobs Table
CREATE TABLE IF NOT EXISTS `product_import_jobs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `job_name` VARCHAR(255) NOT NULL,
  `import_type` ENUM('csv', 'excel', 'xml', 'json', 'api') DEFAULT 'csv',
  `source_file` VARCHAR(500) COMMENT 'Path to uploaded file',
  `vendor_id` INT UNSIGNED COMMENT 'Associated vendor if applicable',
  `status` ENUM('pending', 'mapping', 'validating', 'importing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',

  -- File info
  `file_size` INT UNSIGNED COMMENT 'File size in bytes',
  `total_rows` INT UNSIGNED DEFAULT 0,
  `header_row` INT DEFAULT 1 COMMENT 'Which row contains headers',

  -- Mapping configuration
  `field_mapping` JSON COMMENT 'Maps CSV columns to product fields',
  `default_values` JSON COMMENT 'Default values for unmapped fields',

  -- Import settings
  `update_existing` BOOLEAN DEFAULT FALSE COMMENT 'Update products if SKU exists',
  `match_field` ENUM('sku', 'barcode', 'vendor_sku') DEFAULT 'sku',
  `skip_duplicates` BOOLEAN DEFAULT TRUE,
  `auto_create_categories` BOOLEAN DEFAULT FALSE,
  `auto_create_vendors` BOOLEAN DEFAULT FALSE,

  -- Progress tracking
  `rows_processed` INT UNSIGNED DEFAULT 0,
  `rows_success` INT UNSIGNED DEFAULT 0,
  `rows_updated` INT UNSIGNED DEFAULT 0,
  `rows_skipped` INT UNSIGNED DEFAULT 0,
  `rows_failed` INT UNSIGNED DEFAULT 0,

  -- Results
  `error_log` JSON COMMENT 'Array of error messages',
  `imported_product_ids` JSON COMMENT 'Array of created product IDs',
  `updated_product_ids` JSON COMMENT 'Array of updated product IDs',

  -- Timestamps
  `started_at` TIMESTAMP NULL,
  `completed_at` TIMESTAMP NULL,
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),

  INDEX `idx_status` (`status`),
  INDEX `idx_vendor` (`vendor_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks product import jobs and their progress';

-- Product Import Preview Data (temp staging before import)
CREATE TABLE IF NOT EXISTS `product_import_preview` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `import_job_id` INT UNSIGNED NOT NULL,
  `row_number` INT NOT NULL,
  `raw_data` JSON NOT NULL COMMENT 'Original CSV row data',
  `mapped_data` JSON COMMENT 'Data after field mapping',
  `validation_status` ENUM('valid', 'warning', 'error') DEFAULT 'valid',
  `validation_messages` JSON COMMENT 'Array of validation messages',
  `will_create` BOOLEAN DEFAULT TRUE COMMENT 'Will create new product',
  `will_update` BOOLEAN DEFAULT FALSE COMMENT 'Will update existing product',
  `existing_product_id` INT UNSIGNED COMMENT 'ID if updating existing',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (`import_job_id`) REFERENCES `product_import_jobs`(`id`) ON DELETE CASCADE,

  INDEX `idx_import_job` (`import_job_id`),
  INDEX `idx_row_number` (`row_number`),
  INDEX `idx_validation_status` (`validation_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Preview data before actual import - cleared after import completes';

-- ============================================================================
-- PART 3: Extended Vendor Catalog Integration
-- ============================================================================

-- Vendor Price Lists (for price update imports)
CREATE TABLE IF NOT EXISTS `vendor_price_lists` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT UNSIGNED NOT NULL,
  `price_list_name` VARCHAR(255) NOT NULL,
  `effective_date` DATE NOT NULL,
  `expiration_date` DATE,
  `discount_percentage` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Overall discount %',
  `file_path` VARCHAR(500),
  `import_job_id` INT UNSIGNED COMMENT 'Link to import job if imported',
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`import_job_id`) REFERENCES `product_import_jobs`(`id`),

  INDEX `idx_vendor` (`vendor_id`),
  INDEX `idx_effective_date` (`effective_date`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Vendor price lists for bulk pricing updates';

-- ============================================================================
-- PART 4: Product Enhancements (Missing Shipping Fields)
-- ============================================================================

-- Add additional shipping-related fields to products table
ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `length` DECIMAL(8,2) COMMENT 'Length in inches' AFTER `dimensions`,
ADD COLUMN IF NOT EXISTS `width` DECIMAL(8,2) COMMENT 'Width in inches' AFTER `length`,
ADD COLUMN IF NOT EXISTS `height` DECIMAL(8,2) COMMENT 'Height in inches' AFTER `width`,
ADD COLUMN IF NOT EXISTS `dimension_unit` ENUM('in', 'cm', 'ft', 'm') DEFAULT 'in' AFTER `height`,
ADD COLUMN IF NOT EXISTS `package_weight` DECIMAL(8,2) COMMENT 'Shipping weight with packaging' AFTER `weight_unit`,
ADD COLUMN IF NOT EXISTS `ships_separately` BOOLEAN DEFAULT FALSE COMMENT 'Ships as separate package' AFTER `package_weight`,
ADD COLUMN IF NOT EXISTS `free_shipping` BOOLEAN DEFAULT FALSE AFTER `ships_separately`,
ADD COLUMN IF NOT EXISTS `shipping_class` VARCHAR(50) COMMENT 'Small parcel, freight, etc.' AFTER `free_shipping`,
ADD COLUMN IF NOT EXISTS `harmonized_code` VARCHAR(20) COMMENT 'HS code for international shipping' AFTER `shipping_class`,
ADD COLUMN IF NOT EXISTS `country_of_origin` VARCHAR(2) COMMENT 'ISO country code' AFTER `harmonized_code`;

-- ============================================================================
-- PART 5: RMA Settings
-- ============================================================================

-- Add RMA-related settings
INSERT INTO `settings` (`category`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`)
VALUES
  ('rma', 'rma_enabled', '1', 'boolean', 'Enable RMA system', NOW()),
  ('rma', 'return_window_days', '30', 'integer', 'Days allowed for returns after purchase', NOW()),
  ('rma', 'restocking_fee_percentage', '15', 'integer', 'Restocking fee percentage (0-100)', NOW()),
  ('rma', 'require_manager_approval', '1', 'boolean', 'Require manager approval for RMAs', NOW()),
  ('rma', 'auto_approve_defective', '0', 'boolean', 'Auto-approve defective item returns', NOW()),
  ('rma', 'email_notifications', '1', 'boolean', 'Send email notifications for RMA status changes', NOW()),
  ('rma', 'return_shipping_paid_by', 'customer', 'string', 'Who pays return shipping (customer/company)', NOW())
ON DUPLICATE KEY UPDATE
  `setting_type` = VALUES(`setting_type`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();

-- ============================================================================
-- PART 6: System Metadata
-- ============================================================================

INSERT INTO `system_metadata` (`meta_key`, `meta_value`, `updated_at`)
VALUES
  ('rma_system_enabled', 'true', NOW()),
  ('product_import_enabled', 'true', NOW()),
  ('last_rma_migration', '017_create_rma_and_import_systems', NOW()),
  ('import_max_file_size', '52428800', NOW()), -- 50MB in bytes
  ('import_allowed_types', '["csv", "xlsx", "xls", "xml", "json"]', NOW())
ON DUPLICATE KEY UPDATE
  `meta_value` = VALUES(`meta_value`),
  `updated_at` = NOW();

-- ============================================================================
-- Migration Complete
-- ============================================================================

-- Summary of what was created:
-- 1. RMA system: rma_requests, rma_items, rma_status_history
-- 2. Product import: product_import_jobs, product_import_preview
-- 3. Vendor integration: vendor_price_lists
-- 4. Enhanced product fields for shipping (dimensions, HS codes, etc.)
-- 5. RMA settings and system metadata
