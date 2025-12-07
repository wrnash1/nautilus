-- ================================================
-- Nautilus V6 - PADI Compliance: Student Records
-- Migration: 050_padi_compliance_student_records.sql
-- Description: Course student records for PADI standards compliance
-- ================================================

-- Course student records (based on 10056 Open Water Diver Course Record)
CREATE TABLE IF NOT EXISTS `course_student_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `enrollment_id` INT UNSIGNED NOT NULL,
    `form_type` VARCHAR(100) DEFAULT 'course_record' COMMENT 'course_record, referral, completion',

    -- Knowledge Development (eLearning or classroom)
    `knowledge_review_scores` JSON COMMENT 'Quiz scores per module',
    `final_exam_score` DECIMAL(5,2),
    `knowledge_completion_date` DATE,
    `knowledge_status` ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',

    -- Confined Water (Pool Sessions)
    `confined_water_sessions` JSON COMMENT 'Skills completed per session',
    `confined_water_completion_date` DATE,
    `confined_water_status` ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',

    -- Open Water (Dive Sessions)
    `open_water_dives` JSON COMMENT 'Skills completed per dive',
    `open_water_completion_date` DATE,
    `open_water_status` ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',

    -- Overall Progress
    `overall_status` ENUM('enrolled', 'in_training', 'completed', 'referred', 'withdrawn', 'failed') DEFAULT 'enrolled',
    `completion_date` DATE,
    `certification_number` VARCHAR(50) COMMENT 'PADI certification number if issued',
    `certification_issued_date` DATE,

    -- Referral Information (for students who need to complete elsewhere)
    `is_referral` BOOLEAN DEFAULT FALSE,
    `referral_shop_name` VARCHAR(255),
    `referral_shop_location` VARCHAR(255),
    `referral_shop_number` VARCHAR(50) COMMENT 'PADI store number',
    `referral_instructor_name` VARCHAR(255),
    `referral_instructor_number` VARCHAR(50) COMMENT 'PADI instructor number',
    `referred_date` DATE,
    `referral_notes` TEXT,
    `referral_portions` JSON COMMENT 'What portions need completion: knowledge, confined, open_water',

    -- Incoming Referral (student starting with us from another shop)
    `is_incoming_referral` BOOLEAN DEFAULT FALSE,
    `referring_shop_name` VARCHAR(255),
    `referring_shop_location` VARCHAR(255),
    `referring_instructor_name` VARCHAR(255),
    `received_referral_date` DATE,

    -- Instructor Assignment
    `instructor_id` INT UNSIGNED COMMENT 'Primary instructor',
    `assistant_instructor_id` INT UNSIGNED COMMENT 'Assistant if applicable',

    -- Performance Notes
    `instructor_notes` TEXT,
    `student_strengths` TEXT,
    `student_areas_for_improvement` TEXT,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assistant_instructor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_enrollment` (`enrollment_id`),
    INDEX `idx_status` (`overall_status`),
    INDEX `idx_instructor` (`instructor_id`),
    INDEX `idx_referral` (`is_referral`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Skills assessment for detailed tracking (based on 10081 Water Skills Checkoff)
CREATE TABLE IF NOT EXISTS `student_skills_assessment` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `record_id` INT UNSIGNED NOT NULL,
    `session_type` ENUM('confined_water', 'open_water') NOT NULL,
    `session_number` INT NOT NULL COMMENT '1-5 for confined, 1-4 for open water',
    `session_date` DATE,
    `session_location` VARCHAR(255),

    -- Skill Details
    `skill_name` VARCHAR(255) NOT NULL COMMENT 'e.g., Mask removal and replacement',
    `skill_code` VARCHAR(50) COMMENT 'e.g., CW1, CW2, OW1, OW2',
    `skill_category` VARCHAR(100) COMMENT 'e.g., Equipment, Buoyancy, Emergency',

    -- Performance Assessment
    `performance` ENUM('not_performed', 'needs_improvement', 'adequate', 'proficient') DEFAULT 'not_performed',
    `pass` BOOLEAN DEFAULT FALSE,
    `attempts` INT DEFAULT 1,

    -- Assessment Details
    `assessed_by` INT UNSIGNED COMMENT 'Instructor who assessed',
    `assessment_notes` TEXT,
    `remediation_needed` BOOLEAN DEFAULT FALSE,
    `remediation_notes` TEXT,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`record_id`) REFERENCES `course_student_records`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assessed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_record_session` (`record_id`, `session_type`, `session_number`),
    INDEX `idx_session_date` (`session_date`),
    INDEX `idx_pass` (`pass`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-defined PADI skills for Open Water Diver course
CREATE TABLE IF NOT EXISTS `padi_standard_skills` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `course_type` VARCHAR(100) NOT NULL COMMENT 'open_water, advanced, rescue, etc.',
    `session_type` ENUM('confined_water', 'open_water') NOT NULL,
    `session_number` INT NOT NULL,
    `skill_code` VARCHAR(50) NOT NULL,
    `skill_name` VARCHAR(255) NOT NULL,
    `skill_description` TEXT,
    `skill_category` VARCHAR(100),
    `is_required` BOOLEAN DEFAULT TRUE,
    `display_order` INT DEFAULT 0,

    -- Performance Standards
    `performance_requirements` TEXT COMMENT 'What constitutes mastery',
    `common_problems` TEXT COMMENT 'Common student issues',
    `teaching_tips` TEXT,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY `unique_skill` (`course_type`, `session_type`, `session_number`, `skill_code`),
    INDEX `idx_course_session` (`course_type`, `session_type`, `session_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed standard Open Water Diver confined water skills
