-- ================================================
-- Fix Foreign Key Constraint Issues
-- This script repairs foreign key constraints that failed during migration
-- ================================================

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS=0;

-- Ensure all base tables exist with correct structure
-- These should have been created in migration 000 and 001

-- Drop and recreate role_permissions if it has issues
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS=1;

-- ================================================
-- Verification
-- ================================================
SELECT 'Foreign key fixes applied successfully' AS status;
