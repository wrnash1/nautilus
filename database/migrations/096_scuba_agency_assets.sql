-- Migration: Scuba Agency Assets & Certifications
-- Master list of all diving agencies and certification types
-- Date: 2026-01-04

-- Diving agencies master list
CREATE TABLE IF NOT EXISTS `diving_agency_logos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `website` VARCHAR(255) NULL,
    `logo_url` VARCHAR(500) NULL COMMENT 'Path or URL to logo file',
    `logo_svg` TEXT NULL COMMENT 'Inline SVG if available',
    `is_recreational` BOOLEAN DEFAULT TRUE,
    `is_technical` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `sort_order` INT DEFAULT 99,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pre-populate with major diving agencies
INSERT INTO `diving_agency_logos` (`code`, `name`, `website`, `is_recreational`, `is_technical`, `sort_order`) VALUES
('PADI', 'Professional Association of Diving Instructors', 'https://www.padi.com', TRUE, TRUE, 1),
('SSI', 'Scuba Schools International', 'https://www.divessi.com', TRUE, TRUE, 2),
('NAUI', 'National Association of Underwater Instructors', 'https://www.naui.org', TRUE, TRUE, 3),
('SDI', 'Scuba Diving International', 'https://www.tdisdi.com', TRUE, FALSE, 4),
('TDI', 'Technical Diving International', 'https://www.tdisdi.com', FALSE, TRUE, 5),
('IANTD', 'International Association of Nitrox & Technical Divers', 'https://www.iantd.com', FALSE, TRUE, 6),
('GUE', 'Global Underwater Explorers', 'https://www.gue.com', FALSE, TRUE, 7),
('PSAI', 'Professional Scuba Association International', 'https://www.psai.com', FALSE, TRUE, 8),
('UTD', 'Unified Team Diving', 'https://www.utdscubadiving.com', FALSE, TRUE, 9),
('RAID', 'Rebreather Association of International Divers', 'https://www.diveraid.com', TRUE, TRUE, 10),
('CMAS', 'Confédération Mondiale des Activités Subaquatiques', 'https://www.cmas.org', TRUE, FALSE, 11),
('BSAC', 'British Sub-Aqua Club', 'https://www.bsac.com', TRUE, FALSE, 12),
('PDIC', 'Professional Diving Instructors Corporation', 'https://www.pdic-intl.com', TRUE, FALSE, 13),
('DAN', 'Divers Alert Network', 'https://www.dan.org', TRUE, TRUE, 14),
('RSTC', 'Recreational Scuba Training Council', 'https://www.rstc.org', TRUE, FALSE, 15);

-- Certification types master list (expanded)
CREATE TABLE IF NOT EXISTS `certification_type_master` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `category` ENUM('recreational', 'advanced', 'professional', 'technical', 'specialty') NOT NULL,
    `level` INT UNSIGNED DEFAULT 1 COMMENT 'Numeric level for comparison',
    `min_age` INT UNSIGNED DEFAULT 10,
    `min_dives` INT UNSIGNED DEFAULT 0,
    `typical_agencies` VARCHAR(255) NULL COMMENT 'Comma-separated agency codes',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate recreational certifications
INSERT INTO `certification_type_master` (`code`, `name`, `category`, `level`, `min_age`, `min_dives`, `typical_agencies`) VALUES
-- Entry Level
('OW', 'Open Water Diver', 'recreational', 1, 10, 0, 'PADI,SSI,NAUI,SDI'),
('SD', 'Scuba Diver', 'recreational', 0, 10, 0, 'PADI,SSI'),
('DSD', 'Discover Scuba Diving', 'recreational', 0, 10, 0, 'PADI'),
('TRY', 'Try Scuba', 'recreational', 0, 10, 0, 'SSI,SDI'),

-- Advanced Recreational
('AOW', 'Advanced Open Water Diver', 'advanced', 2, 12, 4, 'PADI,SSI,NAUI,SDI'),
('AOWD', 'Advanced Adventurer', 'advanced', 2, 15, 4, 'SSI'),
('RESCUE', 'Rescue Diver', 'advanced', 3, 12, 20, 'PADI,SSI,NAUI,SDI'),
('STRESS', 'Stress & Rescue Diver', 'advanced', 3, 15, 20, 'SSI'),

-- Professional
('DM', 'Divemaster', 'professional', 4, 18, 40, 'PADI,SSI,NAUI'),
('OWSI', 'Open Water Scuba Instructor', 'professional', 5, 18, 100, 'PADI,SSI,NAUI'),
('MASTER', 'Master Instructor', 'professional', 6, 18, 500, 'PADI,SSI'),
('CD', 'Course Director', 'professional', 7, 18, 1000, 'PADI'),
('IDC_STAFF', 'IDC Staff Instructor', 'professional', 6, 18, 500, 'PADI'),
('DIVE_CONTROL', 'Dive Control Specialist', 'professional', 5, 18, 60, 'SSI'),

-- Technical
('NITROX', 'Enriched Air Nitrox', 'specialty', 1, 12, 0, 'PADI,SSI,NAUI,TDI,SDI'),
('DEEP', 'Deep Diver', 'specialty', 2, 15, 10, 'PADI,SSI,NAUI'),
('ADV_NITROX', 'Advanced Nitrox', 'technical', 3, 18, 25, 'TDI,IANTD,SSI'),
('DECO', 'Decompression Procedures', 'technical', 4, 18, 50, 'TDI,IANTD,GUE'),
('EXTENDED', 'Extended Range', 'technical', 5, 18, 100, 'TDI,IANTD'),
('TRIMIX', 'Trimix Diver', 'technical', 6, 18, 100, 'TDI,IANTD,GUE'),
('ADV_TRIMIX', 'Advanced Trimix', 'technical', 7, 18, 150, 'TDI,IANTD'),
('HELITROX', 'Helitrox Diver', 'technical', 5, 18, 75, 'TDI'),

-- Rebreather
('CCR_INTRO', 'Introduction to CCR', 'technical', 4, 18, 25, 'TDI,IANTD,PADI'),
('CCR_MOD1', 'CCR Air Diluent', 'technical', 5, 18, 50, 'TDI,IANTD'),
('CCR_MOD2', 'CCR Mixed Gas', 'technical', 6, 18, 100, 'TDI,IANTD'),
('SCR', 'Semi-Closed Rebreather', 'technical', 4, 18, 25, 'TDI,SSI'),

-- Cave Diving
('CAVE_INTRO', 'Intro to Cave', 'technical', 4, 18, 50, 'TDI,IANTD,GUE,NSS-CDS'),
('CAVE_FULL', 'Full Cave Diver', 'technical', 5, 18, 75, 'TDI,IANTD,GUE,NSS-CDS'),
('CAVE_STAGE', 'Stage Cave', 'technical', 6, 18, 100, 'TDI,IANTD'),
('CAVE_SURVEY', 'Cave Survey', 'technical', 6, 18, 150, 'TDI,NSS-CDS'),

-- Wreck Diving
('WRECK', 'Wreck Diver', 'specialty', 2, 15, 10, 'PADI,SSI,NAUI,TDI'),
('WRECK_ADV', 'Advanced Wreck', 'technical', 4, 18, 50, 'TDI,IANTD'),
('WRECK_PEN', 'Wreck Penetration', 'technical', 4, 18, 50, 'TDI'),

-- Sidemount & Other
('SIDEMOUNT', 'Sidemount Diver', 'specialty', 2, 15, 25, 'TDI,PADI,SSI'),
('SIDEMOUNT_CAVE', 'Sidemount Cave', 'technical', 5, 18, 100, 'TDI'),
('DPV', 'Diver Propulsion Vehicle', 'specialty', 2, 15, 25, 'TDI,PADI'),
('MINE', 'Mine Diving', 'technical', 5, 18, 100, 'TDI'),
('ICE', 'Ice Diver', 'specialty', 3, 18, 25, 'PADI,SSI,TDI'),
('ALTITUDE', 'Altitude Diver', 'specialty', 2, 15, 5, 'PADI,SSI'),
('DRYSUIT', 'Dry Suit Diver', 'specialty', 1, 10, 0, 'PADI,SSI,NAUI'),
('NAVIGATION', 'Underwater Navigator', 'specialty', 2, 12, 4, 'PADI,SSI,NAUI'),
('NIGHT', 'Night Diver', 'specialty', 2, 12, 4, 'PADI,SSI'),
('BOAT', 'Boat Diver', 'specialty', 1, 10, 0, 'PADI,SSI'),

-- First Aid / Safety
('EFR', 'Emergency First Response', 'specialty', 0, 12, 0, 'PADI'),
('RFA', 'React First Aid', 'specialty', 0, 12, 0, 'SSI'),
('O2', 'Emergency Oxygen Provider', 'specialty', 0, 12, 0, 'DAN,PADI'),
('AED', 'AED Provider', 'specialty', 0, 15, 0, 'EFR');

-- Certification equivalency mapping (for cross-agency recognition)
CREATE TABLE IF NOT EXISTS `certification_equivalency` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `cert_type_id` INT UNSIGNED NOT NULL,
    `agency_code` VARCHAR(20) NOT NULL,
    `agency_cert_name` VARCHAR(100) NOT NULL COMMENT 'Exact name used by this agency',
    `agency_cert_code` VARCHAR(50) NULL COMMENT 'Agency internal code if available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cert_agency` (`cert_type_id`, `agency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Common equivalencies
INSERT INTO `certification_equivalency` (`cert_type_id`, `agency_code`, `agency_cert_name`) 
SELECT m.id, 'PADI', 'Open Water Diver' FROM `certification_type_master` m WHERE m.code = 'OW';

INSERT INTO `certification_equivalency` (`cert_type_id`, `agency_code`, `agency_cert_name`) 
SELECT m.id, 'SSI', 'Open Water Diver' FROM `certification_type_master` m WHERE m.code = 'OW';

INSERT INTO `certification_equivalency` (`cert_type_id`, `agency_code`, `agency_cert_name`) 
SELECT m.id, 'NAUI', 'Scuba Diver' FROM `certification_type_master` m WHERE m.code = 'OW';

INSERT INTO `certification_equivalency` (`cert_type_id`, `agency_code`, `agency_cert_name`) 
SELECT m.id, 'SDI', 'Open Water Scuba Diver' FROM `certification_type_master` m WHERE m.code = 'OW';
