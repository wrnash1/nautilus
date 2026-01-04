-- Migration: Add weekend rate and waiver/credit card fields to rental system
-- Date: 2026-01-04

-- Add weekend rate to rental_equipment table
ALTER TABLE `rental_equipment` 
ADD COLUMN `weekend_rate` DECIMAL(10,2) NULL AFTER `weekly_rate`;

-- Add waiver and credit card tracking to rental_reservations
ALTER TABLE `rental_reservations`
ADD COLUMN `waiver_signed` BOOLEAN DEFAULT FALSE AFTER `notes`,
ADD COLUMN `waiver_signed_at` DATETIME NULL AFTER `waiver_signed`,
ADD COLUMN `waiver_document_id` BIGINT UNSIGNED NULL AFTER `waiver_signed_at`,
ADD COLUMN `card_on_file` BOOLEAN DEFAULT FALSE AFTER `waiver_document_id`,
ADD COLUMN `card_token` VARCHAR(255) NULL AFTER `card_on_file`,
ADD COLUMN `late_fee` DECIMAL(10,2) NULL AFTER `card_token`;

-- Create waivers table if not exists
CREATE TABLE IF NOT EXISTS `waivers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('rental', 'course', 'trip', 'general') NOT NULL DEFAULT 'general',
    `content` LONGTEXT NOT NULL,
    `version` VARCHAR(20) NOT NULL DEFAULT '1.0',
    `is_active` BOOLEAN DEFAULT TRUE,
    `requires_signature` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer signed waivers table
CREATE TABLE IF NOT EXISTS `customer_waivers` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `waiver_id` BIGINT UNSIGNED NOT NULL,
    `signature_data` LONGTEXT COMMENT 'Base64 encoded signature image',
    `ip_address` VARCHAR(45),
    `signed_at` DATETIME NOT NULL,
    `expires_at` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`waiver_id`) REFERENCES `waivers`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer_waiver` (`customer_id`, `waiver_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer payment methods table (credit cards on file)
CREATE TABLE IF NOT EXISTS `customer_payment_methods` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BIGINT UNSIGNED NOT NULL,
    `type` ENUM('credit_card', 'debit_card', 'bank_account') NOT NULL DEFAULT 'credit_card',
    `card_brand` VARCHAR(50) NULL COMMENT 'Visa, MasterCard, Amex, etc.',
    `last_four` VARCHAR(4) NOT NULL,
    `exp_month` TINYINT UNSIGNED NULL,
    `exp_year` SMALLINT UNSIGNED NULL,
    `token` VARCHAR(255) NOT NULL COMMENT 'Payment processor token',
    `is_default` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default rental waiver
INSERT INTO `waivers` (`name`, `type`, `content`, `version`) VALUES 
('Equipment Rental Agreement', 'rental', '
<h2>EQUIPMENT RENTAL AGREEMENT AND LIABILITY WAIVER</h2>

<p>I, the undersigned, hereby agree to the following terms and conditions for the rental of diving equipment:</p>

<h3>1. Equipment Condition</h3>
<p>I acknowledge that I have inspected the equipment and found it to be in good condition. I agree to return all equipment in the same condition, normal wear and tear excepted.</p>

<h3>2. Liability</h3>
<p>I understand that diving is an inherently dangerous activity. I assume all risks associated with the use of this equipment.</p>

<h3>3. Financial Responsibility</h3>
<p>I agree to be financially responsible for any damage to, or loss of, rented equipment during the rental period. I authorize the dive shop to charge my credit card on file for any damages or unreturned equipment.</p>

<h3>4. Late Returns</h3>
<p>I understand that late returns are subject to additional daily rental fees.</p>

<h3>5. Certification</h3>
<p>I certify that I am a trained and certified diver qualified to use the rented equipment.</p>

<p>By signing below, I acknowledge that I have read, understood, and agree to these terms.</p>
', '1.0');
