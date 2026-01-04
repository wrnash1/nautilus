SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `reorder_suggestions`;
DROP TABLE IF EXISTS `inventory_count_items`;
DROP TABLE IF EXISTS `inventory_counts`;
DROP TABLE IF EXISTS `stock_transfer_items`;
DROP TABLE IF EXISTS `stock_transfers`;
DROP TABLE IF EXISTS `inventory_movements`;
DROP TABLE IF EXISTS `serialized_inventory`;
DROP TABLE IF EXISTS `inventory_stock_levels`;
DROP TABLE IF EXISTS `product_master`;
DROP TABLE IF EXISTS `inventory_locations`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `reorder_suggestions`;
DROP TABLE IF EXISTS `inventory_count_items`;
DROP TABLE IF EXISTS `inventory_counts`;
DROP TABLE IF EXISTS `stock_transfer_items`;
DROP TABLE IF EXISTS `stock_transfers`;
DROP TABLE IF EXISTS `inventory_movements`;
DROP TABLE IF EXISTS `serialized_inventory`;
DROP TABLE IF EXISTS `inventory_stock_levels`;
DROP TABLE IF EXISTS `product_master`;
DROP TABLE IF EXISTS `inventory_locations`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `reorder_suggestions`;
DROP TABLE IF EXISTS `inventory_count_items`;
DROP TABLE IF EXISTS `inventory_counts`;
DROP TABLE IF EXISTS `stock_transfer_items`;
DROP TABLE IF EXISTS `stock_transfers`;
DROP TABLE IF EXISTS `inventory_movements`;
DROP TABLE IF EXISTS `serialized_inventory`;
DROP TABLE IF EXISTS `inventory_stock_levels`;
DROP TABLE IF EXISTS `product_master`;
DROP TABLE IF EXISTS `inventory_locations`;

-- =====================================================
-- Advanced Inventory Control System
-- Complete inventory management with RFID, barcodes, multi-location, and automated reordering
-- =====================================================

-- Inventory Locations/Warehouses
CREATE TABLE IF NOT EXISTS `inventory_locations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `location_name` VARCHAR(255) NOT NULL,
    `location_type` ENUM('store', 'warehouse', 'boat', 'van', 'storage', 'consignment') NOT NULL,

    -- Address
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(50) NULL,
    `zip` VARCHAR(20) NULL,
    `country` VARCHAR(100) DEFAULT 'USA',

    -- Contact
    `manager_id` BIGINT UNSIGNED NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(255) NULL,

    -- Capacity
    `total_capacity_sqft` BIGINT UNSIGNED NULL,
    `storage_zones` JSON NULL COMMENT 'Different storage areas within location',

    -- Settings
    `is_primary` BOOLEAN DEFAULT FALSE,
    `allows_sales` BOOLEAN DEFAULT TRUE,
    `allows_receiving` BOOLEAN DEFAULT TRUE,
    `allows_transfers` BOOLEAN DEFAULT TRUE,
    `requires_security_clearance` BOOLEAN DEFAULT FALSE,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_type (`tenant_id`, `location_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Master (extends existing products table)
