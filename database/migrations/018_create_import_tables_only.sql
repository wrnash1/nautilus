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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add product_import_previews alias if code uses plural
-- The code uses `product_import_previews` in DELETE query but `product_import_preview` in INSERT.
-- I should check ProductImportService.php again.
