SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `analytics_events`;
DROP TABLE IF EXISTS `report_schedules`;
DROP TABLE IF EXISTS `dashboard_widgets`;
DROP TABLE IF EXISTS `product_analytics`;
DROP TABLE IF EXISTS `customer_analytics`;
DROP TABLE IF EXISTS `sales_trends`;
DROP TABLE IF EXISTS `business_kpis`;
DROP TABLE IF EXISTS `dashboard_metrics_cache`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `analytics_events`;
DROP TABLE IF EXISTS `report_schedules`;
DROP TABLE IF EXISTS `dashboard_widgets`;
DROP TABLE IF EXISTS `product_analytics`;
DROP TABLE IF EXISTS `customer_analytics`;
DROP TABLE IF EXISTS `sales_trends`;
DROP TABLE IF EXISTS `business_kpis`;
DROP TABLE IF EXISTS `dashboard_metrics_cache`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `analytics_events`;
DROP TABLE IF EXISTS `report_schedules`;
DROP TABLE IF EXISTS `dashboard_widgets`;
DROP TABLE IF EXISTS `product_analytics`;
DROP TABLE IF EXISTS `customer_analytics`;
DROP TABLE IF EXISTS `sales_trends`;
DROP TABLE IF EXISTS `business_kpis`;
DROP TABLE IF EXISTS `dashboard_metrics_cache`;

-- Migration: Analytics Dashboard Tables
-- Description: Creates tables for caching and storing analytics data
-- Version: 057
-- Date: 2025-01-08

