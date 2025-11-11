-- User Permissions and Roles System
-- Comprehensive RBAC (Role-Based Access Control) system

-- Roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    role_name VARCHAR(100) NOT NULL,
    role_code VARCHAR(50) NOT NULL,
    description TEXT,
    is_system_role BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_role (tenant_id, role_code),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_role_code (role_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions table
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL,
    permission_code VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_permission_code (permission_code),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions (many-to-many)
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Roles (many-to-many)
CREATE TABLE IF NOT EXISTS user_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Direct User Permissions (override role permissions)
CREATE TABLE IF NOT EXISTS user_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    is_granted BOOLEAN DEFAULT TRUE,
    granted_by INT UNSIGNED,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    reason TEXT,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_permission (user_id, permission_id),
    INDEX idx_user_id (user_id),
    INDEX idx_permission_id (permission_id),
    INDEX idx_is_granted (is_granted),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permission Audit Log
CREATE TABLE IF NOT EXISTS permission_audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    user_id INT UNSIGNED,
    action VARCHAR(50) NOT NULL,
    permission_code VARCHAR(100),
    role_id INT UNSIGNED,
    granted_to_user_id INT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (granted_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT INTO permissions (permission_name, permission_code, category, description) VALUES
-- Dashboard
('View Dashboard', 'dashboard.view', 'dashboard', 'Access main dashboard'),
('View Analytics', 'analytics.view', 'dashboard', 'View analytics and reports'),

-- Products
('View Products', 'products.view', 'products', 'View product list and details'),
('Create Products', 'products.create', 'products', 'Create new products'),
('Edit Products', 'products.edit', 'products', 'Edit existing products'),
('Delete Products', 'products.delete', 'products', 'Delete products'),
('Manage Inventory', 'products.inventory', 'products', 'Adjust stock levels'),

-- Customers
('View Customers', 'customers.view', 'customers', 'View customer list and details'),
('Create Customers', 'customers.create', 'customers', 'Create new customers'),
('Edit Customers', 'customers.edit', 'customers', 'Edit existing customers'),
('Delete Customers', 'customers.delete', 'customers', 'Delete customers'),

-- Transactions/POS
('View Transactions', 'transactions.view', 'transactions', 'View transaction history'),
('Create Transactions', 'transactions.create', 'transactions', 'Process sales'),
('Void Transactions', 'transactions.void', 'transactions', 'Void/cancel transactions'),
('Process Refunds', 'transactions.refund', 'transactions', 'Process refunds'),

-- Courses
('View Courses', 'courses.view', 'courses', 'View course list and details'),
('Create Courses', 'courses.create', 'courses', 'Create new courses'),
('Edit Courses', 'courses.edit', 'courses', 'Edit existing courses'),
('Delete Courses', 'courses.delete', 'courses', 'Delete courses'),
('Manage Enrollments', 'enrollments.manage', 'courses', 'Manage course enrollments'),

-- Equipment/Rentals
('View Equipment', 'equipment.view', 'equipment', 'View equipment list'),
('Create Equipment', 'equipment.create', 'equipment', 'Add new equipment'),
('Edit Equipment', 'equipment.edit', 'equipment', 'Edit equipment details'),
('Delete Equipment', 'equipment.delete', 'equipment', 'Delete equipment'),
('Manage Rentals', 'rentals.manage', 'equipment', 'Process equipment rentals'),

-- Reports
('View Reports', 'reports.view', 'reports', 'Access reports'),
('Export Data', 'reports.export', 'reports', 'Export data to CSV/Excel'),
('Advanced Reports', 'reports.advanced', 'reports', 'Access advanced analytics'),

-- Settings
('View Settings', 'settings.view', 'settings', 'View application settings'),
('Edit Settings', 'settings.edit', 'settings', 'Modify application settings'),
('Manage Users', 'users.manage', 'settings', 'Manage user accounts'),
('Manage Roles', 'roles.manage', 'settings', 'Manage roles and permissions'),
('View Audit Log', 'audit.view', 'settings', 'View audit logs'),

-- API
('API Access', 'api.access', 'api', 'Access REST API'),
('API Keys Manage', 'api.keys', 'api', 'Manage API keys'),

-- Advanced
('System Admin', 'system.admin', 'system', 'Full system access'),
('Tenant Admin', 'tenant.admin', 'system', 'Full tenant admin access');

-- Insert default system roles (these will be copied per tenant on signup)
INSERT INTO roles (tenant_id, role_name, role_code, description, is_system_role) VALUES
(NULL, 'Super Admin', 'super_admin', 'Full system access across all tenants', TRUE),
(NULL, 'Tenant Admin', 'tenant_admin', 'Full access within tenant', TRUE),
(NULL, 'Manager', 'manager', 'Manage daily operations', TRUE),
(NULL, 'Sales Associate', 'sales_associate', 'Process sales and assist customers', TRUE),
(NULL, 'Instructor', 'instructor', 'Manage courses and students', TRUE),
(NULL, 'Viewer', 'viewer', 'Read-only access', TRUE);
