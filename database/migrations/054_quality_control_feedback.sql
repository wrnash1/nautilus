SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `feedback_triggers`;
DROP TABLE IF EXISTS `quality_control_alerts`;
DROP TABLE IF EXISTS `instructor_performance_metrics`;
DROP TABLE IF EXISTS `feedback_email_log`;
DROP TABLE IF EXISTS `customer_feedback`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `feedback_triggers`;
DROP TABLE IF EXISTS `quality_control_alerts`;
DROP TABLE IF EXISTS `instructor_performance_metrics`;
DROP TABLE IF EXISTS `feedback_email_log`;
DROP TABLE IF EXISTS `customer_feedback`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `feedback_triggers`;
DROP TABLE IF EXISTS `quality_control_alerts`;
DROP TABLE IF EXISTS `instructor_performance_metrics`;
DROP TABLE IF EXISTS `feedback_email_log`;
DROP TABLE IF EXISTS `customer_feedback`;

-- ================================================
-- Nautilus V6 - Quality Control & Customer Feedback
-- Migration: 054_quality_control_feedback.sql
-- Description: Automated customer feedback collection after courses/trips
-- ================================================

-- Customer feedback collection
CREATE TABLE IF NOT EXISTS `customer_feedback` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `course_id` BIGINT UNSIGNED,
    `enrollment_id` BIGINT UNSIGNED,
    `trip_id` BIGINT UNSIGNED,
    `feedback_type` ENUM('course', 'trip', 'rental', 'store_visit', 'general') NOT NULL,

    -- Ratings (1-5 stars)
    `overall_rating` INT CHECK (`overall_rating` BETWEEN 1 AND 5),
    `instructor_rating` INT CHECK (`instructor_rating` BETWEEN 1 AND 5),
    `equipment_rating` INT CHECK (`equipment_rating` BETWEEN 1 AND 5),
    `facilities_rating` INT CHECK (`facilities_rating` BETWEEN 1 AND 5),
    `value_rating` INT CHECK (`value_rating` BETWEEN 1 AND 5),

    -- Open-ended feedback
    `what_went_well` TEXT COMMENT 'Positive feedback',
    `what_needs_improvement` TEXT COMMENT 'Constructive feedback',
    `additional_comments` TEXT,

    -- Course-specific questions
    `knowledge_development_clear` BOOLEAN COMMENT 'Was eLearning/classroom clear?',
    `confined_water_comfortable` BOOLEAN COMMENT 'Felt comfortable in pool?',
    `open_water_prepared` BOOLEAN COMMENT 'Felt prepared for open water?',
    `pace_appropriate` BOOLEAN COMMENT 'Was course pace appropriate?',

    -- Instructor-specific (for courses)
    `instructor_id` BIGINT UNSIGNED,
    `instructor_professional` BOOLEAN,
    `instructor_patient` BOOLEAN,
    `instructor_knowledgeable` BOOLEAN,
    `instructor_safety_focused` BOOLEAN,

    -- Recommendations
    `would_recommend` BOOLEAN COMMENT 'Would recommend to friends?',
    `likely_to_return` BOOLEAN COMMENT 'Likely to come back?',
    `interested_in_continuing_ed` BOOLEAN COMMENT 'Interested in more courses?',

    -- Testimonial
    `allow_testimonial` BOOLEAN DEFAULT FALSE COMMENT 'Can we use this as testimonial?',
    `testimonial_text` TEXT,
    `allow_public_review` BOOLEAN DEFAULT FALSE COMMENT 'Can we post online?',

    -- Submission details
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `submitted_via` ENUM('email', 'web', 'mobile', 'in_person', 'qr_code') DEFAULT 'email',
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,

    -- Follow-up
    `requires_follow_up` BOOLEAN DEFAULT FALSE,
    `follow_up_reason` VARCHAR(255),
    `follow_up_assigned_to` BIGINT UNSIGNED COMMENT 'Staff member assigned',
    `follow_up_completed` BOOLEAN DEFAULT FALSE,
    `follow_up_completed_at` TIMESTAMP NULL,
    `follow_up_notes` TEXT,

    -- Privacy
    `anonymous` BOOLEAN DEFAULT FALSE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`follow_up_assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_course` (`course_id`),
    INDEX `idx_instructor` (`instructor_id`),
    INDEX `idx_rating` (`overall_rating`),
    INDEX `idx_submitted` (`submitted_at`),
    INDEX `idx_follow_up` (`requires_follow_up`, `follow_up_completed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback email tracking
CREATE TABLE IF NOT EXISTS `feedback_email_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `enrollment_id` BIGINT UNSIGNED,
    `trip_id` BIGINT UNSIGNED,
    `email_type` VARCHAR(100) NOT NULL COMMENT 'course_feedback, trip_feedback, etc.',
    `email_subject` VARCHAR(255),
    `feedback_link_token` VARCHAR(100) UNIQUE COMMENT 'Unique token for feedback form access',

    -- Email tracking
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `delivered_at` TIMESTAMP NULL,
    `opened_at` TIMESTAMP NULL,
    `clicked_at` TIMESTAMP NULL,
    `feedback_submitted_at` TIMESTAMP NULL,
    `bounced` BOOLEAN DEFAULT FALSE,
    `bounce_reason` TEXT,

    -- Reminders
    `reminder_sent_count` INT DEFAULT 0,
    `last_reminder_sent` TIMESTAMP NULL,

    -- Response
    `feedback_id` BIGINT UNSIGNED COMMENT 'Linked feedback response',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`feedback_id`) REFERENCES `customer_feedback`(`id`) ON DELETE SET NULL,

    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_sent` (`sent_at`),
    INDEX `idx_token` (`feedback_link_token`),
    INDEX `idx_enrollment` (`enrollment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Instructor performance metrics (aggregated from feedback)
