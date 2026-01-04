SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `travel_partner_apis`;
DROP TABLE IF EXISTS `travel_reviews`;
DROP TABLE IF EXISTS `travel_bookings`;
DROP TABLE IF EXISTS `travel_packages`;
DROP TABLE IF EXISTS `liveaboard_boats`;
DROP TABLE IF EXISTS `dive_resorts`;
DROP TABLE IF EXISTS `travel_destinations`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `travel_partner_apis`;
DROP TABLE IF EXISTS `travel_reviews`;
DROP TABLE IF EXISTS `travel_bookings`;
DROP TABLE IF EXISTS `travel_packages`;
DROP TABLE IF EXISTS `liveaboard_boats`;
DROP TABLE IF EXISTS `dive_resorts`;
DROP TABLE IF EXISTS `travel_destinations`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `travel_partner_apis`;
DROP TABLE IF EXISTS `travel_reviews`;
DROP TABLE IF EXISTS `travel_bookings`;
DROP TABLE IF EXISTS `travel_packages`;
DROP TABLE IF EXISTS `liveaboard_boats`;
DROP TABLE IF EXISTS `dive_resorts`;
DROP TABLE IF EXISTS `travel_destinations`;

-- =====================================================
-- Comprehensive Travel Agent System
-- Dive trips, cruises, liveaboards, resorts, and travel packages
-- =====================================================

-- Travel Destinations
CREATE TABLE IF NOT EXISTS `travel_destinations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `destination_name` VARCHAR(255) NOT NULL,
    `country` VARCHAR(100) NOT NULL,
    `region` VARCHAR(100) NULL COMMENT 'Caribbean, Pacific, Indian Ocean, etc.',
    `city` VARCHAR(100) NULL,

    -- Geographic
    `latitude` DECIMAL(10, 8) NULL,
    `longitude` DECIMAL(11, 8) NULL,
    `timezone` VARCHAR(50) NULL,

    -- Description
    `description` TEXT NULL,
    `highlights` JSON NULL COMMENT 'Key features and attractions',
    `best_season` VARCHAR(100) NULL COMMENT 'Best time to visit',

    -- Diving Info
    `avg_water_temp_f` INT NULL,
    `avg_visibility_ft` INT NULL,
    `skill_level_required` ENUM('beginner', 'intermediate', 'advanced', 'all_levels') DEFAULT 'all_levels',
    `dive_site_count` BIGINT UNSIGNED DEFAULT 0,
    `notable_marine_life` JSON NULL,

    -- Media
    `featured_image_url` VARCHAR(500) NULL,
    `gallery_images` JSON NULL,
    `video_url` VARCHAR(500) NULL,

    -- Ratings
    `average_rating` DECIMAL(3, 2) DEFAULT 0.00,
    `review_count` BIGINT UNSIGNED DEFAULT 0,

    -- Meta
    `is_featured` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_active (`tenant_id`, `is_active`),
    INDEX idx_country_region (`country`, `region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dive Resorts
