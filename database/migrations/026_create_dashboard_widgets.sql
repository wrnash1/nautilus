-- ============================================================================
-- Migration: Create Dashboard Widgets System
-- Created: 2024
-- Description: User-customizable dashboard widgets with drag-and-drop layout
-- ============================================================================

-- Dashboard User Widget Configuration
CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    widget_id VARCHAR(50) NOT NULL,  -- e.g. 'sales_chart', 'low_stock_alert'
    position INTEGER NOT NULL DEFAULT 0,  -- Display order
    row_position INTEGER NOT NULL DEFAULT 0,
    column_position INTEGER NOT NULL DEFAULT 0,
    width INTEGER NOT NULL DEFAULT 1,  -- Grid width (1-12)
    height INTEGER NOT NULL DEFAULT 1,  -- Grid height
    config TEXT,  -- JSON configuration for widget-specific settings
    is_visible BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id, widget_id)
);

CREATE INDEX IF NOT EXISTS idx_dashboard_widgets_user ON dashboard_widgets(user_id);
CREATE INDEX IF NOT EXISTS idx_dashboard_widgets_position ON dashboard_widgets(user_id, position);

-- Widget Categories for Organization
CREATE TABLE IF NOT EXISTS widget_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default widget categories
INSERT OR IGNORE INTO widget_categories (id, name, description, sort_order) VALUES
(1, 'Sales & Revenue', 'Sales metrics, revenue tracking, and financial widgets', 1),
(2, 'Inventory', 'Stock levels, reorder alerts, and inventory management', 2),
(3, 'Customers', 'Customer metrics, engagement, and CRM widgets', 3),
(4, 'Operations', 'Rentals, courses, trips, and operational widgets', 4),
(5, 'Analytics', 'Advanced analytics, charts, and reports', 5),
(6, 'Quick Actions', 'Shortcuts and frequently used functions', 6);

-- Insert default dashboard layouts for different roles
-- Admin gets full dashboard
INSERT OR IGNORE INTO dashboard_widgets (user_id, widget_id, position, row_position, column_position, width, height, is_visible)
SELECT
    u.id,
    'sales_overview',
    1,
    0,
    0,
    6,
    1,
    1
FROM users u
WHERE u.role = 'admin' AND NOT EXISTS (
    SELECT 1 FROM dashboard_widgets dw WHERE dw.user_id = u.id AND dw.widget_id = 'sales_overview'
);

-- ============================================================================
-- Migration Complete
-- ============================================================================
