SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `email_automation_rules`;
DROP TABLE IF EXISTS `email_log`;
DROP TABLE IF EXISTS `email_queue`;
DROP TABLE IF EXISTS `email_campaigns`;
DROP TABLE IF EXISTS `email_templates`;

-- Create missing dependency tables
CREATE TABLE IF NOT EXISTS `email_campaigns` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `display_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NULL,
    `subject` VARCHAR(500) NULL,
    `body_html` LONGTEXT NULL,
    `body_text` LONGTEXT NULL,
    `content` LONGTEXT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_system` BOOLEAN DEFAULT FALSE,
    `available_variables` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Queue Table
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Recipient Information
    `to_email` VARCHAR(255) NOT NULL,
    `to_name` VARCHAR(255) NULL,
    `cc` TEXT NULL COMMENT 'JSON array of CC recipients',
    `bcc` TEXT NULL COMMENT 'JSON array of BCC recipients',

    -- Email Content
    `subject` VARCHAR(500) NOT NULL,
    `body_html` LONGTEXT NULL,
    `body_text` LONGTEXT NULL,
    `from_email` VARCHAR(255) NULL,
    `from_name` VARCHAR(255) NULL,
    `reply_to` VARCHAR(255) NULL,

    -- Attachments
    `attachments` JSON NULL COMMENT 'Array of file paths',

    -- Template & Variables
    `template_name` VARCHAR(100) NULL,
    `template_variables` JSON NULL,

    -- Priority & Scheduling
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `scheduled_at` TIMESTAMP NULL COMMENT 'NULL = send immediately',
    `send_after` TIMESTAMP NULL COMMENT 'Do not send before this time',

    -- Status Tracking
    `status` ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    `attempts` INT DEFAULT 0,
    `max_attempts` INT DEFAULT 3,
    `last_attempt_at` TIMESTAMP NULL,
    `sent_at` TIMESTAMP NULL,
    `error_message` TEXT NULL,

    -- Related Entities
    `related_entity_type` VARCHAR(100) NULL COMMENT 'customer, order, booking, etc.',
    `related_entity_id` BIGINT UNSIGNED NULL,
  
  -- Campaign Tracking
  `campaign_id` BIGINT UNSIGNED NULL,
    `tracking_id` VARCHAR(100) NULL COMMENT 'Unique ID for open/click tracking',

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns`(`id`) ON DELETE SET NULL,

    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`, `created_at`),
    INDEX `idx_scheduled` (`scheduled_at`),
    INDEX `idx_to_email` (`to_email`),
    INDEX `idx_related` (`related_entity_type`, `related_entity_id`),
    INDEX `idx_campaign` (`campaign_id`),
    INDEX `idx_tracking` (`tracking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update email_templates table to match requirements
-- (Removed ALTER statements as table is now created correctly above)

-- Make content nullable as we are using body_html/body_text now
ALTER TABLE email_templates MODIFY COLUMN content LONGTEXT NULL;

-- Email Log (for sent emails)
CREATE TABLE IF NOT EXISTS `email_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Email Details
    `to_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) NOT NULL,
    `body_preview` VARCHAR(500) NULL COMMENT 'First 500 chars for quick view',

    -- Template Used
    `template_name` VARCHAR(100) NULL,

    -- Status
    `sent_at` TIMESTAMP NOT NULL,
    `delivery_status` ENUM('sent', 'delivered', 'bounced', 'complained', 'opened', 'clicked') DEFAULT 'sent',

    -- Tracking
    `opened_at` TIMESTAMP NULL,
    `clicked_at` TIMESTAMP NULL,
    `tracking_id` VARCHAR(100) NULL,

    -- Related Entities
    `related_entity_type` VARCHAR(100) NULL,
    `related_entity_id` BIGINT UNSIGNED NULL,
  `customer_id` BIGINT UNSIGNED NULL,

  -- Campaign
  `campaign_id` BIGINT UNSIGNED NULL,

    -- Error Handling
    `error_message` TEXT NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns`(`id`) ON DELETE SET NULL,

    INDEX `idx_to_email` (`to_email`),
    INDEX `idx_sent` (`sent_at`),
    INDEX `idx_status` (`delivery_status`),
    INDEX `idx_tracking` (`tracking_id`),
    INDEX `idx_campaign` (`campaign_id`),
    INDEX `idx_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Automation Rules
CREATE TABLE IF NOT EXISTS `email_automation_rules` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Rule Identity
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,

    -- Trigger
    `trigger_event` VARCHAR(100) NOT NULL COMMENT 'order_placed, cert_expiring, course_completed, etc.',
    `trigger_conditions` JSON NULL COMMENT 'Additional conditions for trigger',

    -- Email To Send
    `template_name` VARCHAR(100) NOT NULL,
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',

    -- Timing
    `send_delay_minutes` INT DEFAULT 0 COMMENT 'Delay after trigger event',

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,

    -- Statistics
    `triggered_count` INT DEFAULT 0,
    `sent_count` INT DEFAULT 0,
    `last_triggered_at` TIMESTAMP NULL,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` BIGINT UNSIGNED NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_trigger` (`trigger_event`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Default Email Templates (Using INSERT IGNORE to prevent duplicates)
INSERT IGNORE INTO `email_templates` (`name`, `display_name`, `description`, `category`, `subject`, `body_html`, `body_text`, `is_system`, `available_variables`) VALUES
('order_confirmation', 'Order Confirmation', 'Sent when a customer places an order', 'transactional', 'Order Confirmation - {{order_number}}',
'<h1>Thank you for your order!</h1><p>Order #{{order_number}}</p><p>Total: {{order_total}}</p>',
'Thank you for your order! Order #{{order_number}}. Total: {{order_total}}',
1, '["customer_name", "order_number", "order_total", "order_date", "items"]'),

('cert_expiring_soon', 'Certification Expiring Soon', 'Reminds customers their certification is expiring', 'notification', 'Your {{cert_name}} Expires Soon',
'<h2>Certification Renewal Reminder</h2><p>Hi {{customer_name}},</p><p>Your {{cert_name}} certification expires on {{expiry_date}}.</p>',
'Hi {{customer_name}}, Your {{cert_name}} certification expires on {{expiry_date}}.',
1, '["customer_name", "cert_name", "cert_number", "expiry_date"]'),

('course_completion', 'Course Completion', 'Congratulates student on course completion', 'transactional', 'Congratulations on Completing {{course_name}}!',
'<h1>Congratulations!</h1><p>You have successfully completed {{course_name}}.</p><p>Certification Number: {{cert_number}}</p>',
'Congratulations! You completed {{course_name}}. Certification Number: {{cert_number}}',
1, '["customer_name", "course_name", "completion_date", "cert_number"]'),

('medical_form_reminder', 'Medical Form Reminder', 'Reminds customers to complete medical form', 'notification', 'Medical Form Required',
'<h2>Medical Form Required</h2><p>Hi {{customer_name}},</p><p>Please complete your medical form before your next dive.</p>',
'Hi {{customer_name}}, Please complete your medical form before your next dive.',
1, '["customer_name", "form_link"]'),

('waiver_reminder', 'Waiver Reminder', 'Reminds customers to sign waiver', 'notification', 'Liability Waiver Required',
'<h2>Liability Waiver Required</h2><p>Hi {{customer_name}},</p><p>Please sign your liability waiver: {{waiver_link}}</p>',
'Hi {{customer_name}}, Please sign your liability waiver: {{waiver_link}}',
1, '["customer_name", "waiver_link"]'),

('booking_confirmation', 'Booking Confirmation', 'Confirms trip/dive booking', 'transactional', 'Booking Confirmed - {{trip_name}}',
'<h1>Booking Confirmed!</h1><p>Trip: {{trip_name}}</p><p>Date: {{trip_date}}</p><p>Total: {{total}}</p>',
'Booking Confirmed! Trip: {{trip_name}}, Date: {{trip_date}}, Total: {{total}}',
1, '["customer_name", "trip_name", "trip_date", "trip_location", "total"]'),

('password_reset', 'Password Reset', 'Password reset instructions', 'transactional', 'Reset Your Password',
'<h2>Password Reset Request</h2><p>Click here to reset your password: {{reset_link}}</p><p>This link expires in 24 hours.</p>',
'Click here to reset your password: {{reset_link}}. This link expires in 24 hours.',
1, '["customer_name", "reset_link"]'),

('welcome_email', 'Welcome Email', 'Welcome new customers', 'marketing', 'Welcome to Our Dive Shop!',
'<h1>Welcome {{customer_name}}!</h1><p>We are excited to have you join our diving community.</p>',
'Welcome {{customer_name}}! We are excited to have you join our diving community.',
1, '["customer_name", "dive_shop_name"]');


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;