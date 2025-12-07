-- =====================================================
-- Training Tracking System
-- Comprehensive training, certification, and skills tracking
-- =====================================================

-- Training Programs
CREATE TABLE IF NOT EXISTS `training_programs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `program_code` VARCHAR(50) NULL,
    `program_type` ENUM('certification', 'continuing_education', 'safety', 'skills', 'leadership', 'specialty') NOT NULL,

    -- Certification Body
    `certification_agency` ENUM('PADI', 'SSI', 'NAUI', 'SDI', 'TDI', 'RAID', 'CMAS', 'internal', 'other') NULL,
    `agency_course_code` VARCHAR(50) NULL,

    -- Program Details
    `description` TEXT NULL,
    `prerequisites` JSON NULL COMMENT 'Required certifications or skills',
    `minimum_age` INT NULL,
    `minimum_certification_level` VARCHAR(100) NULL,

    -- Duration
    `classroom_hours` DECIMAL(5, 2) NULL,
    `pool_sessions` INT NULL,
    `open_water_dives` INT NULL,
    `total_duration_days` INT NULL,

    -- Skills & Competencies
    `skills_taught` JSON NULL,
    `knowledge_topics` JSON NULL,
    `performance_requirements` JSON NULL,

    -- Materials
    `required_materials` JSON NULL COMMENT 'Books, eLearning, etc.',
    `equipment_required` JSON NULL,

    -- Pricing
    `course_fee` DECIMAL(10, 2) NULL,
    `materials_fee` DECIMAL(10, 2) NULL,
    `certification_fee` DECIMAL(10, 2) NULL,

    -- Validity
    `certification_valid_years` INT NULL COMMENT 'Years before recertification needed',
    `requires_renewal` BOOLEAN DEFAULT FALSE,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_type (`tenant_id`, `program_type`),
    INDEX idx_agency (`certification_agency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Sessions (scheduled classes)
CREATE TABLE IF NOT EXISTS `training_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `program_id` INT UNSIGNED NOT NULL,
    `session_name` VARCHAR(255) NULL,
    `session_code` VARCHAR(50) NULL COMMENT 'e.g., OW-2024-03',

    -- Scheduling
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `start_time` TIME NULL,
    `end_time` TIME NULL,

    -- Location
    `classroom_location` VARCHAR(255) NULL,
    `pool_location` VARCHAR(255) NULL,
    `open_water_location` VARCHAR(255) NULL,

    -- Schedule Details
    `schedule` JSON NULL COMMENT 'Day-by-day schedule',

    -- Instructor
    `lead_instructor_id` INT UNSIGNED NULL,
    `assistant_instructors` JSON NULL COMMENT 'Array of instructor IDs',

    -- Capacity
    `max_students` INT UNSIGNED DEFAULT 8,
    `min_students` INT UNSIGNED DEFAULT 4,
    `enrolled_count` INT UNSIGNED DEFAULT 0,
    `waitlist_count` INT UNSIGNED DEFAULT 0,

    -- Status
    `status` ENUM('scheduled', 'open_enrollment', 'full', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    `registration_deadline` DATE NULL,

    -- Completion
    `actual_start_date` DATE NULL,
    `actual_end_date` DATE NULL,
    `completion_rate` DECIMAL(5, 2) NULL COMMENT 'Percentage who completed',

    -- Notes
    `special_notes` TEXT NULL,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `training_programs`(`id`) ON DELETE RESTRICT,
    INDEX idx_tenant_dates (`tenant_id`, `start_date`, `end_date`),
    INDEX idx_status (`status`),
    INDEX idx_instructor (`lead_instructor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Enrollments
