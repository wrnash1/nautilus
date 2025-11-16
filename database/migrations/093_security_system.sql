-- =====================================================
-- Security System
-- Cameras, access control, alarms, and incident tracking
-- =====================================================

-- Security Cameras
CREATE TABLE IF NOT EXISTS `security_cameras` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `camera_name` VARCHAR(255) NOT NULL,
    `camera_location` VARCHAR(255) NOT NULL,
    `location_id` INT UNSIGNED NULL COMMENT 'Link to inventory_locations',

    -- Camera Details
    `camera_type` ENUM('ip', 'analog', 'ptz', 'dome', 'bullet', 'hidden') NOT NULL DEFAULT 'ip',
    `manufacturer` VARCHAR(100) NULL,
    `model` VARCHAR(100) NULL,
    `serial_number` VARCHAR(100) NULL,

    -- Network
    `ip_address` VARCHAR(45) NULL,
    `mac_address` VARCHAR(17) NULL,
    `port` INT NULL,
    `rtsp_url` VARCHAR(500) NULL,
    `web_interface_url` VARCHAR(500) NULL,

    -- Credentials (encrypted)
    `username` VARCHAR(255) NULL,
    `password` VARCHAR(255) NULL COMMENT 'Encrypted',

    -- Capabilities
    `has_ptz` BOOLEAN DEFAULT FALSE,
    `has_audio` BOOLEAN DEFAULT FALSE,
    `has_night_vision` BOOLEAN DEFAULT FALSE,
    `has_motion_detection` BOOLEAN DEFAULT FALSE,
    `resolution` VARCHAR(20) NULL COMMENT 'e.g., 1080p, 4K',
    `fps` INT DEFAULT 30,

    -- Recording
    `recording_enabled` BOOLEAN DEFAULT TRUE,
    `recording_schedule` JSON NULL COMMENT '24/7 or specific hours',
    `retention_days` INT DEFAULT 30,
    `storage_location` VARCHAR(255) NULL,

    -- Motion Detection
    `motion_detection_enabled` BOOLEAN DEFAULT TRUE,
    `motion_sensitivity` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `motion_zones` JSON NULL COMMENT 'Specific areas to monitor',

    -- Alerts
    `send_alerts` BOOLEAN DEFAULT TRUE,
    `alert_email` VARCHAR(255) NULL,
    `alert_sms` VARCHAR(20) NULL,

    -- Status
    `status` ENUM('online', 'offline', 'error', 'maintenance') DEFAULT 'online',
    `last_online_at` DATETIME NULL,
    `last_recording_at` DATETIME NULL,

    -- Installation
    `installed_date` DATE NULL,
    `installed_by` VARCHAR(255) NULL,
    `warranty_expiry_date` DATE NULL,

    -- Maintenance
    `last_maintenance_date` DATE NULL,
    `next_maintenance_date` DATE NULL,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    INDEX idx_status (`status`),
    INDEX idx_location (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Camera Events (motion, alerts, etc.)
CREATE TABLE IF NOT EXISTS `camera_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `camera_id` INT UNSIGNED NOT NULL,

    -- Event Details
    `event_type` ENUM('motion_detected', 'person_detected', 'vehicle_detected', 'alert', 'offline', 'tamper', 'other') NOT NULL,
    `event_time` DATETIME NOT NULL,
    `duration_seconds` INT NULL,

    -- Detection
    `confidence_score` DECIMAL(5, 2) NULL COMMENT 'AI detection confidence',
    `objects_detected` JSON NULL COMMENT 'People, vehicles, etc.',

    -- Recording
    `video_clip_url` VARCHAR(500) NULL,
    `snapshot_url` VARCHAR(500) NULL,
    `clip_duration_seconds` INT NULL,

    -- Actions Taken
    `alert_sent` BOOLEAN DEFAULT FALSE,
    `alert_sent_to` JSON NULL,
    `alert_sent_at` DATETIME NULL,

    -- Review
    `reviewed` BOOLEAN DEFAULT FALSE,
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` DATETIME NULL,
    `review_notes` TEXT NULL,
    `false_positive` BOOLEAN DEFAULT FALSE,

    -- Related Incident
    `security_incident_id` INT UNSIGNED NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`camera_id`) REFERENCES `security_cameras`(`id`) ON DELETE CASCADE,
    INDEX idx_camera_time (`camera_id`, `event_time`),
    INDEX idx_event_type (`event_type`),
    INDEX idx_reviewed (`reviewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Access Control Points (doors, gates, etc.)
CREATE TABLE IF NOT EXISTS `access_control_points` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `point_name` VARCHAR(255) NOT NULL,
    `location_id` INT UNSIGNED NULL,

    -- Point Details
    `point_type` ENUM('door', 'gate', 'turnstile', 'elevator', 'cabinet', 'safe') NOT NULL,
    `description` TEXT NULL,

    -- Hardware
    `controller_type` VARCHAR(100) NULL COMMENT 'e.g., HID, Salto, Paxton',
    `reader_type` ENUM('card', 'keypad', 'biometric', 'bluetooth', 'nfc', 'qr_code') NULL,
    `lock_type` ENUM('electric_strike', 'magnetic', 'motorized', 'solenoid') NULL,

    -- Network
    `ip_address` VARCHAR(45) NULL,
    `mac_address` VARCHAR(17) NULL,

    -- Access Rules
    `default_access_level` ENUM('public', 'employees', 'managers', 'admin', 'restricted') DEFAULT 'employees',
    `requires_pin` BOOLEAN DEFAULT FALSE,
    `requires_two_factor` BOOLEAN DEFAULT FALSE,

    -- Schedule
    `access_schedule` JSON NULL COMMENT 'Hours when access is allowed',
    `unlocked_hours` JSON NULL COMMENT 'Hours when door stays unlocked',

    -- Auto-lock
    `auto_lock_enabled` BOOLEAN DEFAULT TRUE,
    `auto_lock_delay_seconds` INT DEFAULT 10,

    -- Door Sensor
    `has_door_sensor` BOOLEAN DEFAULT TRUE,
    `door_held_open_alert_seconds` INT DEFAULT 30,

    -- Status
    `status` ENUM('online', 'offline', 'error', 'maintenance', 'bypassed') DEFAULT 'online',
    `current_state` ENUM('locked', 'unlocked', 'propped', 'forced') DEFAULT 'locked',
    `last_state_change` DATETIME NULL,

    -- Alerts
    `send_alerts` BOOLEAN DEFAULT TRUE,
    `alert_on_forced_entry` BOOLEAN DEFAULT TRUE,
    `alert_on_prop_open` BOOLEAN DEFAULT TRUE,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Access Cards/Credentials
CREATE TABLE IF NOT EXISTS `access_credentials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,

    -- Credential Details
    `credential_type` ENUM('card', 'fob', 'pin', 'biometric', 'mobile', 'temporary') NOT NULL,
    `credential_number` VARCHAR(100) NOT NULL,
    `card_number` VARCHAR(50) NULL,
    `facility_code` VARCHAR(20) NULL,

    -- Ownership
    `holder_type` ENUM('employee', 'customer', 'vendor', 'contractor', 'guest') NOT NULL,
    `employee_id` INT UNSIGNED NULL,
    `customer_id` INT UNSIGNED NULL,
    `holder_name` VARCHAR(255) NULL,

    -- Access Level
    `access_level` ENUM('public', 'employee', 'manager', 'admin', 'master') NOT NULL DEFAULT 'employee',
    `allowed_points` JSON NULL COMMENT 'Specific access points allowed',

    -- Validity
    `issued_date` DATE NOT NULL,
    `activation_date` DATE NULL,
    `expiration_date` DATE NULL,
    `is_active` BOOLEAN DEFAULT TRUE,

    -- PIN
    `pin_code` VARCHAR(255) NULL COMMENT 'Encrypted',
    `require_pin` BOOLEAN DEFAULT FALSE,

    -- Temporary Access
    `is_temporary` BOOLEAN DEFAULT FALSE,
    `temp_access_start` DATETIME NULL,
    `temp_access_end` DATETIME NULL,

    -- Status
    `status` ENUM('active', 'suspended', 'revoked', 'lost', 'expired') DEFAULT 'active',
    `status_reason` TEXT NULL,
    `revoked_at` DATETIME NULL,
    `revoked_by` INT UNSIGNED NULL,

    -- Usage Stats
    `total_uses` INT DEFAULT 0,
    `last_used_at` DATETIME NULL,
    `last_used_location` VARCHAR(255) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    UNIQUE KEY unique_credential (`tenant_id`, `credential_number`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Access Events (door access log)
CREATE TABLE IF NOT EXISTS `access_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `access_point_id` INT UNSIGNED NOT NULL,
    `credential_id` INT UNSIGNED NULL,

    -- Event Details
    `event_type` ENUM('access_granted', 'access_denied', 'door_forced', 'door_propped', 'door_unlocked', 'door_locked', 'emergency_unlock') NOT NULL,
    `event_time` DATETIME NOT NULL,

    -- Credential Used
    `credential_number` VARCHAR(100) NULL,
    `pin_used` BOOLEAN DEFAULT FALSE,

    -- Person
    `employee_id` INT UNSIGNED NULL,
    `customer_id` INT UNSIGNED NULL,
    `person_name` VARCHAR(255) NULL,

    -- Denial Reason
    `denial_reason` VARCHAR(255) NULL COMMENT 'Invalid card, expired, wrong time, etc.',

    -- Camera
    `camera_snapshot_url` VARCHAR(500) NULL,
    `video_clip_url` VARCHAR(500) NULL,

    -- Response
    `alert_generated` BOOLEAN DEFAULT FALSE,
    `alert_sent_to` JSON NULL,

    -- Review
    `reviewed` BOOLEAN DEFAULT FALSE,
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` DATETIME NULL,
    `review_notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`access_point_id`) REFERENCES `access_control_points`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`credential_id`) REFERENCES `access_credentials`(`id`) ON DELETE SET NULL,
    INDEX idx_event_time (`event_time`),
    INDEX idx_event_type (`event_type`),
    INDEX idx_credential (`credential_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alarm System
CREATE TABLE IF NOT EXISTS `alarm_systems` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `system_name` VARCHAR(255) NOT NULL,
    `location_id` INT UNSIGNED NULL,

    -- System Details
    `manufacturer` VARCHAR(100) NULL,
    `model` VARCHAR(100) NULL,
    `panel_serial_number` VARCHAR(100) NULL,

    -- Monitoring
    `monitoring_company` VARCHAR(255) NULL,
    `monitoring_account_number` VARCHAR(100) NULL,
    `monitoring_phone` VARCHAR(20) NULL,
    `central_station` VARCHAR(255) NULL,

    -- Network
    `ip_address` VARCHAR(45) NULL,
    `cellular_backup` BOOLEAN DEFAULT TRUE,

    -- Status
    `current_status` ENUM('disarmed', 'armed_stay', 'armed_away', 'triggered', 'trouble', 'offline') DEFAULT 'disarmed',
    `status_changed_at` DATETIME NULL,
    `status_changed_by` INT UNSIGNED NULL,

    -- Zones
    `total_zones` INT DEFAULT 0,
    `zones_config` JSON NULL,

    -- Codes
    `master_code` VARCHAR(255) NULL COMMENT 'Encrypted',
    `duress_code` VARCHAR(255) NULL COMMENT 'Encrypted',

    -- Settings
    `entry_delay_seconds` INT DEFAULT 30,
    `exit_delay_seconds` INT DEFAULT 60,
    `alarm_duration_seconds` INT DEFAULT 300,

    -- Alerts
    `alert_police` BOOLEAN DEFAULT TRUE,
    `alert_contacts` JSON NULL,

    -- Maintenance
    `last_test_date` DATE NULL,
    `next_test_date` DATE NULL,
    `battery_last_replaced` DATE NULL,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alarm Events
CREATE TABLE IF NOT EXISTS `alarm_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `alarm_system_id` INT UNSIGNED NOT NULL,

    -- Event Details
    `event_type` ENUM('armed', 'disarmed', 'triggered', 'cancelled', 'trouble', 'tamper', 'low_battery', 'ac_loss', 'test') NOT NULL,
    `event_time` DATETIME NOT NULL,
    `zone_number` INT NULL,
    `zone_name` VARCHAR(100) NULL,

    -- Person
    `triggered_by_user_id` INT UNSIGNED NULL,
    `user_name` VARCHAR(255) NULL,
    `user_code_used` VARCHAR(10) NULL COMMENT 'Which code was used (not the actual code)',

    -- Response
    `alarm_duration_seconds` INT NULL,
    `police_notified` BOOLEAN DEFAULT FALSE,
    `police_dispatched` BOOLEAN DEFAULT FALSE,
    `false_alarm` BOOLEAN DEFAULT FALSE,

    -- Monitoring
    `monitoring_company_notified` BOOLEAN DEFAULT FALSE,
    `central_station_event_id` VARCHAR(100) NULL,

    -- Response Personnel
    `responded_by` JSON NULL COMMENT 'Who responded to alarm',
    `response_time_minutes` INT NULL,

    -- Resolution
    `resolved_at` DATETIME NULL,
    `resolution_notes` TEXT NULL,

    -- Video Evidence
    `video_clips` JSON NULL,
    `snapshots` JSON NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`alarm_system_id`) REFERENCES `alarm_systems`(`id`) ON DELETE CASCADE,
    INDEX idx_event_time (`event_time`),
    INDEX idx_event_type (`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Incidents
CREATE TABLE IF NOT EXISTS `security_incidents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `incident_number` VARCHAR(50) NOT NULL UNIQUE,

    -- Incident Details
    `incident_type` ENUM('theft', 'burglary', 'robbery', 'vandalism', 'trespassing', 'assault', 'suspicious_activity', 'lost_property', 'found_property', 'other') NOT NULL,
    `severity` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    `incident_date` DATETIME NOT NULL,
    `location_id` INT UNSIGNED NULL,
    `specific_location` VARCHAR(255) NULL,

    -- Description
    `summary` VARCHAR(500) NULL,
    `detailed_description` TEXT NULL,
    `loss_amount` DECIMAL(10, 2) NULL,
    `items_stolen` JSON NULL,

    -- Parties Involved
    `reporter_id` INT UNSIGNED NULL COMMENT 'Employee who reported',
    `reporter_name` VARCHAR(255) NULL,
    `witnesses` JSON NULL,
    `suspects` JSON NULL,

    -- Evidence
    `camera_ids` JSON NULL COMMENT 'Cameras that captured incident',
    `video_clips` JSON NULL,
    `photos` JSON NULL,
    `documents` JSON NULL,

    -- Response
    `police_notified` BOOLEAN DEFAULT FALSE,
    `police_report_number` VARCHAR(100) NULL,
    `police_officer_name` VARCHAR(255) NULL,
    `police_badge_number` VARCHAR(50) NULL,
    `insurance_claim_filed` BOOLEAN DEFAULT FALSE,
    `claim_number` VARCHAR(100) NULL,

    -- Status
    `status` ENUM('reported', 'investigating', 'resolved', 'closed', 'archived') DEFAULT 'reported',
    `resolved_at` DATETIME NULL,
    `resolution` TEXT NULL,

    -- Follow-up
    `assigned_to` INT UNSIGNED NULL,
    `follow_up_required` BOOLEAN DEFAULT FALSE,
    `follow_up_date` DATE NULL,

    -- Prevention
    `preventive_measures` TEXT NULL,
    `security_improvements` TEXT NULL,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`location_id`) REFERENCES `inventory_locations`(`id`) ON DELETE SET NULL,
    INDEX idx_incident_date (`incident_date`),
    INDEX idx_status (`status`),
    INDEX idx_severity (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sample Data
-- =====================================================

-- Sample Cameras
INSERT INTO `security_cameras` (
    `tenant_id`, `camera_name`, `camera_location`, `camera_type`, `resolution`,
    `recording_enabled`, `motion_detection_enabled`, `status`
) VALUES
(1, 'Front Entrance', 'Main entrance door', 'dome', '1080p', TRUE, TRUE, 'online'),
(1, 'Sales Floor - Overview', 'Above retail displays', 'dome', '4K', TRUE, TRUE, 'online'),
(1, 'Cash Register', 'Behind counter', 'bullet', '1080p', TRUE, FALSE, 'online'),
(1, 'Warehouse Entry', 'Warehouse door', 'bullet', '1080p', TRUE, TRUE, 'online'),
(1, 'Parking Lot', 'Rear parking area', 'ptz', '4K', TRUE, TRUE, 'online'),
(1, 'Equipment Room', 'Rental equipment storage', 'dome', '1080p', TRUE, TRUE, 'online');

-- Sample Access Points
INSERT INTO `access_control_points` (
    `tenant_id`, `point_name`, `point_type`, `default_access_level`, `auto_lock_enabled`, `status`
) VALUES
(1, 'Front Door', 'door', 'employees', TRUE, 'online'),
(1, 'Warehouse Door', 'door', 'employees', TRUE, 'online'),
(1, 'Office Door', 'door', 'managers', TRUE, 'online'),
(1, 'Equipment Storage Cabinet', 'cabinet', 'employees', TRUE, 'online'),
(1, 'Safe', 'safe', 'managers', TRUE, 'online');

-- Sample Access Credentials
INSERT INTO `access_credentials` (
    `tenant_id`, `credential_type`, `credential_number`, `holder_type`, `employee_id`,
    `holder_name`, `access_level`, `issued_date`, `is_active`, `status`
) VALUES
(1, 'card', 'CARD-001', 'employee', 1, 'Sarah Johnson', 'admin', '2024-01-15', TRUE, 'active'),
(1, 'card', 'CARD-002', 'employee', 2, 'Mike Chen', 'employee', '2024-01-15', TRUE, 'active'),
(1, 'card', 'CARD-003', 'employee', 3, 'Jessica Martinez', 'employee', '2024-01-20', TRUE, 'active'),
(1, 'fob', 'FOB-001', 'employee', 1, 'Sarah Johnson', 'admin', '2024-01-15', TRUE, 'active');
