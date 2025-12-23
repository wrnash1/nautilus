-- ============================================================================
-- Migration: Create Dashboard Widgets System
-- Created: 2024
-- Description: User-customizable dashboard widgets with drag-and-drop layout
-- ============================================================================

-- Dashboard User Widget Configuration
CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id INTEGER  PRIMARY KEY,
    user_id BIGINTEGER NOT NULL,
    widget_id VARCHAR(50) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    row_position INT NOT NULL DEFAULT 0,
    column_position INT NOT NULL DEFAULT 0,
    width INT NOT NULL DEFAULT 1,
    height INT NOT NULL DEFAULT 1,
    config TEXT,
    is_visible SMALLINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(user_id, widget_id)
);

CREATE INDEX IF NOT EXISTS idx_dashboard_widgets_user ON dashboard_widgets(user_id);
CREATE INDEX IF NOT EXISTS idx_dashboard_widgets_position ON dashboard_widgets(user_id, position);

-- Widget Categories for Organization
CREATE TABLE IF NOT EXISTS widget_categories (
    id INTEGER  PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default widget categories
INSERT IGNORE INTO widget_categories (id, name, description, sort_order) VALUES
(1, 'Sales & Revenue', 'Sales metrics, revenue tracking, and financial widgets', 1),
(2, 'Inventory', 'Stock levels, reorder alerts, and inventory management', 2),
(3, 'Customers', 'Customer metrics, engagement, and CRM widgets', 3),
(4, 'Operations', 'Rentals, courses, trips, and operational widgets', 4),
(5, 'Analytics', 'Advanced analytics, charts, and reports', 5),
(6, 'Quick Actions', 'Shortcuts and frequently used functions', 6);

-- Insert default dashboard layouts for different roles
-- This will be populated when users are created
-- Skipping initial insert to avoid dependency on users table structure

-- ============================================================================
-- Migration Complete
-- ============================================================================
