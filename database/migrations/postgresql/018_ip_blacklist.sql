-- ================================================
-- Nautilus V6 - IP Blacklist
-- Migration: 018_ip_blacklist.sql
-- Description: IP blocking for security
-- ================================================

CREATE TABLE IF NOT EXISTS ip_blacklist (
    id INTEGER  PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    blocked_until TIMESTAMP,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    UNIQUE KEY unique_ip (ip_address),
    INDEX idx_blocked_until (blocked_until),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
