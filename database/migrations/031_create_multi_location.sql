-- ============================================================================
-- Migration: Create Multi-Location/Warehouse Management System
-- Created: 2024
-- Description: Multi-location inventory tracking, transfers, and management
-- ============================================================================

-- Store/Warehouse Locations
CREATE TABLE IF NOT EXISTS locations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,  -- Short code for location (e.g., 'MAIN', 'WH1')
    location_type VARCHAR(20) NOT NULL,  -- 'store', 'warehouse', 'supplier', 'consignment'
    address VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(50),
    zip VARCHAR(20),
    country VARCHAR(50) DEFAULT 'USA',
    phone VARCHAR(50),
    email VARCHAR(200),
    manager_user_id INT UNSIGNED,
    is_active TINYINT(1) DEFAULT 1,
    is_default TINYINT(1) DEFAULT 0,  -- Default location for new products
    can_sell TINYINT(1) DEFAULT 1,  -- Can this location sell products?
    can_ship TINYINT(1) DEFAULT 1,  -- Can this location ship products?
    priority INT DEFAULT 0,  -- Fulfillment priority (higher = preferred)
    operating_hours TEXT,  -- JSON object with hours
    metadata TEXT,  -- JSON for additional settings
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_locations_code ON locations(code);
CREATE INDEX IF NOT EXISTS idx_locations_type ON locations(location_type);
CREATE INDEX IF NOT EXISTS idx_locations_active ON locations(is_active);

-- Per-Location Inventory Levels
CREATE TABLE IF NOT EXISTS location_inventory (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity_on_hand INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_reserved INT UNSIGNED NOT NULL DEFAULT 0,  -- Reserved for orders
    quantity_available INT UNSIGNED NOT NULL DEFAULT 0,  -- on_hand - reserved
    reorder_point INTEGER,  -- Location-specific reorder point
    reorder_quantity INTEGER,  -- Location-specific reorder quantity
    min_stock_level INT DEFAULT 0,
    max_stock_level INTEGER,
    bin_location VARCHAR(50),  -- Physical bin/shelf location
    last_counted_at TIMESTAMP,  -- Last cycle count date
    last_count_variance INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE(location_id, product_id)
);

CREATE INDEX IF NOT EXISTS idx_location_inventory_location ON location_inventory(location_id);
CREATE INDEX IF NOT EXISTS idx_location_inventory_product ON location_inventory(product_id);
CREATE INDEX IF NOT EXISTS idx_location_inventory_available ON location_inventory(quantity_available);

-- Inventory Transfers Between Locations
CREATE TABLE IF NOT EXISTS inventory_transfers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_number VARCHAR(50) NOT NULL UNIQUE,
    from_location_id INT UNSIGNED NOT NULL,
    to_location_id INT UNSIGNED NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- 'pending', 'in_transit', 'received', 'cancelled'
    transfer_type VARCHAR(20) DEFAULT 'standard',  -- 'standard', 'emergency', 'rebalance'
    requested_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    shipped_by INT UNSIGNED,
    received_by INT UNSIGNED,
    requested_date DATE NOT NULL,
    approved_date DATE,
    shipped_date DATE,
    expected_delivery_date DATE,
    received_date DATE,
    notes TEXT,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tracking_number VARCHAR(100),
    carrier VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_location_id) REFERENCES locations(id) ON DELETE RESTRICT,
    FOREIGN KEY (to_location_id) REFERENCES locations(id) ON DELETE RESTRICT,
    FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (shipped_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_inventory_transfers_from ON inventory_transfers(from_location_id);
CREATE INDEX IF NOT EXISTS idx_inventory_transfers_to ON inventory_transfers(to_location_id);
CREATE INDEX IF NOT EXISTS idx_inventory_transfers_status ON inventory_transfers(status);
CREATE INDEX IF NOT EXISTS idx_inventory_transfers_number ON inventory_transfers(transfer_number);

-- Transfer Line Items
CREATE TABLE IF NOT EXISTS inventory_transfer_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transfer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity_requested INT UNSIGNED NOT NULL,
    quantity_shipped INT DEFAULT 0,
    quantity_received INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transfer_id) REFERENCES inventory_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_inventory_transfer_items_transfer ON inventory_transfer_items(transfer_id);
CREATE INDEX IF NOT EXISTS idx_inventory_transfer_items_product ON inventory_transfer_items(product_id);

-- Location-Specific Inventory Adjustments
CREATE TABLE IF NOT EXISTS location_inventory_adjustments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    adjustment_type VARCHAR(50) NOT NULL,  -- 'cycle_count', 'damage', 'theft', 'transfer', 'return', 'manual'
    quantity_change INT UNSIGNED NOT NULL,  -- Positive or negative
    quantity_before INT UNSIGNED NOT NULL,
    quantity_after INT UNSIGNED NOT NULL,
    cost_per_unit DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    reason TEXT,
    reference_number VARCHAR(100),  -- Transfer #, cycle count #, etc.
    adjusted_by INT UNSIGNED,
    approved_by INT UNSIGNED,
    adjustment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_location_inv_adjustments_location ON location_inventory_adjustments(location_id);
CREATE INDEX IF NOT EXISTS idx_location_inv_adjustments_product ON location_inventory_adjustments(product_id);
CREATE INDEX IF NOT EXISTS idx_location_inv_adjustments_type ON location_inventory_adjustments(adjustment_type);
CREATE INDEX IF NOT EXISTS idx_location_inv_adjustments_date ON location_inventory_adjustments(adjustment_date);

-- Insert default main location
INSERT IGNORE INTO locations (id, name, code, location_type, is_active, is_default, can_sell, can_ship, priority) VALUES
(1, 'Main Store', 'MAIN', 'store', 1, 1, 1, 1, 100);

-- Migrate existing product inventory to location_inventory
INSERT IGNORE INTO location_inventory (location_id, product_id, quantity_on_hand, quantity_available)
SELECT
    1 AS location_id,
    id AS product_id,
    stock_quantity AS quantity_on_hand,
    stock_quantity AS quantity_available
FROM products
WHERE stock_quantity IS NOT NULL;

-- ============================================================================
-- Migration Complete
-- ============================================================================
