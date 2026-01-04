-- Nautilus Complete Database Schema
-- WordPress-style single-file installation
-- Version: 1.0

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `tenants`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `migrations`;

-- Tenants table
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_uuid` char(36) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `subdomain` varchar(50) NOT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_tenant_uuid_unique` (`tenant_uuid`),
  UNIQUE KEY `tenants_subdomain_unique` (`subdomain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles table
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Roles junction table
CREATE TABLE `user_roles` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions table
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions junction table
CREATE TABLE `role_permissions` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings table
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(20) DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_setting_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrations tracking table
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Super Admin', 'Full system access', NOW()),
(2, 'Admin', 'Administrator access', NOW()),
(3, 'Manager', 'Manager access', NOW()),
(4, 'Cashier', 'Cashier access', NOW());

-- Insert default permissions
INSERT INTO `permissions` (`name`, `display_name`, `module`, `description`, `created_at`) VALUES
('dashboard.view', 'View Dashboard', 'dashboard', 'View dashboard', NOW()),
('dashboard.export', 'Export Reports', 'dashboard', 'Export reports', NOW()),
('pos.view', 'View POS', 'pos', 'View POS interface', NOW()),
('pos.create', 'Create Transaction', 'pos', 'Create transactions', NOW()),
('pos.void', 'Void Transaction', 'pos', 'Void transactions', NOW()),
('pos.refund', 'Refund Transaction', 'pos', 'Refund transactions', NOW()),
('customers.view', 'View Customers', 'customers', 'View customers', NOW()),
('customers.create', 'Create Customer', 'customers', 'Create customers', NOW()),
('customers.edit', 'Edit Customer', 'customers', 'Edit customers', NOW()),
('customers.delete', 'Delete Customer', 'customers', 'Delete customers', NOW()),
('users.view', 'View Users', 'users', 'View users', NOW()),
('users.create', 'Create User', 'users', 'Create users', NOW()),
('users.edit', 'Edit User', 'users', 'Edit users', NOW()),
('users.delete', 'Delete User', 'users', 'Delete users', NOW());

-- Assign all permissions to Super Admin and Admin roles
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM permissions;

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM permissions;

-- Record this as a migration
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('000_CORE_SCHEMA', 1);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