CREATE TABLE IF NOT EXISTS `training_enrollments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `session_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `program_id` INT UNSIGNED NOT NULL,

    -- Enrollment Details
    `enrollment_date` DATE NOT NULL,
    `enrollment_status` ENUM('enrolled', 'waitlist', 'in_progress', 'completed', 'failed', 'withdrawn', 'no_show') DEFAULT 'enrolled',

    -- Prerequisites Check
    `prerequisites_verified` BOOLEAN DEFAULT FALSE,
    `verified_by` INT UNSIGNED NULL,
    `verified_at` DATETIME NULL,

    -- Medical & Liability
    `medical_form_completed` BOOLEAN DEFAULT FALSE,
    `liability_waiver_signed` BOOLEAN DEFAULT FALSE,
    `medical_clearance_required` BOOLEAN DEFAULT FALSE,
    `medical_clearance_received` BOOLEAN DEFAULT FALSE,

    -- Materials
    `materials_issued` BOOLEAN DEFAULT FALSE,
    `materials_issued_date` DATE NULL,
    `elearning_access_granted` BOOLEAN DEFAULT FALSE,
    `elearning_completion_percentage` DECIMAL(5, 2) DEFAULT 0.00,

    -- Attendance
    `classroom_attendance` JSON NULL COMMENT 'Attendance per session',
    `pool_attendance` JSON NULL,
    `open_water_attendance` JSON NULL,
    `total_attendance_percentage` DECIMAL(5, 2) NULL,

    -- Performance
    `knowledge_review_scores` JSON NULL,
    `quiz_scores` JSON NULL,
    `final_exam_score` DECIMAL(5, 2) NULL,
    `skills_assessment_scores` JSON NULL,
    `overall_performance_score` DECIMAL(5, 2) NULL,

    -- Completion
    `completed_at` DATETIME NULL,
    `certification_issued` BOOLEAN DEFAULT FALSE,
    `certification_number` VARCHAR(100) NULL,
    `certification_card_printed` BOOLEAN DEFAULT FALSE,
    `certification_submitted_to_agency` BOOLEAN DEFAULT FALSE,
    `agency_submission_date` DATE NULL,

    -- Failure/Withdrawal
    `withdrawal_date` DATE NULL,
    `withdrawal_reason` TEXT NULL,
    `failure_reason` TEXT NULL,
    `remedial_training_required` BOOLEAN DEFAULT FALSE,

    -- Payment
    `payment_status` ENUM('pending', 'deposit_paid', 'paid_in_full', 'refunded') DEFAULT 'pending',
    `total_paid` DECIMAL(10, 2) DEFAULT 0.00,

    -- Notes
    `instructor_notes` TEXT NULL,
    `student_notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`session_id`) REFERENCES `training_sessions`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`student_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`program_id`) REFERENCES `training_programs`(`id`) ON DELETE RESTRICT,
    UNIQUE KEY unique_session_student (`session_id`, `student_id`),
    INDEX idx_student (`student_id`),
    INDEX idx_status (`enrollment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skills Assessment Records
CREATE TABLE IF NOT EXISTS `skills_assessments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `enrollment_id` BIGINT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `assessor_id` INT UNSIGNED NOT NULL COMMENT 'Instructor ID',
    `program_id` INT UNSIGNED NOT NULL,

    -- Assessment Details
    `assessment_date` DATE NOT NULL,
    `assessment_type` ENUM('confined_water', 'open_water', 'classroom', 'skills_circuit', 'final_checkout') NOT NULL,
    `dive_number` INT NULL,

    -- Environment
    `location` VARCHAR(255) NULL,
    `water_temp_f` INT NULL,
    `visibility_ft` INT NULL,
    `conditions` VARCHAR(255) NULL,

    -- Skills Evaluated
    `skills_evaluated` JSON NOT NULL COMMENT 'Array of skill objects with pass/fail',
    `skills_passed` INT UNSIGNED DEFAULT 0,
    `skills_failed` INT UNSIGNED DEFAULT 0,
    `skills_total` INT UNSIGNED NOT NULL,

    -- Performance
    `overall_rating` ENUM('excellent', 'good', 'satisfactory', 'needs_improvement', 'fail') NULL,
    `confidence_level` ENUM('high', 'medium', 'low') NULL,
    `comfort_in_water` ENUM('very_comfortable', 'comfortable', 'nervous', 'very_nervous') NULL,

    -- Pass/Fail
    `assessment_result` ENUM('pass', 'fail', 'incomplete', 'pending') DEFAULT 'pending',
    `requires_remediation` BOOLEAN DEFAULT FALSE,
    `remediation_skills` JSON NULL,

    -- Comments
    `assessor_comments` TEXT NULL,
    `strengths` TEXT NULL,
    `areas_for_improvement` TEXT NULL,

    -- Sign-off
    `assessor_signature_data` TEXT NULL,
    `student_signature_data` TEXT NULL,
    `signed_at` DATETIME NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`enrollment_id`) REFERENCES `training_enrollments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `training_programs`(`id`) ON DELETE CASCADE,
    INDEX idx_enrollment (`enrollment_id`),
    INDEX idx_student (`student_id`),
    INDEX idx_assessment_date (`assessment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Instructor Certifications & Qualifications
