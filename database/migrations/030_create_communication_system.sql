-- ============================================================================
-- Migration: Create Enhanced Communication System
-- Created: 2024
-- Description: SMS, Push notifications, campaign tracking, and communication logs
-- Note: customer_communications table already exists from 002_create_customer_tables.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `communication_campaigns`;
DROP TABLE IF EXISTS `communication_log`;
DROP TABLE IF EXISTS `customer_devices`;
DROP TABLE IF EXISTS `communication_templates`;
DROP TABLE IF EXISTS `customer_communication_preferences`;

-- Communication Campaigns (created first so communication_log can reference it)
CREATE TABLE IF NOT EXISTS communication_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    campaign_type VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    target_audience VARCHAR(20) NOT NULL,
    target_segment TEXT,
    message_subject VARCHAR(200),
    message_body TEXT NOT NULL,
    scheduled_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_delivered INT DEFAULT 0,
    total_failed INT DEFAULT 0,
    total_opened INT DEFAULT 0,
    total_clicked INT DEFAULT 0,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    created_by BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_type (campaign_type),
    INDEX idx_scheduled (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communication Message Log (created after campaigns so it can reference campaign_id)
CREATE TABLE IF NOT EXISTS communication_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    message_type VARCHAR(20) NOT NULL,
    campaign_id BIGINT UNSIGNED,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    provider VARCHAR(50),
    provider_message_id VARCHAR(200),
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    failed_reason TEXT,
    cost DECIMAL(10,4),
    metadata TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (campaign_id) REFERENCES communication_campaigns(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_type (message_type),
    INDEX idx_status (status),
    INDEX idx_campaign (campaign_id),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Device Tokens (for Push Notifications)
CREATE TABLE IF NOT EXISTS customer_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    device_type VARCHAR(20) NOT NULL,
    device_token TEXT NOT NULL,
    device_name VARCHAR(100),
    app_version VARCHAR(20),
    os_version VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communication Templates
CREATE TABLE IF NOT EXISTS communication_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    template_type VARCHAR(20) NOT NULL,
    category VARCHAR(50),
    subject VARCHAR(200),
    body TEXT NOT NULL,
    variables TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_type (template_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Communication Preferences (enhanced)
CREATE TABLE IF NOT EXISTS customer_communication_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT TRUE,
    push_enabled BOOLEAN DEFAULT TRUE,
    marketing_email BOOLEAN DEFAULT TRUE,
    marketing_sms BOOLEAN DEFAULT FALSE,
    marketing_push BOOLEAN DEFAULT TRUE,
    transactional_email BOOLEAN DEFAULT TRUE,
    transactional_sms BOOLEAN DEFAULT TRUE,
    reminder_notifications BOOLEAN DEFAULT TRUE,
    promotional_notifications BOOLEAN DEFAULT TRUE,
    newsletter BOOLEAN DEFAULT TRUE,
    preferred_contact_method VARCHAR(20) DEFAULT 'email',
    quiet_hours_start TIME,
    quiet_hours_end TIME,
    timezone VARCHAR(50) DEFAULT 'America/New_York',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer (customer_id),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT IGNORE INTO communication_templates (id, name, template_type, category, subject, body, variables) VALUES
(1, 'Welcome SMS', 'sms', 'transactional', NULL, 'Welcome to {{shop_name}}! Your account is ready. Reply STOP to opt-out.', '["shop_name", "customer_name"]'),
(2, 'Order Confirmation', 'sms', 'transactional', NULL, 'Thanks {{customer_name}}! Order #{{order_number}} confirmed. Total: ${{order_total}}', '["customer_name", "order_number", "order_total"]'),
(3, 'Appointment Reminder', 'sms', 'transactional', NULL, 'Reminder: Your {{appointment_type}} is tomorrow at {{appointment_time}}. See you soon!', '["appointment_type", "appointment_time", "shop_name"]'),
(4, 'Course Starting Soon', 'sms', 'transactional', NULL, 'Your {{course_name}} starts on {{start_date}}. Location: {{location}}. Questions? Call us!', '["course_name", "start_date", "location"]'),
(5, 'Trip Reminder', 'push', 'transactional', 'Trip Reminder', 'Your {{trip_name}} departs in {{days_until}} days! Are you ready?', '["trip_name", "days_until", "departure_date"]');

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================================
-- Migration Complete
-- ============================================================================