CREATE TABLE IF NOT EXISTS `product_master` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NULL COMMENT 'Link to existing products table',

    -- Identification
    `sku` VARCHAR(100) NOT NULL,
    `upc` VARCHAR(50) NULL,
    `barcode` VARCHAR(100) NULL,
    `rfid_tag` VARCHAR(100) NULL,
    `manufacturer_part_number` VARCHAR(100) NULL,

    -- Product Info
    `product_name` VARCHAR(255) NOT NULL,
    `product_category` VARCHAR(100) NULL,
    `product_subcategory` VARCHAR(100) NULL,
    `brand` VARCHAR(100) NULL,
    `manufacturer` VARCHAR(255) NULL,

    -- Description
    `description` TEXT NULL,
    `specifications` JSON NULL,
    `features` JSON NULL,

    -- Inventory Type
    `inventory_type` ENUM('physical', 'service', 'digital', 'rental', 'consumable', 'kit') NOT NULL DEFAULT 'physical',
    `is_serialized` BOOLEAN DEFAULT FALSE COMMENT 'Track individual serial numbers',
    `is_lot_tracked` BOOLEAN DEFAULT FALSE COMMENT 'Track by lot/batch number',

    -- Physical Attributes
    `weight_lbs` DECIMAL(10, 3) NULL,
    `length_inches` DECIMAL(10, 2) NULL,
    `width_inches` DECIMAL(10, 2) NULL,
    `height_inches` DECIMAL(10, 2) NULL,
    `volume_cubic_ft` DECIMAL(10, 3) NULL,

    -- Packaging
    `units_per_case` INT NULL,
    `case_upc` VARCHAR(50) NULL,
    `inner_pack_qty` INT NULL,

    -- Pricing
    `cost` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `average_cost` DECIMAL(10, 2) NULL COMMENT 'Weighted average',
    `last_cost` DECIMAL(10, 2) NULL,
    `retail_price` DECIMAL(10, 2) NULL,
    `wholesale_price` DECIMAL(10, 2) NULL,
    `rental_price_daily` DECIMAL(10, 2) NULL,
    `msrp` DECIMAL(10, 2) NULL,

    -- Margins
    `markup_percentage` DECIMAL(5, 2) NULL,
    `margin_percentage` DECIMAL(5, 2) NULL,

    -- Inventory Control
    `reorder_point` INT DEFAULT 5,
    `reorder_quantity` INT DEFAULT 10,
    `min_stock_level` INT DEFAULT 0,
    `max_stock_level` INT DEFAULT 100,
    `safety_stock` INT DEFAULT 0,
    `economic_order_qty` INT NULL COMMENT 'EOQ calculation',

    -- Lead Times
    `lead_time_days` INT DEFAULT 7,
    `supplier_id` BIGINT UNSIGNED NULL,
    `preferred_vendor_id` BIGINT UNSIGNED NULL,
    `backup_vendor_id` BIGINT UNSIGNED NULL,

    -- Perishability
    `is_perishable` BOOLEAN DEFAULT FALSE,
    `shelf_life_days` INT NULL,
    `requires_expiry_tracking` BOOLEAN DEFAULT FALSE,

    -- Maintenance (for rental equipment)
    `requires_maintenance` BOOLEAN DEFAULT FALSE,
    `maintenance_interval_days` INT NULL,
    `maintenance_interval_uses` INT NULL,
    `last_maintenance_date` DATE NULL,

    -- Tax & Accounting
    `tax_category` VARCHAR(100) NULL,
    `is_taxable` BOOLEAN DEFAULT TRUE,
    `accounting_code` VARCHAR(50) NULL,
    `cogs_account` VARCHAR(50) NULL,

    -- Images & Media
    `primary_image_url` VARCHAR(500) NULL,
    `gallery_images` JSON NULL,
    `manual_pdf_url` VARCHAR(500) NULL,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_discontinued` BOOLEAN DEFAULT FALSE,
    `discontinued_date` DATE NULL,
    `replacement_product_id` BIGINT UNSIGNED NULL,

    -- Meta
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL,
    UNIQUE KEY unique_tenant_sku (`tenant_id`, `sku`),
    INDEX idx_barcode (`barcode`),
    INDEX idx_rfid (`rfid_tag`),
    INDEX idx_category (`product_category`, `product_subcategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Levels by Location
