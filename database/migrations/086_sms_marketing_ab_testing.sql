SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ab_test_participants`;
DROP TABLE IF EXISTS `ab_test_variants`;
DROP TABLE IF EXISTS `ab_test_experiments`;
DROP TABLE IF EXISTS `sms_templates`;
DROP TABLE IF EXISTS `sms_queue`;
DROP TABLE IF EXISTS `sms_providers`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ab_test_participants`;
DROP TABLE IF EXISTS `ab_test_variants`;
DROP TABLE IF EXISTS `ab_test_experiments`;
DROP TABLE IF EXISTS `sms_templates`;
DROP TABLE IF EXISTS `sms_queue`;
DROP TABLE IF EXISTS `sms_providers`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ab_test_participants`;
DROP TABLE IF EXISTS `ab_test_variants`;
DROP TABLE IF EXISTS `ab_test_experiments`;
DROP TABLE IF EXISTS `sms_templates`;
DROP TABLE IF EXISTS `sms_queue`;
DROP TABLE IF EXISTS `sms_providers`;

-- =====================================================
-- SMS Marketing & A/B Testing System
-- =====================================================

-- SMS Provider Configuration
CREATE TABLE IF NOT EXISTS `sms_providers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `provider_name` ENUM('twilio', 'nexmo', 'messagebird', 'aws_sns', 'custom') NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_primary` BOOLEAN DEFAULT FALSE,

    -- API Credentials (encrypted)
    `api_key` VARCHAR(255) NULL,
    `api_secret` VARCHAR(255) NULL,
    `account_sid` VARCHAR(255) NULL,
    `auth_token` VARCHAR(255) NULL,

    -- Configuration
    `sender_id` VARCHAR(11) NULL COMMENT 'Default sender ID',
    `webhook_url` VARCHAR(500) NULL,
    `callback_url` VARCHAR(500) NULL,

    -- Limits
    `daily_limit` BIGINT UNSIGNED DEFAULT 1000,
    `daily_sent` BIGINT UNSIGNED DEFAULT 0,
    `last_reset_date` DATE NULL,

    -- Performance
    `total_sent` BIGINT UNSIGNED DEFAULT 0,
    `total_delivered` BIGINT UNSIGNED DEFAULT 0,
    `total_failed` BIGINT UNSIGNED DEFAULT 0,
    `delivery_rate` DECIMAL(5, 2) DEFAULT 0.00,

    -- Pricing
    `cost_per_sms` DECIMAL(6, 4) DEFAULT 0.0075 COMMENT 'Cost per SMS segment',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_active (`tenant_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Queue
