-- ================================================
-- Nautilus V6 - Serial Number & Barcode Tracking
-- Migration: 023_serial_number_tracking.sql
-- Description: Track individual items by serial/barcode
-- ================================================

-- Serial Numbers Table
CREATE TABLE IF NOT EXISTS serial_numbers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    serial_number VARCHAR(100) NOT NULL,
    barcode VARCHAR(100),
    status ENUM('available', 'sold', 'rented', 'reserved', 'service', 'damaged', 'lost') DEFAULT 'available',
    condition_rating TINYINT COMMENT '1-10 condition rating',
    purchase_date DATE,
    purchase_cost DECIMAL(10,2),
    warranty_expiry DATE,
    last_service_date DATE,
    next_service_due DATE,
    location VARCHAR(100) COMMENT 'Storage location/shelf',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_serial (serial_number),
    UNIQUE KEY unique_barcode (barcode),
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    INDEX idx_barcode (barcode),
    INDEX idx_serial_number (serial_number),
    INDEX idx_next_service (next_service_due),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Serial Number History (tracking events)
CREATE TABLE IF NOT EXISTS serial_number_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serial_number_id BIGINT UNSIGNED NOT NULL,
    event_type ENUM('created', 'sold', 'rented', 'returned', 'service_in', 'service_out',
                    'status_change', 'location_change', 'condition_change', 'warranty_claim') NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    old_location VARCHAR(100),
    new_location VARCHAR(100),
    transaction_id BIGINT UNSIGNED COMMENT 'Related transaction if applicable',
    rental_id INT UNSIGNED COMMENT 'Related rental if applicable',
    work_order_id INT UNSIGNED COMMENT 'Related work order if applicable',
    performed_by INT UNSIGNED COMMENT 'Staff member who performed action',
    notes TEXT,
    event_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_serial_number_id (serial_number_id),
    INDEX idx_event_type (event_type),
    INDEX idx_event_date (event_date),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_rental_id (rental_id),
    FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add serial number reference to rental reservation items
ALTER TABLE rental_reservation_items
ADD COLUMN IF NOT EXISTS serial_number_id BIGINT UNSIGNED AFTER equipment_id,
ADD INDEX idx_rental_items_serial (serial_number_id);

-- Add serial number reference to transaction items
ALTER TABLE transaction_items
ADD COLUMN IF NOT EXISTS serial_number_id BIGINT UNSIGNED AFTER product_id,
ADD INDEX idx_trans_items_serial (serial_number_id);

-- Add serial number reference to work orders
ALTER TABLE work_orders
ADD COLUMN IF NOT EXISTS serial_number_id BIGINT UNSIGNED AFTER customer_id,
ADD INDEX idx_work_orders_serial (serial_number_id);

-- Barcode scans log (for analytics)
CREATE TABLE IF NOT EXISTS barcode_scans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(100) NOT NULL,
    serial_number_id BIGINT UNSIGNED,
    product_id INT UNSIGNED,
    scan_type ENUM('inventory', 'sale', 'rental', 'service', 'return', 'search') NOT NULL,
    scanned_by INT UNSIGNED,
    scan_location VARCHAR(50) COMMENT 'e.g., POS, inventory, rentals',
    result ENUM('success', 'not_found', 'error') DEFAULT 'success',
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_barcode (barcode),
    INDEX idx_serial_number_id (serial_number_id),
    INDEX idx_scanned_at (scanned_at),
    INDEX idx_scanned_by (scanned_by),
    FOREIGN KEY (serial_number_id) REFERENCES serial_numbers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (scanned_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
