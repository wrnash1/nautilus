-- ==========================================
-- Migration: Create Compressor Tracking System
-- Description: Track compressor hours, oil changes, and maintenance
-- ==========================================

-- Compressors Table
CREATE TABLE IF NOT EXISTS compressors (
    id INTEGER PRIMARY KEY ,

    -- Basic Information
    name VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100),
    manufacturer VARCHAR(100),
    model VARCHAR(100),

    -- Purchase & Warranty
    purchase_date DATE,
    purchase_price DECIMAL(10,2),
    warranty_expiration_date DATE,

    -- Current Status
    current_hours DECIMAL(10,2) DEFAULT 0.00,
    last_oil_change_hours DECIMAL(10,2) DEFAULT 0.00,
    last_service_date DATE,

    -- Maintenance Intervals
    oil_change_interval_hours INT DEFAULT 100,
    filter_change_interval_hours INT DEFAULT 50,
    major_service_interval_hours INT DEFAULT 500,

    -- Next Service Due
    next_oil_change_due_hours DECIMAL(10,2),
    next_filter_change_due_hours DECIMAL(10,2),
    next_service_due_hours DECIMAL(10,2),
    next_service_due_date DATE,

    -- Operational Details
    max_pressure_psi INT,
    tank_capacity_cf INT,
    power_rating_hp DECIMAL(5,2),
    voltage VARCHAR(20),

    -- Location & Status
    location VARCHAR(100) COMMENT 'Physical location in shop',
    is_active BOOLEAN DEFAULT TRUE,
    is_operational BOOLEAN DEFAULT TRUE,
    out_of_service_reason TEXT,
    out_of_service_date DATE,

    -- Notes
    notes TEXT,
    internal_notes TEXT COMMENT 'Staff-only notes',

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT UNSIGNED,

    INDEX idx_active (is_active),
    INDEX idx_operational (is_operational),
    INDEX idx_location (location)
);

-- Compressor Logs (Hours, Oil Changes, Service)
CREATE TABLE IF NOT EXISTS compressor_logs (
    id INTEGER PRIMARY KEY ,
    compressor_id INTEGER NOT NULL,

    -- Log Entry Details
    log_type ENUM('hours_logged', 'oil_change', 'filter_change', 'major_service',
                  'repair', 'inspection', 'note', 'out_of_service', 'returned_to_service') NOT NULL,

    -- Hours Information
    hours_before DECIMAL(10,2) COMMENT 'Hours before this entry',
    hours_added DECIMAL(10,2) COMMENT 'Hours added in this entry',
    hours_after DECIMAL(10,2) COMMENT 'Total hours after this entry',

    -- Service Details
    service_description TEXT,
    parts_used TEXT,
    cost DECIMAL(10,2),

    -- Personnel
    performed_by VARCHAR(100) COMMENT 'Technician or staff member name',
    logged_by BIGINT UNSIGNED,

    -- Timestamp
    log_date DATE NOT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Attachments
    receipt_path VARCHAR(500),
    photo_path VARCHAR(500),

    -- Notes
    notes TEXT,

    FOREIGN KEY (compressor_id) REFERENCES compressors(id) ON DELETE CASCADE,

    INDEX idx_compressor (compressor_id),
    INDEX idx_log_type (log_type),
    INDEX idx_log_date (log_date)
);

-- Compressor Maintenance Schedule
CREATE TABLE IF NOT EXISTS compressor_maintenance_schedule (
    id INTEGER PRIMARY KEY ,
    compressor_id INTEGER NOT NULL,

    -- Maintenance Details
    maintenance_type ENUM('oil_change', 'filter_change', 'major_service', 'inspection', 'custom') NOT NULL,
    description TEXT NOT NULL,

    -- Schedule
    scheduled_date DATE NOT NULL,
    completed_date DATE NULL,

    -- Status
    status ENUM('scheduled', 'in_progress', 'completed', 'skipped', 'cancelled') DEFAULT 'scheduled',

    -- Assignment
    assigned_to INTEGER COMMENT 'User assigned to perform maintenance',
    completed_by BIGINT UNSIGNED,

    -- Details
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    hours_estimate DECIMAL(4,2) COMMENT 'Estimated time in hours',
    hours_actual DECIMAL(4,2),

    -- Notes
    notes TEXT,
    completion_notes TEXT,

    -- Reminders
    reminder_sent BOOLEAN DEFAULT FALSE,
    reminder_sent_at TIMESTAMP NULL,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (compressor_id) REFERENCES compressors(id) ON DELETE CASCADE,

    INDEX idx_compressor (compressor_id),
    INDEX idx_scheduled_date (scheduled_date),
    INDEX idx_status (status)
);

