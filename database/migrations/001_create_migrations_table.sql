-- Migration: 001_create_migrations_table.sql
-- Purpose: Ensure the migrations tracking table exists.
-- Note: The migration runner also checks/creates this, but this file remains for manual usage or consistency.

CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) UNIQUE NOT NULL,
    `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    `error_message` TEXT,
    `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;