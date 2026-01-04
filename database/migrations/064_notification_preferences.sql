SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `push_notification_devices`;
DROP TABLE IF EXISTS `notification_queue`;
DROP TABLE IF EXISTS `notification_history`;
DROP TABLE IF EXISTS `notification_preferences`;
DROP TABLE IF EXISTS `notification_types`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `push_notification_devices`;
DROP TABLE IF EXISTS `notification_queue`;
DROP TABLE IF EXISTS `notification_history`;
DROP TABLE IF EXISTS `notification_preferences`;
DROP TABLE IF EXISTS `notification_types`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `push_notification_devices`;
DROP TABLE IF EXISTS `notification_queue`;
DROP TABLE IF EXISTS `notification_history`;
DROP TABLE IF EXISTS `notification_preferences`;
DROP TABLE IF EXISTS `notification_types`;

-- Notification Preferences System
-- User notification settings and delivery channel preferences

-- Notification Types (available notification categories)
CREATE TABLE IF NOT EXISTS notification_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_type VARCHAR(100) UNIQUE NOT NULL,
    notification_name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('sales', 'inventory', 'customers', 'courses', 'system', 'account') NOT NULL,
    default_email BOOLEAN DEFAULT TRUE,
    default_sms BOOLEAN DEFAULT FALSE,
    default_in_app BOOLEAN DEFAULT TRUE,
    default_push BOOLEAN DEFAULT FALSE,
    default_frequency ENUM('instant', 'hourly', 'daily', 'weekly', 'never') DEFAULT 'instant',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_notification_type (notification_type),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Notification Preferences
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type_id INT NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT FALSE,
    in_app_enabled BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT FALSE,
    frequency ENUM('instant', 'hourly', 'daily', 'weekly', 'never') DEFAULT 'instant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (notification_type_id) REFERENCES notification_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_notification (user_id, notification_type_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification History (tracking sent notifications)
CREATE TABLE IF NOT EXISTS notification_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type_id INT NOT NULL,
    channel ENUM('email', 'sms', 'in_app', 'push') NOT NULL,
    recipient VARCHAR(255) COMMENT 'Email address, phone number, or device token',
    subject VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'sent', 'delivered', 'read', 'failed', 'bounced') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    error_message TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (notification_type_id) REFERENCES notification_types(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_channel (channel),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Queue (for batch/scheduled notifications)
CREATE TABLE IF NOT EXISTS notification_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(100) NOT NULL,
    channel ENUM('email', 'sms', 'in_app', 'push') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    data JSON,
    scheduled_for TIMESTAMP NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    last_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_scheduled_for (scheduled_for),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Push Notification Devices (for mobile app push notifications)
CREATE TABLE IF NOT EXISTS push_notification_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NOT NULL,
    device_token VARCHAR(255) UNIQUE NOT NULL,
    device_type ENUM('ios', 'android', 'web') NOT NULL,
    device_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_device_token (device_token),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default notification types
INSERT INTO notification_types (notification_type, notification_name, description, category, default_email, default_sms, default_in_app, default_push, default_frequency) VALUES
-- Sales Notifications
('new_transaction', 'New Transaction', 'Notification when a new sale is completed', 'sales', FALSE, FALSE, TRUE, FALSE, 'instant'),
('large_transaction', 'Large Transaction Alert', 'Alert for transactions above threshold', 'sales', TRUE, FALSE, TRUE, FALSE, 'instant'),
('daily_sales_summary', 'Daily Sales Summary', 'End-of-day sales report', 'sales', TRUE, FALSE, FALSE, FALSE, 'daily'),
('refund_processed', 'Refund Processed', 'Notification when a refund is issued', 'sales', TRUE, FALSE, TRUE, FALSE, 'instant'),

-- Inventory Notifications
('low_stock_alert', 'Low Stock Alert', 'Product stock below threshold', 'inventory', TRUE, FALSE, TRUE, FALSE, 'daily'),
('out_of_stock', 'Out of Stock', 'Product completely out of stock', 'inventory', TRUE, FALSE, TRUE, TRUE, 'instant'),
('stock_count_complete', 'Stock Count Complete', 'Physical inventory count finished', 'inventory', TRUE, FALSE, TRUE, FALSE, 'instant'),
('purchase_order_received', 'Purchase Order Received', 'PO delivery confirmation', 'inventory', TRUE, FALSE, TRUE, FALSE, 'instant'),
('reorder_suggestion', 'Reorder Suggestion', 'Automated reorder recommendation', 'inventory', TRUE, FALSE, FALSE, FALSE, 'weekly'),

-- Customer Notifications
('new_customer', 'New Customer', 'New customer registration', 'customers', FALSE, FALSE, TRUE, FALSE, 'instant'),
('customer_milestone', 'Customer Milestone', 'Customer reached spending milestone', 'customers', TRUE, FALSE, TRUE, FALSE, 'instant'),
('review_submitted', 'Review Submitted', 'Customer left a review', 'customers', TRUE, FALSE, TRUE, FALSE, 'instant'),
('support_ticket_created', 'Support Ticket Created', 'New support ticket opened', 'customers', TRUE, FALSE, TRUE, TRUE, 'instant'),

-- Course Notifications
('course_enrollment', 'Course Enrollment', 'Student enrolled in course', 'courses', TRUE, FALSE, TRUE, FALSE, 'instant'),
('course_starting_soon', 'Course Starting Soon', 'Reminder for upcoming course', 'courses', TRUE, TRUE, TRUE, TRUE, 'instant'),
('course_completed', 'Course Completed', 'Student completed a course', 'courses', TRUE, FALSE, TRUE, FALSE, 'instant'),
('certification_expiring', 'Certification Expiring', 'Certification expires soon', 'courses', TRUE, TRUE, TRUE, TRUE, 'weekly'),

-- System Notifications
('backup_completed', 'Backup Completed', 'System backup finished successfully', 'system', TRUE, FALSE, FALSE, FALSE, 'instant'),
('backup_failed', 'Backup Failed', 'System backup encountered an error', 'system', TRUE, TRUE, TRUE, TRUE, 'instant'),
('scheduled_maintenance', 'Scheduled Maintenance', 'Upcoming system maintenance', 'system', TRUE, FALSE, TRUE, FALSE, 'instant'),
('system_error', 'System Error', 'Critical system error occurred', 'system', TRUE, TRUE, TRUE, TRUE, 'instant'),

-- Account Notifications
('password_changed', 'Password Changed', 'Account password was changed', 'account', TRUE, TRUE, TRUE, FALSE, 'instant'),
('login_from_new_device', 'Login from New Device', 'Account accessed from unrecognized device', 'account', TRUE, TRUE, TRUE, TRUE, 'instant'),
('role_changed', 'Role Changed', 'User role or permissions updated', 'account', TRUE, FALSE, TRUE, FALSE, 'instant'),
('payment_method_added', 'Payment Method Added', 'New payment method added to account', 'account', TRUE, FALSE, TRUE, FALSE, 'instant');


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;