-- =====================================================
-- Employee Scheduling System
-- Staff scheduling, time tracking, and shift management
-- =====================================================

-- Employee/Staff Table (extends users)
CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL COMMENT 'Link to users table',

    -- Personal Information
    `employee_number` VARCHAR(50) NOT NULL UNIQUE,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `emergency_contact_name` VARCHAR(255) NULL,
    `emergency_contact_phone` VARCHAR(20) NULL,

    -- Employment Details
    `hire_date` DATE NOT NULL,
    `termination_date` DATE NULL,
    `employment_type` ENUM('full_time', 'part_time', 'contract', 'seasonal', 'intern') NOT NULL DEFAULT 'full_time',
    `employment_status` ENUM('active', 'on_leave', 'terminated', 'suspended') DEFAULT 'active',

    -- Position
    `job_title` VARCHAR(100) NOT NULL,
    `department` ENUM('sales', 'instruction', 'retail', 'maintenance', 'management', 'customer_service', 'admin') NULL,
    `pay_rate` DECIMAL(10, 2) NULL COMMENT 'Hourly rate or salary',
    `pay_type` ENUM('hourly', 'salary', 'commission', 'hybrid') DEFAULT 'hourly',

    -- Availability
    `max_hours_per_week` INT DEFAULT 40,
    `min_hours_per_week` INT DEFAULT 0,
    `preferred_shifts` JSON NULL COMMENT 'Preferred days/times',
    `unavailable_days` JSON NULL COMMENT 'Days employee cannot work',

    -- Skills & Certifications
    `certifications` JSON NULL COMMENT 'Instructor ratings, CPR, etc.',
    `skills` JSON NULL COMMENT 'Languages, specialties, etc.',
    `can_open_store` BOOLEAN DEFAULT FALSE,
    `can_close_store` BOOLEAN DEFAULT FALSE,
    `can_handle_cash` BOOLEAN DEFAULT FALSE,

    -- Documents
    `profile_photo_url` VARCHAR(500) NULL,
    `w4_on_file` BOOLEAN DEFAULT FALSE,
    `i9_on_file` BOOLEAN DEFAULT FALSE,

    -- Notes
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_status (`tenant_id`, `employment_status`),
    INDEX idx_employee_number (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Work Schedules (weekly/monthly schedules)
CREATE TABLE IF NOT EXISTS `work_schedules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `schedule_name` VARCHAR(255) NOT NULL,

    -- Period
    `schedule_type` ENUM('weekly', 'bi_weekly', 'monthly', 'custom') DEFAULT 'weekly',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,

    -- Status
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `published_at` DATETIME NULL,
    `published_by` INT UNSIGNED NULL,

    -- Notifications
    `employees_notified` BOOLEAN DEFAULT FALSE,
    `notification_sent_at` DATETIME NULL,

    -- Stats
    `total_shifts` INT UNSIGNED DEFAULT 0,
    `total_hours_scheduled` DECIMAL(8, 2) DEFAULT 0.00,
    `total_labor_cost` DECIMAL(10, 2) DEFAULT 0.00,

    -- Notes
    `notes` TEXT NULL,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_dates (`tenant_id`, `start_date`, `end_date`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shifts
CREATE TABLE IF NOT EXISTS `shifts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `schedule_id` INT UNSIGNED NULL,
    `employee_id` INT UNSIGNED NULL,

    -- Shift Details
    `shift_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `break_minutes` INT DEFAULT 30,

    -- Calculated
    `total_hours` DECIMAL(5, 2) NULL,
    `regular_hours` DECIMAL(5, 2) NULL,
    `overtime_hours` DECIMAL(5, 2) NULL,

    -- Role/Position for this shift
    `position` VARCHAR(100) NULL COMMENT 'e.g., Sales Floor, Front Desk, Instructor',
    `location` VARCHAR(100) NULL COMMENT 'Store, Pool, Boat, etc.',

    -- Status
    `status` ENUM('scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    `confirmed_by_employee` BOOLEAN DEFAULT FALSE,
    `confirmed_at` DATETIME NULL,

    -- Coverage
    `requires_coverage` BOOLEAN DEFAULT FALSE,
    `coverage_requested` BOOLEAN DEFAULT FALSE,
    `coverage_found` BOOLEAN DEFAULT FALSE,
    `covered_by_employee_id` INT UNSIGNED NULL,

    -- Trade Requests
    `trade_requested` BOOLEAN DEFAULT FALSE,
    `trade_with_employee_id` INT UNSIGNED NULL,
    `trade_approved` BOOLEAN DEFAULT FALSE,

    -- Check-in/Check-out
    `actual_start_time` DATETIME NULL,
    `actual_end_time` DATETIME NULL,
    `actual_hours` DECIMAL(5, 2) NULL,
    `checked_in_method` ENUM('manual', 'system', 'biometric', 'mobile') NULL,
    `checked_out_method` ENUM('manual', 'system', 'biometric', 'mobile') NULL,

    -- Geolocation check-in
    `checkin_latitude` DECIMAL(10, 8) NULL,
    `checkin_longitude` DECIMAL(11, 8) NULL,
    `checkout_latitude` DECIMAL(10, 8) NULL,
    `checkout_longitude` DECIMAL(11, 8) NULL,

    -- Pay
    `pay_rate` DECIMAL(10, 2) NULL COMMENT 'Rate for this shift',
    `shift_pay_total` DECIMAL(10, 2) NULL,
    `tips` DECIMAL(10, 2) DEFAULT 0.00,
    `commission` DECIMAL(10, 2) DEFAULT 0.00,

    -- Notes
    `notes` TEXT NULL,
    `manager_notes` TEXT NULL,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`schedule_id`) REFERENCES `work_schedules`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`covered_by_employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL,
    INDEX idx_employee_date (`employee_id`, `shift_date`),
    INDEX idx_shift_date (`shift_date`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Time Off Requests
CREATE TABLE IF NOT EXISTS `time_off_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NOT NULL,

    -- Request Details
    `request_type` ENUM('vacation', 'sick', 'personal', 'unpaid', 'bereavement', 'jury_duty', 'other') NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `total_days` INT UNSIGNED NOT NULL,
    `total_hours` DECIMAL(6, 2) NULL,

    -- Partial Day
    `is_partial_day` BOOLEAN DEFAULT FALSE,
    `start_time` TIME NULL,
    `end_time` TIME NULL,

    -- Request Info
    `reason` TEXT NULL,
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Approval
    `status` ENUM('pending', 'approved', 'denied', 'cancelled') DEFAULT 'pending',
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` DATETIME NULL,
    `denial_reason` TEXT NULL,

    -- Impact
    `conflicts_with_shifts` BOOLEAN DEFAULT FALSE,
    `affected_shift_ids` JSON NULL,

    -- Notes
    `employee_notes` TEXT NULL,
    `manager_notes` TEXT NULL,

    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    INDEX idx_employee (`employee_id`),
    INDEX idx_dates (`start_date`, `end_date`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Availability Templates
CREATE TABLE IF NOT EXISTS `employee_availability` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NOT NULL,

    -- Recurring Availability
    `day_of_week` TINYINT NOT NULL COMMENT '0=Sunday, 6=Saturday',
    `is_available` BOOLEAN DEFAULT TRUE,
    `start_time` TIME NULL,
    `end_time` TIME NULL,

    -- Effective Period
    `effective_from` DATE NOT NULL,
    `effective_to` DATE NULL,

    -- Preferences
    `preferred` BOOLEAN DEFAULT FALSE COMMENT 'Preferred vs available',

    -- Notes
    `notes` VARCHAR(500) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    INDEX idx_employee_day (`employee_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shift Swap Requests
CREATE TABLE IF NOT EXISTS `shift_swap_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `requesting_employee_id` INT UNSIGNED NOT NULL,
    `shift_to_swap_id` BIGINT UNSIGNED NOT NULL,

    -- Swap Details
    `swap_type` ENUM('give_away', 'trade', 'cover_needed') NOT NULL,
    `target_employee_id` INT UNSIGNED NULL COMMENT 'Specific employee to swap with',
    `shift_offered_in_return_id` BIGINT UNSIGNED NULL COMMENT 'For trades',

    -- Request
    `reason` TEXT NULL,
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Status
    `status` ENUM('pending', 'accepted', 'declined', 'manager_approved', 'manager_denied', 'cancelled', 'expired') DEFAULT 'pending',
    `responded_by` INT UNSIGNED NULL,
    `responded_at` DATETIME NULL,

    -- Manager Approval
    `requires_manager_approval` BOOLEAN DEFAULT TRUE,
    `manager_approved_by` INT UNSIGNED NULL,
    `manager_approved_at` DATETIME NULL,
    `manager_notes` TEXT NULL,

    -- Expiration
    `expires_at` DATETIME NULL,

    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requesting_employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shift_to_swap_id`) REFERENCES `shifts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`target_employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL,
    INDEX idx_status (`status`),
    INDEX idx_requesting_employee (`requesting_employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Labor Cost Budgets
CREATE TABLE IF NOT EXISTS `labor_budgets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,

    -- Budget Period
    `budget_period` VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
    `budget_type` ENUM('monthly', 'weekly', 'daily') DEFAULT 'monthly',

    -- Budget Amounts
    `budgeted_hours` DECIMAL(10, 2) NOT NULL,
    `budgeted_amount` DECIMAL(12, 2) NOT NULL,
    `max_overtime_hours` DECIMAL(8, 2) DEFAULT 0.00,

    -- Department Breakdown
    `department_budgets` JSON NULL COMMENT 'Budget by department',

    -- Actual vs Budget
    `actual_hours` DECIMAL(10, 2) DEFAULT 0.00,
    `actual_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `variance_hours` DECIMAL(10, 2) DEFAULT 0.00,
    `variance_amount` DECIMAL(12, 2) DEFAULT 0.00,
    `variance_percentage` DECIMAL(6, 2) DEFAULT 0.00,

    -- Alerts
    `alert_threshold_percentage` DECIMAL(5, 2) DEFAULT 90.00,
    `alert_triggered` BOOLEAN DEFAULT FALSE,
    `alert_triggered_at` DATETIME NULL,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_period (`tenant_id`, `budget_period`, `budget_type`),
    INDEX idx_budget_period (`budget_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance Records
CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `employee_id` INT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NULL,

    -- Date
    `attendance_date` DATE NOT NULL,

    -- Status
    `status` ENUM('present', 'absent', 'late', 'left_early', 'no_show', 'excused') NOT NULL,

    -- Times
    `scheduled_start` TIME NULL,
    `actual_start` TIME NULL,
    `scheduled_end` TIME NULL,
    `actual_end` TIME NULL,
    `minutes_late` INT DEFAULT 0,
    `minutes_early_departure` INT DEFAULT 0,

    -- Reason
    `absence_reason` VARCHAR(255) NULL,
    `excused` BOOLEAN DEFAULT FALSE,
    `excuse_notes` TEXT NULL,

    -- Disciplinary
    `requires_action` BOOLEAN DEFAULT FALSE,
    `action_taken` VARCHAR(255) NULL,
    `action_taken_by` INT UNSIGNED NULL,
    `action_taken_at` DATETIME NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shift_id`) REFERENCES `shifts`(`id`) ON DELETE SET NULL,
    INDEX idx_employee_date (`employee_id`, `attendance_date`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pre-seeded Sample Data
-- =====================================================

-- Sample Employees
INSERT INTO `employees` (
    `tenant_id`, `employee_number`, `first_name`, `last_name`, `email`, `phone`,
    `hire_date`, `employment_type`, `job_title`, `department`, `pay_rate`, `pay_type`,
    `max_hours_per_week`, `can_open_store`, `can_close_store`, `can_handle_cash`
) VALUES
(1, 'EMP001', 'Sarah', 'Johnson', 'sarah.j@diveshop.com', '555-0101', '2022-03-15', 'full_time', 'Store Manager', 'management', 25.00, 'hourly', 40, TRUE, TRUE, TRUE),
(1, 'EMP002', 'Mike', 'Chen', 'mike.c@diveshop.com', '555-0102', '2022-06-01', 'full_time', 'PADI Instructor', 'instruction', 30.00, 'hourly', 40, TRUE, TRUE, FALSE),
(1, 'EMP003', 'Jessica', 'Martinez', 'jessica.m@diveshop.com', '555-0103', '2023-01-10', 'part_time', 'Sales Associate', 'sales', 18.00, 'hourly', 25, FALSE, TRUE, TRUE),
(1, 'EMP004', 'Tom', 'Wilson', 'tom.w@diveshop.com', '555-0104', '2023-04-20', 'part_time', 'Divemaster', 'instruction', 20.00, 'hourly', 30, FALSE, FALSE, FALSE),
(1, 'EMP005', 'Emily', 'Davis', 'emily.d@diveshop.com', '555-0105', '2023-08-01', 'full_time', 'Retail Manager', 'retail', 22.00, 'hourly', 40, TRUE, TRUE, TRUE);

-- Sample Schedule
INSERT INTO `work_schedules` (
    `tenant_id`, `schedule_name`, `schedule_type`, `start_date`, `end_date`, `status`
) VALUES
(1, 'Week of March 15-21, 2024', 'weekly', '2024-03-15', '2024-03-21', 'published');

-- Sample Shifts for the week
INSERT INTO `shifts` (
    `tenant_id`, `schedule_id`, `employee_id`, `shift_date`, `start_time`, `end_time`,
    `position`, `location`, `status`, `total_hours`
) VALUES
-- Monday
(1, 1, 1, '2024-03-15', '09:00:00', '17:00:00', 'Store Manager', 'Main Store', 'scheduled', 8.00),
(1, 1, 3, '2024-03-15', '10:00:00', '16:00:00', 'Sales Associate', 'Main Store', 'scheduled', 6.00),
(1, 1, 2, '2024-03-15', '13:00:00', '20:00:00', 'Instructor', 'Pool', 'scheduled', 7.00),

-- Tuesday
(1, 1, 1, '2024-03-16', '09:00:00', '17:00:00', 'Store Manager', 'Main Store', 'scheduled', 8.00),
(1, 1, 4, '2024-03-16', '10:00:00', '18:00:00', 'Divemaster', 'Boat', 'scheduled', 8.00),
(1, 1, 5, '2024-03-16', '11:00:00', '19:00:00', 'Retail Manager', 'Main Store', 'scheduled', 8.00);
