SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `medical_clearance_history`;
DROP TABLE IF EXISTS `medical_form_questions`;
DROP TABLE IF EXISTS `customer_medical_forms`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `medical_clearance_history`;
DROP TABLE IF EXISTS `medical_form_questions`;
DROP TABLE IF EXISTS `customer_medical_forms`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `medical_clearance_history`;
DROP TABLE IF EXISTS `medical_form_questions`;
DROP TABLE IF EXISTS `customer_medical_forms`;

-- ================================================
-- Nautilus V6 - PADI Compliance: Medical Forms
-- Migration: 051_padi_compliance_medical_forms.sql
-- Description: Medical form management (based on 10346 Diver Medical Form)
-- ================================================

-- Customer medical forms (PADI Medical Form 10346)
CREATE TABLE IF NOT EXISTS `customer_medical_forms` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `form_type` VARCHAR(100) DEFAULT 'padi_medical',
    `form_version` VARCHAR(50) COMMENT 'e.g., 10346 Rev. 11/20',

    -- Medical Questions (Part 1 - all YES/NO questions)
    -- If ALL NO = cleared to dive, if ANY YES = physician clearance required
    `medical_questions` JSON COMMENT 'Array of question-answer pairs',
    `any_yes_answers` BOOLEAN DEFAULT FALSE,
    `physician_clearance_required` BOOLEAN DEFAULT FALSE,

    -- Specific Medical Conditions Tracking (for reporting/safety)
    `has_heart_condition` BOOLEAN DEFAULT FALSE,
    `has_respiratory_condition` BOOLEAN DEFAULT FALSE,
    `has_ear_condition` BOOLEAN DEFAULT FALSE,
    `has_diabetes` BOOLEAN DEFAULT FALSE,
    `takes_medication` BOOLEAN DEFAULT FALSE,
    `medication_details` TEXT,

    -- Physician Clearance (Part 2 - if needed)
    `physician_name` VARCHAR(255),
    `physician_specialty` VARCHAR(255),
    `physician_phone` VARCHAR(50),
    `physician_address` TEXT,
    `physician_signature_path` VARCHAR(255) COMMENT 'Path to digital signature',
    `physician_signature_date` DATE,
    `cleared_to_dive` BOOLEAN DEFAULT FALSE,
    `clearance_restrictions` TEXT COMMENT 'Any restrictions on diving',

    -- Participant Signature (Part 3)
    `participant_signature_path` VARCHAR(255),
    `participant_signature_date` DATE,
    `participant_name` VARCHAR(255) COMMENT 'Printed name',

    -- Parent/Guardian (if minor)
    `is_minor` BOOLEAN DEFAULT FALSE,
    `parent_guardian_name` VARCHAR(255),
    `parent_signature_path` VARCHAR(255),
    `parent_signature_date` DATE,

    -- Validity Period
    `form_date` DATE NOT NULL,
    `expiry_date` DATE COMMENT 'Typically 1 year from form_date',
    `status` ENUM('pending', 'cleared', 'needs_physician', 'expired', 'rejected') DEFAULT 'pending',

    -- Document Management
    `uploaded_pdf_path` VARCHAR(255) COMMENT 'Original signed PDF',
    `notes` TEXT,
    `reviewed_by` BIGINT UNSIGNED COMMENT 'Staff who reviewed',
    `reviewed_at` TIMESTAMP NULL,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_customer_status` (`customer_id`, `status`),
    INDEX `idx_expiry` (`expiry_date`),
    INDEX `idx_clearance_required` (`physician_clearance_required`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medical form questions template (standard PADI questions)
CREATE TABLE IF NOT EXISTS `medical_form_questions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `form_type` VARCHAR(100) DEFAULT 'padi_medical',
    `question_number` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_category` VARCHAR(100) COMMENT 'heart, respiratory, neurological, etc.',
    `is_active` BOOLEAN DEFAULT TRUE,
    `display_order` INT DEFAULT 0,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_form_type` (`form_type`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed PADI standard medical questions
INSERT INTO `medical_form_questions` (`form_type`, `question_number`, `question_text`, `question_category`, `display_order`) VALUES
('padi_medical', 1, 'Could you be pregnant, or are you attempting to become pregnant?', 'reproductive', 1),
('padi_medical', 2, 'Are you presently taking prescription medications? (with exception of birth control or anti-malarial)', 'medication', 2),
('padi_medical', 3, 'Are you over 45 years of age?', 'age', 3),
('padi_medical', 4, 'Have you ever had or do you currently have: Asthma, or wheezing with breathing, or wheezing with exercise?', 'respiratory', 4),
('padi_medical', 5, 'Have you ever had or do you currently have: Frequent or severe attacks of hayfever or allergy?', 'respiratory', 5),
('padi_medical', 6, 'Have you ever had or do you currently have: Frequent colds, sinusitis or bronchitis?', 'respiratory', 6),
('padi_medical', 7, 'Have you ever had or do you currently have: Any form of lung disease?', 'respiratory', 7),
('padi_medical', 8, 'Have you ever had or do you currently have: Pneumothorax (collapsed lung)?', 'respiratory', 8),
('padi_medical', 9, 'Have you ever had or do you currently have: Other chest disease or chest surgery?', 'respiratory', 9),
('padi_medical', 10, 'Have you ever had or do you currently have: Behavioral health, mental or psychological problems?', 'neurological', 10),
('padi_medical', 11, 'Have you ever had or do you currently have: Epilepsy, seizures, convulsions or take medications to prevent them?', 'neurological', 11),
('padi_medical', 12, 'Have you ever had or do you currently have: Recurring complicated migraine headaches or take medications to prevent them?', 'neurological', 12),
('padi_medical', 13, 'Have you ever had or do you currently have: Blackouts or fainting (full/partial loss of consciousness)?', 'neurological', 13),
('padi_medical', 14, 'Have you ever had or do you currently have: Frequent or severe suffering from motion sickness?', 'neurological', 14),
('padi_medical', 15, 'Have you ever had or do you currently have: Dysentery or dehydration requiring medical intervention?', 'gastrointestinal', 15),
('padi_medical', 16, 'Have you ever had or do you currently have: Any dive accident or decompression sickness?', 'diving', 16),
('padi_medical', 17, 'Have you ever had or do you currently have: Unable to perform moderate exercise? (Example: walk 1.6 kilometer/one mile in 12 minutes)', 'fitness', 17),
('padi_medical', 18, 'Have you ever had or do you currently have: Within the last five years, have you had routine medical care, other than for minor illness or injury?', 'general', 18),
('padi_medical', 19, 'Have you ever had or do you currently have: Within the last five years, have you been hospitalized for any reason?', 'general', 19),
('padi_medical', 20, 'Have you ever had or do you currently have: Within the last five years, have you had surgery or spinal injury?', 'surgery', 20),
('padi_medical', 21, 'Have you ever had or do you currently have: Heart disease?', 'heart', 21),
('padi_medical', 22, 'Have you ever had or do you currently have: Heart attack?', 'heart', 22),
('padi_medical', 23, 'Have you ever had or do you currently have: Angina, heart surgery or blood vessel surgery?', 'heart', 23),
('padi_medical', 24, 'Have you ever had or do you currently have: Sinus surgery?', 'ear_nose_throat', 24),
('padi_medical', 25, 'Have you ever had or do you currently have: Ear disease or ear surgery, hearing loss or problems with balance?', 'ear_nose_throat', 25),
('padi_medical', 26, 'Have you ever had or do you currently have: Recurrent ear problems?', 'ear_nose_throat', 26),
('padi_medical', 27, 'Have you ever had or do you currently have: Bleeding or other blood disorders?', 'blood', 27),
('padi_medical', 28, 'Have you ever had or do you currently have: Diabetes?', 'metabolic', 28),
('padi_medical', 29, 'Have you ever had or do you currently have: Back, arm or leg problems following surgery, injury or fracture?', 'musculoskeletal', 29),
('padi_medical', 30, 'Have you ever had or do you currently have: High blood pressure or take medicine to control blood pressure?', 'heart', 30),
('padi_medical', 31, 'Have you ever had or do you currently have: Hernia?', 'gastrointestinal', 31),
('padi_medical', 32, 'Have you ever had or do you currently have: Ulcers or ulcer surgery?', 'gastrointestinal', 32),
('padi_medical', 33, 'Have you ever had or do you currently have: A colostomy or ileostomy?', 'gastrointestinal', 33),
('padi_medical', 34, 'Have you ever had or do you currently have: Recreational drug use or treatment for substance use disorder within the past five years?', 'substance', 34);

-- Medical clearance history (audit trail)
CREATE TABLE IF NOT EXISTS `medical_clearance_history` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `medical_form_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'created, updated, approved, rejected, expired',
    `previous_status` VARCHAR(50),
    `new_status` VARCHAR(50),
    `action_by` BIGINT UNSIGNED COMMENT 'User who performed action',
    `action_notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`medical_form_id`) REFERENCES `customer_medical_forms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`action_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_form` (`medical_form_id`),
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;