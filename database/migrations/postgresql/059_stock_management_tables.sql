-- Stock Management Tables
-- Advanced inventory tracking, stock counts, transfers, and forecasting

-- Stock Counts table (for physical inventory audits)
CREATE TABLE IF NOT EXISTS stock_counts (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    count_date DATE NOT NULL,
    counted_by BIGINT UNSIGNED,
    status ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'in_progress',
    notes TEXT,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (counted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_count_date (count_date),
    INDEX idx_status (status)
);

-- Stock Count Items (individual product counts)
CREATE TABLE IF NOT EXISTS stock_count_items (
    id INTEGER  PRIMARY KEY,
    stock_count_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    system_quantity INT NOT NULL DEFAULT 0,
    counted_quantity INT NOT NULL DEFAULT 0,
    variance INT NOT NULL DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (stock_count_id) REFERENCES stock_counts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_stock_count (stock_count_id),
    INDEX idx_product (product_id),
    INDEX idx_variance (variance)
);

-- Stock Transfers (between locations/warehouses)
CREATE TABLE IF NOT EXISTS stock_transfers (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    product_id INTEGER NOT NULL,
    from_location VARCHAR(100) NOT NULL,
    to_location VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    transfer_date DATE NOT NULL,
    transferred_by BIGINT UNSIGNED,
    status ENUM('pending', 'in_transit', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (transferred_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_product_id (product_id),
    INDEX idx_from_location (from_location),
    INDEX idx_to_location (to_location),
    INDEX idx_transfer_date (transfer_date),
    INDEX idx_status (status)
);

-- Purchase Orders
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    vendor_id BIGINT UNSIGNED,
    order_date DATE NOT NULL,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    status ENUM('draft', 'submitted', 'approved', 'ordered', 'received', 'cancelled') DEFAULT 'draft',
    subtotal DECIMAL(12, 2) DEFAULT 0.00,
    tax DECIMAL(12, 2) DEFAULT 0.00,
    shipping DECIMAL(12, 2) DEFAULT 0.00,
    total DECIMAL(12, 2) DEFAULT 0.00,
    notes TEXT,
    created_by BIGINT UNSIGNED,
    approved_by BIGINT UNSIGNED,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_po_number (po_number),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status)
);

-- Purchase Order Items
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INTEGER  PRIMARY KEY,
    purchase_order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity_ordered INT NOT NULL,
    quantity_received INT DEFAULT 0,
    unit_cost DECIMAL(10, 2) NOT NULL,
    line_total DECIMAL(12, 2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_purchase_order (purchase_order_id),
    INDEX idx_product (product_id)
);

-- Vendors/Suppliers
CREATE TABLE IF NOT EXISTS vendors (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    vendor_name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    payment_terms VARCHAR(100),
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_vendor_name (vendor_name),
    INDEX idx_is_active (is_active)
);

-- Add vendor_id foreign key to purchase_orders if not exists
-- Note: This FK is added after both tables are created
ALTER TABLE purchase_orders
ADD CONSTRAINT IF NOT EXISTS fk_vendor
FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL;

-- Stock Locations/Warehouses
CREATE TABLE IF NOT EXISTS stock_locations (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    location_name VARCHAR(100) NOT NULL,
    location_type ENUM('warehouse', 'store', 'vehicle', 'other') DEFAULT 'warehouse',
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_location_name (location_name),
    INDEX idx_is_active (is_active)
);

-- Product Stock by Location
CREATE TABLE IF NOT EXISTS product_stock_locations (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    product_id INTEGER NOT NULL,
    location_id INTEGER NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES stock_locations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_location (product_id, location_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_product_id (product_id),
    INDEX idx_location_id (location_id)
);

-- Inventory Alerts/Notifications
CREATE TABLE IF NOT EXISTS inventory_alerts (
    id INTEGER  PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    product_id INTEGER NOT NULL,
    alert_type ENUM('low_stock', 'overstock', 'expiring_soon', 'stockout') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    message TEXT NOT NULL,
    is_acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by INTEGER NULL,
    acknowledged_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (acknowledged_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_product_id (product_id),
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_acknowledged (is_acknowledged),
    INDEX idx_created_at (created_at)
);

-- Add indexes to existing inventory_adjustments table if not present
ALTER TABLE inventory_adjustments ADD INDEX IF NOT EXISTS idx_adjustment_type (adjustment_type);
ALTER TABLE inventory_adjustments ADD INDEX IF NOT EXISTS idx_adjusted_at (adjusted_at);
ALTER TABLE inventory_adjustments ADD INDEX IF NOT EXISTS idx_adjusted_by (adjusted_by);
