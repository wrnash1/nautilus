SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `marine_species`;
DROP TABLE IF EXISTS `dive_statistics`;
DROP TABLE IF EXISTS `dive_log_media`;
DROP TABLE IF EXISTS `dive_logs`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `marine_species`;
DROP TABLE IF EXISTS `dive_statistics`;
DROP TABLE IF EXISTS `dive_log_media`;
DROP TABLE IF EXISTS `dive_logs`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `marine_species`;
DROP TABLE IF EXISTS `dive_statistics`;
DROP TABLE IF EXISTS `dive_log_media`;
DROP TABLE IF EXISTS `dive_logs`;

-- ================================================
-- Nautilus - Digital Dive Log System
-- Migration: 077_dive_log_system.sql
-- Description: Comprehensive dive logging with PADI/SSI RDP integration
-- ================================================

-- Dive Logs
CREATE TABLE IF NOT EXISTS `dive_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Diver Information
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `dive_number` INT NOT NULL COMMENT 'Lifetime dive count for this diver',

    -- Dive Details
    `dive_date` DATE NOT NULL,
    `dive_site_id` BIGINT UNSIGNED NULL,
    `dive_site_name` VARCHAR(255) NULL COMMENT 'If not in database',
    `location` VARCHAR(255) NULL,
    `country` VARCHAR(100) NULL,

    -- Dive Profile
    `entry_time` TIME NULL,
    `exit_time` TIME NULL,
    `surface_interval_minutes` INT NULL COMMENT 'From previous dive',
    `max_depth_feet` DECIMAL(6,2) NULL,
    `max_depth_meters` DECIMAL(6,2) NULL,
    `average_depth_feet` DECIMAL(6,2) NULL,
    `average_depth_meters` DECIMAL(6,2) NULL,
    `bottom_time_minutes` INT NULL,
    `total_dive_time_minutes` INT NULL,

    -- Air/Gas
    `starting_pressure_psi` INT NULL,
    `ending_pressure_psi` INT NULL,
    `starting_pressure_bar` INT NULL,
    `ending_pressure_bar` INT NULL,
    `tank_size_cu_ft` INT NULL,
    `gas_mix` VARCHAR(50) DEFAULT 'Air' COMMENT 'Air, EAN32, EAN36, Trimix, etc.',
    `oxygen_percentage` DECIMAL(5,2) NULL,

    -- Conditions
    `water_temp_fahrenheit` DECIMAL(5,2) NULL,
    `water_temp_celsius` DECIMAL(5,2) NULL,
    `air_temp_fahrenheit` DECIMAL(5,2) NULL,
    `air_temp_celsius` DECIMAL(5,2) NULL,
    `visibility_feet` INT NULL,
    `visibility_meters` INT NULL,
    `current` ENUM('none', 'light', 'moderate', 'strong', 'very_strong') NULL,
    `wave_height` ENUM('calm', 'light', 'moderate', 'rough', 'very_rough') NULL,
    `weather` VARCHAR(255) NULL,

    -- Entry/Exit
    `entry_type` ENUM('shore', 'boat', 'beach', 'platform', 'ice', 'other') NULL,
    `exit_type` ENUM('shore', 'boat', 'beach', 'platform', 'ice', 'other') NULL,

    -- Dive Purpose/Type
    `dive_type` ENUM('recreational', 'training', 'technical', 'work', 'research', 'specialty') DEFAULT 'recreational',
    `dive_purpose` JSON NULL COMMENT 'Array: fun, photography, navigation, wreck, etc.',

    -- Equipment Used
    `wetsuit_thickness_mm` INT NULL,
    `weight_used_lbs` DECIMAL(5,2) NULL,
    `weight_used_kg` DECIMAL(5,2) NULL,
    `bcd_type` VARCHAR(100) NULL,
    `computer_used` VARCHAR(100) NULL,
    `additional_equipment` JSON NULL,

    -- Buddy Information
    `buddy_customer_id` BIGINT UNSIGNED NULL,
    `buddy_name` VARCHAR(255) NULL COMMENT 'If buddy not in system',

    -- Dive Conducted With
    `trip_id` BIGINT UNSIGNED NULL,
    `instructor_id` BIGINT UNSIGNED NULL COMMENT 'If training dive',
    `dive_operator` VARCHAR(255) NULL,
    `boat_name` VARCHAR(255) NULL,

    -- Safety & Decompression
    `safety_stop_made` BOOLEAN DEFAULT FALSE,
    `safety_stop_depth_feet` INT NULL,
    `safety_stop_duration_minutes` INT NULL,
    `deco_stops_made` BOOLEAN DEFAULT FALSE,
    `deco_schedule` TEXT NULL,
    `residual_nitrogen_time` INT NULL COMMENT 'From dive computer/RDP',

    -- Marine Life & Sightings
    `marine_life_sightings` JSON NULL COMMENT 'Array of species seen',
    `highlights` TEXT NULL,

    -- Ratings & Conditions Assessment
    `visibility_rating` INT NULL COMMENT '1-5 stars',
    `conditions_rating` INT NULL COMMENT '1-5 stars',
    `dive_rating` INT NULL COMMENT 'Overall dive rating 1-5',

    -- Notes
    `notes` TEXT NULL,
    `problems_encountered` TEXT NULL,

    -- Photos/Videos
    `photo_count` INT DEFAULT 0,
    `video_count` INT DEFAULT 0,
    `media_folder_path` VARCHAR(500) NULL,

    -- Certification & Skills
    `certification_dive` BOOLEAN DEFAULT FALSE,
    `course_id` BIGINT UNSIGNED NULL,
    `skills_practiced` JSON NULL,

    -- GPS Location
    `gps_latitude` DECIMAL(10, 8) NULL,
    `gps_longitude` DECIMAL(11, 8) NULL,

    -- Verification
    `buddy_verified` BOOLEAN DEFAULT FALSE,
    `buddy_signature` TEXT NULL COMMENT 'Digital signature data',
    `instructor_verified` BOOLEAN DEFAULT FALSE,
    `instructor_signature` TEXT NULL,

    -- Dive Computer Data
    `computer_profile_file` VARCHAR(500) NULL COMMENT 'Path to .fit, .xml, etc.',
    `computer_data_imported` BOOLEAN DEFAULT FALSE,

    -- Status
    `is_public` BOOLEAN DEFAULT FALSE COMMENT 'Share in public dive log',
    `is_favorite` BOOLEAN DEFAULT FALSE,

    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `logged_by_user_id` BIGINT UNSIGNED NULL COMMENT 'Who created this log entry',

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`dive_site_id`) REFERENCES `dive_sites`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`buddy_customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`trip_id`) REFERENCES `trips`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`logged_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_customer` (`customer_id`, `dive_date`),
    INDEX `idx_dive_site` (`dive_site_id`),
    INDEX `idx_dive_date` (`dive_date`),
    INDEX `idx_dive_number` (`customer_id`, `dive_number`),
    INDEX `idx_location` (`country`, `location`),
    INDEX `idx_trip` (`trip_id`),
    INDEX `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dive Log Photos/Videos
CREATE TABLE IF NOT EXISTS `dive_log_media` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dive_log_id` BIGINT UNSIGNED NOT NULL,

    `media_type` ENUM('photo', 'video') NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size_bytes` BIGINT NULL,

    -- Metadata
    `caption` TEXT NULL,
    `timestamp_in_dive` INT NULL COMMENT 'Minutes into dive when taken',
    `depth_when_taken_feet` DECIMAL(6,2) NULL,

    -- Species Tagging
    `species_tagged` JSON NULL COMMENT 'Marine life in photo',

    -- Ordering
    `display_order` INT DEFAULT 0,
    `is_cover_photo` BOOLEAN DEFAULT FALSE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`dive_log_id`) REFERENCES `dive_logs`(`id`) ON DELETE CASCADE,

    INDEX `idx_dive_log` (`dive_log_id`),
    INDEX `idx_cover` (`dive_log_id`, `is_cover_photo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dive Statistics (aggregated data for quick access)
