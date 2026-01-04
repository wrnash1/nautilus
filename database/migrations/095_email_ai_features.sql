-- Migration: Email Marketing & AI Features
-- SendGrid integration, email templates, AI training data
-- Date: 2026-01-04

-- Email templates
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `html_content` LONGTEXT NOT NULL,
    `text_content` TEXT NULL,
    `category` ENUM('newsletter', 'transactional', 'reminder', 'marketing', 'welcome', 'other') DEFAULT 'other',
    `merge_tags` JSON NULL COMMENT 'Available merge tags for this template',
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant_category` (`tenant_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email sending logs
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NULL,
    `template_id` BIGINT UNSIGNED NULL,
    `to_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `status` ENUM('queued', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed') DEFAULT 'queued',
    `provider` VARCHAR(50) DEFAULT 'sendgrid',
    `provider_message_id` VARCHAR(255) NULL,
    `opened_at` DATETIME NULL,
    `clicked_at` DATETIME NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tenant_status` (`tenant_id`, `status`),
    INDEX `idx_to_email` (`to_email`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email campaigns
CREATE TABLE IF NOT EXISTS `email_campaigns` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `template_id` BIGINT UNSIGNED NULL,
    `segment` ENUM('all', 'newsletter', 'customers', 'divers', 'students', 'custom') DEFAULT 'all',
    `custom_query` TEXT NULL,
    `status` ENUM('draft', 'scheduled', 'sending', 'sent', 'cancelled') DEFAULT 'draft',
    `scheduled_at` DATETIME NULL,
    `sent_at` DATETIME NULL,
    `total_recipients` INT UNSIGNED DEFAULT 0,
    `total_sent` INT UNSIGNED DEFAULT 0,
    `total_delivered` INT UNSIGNED DEFAULT 0,
    `total_opened` INT UNSIGNED DEFAULT 0,
    `total_clicked` INT UNSIGNED DEFAULT 0,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant_status` (`tenant_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI training data exports
CREATE TABLE IF NOT EXISTS `ai_training_exports` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `tenant_hash` VARCHAR(64) NOT NULL COMMENT 'Anonymous identifier',
    `export_date` DATE NOT NULL,
    `product_count` INT UNSIGNED DEFAULT 0,
    `course_count` INT UNSIGNED DEFAULT 0,
    `uploaded_to_github` BOOLEAN DEFAULT FALSE,
    `github_commit_sha` VARCHAR(64) NULL,
    `file_path` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_date` (`tenant_id`, `export_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI recognition logs for training improvement
CREATE TABLE IF NOT EXISTS `ai_recognition_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `recognition_type` ENUM('product', 'barcode', 'cert_card', 'serial_number') NOT NULL,
    `image_path` VARCHAR(500) NULL,
    `recognized_value` TEXT NULL,
    `matched_product_id` BIGINT UNSIGNED NULL,
    `confidence_score` DECIMAL(5,4) NULL,
    `was_correct` BOOLEAN NULL COMMENT 'User feedback on accuracy',
    `correction_value` TEXT NULL COMMENT 'Correct value if wrong',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_type` (`recognition_type`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email templates
INSERT INTO `email_templates` (`tenant_id`, `name`, `subject`, `html_content`, `category`, `merge_tags`) VALUES
(1, 'Welcome Email', 'Welcome to {{shop_name}}!', 
'<!DOCTYPE html>
<html>
<head><style>body{font-family:Arial,sans-serif;}</style></head>
<body>
<h1>Welcome, {{first_name}}!</h1>
<p>Thank you for joining {{shop_name}}. We''re excited to have you as part of our diving community!</p>
<p>Visit us at {{shop_address}} or call us at {{shop_phone}}.</p>
<p>Happy Diving!<br>{{shop_name}} Team</p>
</body>
</html>', 'welcome', '["first_name", "shop_name", "shop_address", "shop_phone"]'),

(1, 'Course Reminder', 'Your {{course_name}} Class is Coming Up!',
'<!DOCTYPE html>
<html>
<head><style>body{font-family:Arial,sans-serif;}</style></head>
<body>
<h1>Hi {{first_name}},</h1>
<p>This is a reminder that your <strong>{{course_name}}</strong> class is scheduled for:</p>
<p><strong>Date:</strong> {{course_date}}<br>
<strong>Time:</strong> {{course_time}}<br>
<strong>Location:</strong> {{location}}</p>
<p>Please bring the following items:</p>
<ul>
<li>Valid ID</li>
<li>Swimsuit</li>
<li>Towel</li>
</ul>
<p>See you there!<br>{{shop_name}}</p>
</body>
</html>', 'reminder', '["first_name", "course_name", "course_date", "course_time", "location", "shop_name"]'),

(1, 'Trip Confirmation', 'Your {{trip_name}} Trip is Confirmed!',
'<!DOCTYPE html>
<html>
<head><style>body{font-family:Arial,sans-serif;}</style></head>
<body>
<h1>Trip Confirmed! 🌊</h1>
<p>Hi {{first_name}},</p>
<p>Great news! Your spot on <strong>{{trip_name}}</strong> is confirmed.</p>
<p><strong>Departure:</strong> {{departure_date}}<br>
<strong>Return:</strong> {{return_date}}<br>
<strong>Meeting Point:</strong> {{meeting_point}}</p>
<p>Don''t forget to bring your certification cards and dive log!</p>
<p>Questions? Call us at {{shop_phone}}.</p>
<p>{{shop_name}}</p>
</body>
</html>', 'transactional', '["first_name", "trip_name", "departure_date", "return_date", "meeting_point", "shop_phone", "shop_name"]'),

(1, 'Monthly Newsletter', '{{month}} Diving News & Deals',
'<!DOCTYPE html>
<html>
<head><style>body{font-family:Arial,sans-serif;} .deal{background:#f0f8ff;padding:15px;margin:10px 0;}</style></head>
<body>
<h1>{{shop_name}} Newsletter - {{month}}</h1>
<p>Hi {{first_name}},</p>
<p>Here''s what''s happening at the shop this month!</p>
<div class="deal">
<h3>Featured Course</h3>
<p>{{featured_course}}</p>
</div>
<div class="deal">
<h3>Upcoming Trips</h3>
<p>{{upcoming_trips}}</p>
</div>
<div class="deal">
<h3>Gear Sale</h3>
<p>{{sale_items}}</p>
</div>
<p>See you at the shop!<br>{{shop_name}}</p>
<hr>
<small><a href="{{unsubscribe_url}}">Unsubscribe</a></small>
</body>
</html>', 'newsletter', '["first_name", "shop_name", "month", "featured_course", "upcoming_trips", "sale_items", "unsubscribe_url"]');
