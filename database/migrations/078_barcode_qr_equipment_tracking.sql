-- ================================================
-- Nautilus - Barcode/QR Code Equipment Tracking
-- Migration: 078_barcode_qr_equipment_tracking.sql
-- Description: Enhanced equipment tracking with barcode/QR scanning
-- ================================================

-- Equipment Barcodes/QR Codes
CREATE TABLE IF NOT EXISTS `equipment_barcodes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,

    -- Equipment Reference
    `equipment_id` INT UNSIGNED NULL COMMENT 'Links to rental_equipment or inventory item',
    `equipment_type` ENUM('rental', 'service', 'inventory', 'asset') NOT NULL,

    -- Barcode/QR Data
    `barcode_type` ENUM('CODE128', 'CODE39', 'EAN13', 'QR', 'DATAMATRIX') DEFAULT 'CODE128',
    `barcode_value` VARCHAR(255) NOT NULL,
    `qr_code_data` JSON NULL COMMENT 'Extended data for QR codes',

    -- Physical Tag
    `tag_number` VARCHAR(50) NULL COMMENT 'Physical tag/sticker number',
    `tag_location` VARCHAR(255) NULL COMMENT 'Where tag is affixed on equipment',

    -- Generation
    `barcode_image_path` VARCHAR(500) NULL,
    `qr_code_image_path` VARCHAR(500) NULL,
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `printed` BOOLEAN DEFAULT FALSE,
    `printed_at` TIMESTAMP NULL,

    -- Metadata
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `unique_barcode` (`barcode_value`),
    INDEX `idx_equipment` (`equipment_type`, `equipment_id`),
    INDEX `idx_tag` (`tag_number`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Barcode Scan History
CREATE TABLE IF NOT EXISTS `barcode_scan_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,

    -- Scan Details
    `barcode_id` INT UNSIGNED NOT NULL,
    `barcode_value` VARCHAR(255) NOT NULL,

    -- Scan Context
    `scan_action` ENUM('checkout', 'checkin', 'inventory', 'service', 'verify', 'other') NOT NULL,
    `scan_location` VARCHAR(255) NULL,

    -- Who Scanned
    `scanned_by_user_id` INT UNSIGNED NULL,
    `customer_id` INT UNSIGNED NULL COMMENT 'If checkout/checkin',

    -- Related Transaction
    `rental_id` INT UNSIGNED NULL,
    `service_record_id` INT UNSIGNED NULL,
    `transaction_id` INT UNSIGNED NULL,

    -- Scan Device
    `device_type` ENUM('mobile', 'handheld', 'desktop', 'pos') NULL,
    `device_id` VARCHAR(100) NULL,

    -- GPS if mobile scan
    `gps_latitude` DECIMAL(10, 8) NULL,
    `gps_longitude` DECIMAL(11, 8) NULL,

    -- Notes
    `notes` TEXT NULL,

    `scanned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`barcode_id`) REFERENCES `equipment_barcodes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`scanned_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,

    INDEX `idx_barcode` (`barcode_value`),
    INDEX `idx_action` (`scan_action`),
    INDEX `idx_scanned_at` (`scanned_at`),
    INDEX `idx_user` (`scanned_by_user_id`),
    INDEX `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QR Code Asset Tags (extended equipment info)
CREATE TABLE IF NOT EXISTS `asset_tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,

    -- Asset Identification
    `asset_number` VARCHAR(100) NOT NULL,
    `asset_name` VARCHAR(255) NOT NULL,
    `asset_category` ENUM('scuba', 'camera', 'boat', 'vehicle', 'facility', 'other') NOT NULL,

    -- Barcode Link
    `barcode_id` INT UNSIGNED NULL,

    -- Asset Details
    `make` VARCHAR(100) NULL,
    `model` VARCHAR(100) NULL,
    `serial_number` VARCHAR(255) NULL,
    `manufacture_date` DATE NULL,
    `purchase_date` DATE NULL,
    `purchase_price` DECIMAL(10,2) NULL,

    -- Current Status
    `status` ENUM('available', 'in_use', 'maintenance', 'retired', 'lost', 'damaged') DEFAULT 'available',
    `current_location` VARCHAR(255) NULL,
    `assigned_to_user_id` INT UNSIGNED NULL,

    -- Maintenance Schedule
    `last_service_date` DATE NULL,
    `next_service_due` DATE NULL,
    `service_interval_days` INT NULL,

    -- Depreciation
    `depreciation_rate_percent` DECIMAL(5,2) NULL,
    `current_value` DECIMAL(10,2) NULL,

    -- Insurance
    `insured` BOOLEAN DEFAULT FALSE,
    `insurance_value` DECIMAL(10,2) NULL,
    `insurance_policy_number` VARCHAR(100) NULL,

    -- Photos
    `photo_url` VARCHAR(500) NULL,

    -- Notes
    `description` TEXT NULL,
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`barcode_id`) REFERENCES `equipment_barcodes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    UNIQUE KEY `unique_asset_number` (`tenant_id`, `asset_number`),
    INDEX `idx_status` (`status`),
    INDEX `idx_category` (`asset_category`),
    INDEX `idx_next_service` (`next_service_due`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mobile Scan Sessions
CREATE TABLE IF NOT EXISTS `scan_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,

    `session_token` VARCHAR(100) NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,

    -- Session Purpose
    `session_type` ENUM('inventory_count', 'rental_checkout', 'rental_return', 'service_intake', 'audit') NOT NULL,

    -- Scans in this session
    `total_scans` INT DEFAULT 0,
    `successful_scans` INT DEFAULT 0,
    `failed_scans` INT DEFAULT 0,

    -- Status
    `status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',

    -- Timestamps
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    UNIQUE KEY `unique_session_token` (`session_token`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_started` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Barcode Print Queue
CREATE TABLE IF NOT EXISTS `barcode_print_queue` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,

    `barcode_id` INT UNSIGNED NOT NULL,

    -- Print Job Details
    `print_type` ENUM('barcode_label', 'qr_label', 'asset_tag', 'equipment_tag') NOT NULL,
    `label_size` VARCHAR(50) NULL COMMENT 'e.g., 2x1inch, 4x6inch',
    `copies` INT DEFAULT 1,

    -- Printer
    `printer_name` VARCHAR(100) NULL,

    -- Status
    `status` ENUM('pending', 'printing', 'completed', 'failed') DEFAULT 'pending',
    `printed_at` TIMESTAMP NULL,
    `error_message` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`barcode_id`) REFERENCES `equipment_barcodes`(`id`) ON DELETE CASCADE,

    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
