-- Dashboard Widgets System
-- Configurable dashboard with various widget types

-- Widget Types (available widget configurations)
CREATE TABLE IF NOT EXISTS widget_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_code VARCHAR(50) UNIQUE NOT NULL,
    widget_name VARCHAR(100) NOT NULL,
    description TEXT,
    category ENUM('sales', 'inventory', 'customers', 'courses', 'equipment', 'reports') NOT NULL,
    default_size ENUM('small', 'medium', 'large', 'full') DEFAULT 'medium',
    is_active BOOLEAN DEFAULT TRUE,
    requires_permission VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_widget_code (widget_code),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Dashboard Widgets (user's configured widgets)
CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT,
    user_id INT NOT NULL,
    widget_type_id INT NOT NULL,
    position INT NOT NULL DEFAULT 0,
    size ENUM('small', 'medium', 'large', 'full') DEFAULT 'medium',
    settings JSON COMMENT 'Widget-specific settings (date ranges, limits, etc.)',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_type_id) REFERENCES widget_types(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_position (position),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dashboard Templates (pre-configured dashboard layouts)
CREATE TABLE IF NOT EXISTS dashboard_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    description TEXT,
    role_code VARCHAR(50) COMMENT 'Suggested for specific role',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_role_code (role_code),
    INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Template Widgets (widgets included in templates)
CREATE TABLE IF NOT EXISTS dashboard_template_widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    widget_type_id INT NOT NULL,
    position INT NOT NULL,
    size ENUM('small', 'medium', 'large', 'full') DEFAULT 'medium',
    default_settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (template_id) REFERENCES dashboard_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (widget_type_id) REFERENCES widget_types(id) ON DELETE CASCADE,
    INDEX idx_template_id (template_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default widget types
INSERT INTO widget_types (widget_code, widget_name, description, category, default_size, requires_permission) VALUES
-- Sales Widgets
('sales_today', 'Sales Today', 'Today''s sales summary with trend comparison', 'sales', 'medium', 'dashboard.view'),
('sales_chart', 'Sales Chart', 'Sales trend chart over time', 'sales', 'large', 'dashboard.view'),
('revenue_by_category', 'Revenue by Category', 'Sales breakdown by product category', 'sales', 'medium', 'analytics.view'),
('monthly_comparison', 'Monthly Comparison', 'Month-over-month sales comparison', 'sales', 'large', 'analytics.view'),
('recent_transactions', 'Recent Transactions', 'Latest completed transactions', 'sales', 'medium', 'transactions.view'),

-- Inventory Widgets
('low_stock_alerts', 'Low Stock Alerts', 'Products below reorder threshold', 'inventory', 'medium', 'products.view'),
('inventory_value', 'Inventory Value', 'Total inventory value and statistics', 'inventory', 'small', 'products.view'),
('top_products', 'Top Products', 'Best-selling products by revenue or units', 'inventory', 'medium', 'products.view'),
('pending_orders', 'Pending Orders', 'Purchase orders awaiting delivery', 'inventory', 'medium', 'products.inventory'),

-- Customer Widgets
('customer_stats', 'Customer Statistics', 'Customer metrics and top customers', 'customers', 'medium', 'customers.view'),

-- Course Widgets
('upcoming_courses', 'Upcoming Courses', 'Scheduled courses with enrollment status', 'courses', 'medium', 'courses.view'),

-- Equipment Widgets
('active_rentals', 'Active Rentals', 'Currently rented equipment and due dates', 'equipment', 'medium', 'equipment.view');

-- Create default dashboard templates
INSERT INTO dashboard_templates (template_name, description, role_code, is_default) VALUES
('Manager Dashboard', 'Comprehensive overview for managers', 'manager', TRUE),
('Sales Associate Dashboard', 'Focus on sales and transactions', 'sales_associate', TRUE),
('Inventory Manager Dashboard', 'Stock and inventory focus', NULL, FALSE),
('Course Instructor Dashboard', 'Course and student focus', 'instructor', TRUE);

-- Manager Dashboard Template
INSERT INTO dashboard_template_widgets (template_id, widget_type_id, position, size, default_settings) VALUES
(1, (SELECT id FROM widget_types WHERE widget_code = 'sales_today'), 1, 'medium', '{}'),
(1, (SELECT id FROM widget_types WHERE widget_code = 'sales_chart'), 2, 'large', '{"days": 30}'),
(1, (SELECT id FROM widget_types WHERE widget_code = 'inventory_value'), 3, 'small', '{}'),
(1, (SELECT id FROM widget_types WHERE widget_code = 'low_stock_alerts'), 4, 'medium', '{"limit": 10}'),
(1, (SELECT id FROM widget_types WHERE widget_code = 'top_products'), 5, 'medium', '{"days": 30, "limit": 10}'),
(1, (SELECT id FROM widget_types WHERE widget_code = 'customer_stats'), 6, 'medium', '{"days": 30}');

-- Sales Associate Dashboard Template
INSERT INTO dashboard_template_widgets (template_id, widget_type_id, position, size, default_settings) VALUES
(2, (SELECT id FROM widget_types WHERE widget_code = 'sales_today'), 1, 'large', '{}'),
(2, (SELECT id FROM widget_types WHERE widget_code = 'recent_transactions'), 2, 'medium', '{"limit": 10}'),
(2, (SELECT id FROM widget_types WHERE widget_code = 'top_products'), 3, 'medium', '{"days": 7, "limit": 5}'),
(2, (SELECT id FROM widget_types WHERE widget_code = 'low_stock_alerts'), 4, 'medium', '{"limit": 5}');

-- Inventory Manager Dashboard Template
INSERT INTO dashboard_template_widgets (template_id, widget_type_id, position, size, default_settings) VALUES
(3, (SELECT id FROM widget_types WHERE widget_code = 'inventory_value'), 1, 'medium', '{}'),
(3, (SELECT id FROM widget_types WHERE widget_code = 'low_stock_alerts'), 2, 'large', '{"limit": 20}'),
(3, (SELECT id FROM widget_types WHERE widget_code = 'pending_orders'), 3, 'medium', '{}'),
(3, (SELECT id FROM widget_types WHERE widget_code = 'top_products'), 4, 'medium', '{"days": 30, "limit": 10}');

-- Instructor Dashboard Template
INSERT INTO dashboard_template_widgets (template_id, widget_type_id, position, size, default_settings) VALUES
(4, (SELECT id FROM widget_types WHERE widget_code = 'upcoming_courses'), 1, 'large', '{"limit": 10}'),
(4, (SELECT id FROM widget_types WHERE widget_code = 'active_rentals'), 2, 'medium', '{}'),
(4, (SELECT id FROM widget_types WHERE widget_code = 'customer_stats'), 3, 'medium', '{"days": 30}');
