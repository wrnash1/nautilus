-- Import/Export Tables

-- Export Schedules
CREATE TABLE IF NOT EXISTS export_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    export_type VARCHAR(50) NOT NULL COMMENT 'customers, products, transactions, inventory, etc',
    format VARCHAR(20) DEFAULT 'csv' COMMENT 'csv, excel, json, pdf',
    schedule_type VARCHAR(20) DEFAULT 'daily' COMMENT 'daily, weekly, monthly, custom',
    schedule_config TEXT NULL COMMENT 'JSON schedule configuration',
    filters TEXT NULL COMMENT 'JSON filter configuration',
    email_recipients TEXT NULL COMMENT 'JSON array of email addresses',
    is_active BOOLEAN DEFAULT TRUE,
    last_run_at DATETIME NULL,
    last_run_status VARCHAR(20) NULL,
    last_run_error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_schedule_tenant (tenant_id),
    INDEX idx_schedule_active (is_active),
    INDEX idx_schedule_last_run (last_run_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Export History
CREATE TABLE IF NOT EXISTS export_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NULL,
    tenant_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    record_count INT DEFAULT 0,
    file_size BIGINT DEFAULT 0 COMMENT 'in bytes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    INDEX idx_export_tenant (tenant_id),
    INDEX idx_export_schedule (schedule_id),
    INDEX idx_export_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Import History
CREATE TABLE IF NOT EXISTS import_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    import_type VARCHAR(50) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    total_rows INT DEFAULT 0,
    imported_rows INT DEFAULT 0,
    skipped_rows INT DEFAULT 0,
    errors TEXT NULL COMMENT 'JSON array of errors',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,
    INDEX idx_import_tenant (tenant_id),
    INDEX idx_import_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
