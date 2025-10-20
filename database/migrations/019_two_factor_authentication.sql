-- ================================================
-- Nautilus V6 - Two-Factor Authentication
-- Migration: 019_two_factor_authentication.sql
-- Description: 2FA tables and user settings
-- ================================================

-- Two-Factor Authentication Settings
CREATE TABLE IF NOT EXISTS user_two_factor (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    secret TEXT NOT NULL COMMENT 'Encrypted TOTP secret',
    backup_codes TEXT COMMENT 'Encrypted backup codes JSON array',
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Two-Factor Verification Logs
CREATE TABLE IF NOT EXISTS two_factor_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    success BOOLEAN NOT NULL,
    method ENUM('totp', 'backup_code') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_success (success),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add 2FA requirement column to users table (if not exists)
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS two_factor_required BOOLEAN DEFAULT FALSE COMMENT 'Force 2FA for this user';
