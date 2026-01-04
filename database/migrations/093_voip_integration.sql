-- Migration: VOIP Integration Support
-- Call logging, SMS logging, and configuration
-- Date: 2026-01-04

-- Call logs table
CREATE TABLE IF NOT EXISTS `call_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `customer_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL COMMENT 'Staff member who handled call',
    `phone_number` VARCHAR(20) NOT NULL,
    `direction` ENUM('inbound', 'outbound') NOT NULL,
    `status` ENUM('initiated', 'ringing', 'answered', 'completed', 'missed', 'voicemail', 'failed') NOT NULL,
    `duration_seconds` INT UNSIGNED NULL,
    `recording_url` VARCHAR(500) NULL,
    `notes` TEXT NULL,
    `provider` VARCHAR(50) DEFAULT 'twilio',
    `provider_call_id` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant_customer` (`tenant_id`, `customer_id`),
    INDEX `idx_phone` (`phone_number`),
    INDEX `idx_direction` (`direction`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS logs table
CREATE TABLE IF NOT EXISTS `sms_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `customer_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `message` TEXT NOT NULL,
    `direction` ENUM('inbound', 'outbound') NOT NULL,
    `status` ENUM('queued', 'sent', 'delivered', 'failed', 'received') NOT NULL DEFAULT 'queued',
    `provider` VARCHAR(50) DEFAULT 'twilio',
    `provider_message_id` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tenant_customer` (`tenant_id`, `customer_id`),
    INDEX `idx_phone` (`phone_number`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Communication templates (for SMS and email)
CREATE TABLE IF NOT EXISTS `communication_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `type` ENUM('sms', 'email', 'both') NOT NULL DEFAULT 'sms',
    `category` ENUM('reminder', 'confirmation', 'marketing', 'alert', 'other') NOT NULL DEFAULT 'other',
    `subject` VARCHAR(255) NULL COMMENT 'For email templates',
    `body` TEXT NOT NULL,
    `variables` JSON NULL COMMENT 'Available merge variables',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant_type` (`tenant_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default SMS templates
INSERT INTO `communication_templates` (`tenant_id`, `name`, `type`, `category`, `body`, `variables`) VALUES
(1, 'Appointment Reminder', 'sms', 'reminder', 'Hi {{customer_name}}! Reminder: You have an appointment at {{shop_name}} on {{date}} at {{time}}. Reply CONFIRM or call us at {{shop_phone}}.', '["customer_name", "shop_name", "date", "time", "shop_phone"]'),
(1, 'Course Reminder', 'sms', 'reminder', 'Hi {{customer_name}}! Your {{course_name}} class starts on {{date}} at {{time}}. See you there!', '["customer_name", "course_name", "date", "time"]'),
(1, 'Rental Return Reminder', 'sms', 'reminder', 'Hi {{customer_name}}! Your rental equipment is due back on {{due_date}}. Please return to {{shop_name}} to avoid late fees.', '["customer_name", "due_date", "shop_name"]'),
(1, 'Equipment Ready', 'sms', 'alert', 'Hi {{customer_name}}! Your equipment service is complete and ready for pickup at {{shop_name}}.', '["customer_name", "shop_name"]'),
(1, 'Tank Ready', 'sms', 'alert', 'Hi {{customer_name}}! Your tank fill is ready for pickup. Thanks for choosing {{shop_name}}!', '["customer_name", "shop_name"]');
