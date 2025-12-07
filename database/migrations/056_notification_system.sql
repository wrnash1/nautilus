-- Migration: Automated Notification System
-- Description: Creates tables for notification settings and logging
-- Version: 056
-- Date: 2025-01-08

-- Notification Settings Table
-- Stores global notification configuration
CREATE TABLE IF NOT EXISTS notification_settings (
    id INT UNSIGNED PRIMARY KEY DEFAULT 1,

    -- Feature toggles
    low_stock_enabled BOOLEAN DEFAULT TRUE,
    maintenance_enabled BOOLEAN DEFAULT TRUE,
    course_enabled BOOLEAN DEFAULT TRUE,
    rental_enabled BOOLEAN DEFAULT TRUE,
    milestone_enabled BOOLEAN DEFAULT TRUE,

    -- Recipient emails
    admin_email VARCHAR(255),
    manager_email VARCHAR(255),
    inventory_email VARCHAR(255),
    maintenance_email VARCHAR(255),

    -- Notification thresholds
    low_stock_days_notice INT DEFAULT 7,
    maintenance_days_notice INT DEFAULT 7,
    rental_reminder_days INT DEFAULT 1,

    -- Email settings
    send_receipts BOOLEAN DEFAULT TRUE,
    send_confirmations BOOLEAN DEFAULT TRUE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Ensure only one settings record exists
    CONSTRAINT single_settings_row CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO notification_settings (id) VALUES (1);

-- Notification Log Table
-- Tracks all sent notifications for auditing and analytics
CREATE TABLE IF NOT EXISTS notification_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Notification details
    notification_type VARCHAR(50) NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),

    -- Reference to related entity
    reference_type VARCHAR(50),
    reference_id INT,

    -- Status tracking
    status ENUM('pending', 'sent', 'failed', 'bounced') DEFAULT 'sent',
    error_message TEXT,

    -- Engagement tracking
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,

    -- Timestamps
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Indexes for performance
    INDEX idx_notification_type (notification_type),
    INDEX idx_recipient (recipient),
    INDEX idx_sent_at (sent_at),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Templates Table
-- Stores customizable email templates
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Template identification
    template_key VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,

    -- Template content
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT,

    -- Variables available in template
    available_variables JSON,

    -- Template settings
    is_active BOOLEAN DEFAULT TRUE,
    category VARCHAR(50),

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_template_key (template_key),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO notification_templates (template_key, name, subject, body_html, category, available_variables) VALUES
('low_stock_alert', 'Low Stock Alert', 'Low Stock Alert - {{count}} Items Need Restocking',
'<h2>Low Stock Alert</h2><p>{{count}} products need restocking.</p>{{product_table}}',
'inventory', '["count", "product_table"]'),

('maintenance_due', 'Maintenance Due Alert', 'Equipment Maintenance Alert - {{count}} Items Due',
'<h2>Equipment Maintenance Due</h2><p>{{count}} items require maintenance.</p>{{equipment_table}}',
'maintenance', '["count", "equipment_table"]'),

('course_enrollment', 'Course Enrollment Confirmation', 'Course Enrollment Confirmation - {{course_name}}',
'<h2>Course Enrollment Confirmation</h2><p>Dear {{customer_name}},</p><p>You are enrolled in {{course_name}}.</p>',
'courses', '["customer_name", "course_name", "start_date", "course_code"]'),

('transaction_receipt', 'Transaction Receipt', 'Receipt for Transaction #{{transaction_number}}',
'<h2>Transaction Receipt</h2><p>Transaction #: {{transaction_number}}</p>{{items_table}}',
'sales', '["transaction_number", "items_table", "total", "date"]'),

('rental_reminder', 'Rental Return Reminder', 'Rental Return Reminder - {{equipment_name}}',
'<h2>Rental Return Reminder</h2><p>Your rental is due in {{days}} days.</p>',
'rentals', '["equipment_name", "due_date", "days"]');

-- Scheduled Notifications Table
-- Manages queued notifications to be sent
CREATE TABLE IF NOT EXISTS scheduled_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Notification details
    notification_type VARCHAR(50) NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    body TEXT,

    -- Scheduling
    scheduled_for TIMESTAMP NOT NULL,
    priority ENUM('low', 'normal', 'high') DEFAULT 'normal',

    -- Reference
    reference_type VARCHAR(50),
    reference_id INT,

    -- Processing status
    status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    last_error TEXT,

    -- Timestamps
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_scheduled_for (scheduled_for),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_notification_type (notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Preferences Table
-- Stores customer-specific notification preferences
CREATE TABLE IF NOT EXISTS customer_notification_preferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,

    -- Communication preferences
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT FALSE,

    -- Notification types
    receive_receipts BOOLEAN DEFAULT TRUE,
    receive_confirmations BOOLEAN DEFAULT TRUE,
    receive_reminders BOOLEAN DEFAULT TRUE,
    receive_marketing BOOLEAN DEFAULT FALSE,
    receive_promotions BOOLEAN DEFAULT FALSE,

    -- Preferred contact times
    preferred_contact_time ENUM('morning', 'afternoon', 'evening', 'any') DEFAULT 'any',
    timezone VARCHAR(50) DEFAULT 'America/New_York',

    -- Unsubscribe tracking
    unsubscribed_at TIMESTAMP NULL,
    unsubscribe_reason TEXT,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Statistics Table
-- Tracks notification metrics for analytics
CREATE TABLE IF NOT EXISTS notification_statistics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Date and type
    stat_date DATE NOT NULL,
    notification_type VARCHAR(50) NOT NULL,

    -- Metrics
    sent_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    opened_count INT DEFAULT 0,
    clicked_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    bounced_count INT DEFAULT 0,

    -- Rates (calculated)
    delivery_rate DECIMAL(5,2),
    open_rate DECIMAL(5,2),
    click_rate DECIMAL(5,2),

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_date_type (stat_date, notification_type),
    INDEX idx_stat_date (stat_date),
    INDEX idx_notification_type (notification_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add notification tracking columns to existing tables (conditional)
SET @dbname = DATABASE();

-- pos_transactions table
SET @tablename = "pos_transactions";
SET @columnname = "receipt_sent_at";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE pos_transactions ADD COLUMN receipt_sent_at TIMESTAMP NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "receipt_notification_id";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE pos_transactions ADD COLUMN receipt_notification_id BIGINT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- course_enrollments table
SET @tablename = "course_enrollments";
SET @columnname = "confirmation_sent_at";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE course_enrollments ADD COLUMN confirmation_sent_at TIMESTAMP NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "confirmation_notification_id";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE course_enrollments ADD COLUMN confirmation_notification_id BIGINT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- rental_reservations table (fixed from rental_transactions)
SET @tablename = "rental_reservations";
SET @columnname = "reminder_sent_at";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE rental_reservations ADD COLUMN reminder_sent_at TIMESTAMP NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @columnname = "reminder_notification_id";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE rental_reservations ADD COLUMN reminder_notification_id BIGINT NULL"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
