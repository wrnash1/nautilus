SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `dive_insurance_policies`;
DROP TABLE IF EXISTS `conservation_participants`;
DROP TABLE IF EXISTS `conservation_initiatives`;
DROP TABLE IF EXISTS `buddy_pairs`;
DROP TABLE IF EXISTS `club_communications`;
DROP TABLE IF EXISTS `club_event_registrations`;
DROP TABLE IF EXISTS `club_events`;
DROP TABLE IF EXISTS `club_memberships`;
DROP TABLE IF EXISTS `diving_clubs`;
DROP TABLE IF EXISTS `layaway_payment_schedules`;
DROP TABLE IF EXISTS `layaway_agreements`;
DROP TABLE IF EXISTS `layaway_plans`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `dive_insurance_policies`;
DROP TABLE IF EXISTS `conservation_participants`;
DROP TABLE IF EXISTS `conservation_initiatives`;
DROP TABLE IF EXISTS `buddy_pairs`;
DROP TABLE IF EXISTS `club_communications`;
DROP TABLE IF EXISTS `club_event_registrations`;
DROP TABLE IF EXISTS `club_events`;
DROP TABLE IF EXISTS `club_memberships`;
DROP TABLE IF EXISTS `diving_clubs`;
DROP TABLE IF EXISTS `layaway_payment_schedules`;
DROP TABLE IF EXISTS `layaway_agreements`;
DROP TABLE IF EXISTS `layaway_plans`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `dive_insurance_policies`;
DROP TABLE IF EXISTS `conservation_participants`;
DROP TABLE IF EXISTS `conservation_initiatives`;
DROP TABLE IF EXISTS `buddy_pairs`;
DROP TABLE IF EXISTS `club_communications`;
DROP TABLE IF EXISTS `club_event_registrations`;
DROP TABLE IF EXISTS `club_events`;
DROP TABLE IF EXISTS `club_memberships`;
DROP TABLE IF EXISTS `diving_clubs`;
DROP TABLE IF EXISTS `layaway_payment_schedules`;
DROP TABLE IF EXISTS `layaway_agreements`;
DROP TABLE IF EXISTS `layaway_plans`;

-- =============================================
-- Migration 098: Layaway System & Diving Club Management
-- =============================================
-- This migration adds layaway/payment plans for equipment purchases
-- and comprehensive diving club management features

-- =============================================
-- Layaway & Payment Plans
-- =============================================

