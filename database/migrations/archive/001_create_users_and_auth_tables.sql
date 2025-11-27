-- roles table created in 000_multi_tenant_base.sql

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

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20),
  `google_id` VARCHAR(255) UNIQUE,
  `two_factor_secret` VARCHAR(255),
  `two_factor_enabled` BOOLEAN DEFAULT FALSE,
  `last_login_at` TIMESTAMP NULL,
  `password_changed_at` TIMESTAMP NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
  INDEX `idx_tenant_id` (`tenant_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_google_id` (`google_id`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `expires_at` TIMESTAMP NOT NULL,
  `used_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_token` (`token`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) PRIMARY KEY,
  `user_id` INT UNSIGNED,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `payload` TEXT NOT NULL,
  `last_activity` INT NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50),
  `entity_id` INT UNSIGNED,
  `old_values` JSON,
  `new_values` JSON,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_module` (`module`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_role` (`user_id`, `role_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- Assign all permissions to admin role (role_id = 1)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id);
