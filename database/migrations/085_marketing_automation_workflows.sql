-- =====================================================
-- Marketing Automation Workflows
-- Build automated customer journey workflows
-- =====================================================

-- Automation Workflows
-- Automation Workflows (Created in 011)
-- CREATE TABLE IF NOT EXISTS `automation_workflows` (
--     `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     `tenant_id` INT UNSIGNED NOT NULL,
--     `name` VARCHAR(255) NOT NULL,
--     `description` TEXT NULL,
--     `workflow_type` ENUM('drip', 'trigger', 'nurture', 'transactional', 'win_back', 'onboarding') NOT NULL,
--     `status` ENUM('draft', 'active', 'paused', 'archived') DEFAULT 'draft',
-- 
--     -- Trigger Configuration
--     `trigger_type` ENUM('event', 'date', 'segment', 'behavior', 'api', 'manual') NOT NULL,
--     `trigger_config` JSON NOT NULL COMMENT 'Trigger conditions and settings',
-- 
--     -- Entry Criteria
--     `entry_criteria` JSON NULL COMMENT 'Who can enter this workflow',
--     `can_re_enter` BOOLEAN DEFAULT FALSE COMMENT 'Allow customers to re-enter',
--     `re_entry_wait_days` INT UNSIGNED NULL,
-- 
--     -- Exit Criteria
--     `exit_criteria` JSON NULL COMMENT 'Conditions that remove customer from workflow',
--     `max_duration_days` INT UNSIGNED NULL,
-- 
--     -- Performance
--     `total_entries` INT UNSIGNED DEFAULT 0,
--     `active_members` INT UNSIGNED DEFAULT 0,
--     `completed_members` INT UNSIGNED DEFAULT 0,
--     `total_conversions` INT UNSIGNED DEFAULT 0,
--     `conversion_rate` DECIMAL(5, 2) DEFAULT 0.00,
--     `total_revenue` DECIMAL(10, 2) DEFAULT 0.00,
-- 
--     -- Settings
--     `send_time_optimization` BOOLEAN DEFAULT FALSE COMMENT 'AI-optimized send times',
--     `frequency_cap` JSON NULL COMMENT 'Max messages per day/week',
--     `quiet_hours_start` TIME NULL DEFAULT '22:00:00',
--     `quiet_hours_end` TIME NULL DEFAULT '08:00:00',
-- 
--     -- Ownership
--     `created_by` INT UNSIGNED NULL,
--     `updated_by` INT UNSIGNED NULL,
--     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     `activated_at` DATETIME NULL,
-- 
--     FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
--     INDEX idx_tenant_status (`tenant_id`, `status`),
--     INDEX idx_workflow_type (`workflow_type`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Update automation_workflows table to match requirements
SET @dbname = DATABASE();
SET @tablename = "automation_workflows";

-- Add tenant_id
SET @columnname = "tenant_id";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE automation_workflows ADD COLUMN tenant_id INT UNSIGNED NULL AFTER id;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add description
SET @columnname = "description";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE automation_workflows ADD COLUMN description TEXT NULL AFTER name;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add workflow_type
SET @columnname = "workflow_type";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE automation_workflows ADD COLUMN workflow_type ENUM('drip', 'trigger', 'nurture', 'transactional', 'win_back', 'onboarding') NOT NULL AFTER description;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add status
SET @columnname = "status";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE automation_workflows ADD COLUMN status ENUM('draft', 'active', 'paused', 'archived') DEFAULT 'draft' AFTER workflow_type;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update trigger_type ENUM
SET @dbname = DATABASE();
SET @tablename = "automation_workflows";
SET @columnname = "trigger_type";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "ALTER TABLE automation_workflows MODIFY COLUMN trigger_type ENUM('event', 'date', 'segment', 'behavior', 'api', 'manual', 'schedule', 'segment_entry', 'segment_exit') NOT NULL;",
  "ALTER TABLE automation_workflows ADD COLUMN trigger_type ENUM('event', 'date', 'segment', 'behavior', 'api', 'manual') NOT NULL AFTER description;"
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Add trigger_config
SET @columnname = "trigger_config";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE automation_workflows ADD COLUMN trigger_config JSON NOT NULL COMMENT 'Trigger conditions and settings' AFTER trigger_type;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add can_re_enter
SET @columnname = "can_re_enter";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE automation_workflows ADD COLUMN can_re_enter BOOLEAN DEFAULT FALSE COMMENT 'Allow customers to re-enter' AFTER trigger_config;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Make actions nullable as we are using other fields now
SET @dbname = DATABASE();
SET @tablename = "automation_workflows";
SET @columnname = "actions";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "ALTER TABLE automation_workflows MODIFY COLUMN actions JSON NULL;",
  "SELECT 1"
));
PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists;

-- Workflow Steps
CREATE TABLE IF NOT EXISTS `automation_workflow_steps` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `step_order` INT UNSIGNED NOT NULL,
    `step_name` VARCHAR(255) NOT NULL,
    `step_type` ENUM('email', 'sms', 'wait', 'condition', 'split_test', 'webhook', 'task', 'goal') NOT NULL,

    -- Timing
    `delay_amount` INT UNSIGNED DEFAULT 0,
    `delay_unit` ENUM('minutes', 'hours', 'days', 'weeks') DEFAULT 'days',
    `send_time` TIME NULL COMMENT 'Specific time to send, null for immediate',
    `send_day_of_week` TINYINT NULL COMMENT '0=Sunday, 6=Saturday',

    -- Step Configuration
    `config` JSON NOT NULL DEFAULT ('{}') COMMENT 'Step-specific configuration',

    -- Email/SMS Content
    `email_template_id` INT UNSIGNED NULL,
    `sms_template_id` INT UNSIGNED NULL,
    `subject_line` VARCHAR(255) NULL,
    `email_content` LONGTEXT NULL,
    `sms_content` VARCHAR(1600) NULL,

    -- Conditional Logic
    `condition_rules` JSON NULL COMMENT 'IF/THEN conditions',
    `true_next_step_id` INT UNSIGNED NULL COMMENT 'Step to go to if condition is true',
    `false_next_step_id` INT UNSIGNED NULL COMMENT 'Step to go to if condition is false',

    -- A/B Testing
    `is_ab_test` BOOLEAN DEFAULT FALSE,
    `ab_split_percentage` TINYINT NULL COMMENT 'Percentage for variant A',

    -- Performance
    `total_sent` INT UNSIGNED DEFAULT 0,
    `total_delivered` INT UNSIGNED DEFAULT 0,
    `total_opened` INT UNSIGNED DEFAULT 0,
    `total_clicked` INT UNSIGNED DEFAULT 0,
    `total_conversions` INT UNSIGNED DEFAULT 0,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`workflow_id`) REFERENCES `automation_workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_workflow_order (`workflow_id`, `step_order`),
    INDEX idx_step_type (`step_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workflow Members (customers in workflows)
CREATE TABLE IF NOT EXISTS `automation_workflow_members` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,

    -- Entry Details
    `entered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `entry_trigger` VARCHAR(255) NULL COMMENT 'What triggered entry',
    `current_step_id` INT UNSIGNED NULL,
    `current_step_entered_at` DATETIME NULL,

    -- Status
    `status` ENUM('active', 'completed', 'exited', 'paused', 'failed') DEFAULT 'active',
    `completed_at` DATETIME NULL,
    `exited_at` DATETIME NULL,
    `exit_reason` VARCHAR(255) NULL,

    -- Progress Tracking
    `steps_completed` INT UNSIGNED DEFAULT 0,
    `total_steps` INT UNSIGNED DEFAULT 0,
    `emails_sent` INT UNSIGNED DEFAULT 0,
    `emails_opened` INT UNSIGNED DEFAULT 0,
    `emails_clicked` INT UNSIGNED DEFAULT 0,
    `sms_sent` INT UNSIGNED DEFAULT 0,

    -- Conversion
    `converted` BOOLEAN DEFAULT FALSE,
    `converted_at` DATETIME NULL,
    `conversion_value` DECIMAL(10, 2) NULL,
    `conversion_type` VARCHAR(100) NULL,

    -- Next Action
    `next_action_at` DATETIME NULL,
    `is_waiting` BOOLEAN DEFAULT FALSE,

    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`workflow_id`) REFERENCES `automation_workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`current_step_id`) REFERENCES `automation_workflow_steps`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_workflow_customer (`workflow_id`, `customer_id`),
    INDEX idx_status (`status`),
    INDEX idx_next_action (`next_action_at`),
    UNIQUE KEY unique_workflow_customer_active (`workflow_id`, `customer_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workflow Step Executions (audit log)
CREATE TABLE IF NOT EXISTS `automation_step_executions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_member_id` BIGINT UNSIGNED NOT NULL,
    `workflow_id` INT UNSIGNED NOT NULL,
    `step_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,

    -- Execution Details
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `execution_status` ENUM('pending', 'success', 'failed', 'skipped') DEFAULT 'pending',
    `error_message` TEXT NULL,

    -- Action Taken
    `action_type` VARCHAR(50) NOT NULL COMMENT 'email, sms, wait, etc.',
    `action_details` JSON NULL,

    -- Engagement (if applicable)
    `delivered` BOOLEAN DEFAULT FALSE,
    `opened` BOOLEAN DEFAULT FALSE,
    `clicked` BOOLEAN DEFAULT FALSE,
    `opened_at` DATETIME NULL,
    `clicked_at` DATETIME NULL,

    -- Conversion (if applicable)
    `led_to_conversion` BOOLEAN DEFAULT FALSE,
    `conversion_value` DECIMAL(10, 2) NULL,

    FOREIGN KEY (`workflow_member_id`) REFERENCES `automation_workflow_members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`workflow_id`) REFERENCES `automation_workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`step_id`) REFERENCES `automation_workflow_steps`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_workflow_member (`workflow_member_id`),
    INDEX idx_executed_at (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Workflow Goals (conversion tracking)
CREATE TABLE IF NOT EXISTS `automation_workflow_goals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `workflow_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `goal_name` VARCHAR(255) NOT NULL,
    `goal_type` ENUM('page_visit', 'form_submit', 'booking', 'purchase', 'certification', 'custom') NOT NULL,

    -- Goal Configuration
    `goal_config` JSON NOT NULL COMMENT 'What constitutes goal completion',
    `goal_value` DECIMAL(10, 2) NULL COMMENT 'Monetary value of achieving goal',

    -- Tracking
    `total_achieved` INT UNSIGNED DEFAULT 0,
    `achievement_rate` DECIMAL(5, 2) DEFAULT 0.00,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`workflow_id`) REFERENCES `automation_workflows`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_workflow (`workflow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pre-seeded Automation Workflows
