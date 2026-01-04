-- Migration: Technical Diving Support & Agency Certifications
-- Full support for technical diving courses, equipment, and certifications
-- Date: 2026-01-04

-- Diving agencies table
CREATE TABLE IF NOT EXISTS `diving_agencies` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE COMMENT 'PADI, SSI, TDI, etc.',
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('recreational', 'technical', 'both') NOT NULL DEFAULT 'recreational',
    `website` VARCHAR(500) NULL,
    `logo_path` VARCHAR(500) NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default diving agencies
INSERT INTO `diving_agencies` (`code`, `name`, `type`, `website`, `sort_order`) VALUES
-- Recreational/Both
('PADI', 'Professional Association of Diving Instructors', 'both', 'https://www.padi.com', 1),
('SSI', 'Scuba Schools International', 'both', 'https://www.divessi.com', 2),
('NAUI', 'National Association of Underwater Instructors', 'both', 'https://www.naui.org', 3),
('SDI', 'Scuba Diving International', 'recreational', 'https://www.tdisdi.com', 4),
('CMAS', 'World Underwater Federation', 'recreational', 'https://www.cmas.org', 5),
('BSAC', 'British Sub-Aqua Club', 'both', 'https://www.bsac.com', 6),
('RAID', 'Rebreather Association of International Divers', 'both', 'https://www.diveraid.com', 7),
-- Technical Only
('TDI', 'Technical Diving International', 'technical', 'https://www.tdisdi.com', 10),
('IANTD', 'International Association of Nitrox and Technical Divers', 'technical', 'https://www.iantd.com', 11),
('GUE', 'Global Underwater Explorers', 'technical', 'https://www.gue.com', 12),
('PSAI', 'Professional Scuba Association International', 'technical', 'https://www.psai.com', 13),
('UTD', 'Unified Team Diving', 'technical', 'https://www.unifiedteamdiving.com', 14);