-- Layaway plans for equipment purchases
CREATE TABLE IF NOT EXISTS `layaway_plans` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `plan_name` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,

    -- Plan Configuration
    `min_purchase_amount` DECIMAL(10, 2) DEFAULT 100.00,
    `max_purchase_amount` DECIMAL(10, 2) NULL,
    `down_payment_percentage` DECIMAL(5, 2) NOT NULL DEFAULT 20.00,
    `down_payment_minimum` DECIMAL(10, 2) DEFAULT 50.00,

    -- Terms
    `plan_duration_days` INT NOT NULL DEFAULT 90 COMMENT '30, 60, 90, 120, 180 days',
    `number_of_payments` INT NOT NULL DEFAULT 3,
    `payment_frequency` ENUM('weekly', 'biweekly', 'monthly', 'custom') DEFAULT 'monthly',

    -- Fees
    `layaway_fee` DECIMAL(10, 2) DEFAULT 0.00,
    `layaway_fee_type` ENUM('flat', 'percentage') DEFAULT 'flat',
    `late_fee_amount` DECIMAL(10, 2) DEFAULT 10.00,
    `cancellation_fee` DECIMAL(10, 2) DEFAULT 25.00,
    `restocking_fee_percentage` DECIMAL(5, 2) DEFAULT 15.00,

    -- Restrictions
    `allowed_product_categories` JSON NULL COMMENT 'Restrict to certain categories',
    `excluded_products` JSON NULL,
    `requires_approval` BOOLEAN DEFAULT FALSE,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_active (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway agreements (individual customer layaway contracts)
CREATE TABLE IF NOT EXISTS `layaway_agreements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `agreement_number` VARCHAR(50) UNIQUE NOT NULL,
    `layaway_plan_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Products on layaway
    `items` JSON NOT NULL COMMENT 'Array of product IDs, quantities, prices',
    `total_amount` DECIMAL(10, 2) NOT NULL,
    `down_payment` DECIMAL(10, 2) NOT NULL,
    `layaway_fee` DECIMAL(10, 2) DEFAULT 0.00,
    `total_due` DECIMAL(10, 2) NOT NULL,

    -- Payment Schedule
    `number_of_payments` INT NOT NULL,
    `payment_amount` DECIMAL(10, 2) NOT NULL,
    `payment_frequency` VARCHAR(20) NOT NULL,
    `first_payment_date` DATE NOT NULL,
    `final_payment_date` DATE NOT NULL,

    -- Current Status
    `amount_paid` DECIMAL(10, 2) DEFAULT 0.00,
    `balance_remaining` DECIMAL(10, 2) NOT NULL,
    `payments_made` INT DEFAULT 0,
    `payments_missed` INT DEFAULT 0,

    -- Agreement Status
    `status` ENUM('pending', 'active', 'completed', 'defaulted', 'cancelled') DEFAULT 'pending',
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,

    -- Products held
    `products_reserved` BOOLEAN DEFAULT TRUE COMMENT 'Products held in inventory',
    `reservation_location_id` BIGINT UNSIGNED NULL,

    -- Completion/Cancellation
    `completed_at` TIMESTAMP NULL,
    `cancelled_at` TIMESTAMP NULL,
    `cancellation_reason` TEXT NULL,
    `refund_amount` DECIMAL(10, 2) NULL,

    -- Staff
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_agreement_number (`agreement_number`),
    INDEX idx_tenant (`tenant_id`),
    INDEX idx_customer (`customer_id`),
    INDEX idx_status (`status`),
    INDEX idx_plan (`layaway_plan_id`),
    FOREIGN KEY (`layaway_plan_id`) REFERENCES `layaway_plans`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Layaway payment schedule
CREATE TABLE IF NOT EXISTS `layaway_payment_schedules` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `agreement_id` BIGINT UNSIGNED NOT NULL,

    `payment_number` INT NOT NULL,
    `due_date` DATE NOT NULL,
    `amount_due` DECIMAL(10, 2) NOT NULL,

    -- Payment Status
    `amount_paid` DECIMAL(10, 2) DEFAULT 0.00,
    `payment_status` ENUM('pending', 'paid', 'partial', 'late', 'missed') DEFAULT 'pending',
    `paid_date` DATE NULL,
    `payment_id` BIGINT UNSIGNED NULL COMMENT 'FK to payments table',

    -- Late Fees
    `late_fee_assessed` DECIMAL(10, 2) DEFAULT 0.00,
    `grace_period_days` INT DEFAULT 7,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_agreement (`agreement_id`),
    INDEX idx_due_date (`due_date`),
    INDEX idx_status (`payment_status`),
    FOREIGN KEY (`agreement_id`) REFERENCES `layaway_agreements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Scuba Diving Club Management
-- =============================================

-- Diving clubs/groups
CREATE TABLE IF NOT EXISTS `diving_clubs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `club_name` VARCHAR(200) NOT NULL,
    `club_code` VARCHAR(50) UNIQUE NOT NULL,
    `description` TEXT NULL,

    -- Club Details
    `club_type` ENUM('social', 'technical', 'conservation', 'photography', 'competitive', 'general') DEFAULT 'general',
    `founded_date` DATE NULL,
    `meeting_schedule` VARCHAR(200) NULL COMMENT 'e.g., "First Tuesday of each month"',
    `meeting_location` VARCHAR(200) NULL,

    -- Membership
    `membership_type` ENUM('open', 'invitation_only', 'application_required') DEFAULT 'open',
    `min_certification_level` VARCHAR(50) NULL COMMENT 'Open Water, Advanced, etc.',
    `min_dives_required` INT DEFAULT 0,
    `annual_dues` DECIMAL(10, 2) DEFAULT 0.00,

    -- Limits
    `max_members` INT NULL,
    `current_member_count` INT DEFAULT 0,
    `waitlist_enabled` BOOLEAN DEFAULT FALSE,

    -- Benefits
    `member_benefits` JSON NULL COMMENT 'Discounts, privileges, etc.',
    `discount_percentage` DECIMAL(5, 2) NULL COMMENT 'Member discount on purchases',

    -- Leadership
    `president_id` BIGINT UNSIGNED NULL,
    `vice_president_id` BIGINT UNSIGNED NULL,
    `treasurer_id` BIGINT UNSIGNED NULL,
    `secretary_id` BIGINT UNSIGNED NULL,

    -- Contact
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `website` VARCHAR(500) NULL,
    `social_media` JSON NULL,

    -- Settings
    `logo_url` VARCHAR(500) NULL,
    `banner_image_url` VARCHAR(500) NULL,
    `club_color` VARCHAR(7) NULL COMMENT 'Hex color code',

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_club_code (`club_code`),
    INDEX idx_tenant (`tenant_id`),
    INDEX idx_type (`club_type`),
    INDEX idx_active (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Club memberships
CREATE TABLE IF NOT EXISTS `club_memberships` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `club_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Membership Details
    `member_number` VARCHAR(50) NULL,
    `membership_status` ENUM('pending', 'active', 'suspended', 'expired', 'cancelled') DEFAULT 'pending',
    `member_role` ENUM('member', 'officer', 'board_member', 'president', 'vice_president', 'treasurer', 'secretary') DEFAULT 'member',

    -- Dates
    `join_date` DATE NOT NULL,
    `membership_start_date` DATE NOT NULL,
    `membership_end_date` DATE NULL,
    `last_dues_paid_date` DATE NULL,
    `next_dues_date` DATE NULL,

    -- Payments
    `annual_dues` DECIMAL(10, 2) DEFAULT 0.00,
    `dues_paid` BOOLEAN DEFAULT FALSE,
    `lifetime_member` BOOLEAN DEFAULT FALSE,

    -- Participation
    `total_club_dives` INT DEFAULT 0,
    `total_club_events` INT DEFAULT 0,
    `volunteer_hours` INT DEFAULT 0,

    -- Application (if required)
    `application_date` DATE NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` TIMESTAMP NULL,
    `application_notes` TEXT NULL,

    -- Notes
    `membership_notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_club_member (`club_id`, `customer_id`),
    INDEX idx_tenant (`tenant_id`),
    INDEX idx_club (`club_id`),
    INDEX idx_customer (`customer_id`),
    INDEX idx_status (`membership_status`),
    FOREIGN KEY (`club_id`) REFERENCES `diving_clubs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Club events and activities
CREATE TABLE IF NOT EXISTS `club_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `club_id` BIGINT UNSIGNED NOT NULL,
    `event_name` VARCHAR(200) NOT NULL,
    `event_type` ENUM('dive_trip', 'meeting', 'social', 'training', 'competition', 'conservation', 'fundraiser', 'other') NOT NULL,

    -- Event Details
    `description` TEXT NULL,
    `event_date` DATE NOT NULL,
    `start_time` TIME NULL,
    `end_time` TIME NULL,
    `location` VARCHAR(500) NULL,

    -- Capacity
    `max_participants` INT NULL,
    `current_participants` INT DEFAULT 0,
    `members_only` BOOLEAN DEFAULT FALSE,
    `guests_allowed` BOOLEAN DEFAULT TRUE,

    -- Costs
    `member_cost` DECIMAL(10, 2) DEFAULT 0.00,
    `non_member_cost` DECIMAL(10, 2) NULL,
    `guest_cost` DECIMAL(10, 2) NULL,

    -- Registration
    `registration_required` BOOLEAN DEFAULT TRUE,
    `registration_deadline` DATE NULL,
    `registration_opens` DATE NULL,

    -- Organizer
    `organizer_id` BIGINT UNSIGNED NULL,
    `contact_email` VARCHAR(255) NULL,
    `contact_phone` VARCHAR(20) NULL,

    -- Status
    `status` ENUM('scheduled', 'open_registration', 'closed_registration', 'completed', 'cancelled') DEFAULT 'scheduled',
    `cancelled_reason` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_club (`club_id`),
    INDEX idx_event_date (`event_date`),
    INDEX idx_status (`status`),
    FOREIGN KEY (`club_id`) REFERENCES `diving_clubs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event registrations
CREATE TABLE IF NOT EXISTS `club_event_registrations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `event_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Registration Details
    `registration_type` ENUM('member', 'non_member', 'guest') NOT NULL,
    `guests_count` INT DEFAULT 0,
    `guest_names` JSON NULL,

    -- Payment
    `amount_due` DECIMAL(10, 2) NOT NULL,
    `amount_paid` DECIMAL(10, 2) DEFAULT 0.00,
    `payment_status` ENUM('pending', 'paid', 'refunded', 'waived') DEFAULT 'pending',
    `payment_id` BIGINT UNSIGNED NULL,

    -- Status
    `registration_status` ENUM('pending', 'confirmed', 'waitlist', 'cancelled', 'attended') DEFAULT 'pending',
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `cancelled_at` TIMESTAMP NULL,

    -- Special Requirements
    `dietary_restrictions` TEXT NULL,
    `special_needs` TEXT NULL,
    `emergency_contact` VARCHAR(200) NULL,
    `notes` TEXT NULL,

    INDEX idx_event (`event_id`),
    INDEX idx_customer (`customer_id`),
    INDEX idx_status (`registration_status`),
    FOREIGN KEY (`event_id`) REFERENCES `club_events`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Club communications (newsletter, announcements)
CREATE TABLE IF NOT EXISTS `club_communications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `club_id` BIGINT UNSIGNED NOT NULL,

    `communication_type` ENUM('announcement', 'newsletter', 'email_blast', 'reminder', 'update') NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,

    -- Targeting
    `target_audience` ENUM('all_members', 'active_only', 'officers_only', 'specific_members') DEFAULT 'all_members',
    `recipient_ids` JSON NULL COMMENT 'Specific member IDs',

    -- Delivery
    `send_via` JSON NOT NULL COMMENT 'email, sms, app_notification',
    `scheduled_send_at` TIMESTAMP NULL,
    `sent_at` TIMESTAMP NULL,
    `status` ENUM('draft', 'scheduled', 'sent', 'cancelled') DEFAULT 'draft',

    -- Tracking
    `total_recipients` INT DEFAULT 0,
    `emails_sent` INT DEFAULT 0,
    `emails_opened` INT DEFAULT 0,
    `links_clicked` INT DEFAULT 0,

    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_club (`club_id`),
    INDEX idx_status (`status`),
    FOREIGN KEY (`club_id`) REFERENCES `diving_clubs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Additional Features
-- =============================================

-- Buddy system for dive safety
CREATE TABLE IF NOT EXISTS `buddy_pairs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,

    -- Divers
    `diver1_id` BIGINT UNSIGNED NOT NULL,
    `diver2_id` BIGINT UNSIGNED NOT NULL,

    -- Relationship
    `relationship_type` ENUM('permanent', 'trip_specific', 'single_dive', 'preferred') DEFAULT 'trip_specific',
    `paired_for_trip_id` BIGINT UNSIGNED NULL COMMENT 'Specific trip/booking',
    `paired_for_date` DATE NULL,

    -- Experience Matching
    `experience_level_match` ENUM('excellent', 'good', 'acceptable', 'mismatched') NULL,
    `compatibility_score` INT NULL COMMENT '1-10',

    -- Status
    `status` ENUM('active', 'inactive', 'completed', 'dissolved') DEFAULT 'active',
    `paired_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `dissolved_at` TIMESTAMP NULL,
    `dissolved_reason` TEXT NULL,

    -- Performance
    `dives_together` INT DEFAULT 0,
    `last_dive_date` DATE NULL,

    `notes` TEXT NULL,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_diver1 (`diver1_id`),
    INDEX idx_diver2 (`diver2_id`),
    INDEX idx_status (`status`),
    FOREIGN KEY (`diver1_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`diver2_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Marine conservation initiatives
CREATE TABLE IF NOT EXISTS `conservation_initiatives` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,

    `initiative_name` VARCHAR(200) NOT NULL,
    `initiative_type` ENUM('cleanup', 'reef_restoration', 'species_monitoring', 'education', 'research', 'advocacy', 'other') NOT NULL,
    `description` TEXT NULL,

    -- Partner Organizations
    `partner_organizations` JSON NULL COMMENT 'NGOs, universities, etc.',
    `certification_program` VARCHAR(200) NULL COMMENT 'e.g., Green Fins, Blue Star',

    -- Timeline
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `is_ongoing` BOOLEAN DEFAULT TRUE,

    -- Metrics
    `participants_count` INT DEFAULT 0,
    `volunteer_hours` INT DEFAULT 0,
    `funds_raised` DECIMAL(10, 2) DEFAULT 0.00,
    `impact_metrics` JSON NULL COMMENT 'Trash collected, corals planted, etc.',

    -- Engagement
    `next_event_date` DATE NULL,
    `meeting_frequency` VARCHAR(100) NULL,

    `coordinator_id` BIGINT UNSIGNED NULL,
    `status` ENUM('planning', 'active', 'completed', 'on_hold', 'cancelled') DEFAULT 'planning',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_type (`initiative_type`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Conservation participation tracking
CREATE TABLE IF NOT EXISTS `conservation_participants` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `initiative_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Participation
    `participation_level` ENUM('volunteer', 'coordinator', 'leader', 'donor', 'supporter') DEFAULT 'volunteer',
    `join_date` DATE NOT NULL,
    `volunteer_hours` INT DEFAULT 0,
    `donations_total` DECIMAL(10, 2) DEFAULT 0.00,

    -- Recognition
    `achievements` JSON NULL COMMENT 'Badges, milestones',
    `certificates_earned` JSON NULL,

    `is_active` BOOLEAN DEFAULT TRUE,
    `last_activity_date` DATE NULL,

    INDEX idx_initiative (`initiative_id`),
    INDEX idx_customer (`customer_id`),
    FOREIGN KEY (`initiative_id`) REFERENCES `conservation_initiatives`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dive insurance tracking
CREATE TABLE IF NOT EXISTS `dive_insurance_policies` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Policy Details
    `insurance_provider` VARCHAR(200) NOT NULL COMMENT 'DAN, Divers Alert Network, etc.',
    `policy_number` VARCHAR(100) NOT NULL,
    `policy_type` ENUM('individual', 'family', 'group', 'professional') DEFAULT 'individual',

    -- Coverage
    `coverage_level` VARCHAR(100) NULL COMMENT 'Basic, Preferred, Master, etc.',
    `coverage_amount` DECIMAL(12, 2) NULL,
    `deductible` DECIMAL(10, 2) NULL,
    `covers_hyperbaric` BOOLEAN DEFAULT TRUE,
    `covers_evacuation` BOOLEAN DEFAULT TRUE,
    `covers_recompression` BOOLEAN DEFAULT TRUE,
    `covers_medical` BOOLEAN DEFAULT TRUE,
    `covers_equipment` BOOLEAN DEFAULT FALSE,

    -- Dates
    `effective_date` DATE NOT NULL,
    `expiration_date` DATE NOT NULL,
    `reminder_sent` BOOLEAN DEFAULT FALSE,

    -- Emergency Contact
    `emergency_phone` VARCHAR(20) NULL,
    `claims_phone` VARCHAR(20) NULL,
    `policy_url` VARCHAR(500) NULL,

    -- Document Storage
    `policy_document_url` VARCHAR(500) NULL,
    `id_card_url` VARCHAR(500) NULL,

    -- Verification
    `verified` BOOLEAN DEFAULT FALSE,
    `verified_by` BIGINT UNSIGNED NULL,
    `verified_at` TIMESTAMP NULL,

    -- Status
    `status` ENUM('active', 'expired', 'cancelled', 'pending_renewal') DEFAULT 'active',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_customer (`customer_id`),
    INDEX idx_expiration (`expiration_date`),
    INDEX idx_status (`status`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Pre-seed Sample Data
-- =============================================

-- Sample Layaway Plan
INSERT INTO `layaway_plans` (
    `tenant_id`, `plan_name`, `description`,
    `min_purchase_amount`, `down_payment_percentage`, `plan_duration_days`,
    `number_of_payments`, `payment_frequency`, `layaway_fee`
) VALUES
(1, '90-Day Equipment Layaway', 'Purchase scuba equipment with 20% down, pay over 90 days',
    100.00, 20.00, 90, 3, 'monthly', 10.00),
(1, '60-Day Quick Layaway', 'Faster payment plan with bi-weekly payments',
    100.00, 25.00, 60, 4, 'biweekly', 5.00);

-- Sample Diving Clubs
INSERT INTO `diving_clubs` (
    `tenant_id`, `club_name`, `club_code`, `description`, `club_type`,
    `meeting_schedule`, `annual_dues`, `discount_percentage`
) VALUES
(1, 'Ocean Explorers Dive Club', 'OEDC',
    'A social diving club for recreational divers of all levels. Monthly meetings and quarterly dive trips.',
    'social', 'First Saturday of each month at 2pm', 50.00, 10.00),
(1, 'Technical Diving Society', 'TDS',
    'Advanced technical diving group focused on wreck and cave diving.',
    'technical', 'Last Wednesday of each month at 7pm', 100.00, 15.00),
(1, 'Underwater Photography Club', 'UPC',
    'For divers passionate about capturing the underwater world.',
    'photography', 'Third Sunday monthly at 3pm', 60.00, 10.00),
(1, 'Reef Guardians', 'RG',
    'Marine conservation and reef monitoring club.',
    'conservation', 'Second Tuesday monthly at 6pm', 40.00, 5.00);

-- Sample Club Event
INSERT INTO `club_events` (
    `tenant_id`, `club_id`, `event_name`, `event_type`,
    `description`, `event_date`, `start_time`,
    `max_participants`, `member_cost`, `non_member_cost`
) VALUES
(1, 1, 'Monthly Social Dive - Local Reef', 'dive_trip',
    'Relaxed dive at our favorite local reef site. All levels welcome.',
    DATE_ADD(CURDATE(), INTERVAL 15 DAY), '08:00:00',
    20, 45.00, 65.00),
(1, 4, 'Beach Cleanup & Conservation Day', 'conservation',
    'Join us for a morning beach cleanup followed by a conservation awareness dive.',
    DATE_ADD(CURDATE(), INTERVAL 30 DAY), '09:00:00',
    30, 0.00, 0.00);

-- Sample Conservation Initiative
INSERT INTO `conservation_initiatives` (
    `tenant_id`, `initiative_name`, `initiative_type`, `description`,
    `start_date`, `is_ongoing`
) VALUES
(1, 'Coral Restoration Project 2025', 'reef_restoration',
    'Partnering with local marine biologists to plant and monitor coral fragments on damaged reef areas.',
    '2025-01-01', TRUE),
(1, 'Monthly Beach Cleanups', 'cleanup',
    'First Saturday of every month we clean our local dive beaches and document marine debris.',
    '2024-06-01', TRUE),
(1, 'Lionfish Removal Program', 'species_monitoring',
    'Invasive lionfish removal to protect native reef fish populations.',
    '2024-08-01', TRUE);

-- =============================================
-- Migration Complete
-- =============================================
-- This migration adds:
-- - Layaway/payment plan system for equipment purchases
-- - Comprehensive diving club management
-- - Buddy pairing system for dive safety
-- - Marine conservation tracking
-- - Dive insurance management


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;