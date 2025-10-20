-- ================================================
-- Nautilus V6 - Error Logging System
-- Migration: 015_error_logging_system.sql
-- Description: Database tables for error logging and monitoring
-- ================================================

-- Error Logs Table
CREATE TABLE IF NOT EXISTS error_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level ENUM('emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug') NOT NULL,
    message TEXT NOT NULL,
    context JSON,
    url VARCHAR(500),
    ip_address VARCHAR(45),
    user_id INT UNSIGNED,
    user_agent TEXT,
    resolved BOOLEAN DEFAULT FALSE,
    resolved_by INT UNSIGNED,
    resolved_at DATETIME,
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id),
    INDEX idx_resolved (resolved),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Application Performance Monitoring
CREATE TABLE IF NOT EXISTS performance_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL,
    execution_time DECIMAL(10,4) NOT NULL COMMENT 'Execution time in seconds',
    memory_usage INT UNSIGNED COMMENT 'Peak memory usage in bytes',
    query_count INT UNSIGNED DEFAULT 0,
    query_time DECIMAL(10,4) DEFAULT 0,
    user_id INT UNSIGNED,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_endpoint (endpoint),
    INDEX idx_execution_time (execution_time),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Health Checks
CREATE TABLE IF NOT EXISTS system_health_checks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    check_type VARCHAR(50) NOT NULL COMMENT 'database, disk_space, cache, email, etc',
    status ENUM('healthy', 'warning', 'critical') NOT NULL,
    message TEXT,
    details JSON,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_check_type (check_type),
    INDEX idx_status (status),
    INDEX idx_checked_at (checked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Failed Jobs Queue (for background tasks)
CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_type VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    retry_count INT UNSIGNED DEFAULT 0,
    max_retries INT UNSIGNED DEFAULT 3,
    next_retry_at DATETIME,
    INDEX idx_job_type (job_type),
    INDEX idx_next_retry_at (next_retry_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Rate Limiting
CREATE TABLE IF NOT EXISTS api_rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL COMMENT 'IP address, user ID, or API key',
    endpoint VARCHAR(255) NOT NULL,
    requests_count INT UNSIGNED DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    window_end TIMESTAMP NOT NULL,
    blocked_until TIMESTAMP NULL,
    UNIQUE KEY unique_rate_limit (identifier, endpoint, window_start),
    INDEX idx_identifier (identifier),
    INDEX idx_window_end (window_end),
    INDEX idx_blocked_until (blocked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Events Log
CREATE TABLE IF NOT EXISTS security_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL COMMENT 'login_failed, suspicious_activity, brute_force, etc',
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_id INT UNSIGNED,
    user_agent TEXT,
    details JSON,
    action_taken VARCHAR(100) COMMENT 'blocked, logged, notified_admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_severity (severity),
    INDEX idx_ip_address (ip_address),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Session Activity Tracking
CREATE TABLE IF NOT EXISTS session_activity (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128) NOT NULL,
    user_id INT UNSIGNED,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    page_views INT UNSIGNED DEFAULT 1,
    actions_performed INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