INSERT INTO `padi_standard_skills` (`course_type`, `session_type`, `session_number`, `skill_code`, `skill_name`, `skill_category`, `display_order`) VALUES
-- Confined Water 1
('open_water', 'confined_water', 1, 'CW1-1', 'Equipment assembly and disassembly', 'Equipment', 1),
('open_water', 'confined_water', 1, 'CW1-2', 'Pre-dive safety check (BWRAF)', 'Safety', 2),
('open_water', 'confined_water', 1, 'CW1-3', 'Deep water entry and exit', 'Water Entry/Exit', 3),
('open_water', 'confined_water', 1, 'CW1-4', 'Regulator breathing', 'Breathing', 4),
('open_water', 'confined_water', 1, 'CW1-5', 'Regulator recovery and clearing', 'Breathing', 5),
('open_water', 'confined_water', 1, 'CW1-6', 'Clear partially flooded mask', 'Mask Skills', 6),
('open_water', 'confined_water', 1, 'CW1-7', 'Underwater swimming', 'Buoyancy', 7),

-- Confined Water 2
('open_water', 'confined_water', 2, 'CW2-1', 'Proper weighting check', 'Buoyancy', 1),
('open_water', 'confined_water', 2, 'CW2-2', 'Inflate/deflate BCD at surface', 'Buoyancy', 2),
('open_water', 'confined_water', 2, 'CW2-3', 'Five-point ascent', 'Ascents/Descents', 3),
('open_water', 'confined_water', 2, 'CW2-4', 'Remove and replace mask', 'Mask Skills', 4),
('open_water', 'confined_water', 2, 'CW2-5', 'Alternate air source use (stationary)', 'Emergency', 5),
('open_water', 'confined_water', 2, 'CW2-6', 'Hover for 30 seconds', 'Buoyancy', 6),

-- Confined Water 3
('open_water', 'confined_water', 3, 'CW3-1', 'Controlled descent', 'Ascents/Descents', 1),
('open_water', 'confined_water', 3, 'CW3-2', 'Underwater swimming and communication', 'Communication', 2),
('open_water', 'confined_water', 3, 'CW3-3', 'Mask removal, replace and clear', 'Mask Skills', 3),
('open_water', 'confined_water', 3, 'CW3-4', 'Alternate air source use (swimming)', 'Emergency', 4),
('open_water', 'confined_water', 3, 'CW3-5', 'Free-flow regulator breathing', 'Emergency', 5),
('open_water', 'confined_water', 3, 'CW3-6', 'Neutral buoyancy', 'Buoyancy', 6),

