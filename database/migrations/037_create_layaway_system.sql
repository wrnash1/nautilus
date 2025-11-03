-- ==========================================
-- Migration: Create Layaway System
-- Description: Layaway functionality for Point of Sale
-- ==========================================

-- Layaway Transactions
CREATE TABLE IF NOT EXISTS layaway (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT UNSIGNED NOT NULL,

    -- Financial Details
    total_amount DECIMAL(10,2) NOT NULL,
    deposit_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    balance_due DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,

    -- Payment Schedule
    payment_schedule ENUM('weekly', 'biweekly', 'monthly', 'custom') DEFAULT 'weekly',
    payment_amount DECIMAL(10,2) COMMENT 'Expected payment amount per period',
    start_date DATE NOT NULL,
    due_date DATE NOT NULL,
    completed_date DATE NULL,

    -- Status and Management
    status ENUM('active', 'completed', 'cancelled', 'defaulted') DEFAULT 'active',
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP NULL,
    cancelled_by INT UNSIGNED NULL,

    -- Store Information
    location_id INT UNSIGNED NULL COMMENT 'Store location if multi-location',
    notes TEXT,
    internal_notes TEXT COMMENT 'Staff-only notes',

    -- Audit Trail
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED NULL,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id),
    FOREIGN KEY (cancelled_by) REFERENCES users(id),

    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_layaway_number (layaway_number),
    INDEX idx_start_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway Items
