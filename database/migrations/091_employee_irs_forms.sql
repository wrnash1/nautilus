-- Migration: Employee Documents & IRS Forms
-- Secure storage for employee tax forms and HR documents
-- Date: 2026-01-04

-- Employee Documents table
CREATE TABLE IF NOT EXISTS `employee_documents` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `document_type` ENUM('w4', 'i9', 'w2', 'w9', '1099', 'state_w4', 'direct_deposit', 'other') NOT NULL,
    `tax_year` YEAR NULL,
    `file_path` VARCHAR(500) NULL,
    `file_name` VARCHAR(255) NULL,
    `file_size` INT UNSIGNED NULL,
    `mime_type` VARCHAR(100) NULL,
    `encrypted_ssn` VARBINARY(256) NULL COMMENT 'AES-256 encrypted SSN',
    `ssn_last_four` CHAR(4) NULL COMMENT 'Last 4 digits for display',
    `status` ENUM('pending', 'submitted', 'verified', 'rejected', 'expired') DEFAULT 'pending',
    `submitted_at` DATETIME NULL,
    `verified_by` BIGINT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `expires_at` DATE NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_type` (`user_id`, `document_type`),
    INDEX `idx_tax_year` (`tax_year`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employee tax information (for W-2 generation)
CREATE TABLE IF NOT EXISTS `employee_tax_info` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `tax_year` YEAR NOT NULL,
    `filing_status` ENUM('single', 'married_filing_jointly', 'married_filing_separately', 'head_of_household') NULL,
    `federal_allowances` TINYINT UNSIGNED DEFAULT 0,
    `state_allowances` TINYINT UNSIGNED DEFAULT 0,
    `additional_withholding` DECIMAL(10,2) DEFAULT 0,
    `exempt_from_withholding` BOOLEAN DEFAULT FALSE,
    `is_contractor` BOOLEAN DEFAULT FALSE COMMENT 'True for 1099, False for W-2',
    `hourly_rate` DECIMAL(10,2) NULL,
    `salary_annual` DECIMAL(12,2) NULL,
    `ytd_wages` DECIMAL(12,2) DEFAULT 0,
    `ytd_federal_tax` DECIMAL(10,2) DEFAULT 0,
    `ytd_state_tax` DECIMAL(10,2) DEFAULT 0,
    `ytd_social_security` DECIMAL(10,2) DEFAULT 0,
    `ytd_medicare` DECIMAL(10,2) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_user_year` (`user_id`, `tax_year`),
    INDEX `idx_tenant_year` (`tenant_id`, `tax_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Direct deposit information
CREATE TABLE IF NOT EXISTS `employee_direct_deposit` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `account_type` ENUM('checking', 'savings') NOT NULL,
    `bank_name` VARCHAR(255) NOT NULL,
    `routing_number_encrypted` VARBINARY(256) NOT NULL,
    `account_number_encrypted` VARBINARY(256) NOT NULL,
    `account_last_four` CHAR(4) NOT NULL,
    `is_primary` BOOLEAN DEFAULT TRUE,
    `percentage` DECIMAL(5,2) DEFAULT 100.00 COMMENT 'Percentage of pay to deposit',
    `verified` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payroll records
CREATE TABLE IF NOT EXISTS `payroll_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `pay_period_start` DATE NOT NULL,
    `pay_period_end` DATE NOT NULL,
    `pay_date` DATE NOT NULL,
    `regular_hours` DECIMAL(6,2) DEFAULT 0,
    `overtime_hours` DECIMAL(6,2) DEFAULT 0,
    `tips` DECIMAL(10,2) DEFAULT 0,
    `commission` DECIMAL(10,2) DEFAULT 0,
    `bonus` DECIMAL(10,2) DEFAULT 0,
    `gross_pay` DECIMAL(12,2) NOT NULL,
    `federal_tax` DECIMAL(10,2) DEFAULT 0,
    `state_tax` DECIMAL(10,2) DEFAULT 0,
    `social_security` DECIMAL(10,2) DEFAULT 0,
    `medicare` DECIMAL(10,2) DEFAULT 0,
    `other_deductions` DECIMAL(10,2) DEFAULT 0,
    `net_pay` DECIMAL(12,2) NOT NULL,
    `status` ENUM('draft', 'approved', 'paid', 'void') DEFAULT 'draft',
    `check_number` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_date` (`user_id`, `pay_date`),
    INDEX `idx_pay_period` (`pay_period_start`, `pay_period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
