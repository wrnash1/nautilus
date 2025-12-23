SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `calendar_view_preferences`;
DROP TABLE IF EXISTS `calendar_blackout_dates`;
DROP TABLE IF EXISTS `instructor_availability`;
DROP TABLE IF EXISTS `bookable_resources`;
DROP TABLE IF EXISTS `calendar_resource_allocations`;
DROP TABLE IF EXISTS `calendar_event_participants`;
DROP TABLE IF EXISTS `calendar_events`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `calendar_view_preferences`;
DROP TABLE IF EXISTS `calendar_blackout_dates`;
DROP TABLE IF EXISTS `instructor_availability`;
DROP TABLE IF EXISTS `bookable_resources`;
DROP TABLE IF EXISTS `calendar_resource_allocations`;
DROP TABLE IF EXISTS `calendar_event_participants`;
DROP TABLE IF EXISTS `calendar_events`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `calendar_view_preferences`;
DROP TABLE IF EXISTS `calendar_blackout_dates`;
DROP TABLE IF EXISTS `instructor_availability`;
DROP TABLE IF EXISTS `bookable_resources`;
DROP TABLE IF EXISTS `calendar_resource_allocations`;
DROP TABLE IF EXISTS `calendar_event_participants`;
DROP TABLE IF EXISTS `calendar_events`;

-- ================================================
-- Nautilus - Advanced Scheduling & Calendar System
-- Migration: 080_advanced_scheduling_system.sql
-- Description: Drag-and-drop calendar, resource allocation, conflict detection
-- ================================================