CREATE TABLE IF NOT EXISTS `dive_resorts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `destination_id` BIGINT UNSIGNED NOT NULL,
    `resort_name` VARCHAR(255) NOT NULL,
    `resort_type` ENUM('resort', 'hotel', 'dive_center', 'eco_lodge', 'villa') DEFAULT 'resort',

    -- Contact
    `address` TEXT NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(255) NULL,
    `website` VARCHAR(500) NULL,

    -- Amenities
    `amenities` JSON NULL COMMENT 'Pool, spa, restaurant, bar, wifi, etc.',
    `room_types` JSON NULL COMMENT 'Standard, deluxe, suite, villa',
    `total_rooms` BIGINT UNSIGNED NULL,

    -- Diving Facilities
    `dive_center_onsite` BOOLEAN DEFAULT TRUE,
    `equipment_rental_available` BOOLEAN DEFAULT TRUE,
    `nitrox_available` BOOLEAN DEFAULT FALSE,
    `rebreather_friendly` BOOLEAN DEFAULT FALSE,
    `boats_available` BIGINT UNSIGNED DEFAULT 1,
    `dives_per_day` BIGINT UNSIGNED DEFAULT 2,

    -- Certifications
    `padi_certified` BOOLEAN DEFAULT FALSE,
    `ssi_certified` BOOLEAN DEFAULT FALSE,
    `certifications_offered` JSON NULL,

    -- Pricing (base rates)
    `price_per_night_from` DECIMAL(10, 2) NULL,
    `dive_package_price_from` DECIMAL(10, 2) NULL,

    -- Ratings
    `star_rating` TINYINT NULL COMMENT '1-5 stars',
    `tripadvisor_rating` DECIMAL(3, 2) NULL,
    `average_rating` DECIMAL(3, 2) DEFAULT 0.00,
    `review_count` BIGINT UNSIGNED DEFAULT 0,

    -- Media
    `featured_image_url` VARCHAR(500) NULL,
    `gallery_images` JSON NULL,
    `virtual_tour_url` VARCHAR(500) NULL,

    -- Partner Info
    `is_partner` BOOLEAN DEFAULT FALSE,
    `commission_rate` DECIMAL(5, 2) NULL COMMENT 'Commission percentage',
    `partner_since` DATE NULL,

    -- Meta
    `is_featured` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destination_id`) REFERENCES `travel_destinations`(`id`) ON DELETE CASCADE,
    INDEX idx_destination (`destination_id`),
    INDEX idx_partner (`is_partner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Liveaboard Boats
CREATE TABLE IF NOT EXISTS `liveaboard_boats` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `boat_name` VARCHAR(255) NOT NULL,
    `operator_name` VARCHAR(255) NULL,

    -- Boat Specifications
    `boat_type` ENUM('motor_yacht', 'sailing_yacht', 'catamaran', 'traditional') DEFAULT 'motor_yacht',
    `length_feet` BIGINT UNSIGNED NULL,
    `beam_feet` BIGINT UNSIGNED NULL,
    `year_built` INT NULL,
    `year_refurbished` INT NULL,
    `passenger_capacity` BIGINT UNSIGNED NOT NULL,
    `crew_count` BIGINT UNSIGNED NULL,
    `cabin_count` BIGINT UNSIGNED NULL,

    -- Cabin Configuration
    `cabin_types` JSON NULL COMMENT 'Double, twin, single, suite',
    `bathrooms` BIGINT UNSIGNED NULL,

    -- Diving Facilities
    `dive_deck_type` VARCHAR(100) NULL,
    `nitrox_available` BOOLEAN DEFAULT FALSE,
    `rebreather_friendly` BOOLEAN DEFAULT FALSE,
    `underwater_camera_room` BOOLEAN DEFAULT FALSE,
    `dives_per_day` BIGINT UNSIGNED DEFAULT 4,
    `night_dives_available` BOOLEAN DEFAULT TRUE,

    -- Amenities
    `amenities` JSON NULL COMMENT 'AC, wifi, sun deck, hot tub, etc.',
    `entertainment` JSON NULL COMMENT 'TV, movies, music system, etc.',
    `special_features` JSON NULL,

    -- Safety
    `safety_equipment` JSON NULL COMMENT 'Life rafts, EPIRB, oxygen, first aid',
    `safety_certifications` JSON NULL,

    -- Regions Operated
    `operating_regions` JSON NULL COMMENT 'Where the boat operates',

    -- Pricing
    `price_per_person_from` DECIMAL(10, 2) NULL COMMENT 'Starting price per person per trip',
    `single_supplement_fee` DECIMAL(10, 2) NULL,

    -- Ratings
    `average_rating` DECIMAL(3, 2) DEFAULT 0.00,
    `review_count` BIGINT UNSIGNED DEFAULT 0,

    -- Media
    `featured_image_url` VARCHAR(500) NULL,
    `gallery_images` JSON NULL,
    `video_tour_url` VARCHAR(500) NULL,
    `deck_plan_url` VARCHAR(500) NULL,

    -- Partner Info
    `is_partner` BOOLEAN DEFAULT FALSE,
    `commission_rate` DECIMAL(5, 2) NULL,
    `booking_contact_email` VARCHAR(255) NULL,
    `booking_contact_phone` VARCHAR(20) NULL,

    -- Meta
    `is_featured` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_active (`tenant_id`, `is_active`),
    INDEX idx_capacity (`passenger_capacity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travel Packages (cruises, liveaboard trips, resort packages)
CREATE TABLE IF NOT EXISTS `travel_packages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `package_name` VARCHAR(255) NOT NULL,
    `package_type` ENUM('liveaboard', 'resort', 'day_trip', 'multi_destination', 'cruise', 'custom') NOT NULL,

    -- Associated Resources
    `destination_id` BIGINT UNSIGNED NULL,
    `resort_id` BIGINT UNSIGNED NULL,
    `liveaboard_id` BIGINT UNSIGNED NULL,

    -- Package Details
    `description` TEXT NULL,
    `itinerary` JSON NULL COMMENT 'Day-by-day itinerary',
    `included_items` JSON NULL COMMENT 'What\'s included',
    `excluded_items` JSON NULL COMMENT 'What\'s not included',

    -- Duration
    `duration_days` BIGINT UNSIGNED NOT NULL,
    `duration_nights` BIGINT UNSIGNED NOT NULL,

    -- Diving
    `total_dives_included` BIGINT UNSIGNED NULL,
    `certification_required` VARCHAR(100) NULL COMMENT 'Min certification required',

    -- Capacity
    `min_participants` BIGINT UNSIGNED DEFAULT 1,
    `max_participants` BIGINT UNSIGNED NULL,

    -- Pricing
    `price_per_person` DECIMAL(10, 2) NOT NULL,
    `single_supplement` DECIMAL(10, 2) NULL,
    `deposit_required` DECIMAL(10, 2) NULL,
    `deposit_percentage` DECIMAL(5, 2) NULL,
    `early_bird_discount` DECIMAL(5, 2) NULL COMMENT 'Discount percentage',
    `early_bird_deadline_days` INT NULL COMMENT 'Days before departure',

    -- Availability
    `available_dates` JSON NULL COMMENT 'Specific departure dates',
    `recurring_schedule` JSON NULL COMMENT 'Weekly, bi-weekly schedule',
    `booking_deadline_days` INT DEFAULT 30 COMMENT 'Days before departure',
    `cancellation_policy` TEXT NULL,

    -- Travel
    `departure_location` VARCHAR(255) NULL,
    `arrival_location` VARCHAR(255) NULL,
    `flights_included` BOOLEAN DEFAULT FALSE,
    `airport_transfers_included` BOOLEAN DEFAULT FALSE,

    -- Stats
    `total_bookings` BIGINT UNSIGNED DEFAULT 0,
    `average_rating` DECIMAL(3, 2) DEFAULT 0.00,
    `review_count` BIGINT UNSIGNED DEFAULT 0,

    -- Media
    `featured_image_url` VARCHAR(500) NULL,
    `gallery_images` JSON NULL,
    `brochure_url` VARCHAR(500) NULL,

    -- PADI Travel / Ocean First Travel Integration
    `padi_travel_id` VARCHAR(100) NULL COMMENT 'PADI Travel package ID',
    `ocean_first_id` VARCHAR(100) NULL COMMENT 'Ocean First Travel package ID',
    `external_booking_url` VARCHAR(500) NULL,

    -- Meta
    `is_featured` BOOLEAN DEFAULT FALSE,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destination_id`) REFERENCES `travel_destinations`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`resort_id`) REFERENCES `dive_resorts`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`liveaboard_id`) REFERENCES `liveaboard_boats`(`id`) ON DELETE SET NULL,
    INDEX idx_tenant_type (`tenant_id`, `package_type`),
    INDEX idx_destination (`destination_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travel Bookings
CREATE TABLE IF NOT EXISTS `travel_bookings` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `booking_reference` VARCHAR(50) NOT NULL UNIQUE,
    `package_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Trip Details
    `departure_date` DATE NOT NULL,
    `return_date` DATE NOT NULL,
    `number_of_travelers` BIGINT UNSIGNED NOT NULL DEFAULT 1,

    -- Traveler Information
    `primary_traveler` JSON NOT NULL COMMENT 'Lead traveler details',
    `additional_travelers` JSON NULL COMMENT 'Other travelers',

    -- Pricing
    `base_price` DECIMAL(10, 2) NOT NULL,
    `additional_charges` DECIMAL(10, 2) DEFAULT 0.00,
    `discounts` DECIMAL(10, 2) DEFAULT 0.00,
    `taxes_fees` DECIMAL(10, 2) DEFAULT 0.00,
    `total_price` DECIMAL(10, 2) NOT NULL,

    -- Payment
    `deposit_amount` DECIMAL(10, 2) DEFAULT 0.00,
    `deposit_paid` BOOLEAN DEFAULT FALSE,
    `deposit_paid_at` DATETIME NULL,
    `balance_due` DECIMAL(10, 2) NULL,
    `balance_due_date` DATE NULL,
    `total_paid` DECIMAL(10, 2) DEFAULT 0.00,
    `payment_status` ENUM('pending', 'deposit_paid', 'paid_in_full', 'refunded', 'cancelled') DEFAULT 'pending',

    -- Status
    `booking_status` ENUM('pending', 'confirmed', 'waitlist', 'cancelled', 'completed') DEFAULT 'pending',
    `confirmed_at` DATETIME NULL,
    `cancelled_at` DATETIME NULL,
    `cancellation_reason` TEXT NULL,

    -- Travel Documents
    `passports_collected` BOOLEAN DEFAULT FALSE,
    `travel_insurance` BOOLEAN DEFAULT FALSE,
    `insurance_provider` VARCHAR(255) NULL,
    `insurance_policy_number` VARCHAR(100) NULL,
    `medical_forms_complete` BOOLEAN DEFAULT FALSE,

    -- Special Requests
    `dietary_requirements` TEXT NULL,
    `special_requests` TEXT NULL,
    `accessibility_needs` TEXT NULL,

    -- Commission & Affiliate
    `booked_via_affiliate` BOOLEAN DEFAULT FALSE,
    `affiliate_id` BIGINT UNSIGNED NULL,
    `commission_amount` DECIMAL(10, 2) NULL,
    `commission_paid` BOOLEAN DEFAULT FALSE,

    -- External Integration
    `padi_travel_booking_id` VARCHAR(100) NULL,
    `ocean_first_booking_id` VARCHAR(100) NULL,
    `external_confirmation_number` VARCHAR(100) NULL,

    -- Notes
    `internal_notes` TEXT NULL,
    `customer_notes` TEXT NULL,

    -- Staff
    `booked_by` BIGINT UNSIGNED NULL COMMENT 'Staff who made the booking',
    `assigned_to` BIGINT UNSIGNED NULL COMMENT 'Travel coordinator',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`package_id`) REFERENCES `travel_packages`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT,
    INDEX idx_booking_reference (`booking_reference`),
    INDEX idx_customer (`customer_id`),
    INDEX idx_departure_date (`departure_date`),
    INDEX idx_status (`booking_status`, `payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travel Reviews
CREATE TABLE IF NOT EXISTS `travel_reviews` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `booking_id` BIGINT UNSIGNED NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Review Target
    `review_type` ENUM('package', 'destination', 'resort', 'liveaboard') NOT NULL,
    `package_id` BIGINT UNSIGNED NULL,
    `destination_id` BIGINT UNSIGNED NULL,
    `resort_id` BIGINT UNSIGNED NULL,
    `liveaboard_id` BIGINT UNSIGNED NULL,

    -- Rating
    `overall_rating` TINYINT NOT NULL COMMENT '1-5 stars',
    `dive_sites_rating` TINYINT NULL,
    `accommodation_rating` TINYINT NULL,
    `food_rating` TINYINT NULL,
    `staff_rating` TINYINT NULL,
    `value_rating` TINYINT NULL,

    -- Review Content
    `title` VARCHAR(255) NULL,
    `review_text` TEXT NOT NULL,
    `pros` TEXT NULL,
    `cons` TEXT NULL,

    -- Trip Details
    `travel_date` DATE NULL,
    `traveled_with` ENUM('solo', 'partner', 'family', 'friends', 'group') NULL,

    -- Verification
    `verified_booking` BOOLEAN DEFAULT FALSE,
    `verified_at` DATETIME NULL,

    -- Moderation
    `status` ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    `moderated_by` BIGINT UNSIGNED NULL,
    `moderated_at` DATETIME NULL,

    -- Engagement
    `helpful_count` BIGINT UNSIGNED DEFAULT 0,
    `unhelpful_count` BIGINT UNSIGNED DEFAULT 0,

    -- Response
    `management_response` TEXT NULL,
    `response_date` DATETIME NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`booking_id`) REFERENCES `travel_bookings`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`destination_id`) REFERENCES `travel_destinations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`resort_id`) REFERENCES `dive_resorts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`liveaboard_id`) REFERENCES `liveaboard_boats`(`id`) ON DELETE CASCADE,
    INDEX idx_review_type (`review_type`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travel Partner APIs (PADI Travel, Ocean First, etc.)
CREATE TABLE IF NOT EXISTS `travel_partner_apis` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `partner_name` ENUM('padi_travel', 'ocean_first', 'diviacademy', 'epic_diving', 'custom') NOT NULL,

    -- API Credentials
    `api_key` VARCHAR(255) NULL COMMENT 'Encrypted',
    `api_secret` VARCHAR(255) NULL COMMENT 'Encrypted',
    `api_endpoint` VARCHAR(500) NULL,
    `affiliate_id` VARCHAR(100) NULL,

    -- Configuration
    `is_active` BOOLEAN DEFAULT FALSE,
    `auto_sync_enabled` BOOLEAN DEFAULT FALSE,
    `sync_frequency` ENUM('hourly', 'daily', 'weekly', 'manual') DEFAULT 'daily',
    `last_sync_at` DATETIME NULL,
    `next_sync_at` DATETIME NULL,

    -- Commission
    `commission_rate` DECIMAL(5, 2) NULL,
    `commission_structure` JSON NULL,

    -- Stats
    `total_referrals` BIGINT UNSIGNED DEFAULT 0,
    `total_bookings` BIGINT UNSIGNED DEFAULT 0,
    `total_commission_earned` DECIMAL(10, 2) DEFAULT 0.00,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_partner (`tenant_id`, `partner_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pre-seeded Sample Data
