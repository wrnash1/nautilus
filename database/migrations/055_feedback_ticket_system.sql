-- ================================================
-- Nautilus V6 - Feedback & Support Ticket System
-- Migration: 055_feedback_ticket_system.sql
-- Description: Built-in feedback and trouble ticket system
-- ================================================

-- Feedback tickets (bugs, feature requests, general feedback)
CREATE TABLE IF NOT EXISTS `feedback_tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(50) UNIQUE NOT NULL COMMENT 'e.g., TICKET-2025-001',

    -- Submitter Information
    `submitted_by_user_id` INT UNSIGNED COMMENT 'User who submitted (if logged in)',
    `submitted_by_name` VARCHAR(255) NOT NULL COMMENT 'Name of submitter',
    `submitted_by_email` VARCHAR(255) NOT NULL COMMENT 'Email for follow-up',
    `submitted_by_phone` VARCHAR(50),
    `dive_shop_name` VARCHAR(255) COMMENT 'Which dive shop is reporting',
    `dive_shop_location` VARCHAR(255),

    -- Ticket Details
    `ticket_type` ENUM('bug', 'feature_request', 'question', 'improvement', 'documentation', 'other') NOT NULL,
    `severity` ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    `priority` ENUM('urgent', 'high', 'normal', 'low') DEFAULT 'normal',

    `title` VARCHAR(255) NOT NULL COMMENT 'Short description',
    `description` TEXT NOT NULL COMMENT 'Detailed description',

    -- Context Information (automatically captured)
    `page_url` VARCHAR(500) COMMENT 'Page where issue occurred',
    `browser` VARCHAR(100) COMMENT 'Browser name and version',
    `operating_system` VARCHAR(100) COMMENT 'OS name and version',
    `screen_resolution` VARCHAR(50),
    `nautilus_version` VARCHAR(50) DEFAULT 'Beta 1',
    `php_version` VARCHAR(50),
    `mysql_version` VARCHAR(50),

    -- Reproduction Steps
    `steps_to_reproduce` TEXT COMMENT 'How to reproduce the issue',
    `expected_behavior` TEXT COMMENT 'What should happen',
    `actual_behavior` TEXT COMMENT 'What actually happens',

    -- Attachments
    `screenshots` JSON COMMENT 'Array of screenshot paths',
    `error_logs` TEXT COMMENT 'Error messages or logs',
    `additional_files` JSON COMMENT 'Other attachments',

    -- Status & Assignment
    `status` ENUM('new', 'acknowledged', 'in_progress', 'need_info', 'resolved', 'closed', 'wont_fix', 'duplicate') DEFAULT 'new',
    `assigned_to` INT UNSIGNED COMMENT 'Developer assigned',
    `assigned_at` TIMESTAMP NULL,

    -- Resolution
    `resolved_by` INT UNSIGNED,
    `resolved_at` TIMESTAMP NULL,
    `resolution_notes` TEXT,
    `resolution_version` VARCHAR(50) COMMENT 'Version where fixed',

    -- Follow-up
    `needs_follow_up` BOOLEAN DEFAULT FALSE,
    `follow_up_date` DATE,
    `follow_up_notes` TEXT,

    -- Voting/Priority
    `upvotes` INT DEFAULT 0 COMMENT 'Other users who want this too',
    `duplicate_of` INT UNSIGNED COMMENT 'If duplicate, link to original',

    -- Internal Notes
    `internal_notes` TEXT COMMENT 'Developer notes (not visible to submitter)',
    `technical_details` TEXT COMMENT 'Technical investigation notes',

    -- Timestamps
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_activity_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`resolved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`duplicate_of`) REFERENCES `feedback_tickets`(`id`) ON DELETE SET NULL,

    INDEX `idx_ticket_number` (`ticket_number`),
    INDEX `idx_status` (`status`),
    INDEX `idx_type` (`ticket_type`),
    INDEX `idx_severity` (`severity`),
    INDEX `idx_submitter` (`submitted_by_user_id`),
    INDEX `idx_assigned` (`assigned_to`),
    INDEX `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket comments/updates
CREATE TABLE IF NOT EXISTS `feedback_ticket_comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,

    `comment_by_user_id` INT UNSIGNED,
    `comment_by_name` VARCHAR(255) NOT NULL,
    `comment_by_email` VARCHAR(255),
    `is_staff` BOOLEAN DEFAULT FALSE COMMENT 'Is this from developer/staff?',

    `comment_text` TEXT NOT NULL,
    `comment_type` ENUM('comment', 'status_change', 'assignment', 'resolution', 'note') DEFAULT 'comment',

    -- For status changes
    `old_status` VARCHAR(50),
    `new_status` VARCHAR(50),

    -- Attachments
    `attachments` JSON COMMENT 'Screenshots or files attached to comment',

    -- Visibility
    `is_internal` BOOLEAN DEFAULT FALSE COMMENT 'Only visible to staff',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`ticket_id`) REFERENCES `feedback_tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`comment_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_ticket` (`ticket_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket votes (for feature requests)