-- Certification types/levels
CREATE TABLE IF NOT EXISTS `certification_levels` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `agency_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `category` ENUM('recreational', 'specialty', 'technical', 'instructor', 'professional') NOT NULL,
    `is_technical` BOOLEAN DEFAULT FALSE,
    `min_age` TINYINT UNSIGNED DEFAULT 10,
    `prerequisites` TEXT NULL COMMENT 'JSON array of required cert codes',
    `min_dives` INT UNSIGNED NULL,
    `description` TEXT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`agency_id`) REFERENCES `diving_agencies`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_agency_code` (`agency_id`, `code`),
    INDEX `idx_category` (`category`),
    INDEX `idx_technical` (`is_technical`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert common certification levels
INSERT INTO `certification_levels` (`agency_id`, `code`, `name`, `category`, `is_technical`, `min_age`, `min_dives`) VALUES
-- PADI Recreational (agency_id = 1)
(1, 'OW', 'Open Water Diver', 'recreational', FALSE, 10, 4),
(1, 'AOW', 'Advanced Open Water Diver', 'recreational', FALSE, 12, 9),
(1, 'RESCUE', 'Rescue Diver', 'recreational', FALSE, 12, 20),
(1, 'DM', 'Divemaster', 'professional', FALSE, 18, 60),
(1, 'OWSI', 'Open Water Scuba Instructor', 'instructor', FALSE, 18, 100),
-- PADI Technical
(1, 'EAN', 'Enriched Air Nitrox', 'specialty', FALSE, 12, NULL),
(1, 'DEEP', 'Deep Diver', 'specialty', FALSE, 15, NULL),
(1, 'TECREC_40', 'Tec 40', 'technical', TRUE, 18, 30),
(1, 'TECREC_45', 'Tec 45', 'technical', TRUE, 18, 50),
(1, 'TECREC_50', 'Tec 50', 'technical', TRUE, 18, 100),
(1, 'TECREC_TRIMIX', 'Tec Trimix', 'technical', TRUE, 18, 150),
-- TDI Technical (agency_id = 10)
(10, 'NITROX', 'Nitrox Diver', 'specialty', FALSE, 15, NULL),
(10, 'ADV_NITROX', 'Advanced Nitrox', 'technical', TRUE, 18, 25),
(10, 'DECO', 'Decompression Procedures', 'technical', TRUE, 18, 50),
(10, 'EXT_RANGE', 'Extended Range', 'technical', TRUE, 18, 100),
(10, 'TRIMIX', 'Trimix Diver', 'technical', TRUE, 18, 150),
(10, 'ADV_TRIMIX', 'Advanced Trimix', 'technical', TRUE, 18, 200),
(10, 'CAVE_INTRO', 'Intro to Cave', 'technical', TRUE, 18, 100),
(10, 'CAVE_FULL', 'Full Cave', 'technical', TRUE, 18, 150),
(10, 'CAVE_STAGE', 'Stage Cave', 'technical', TRUE, 18, 200),
(10, 'SIDEMOUNT', 'Sidemount Diver', 'technical', TRUE, 18, 25),
(10, 'CCR', 'CCR Diver', 'technical', TRUE, 18, 100),
(10, 'WRECK_PEN', 'Wreck Penetration', 'technical', TRUE, 18, 50);

-- Technical equipment types
CREATE TABLE IF NOT EXISTS `equipment_gas_compatibility` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `equipment_id` BIGINT UNSIGNED NOT NULL COMMENT 'References rental_equipment',
    `oxygen_clean` BOOLEAN DEFAULT FALSE,
    `oxygen_service_date` DATE NULL,
    `max_oxygen_percentage` DECIMAL(5,2) DEFAULT 21.00,
    `helium_compatible` BOOLEAN DEFAULT FALSE,
    `max_pressure_bar` INT UNSIGNED DEFAULT 232,
    `rebreather_compatible` BOOLEAN DEFAULT FALSE,
    `last_service_date` DATE NULL,
    `next_service_due` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_equipment` (`equipment_id`),
    INDEX `idx_oxygen_clean` (`oxygen_clean`),
    INDEX `idx_next_service` (`next_service_due`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gas analyzer records
CREATE TABLE IF NOT EXISTS `gas_analysis_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `equipment_id` BIGINT UNSIGNED NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `analyzed_by` BIGINT UNSIGNED NOT NULL,
    `analysis_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `gas_type` ENUM('air', 'nitrox', 'trimix', 'heliox', 'oxygen') NOT NULL,
    `oxygen_percentage` DECIMAL(5,2) NOT NULL,
    `helium_percentage` DECIMAL(5,2) DEFAULT 0,
    `pressure_bar` INT UNSIGNED NOT NULL,
    `mod_meters` INT UNSIGNED NULL COMMENT 'Maximum Operating Depth in meters',
    `mod_feet` INT UNSIGNED NULL COMMENT 'Maximum Operating Depth in feet',
    `customer_verified` BOOLEAN DEFAULT FALSE,
    `customer_signature` LONGTEXT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`analyzed_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_equipment` (`equipment_id`),
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_date` (`analysis_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rebreather service records
CREATE TABLE IF NOT EXISTS `rebreather_service_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `equipment_id` BIGINT UNSIGNED NOT NULL,
    `service_type` ENUM('pre_dive', 'post_dive', 'cell_replacement', 'scrubber_change', 'annual_service', 'full_service') NOT NULL,
    `performed_by` BIGINT UNSIGNED NOT NULL,
    `service_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `cell_1_mv` DECIMAL(6,2) NULL,
    `cell_2_mv` DECIMAL(6,2) NULL,
    `cell_3_mv` DECIMAL(6,2) NULL,
    `scrubber_time_remaining` INT UNSIGNED NULL COMMENT 'Minutes',
    `scrubber_replaced` BOOLEAN DEFAULT FALSE,
    `loop_test_passed` BOOLEAN NULL,
    `negative_test_passed` BOOLEAN NULL,
    `positive_test_passed` BOOLEAN NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_equipment` (`equipment_id`),
    INDEX `idx_date` (`service_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
