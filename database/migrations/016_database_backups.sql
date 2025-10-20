-- ================================================
-- Nautilus V6 - Database Backup System
-- Migration: 016_database_backups.sql
-- Description: Tables for managing database backups
-- ================================================

CREATE TABLE IF NOT EXISTS database_backups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL COMMENT 'Size in bytes',
    type ENUM('manual', 'automatic', 'pre_restore') NOT NULL DEFAULT 'manual',
    created_by INT UNSIGNED,
    status ENUM('in_progress', 'completed', 'failed', 'restored') NOT NULL DEFAULT 'completed',
    restored_at DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
