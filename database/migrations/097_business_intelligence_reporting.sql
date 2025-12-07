-- =============================================
-- Migration 097: Business Intelligence & Advanced Reporting
-- =============================================
-- This migration creates a comprehensive business intelligence and reporting system
-- with KPIs, dashboards, custom reports, data visualization, and analytics

-- =============================================
-- Report Templates & Custom Reports
-- =============================================

-- Pre-built and custom report templates
CREATE TABLE IF NOT EXISTS `report_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `report_name` VARCHAR(200) NOT NULL,
    `report_category` ENUM('financial', 'sales', 'inventory', 'customer', 'employee', 'operations', 'marketing', 'custom') NOT NULL,
    `report_type` ENUM('summary', 'detail', 'comparison', 'trend', 'forecast', 'custom_query') NOT NULL,
    `description` TEXT NULL,

    -- Report Configuration
    `is_system_template` BOOLEAN DEFAULT FALSE COMMENT 'Pre-built by system vs user-created',
    `query_template` TEXT NULL COMMENT 'SQL query template with placeholders',
    `parameters` JSON NULL COMMENT 'Report parameters and filters',
    `grouping_fields` JSON NULL,
    `sorting_config` JSON NULL,
    `aggregations` JSON NULL COMMENT 'SUM, AVG, COUNT, etc.',

    -- Visualization
    `default_chart_type` ENUM('table', 'bar', 'line', 'pie', 'donut', 'area', 'scatter', 'heatmap', 'gauge', 'funnel') DEFAULT 'table',
    `visualization_config` JSON NULL,

    -- Scheduling
    `can_be_scheduled` BOOLEAN DEFAULT TRUE,
    `default_schedule` VARCHAR(100) NULL COMMENT 'daily, weekly, monthly, quarterly',

    -- Access Control
    `required_permission` VARCHAR(100) NULL,
    `is_public` BOOLEAN DEFAULT FALSE,
    `created_by` INT UNSIGNED NULL,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_category (`report_category`),
    INDEX idx_type (`report_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generated report instances
CREATE TABLE IF NOT EXISTS `generated_reports` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `template_id` INT UNSIGNED NULL,
    `report_name` VARCHAR(200) NOT NULL,

    -- Generation Details
    `generated_by` INT UNSIGNED NULL,
    `generation_method` ENUM('manual', 'scheduled', 'api', 'webhook') DEFAULT 'manual',
    `parameters_used` JSON NULL,
    `date_range_start` DATE NULL,
    `date_range_end` DATE NULL,

    -- Data
    `result_data` LONGTEXT NULL COMMENT 'JSON data or CSV',
    `result_format` ENUM('json', 'csv', 'excel', 'pdf', 'html') DEFAULT 'json',
    `row_count` INT DEFAULT 0,
    `file_path` VARCHAR(500) NULL,
    `file_size_bytes` BIGINT NULL,

    -- Performance
    `execution_time_ms` INT NULL,
    `query_executed` TEXT NULL,

    -- Status
    `status` ENUM('generating', 'completed', 'failed', 'cancelled') DEFAULT 'generating',
    `error_message` TEXT NULL,

    -- Sharing
    `is_shared` BOOLEAN DEFAULT FALSE,
    `share_token` VARCHAR(64) NULL,
    `share_expires_at` TIMESTAMP NULL,

    `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NULL COMMENT 'Auto-delete old reports',

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_template (`template_id`),
    INDEX idx_generated_by (`generated_by`),
    INDEX idx_status (`status`),
    INDEX idx_share_token (`share_token`),
    FOREIGN KEY (`template_id`) REFERENCES `report_templates`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scheduled reports
CREATE TABLE IF NOT EXISTS `scheduled_reports` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `template_id` INT UNSIGNED NOT NULL,
    `schedule_name` VARCHAR(200) NOT NULL,

    -- Schedule Configuration
    `frequency` ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'custom_cron') NOT NULL,
    `cron_expression` VARCHAR(100) NULL COMMENT 'For custom schedules',
    `day_of_week` TINYINT NULL COMMENT '0=Sunday, 6=Saturday',
    `day_of_month` TINYINT NULL,
    `time_of_day` TIME DEFAULT '08:00:00',
    `timezone` VARCHAR(50) DEFAULT 'America/New_York',

    -- Report Parameters
    `parameters` JSON NULL,
    `date_range_type` ENUM('yesterday', 'last_7_days', 'last_30_days', 'last_month', 'last_quarter', 'last_year', 'custom') DEFAULT 'last_30_days',

    -- Delivery
    `delivery_method` ENUM('email', 'save_only', 'both') DEFAULT 'email',
    `recipients` JSON NULL COMMENT 'Email addresses',
    `email_subject` VARCHAR(200) NULL,
    `email_body` TEXT NULL,
    `attach_as_format` ENUM('pdf', 'excel', 'csv', 'html') DEFAULT 'pdf',

    -- Execution
    `last_run_at` TIMESTAMP NULL,
    `next_run_at` TIMESTAMP NULL,
    `last_run_status` ENUM('success', 'failed', 'skipped') NULL,
    `consecutive_failures` INT DEFAULT 0,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_next_run (`next_run_at`),
    INDEX idx_active (`is_active`),
    FOREIGN KEY (`template_id`) REFERENCES `report_templates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Dashboards & KPIs
