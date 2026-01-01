SET FOREIGN_KEY_CHECKS=0;

-- ============================================================================
-- MIGRATION 115: Ensure Feature Tables
-- Purpose: Create missing tables for Gift Cards, Safety, Incidents, etc.
-- ============================================================================

-- Gift Cards table
CREATE TABLE IF NOT EXISTS `gift_cards` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED DEFAULT 1,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `initial_balance` DECIMAL(10,2) NOT NULL,
    `current_balance` DECIMAL(10,2) NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `purchaser_id` BIGINT UNSIGNED NULL,
    `status` ENUM('active', 'inactive', 'depleted', 'expired') DEFAULT 'active',
    `expires_at` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gift_cards_code (code),
    INDEX idx_gift_cards_tenant (tenant_id),
    INDEX idx_gift_cards_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gift Card Transactions
CREATE TABLE IF NOT EXISTS `gift_card_transactions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gift_card_id` BIGINT UNSIGNED NOT NULL,
    `transaction_type` ENUM('purchase', 'redemption', 'refund', 'adjustment') NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `balance_after` DECIMAL(10,2) NOT NULL,
    `reference_id` BIGINT UNSIGNED NULL,
    `reference_type` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gct_gift_card (gift_card_id),
    INDEX idx_gct_type (transaction_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pre-Dive Safety Checks
CREATE TABLE IF NOT EXISTS `pre_dive_safety_checks` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED DEFAULT 1,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `instructor_id` BIGINT UNSIGNED NULL,
    `trip_id` BIGINT UNSIGNED NULL,
    `check_date` DATE NOT NULL,
    `equipment_check` JSON NULL,
    `buddy_check_completed` TINYINT(1) DEFAULT 0,
    `air_pressure_psi` INT NULL,
    `overall_status` ENUM('pass', 'fail', 'conditional') DEFAULT 'pass',
    `notes` TEXT NULL,
    `signed_at` TIMESTAMP NULL,
    `signature_data` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pdsc_customer (customer_id),
    INDEX idx_pdsc_date (check_date),
    INDEX idx_pdsc_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Incident Reports
CREATE TABLE IF NOT EXISTS `incident_reports` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED DEFAULT 1,
    `incident_number` VARCHAR(50) NOT NULL UNIQUE,
    `incident_date` DATETIME NOT NULL,
    `incident_type` ENUM('injury', 'equipment_failure', 'near_miss', 'environmental', 'other') NOT NULL,
    `severity` ENUM('minor', 'moderate', 'major', 'critical') DEFAULT 'minor',
    `location` VARCHAR(255) NULL,
    `dive_site_id` BIGINT UNSIGNED NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `staff_id` BIGINT UNSIGNED NULL,
    `description` TEXT NOT NULL,
    `immediate_actions` TEXT NULL,
    `root_cause` TEXT NULL,
    `corrective_actions` TEXT NULL,
    `witnesses` JSON NULL,
    `equipment_involved` JSON NULL,
    `weather_conditions` VARCHAR(255) NULL,
    `dive_conditions` JSON NULL,
    `medical_attention_required` TINYINT(1) DEFAULT 0,
    `medical_details` TEXT NULL,
    `reported_to_authorities` TINYINT(1) DEFAULT 0,
    `authority_report_details` TEXT NULL,
    `status` ENUM('open', 'investigating', 'resolved', 'closed') DEFAULT 'open',
    `resolution_date` DATE NULL,
    `resolution_notes` TEXT NULL,
    `reported_by` BIGINT UNSIGNED NULL,
    `reviewed_by` BIGINT UNSIGNED NULL,
    `reviewed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ir_number (incident_number),
    INDEX idx_ir_date (incident_date),
    INDEX idx_ir_type (incident_type),
    INDEX idx_ir_status (status),
    INDEX idx_ir_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Equipment Maintenance Log
CREATE TABLE IF NOT EXISTS `equipment_maintenance_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED DEFAULT 1,
    `equipment_id` BIGINT UNSIGNED NOT NULL,
    `maintenance_type` ENUM('routine', 'repair', 'inspection', 'calibration', 'replacement') NOT NULL,
    `description` TEXT NOT NULL,
    `parts_used` JSON NULL,
    `cost` DECIMAL(10,2) DEFAULT 0,
    `performed_by` BIGINT UNSIGNED NULL,
    `performed_at` DATETIME NOT NULL,
    `next_maintenance_date` DATE NULL,
    `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'completed',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eml_equipment (equipment_id),
    INDEX idx_eml_date (performed_at),
    INDEX idx_eml_type (maintenance_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Waivers
CREATE TABLE IF NOT EXISTS `customer_waivers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED DEFAULT 1,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `waiver_template_id` BIGINT UNSIGNED NULL,
    `waiver_type` VARCHAR(100) NOT NULL,
    `version` VARCHAR(20) DEFAULT '1.0',
    `signed_at` TIMESTAMP NULL,
    `signature_data` MEDIUMTEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `expires_at` DATE NULL,
    `status` ENUM('pending', 'signed', 'expired', 'revoked') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cw_customer (customer_id),
    INDEX idx_cw_type (waiver_type),
    INDEX idx_cw_status (status),
    INDEX idx_cw_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Migration 115 Complete' AS status;

SET FOREIGN_KEY_CHECKS=1;