CREATE TABLE IF NOT EXISTS `feedback_ticket_votes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED,
    `dive_shop_name` VARCHAR(255),
    `voter_email` VARCHAR(255) NOT NULL,
    `vote_type` ENUM('upvote', 'critical', 'nice_to_have') DEFAULT 'upvote',
    `comment` TEXT COMMENT 'Why this is important to them',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`ticket_id`) REFERENCES `feedback_tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    UNIQUE KEY `unique_vote` (`ticket_id`, `voter_email`),
    INDEX `idx_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback categories (for organizing)
CREATE TABLE IF NOT EXISTS `feedback_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL,
    `category_slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `icon` VARCHAR(50) COMMENT 'Bootstrap icon class',
    `color` VARCHAR(7) DEFAULT '#0066CC',
    `display_order` INT DEFAULT 0,
    `is_active` BOOLEAN DEFAULT TRUE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_slug` (`category_slug`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket category assignments (many-to-many)
CREATE TABLE IF NOT EXISTS `feedback_ticket_categories` (
    `ticket_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,

    PRIMARY KEY (`ticket_id`, `category_id`),
    FOREIGN KEY (`ticket_id`) REFERENCES `feedback_tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `feedback_categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email notifications for ticket updates
CREATE TABLE IF NOT EXISTS `feedback_ticket_notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `recipient_email` VARCHAR(255) NOT NULL,
    `notification_type` ENUM('new_ticket', 'status_change', 'new_comment', 'assignment', 'resolution') NOT NULL,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `email_opened` BOOLEAN DEFAULT FALSE,
    `opened_at` TIMESTAMP NULL,

    FOREIGN KEY (`ticket_id`) REFERENCES `feedback_tickets`(`id`) ON DELETE CASCADE,

    INDEX `idx_ticket` (`ticket_id`),
    INDEX `idx_sent` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default categories
INSERT INTO `feedback_categories` (`category_name`, `category_slug`, `description`, `icon`, `color`, `display_order`) VALUES
('Point of Sale', 'pos', 'Issues related to checkout, payments, transactions', 'bi-cash-register', '#2ecc71', 1),
('Inventory', 'inventory', 'Stock management, products, barcode scanning', 'bi-box-seam', '#3498db', 2),
('Customers', 'customers', 'Customer management, profiles, certifications', 'bi-people', '#9b59b6', 3),
('Courses', 'courses', 'Course enrollment, scheduling, student tracking', 'bi-book', '#e74c3c', 4),
('PADI Compliance', 'padi', 'Skills checkoff, medical forms, waivers, incidents', 'bi-award', '#f39c12', 5),
('Equipment Rentals', 'rentals', 'Rental management, equipment tracking', 'bi-tools', '#1abc9c', 6),
('Trips', 'trips', 'Trip management, manifests, travel', 'bi-airplane', '#34495e', 7),
('Reporting', 'reporting', 'Reports, analytics, dashboards', 'bi-graph-up', '#16a085', 8),
('Cash Drawer', 'cash-drawer', 'Cash management, sessions, reconciliation', 'bi-wallet2', '#27ae60', 9),
('User Interface', 'ui', 'Design, layout, navigation, mobile/tablet', 'bi-phone', '#8e44ad', 10),
('Performance', 'performance', 'Speed, loading times, optimization', 'bi-speedometer', '#c0392b', 11),
('Security', 'security', 'Access control, permissions, data protection', 'bi-shield-check', '#d35400', 12),
('Installation', 'installation', 'Setup, configuration, deployment', 'bi-gear', '#7f8c8d', 13),
('Documentation', 'documentation', 'Guides, help text, tutorials', 'bi-file-text', '#95a5a6', 14),
('Integration', 'integration', 'API, third-party services, imports/exports', 'bi-plug', '#2c3e50', 15);
