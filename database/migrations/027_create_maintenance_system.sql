-- ============================================================================
-- Migration: Create Equipment Maintenance Tracking System
-- Created: 2024
-- Description: Track maintenance history, schedules, and inspections for rental equipment
-- ============================================================================

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `equipment_maintenance`;
DROP TABLE IF EXISTS `maintenance_schedules`;
DROP TABLE IF EXISTS `maintenance_cost_categories`;

-- Equipment Maintenance Records
CREATE TABLE IF NOT EXISTS equipment_maintenance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    maintenance_type VARCHAR(50) NOT NULL,  -- 'inspection', 'service', 'repair', 'annual_inspection'
    performed_date DATE NOT NULL,
    performed_by BIGINT UNSIGNED,  -- user_id of staff member
    next_maintenance_date DATE,
    hours_at_maintenance DECIMAL(10,2),  -- Equipment usage hours at time of maintenance
    cost DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    parts_replaced TEXT,  -- JSON array of parts
    certification_number VARCHAR(100),  -- For certified inspections
    is_passed TINYINT(1) DEFAULT 1,  -- Did equipment pass inspection?
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES rental_equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_equipment_maintenance_equipment ON equipment_maintenance(equipment_id);
CREATE INDEX IF NOT EXISTS idx_equipment_maintenance_date ON equipment_maintenance(performed_date);
CREATE INDEX IF NOT EXISTS idx_equipment_maintenance_next_date ON equipment_maintenance(next_maintenance_date);
CREATE INDEX IF NOT EXISTS idx_equipment_maintenance_type ON equipment_maintenance(maintenance_type);

-- Scheduled Maintenance
CREATE TABLE IF NOT EXISTS maintenance_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    equipment_id BIGINT UNSIGNED NOT NULL,
    maintenance_type VARCHAR(50) NOT NULL,
    frequency_type VARCHAR(20) NOT NULL,  -- 'days', 'weeks', 'months', 'hours', 'uses'
    frequency_value BIGINT UNSIGNED NOT NULL,  -- Every X days/weeks/months/hours/uses
    last_maintenance_date DATE,
    next_due_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    notify_days_before INT DEFAULT 7,  -- Send reminder X days before due
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES rental_equipment(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_maintenance_schedules_equipment ON maintenance_schedules(equipment_id);
CREATE INDEX IF NOT EXISTS idx_maintenance_schedules_due_date ON maintenance_schedules(next_due_date);
CREATE INDEX IF NOT EXISTS idx_maintenance_schedules_active ON maintenance_schedules(is_active);

-- Maintenance Costs by Category (for analytics)
CREATE TABLE IF NOT EXISTS maintenance_cost_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default maintenance cost categories
INSERT IGNORE INTO maintenance_cost_categories (id, name, description) VALUES
(1, 'Inspection', 'Regular safety and operational inspections'),
(2, 'Service', 'Routine service and preventive maintenance'),
(3, 'Repair', 'Breakdown repairs and emergency fixes'),
(4, 'Parts Replacement', 'Replacement of worn or damaged parts'),
(5, 'Annual Certification', 'Annual certifications and compliance checks'),
(6, 'Cleaning & Sanitization', 'Equipment cleaning and sanitization'),
(7, 'Testing', 'Equipment testing and calibration');

-- ============================================================================
-- Migration Complete
-- ============================================================================
