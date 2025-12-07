-- Backup System Tables
-- Track backup history and scheduled backups

-- Backup Log
CREATE TABLE IF NOT EXISTS backup_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    backup_type ENUM('database', 'files', 'complete') NOT NULL,
    filename VARCHAR(255),
    filepath VARCHAR(500),
    filesize BIGINT,
    status ENUM('completed', 'failed', 'in_progress') DEFAULT 'completed',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_backup_type (backup_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scheduled Backups
CREATE TABLE IF NOT EXISTS scheduled_backups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    backup_name VARCHAR(100) NOT NULL,
    backup_type ENUM('database', 'files', 'complete') NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly') NOT NULL,
    schedule_time TIME NOT NULL,
    day_of_week TINYINT NULL COMMENT '0=Sunday, 6=Saturday for weekly backups',
    day_of_month TINYINT NULL COMMENT '1-31 for monthly backups',
    is_active BOOLEAN DEFAULT TRUE,
    last_run_at TIMESTAMP NULL,
    next_run_at TIMESTAMP NULL,
    retention_days INT DEFAULT 30,
    notification_email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_is_active (is_active),
    INDEX idx_next_run_at (next_run_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backup Storage Locations (for cloud storage support)
CREATE TABLE IF NOT EXISTS backup_storage_locations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    location_name VARCHAR(100) NOT NULL,
    storage_type ENUM('local', 's3', 'ftp', 'sftp', 'dropbox', 'google_drive') NOT NULL,
    configuration JSON COMMENT 'Storage-specific configuration',
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_storage_type (storage_type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default local storage location for each tenant
INSERT INTO backup_storage_locations (tenant_id, location_name, storage_type, configuration, is_default)
SELECT id, 'Local Server', 'local', '{"path": "/storage/backups"}', TRUE
FROM tenants
WHERE NOT EXISTS (
    SELECT 1 FROM backup_storage_locations
    WHERE tenant_id = tenants.id AND storage_type = 'local'
);