-- =============================================

-- Custom dashboards
-- Drop the old dashboards table if it exists (from migration 013) and recreate with new schema
DROP TABLE IF EXISTS `dashboards`;
CREATE TABLE `dashboards` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `dashboard_name` VARCHAR(200) NOT NULL,
    `dashboard_category` ENUM('overview', 'sales', 'financial', 'operations', 'customer', 'employee', 'inventory', 'custom') DEFAULT 'custom',
    `description` TEXT NULL,

    -- Configuration
    `layout_config` JSON NULL COMMENT 'Grid layout, widget positions',
    `is_system_dashboard` BOOLEAN DEFAULT FALSE,
    `is_default` BOOLEAN DEFAULT FALSE COMMENT 'Load by default for user role',

    -- Access Control
    `visibility` ENUM('private', 'shared', 'public', 'role_based') DEFAULT 'private',
    `shared_with_roles` JSON NULL COMMENT 'Array of role IDs',
    `shared_with_users` JSON NULL COMMENT 'Array of user IDs',

    -- Refresh Settings
    `auto_refresh` BOOLEAN DEFAULT FALSE,
    `refresh_interval_seconds` INT DEFAULT 300,

    `created_by` INT UNSIGNED NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_category (`dashboard_category`),
    INDEX idx_created_by (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Dashboard widgets
-- Drop the old dashboard_widgets table if it exists (from migration 026) and recreate with new schema
DROP TABLE IF EXISTS `dashboard_widgets`;
CREATE TABLE `dashboard_widgets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dashboard_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `widget_name` VARCHAR(200) NOT NULL,
    `widget_type` ENUM('kpi', 'chart', 'table', 'gauge', 'map', 'calendar', 'list', 'html', 'iframe') NOT NULL,

    -- Data Source
    `data_source_type` ENUM('report_template', 'custom_query', 'api', 'static') DEFAULT 'report_template',
    `report_template_id` INT UNSIGNED NULL,
    `custom_query` TEXT NULL,
    `api_endpoint` VARCHAR(500) NULL,
    `static_data` JSON NULL,

    -- Widget Configuration
    `chart_type` ENUM('bar', 'line', 'pie', 'donut', 'area', 'scatter', 'heatmap', 'gauge', 'funnel', 'table') NULL,
    `visualization_config` JSON NULL,
    `filters` JSON NULL,
    `date_range` VARCHAR(100) DEFAULT 'last_30_days',

    -- Layout
    `position_row` INT DEFAULT 0,
    `position_col` INT DEFAULT 0,
    `width` INT DEFAULT 4 COMMENT 'Grid columns (1-12)',
    `height` INT DEFAULT 4 COMMENT 'Grid rows',

    -- Refresh
    `auto_refresh` BOOLEAN DEFAULT TRUE,
    `refresh_interval_seconds` INT DEFAULT 300,
    `last_refreshed_at` TIMESTAMP NULL,

    -- Caching
    `cache_enabled` BOOLEAN DEFAULT TRUE,
    `cache_ttl_seconds` INT DEFAULT 300,
    `cached_data` LONGTEXT NULL,
    `cached_at` TIMESTAMP NULL,

    `display_order` INT DEFAULT 0,
    `is_visible` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_dashboard (`dashboard_id`),
    INDEX idx_tenant (`tenant_id`),
    INDEX idx_report (`report_template_id`),
    FOREIGN KEY (`dashboard_id`) REFERENCES `dashboards`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`report_template_id`) REFERENCES `report_templates`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Key Performance Indicators
CREATE TABLE IF NOT EXISTS `kpi_definitions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `kpi_name` VARCHAR(200) NOT NULL,
    `kpi_category` ENUM('financial', 'sales', 'customer', 'operational', 'employee', 'marketing', 'inventory') NOT NULL,
    `description` TEXT NULL,

    -- Calculation
    `calculation_type` ENUM('sum', 'average', 'count', 'ratio', 'percentage', 'custom_formula') NOT NULL,
    `calculation_formula` TEXT NULL COMMENT 'SQL or formula',
    `data_source_query` TEXT NULL,
    `aggregation_period` ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly', 'real_time') DEFAULT 'daily',

    -- Display
    `unit_type` ENUM('currency', 'percentage', 'number', 'count', 'time', 'ratio') DEFAULT 'number',
    `decimal_places` TINYINT DEFAULT 0,
    `prefix` VARCHAR(10) NULL COMMENT '$, #, etc.',
    `suffix` VARCHAR(10) NULL COMMENT '%, items, etc.',

    -- Targets & Thresholds
    `has_target` BOOLEAN DEFAULT FALSE,
    `target_value` DECIMAL(15, 2) NULL,
    `target_type` ENUM('minimum', 'maximum', 'exact', 'range') NULL,
    `green_threshold` DECIMAL(15, 2) NULL,
    `yellow_threshold` DECIMAL(15, 2) NULL,
    `red_threshold` DECIMAL(15, 2) NULL,

    -- Trend Analysis
    `track_trend` BOOLEAN DEFAULT TRUE,
    `comparison_period` ENUM('previous_period', 'same_period_last_year', 'custom') DEFAULT 'previous_period',

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_category (`kpi_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KPI values (historical tracking)
CREATE TABLE IF NOT EXISTS `kpi_values` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `kpi_id` INT UNSIGNED NOT NULL,

    -- Time Period
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `period_type` ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,

    -- Values
    `actual_value` DECIMAL(15, 4) NOT NULL,
    `target_value` DECIMAL(15, 4) NULL,
    `variance` DECIMAL(15, 4) NULL COMMENT 'Difference from target',
    `variance_percentage` DECIMAL(10, 2) NULL,

    -- Trend
    `previous_period_value` DECIMAL(15, 4) NULL,
    `change_value` DECIMAL(15, 4) NULL,
    `change_percentage` DECIMAL(10, 2) NULL,
    `trend_direction` ENUM('up', 'down', 'flat') NULL,

    -- Status
    `status` ENUM('green', 'yellow', 'red', 'neutral') DEFAULT 'neutral',

    -- Metadata
    `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `calculation_notes` TEXT NULL,

    UNIQUE KEY unique_kpi_period (`tenant_id`, `kpi_id`, `period_start`, `period_end`),
    INDEX idx_kpi (`kpi_id`),
    INDEX idx_period (`period_start`, `period_end`),
    FOREIGN KEY (`kpi_id`) REFERENCES `kpi_definitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Data Analytics & Insights
-- =============================================

-- Customer analytics and segmentation
CREATE TABLE IF NOT EXISTS `customer_analytics` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,

    -- Calculated Metrics
    `total_bookings` INT DEFAULT 0,
    `total_revenue` DECIMAL(10, 2) DEFAULT 0.00,
    `average_booking_value` DECIMAL(10, 2) DEFAULT 0.00,
    `lifetime_value` DECIMAL(10, 2) DEFAULT 0.00,

    -- Frequency
    `first_booking_date` DATE NULL,
    `last_booking_date` DATE NULL,
    `days_since_last_booking` INT NULL,
    `booking_frequency` DECIMAL(5, 2) NULL COMMENT 'Bookings per month',

    -- Behavior
    `preferred_booking_method` ENUM('online', 'phone', 'in_person', 'mobile_app') NULL,
    `preferred_payment_method` VARCHAR(50) NULL,
    `avg_days_advance_booking` INT NULL,
    `cancellation_rate` DECIMAL(5, 2) NULL,

    -- Segmentation
    `customer_segment` ENUM('vip', 'loyal', 'regular', 'occasional', 'new', 'at_risk', 'lost') NULL,
    `rfm_score` VARCHAR(10) NULL COMMENT 'Recency-Frequency-Monetary',
    `churn_risk_score` DECIMAL(5, 2) NULL COMMENT '0-100',
    `next_booking_probability` DECIMAL(5, 2) NULL,

    -- Preferences
    `favorite_activities` JSON NULL,
    `favorite_instructors` JSON NULL,
    `typical_booking_days` JSON NULL COMMENT 'Weekday preferences',

    -- Last Calculated
    `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `calculation_period_start` DATE NULL,
    `calculation_period_end` DATE NULL,

    UNIQUE KEY unique_tenant_customer (`tenant_id`, `customer_id`),
    INDEX idx_segment (`customer_segment`),
    INDEX idx_ltv (`lifetime_value`),
    INDEX idx_last_booking (`last_booking_date`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product performance analytics
CREATE TABLE IF NOT EXISTS `product_analytics` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NULL,
    `product_category` VARCHAR(100) NULL,
    `analysis_period_start` DATE NOT NULL,
    `analysis_period_end` DATE NOT NULL,

    -- Sales Performance
    `units_sold` INT DEFAULT 0,
    `revenue` DECIMAL(10, 2) DEFAULT 0.00,
    `cost` DECIMAL(10, 2) DEFAULT 0.00,
    `profit` DECIMAL(10, 2) DEFAULT 0.00,
    `profit_margin` DECIMAL(5, 2) NULL,

    -- Inventory Metrics
    `avg_inventory_level` DECIMAL(10, 2) NULL,
    `stockout_days` INT DEFAULT 0,
    `inventory_turnover` DECIMAL(5, 2) NULL,
    `days_of_inventory` DECIMAL(5, 2) NULL,

    -- Performance
    `return_rate` DECIMAL(5, 2) NULL,
    `avg_sale_price` DECIMAL(10, 2) NULL,
    `discount_percentage` DECIMAL(5, 2) NULL,

    -- Ranking
    `revenue_rank` INT NULL,
    `profit_rank` INT NULL,
    `velocity_rank` INT NULL,

    -- ABC Analysis
    `abc_classification` ENUM('A', 'B', 'C', 'D') NULL COMMENT 'Pareto analysis',

    `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_product (`product_id`),
    INDEX idx_period (`analysis_period_start`, `analysis_period_end`),
    INDEX idx_classification (`abc_classification`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Revenue analytics
CREATE TABLE IF NOT EXISTS `revenue_analytics` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `period_type` ENUM('daily', 'weekly', 'monthly', 'quarterly', 'yearly') NOT NULL,

    -- Revenue Breakdown
    `total_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `course_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `retail_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `rental_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `service_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `travel_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `membership_revenue` DECIMAL(12, 2) DEFAULT 0.00,
    `other_revenue` DECIMAL(12, 2) DEFAULT 0.00,

    -- Costs
    `total_cost` DECIMAL(12, 2) DEFAULT 0.00,
    `cogs` DECIMAL(12, 2) DEFAULT 0.00 COMMENT 'Cost of Goods Sold',
    `labor_cost` DECIMAL(12, 2) DEFAULT 0.00,
    `operating_expenses` DECIMAL(12, 2) DEFAULT 0.00,

    -- Profitability
    `gross_profit` DECIMAL(12, 2) DEFAULT 0.00,
    `gross_margin` DECIMAL(5, 2) NULL,
    `net_profit` DECIMAL(12, 2) DEFAULT 0.00,
    `net_margin` DECIMAL(5, 2) NULL,

    -- Transactions
    `total_transactions` INT DEFAULT 0,
    `avg_transaction_value` DECIMAL(10, 2) NULL,
    `unique_customers` INT DEFAULT 0,

    -- Comparisons
    `previous_period_revenue` DECIMAL(12, 2) NULL,
    `revenue_growth` DECIMAL(5, 2) NULL COMMENT 'Percentage',
    `yoy_revenue` DECIMAL(12, 2) NULL COMMENT 'Year over year',
    `yoy_growth` DECIMAL(5, 2) NULL,

    `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_period (`tenant_id`, `period_start`, `period_end`, `period_type`),
    INDEX idx_tenant (`tenant_id`),
    INDEX idx_period_type (`period_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Data Export & Integration
-- =============================================

-- Data export jobs
CREATE TABLE IF NOT EXISTS `data_exports` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `export_name` VARCHAR(200) NOT NULL,
    `export_type` ENUM('customers', 'bookings', 'inventory', 'financial', 'custom_query', 'full_backup') NOT NULL,

    -- Configuration
    `export_format` ENUM('csv', 'excel', 'json', 'xml', 'sql', 'pdf') DEFAULT 'csv',
    `date_range_start` DATE NULL,
    `date_range_end` DATE NULL,
    `filters` JSON NULL,
    `columns_to_export` JSON NULL,
    `custom_query` TEXT NULL,

    -- Execution
    `requested_by` INT UNSIGNED NULL,
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,

    -- Results
    `status` ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    `file_path` VARCHAR(500) NULL,
    `file_size_bytes` BIGINT NULL,
    `row_count` INT NULL,
    `download_url` VARCHAR(500) NULL,
    `download_expires_at` TIMESTAMP NULL,

    -- Error Handling
    `error_message` TEXT NULL,
    `retry_count` INT DEFAULT 0,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_status (`status`),
    INDEX idx_requested_by (`requested_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics API access logs
CREATE TABLE IF NOT EXISTS `analytics_api_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `endpoint` VARCHAR(200) NOT NULL,
    `method` VARCHAR(10) NOT NULL,

    -- Request
    `query_params` JSON NULL,
    `request_body` TEXT NULL,
    `requested_by` INT UNSIGNED NULL,
    `api_token_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,

    -- Response
    `status_code` INT NULL,
    `response_time_ms` INT NULL,
    `records_returned` INT NULL,

    -- Caching
    `cache_hit` BOOLEAN DEFAULT FALSE,

    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tenant (`tenant_id`),
    INDEX idx_endpoint (`endpoint`),
    INDEX idx_requested_at (`requested_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Pre-seed Sample Data
-- =============================================

-- Sample KPI Definitions
INSERT INTO `kpi_definitions` (
    `tenant_id`, `kpi_name`, `kpi_category`, `description`,
    `calculation_type`, `calculation_formula`, `aggregation_period`,
    `unit_type`, `decimal_places`, `prefix`,
    `has_target`, `target_value`, `green_threshold`, `yellow_threshold`, `red_threshold`
) VALUES
(1, 'Monthly Revenue', 'financial', 'Total revenue for the month',
    'sum', 'SELECT SUM(total_amount) FROM bookings WHERE status = "completed"', 'monthly',
    'currency', 2, '$', TRUE, 50000.00, 50000.00, 40000.00, 30000.00),
(1, 'Customer Acquisition Rate', 'customer', 'New customers acquired per month',
    'count', 'SELECT COUNT(*) FROM customers WHERE created_at >= ?', 'monthly',
    'number', 0, NULL, TRUE, 25.00, 25.00, 15.00, 10.00),
(1, 'Average Booking Value', 'sales', 'Average value per booking',
    'average', 'SELECT AVG(total_amount) FROM bookings', 'daily',
    'currency', 2, '$', TRUE, 500.00, 500.00, 400.00, 300.00),
(1, 'Course Completion Rate', 'operational', 'Percentage of courses successfully completed',
    'percentage', 'SELECT (COUNT(CASE WHEN status = "completed" THEN 1 END) / COUNT(*)) * 100 FROM courses', 'monthly',
    'percentage', 1, NULL, TRUE, 95.00, 95.00, 85.00, 75.00),
(1, 'Inventory Turnover', 'inventory', 'How quickly inventory is sold and replaced',
    'ratio', 'SELECT (total_sales / avg_inventory) FROM inventory_metrics', 'monthly',
    'ratio', 2, NULL, TRUE, 6.00, 6.00, 4.00, 2.00),
(1, 'Customer Retention Rate', 'customer', 'Percentage of customers who return',
    'percentage', 'SELECT (returning_customers / total_customers) * 100', 'quarterly',
    'percentage', 1, NULL, TRUE, 75.00, 75.00, 60.00, 50.00);

-- Sample Report Templates
INSERT INTO `report_templates` (
    `tenant_id`, `report_name`, `report_category`, `report_type`, `description`,
    `is_system_template`, `default_chart_type`, `can_be_scheduled`
) VALUES
(1, 'Daily Sales Summary', 'sales', 'summary', 'Overview of daily sales performance', TRUE, 'table', TRUE),
(1, 'Monthly Revenue by Category', 'financial', 'summary', 'Revenue breakdown by category', TRUE, 'pie', TRUE),
(1, 'Customer Lifetime Value Report', 'customer', 'detail', 'Detailed customer value analysis', TRUE, 'table', TRUE),
(1, 'Inventory Stock Levels', 'inventory', 'detail', 'Current stock levels across all locations', TRUE, 'table', TRUE),
(1, 'Employee Performance', 'employee', 'comparison', 'Compare employee performance metrics', TRUE, 'bar', TRUE),
(1, 'Course Booking Trends', 'sales', 'trend', '12-month course booking trends', TRUE, 'line', TRUE),
(1, 'Top Selling Products', 'sales', 'summary', 'Best performing products by revenue', TRUE, 'bar', TRUE),
(1, 'Customer Acquisition Cost', 'marketing', 'trend', 'Monthly customer acquisition costs', TRUE, 'line', TRUE);

-- Sample Dashboard
INSERT INTO `dashboards` (
    `tenant_id`, `dashboard_name`, `dashboard_category`, `description`,
    `is_system_dashboard`, `is_default`, `visibility`
) VALUES
(1, 'Executive Overview', 'overview', 'High-level business metrics for management', TRUE, TRUE, 'role_based'),
(1, 'Sales Performance', 'sales', 'Detailed sales analytics and trends', TRUE, FALSE, 'shared'),
(1, 'Operations Dashboard', 'operations', 'Daily operational metrics', TRUE, FALSE, 'role_based');

-- Sample Dashboard Widgets for Executive Overview
INSERT INTO `dashboard_widgets` (
    `dashboard_id`, `tenant_id`, `widget_name`, `widget_type`, `data_source_type`,
    `chart_type`, `position_row`, `position_col`, `width`, `height`
) VALUES
(1, 1, 'Monthly Revenue', 'kpi', 'report_template', 'gauge', 0, 0, 3, 2),
(1, 1, 'New Customers', 'kpi', 'report_template', 'gauge', 0, 3, 3, 2),
(1, 1, 'Active Bookings', 'kpi', 'report_template', 'gauge', 0, 6, 3, 2),
(1, 1, 'Customer Satisfaction', 'kpi', 'report_template', 'gauge', 0, 9, 3, 2),
(1, 1, 'Revenue Trend', 'chart', 'report_template', 'line', 2, 0, 6, 4),
(1, 1, 'Revenue by Category', 'chart', 'report_template', 'pie', 2, 6, 6, 4),
(1, 1, 'Top Customers', 'table', 'report_template', 'table', 6, 0, 6, 4),
(1, 1, 'Recent Bookings', 'table', 'report_template', 'table', 6, 6, 6, 4);

-- =============================================
-- Migration Complete
-- =============================================
-- This migration adds comprehensive business intelligence:
-- - Custom report builder
-- - Scheduled reports
-- - Interactive dashboards
-- - KPI tracking and monitoring
-- - Customer analytics and segmentation
-- - Product performance analysis
-- - Revenue analytics
-- - Data export capabilities
-- - API access logging
