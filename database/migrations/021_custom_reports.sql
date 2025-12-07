-- ================================================
-- Nautilus V6 - Custom Report Builder
-- Migration: 021_custom_reports.sql
-- Description: Tables for custom report functionality
-- ================================================

-- Custom Reports
CREATE TABLE IF NOT EXISTS custom_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    table_name VARCHAR(100) NOT NULL,
    columns JSON NOT NULL COMMENT 'Selected columns with optional aggregates',
    filters JSON COMMENT 'Filter conditions',
    grouping JSON COMMENT 'Group by columns',
    sorting JSON COMMENT 'Sort order',
    chart_type VARCHAR(50) COMMENT 'bar, line, pie, table, etc',
    is_public BOOLEAN DEFAULT FALSE,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created_by (created_by),
    INDEX idx_is_public (is_public),
    INDEX idx_table_name (table_name),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Executions Log
CREATE TABLE IF NOT EXISTS report_executions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id INT UNSIGNED NOT NULL,
    executed_by INT UNSIGNED,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    INDEX idx_executed_at (executed_at),
    FOREIGN KEY (report_id) REFERENCES custom_reports(id) ON DELETE CASCADE,
    FOREIGN KEY (executed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scheduled Reports
CREATE TABLE IF NOT EXISTS scheduled_reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id INT UNSIGNED NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly') NOT NULL,
    schedule_time TIME DEFAULT '09:00:00',
    day_of_week TINYINT COMMENT 'For weekly reports (0=Sunday, 6=Saturday)',
    day_of_month TINYINT COMMENT 'For monthly reports (1-31)',
    recipients JSON COMMENT 'Email addresses to send report',
    format ENUM('csv', 'pdf', 'excel') DEFAULT 'csv',
    last_run_at DATETIME,
    next_run_at DATETIME,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    INDEX idx_next_run (next_run_at, is_active),
    FOREIGN KEY (report_id) REFERENCES custom_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Favorites (user bookmarks)
CREATE TABLE IF NOT EXISTS report_favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    report_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, report_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (report_id) REFERENCES custom_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