-- Compressor Alerts
CREATE TABLE IF NOT EXISTS compressor_alerts (
    id INTEGER PRIMARY KEY ,
    compressor_id INTEGER NOT NULL,

    -- Alert Details
    alert_type ENUM('oil_change_due', 'filter_change_due', 'service_due',
                    'high_hours', 'out_of_service', 'custom') NOT NULL,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'warning',
    message TEXT NOT NULL,

    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    is_acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by INTEGER NULL,
    acknowledged_at TIMESTAMP NULL,

    -- Auto-Dismiss
    auto_dismiss_after_hours DECIMAL(10,2) COMMENT 'Auto dismiss when compressor reaches these hours',

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (compressor_id) REFERENCES compressors(id) ON DELETE CASCADE,

    INDEX idx_compressor (compressor_id),
    INDEX idx_active (is_active),
    INDEX idx_severity (severity)
);

-- Compressor Parts Inventory
CREATE TABLE IF NOT EXISTS compressor_parts (
    id INTEGER PRIMARY KEY ,

    -- Part Information
    part_number VARCHAR(100) NOT NULL,
    part_name VARCHAR(200) NOT NULL,
    description TEXT,
    manufacturer VARCHAR(100),

    -- Compatibility
    compatible_models TEXT COMMENT 'Comma-separated list of compatible models',

    -- Inventory
    quantity_on_hand INT DEFAULT 0,
    minimum_quantity INT DEFAULT 1,
    unit_cost DECIMAL(10,2),

    -- Supplier
    supplier_name VARCHAR(200),
    supplier_part_number VARCHAR(100),
    lead_time_days INT,

    -- Location
    storage_location VARCHAR(100),

    -- Status
    is_active BOOLEAN DEFAULT TRUE,

    -- Audit
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_part_number (part_number),
    INDEX idx_quantity (quantity_on_hand)
);

-- Link parts used in maintenance
CREATE TABLE IF NOT EXISTS compressor_log_parts (
    id INTEGER PRIMARY KEY ,
    log_id INTEGER NOT NULL,
    part_id INTEGER NOT NULL,
    quantity_used INT NOT NULL DEFAULT 1,

    FOREIGN KEY (log_id) REFERENCES compressor_logs(id) ON DELETE CASCADE,

    INDEX idx_log (log_id),
    INDEX idx_part (part_id)
);

-- NOTE: View 'compressor_status_dashboard' removed from migration.
-- CREATE VIEW statements cause issues with mysqli::multi_query() execution.
-- The view can be created manually after installation if needed.

-- Insert Sample Compressor
INSERT INTO compressors (
    name, manufacturer, model, serial_number,
    current_hours, last_oil_change_hours,
    oil_change_interval_hours, filter_change_interval_hours, major_service_interval_hours,
    location, is_active, is_operational,
    max_pressure_psi, tank_capacity_cf, power_rating_hp
) VALUES (
    'Main Compressor #1',
    'Bauer',
    'Junior II',
    'BJ2-2024-001',
    250.5,
    200.0,
    100,
    50,
    500,
    'Fill Station',
    TRUE,
    TRUE,
    4500,
    3000,
    5.0
);

-- Set the next service due hours for the sample compressor
UPDATE compressors
SET
    next_oil_change_due_hours = last_oil_change_hours + oil_change_interval_hours,
    next_filter_change_due_hours = current_hours + filter_change_interval_hours,
    next_service_due_hours = current_hours + major_service_interval_hours
WHERE id = 1;

-- Insert Sample Compressor Parts
INSERT INTO compressor_parts (part_number, part_name, description, manufacturer, quantity_on_hand, minimum_quantity, unit_cost) VALUES
('BOI-100', 'Compressor Oil - 1 Gallon', 'High-temperature synthetic compressor oil', 'Bauer', 5, 2, 45.00),
('FIL-001', 'Intake Filter Cartridge', 'High-efficiency intake filter', 'Bauer', 10, 3, 25.00),
('FIL-002', 'Separator Filter', 'Oil separator filter element', 'Bauer', 8, 2, 35.00),
('PST-001', 'Piston Ring Set', 'Complete piston ring replacement set', 'Bauer', 2, 1, 125.00),
('VLV-001', 'Safety Valve', 'Pressure relief safety valve', 'Bauer', 3, 1, 75.00);

-- Comments
ALTER TABLE compressors COMMENT = 'Dive shop air compressor inventory';
ALTER TABLE compressor_logs COMMENT = 'Compressor usage, maintenance, and service logs';
ALTER TABLE compressor_maintenance_schedule COMMENT = 'Scheduled maintenance tasks';
ALTER TABLE compressor_alerts COMMENT = 'Active alerts for compressor maintenance';
ALTER TABLE compressor_parts COMMENT = 'Parts inventory for compressor maintenance';
ALTER TABLE compressor_log_parts COMMENT = 'Parts used in each maintenance log entry';
