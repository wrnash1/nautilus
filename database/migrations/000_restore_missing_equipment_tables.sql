-- ============================================================================
-- Migration: Restore Missing Equipment Tables
-- Purpose: Restore missing `equipment` and `equipment_rentals` tables.
-- These tables are referenced by code (CustomerPortalService) and other migrations
-- but the original creation files are missing.
-- ============================================================================

-- Restore `equipment` table
CREATE TABLE IF NOT EXISTS `equipment` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `serial_number` VARCHAR(100) NULL,
    `description` TEXT NULL,
    `status` ENUM('available', 'rented', 'maintenance', 'retired') DEFAULT 'available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restore `equipment_rentals` table
CREATE TABLE IF NOT EXISTS `equipment_rentals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `equipment_id` INT UNSIGNED NOT NULL,
    `rental_date` DATE NOT NULL,
    `return_due_date` DATE NOT NULL,
    `return_date` DATE NULL,
    `daily_rate` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `deposit_amount` DECIMAL(10, 2) DEFAULT 0.00,
    `status` ENUM('active', 'completed', 'overdue', 'cancelled') DEFAULT 'active',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer_id` (`customer_id`),
    INDEX `idx_equipment_id` (`equipment_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restore `suppliers` table
CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `contact_name` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `address` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