-- Scheduling Events (Unified Calendar)
CREATE TABLE IF NOT EXISTS `calendar_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Event Identity
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `event_type` ENUM('course', 'trip', 'rental', 'maintenance', 'meeting', 'personal', 'other') NOT NULL,
    `color` VARCHAR(20) DEFAULT '#3788d8' COMMENT 'Hex color for calendar display',

    -- Timing
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `all_day` BOOLEAN DEFAULT FALSE,
    `timezone` VARCHAR(50) DEFAULT 'UTC',

    -- Recurrence
    `is_recurring` BOOLEAN DEFAULT FALSE,
    `recurrence_rule` VARCHAR(500) NULL COMMENT 'iCal RRULE format',
    `recurrence_end_date` DATE NULL,
    `parent_event_id` BIGINT UNSIGNED NULL COMMENT 'Link to parent if recurring instance',

    -- Location
    `location` VARCHAR(255) NULL,
    `dive_site_id` BIGINT UNSIGNED NULL,
    `room` VARCHAR(100) NULL,
    `online_meeting_url` VARCHAR(500) NULL,

    -- Related Entities
    `related_entity_type` VARCHAR(100) NULL COMMENT 'course, trip, rental, etc.',
    `related_entity_id` BIGINT UNSIGNED NULL,
    `course_id` BIGINT UNSIGNED NULL,
    `trip_id` BIGINT UNSIGNED NULL,

    -- Participants
    `organizer_user_id` BIGINT UNSIGNED NULL,
    `max_participants` INT NULL,
    `current_participants` INT DEFAULT 0,

    -- Resources Required
    `resources_required` JSON NULL COMMENT 'boats, instructors, equipment, rooms',

    -- Status
    `status` ENUM('scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'postponed') DEFAULT 'scheduled',
    `visibility` ENUM('public', 'private', 'staff_only') DEFAULT 'public',

    -- Reminders
    `reminder_minutes_before` INT NULL COMMENT 'Minutes before event to send reminder',
    `reminder_sent` BOOLEAN DEFAULT FALSE,

    -- Notifications
    `notify_participants` BOOLEAN DEFAULT TRUE,
    `notify_on_change` BOOLEAN DEFAULT TRUE,

    -- Notes
    `internal_notes` TEXT NULL,
    `customer_notes` TEXT NULL,

    -- Conflict Detection
    `allows_conflicts` BOOLEAN DEFAULT FALSE,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by_user_id` BIGINT UNSIGNED NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`dive_site_id`) REFERENCES `dive_sites`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`trip_id`) REFERENCES `trips`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`organizer_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`parent_event_id`) REFERENCES `calendar_events`(`id`) ON DELETE CASCADE,

    INDEX `idx_start_datetime` (`start_datetime`),
    INDEX `idx_end_datetime` (`end_datetime`),
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_organizer` (`organizer_user_id`),
    INDEX `idx_related` (`related_entity_type`, `related_entity_id`),
    INDEX `idx_date_range` (`start_datetime`, `end_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event Participants
CREATE TABLE IF NOT EXISTS `calendar_event_participants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `event_id` BIGINT UNSIGNED NOT NULL,

    -- Participant
    `participant_type` ENUM('customer', 'staff', 'instructor', 'external') NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `external_name` VARCHAR(255) NULL COMMENT 'If external participant',
    `external_email` VARCHAR(255) NULL,

    -- Role in Event
    `role` ENUM('organizer', 'instructor', 'student', 'assistant', 'attendee', 'guest') DEFAULT 'attendee',

    -- RSVP
    `rsvp_status` ENUM('pending', 'accepted', 'declined', 'tentative', 'no_response') DEFAULT 'pending',
    `rsvp_date` TIMESTAMP NULL,

    -- Attendance
    `checked_in` BOOLEAN DEFAULT FALSE,
    `checked_in_at` TIMESTAMP NULL,
    `attended` BOOLEAN DEFAULT FALSE,

    -- Notifications
    `notification_sent` BOOLEAN DEFAULT FALSE,
    `reminder_sent` BOOLEAN DEFAULT FALSE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`event_id`) REFERENCES `calendar_events`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    INDEX `idx_event` (`event_id`),
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_rsvp` (`rsvp_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resource Allocation (Boats, Equipment, Rooms, Staff)
CREATE TABLE IF NOT EXISTS `calendar_resource_allocations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `event_id` BIGINT UNSIGNED NOT NULL,

    -- Resource
    `resource_type` ENUM('boat', 'vehicle', 'room', 'equipment', 'instructor', 'staff', 'other') NOT NULL,
    `resource_id` BIGINT UNSIGNED NULL,
    `resource_name` VARCHAR(255) NULL,

    -- Allocation Details
    `quantity` INT DEFAULT 1,
    `allocated_from` DATETIME NOT NULL,
    `allocated_to` DATETIME NOT NULL,

    -- Status
    `status` ENUM('requested', 'confirmed', 'in_use', 'returned', 'cancelled') DEFAULT 'requested',

    -- Notes
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`event_id`) REFERENCES `calendar_events`(`id`) ON DELETE CASCADE,

    INDEX `idx_event` (`event_id`),
    INDEX `idx_resource` (`resource_type`, `resource_id`),
    INDEX `idx_dates` (`allocated_from`, `allocated_to`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resources (Boats, Vehicles, Rooms)
CREATE TABLE IF NOT EXISTS `bookable_resources` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Resource Identity
    `resource_name` VARCHAR(255) NOT NULL,
    `resource_type` ENUM('boat', 'vehicle', 'classroom', 'pool', 'conference_room', 'equipment_set', 'other') NOT NULL,
    `resource_code` VARCHAR(50) NULL,

    -- Details
    `description` TEXT NULL,
    `capacity` INT NULL COMMENT 'Max people/items',
    `location` VARCHAR(255) NULL,

    -- Boat/Vehicle Specific
    `make` VARCHAR(100) NULL,
    `model` VARCHAR(100) NULL,
    `year` INT NULL,
    `registration_number` VARCHAR(100) NULL,
    `license_plate` VARCHAR(50) NULL,

    -- Features
    `features` JSON NULL COMMENT 'Air conditioning, wifi, dive platform, etc.',
    `amenities` JSON NULL,

    -- Availability
    `is_active` BOOLEAN DEFAULT TRUE,
    `requires_operator` BOOLEAN DEFAULT FALSE,
    `requires_license` BOOLEAN DEFAULT FALSE,

    -- Booking Rules
    `min_booking_duration_hours` DECIMAL(5,2) NULL,
    `max_booking_duration_hours` DECIMAL(5,2) NULL,
    `booking_buffer_minutes` INT DEFAULT 30 COMMENT 'Buffer between bookings',

    -- Costs
    `hourly_rate` DECIMAL(10,2) NULL,
    `daily_rate` DECIMAL(10,2) NULL,
    `fuel_cost_per_hour` DECIMAL(10,2) NULL,

    -- Maintenance
    `last_maintenance_date` DATE NULL,
    `next_maintenance_date` DATE NULL,
    `maintenance_notes` TEXT NULL,

    -- Photo
    `photo_url` VARCHAR(500) NULL,

    -- Display
    `calendar_color` VARCHAR(20) DEFAULT '#4CAF50',
    `display_order` INT DEFAULT 0,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,

    INDEX `idx_resource_type` (`resource_type`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Instructor Availability
CREATE TABLE IF NOT EXISTS `instructor_availability` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,

    -- Availability Pattern
    `day_of_week` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,

    -- Effective Dates
    `effective_from` DATE NULL,
    `effective_to` DATE NULL,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,

    -- Notes
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    INDEX `idx_user` (`user_id`),
    INDEX `idx_day` (`day_of_week`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Time Off / Blackout Dates
CREATE TABLE IF NOT EXISTS `calendar_blackout_dates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Who/What is unavailable
    `blackout_type` ENUM('instructor', 'resource', 'location', 'global') NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `resource_id` BIGINT UNSIGNED NULL,

    -- Date Range
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NOT NULL,
    `all_day` BOOLEAN DEFAULT TRUE,

    -- Reason
    `reason` VARCHAR(255) NULL,
    `notes` TEXT NULL,

    -- Recurrence (for holidays)
    `is_recurring` BOOLEAN DEFAULT FALSE,
    `recurrence_rule` VARCHAR(500) NULL,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_by_user_id` BIGINT UNSIGNED NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    INDEX `idx_dates` (`start_datetime`, `end_datetime`),
    INDEX `idx_type` (`blackout_type`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Calendar Views/Filters (User Preferences)
CREATE TABLE IF NOT EXISTS `calendar_view_preferences` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,

    -- View Settings
    `default_view` ENUM('month', 'week', 'day', 'agenda', 'timeline') DEFAULT 'week',
    `start_day_of_week` ENUM('sunday', 'monday', 'saturday') DEFAULT 'monday',
    `time_slot_duration` INT DEFAULT 30 COMMENT 'Minutes per slot',
    `start_hour` INT DEFAULT 7,
    `end_hour` INT DEFAULT 20,

    -- Visible Event Types
    `visible_event_types` JSON NULL COMMENT 'Array of event types to show',
    `visible_resources` JSON NULL,

    -- Color Scheme
    `color_by` ENUM('event_type', 'status', 'resource', 'custom') DEFAULT 'event_type',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,

    UNIQUE KEY `unique_user_prefs` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed sample resources
INSERT INTO `bookable_resources` (`resource_name`, `resource_type`, `capacity`, `description`, `hourly_rate`, `daily_rate`, `calendar_color`) VALUES
('Dive Boat Alpha', 'boat', 20, '45ft dive boat with full dive platform and equipment storage', 150.00, 1000.00, '#2196F3'),
('Dive Boat Bravo', 'boat', 12, '32ft speedboat for small groups', 100.00, 650.00, '#1976D2'),
('Classroom A', 'classroom', 30, 'Main training classroom with projector and whiteboards', NULL, NULL, '#4CAF50'),
('Classroom B', 'classroom', 15, 'Small classroom for theory sessions', NULL, NULL, '#66BB6A'),
('Training Pool', 'pool', 20, 'Heated pool with depth to 12 feet for confined water training', NULL, NULL, '#00BCD4'),
('Equipment Van', 'vehicle', 8, 'Cargo van for equipment transport', 25.00, 150.00, '#FF9800');


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;