CREATE TABLE IF NOT EXISTS `dive_statistics` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Totals
    `total_dives` INT DEFAULT 0,
    `total_bottom_time_minutes` INT DEFAULT 0,
    `max_depth_ever_feet` DECIMAL(6,2) NULL,
    `max_depth_ever_meters` DECIMAL(6,2) NULL,

    -- By Year
    `dives_this_year` INT DEFAULT 0,
    `dives_last_year` INT DEFAULT 0,

    -- Certifications
    `current_certification_level` VARCHAR(100) NULL,
    `total_certifications` INT DEFAULT 0,

    -- Dive Sites
    `unique_dive_sites` INT DEFAULT 0,
    `unique_countries` INT DEFAULT 0,

    -- Last Dive
    `last_dive_date` DATE NULL,
    `last_dive_site` VARCHAR(255) NULL,

    -- Specialty Counts
    `night_dives` INT DEFAULT 0,
    `deep_dives` INT DEFAULT 0,
    `wreck_dives` INT DEFAULT 0,
    `nitrox_dives` INT DEFAULT 0,

    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,

    UNIQUE KEY `unique_customer_stats` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marine Life Species Database
CREATE TABLE IF NOT EXISTS `marine_species` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `common_name` VARCHAR(255) NOT NULL,
    `scientific_name` VARCHAR(255) NULL,
    `category` VARCHAR(100) NULL COMMENT 'Fish, Coral, Mammal, Invertebrate, etc.',
    `description` TEXT NULL,

    `conservation_status` ENUM('LC', 'NT', 'VU', 'EN', 'CR', 'EW', 'EX') NULL COMMENT 'IUCN Red List',

    `photo_url` VARCHAR(500) NULL,
    `info_url` VARCHAR(500) NULL,

    `is_active` BOOLEAN DEFAULT TRUE,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_common_name` (`common_name`),
    INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common marine species
INSERT INTO `marine_species` (`common_name`, `scientific_name`, `category`) VALUES
('Green Sea Turtle', 'Chelonia mydas', 'Reptile'),
('Hawksbill Turtle', 'Eretmochelys imbricata', 'Reptile'),
('Manta Ray', 'Manta birostris', 'Fish'),
('Whale Shark', 'Rhincodon typus', 'Fish'),
('Great White Shark', 'Carcharodon carcharias', 'Fish'),
('Hammerhead Shark', 'Sphyrna mokarran', 'Fish'),
('Reef Shark', 'Carcharhinus melanopterus', 'Fish'),
('Clownfish', 'Amphiprioninae', 'Fish'),
('Barracuda', 'Sphyraena', 'Fish'),
('Moray Eel', 'Muraenidae', 'Fish'),
('Octopus', 'Octopoda', 'Invertebrate'),
('Dolphin', 'Delphinidae', 'Mammal'),
('Sea Lion', 'Otariinae', 'Mammal'),
('Jellyfish', 'Medusozoa', 'Invertebrate'),
('Seahorse', 'Hippocampus', 'Fish'),
('Stingray', 'Dasyatidae', 'Fish'),
('Grouper', 'Epinephelus', 'Fish'),
('Angelfish', 'Pomacanthidae', 'Fish'),
('Lionfish', 'Pterois', 'Fish'),
('Nudibranch', 'Nudibranchia', 'Invertebrate');


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;