-- Dashboard Metrics Cache Table
-- Stores pre-calculated metrics to improve dashboard performance
CREATE TABLE IF NOT EXISTS dashboard_metrics_cache (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Metric identification
    metric_key VARCHAR(100) NOT NULL,
    metric_name VARCHAR(100),

    -- Time period
    period_type ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,

    -- Metric value
    metric_value DECIMAL(15,2),
    metric_data JSON,

    -- Comparison data
    previous_period_value DECIMAL(15,2),
    growth_rate DECIMAL(10,2),

    -- Metadata
    calculation_time DECIMAL(10,4) COMMENT 'Time in seconds to calculate',
    last_calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    UNIQUE KEY unique_metric_period (metric_key, period_type, period_start, period_end),
    INDEX idx_metric_key (metric_key),
    INDEX idx_period (period_start, period_end),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Business KPIs Table
-- Stores key performance indicators over time
CREATE TABLE IF NOT EXISTS business_kpis (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Date tracking
    kpi_date DATE NOT NULL,

    -- Sales KPIs
    total_revenue DECIMAL(12,2) DEFAULT 0,
    total_transactions INT DEFAULT 0,
    average_order_value DECIMAL(10,2) DEFAULT 0,
    revenue_per_day DECIMAL(10,2) DEFAULT 0,

    -- Customer KPIs
    new_customers INT DEFAULT 0,
    repeat_customers INT DEFAULT 0,
    customer_retention_rate DECIMAL(5,2) DEFAULT 0,
    avg_customer_lifetime_value DECIMAL(10,2) DEFAULT 0,

    -- Product KPIs
    units_sold INT DEFAULT 0,
    inventory_turnover_ratio DECIMAL(5,2) DEFAULT 0,
    gross_profit_margin DECIMAL(5,2) DEFAULT 0,

    -- Course KPIs
    course_enrollments INT DEFAULT 0,
    course_completion_rate DECIMAL(5,2) DEFAULT 0,
    course_revenue DECIMAL(10,2) DEFAULT 0,

    -- Rental KPIs
    rental_count INT DEFAULT 0,
    equipment_utilization_rate DECIMAL(5,2) DEFAULT 0,
    rental_revenue DECIMAL(10,2) DEFAULT 0,

    -- Operational KPIs
    low_stock_items INT DEFAULT 0,
    maintenance_overdue INT DEFAULT 0,
    customer_satisfaction_score DECIMAL(3,2) DEFAULT 0,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_kpi_date (kpi_date),
    INDEX idx_kpi_date (kpi_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sales Trends Table
-- Tracks daily sales trends for visualization
CREATE TABLE IF NOT EXISTS sales_trends (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Date
    trend_date DATE NOT NULL,

    -- Sales metrics
    daily_revenue DECIMAL(10,2) DEFAULT 0,
    daily_transactions INT DEFAULT 0,
    daily_units_sold INT DEFAULT 0,

    -- Payment breakdown
    cash_sales DECIMAL(10,2) DEFAULT 0,
    credit_sales DECIMAL(10,2) DEFAULT 0,
    other_sales DECIMAL(10,2) DEFAULT 0,

    -- Category breakdown (stored as JSON for flexibility)
    category_breakdown JSON,

    -- Hour-by-hour breakdown (for identifying peak hours)
    hourly_breakdown JSON,

    -- Comparison metrics
    previous_day_revenue DECIMAL(10,2),
    previous_week_revenue DECIMAL(10,2),
    previous_month_revenue DECIMAL(10,2),

    -- Trend indicators
    trend_direction ENUM('increasing', 'stable', 'decreasing'),
    daily_growth_rate DECIMAL(10,2),
    weekly_growth_rate DECIMAL(10,2),

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_trend_date (trend_date),
    INDEX idx_trend_date (trend_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Analytics Table
-- Stores customer segmentation and analytics
CREATE TABLE IF NOT EXISTS customer_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,

    -- Purchase behavior
    total_purchases INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0,
    average_order_value DECIMAL(10,2) DEFAULT 0,
    last_purchase_date DATE,

    -- Engagement metrics
    days_since_last_purchase INT,
    purchase_frequency DECIMAL(5,2) COMMENT 'Purchases per month',
    customer_lifetime_value DECIMAL(10,2),

    -- Customer segmentation
    customer_segment ENUM('new', 'active', 'at_risk', 'dormant', 'vip') DEFAULT 'new',
    rfm_score INT COMMENT 'Recency, Frequency, Monetary score',
    rfm_segment VARCHAR(20),

    -- Preferences
    favorite_category VARCHAR(100),
    preferred_payment_method VARCHAR(50),

    -- Course activity
    courses_completed INT DEFAULT 0,
    certifications_count INT DEFAULT 0,

    -- Rental activity
    rentals_count INT DEFAULT 0,
    rental_revenue DECIMAL(10,2) DEFAULT 0,

    -- Calculated at
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer (customer_id),
    INDEX idx_segment (customer_segment),
    INDEX idx_clv (customer_lifetime_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Performance Table
-- Tracks product-level analytics
CREATE TABLE IF NOT EXISTS product_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,

    -- Time period
    period_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,

    -- Sales metrics
    units_sold INT DEFAULT 0,
    revenue DECIMAL(10,2) DEFAULT 0,
    profit DECIMAL(10,2) DEFAULT 0,
    profit_margin DECIMAL(5,2) DEFAULT 0,

    -- Inventory metrics
    avg_stock_level DECIMAL(10,2),
    stockout_days INT DEFAULT 0,
    turnover_rate DECIMAL(5,2),

    -- Customer metrics
    unique_customers INT DEFAULT 0,
    repeat_purchase_rate DECIMAL(5,2),

    -- Ranking
    sales_rank INT,
    revenue_rank INT,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_period (product_id, period_type, period_start, period_end),
    INDEX idx_product_period (product_id, period_type),
    INDEX idx_period (period_start, period_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dashboard Widgets Table
-- Stores user-customizable dashboard configurations
CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,

    -- Widget configuration
    widget_type VARCHAR(50) NOT NULL,
    widget_title VARCHAR(100),
    widget_position INT DEFAULT 0,
    widget_size ENUM('small', 'medium', 'large', 'full') DEFAULT 'medium',

    -- Widget settings
    settings JSON,
    refresh_interval INT DEFAULT 300 COMMENT 'Seconds',

    -- Visibility
    is_visible BOOLEAN DEFAULT TRUE,
    is_collapsed BOOLEAN DEFAULT FALSE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_widget_type (widget_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Schedules Table
-- Manages scheduled report generation and delivery
CREATE TABLE IF NOT EXISTS report_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Report details
    report_name VARCHAR(100) NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    description TEXT,

    -- Schedule
    schedule_type ENUM('daily', 'weekly', 'monthly', 'quarterly') NOT NULL,
    schedule_time TIME DEFAULT '09:00:00',
    schedule_day INT COMMENT 'Day of week (1-7) or day of month (1-31)',

    -- Recipients
    recipients JSON NOT NULL COMMENT 'Array of email addresses',

    -- Report parameters
    parameters JSON,

    -- Format
    output_format ENUM('pdf', 'excel', 'csv', 'html') DEFAULT 'pdf',

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    last_run_at TIMESTAMP NULL,
    next_run_at TIMESTAMP NULL,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_next_run (next_run_at),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics Events Table
-- Tracks important business events for analytics
CREATE TABLE IF NOT EXISTS analytics_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Event details
    event_type VARCHAR(50) NOT NULL,
    event_name VARCHAR(100),
    event_category VARCHAR(50),

    -- Associated entities
    user_id BIGINT,
    customer_id INT,
    transaction_id INT,
    product_id INT,

    -- Event data
    event_data JSON,
    event_value DECIMAL(10,2),

    -- Context
    session_id VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,

    -- Timestamps
    event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_event_type (event_type),
    INDEX idx_event_timestamp (event_timestamp),
    INDEX idx_customer_id (customer_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create initial dashboard widgets for admin users
-- Commented out due to potential FK issues - can be run manually after all users are set up
-- INSERT INTO dashboard_widgets (user_id, widget_type, widget_title, widget_position, widget_size, settings)
-- SELECT
--     id,
--     'sales_overview',
--     'Sales Overview',
--     1,
--     'large',
--     '{"metric": "revenue", "period": "30days"}'
-- FROM users
-- WHERE role_id = (SELECT id FROM roles WHERE name = 'Administrator' LIMIT 1)
-- ON DUPLICATE KEY UPDATE id=id;


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;