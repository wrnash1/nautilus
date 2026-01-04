-- Create work_orders table
CREATE TABLE IF NOT EXISTS work_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    equipment_brand VARCHAR(100) NULL,
    equipment_model VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    issue_description TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('pending', 'in_progress', 'waiting_parts', 'completed', 'cancelled') DEFAULT 'pending',
    estimated_cost DECIMAL(10,2) NULL,
    final_cost DECIMAL(10,2) NULL,
    assigned_to INT NULL,
    created_by INT NOT NULL,
    updated_by INT NULL,
    completed_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create work_order_notes table
CREATE TABLE IF NOT EXISTS work_order_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    work_order_id INT NOT NULL,
    note TEXT NOT NULL,
    is_visible_to_customer TINYINT(1) DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (work_order_id) REFERENCES work_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