-- =====================================================

-- Sample Destinations
INSERT INTO `travel_destinations` (
    `tenant_id`, `destination_name`, `country`, `region`, `description`,
    `skill_level_required`, `avg_water_temp_f`, `avg_visibility_ft`, `is_featured`
) VALUES
(1, 'Cozumel', 'Mexico', 'Caribbean', 'World-renowned drift diving with spectacular coral reefs and diverse marine life', 'all_levels', 78, 100, TRUE),
(1, 'Great Barrier Reef', 'Australia', 'Pacific', 'The world\'s largest coral reef system with incredible biodiversity', 'all_levels', 75, 80, TRUE),
(1, 'Maldives', 'Maldives', 'Indian Ocean', 'Crystal clear waters, vibrant reefs, and encounters with manta rays and whale sharks', 'intermediate', 82, 100, TRUE),
(1, 'Raja Ampat', 'Indonesia', 'Pacific', 'The epicenter of marine biodiversity with pristine reefs', 'advanced', 82, 60, TRUE),
(1, 'Red Sea - Egypt', 'Egypt', 'Middle East', 'Famous for wrecks, walls, and colorful coral reefs', 'all_levels', 75, 80, TRUE),
(1, 'Galapagos Islands', 'Ecuador', 'Pacific', 'Unique wildlife and thrilling encounters with hammerhead sharks', 'advanced', 70, 50, TRUE);

