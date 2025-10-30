-- ============================================================================
-- Migration: Create Advanced Inventory Management System
-- Created: 2024
-- Description: Automated reordering, forecasting, cycle counts, and purchase orders
-- ============================================================================

-- Product Reorder Rules
CREATE TABLE IF NOT EXISTS product_reorder_rules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    reorder_point INTEGER NOT NULL,  -- Trigger reorder when stock hits this level
    reorder_quantity INTEGER NOT NULL,  -- How much to order
    lead_time_days INTEGER DEFAULT 7,  -- How long until delivery
    safety_stock_days INTEGER DEFAULT 3,  -- Extra buffer stock
    is_active BOOLEAN DEFAULT 1,
    auto_create_po BOOLEAN DEFAULT 0,  -- Automatically create purchase orders?
    preferred_vendor_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (preferred_vendor_id) REFERENCES vendors(id) ON DELETE SET NULL,
    UNIQUE(product_id)
);

CREATE INDEX IF NOT EXISTS idx_product_reorder_rules_product ON product_reorder_rules(product_id);
CREATE INDEX IF NOT EXISTS idx_product_reorder_rules_active ON product_reorder_rules(is_active);

-- Inventory Cycle Counts
CREATE TABLE IF NOT EXISTS inventory_cycle_counts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    counted_by INTEGER,  -- user_id
    count_date DATE NOT NULL,
    expected_quantity INTEGER NOT NULL,
    actual_quantity INTEGER NOT NULL,
    variance INTEGER NOT NULL,  -- actual - expected
    variance_value DECIMAL(10,2),  -- Financial impact of variance
    notes TEXT,
    is_resolved BOOLEAN DEFAULT 0,
    resolved_by INTEGER,
    resolved_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (counted_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_inventory_cycle_counts_product ON inventory_cycle_counts(product_id);
CREATE INDEX IF NOT EXISTS idx_inventory_cycle_counts_date ON inventory_cycle_counts(count_date);
CREATE INDEX IF NOT EXISTS idx_inventory_cycle_counts_resolved ON inventory_cycle_counts(is_resolved);

-- Purchase Orders
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    vendor_id INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',  -- 'draft', 'sent', 'confirmed', 'received', 'cancelled'
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(10,2) DEFAULT 0.00,
    shipping DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT,
    created_by INTEGER,
    received_by INTEGER,
    auto_generated BOOLEAN DEFAULT 0,  -- Was this auto-created by reorder rules?
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_purchase_orders_vendor ON purchase_orders(vendor_id);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_status ON purchase_orders(status);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_order_date ON purchase_orders(order_date);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_number ON purchase_orders(po_number);

-- Purchase Order Line Items
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    purchase_order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity_ordered INTEGER NOT NULL,
    quantity_received INTEGER DEFAULT 0,
    unit_cost DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

CREATE INDEX IF NOT EXISTS idx_purchase_order_items_po ON purchase_order_items(purchase_order_id);
CREATE INDEX IF NOT EXISTS idx_purchase_order_items_product ON purchase_order_items(product_id);

-- Inventory Movement Categories
CREATE TABLE IF NOT EXISTS inventory_movement_types (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    affects_quantity BOOLEAN DEFAULT 1,
    direction VARCHAR(10),  -- 'in', 'out', 'adjust'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default movement types
INSERT OR IGNORE INTO inventory_movement_types (id, code, name, affects_quantity, direction) VALUES
(1, 'PURCHASE', 'Purchase Order Received', 1, 'in'),
(2, 'SALE', 'Sold to Customer', 1, 'out'),
(3, 'RETURN', 'Customer Return', 1, 'in'),
(4, 'DAMAGE', 'Damaged/Write-off', 1, 'out'),
(5, 'THEFT', 'Theft/Loss', 1, 'out'),
(6, 'ADJUSTMENT', 'Manual Adjustment', 1, 'adjust'),
(7, 'CYCLE_COUNT', 'Cycle Count Adjustment', 1, 'adjust'),
(8, 'TRANSFER', 'Location Transfer', 0, 'adjust'),
(9, 'RENTAL', 'Rented to Customer', 0, 'out'),
(10, 'RENTAL_RETURN', 'Rental Returned', 0, 'in');

-- ============================================================================
-- Migration Complete
-- ============================================================================
