-- ================================================
-- Nautilus V6 - PADI Compliance: Enhanced Waivers
-- Migration: 052_padi_compliance_waivers_enhanced.sql
-- Description: Enhanced liability waivers linked to customers
-- ================================================

-- Enhanced customer waivers (multiple PADI waiver types)
CREATE TABLE IF NOT EXISTS `customer_waivers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `waiver_type` ENUM(
        'general_training',      -- 10072 Release of Liability - General Training
        'nitrox',                -- 10078 Enriched Air (Nitrox) Training Release
        'travel',                -- 10079 Travel and Excursions
        'diver_activities',      -- 10086 Diver Activities
        'equipment_rental',      -- 10087 Equipment Rental Agreement
        'special_event',         -- 10085 Special Event
        'minor',                 -- 10348 Florida Minor Child Parent Agreement
        'non_agency',            -- 10334 Non-Agency Disclosure
        'youth_diving',          -- 10615 Youth Diving Responsibility
        'safe_diving_practices', -- 10060 Standard Safe Diving Practices
        'self_reliant',          -- 10155 Certified Self-Reliant Diver
        'supplied_air_snorkel'   -- 10091 Supplied Air Snorkeling
    ) NOT NULL,

    -- Associated Activities
    `course_id` INT UNSIGNED COMMENT 'If course-specific',
    `trip_id` INT UNSIGNED COMMENT 'If trip-specific',
    `rental_id` INT UNSIGNED COMMENT 'If equipment rental',

    -- Waiver Content
    `waiver_template_id` INT UNSIGNED COMMENT 'Template used',
    `waiver_text` LONGTEXT COMMENT 'Full waiver text at time of signing',
    `acknowledgments` JSON COMMENT 'List of acknowledgments checked',

    -- Primary Signature
    `signature_path` VARCHAR(255),
    `signature_date` DATE,
    `signature_ip_address` VARCHAR(45),
    `signature_location` VARCHAR(255) COMMENT 'Where signed (GPS or location name)',

    -- Witness (if applicable)
    `witness_name` VARCHAR(255),
    `witness_signature_path` VARCHAR(255),
    `witness_date` DATE,

    -- For Minors
    `is_minor` BOOLEAN DEFAULT FALSE,
    `minor_age` INT,
    `parent_guardian_name` VARCHAR(255),
    `parent_guardian_relationship` VARCHAR(50),
    `parent_signature_path` VARCHAR(255),
    `parent_signature_date` DATE,
    `parent_id_verified` BOOLEAN DEFAULT FALSE,
    `parent_id_type` VARCHAR(100),

    -- Emergency Contact (often on waivers)
    `emergency_contact_name` VARCHAR(255),
    `emergency_contact_phone` VARCHAR(50),
    `emergency_contact_relationship` VARCHAR(100),

    -- Validity Period
    `valid_from` DATE NOT NULL,
    `valid_until` DATE COMMENT 'Some waivers expire (typically 1 year)',
    `status` ENUM('pending', 'signed', 'expired', 'revoked') DEFAULT 'pending',

    -- Document Management
    `pdf_path` VARCHAR(255) COMMENT 'Signed PDF',
    `pdf_generated_at` TIMESTAMP NULL,

    -- Compliance
    `reviewed_by` INT UNSIGNED COMMENT 'Staff who reviewed',
    `reviewed_at` TIMESTAMP NULL,
    `review_notes` TEXT,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_customer_type` (`customer_id`, `waiver_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_valid_dates` (`valid_from`, `valid_until`),
    INDEX `idx_course` (`course_id`),
    INDEX `idx_trip` (`trip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waiver templates (master waiver documents)
CREATE TABLE IF NOT EXISTS `waiver_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `waiver_type` VARCHAR(100) NOT NULL,
    `template_name` VARCHAR(255) NOT NULL,
    `padi_form_number` VARCHAR(50) COMMENT 'e.g., 10072, 10079',
    `version` VARCHAR(50) COMMENT 'e.g., Rev. 01/21',
    `language` VARCHAR(10) DEFAULT 'en',

    -- Content
    `waiver_title` VARCHAR(255),
    `waiver_text` LONGTEXT NOT NULL,
    `acknowledgment_items` JSON COMMENT 'List of items participant must acknowledge',
    `requires_witness` BOOLEAN DEFAULT FALSE,
    `requires_parent_signature` BOOLEAN DEFAULT FALSE,

    -- Validity
    `validity_days` INT COMMENT 'How long waiver is valid (e.g., 365)',
    `is_active` BOOLEAN DEFAULT TRUE,
    `effective_date` DATE,

    -- Document
    `pdf_template_path` VARCHAR(255) COMMENT 'Blank PDF template',

    -- Metadata
    `created_by` INT UNSIGNED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_type_active` (`waiver_type`, `is_active`),
    INDEX `idx_padi_form` (`padi_form_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed standard PADI waiver templates
INSERT INTO `waiver_templates` (`id`, `waiver_type`, `template_name`, `padi_form_number`, `version`, `waiver_title`, `waiver_text`, `acknowledgment_items`, `requires_witness`, `validity_days`, `is_active`, `effective_date`) VALUES
(1, 'general_training', 'PADI General Training Liability Release', '10072', 'Rev. 01/21',
'Release of Liability, Assumption of Risk, and Non-Agency Acknowledgment Agreement',
'[Full PADI Form 10072 text - to be filled in by administrator]',
'["I understand that scuba diving involves certain inherent risks", "I affirm that I do not have any medical conditions that would prevent me from diving", "I agree to follow all safety rules and instructions", "I release and hold harmless the dive center, instructors, and PADI from all liability"]',
FALSE, 365, TRUE, '2021-01-01'),

(2, 'nitrox', 'PADI Enriched Air (Nitrox) Training Release', '10078', 'Rev. 01/19',
'Enriched Air (Nitrox) Diver Training Release of Liability',
'[Full PADI Form 10078 text - to be filled in by administrator]',
'["I understand the risks of oxygen exposure", "I will analyze all gas mixtures before use", "I understand maximum operating depths for nitrox", "I will not exceed depth limits for my gas mixture"]',
FALSE, 365, TRUE, '2019-01-01'),

(3, 'travel', 'PADI Travel and Excursions Release', '10079', 'Rev. 01/22',
'Travel and Excursions Liability Release',
'[Full PADI Form 10079 text - to be filled in by administrator]',
'["I understand travel involves additional risks", "I have appropriate insurance coverage", "I am physically fit to dive", "I accept all travel-related risks"]',
FALSE, NULL, TRUE, '2022-01-01'),

(4, 'diver_activities', 'PADI Diver Activities Release', '10086', 'Rev. 01/22',
'Release of Liability for Diver Activities',
'[Full PADI Form 10086 text - to be filled in by administrator]',
'["I am a certified diver", "I understand the risks of recreational diving", "I will dive within my certification limits", "I accept responsibility for my own safety"]',
FALSE, 365, TRUE, '2022-01-01'),

(5, 'equipment_rental', 'PADI Equipment Rental Agreement', '10087', 'Rev. 01/20',
'Equipment Rental Agreement',
'[Full PADI Form 10087 text - to be filled in by administrator]',
'["I am certified to use this equipment", "I will inspect all equipment before use", "I am responsible for loss or damage", "I will return equipment in good condition"]',
TRUE, NULL, TRUE, '2020-01-01'),

(6, 'minor', 'Florida Minor Child Parent Agreement', '10348', 'Rev. 01/18',
'Florida Minor Child Parent Agreement',
'[Full PADI Form 10348 text - to be filled in by administrator]',
'["I am the parent/guardian of the minor", "I understand the risks to my child", "I authorize my child to participate", "I assume all risks on behalf of my child"]',
FALSE, 365, TRUE, '2018-01-01'),

(7, 'non_agency', 'Non-Agency Disclosure', '10334', 'Rev. 01/15',
'Non-Agency Disclosure and Acknowledgment Agreement',
'[Full PADI Form 10334 text - to be filled in by administrator]',
'["I understand PADI is not the dive center operator", "PADI does not supervise, monitor or control the dive center", "The dive center is independent", "PADI is not liable for the dive center actions"]',
FALSE, NULL, TRUE, '2015-01-01'),

(8, 'youth_diving', 'Youth Diving Responsibility', '10615', 'Rev. 01/17',
'Youth Diving: Responsibility and Risks Acknowledgment',
'[Full PADI Form 10615 text - to be filled in by administrator]',
'["I understand youth diving restrictions", "I will supervise the minor diver appropriately", "I accept the risks of youth diving", "I will ensure proper supervision"]',
FALSE, 365, TRUE, '2017-01-01'),

(9, 'safe_diving_practices', 'Standard Safe Diving Practices', '10060', 'Rev. 01/19',
'Standard Safe Diving Practices Statement of Understanding',
'[Full PADI Form 10060 text - to be filled in by administrator]',
'["I will dive with a buddy", "I will monitor depth and time", "I will make safety stops", "I will not dive beyond my training", "I will maintain my equipment"]',
FALSE, NULL, TRUE, '2019-01-01');

-- Waiver reminders (for expiring waivers)
CREATE TABLE IF NOT EXISTS `waiver_reminders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_waiver_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `reminder_type` ENUM('expiring_soon', 'expired', 'renewal_needed') NOT NULL,
    `reminder_sent_at` TIMESTAMP NULL,
    `reminder_method` VARCHAR(50) COMMENT 'email, sms, in_person',
    `is_acknowledged` BOOLEAN DEFAULT FALSE,
    `acknowledged_at` TIMESTAMP NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_waiver_id`) REFERENCES `customer_waivers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,

    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_sent` (`reminder_sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