CREATE TABLE IF NOT EXISTS layaway_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,

    -- Product Details (snapshot at time of layaway)
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100),
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,

    -- Inventory Hold
    inventory_reserved BOOLEAN DEFAULT TRUE,
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Item Status
    item_status ENUM('reserved', 'released', 'sold', 'returned') DEFAULT 'reserved',
    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (layaway_id) REFERENCES layaway(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),

    INDEX idx_layaway (layaway_id),
    INDEX idx_product (product_id),
    INDEX idx_status (item_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway Payments
CREATE TABLE IF NOT EXISTS layaway_payments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_id INT UNSIGNED NOT NULL,

    -- Payment Details
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'credit_card', 'debit_card', 'check', 'other') NOT NULL,
    payment_reference VARCHAR(100) COMMENT 'Check number, transaction ID, etc.',

    -- Card Details (if applicable)
    card_last_four VARCHAR(4),
    card_type VARCHAR(50),

    -- Receipt
    receipt_number VARCHAR(50),

    -- Status
    payment_status ENUM('completed', 'pending', 'failed', 'refunded') DEFAULT 'completed',
    refund_amount DECIMAL(10,2) DEFAULT 0.00,
    refund_reason TEXT,
    refunded_at TIMESTAMP NULL,

    -- Audit
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_by INT UNSIGNED NOT NULL,
    notes TEXT,

    FOREIGN KEY (layaway_id) REFERENCES layaway(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id),

    INDEX idx_layaway (layaway_id),
    INDEX idx_paid_at (paid_at),
    INDEX idx_payment_method (payment_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway History/Activity Log
CREATE TABLE IF NOT EXISTS layaway_history (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_id INT UNSIGNED NOT NULL,

    -- Event Details
    event_type ENUM('created', 'payment_received', 'payment_missed', 'reminder_sent',
                    'status_changed', 'item_added', 'item_removed', 'note_added',
                    'completed', 'cancelled', 'defaulted') NOT NULL,
    event_description TEXT NOT NULL,

    -- Related Data
    payment_id INT UNSIGNED NULL COMMENT 'If event is payment-related',
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    amount DECIMAL(10,2) NULL,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,

    FOREIGN KEY (layaway_id) REFERENCES layaway(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES layaway_payments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),

    INDEX idx_layaway (layaway_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway Reminders/Notifications
CREATE TABLE IF NOT EXISTS layaway_reminders (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_id INT UNSIGNED NOT NULL,

    -- Reminder Details
    reminder_type ENUM('payment_due', 'payment_overdue', 'final_payment', 'cancellation_warning') NOT NULL,
    reminder_method ENUM('email', 'sms', 'phone', 'in_person') NOT NULL,

    -- Scheduling
    scheduled_date DATE NOT NULL,
    sent_at TIMESTAMP NULL,

    -- Status
    status ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    error_message TEXT,

    -- Content
    recipient_email VARCHAR(100),
    recipient_phone VARCHAR(20),
    message_content TEXT,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,

    FOREIGN KEY (layaway_id) REFERENCES layaway(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),

    INDEX idx_layaway (layaway_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway Settings/Configuration
CREATE TABLE IF NOT EXISTS layaway_settings (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,

    -- Policy Settings
    minimum_deposit_percentage INT DEFAULT 20 COMMENT 'Minimum deposit % required',
    minimum_deposit_amount DECIMAL(10,2) DEFAULT 0.00,
    maximum_layaway_period_days INT DEFAULT 90,
    default_payment_schedule ENUM('weekly', 'biweekly', 'monthly') DEFAULT 'weekly',

    -- Fees
    layaway_fee DECIMAL(10,2) DEFAULT 0.00 COMMENT 'One-time layaway fee',
    late_fee_amount DECIMAL(10,2) DEFAULT 0.00,
    late_fee_grace_period_days INT DEFAULT 7,
    cancellation_fee DECIMAL(10,2) DEFAULT 0.00,
    restocking_fee_percentage INT DEFAULT 0,

    -- Inventory Management
    auto_reserve_inventory BOOLEAN DEFAULT TRUE,
    release_on_cancellation BOOLEAN DEFAULT TRUE,
    days_before_default INT DEFAULT 30 COMMENT 'Days overdue before default',

    -- Notifications
    send_payment_reminders BOOLEAN DEFAULT TRUE,
    reminder_days_before_due INT DEFAULT 3,
    send_overdue_notifications BOOLEAN DEFAULT TRUE,

    -- Restrictions
    max_active_layaways_per_customer INT DEFAULT 3,
    require_customer_account BOOLEAN DEFAULT TRUE,

    -- Terms and Conditions
    terms_text TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED,

    FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Settings
INSERT INTO layaway_settings (
    minimum_deposit_percentage,
    minimum_deposit_amount,
    maximum_layaway_period_days,
    default_payment_schedule,
    layaway_fee,
    auto_reserve_inventory,
    send_payment_reminders,
    reminder_days_before_due
) VALUES (
    20,     -- 20% minimum deposit
    0.00,   -- No minimum dollar amount
    90,     -- 90 days max
    'weekly',
    0.00,   -- No layaway fee
    TRUE,   -- Auto reserve inventory
    TRUE,   -- Send reminders
    3       -- Remind 3 days before due
);

-- Create View for Active Layaways with Balances
CREATE OR REPLACE VIEW layaway_summary AS
SELECT
    l.id,
    l.layaway_number,
    l.customer_id,
    CONCAT(c.first_name, ' ', c.last_name) as customer_name,
    c.email as customer_email,
    c.phone as customer_phone,
    l.total_amount,
    l.deposit_amount,
    l.amount_paid,
    l.balance_due,
    l.payment_schedule,
    l.payment_amount,
    l.start_date,
    l.due_date,
    l.status,
    DATEDIFF(l.due_date, CURDATE()) as days_until_due,
    CASE
        WHEN l.status = 'completed' THEN 'Completed'
        WHEN l.status = 'cancelled' THEN 'Cancelled'
        WHEN l.status = 'defaulted' THEN 'Defaulted'
        WHEN CURDATE() > l.due_date THEN 'Overdue'
        WHEN DATEDIFF(l.due_date, CURDATE()) <= 7 THEN 'Due Soon'
        ELSE 'Current'
    END as payment_status,
    (SELECT COUNT(*) FROM layaway_items WHERE layaway_id = l.id) as item_count,
    (SELECT SUM(quantity) FROM layaway_items WHERE layaway_id = l.id) as total_items,
    l.created_at,
    CONCAT(u.first_name, ' ', u.last_name) as created_by_name
FROM layaway l
LEFT JOIN customers c ON l.customer_id = c.id
LEFT JOIN users u ON l.created_by = u.id;

-- Comments
ALTER TABLE layaway COMMENT = 'Main layaway transactions';
ALTER TABLE layaway_items COMMENT = 'Items in each layaway with inventory reservation';
ALTER TABLE layaway_payments COMMENT = 'Payment history for layaway transactions';
ALTER TABLE layaway_history COMMENT = 'Activity log for layaway transactions';
ALTER TABLE layaway_reminders COMMENT = 'Payment reminders and notifications';
ALTER TABLE layaway_settings COMMENT = 'System-wide layaway configuration';
