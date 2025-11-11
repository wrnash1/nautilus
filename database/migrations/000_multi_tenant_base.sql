-- ================================================
-- Nautilus - Multi-Tenant Base Structure
-- Migration: 000_multi_tenant_base.sql
-- Description: Create tenants table and prepare for multi-tenant architecture
-- This must run BEFORE 001 so tenant_id can be added to users table
-- ================================================

CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_uuid` VARCHAR(36) NOT NULL UNIQUE,
  `company_name` VARCHAR(255) NOT NULL,
  `subdomain` VARCHAR(100) NOT NULL UNIQUE,
  `contact_email` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'suspended', 'cancelled') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_subdomain` (`subdomain`),
  INDEX `idx_status` (`status`),
  INDEX `idx_tenant_uuid` (`tenant_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin role (needed before users table is created)
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `display_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin role
INSERT INTO `roles` (`id`, `name`, `display_name`, `description`)
VALUES (1, 'admin', 'Administrator', 'Full system access')
ON DUPLICATE KEY UPDATE `id` = `id`;

-- ================================================
-- Multi-tenant base structure complete
-- ================================================