CREATE TABLE IF NOT EXISTS `instructor_performance_metrics` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `instructor_id` BIGINT UNSIGNED NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,

    -- Aggregated ratings
    `total_feedbacks` INT DEFAULT 0,
    `avg_instructor_rating` DECIMAL(3,2),
    `avg_overall_rating` DECIMAL(3,2),

    -- Detailed scores
    `professional_score` DECIMAL(3,2),
    `patient_score` DECIMAL(3,2),
    `knowledgeable_score` DECIMAL(3,2),
    `safety_score` DECIMAL(3,2),

    -- Recommendations
    `recommendation_rate` DECIMAL(5,2) COMMENT 'Percentage who would recommend',
    `continuing_ed_interest` DECIMAL(5,2) COMMENT 'Percentage interested in more courses',

    -- Course completion rate
    `courses_started` INT DEFAULT 0,
    `courses_completed` INT DEFAULT 0,
    `completion_rate` DECIMAL(5,2),

    -- Students
    `total_students` INT DEFAULT 0,
    `students_certified` INT DEFAULT 0,
    `students_referred` INT DEFAULT 0,

    -- Generated
    `last_calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    UNIQUE KEY `unique_instructor_period` (`instructor_id`, `period_start`, `period_end`),
    INDEX `idx_instructor` (`instructor_id`),
    INDEX `idx_period` (`period_start`, `period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quality control alerts (for management)
CREATE TABLE IF NOT EXISTS `quality_control_alerts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `alert_type` ENUM('low_rating', 'negative_feedback', 'equipment_issue', 'safety_concern', 'complaint') NOT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL,

    -- Related records
    `feedback_id` BIGINT UNSIGNED,
    `customer_id` BIGINT UNSIGNED,
    `instructor_id` BIGINT UNSIGNED,
    `course_id` BIGINT UNSIGNED,

    -- Alert details
    `alert_title` VARCHAR(255) NOT NULL,
    `alert_description` TEXT,
    `trigger_reason` TEXT COMMENT 'Why this alert was created',

    -- Response
    `status` ENUM('new', 'acknowledged', 'investigating', 'resolved', 'escalated') DEFAULT 'new',
    `assigned_to` BIGINT UNSIGNED,
    `acknowledged_by` BIGINT UNSIGNED,
    `acknowledged_at` TIMESTAMP NULL,
    `resolved_by` BIGINT UNSIGNED,
    `resolved_at` TIMESTAMP NULL,
    `resolution_notes` TEXT,

    -- Actions taken
    `action_plan` TEXT,
    `preventive_measures` TEXT,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`feedback_id`) REFERENCES `customer_feedback`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`acknowledged_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`resolved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_status` (`status`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_type` (`alert_type`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback triggers (automated feedback requests)
CREATE TABLE IF NOT EXISTS `feedback_triggers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `trigger_name` VARCHAR(255) NOT NULL,
    `trigger_type` ENUM('course_completion', 'trip_completion', 'rental_return', 'visit', 'quarterly') NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,

    -- Timing
    `delay_hours` INT DEFAULT 24 COMMENT 'Hours after event to send feedback request',
    `reminder_enabled` BOOLEAN DEFAULT TRUE,
    `reminder_delay_days` INT DEFAULT 3 COMMENT 'Days after initial email to send reminder',
    `max_reminders` INT DEFAULT 2,

    -- Email content
    `email_subject` VARCHAR(255),
    `email_template` TEXT COMMENT 'HTML email template',

    -- Conditions
    `applicable_courses` JSON COMMENT 'Course IDs this applies to (null = all)',
    `min_rating_for_testimonial` INT DEFAULT 4 COMMENT 'Only ask for testimonial if rating >= this',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_type` (`trigger_type`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default feedback triggers
INSERT INTO `feedback_triggers` (`trigger_name`, `trigger_type`, `delay_hours`, `email_subject`, `email_template`) VALUES
('Course Completion Feedback', 'course_completion', 24,
'How was your diving course with us?',
'<p>Hi {customer_name},</p><p>Congratulations on completing your {course_name}!</p><p>We would love to hear about your experience. Your feedback helps us improve and serve future students better.</p><p><a href="{feedback_link}">Share Your Feedback (2 minutes)</a></p><p>Thank you for choosing us!<br>The Team</p>'),

('Trip Feedback', 'trip_completion', 6,
'How was your dive trip?',
'<p>Hi {customer_name},</p><p>We hope you had an amazing time on your dive trip to {trip_destination}!</p><p>Please take a moment to share your experience:</p><p><a href="{feedback_link}">Leave Feedback</a></p><p>Safe diving!<br>The Team</p>'),

('Quarterly Check-in', 'quarterly', 2160,
'We miss you! How are you doing?',
'<p>Hi {customer_name},</p><p>It has been a while since your last visit. We wanted to check in and see how you are doing!</p><p>Are you interested in:</p><ul><li>Continuing your dive education?</li><li>Joining us on an upcoming trip?</li><li>Refreshing your skills?</li></ul><p><a href="{feedback_link}">Let us know!</a></p>');


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;