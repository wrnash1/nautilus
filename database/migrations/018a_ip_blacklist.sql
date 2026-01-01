-- ================================================
-- Nautilus V6 - IP Blacklist
-- Migration: 018_ip_blacklist.sql
-- Description: IP blocking for security
-- ================================================

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ip_blacklist`;

CREATE TABLE IF NOT EXISTS ip_blacklist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    blocked_until DATETIME,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    UNIQUE KEY unique_ip (ip_address),
    INDEX idx_blocked_until (blocked_until),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