CREATE TABLE IF NOT EXISTS `sms_queue` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `provider_id` BIGINT UNSIGNED NULL,

    -- Recipient
    `to_phone` VARCHAR(20) NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,

    -- Content
    `message` VARCHAR(1600) NOT NULL,
    `segment_count` TINYINT UNSIGNED DEFAULT 1,

    -- Campaign/Workflow
    `campaign_id` BIGINT UNSIGNED NULL,
    `workflow_id` BIGINT UNSIGNED NULL,

    -- Sender
    `sender_id` VARCHAR(11) NULL,
    `from_phone` VARCHAR(20) NULL,

    -- Scheduling
    `scheduled_for` DATETIME NULL,
    `send_after` DATETIME NULL COMMENT 'Respect quiet hours',

    -- Status
    `status` ENUM('pending', 'queued', 'sent', 'delivered', 'failed', 'cancelled') DEFAULT 'pending',
    `sent_at` DATETIME NULL,
    `delivered_at` DATETIME NULL,
    `failed_at` DATETIME NULL,

    -- Delivery Details
    `provider_message_id` VARCHAR(255) NULL,
    `delivery_status` VARCHAR(50) NULL,
    `error_code` VARCHAR(50) NULL,
    `error_message` TEXT NULL,

    -- Tracking
    `short_url` VARCHAR(255) NULL COMMENT 'Shortened tracking link',
    `clicked` BOOLEAN DEFAULT FALSE,
    `clicked_at` DATETIME NULL,

    -- Priority
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',

    -- Retry Logic
    `retry_count` TINYINT UNSIGNED DEFAULT 0,
    `max_retries` TINYINT UNSIGNED DEFAULT 3,
    `last_retry_at` DATETIME NULL,

    -- Cost
    `cost` DECIMAL(6, 4) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`provider_id`) REFERENCES `sms_providers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `marketing_campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`workflow_id`) REFERENCES `automation_workflows`(`id`) ON DELETE CASCADE,
    INDEX idx_status_priority (`status`, `priority`),
    INDEX idx_scheduled (`scheduled_for`),
    INDEX idx_phone (`to_phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS Templates
CREATE TABLE IF NOT EXISTS `sms_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `category` ENUM('transactional', 'promotional', 'reminder', 'alert', 'notification') NOT NULL,
    `message_template` VARCHAR(1600) NOT NULL,

    -- Personalization
    `available_tags` JSON NULL COMMENT 'Merge tags available',

    -- Compliance
    `requires_opt_in` BOOLEAN DEFAULT TRUE,
    `include_unsubscribe` BOOLEAN DEFAULT TRUE,

    -- Usage Stats
    `times_used` BIGINT UNSIGNED DEFAULT 0,
    `last_used_at` DATETIME NULL,

    -- Performance
    `avg_delivery_rate` DECIMAL(5, 2) DEFAULT 0.00,
    `avg_click_rate` DECIMAL(5, 2) DEFAULT 0.00,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_category (`tenant_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A/B Test Experiments
CREATE TABLE IF NOT EXISTS `ab_test_experiments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `experiment_type` ENUM('email_subject', 'email_content', 'sms_content', 'send_time', 'cta_button', 'landing_page') NOT NULL,

    -- Test Configuration
    `campaign_id` BIGINT UNSIGNED NULL,
    `workflow_id` BIGINT UNSIGNED NULL,
    `test_channel` ENUM('email', 'sms', 'both') DEFAULT 'email',

    -- Status
    `status` ENUM('draft', 'running', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    `started_at` DATETIME NULL,
    `ended_at` DATETIME NULL,

    -- Test Settings
    `traffic_split` JSON NOT NULL COMMENT 'Percentage for each variant',
    `sample_size` BIGINT UNSIGNED DEFAULT 1000,
    `min_sample_size` BIGINT UNSIGNED DEFAULT 100,
    `confidence_level` DECIMAL(5, 2) DEFAULT 95.00,

    -- Primary Metric
    `primary_metric` ENUM('open_rate', 'click_rate', 'conversion_rate', 'revenue') NOT NULL DEFAULT 'conversion_rate',
    `secondary_metrics` JSON NULL,

    -- Winner Detection
    `auto_declare_winner` BOOLEAN DEFAULT TRUE,
    `winner_variant` VARCHAR(50) NULL,
    `winner_declared_at` DATETIME NULL,
    `statistical_significance` DECIMAL(5, 2) NULL,

    -- Results
    `result_summary` JSON NULL,

    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `marketing_campaigns`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`workflow_id`) REFERENCES `automation_workflows`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_status (`tenant_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A/B Test Variants
CREATE TABLE IF NOT EXISTS `ab_test_variants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `experiment_id` BIGINT UNSIGNED NOT NULL,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `variant_name` VARCHAR(50) NOT NULL COMMENT 'A, B, C, Control',
    `is_control` BOOLEAN DEFAULT FALSE,

    -- Variant Content
    `variant_config` JSON NOT NULL COMMENT 'What is different in this variant',

    -- Email Variants
    `email_subject_line` VARCHAR(255) NULL,
    `email_content` LONGTEXT NULL,
    `email_from_name` VARCHAR(100) NULL,

    -- SMS Variants
    `sms_message` VARCHAR(1600) NULL,

    -- Send Time Variants
    `send_time` TIME NULL,
    `send_day_of_week` TINYINT NULL,

    -- Traffic Allocation
    `traffic_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 50.00,

    -- Performance Metrics
    `total_sent` BIGINT UNSIGNED DEFAULT 0,
    `total_delivered` BIGINT UNSIGNED DEFAULT 0,
    `total_opened` BIGINT UNSIGNED DEFAULT 0,
    `total_clicked` BIGINT UNSIGNED DEFAULT 0,
    `total_conversions` BIGINT UNSIGNED DEFAULT 0,
    `total_revenue` DECIMAL(10, 2) DEFAULT 0.00,

    -- Calculated Rates
    `open_rate` DECIMAL(5, 2) DEFAULT 0.00,
    `click_rate` DECIMAL(5, 2) DEFAULT 0.00,
    `conversion_rate` DECIMAL(5, 2) DEFAULT 0.00,
    `avg_revenue_per_recipient` DECIMAL(10, 2) DEFAULT 0.00,

    -- Winner Status
    `is_winner` BOOLEAN DEFAULT FALSE,
    `performance_score` DECIMAL(10, 4) NULL COMMENT 'Normalized performance score',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`experiment_id`) REFERENCES `ab_test_experiments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_experiment (`experiment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- A/B Test Participant Assignments
CREATE TABLE IF NOT EXISTS `ab_test_participants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `experiment_id` BIGINT UNSIGNED NOT NULL,
    `variant_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `tenant_id` BIGINT UNSIGNED NOT NULL,

    -- Assignment
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `assignment_method` ENUM('random', 'weighted', 'manual') DEFAULT 'weighted',

    -- Engagement
    `email_sent` BOOLEAN DEFAULT FALSE,
    `email_delivered` BOOLEAN DEFAULT FALSE,
    `email_opened` BOOLEAN DEFAULT FALSE,
    `email_clicked` BOOLEAN DEFAULT FALSE,

    `sms_sent` BOOLEAN DEFAULT FALSE,
    `sms_delivered` BOOLEAN DEFAULT FALSE,
    `sms_clicked` BOOLEAN DEFAULT FALSE,

    -- Conversion
    `converted` BOOLEAN DEFAULT FALSE,
    `converted_at` DATETIME NULL,
    `conversion_value` DECIMAL(10, 2) NULL,

    FOREIGN KEY (`experiment_id`) REFERENCES `ab_test_experiments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `ab_test_variants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    UNIQUE KEY unique_experiment_customer (`experiment_id`, `customer_id`),
    INDEX idx_variant (`variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pre-seeded Data
-- =====================================================

-- SMS Templates
INSERT INTO `sms_templates` (
    `tenant_id`, `name`, `category`, `message_template`, `available_tags`, `requires_opt_in`
) VALUES
(1, 'Booking Confirmation', 'transactional',
    'Hi {{first_name}}! Your {{course_name}} is confirmed for {{date}} at {{time}}. See you soon! Reply STOP to opt out.',
    '["first_name", "course_name", "date", "time"]', FALSE),

(1, 'Appointment Reminder', 'reminder',
    'Reminder: You have a {{service}} scheduled tomorrow at {{time}}. Reply C to confirm or R to reschedule. Text STOP to opt out.',
    '["service", "time"]', FALSE),

(1, 'Flash Sale Alert', 'promotional',
    '🌊 FLASH SALE! Get 30% off all dive equipment today only. Shop now: {{short_url}} Reply STOP to unsubscribe.',
    '["short_url"]', TRUE),

(1, 'Certification Expiry', 'reminder',
    'Hi {{first_name}}, your {{cert_name}} expires in {{days}} days. Renew now to keep diving: {{renew_url}} Text STOP to opt out.',
    '["first_name", "cert_name", "days", "renew_url"]', FALSE),

(1, 'Welcome New Customer', 'notification',
    'Welcome to {{shop_name}}! 🤿 Get 15% off your first course with code: WELCOME15. Browse courses: {{url}} Reply STOP to opt out.',
    '["shop_name", "url"]', TRUE);

-- A/B Test Example
INSERT INTO `ab_test_experiments` (
    `tenant_id`, `name`, `description`, `experiment_type`, `status`,
    `test_channel`, `traffic_split`, `primary_metric`, `auto_declare_winner`
) VALUES
(1, 'Subject Line Test - Open Water Promo',
    'Testing which subject line drives more course bookings',
    'email_subject', 'draft', 'email',
    '{"A": 50, "B": 50}', 'conversion_rate', TRUE);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;