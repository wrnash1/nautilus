-- Seed Default Roles and Permissions for Nautilus

-- Insert Roles
INSERT IGNORE INTO roles (id, name, display_name, description) VALUES
(1, 'admin', 'Administrator', 'Full system access with all permissions'),
(2, 'manager', 'Manager', 'Store manager with most permissions except system settings'),
(3, 'employee', 'Employee', 'Regular employee with limited permissions'),
(4, 'instructor', 'Instructor', 'Dive instructor with course/certification permissions'),
(5, 'cashier', 'Cashier', 'POS and cash drawer access only');

-- Insert Permissions
INSERT IGNORE INTO permissions (name, display_name, module, description) VALUES
-- Dashboard
('dashboard.view', 'View Dashboard', 'Dashboard', 'Access to main dashboard'),

-- POS
('pos.view', 'View POS', 'POS', 'Access point of sale'),
('pos.access', 'Use POS', 'POS', 'Process transactions'),
('pos.refund', 'Process Refunds', 'POS', 'Issue refunds and returns'),

-- Customers
('customers.view', 'View Customers', 'Customers', 'View customer list and details'),
('customers.create', 'Create Customers', 'Customers', 'Add new customers'),
('customers.edit', 'Edit Customers', 'Customers', 'Modify customer information'),
('customers.delete', 'Delete Customers', 'Customers', 'Remove customers'),
('customers.export', 'Export Customer Data', 'Customers', 'Export customer data'),

-- Products & Inventory
('products.view', 'View Products', 'Products', 'View product catalog'),
('products.create', 'Create Products', 'Products', 'Add new products'),
('products.edit', 'Edit Products', 'Products', 'Modify product information'),
('products.delete', 'Delete Products', 'Products', 'Remove products'),
('products.adjust_inventory', 'Adjust Inventory', 'Products', 'Modify stock levels'),

-- Categories
('categories.view', 'View Categories', 'Categories', 'View product categories'),
('categories.manage', 'Manage Categories', 'Categories', 'Create, edit, delete categories'),

-- Rentals
('rentals.view', 'View Rentals', 'Rentals', 'View rental equipment and reservations'),
('rentals.manage', 'Manage Rentals', 'Rentals', 'Create and manage rentals'),

-- Courses
('courses.view', 'View Courses', 'Courses', 'View course information'),
('courses.manage', 'Manage Courses', 'Courses', 'Create and manage courses'),
('courses.enroll', 'Enroll Students', 'Courses', 'Enroll customers in courses'),
('courses.certify', 'Issue Certifications', 'Courses', 'Issue dive certifications'),

-- Trips
('trips.view', 'View Trips', 'Trips', 'View trip information'),
('trips.manage', 'Manage Trips', 'Trips', 'Create and manage dive trips'),
('trips.book', 'Book Trips', 'Trips', 'Book customers on trips'),

-- Work Orders
('workorders.view', 'View Work Orders', 'Work Orders', 'View service tickets'),
('workorders.manage', 'Manage Work Orders', 'Work Orders', 'Create and manage work orders'),

-- Orders
('orders.view', 'View Orders', 'Orders', 'View online store orders'),
('orders.manage', 'Manage Orders', 'Orders', 'Process and fulfill orders'),

-- Reports
('reports.view', 'View Reports', 'Reports', 'Access to basic reports'),
('reports.advanced', 'Advanced Reports', 'Reports', 'Access to all reports and analytics'),

-- Staff
('staff.view', 'View Staff', 'Staff', 'View employee information'),
('staff.manage', 'Manage Staff', 'Staff', 'Manage employee records and schedules'),

-- Air Fills
('air_fills.view', 'View Air Fills', 'Air Fills', 'View air fill records'),
('air_fills.create', 'Record Air Fills', 'Air Fills', 'Record new air fills'),

-- Admin Functions
('admin.settings', 'System Settings', 'Admin', 'Access system settings'),
('admin.users', 'User Management', 'Admin', 'Manage system users'),
('admin.roles', 'Role Management', 'Admin', 'Manage roles and permissions'),
('admin.integrations', 'Integrations', 'Admin', 'Manage third-party integrations'),
('admin.api', 'API Management', 'Admin', 'Manage API tokens and access'),
('admin.backups', 'Backups', 'Admin', 'Access database backups');

-- Assign ALL permissions to Admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Assign Manager permissions (all except admin functions)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE module != 'Admin' OR name IN ('admin.settings', 'admin.integrations');

-- Assign Employee permissions (basic access)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE name IN (
    'dashboard.view',
    'pos.view', 'pos.access',
    'customers.view', 'customers.create', 'customers.edit',
    'products.view',
    'categories.view',
    'rentals.view', 'rentals.manage',
    'air_fills.view', 'air_fills.create',
    'workorders.view', 'workorders.manage',
    'reports.view'
);

-- Assign Instructor permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions
WHERE name IN (
    'dashboard.view',
    'customers.view', 'customers.create', 'customers.edit',
    'courses.view', 'courses.manage', 'courses.enroll', 'courses.certify',
    'trips.view', 'trips.book',
    'reports.view'
);

-- Assign Cashier permissions (POS only)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions
WHERE name IN (
    'dashboard.view',
    'pos.view', 'pos.access',
    'customers.view',
    'products.view'
);

-- Summary
SELECT
    'Database Seeding Complete!' as status,
    (SELECT COUNT(*) FROM roles) as total_roles,
    (SELECT COUNT(*) FROM permissions) as total_permissions,
    (SELECT COUNT(*) FROM role_permissions) as total_role_permissions;