-- =====================================================

INSERT INTO `automation_workflows` (
    `tenant_id`, `name`, `description`, `workflow_type`, `status`, `trigger_type`, `trigger_config`, `can_re_enter`
) VALUES
(1, 'New Customer Welcome Series',
    'Welcome new customers with a 5-email nurture series over 14 days',
    'onboarding', 'active', 'event',
    '{"event": "customer_created", "delay": 0}',
    FALSE),

(1, 'Abandoned Course Booking',
    'Re-engage customers who started but didn\'t complete course booking',
    'trigger', 'active', 'behavior',
    '{"event": "booking_abandoned", "delay": 60, "delay_unit": "minutes"}',
    TRUE),

(1, 'Certification Expiry Reminder',
    'Multi-touch campaign for certification renewals 60-30-7 days before expiry',
    'transactional', 'active', 'date',
    '{"trigger_date_field": "certification_expiry_date", "days_before": 60}',
    FALSE),

(1, 'Post-Course Follow-up',
    'Gather feedback and promote next courses after completion',
    'nurture', 'active', 'event',
    '{"event": "course_completed", "delay": 1, "delay_unit": "days"}',
    TRUE),

(1, 'Win-Back Dormant Customers',
    'Re-activate customers with no activity in 6 months',
    'win_back', 'active', 'segment',
    '{"segment_id": 5, "check_frequency": "weekly"}',
    FALSE),

