-- ============================================================================
-- NAUTILUS DIVE SHOP - CORE SCHEMA
-- Essential tables for application to function
-- This replaces the complex 100+ migration files for initial setup
-- ============================================================================

-- Database creation is handled by the installer or setup script

-- ============================================================================
-- MULTI-TENANT & AUTHENTICATION
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `subdomain` VARCHAR(100) UNIQUE,
    `custom_domain` VARCHAR(255),
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `settings` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_subdomain` (`subdomain`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default tenant
INSERT INTO `tenants` (`id`, `name`, `subdomain`, `status`) VALUES (1, 'Default Tenant', 'default', 'active');

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`name`, `description`) VALUES
('Super Admin', 'Full system access'),
('Admin', 'Store administrator'),
('Manager', 'Store manager'),
('Staff', 'Store staff'),
('Instructor', 'Diving instructor');

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `display_name` VARCHAR(150) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert core permissions
-- Insert default permissions
INSERT INTO permissions (name, display_name, module, description) VALUES
-- Dashboard
('dashboard.view', 'View Dashboard', 'dashboard', 'Access main dashboard'),
('analytics.view', 'View Analytics', 'dashboard', 'View analytics and reports'),

-- Point of Sale
('pos.view', 'View POS', 'pos', 'Access point of sale system'),
('pos.access', 'Access POS', 'pos', 'Process sales transactions'),

-- Products & Inventory
('products.view', 'View Products', 'products', 'View product list and details'),
('products.create', 'Create Products', 'products', 'Create new products'),
('products.edit', 'Edit Products', 'products', 'Edit existing products'),
('products.delete', 'Delete Products', 'products', 'Delete products'),
('products.inventory', 'Manage Inventory', 'products', 'Adjust stock levels'),

-- Categories
('categories.view', 'View Categories', 'products', 'View product categories'),
('categories.manage', 'Manage Categories', 'products', 'Create and edit categories'),

-- Customers
('customers.view', 'View Customers', 'customers', 'View customer list and details'),
('customers.create', 'Create Customers', 'customers', 'Create new customers'),
('customers.edit', 'Edit Customers', 'customers', 'Edit existing customers'),
('customers.delete', 'Delete Customers', 'customers', 'Delete customers'),

-- Transactions
('transactions.view', 'View Transactions', 'transactions', 'View transaction history'),
('transactions.create', 'Create Transactions', 'transactions', 'Process sales'),
('transactions.void', 'Void Transactions', 'transactions', 'Void/cancel transactions'),
('transactions.refund', 'Process Refunds', 'transactions', 'Process refunds'),

-- Courses
('courses.view', 'View Courses', 'courses', 'View course list and details'),
('courses.create', 'Create Courses', 'courses', 'Create new courses'),
('courses.edit', 'Edit Courses', 'courses', 'Edit existing courses'),
('courses.delete', 'Delete Courses', 'courses', 'Delete courses'),
('enrollments.manage', 'Manage Enrollments', 'courses', 'Manage course enrollments'),

-- Trips
('trips.view', 'View Trips', 'trips', 'View trip list and details'),
('trips.manage', 'Manage Trips', 'trips', 'Create and edit trips'),

-- Equipment/Rentals
('rentals.view', 'View Rentals', 'rentals', 'View equipment rentals'),
('rentals.manage', 'Manage Rentals', 'rentals', 'Process equipment rentals'),

-- Air Fills
('air_fills.view', 'View Air Fills', 'air_fills', 'View air fill records'),
('air_fills.manage', 'Manage Air Fills', 'air_fills', 'Process air fills'),

-- Reports
('reports.view', 'View Reports', 'reports', 'Access reports'),
('reports.export', 'Export Data', 'reports', 'Export data to CSV/Excel'),
('reports.advanced', 'Advanced Reports', 'reports', 'Access advanced analytics'),

-- Settings
('settings.view', 'View Settings', 'settings', 'View application settings'),
('settings.edit', 'Edit Settings', 'settings', 'Modify application settings'),
('users.manage', 'Manage Users', 'settings', 'Manage user accounts'),
('roles.manage', 'Manage Roles', 'settings', 'Manage roles and permissions'),
('audit.view', 'View Audit Log', 'settings', 'View audit logs'),

-- System
('system.admin', 'System Admin', 'system', 'Full system access')
ON DUPLICATE KEY UPDATE name=VALUES(name);

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `permission_code` VARCHAR(100),
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grant all permissions to Super Admin
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED DEFAULT 1,
    `username` VARCHAR(100) UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100),
    `last_name` VARCHAR(100),
    `phone` VARCHAR(20),
    `google_id` VARCHAR(255) UNIQUE,
    `is_active` TINYINT(1) DEFAULT 1,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `two_factor_secret` VARCHAR(255),
    `last_login_at` TIMESTAMP NULL,
    `password_changed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant_id` (`tenant_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_is_active` (`is_active`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `assigned_by` INT UNSIGNED,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_role` (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create default admin user (password: admin123)
INSERT INTO `users` (`tenant_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`) VALUES
(1, 'admin', 'admin@nautilus.local', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5Bf/6H/T3CQLC', 'Admin', 'User');

-- Assign Super Admin role to default user
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 1);

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) PRIMARY KEY,
    `user_id` INT UNSIGNED,
    `payload` TEXT,
    `last_activity` INT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_token` (`token`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- COMPLETE
-- ============================================================================
SELECT 'Core schema installation complete!' as status;
