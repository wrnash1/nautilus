SET FOREIGN_KEY_CHECKS=0;

-- Migration: Add Air Fill Deep Dive Tables

-- 1. Customer Equipment Table
CREATE TABLE IF NOT EXISTS customer_equipment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    type ENUM('tank', 'regulator', 'bcd', 'other') DEFAULT 'tank',
    serial_number VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(100),
    model VARCHAR(100),
    size VARCHAR(50),
    material ENUM('aluminum', 'steel', 'composite') DEFAULT 'aluminum',
    last_hydro_date DATE,
    last_vip_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_serial (serial_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Compressors Table
CREATE TABLE IF NOT EXISTS compressors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(100),
    serial_number VARCHAR(100),
    current_hours DECIMAL(10, 2) DEFAULT 0.00,
    last_oil_change_hours DECIMAL(10, 2) DEFAULT 0.00,
    last_filter_change_hours DECIMAL(10, 2) DEFAULT 0.00,
    oil_change_interval INT DEFAULT 100,
    filter_change_interval INT DEFAULT 50,
    status ENUM('active', 'maintenance', 'offline') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Compressor Logs Table
CREATE TABLE IF NOT EXISTS compressor_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compressor_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('fill_run', 'maintenance', 'check') DEFAULT 'fill_run',
    hours_recorded DECIMAL(10, 2) DEFAULT 0.00,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compressor_id) REFERENCES compressors(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Update Air Fills Table to link to Customer Equipment and Compressor
ALTER TABLE air_fills
ADD COLUMN customer_equipment_id BIGINT UNSIGNED NULL AFTER equipment_id,
ADD COLUMN compressor_id BIGINT UNSIGNED NULL AFTER filled_by,
ADD CONSTRAINT fk_air_fills_customer_equip FOREIGN KEY (customer_equipment_id) REFERENCES customer_equipment(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_air_fills_compressor FOREIGN KEY (compressor_id) REFERENCES compressors(id) ON DELETE SET NULL;


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;