-- Sample Liveaboards
INSERT INTO `liveaboard_boats` (
    `tenant_id`, `boat_name`, `operator_name`, `boat_type`, `length_feet`, `passenger_capacity`,
    `crew_count`, `cabin_count`, `nitrox_available`, `dives_per_day`, `price_per_person_from`, `is_featured`
) VALUES
(1, 'MV Nautilus Explorer', 'Nautilus Dive Adventures', 'motor_yacht', 112, 26, 12, 13, TRUE, 4, 2995.00, TRUE),
(1, 'Emperor Elite', 'Emperor Divers', 'motor_yacht', 138, 26, 12, 13, TRUE, 4, 1795.00, TRUE),
(1, 'Aggressor IV', 'Aggressor Fleet', 'motor_yacht', 120, 22, 10, 11, TRUE, 5, 2395.00, TRUE);

-- Sample Resorts
INSERT INTO `dive_resorts` (
    `tenant_id`, `destination_id`, `resort_name`, `resort_type`, `dive_center_onsite`,
    `equipment_rental_available`, `nitrox_available`, `padi_certified`, `price_per_night_from`, `is_featured`
) VALUES
(1, 1, 'Cozumel Palace', 'resort', TRUE, TRUE, TRUE, TRUE, 250.00, TRUE),
(1, 3, 'Cocoa Island Resort', 'resort', TRUE, TRUE, TRUE, TRUE, 850.00, TRUE),
(1, 2, 'Lizard Island Resort', 'resort', TRUE, TRUE, FALSE, TRUE, 450.00, FALSE);