CREATE TABLE IF NOT EXISTS `instructor_qualifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `instructor_id` INT UNSIGNED NOT NULL COMMENT 'Staff/User ID',

    -- Certification Details
    `certification_type` VARCHAR(100) NOT NULL COMMENT 'e.g., PADI OWSI, SSI Instructor, etc.',
    `certification_agency` VARCHAR(100) NOT NULL,
    `certification_number` VARCHAR(100) NOT NULL,
    `certification_level` ENUM('divemaster', 'assistant_instructor', 'instructor', 'master_instructor', 'course_director') NOT NULL,

    -- Dates
    `certification_date` DATE NOT NULL,
    `expiration_date` DATE NULL,
    `last_renewal_date` DATE NULL,

    -- Teaching Ratings
    `rating_level` VARCHAR(50) NULL COMMENT 'e.g., Open Water, Advanced, etc.',
    `specialties_rated` JSON NULL COMMENT 'Specialty courses authorized to teach',

    -- Insurance
    `liability_insurance_provider` VARCHAR(255) NULL,
    `insurance_policy_number` VARCHAR(100) NULL,
    `insurance_expiration_date` DATE NULL,

    -- Status
    `is_current` BOOLEAN DEFAULT TRUE,
    `status` ENUM('active', 'inactive', 'suspended', 'expired', 'revoked') DEFAULT 'active',
    `status_notes` TEXT NULL,

    -- Teaching Stats
    `total_students_certified` INT UNSIGNED DEFAULT 0,
    `total_courses_taught` INT UNSIGNED DEFAULT 0,
    `average_student_rating` DECIMAL(3, 2) DEFAULT 0.00,

    -- Documents
    `certification_card_url` VARCHAR(500) NULL,
    `insurance_document_url` VARCHAR(500) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_instructor (`instructor_id`),
    INDEX idx_expiration (`expiration_date`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Certifications Earned
CREATE TABLE IF NOT EXISTS `student_certifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `enrollment_id` BIGINT UNSIGNED NULL,
    `program_id` INT UNSIGNED NULL,

    -- Certification Details
    `certification_name` VARCHAR(255) NOT NULL,
    `certification_agency` VARCHAR(100) NOT NULL,
    `certification_number` VARCHAR(100) NOT NULL UNIQUE,
    `certification_level` VARCHAR(100) NULL,

    -- Dates
    `certification_date` DATE NOT NULL,
    `expiration_date` DATE NULL,
    `last_renewed_date` DATE NULL,

    -- Issuing Instructor
    `instructor_id` INT UNSIGNED NULL,
    `instructor_name` VARCHAR(255) NULL,
    `instructor_number` VARCHAR(100) NULL,

    -- Card Details
    `card_printed` BOOLEAN DEFAULT FALSE,
    `card_issued_date` DATE NULL,
    `ecard_issued` BOOLEAN DEFAULT FALSE,
    `ecard_url` VARCHAR(500) NULL,

    -- Agency Submission
    `submitted_to_agency` BOOLEAN DEFAULT FALSE,
    `submission_date` DATE NULL,
    `agency_confirmation_number` VARCHAR(100) NULL,

    -- Status
    `is_current` BOOLEAN DEFAULT TRUE,
    `status` ENUM('active', 'expired', 'suspended', 'revoked') DEFAULT 'active',

    -- Documents
    `certificate_pdf_url` VARCHAR(500) NULL,
    `card_image_url` VARCHAR(500) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`enrollment_id`) REFERENCES `training_enrollments`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`program_id`) REFERENCES `training_programs`(`id`) ON DELETE SET NULL,
    INDEX idx_student (`student_id`),
    INDEX idx_cert_number (`certification_number`),
    INDEX idx_expiration (`expiration_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Training Materials & eLearning
CREATE TABLE IF NOT EXISTS `training_materials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `program_id` INT UNSIGNED NULL,

    -- Material Details
    `material_name` VARCHAR(255) NOT NULL,
    `material_type` ENUM('textbook', 'workbook', 'video', 'elearning', 'equipment', 'manual', 'slate', 'logbook', 'other') NOT NULL,
    `sku` VARCHAR(100) NULL,

    -- Content
    `description` TEXT NULL,
    `manufacturer` VARCHAR(255) NULL,
    `version` VARCHAR(50) NULL,

    -- Pricing
    `cost` DECIMAL(10, 2) NULL,
    `retail_price` DECIMAL(10, 2) NULL,
    `rental_price` DECIMAL(10, 2) NULL,

    -- eLearning
    `elearning_provider` VARCHAR(100) NULL COMMENT 'PADI eLearning, SSI Digital, etc.',
    `access_code_required` BOOLEAN DEFAULT FALSE,
    `online_access_url` VARCHAR(500) NULL,

    -- Inventory
    `stock_quantity` INT DEFAULT 0,
    `reorder_level` INT DEFAULT 5,

    -- Status
    `is_required` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `training_programs`(`id`) ON DELETE SET NULL,
    INDEX idx_program (`program_id`),
    INDEX idx_type (`material_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pre-seeded Training Programs
-- =====================================================

INSERT INTO `training_programs` (
    `tenant_id`, `program_name`, `program_code`, `program_type`, `certification_agency`,
    `agency_course_code`, `minimum_age`, `classroom_hours`, `pool_sessions`, `open_water_dives`,
    `total_duration_days`, `course_fee`, `certification_valid_years`
) VALUES
-- PADI Courses
(1, 'Open Water Diver', 'OWD', 'certification', 'PADI', '60105', 10, 8.0, 5, 4, 4, 399.00, NULL),
(1, 'Advanced Open Water Diver', 'AOW', 'certification', 'PADI', '60107', 12, 4.0, 0, 5, 2, 299.00, NULL),
(1, 'Rescue Diver', 'RD', 'certification', 'PADI', '60109', 12, 8.0, 3, 2, 3, 349.00, NULL),
(1, 'Divemaster', 'DM', 'leadership', 'PADI', '60137', 18, 40.0, 10, 20, 30, 899.00, 2),
(1, 'Enriched Air (Nitrox)', 'EAN', 'specialty', 'PADI', '60130', 12, 4.0, 0, 2, 1, 199.00, NULL),
(1, 'Deep Diver', 'DEEP', 'specialty', 'PADI', '60125', 15, 2.0, 0, 4, 2, 249.00, NULL),
(1, 'Wreck Diver', 'WRECK', 'specialty', 'PADI', '60127', 15, 2.0, 0, 4, 2, 249.00, NULL),
(1, 'Night Diver', 'NIGHT', 'specialty', 'PADI', '60126', 12, 2.0, 0, 3, 1, 199.00, NULL),
(1, 'Underwater Navigator', 'NAV', 'specialty', 'PADI', '60132', 10, 2.0, 0, 3, 1, 199.00, NULL),
(1, 'Underwater Photographer', 'PHOTO', 'specialty', 'PADI', '60133', 10, 2.0, 0, 2, 1, 229.00, NULL),

-- Safety & Professional
(1, 'Emergency First Response', 'EFR', 'safety', 'PADI', '60201', 10, 8.0, 0, 0, 1, 175.00, 2),
(1, 'Emergency Oxygen Provider', 'O2', 'safety', 'PADI', '60203', 12, 4.0, 0, 0, 1, 125.00, 2);

-- Sample Training Sessions
INSERT INTO `training_sessions` (
    `tenant_id`, `program_id`, `session_name`, `session_code`, `start_date`, `end_date`,
    `max_students`, `min_students`, `status`, `registration_deadline`
) VALUES
(1, 1, 'Open Water - March Weekend', 'OW-2024-03-WE', '2024-03-15', '2024-03-17', 8, 4, 'open_enrollment', '2024-03-08'),
(1, 2, 'Advanced Open Water - April', 'AOW-2024-04', '2024-04-05', '2024-04-06', 6, 3, 'scheduled', '2024-03-29'),
(1, 3, 'Rescue Diver - May', 'RD-2024-05', '2024-05-10', '2024-05-12', 6, 3, 'scheduled', '2024-05-03');