-- Confined Water 4
('open_water', 'confined_water', 4, 'CW4-1', 'Remove and replace weights', 'Equipment', 1),
('open_water', 'confined_water', 4, 'CW4-2', 'Remove and replace scuba unit underwater', 'Equipment', 2),
('open_water', 'confined_water', 4, 'CW4-3', 'Controlled Emergency Swimming Ascent (CESA)', 'Emergency', 3),
('open_water', 'confined_water', 4, 'CW4-4', 'Cramp removal', 'Problem Management', 4),
('open_water', 'confined_water', 4, 'CW4-5', 'Tired diver tow', 'Rescue', 5),

-- Confined Water 5
('open_water', 'confined_water', 5, 'CW5-1', 'Fin pivot neutral buoyancy', 'Buoyancy', 1),
('open_water', 'confined_water', 5, 'CW5-2', 'Hovering', 'Buoyancy', 2),
('open_water', 'confined_water', 5, 'CW5-3', 'Underwater tour with compass', 'Navigation', 3),
('open_water', 'confined_water', 5, 'CW5-4', 'Loose cylinder band', 'Problem Management', 4),
('open_water', 'confined_water', 5, 'CW5-5', 'Weight system removal and replacement at surface', 'Equipment', 5);

-- Seed standard Open Water Diver open water skills
INSERT INTO `padi_standard_skills` (`course_type`, `session_type`, `session_number`, `skill_code`, `skill_name`, `skill_category`, `display_order`) VALUES
-- Open Water Dive 1
('open_water', 'open_water', 1, 'OW1-1', 'Equipment assembly and disassembly', 'Equipment', 1),
('open_water', 'open_water', 1, 'OW1-2', 'Pre-dive safety check', 'Safety', 2),
('open_water', 'open_water', 1, 'OW1-3', 'Entry and exit', 'Water Entry/Exit', 3),
('open_water', 'open_water', 1, 'OW1-4', 'Buoyancy control', 'Buoyancy', 4),
('open_water', 'open_water', 1, 'OW1-5', 'Regulator recovery and clear', 'Breathing', 5),
('open_water', 'open_water', 1, 'OW1-6', 'Mask clear', 'Mask Skills', 6),
('open_water', 'open_water', 1, 'OW1-7', 'Alternate air source use', 'Emergency', 7),

-- Open Water Dive 2
('open_water', 'open_water', 2, 'OW2-1', 'Five-point descent', 'Ascents/Descents', 1),
('open_water', 'open_water', 2, 'OW2-2', 'Underwater exploration', 'Navigation', 2),
('open_water', 'open_water', 2, 'OW2-3', 'Mask removal and replace', 'Mask Skills', 3),
('open_water', 'open_water', 2, 'OW2-4', 'Alternate air source use (swimming)', 'Emergency', 4),
('open_water', 'open_water', 2, 'OW2-5', 'Controlled ascent', 'Ascents/Descents', 5),
('open_water', 'open_water', 2, 'OW2-6', 'Safety stop', 'Safety', 6),

-- Open Water Dive 3
('open_water', 'open_water', 3, 'OW3-1', 'Weight check', 'Buoyancy', 1),
('open_water', 'open_water', 3, 'OW3-2', 'Neutral buoyancy', 'Buoyancy', 2),
('open_water', 'open_water', 3, 'OW3-3', 'Hovering', 'Buoyancy', 3),
('open_water', 'open_water', 3, 'OW3-4', 'Free-flow regulator breathing', 'Emergency', 4),
('open_water', 'open_water', 3, 'OW3-5', 'Disconnect/reconnect low-pressure inflator', 'Equipment', 5),
('open_water', 'open_water', 3, 'OW3-6', 'Controlled Emergency Swimming Ascent', 'Emergency', 6),

-- Open Water Dive 4
('open_water', 'open_water', 4, 'OW4-1', 'Underwater navigation', 'Navigation', 1),
('open_water', 'open_water', 4, 'OW4-2', 'Compass navigation', 'Navigation', 2),
('open_water', 'open_water', 4, 'OW4-3', 'Surface compass navigation', 'Navigation', 3),
('open_water', 'open_water', 4, 'OW4-4', 'Remove and replace scuba unit underwater', 'Equipment', 4),
('open_water', 'open_water', 4, 'OW4-5', 'Remove and replace weight system underwater', 'Equipment', 5),
('open_water', 'open_water', 4, 'OW4-6', 'Complete dive', 'General', 6);