-- Sample Travel Packages
INSERT INTO `travel_packages` (
    `tenant_id`, `package_name`, `package_type`, `destination_id`, `liveaboard_id`,
    `duration_days`, `duration_nights`, `total_dives_included`, `price_per_person`,
    `deposit_required`, `min_participants`, `max_participants`, `is_featured`
) VALUES
(1, 'Maldives Liveaboard - 7 Nights', 'liveaboard', 3, 2, 8, 7, 18, 2495.00, 500.00, 6, 26, TRUE),
(1, 'Cozumel All-Inclusive Dive Package', 'resort', 1, NULL, 7, 6, 10, 1799.00, 350.00, 1, 20, TRUE),
(1, 'Raja Ampat Explorer - 10 Days', 'liveaboard', 4, NULL, 11, 10, 25, 3995.00, 800.00, 10, 18, TRUE),
(1, 'Red Sea Week - Emperor Fleet', 'liveaboard', 5, NULL, 8, 7, 20, 1895.00, 400.00, 8, 26, TRUE);

-- Sample PADI Travel API Integration
INSERT INTO `travel_partner_apis` (
    `tenant_id`, `partner_name`, `affiliate_id`, `is_active`, `auto_sync_enabled`, `commission_rate`
) VALUES
(1, 'padi_travel', 'AFFILIATE123', TRUE, FALSE, 10.00),
(1, 'ocean_first', 'OF456789', TRUE, FALSE, 8.00);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;