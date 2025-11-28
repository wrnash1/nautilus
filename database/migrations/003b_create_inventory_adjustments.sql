-- Create Inventory Adjustments Table
CREATE TABLE IF NOT EXISTS inventory_adjustments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    adjustment_type ENUM('add', 'subtract', 'set', 'audit', 'damage', 'loss') NOT NULL,
    quantity INT NOT NULL,
    reason TEXT,
    adjusted_by INT UNSIGNED,
    adjusted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