(1, 'Birthday Club',
    'Send birthday greeting with special offer',
    'drip', 'active', 'date',
    '{"trigger_date_field": "date_of_birth", "days_before": 7, "recurring": "yearly"}',
    TRUE),

(1, 'Equipment Maintenance Reminder',
    'Remind customers to service equipment annually',
    'transactional', 'active', 'date',
    '{"trigger_date_field": "last_equipment_service", "days_after": 365}',
    TRUE),

(1, 'Dive Log Encouragement',
    'Encourage dive logging after completed dives',
    'trigger', 'draft', 'event',
    '{"event": "dive_completed", "delay": 2, "delay_unit": "hours"}',
    TRUE);

-- =====================================================
-- Sample Workflow Steps for "New Customer Welcome Series"
-- =====================================================

INSERT INTO `automation_workflow_steps` (
    `workflow_id`, `tenant_id`, `step_order`, `step_name`, `step_type`,
    `delay_amount`, `delay_unit`, `subject_line`, `email_content`
) VALUES
-- Step 1: Immediate welcome
(1, 1, 1, 'Welcome Email', 'email', 0, 'minutes',
    'Welcome to [Shop Name] - Your Diving Adventure Begins!',
    '<h1>Welcome!</h1><p>We\'re thrilled to have you join our diving community...</p>'),

-- Step 2: Day 2 - Getting started
(1, 1, 2, 'Getting Started Guide', 'email', 2, 'days',
    'Your Guide to Getting Started with Scuba Diving',
    '<h1>Ready to Dive In?</h1><p>Here\'s everything you need to know about our Open Water course...</p>'),

-- Step 3: Day 5 - Social proof
(1, 1, 3, 'Customer Success Stories', 'email', 5, 'days',
    'See Why Divers Love [Shop Name]',
    '<h1>Don\'t Just Take Our Word For It</h1><p>Here\'s what our certified divers are saying...</p>'),

-- Step 4: Day 9 - Special offer
(1, 1, 4, 'Exclusive New Customer Offer', 'email', 9, 'days',
    'Special Offer: 15% Off Your First Course',
    '<h1>We Have a Gift For You!</h1><p>Get 15% off any course when you book this week...</p>'),

-- Step 5: Day 14 - Final touch
(1, 1, 5, 'Final Reminder', 'email', 14, 'days',
    'Last Chance: Your Welcome Offer Expires Soon',
    '<h1>Don\'t Miss Out!</h1><p>Your 15% discount expires in 48 hours...</p>');

-- =====================================================
-- Sample Goals
-- =====================================================

INSERT INTO `automation_workflow_goals` (
    `workflow_id`, `tenant_id`, `goal_name`, `goal_type`, `goal_config`, `goal_value`
) VALUES
(1, 1, 'First Course Booking', 'booking',
    '{"booking_type": "course", "min_value": 0}', 399.00),

(2, 1, 'Complete Abandoned Booking', 'booking',
    '{"complete_abandoned_booking": true}', 299.00),

(4, 1, 'Book Advanced Course', 'booking',
    '{"course_level": "advanced"}', 299.00);