CREATE TABLE IF NOT EXISTS `inventory_stock_levels` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `location_id` BIGINT UNSIGNED NOT NULL,

    -- Quantities
    `quantity_on_hand` INT NOT NULL DEFAULT 0,
    `quantity_available` INT NOT NULL DEFAULT 0 COMMENT 'On hand - reserved',
    `quantity_reserved` INT DEFAULT 0 COMMENT 'Allocated to orders',
    `quantity_on_order` INT DEFAULT 0 COMMENT 'In transit from suppliers',
    `quantity_in_transfer` INT DEFAULT 0 COMMENT 'Being transferred between locations',
    `quantity_damaged` INT DEFAULT 0,
    `quantity_lost` INT DEFAULT 0,

    -- Storage Location
    `storage_zone` VARCHAR(100) NULL COMMENT 'Aisle, bin, shelf location',
    `bin_location` VARCHAR(50) NULL,

    -- Valuation
    `total_value_cost` DECIMAL(12, 2) NULL COMMENT 'Qty * avg cost',
    `total_value_retail` DECIMAL(12, 2) NULL,

    -- Last Activity
    `last_counted_at` DATETIME NULL,
    `last_received_at` DATETIME NULL,
    `last_sold_at` DATETIME NULL,
    `last_movement_at` DATETIME NULL,

    -- Reorder Status
    `needs_reorder` BOOLEAN DEFAULT FALSE,
    `reorder_triggered_at` DATETIME NULL,

    -- Cycle Count
    `cycle_count_frequency` ENUM('daily', 'weekly', 'monthly', 'quarterly', 'annual') DEFAULT 'monthly',
    `next_cycle_count_date` DATE NULL,

    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product_master`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE CASCADE,
    UNIQUE KEY unique_product_location (`product_id`, `location_id`),
    INDEX idx_needs_reorder (`needs_reorder`),
    INDEX idx_location (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Serialized Inventory (individual items with serial numbers)
CREATE TABLE IF NOT EXISTS `serialized_inventory` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `location_id` BIGINT UNSIGNED NULL,

    -- Serial Info
    `serial_number` VARCHAR(100) NOT NULL,
    `lot_number` VARCHAR(100) NULL,
    `rfid_tag_id` VARCHAR(100) NULL,

    -- Dates
    `manufactured_date` DATE NULL,
    `received_date` DATE NOT NULL,
    `expiry_date` DATE NULL,
    `warranty_expiry_date` DATE NULL,

    -- Condition
    `condition` ENUM('new', 'like_new', 'good', 'fair', 'poor', 'refurbished', 'damaged') DEFAULT 'new',
    `condition_notes` TEXT NULL,

    -- Status
    `status` ENUM('in_stock', 'sold', 'rented', 'reserved', 'in_service', 'damaged', 'lost', 'returned') DEFAULT 'in_stock',
    `status_changed_at` DATETIME NULL,

    -- Ownership
    `customer_id` BIGINT UNSIGNED NULL COMMENT 'If sold or rented to customer',
    `order_id` BIGINT UNSIGNED NULL,
    `rental_id` BIGINT UNSIGNED NULL,

    -- Service History
    `total_rental_days` INT DEFAULT 0,
    `total_service_events` INT DEFAULT 0,
    `last_serviced_date` DATE NULL,
    `next_service_due_date` DATE NULL,

    -- Cost Tracking
    `acquisition_cost` DECIMAL(10, 2) NULL,
    `current_value` DECIMAL(10, 2) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product_master`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    UNIQUE KEY unique_serial (`tenant_id`, `serial_number`),
    INDEX idx_rfid (`rfid_tag_id`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Movements/Transactions
CREATE TABLE IF NOT EXISTS `inventory_movements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,

    -- Movement Details
    `movement_type` ENUM('receipt', 'sale', 'transfer', 'adjustment', 'return', 'damage', 'loss', 'found', 'rental_out', 'rental_return', 'cycle_count', 'scrap') NOT NULL,
    `movement_date` DATETIME NOT NULL,
    `transaction_reference` VARCHAR(100) NULL COMMENT 'PO, Order, Transfer ID',

    -- Locations
    `from_location_id` BIGINT UNSIGNED NULL,
    `to_location_id` BIGINT UNSIGNED NULL,

    -- Quantities
    `quantity` INT NOT NULL,
    `quantity_before` INT NULL,
    `quantity_after` INT NULL,

    -- Serialized Items
    `serial_numbers` JSON NULL COMMENT 'Array of serial numbers involved',

    -- Cost
    `unit_cost` DECIMAL(10, 2) NULL,
    `total_cost` DECIMAL(10, 2) NULL,

    -- Reason & Notes
    `reason_code` VARCHAR(50) NULL,
    `notes` TEXT NULL,

    -- Audit
    `performed_by` BIGINT UNSIGNED NULL COMMENT 'User ID',
    `approved_by` BIGINT UNSIGNED NULL,
    `requires_approval` BOOLEAN DEFAULT FALSE,
    `approval_status` ENUM('pending', 'approved', 'rejected') NULL,

    -- Related Records
    `order_id` BIGINT UNSIGNED NULL,
    `purchase_order_id` BIGINT UNSIGNED NULL,
    `transfer_id` BIGINT UNSIGNED NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product_master`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`to_location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    INDEX idx_product_date (`product_id`, `movement_date`),
    INDEX idx_movement_type (`movement_type`),
    INDEX idx_location (`from_location_id`, `to_location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Transfers Between Locations
CREATE TABLE IF NOT EXISTS `stock_transfers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `transfer_number` VARCHAR(50) NOT NULL UNIQUE,

    -- Locations
    `from_location_id` BIGINT UNSIGNED NOT NULL,
    `to_location_id` BIGINT UNSIGNED NOT NULL,

    -- Status
    `status` ENUM('draft', 'pending', 'in_transit', 'received', 'cancelled') DEFAULT 'draft',
    `created_date` DATE NOT NULL,
    `shipped_date` DATE NULL,
    `expected_arrival_date` DATE NULL,
    `received_date` DATE NULL,

    -- Shipping
    `shipping_method` VARCHAR(100) NULL,
    `tracking_number` VARCHAR(100) NULL,
    `carrier` VARCHAR(100) NULL,
    `shipping_cost` DECIMAL(10, 2) DEFAULT 0.00,

    -- Totals
    `total_items` INT DEFAULT 0,
    `total_quantity` INT DEFAULT 0,
    `total_value` DECIMAL(12, 2) DEFAULT 0.00,

    -- Personnel
    `initiated_by` BIGINT UNSIGNED NULL,
    `shipped_by` BIGINT UNSIGNED NULL,
    `received_by` BIGINT UNSIGNED NULL,

    -- Discrepancies
    `has_discrepancies` BOOLEAN DEFAULT FALSE,
    `discrepancy_notes` TEXT NULL,

    -- Notes
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`from_location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`to_location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE RESTRICT,
    INDEX idx_status (`status`),
    INDEX idx_locations (`from_location_id`, `to_location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Transfer Line Items
CREATE TABLE IF NOT EXISTS `stock_transfer_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transfer_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,

    -- Quantities
    `quantity_requested` INT NOT NULL,
    `quantity_shipped` INT DEFAULT 0,
    `quantity_received` INT DEFAULT 0,
    `quantity_damaged` INT DEFAULT 0,

    -- Serialized
    `serial_numbers` JSON NULL,

    -- Cost
    `unit_cost` DECIMAL(10, 2) NULL,
    `total_cost` DECIMAL(10, 2) NULL,

    -- Notes
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product_master`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Physical Inventory Counts
CREATE TABLE IF NOT EXISTS `inventory_counts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `count_name` VARCHAR(255) NOT NULL,
    `count_type` ENUM('full', 'partial', 'cycle', 'spot') NOT NULL,

    -- Scope
    `location_id` BIGINT UNSIGNED NULL,
    `category` VARCHAR(100) NULL,
    `products_to_count` JSON NULL COMMENT 'Specific product IDs',

    -- Schedule
    `scheduled_date` DATE NOT NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,

    -- Status
    `status` ENUM('scheduled', 'in_progress', 'completed', 'reconciled', 'cancelled') DEFAULT 'scheduled',

    -- Counts
    `total_products` INT DEFAULT 0,
    `products_counted` INT DEFAULT 0,
    `discrepancies_found` INT DEFAULT 0,

    -- Variances
    `total_variance_qty` INT DEFAULT 0,
    `total_variance_value` DECIMAL(12, 2) DEFAULT 0.00,

    -- Personnel
    `assigned_to` JSON NULL COMMENT 'Array of user IDs',
    `completed_by` BIGINT UNSIGNED NULL,
    `reviewed_by` BIGINT UNSIGNED NULL,

    -- Notes
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    INDEX idx_status_date (`status`, `scheduled_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Physical Count Details
CREATE TABLE IF NOT EXISTS `inventory_count_items` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `count_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `location_id` BIGINT UNSIGNED NULL,

    -- Expected vs Actual
    `expected_quantity` INT NOT NULL,
    `counted_quantity` INT NULL,
    `variance` INT NULL,

    -- Multiple Counts
    `first_count` INT NULL,
    `second_count` INT NULL,
    `third_count` INT NULL COMMENT 'For discrepancies',

    -- Serialized
    `serial_numbers_expected` JSON NULL,
    `serial_numbers_found` JSON NULL,
    `serial_numbers_missing` JSON NULL,

    -- Cost Impact
    `unit_cost` DECIMAL(10, 2) NULL,
    `variance_value` DECIMAL(10, 2) NULL,

    -- Status
    `status` ENUM('pending', 'counted', 'reconciled', 'needs_recount') DEFAULT 'pending',
    `discrepancy_reason` TEXT NULL,

    -- Audit
    `counted_by` BIGINT UNSIGNED NULL,
    `counted_at` DATETIME NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`count_id`) REFERENCES `inventory_counts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product_master`(`id`) ON DELETE CASCADE,
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reorder Suggestions
CREATE TABLE IF NOT EXISTS `reorder_suggestions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `location_id` BIGINT UNSIGNED NULL,

    -- Current State
    `current_stock` INT NOT NULL,
    `reorder_point` INT NOT NULL,
    `suggested_quantity` INT NOT NULL,

    -- Analysis
    `avg_daily_usage` DECIMAL(8, 2) NULL,
    `days_until_stockout` INT NULL,
    `priority` ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',

    -- Status
    `status` ENUM('pending', 'ordered', 'received', 'dismissed') DEFAULT 'pending',
    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ordered_at` DATETIME NULL,
    `purchase_order_id` BIGINT UNSIGNED NULL,
    `dismissed_at` DATETIME NULL,
    `dismissed_reason` TEXT NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `product_master`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE CASCADE,
    INDEX idx_status_priority (`status`, `priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sample Data
-- =====================================================

-- Sample Locations
INSERT INTO `inventory_locations` (
    `tenant_id`, `location_name`, `location_type`, `address`, `city`, `state`, `zip`, `is_primary`, `allows_sales`
) VALUES
(1, 'Main Retail Store', 'store', '123 Ocean Ave', 'San Diego', 'CA', '92101', TRUE, TRUE),
(1, 'Warehouse - Equipment Storage', 'warehouse', '456 Industrial Blvd', 'San Diego', 'CA', '92102', FALSE, FALSE),
(1, 'Dive Boat - Neptune I', 'boat', 'Marina Slip 23', 'San Diego', 'CA', '92101', FALSE, TRUE),
(1, 'Mobile Service Van', 'van', NULL, NULL, NULL, NULL, FALSE, TRUE);

-- Sample Products
INSERT INTO `product_master` (
    `tenant_id`, `sku`, `upc`, `product_name`, `product_category`, `brand`, `inventory_type`,
    `cost`, `retail_price`, `reorder_point`, `reorder_quantity`, `is_serialized`
) VALUES
-- Regulators
(1, 'REG-001', '123456789012', 'Scubapro MK25 EVO/S620 Ti Regulator', 'Regulators', 'Scubapro', 'physical', 850.00, 1299.00, 3, 5, TRUE),
(1, 'REG-002', '123456789013', 'Atomic Aquatics T3 Regulator', 'Regulators', 'Atomic', 'physical', 1200.00, 1850.00, 2, 3, TRUE),

-- BCDs
(1, 'BCD-001', '123456789014', 'Scubapro Hydros Pro BCD - Medium', 'BCDs', 'Scubapro', 'physical', 450.00, 699.00, 4, 6, FALSE),
(1, 'BCD-002', '123456789015', 'Zeagle Ranger BCD - Large', 'BCDs', 'Zeagle', 'physical', 380.00, 599.00, 3, 5, FALSE),

-- Dive Computers
(1, 'COMP-001', '123456789016', 'Shearwater Perdix AI', 'Computers', 'Shearwater', 'physical', 850.00, 1299.00, 2, 4, TRUE),
(1, 'COMP-002', '123456789017', 'Suunto D5', 'Computers', 'Suunto', 'physical', 420.00, 649.00, 3, 5, TRUE),

-- Tanks
(1, 'TANK-AL80', '123456789018', 'Aluminum 80 cu ft Tank', 'Tanks', 'Catalina', 'physical', 180.00, 299.00, 10, 10, TRUE),
(1, 'TANK-ST100', '123456789019', 'Steel 100 cu ft Tank', 'Tanks', 'Faber', 'physical', 320.00, 499.00, 5, 5, TRUE),

-- Wetsuits
(1, 'WET-3MM-M', '123456789020', '3mm Wetsuit - Medium', 'Wetsuits', 'Henderson', 'physical', 120.00, 199.00, 5, 10, FALSE),
(1, 'WET-5MM-L', '123456789021', '5mm Wetsuit - Large', 'Wetsuits', 'Bare', 'physical', 180.00, 299.00, 4, 8, FALSE),

-- Consumables
(1, 'O-RING-KIT', '123456789022', 'O-Ring Service Kit', 'Parts', 'Generic', 'consumable', 12.00, 24.99, 20, 50, FALSE),
(1, 'TANK-VALVE', '123456789023', 'Tank Valve - DIN', 'Parts', 'XS Scuba', 'physical', 45.00, 79.99, 10, 20, FALSE);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;