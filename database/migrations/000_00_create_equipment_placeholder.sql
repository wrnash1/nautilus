-- ============================================================================
-- Migration: Create Equipment Placeholder
-- Purpose: Fix "Table 'nautilus.equipment' doesn't exist" error during installation
-- This table is referenced by a migration or seed but is missing from the schema.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `equipment` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
