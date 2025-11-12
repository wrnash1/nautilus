-- Enhancement for certification bodies, dive sites, travel packets, and service reminders

-- Add logo and color scheme to certification agencies
ALTER TABLE `certification_agencies`
ADD COLUMN IF NOT EXISTS `logo_path` VARCHAR(255) AFTER `abbreviation`,
ADD COLUMN IF NOT EXISTS `primary_color` VARCHAR(7) DEFAULT '#0066CC' AFTER `logo_path`,
ADD COLUMN IF NOT EXISTS `verification_enabled` BOOLEAN DEFAULT FALSE AFTER `api_key_encrypted`,
ADD COLUMN IF NOT EXISTS `verification_url` VARCHAR(255) AFTER `verification_enabled`,
ADD COLUMN IF NOT EXISTS `country` VARCHAR(100) AFTER `website`;

-- Add expiry tracking to customer certifications
ALTER TABLE `customer_certifications`
ADD COLUMN IF NOT EXISTS `expiry_date` DATE AFTER `issue_date`,
ADD COLUMN IF NOT EXISTS `auto_verified` BOOLEAN DEFAULT FALSE AFTER `verification_status`,
ADD COLUMN IF NOT EXISTS `verified_at` TIMESTAMP NULL AFTER `auto_verified`,
ADD COLUMN IF NOT EXISTS `verified_by` INT UNSIGNED NULL AFTER `verified_at`;

-- Add foreign key if it doesn't exist
SET @constraint_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = DATABASE()
AND TABLE_NAME = 'customer_certifications'
AND CONSTRAINT_NAME = 'fk_verified_by_user'
AND CONSTRAINT_TYPE = 'FOREIGN KEY');

SET @sql = IF(@constraint_exists = 0,
    'ALTER TABLE `customer_certifications` ADD CONSTRAINT `fk_verified_by_user` FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`)',
    'SELECT "Foreign key already exists"');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add photo to customers table if not exists
ALTER TABLE `customers`
ADD COLUMN IF NOT EXISTS `photo_path` VARCHAR(255) AFTER `email`;

-- Dive Sites Table
CREATE TABLE IF NOT EXISTS `dive_sites` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `region` VARCHAR(100),
  `latitude` DECIMAL(10, 8),
  `longitude` DECIMAL(11, 8),
  `max_depth_meters` DECIMAL(5, 2),
  `min_depth_meters` DECIMAL(5, 2),
  `skill_level` ENUM('beginner', 'intermediate', 'advanced', 'technical') DEFAULT 'beginner',
  `minimum_certification_level` INT,
  `site_type` ENUM('shore', 'boat', 'wreck', 'reef', 'cave', 'drift', 'wall', 'blue_hole', 'other') DEFAULT 'reef',
  `description` TEXT,
  `highlights` TEXT,
  `marine_life` TEXT,
  `hazards` TEXT,
  `best_season` VARCHAR(100),
  `average_visibility_meters` DECIMAL(5, 2),
  `average_current` ENUM('none', 'mild', 'moderate', 'strong') DEFAULT 'none',
  `entry_exit_type` VARCHAR(100),
  `facilities` JSON,
  `notes` TEXT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_country` (`country`),
  INDEX `idx_skill_level` (`skill_level`),
  INDEX `idx_site_type` (`site_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dive Site Conditions (Weather/Water tracking)
CREATE TABLE IF NOT EXISTS `dive_site_conditions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `dive_site_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME,
  `water_temp_celsius` DECIMAL(4, 2),
  `air_temp_celsius` DECIMAL(4, 2),
  `visibility_meters` DECIMAL(5, 2),
  `current` ENUM('none', 'mild', 'moderate', 'strong'),
  `wave_height_meters` DECIMAL(4, 2),
  `wind_speed_kmh` DECIMAL(5, 2),
  `wind_direction` VARCHAR(20),
  `weather_condition` ENUM('sunny', 'partly_cloudy', 'cloudy', 'rainy', 'stormy'),
  `tide` ENUM('low', 'rising', 'high', 'falling'),
  `notes` TEXT,
  `reported_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`dive_site_id`) REFERENCES `dive_sites`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reported_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_dive_site_date` (`dive_site_id`, `date`),
  INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip Dive Sites (linking trips to dive sites)
CREATE TABLE IF NOT EXISTS `trip_dive_sites` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `trip_schedule_id` INT UNSIGNED NOT NULL,
  `dive_site_id` INT UNSIGNED NOT NULL,
  `planned_date` DATE,
  `dive_number` INT,
  `notes` TEXT,
  FOREIGN KEY (`trip_schedule_id`) REFERENCES `trip_schedules`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dive_site_id`) REFERENCES `dive_sites`(`id`) ON DELETE CASCADE,
  INDEX `idx_trip_schedule` (`trip_schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Travel Documents
CREATE TABLE IF NOT EXISTS `customer_travel_documents` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `document_type` ENUM('passport', 'visa', 'travel_insurance', 'medical_clearance', 'other') NOT NULL,
  `document_number` VARCHAR(100),
  `issue_date` DATE,
  `expiry_date` DATE,
  `issuing_country` VARCHAR(100),
  `file_path` VARCHAR(255),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_expiry_date` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Medical Information
CREATE TABLE IF NOT EXISTS `customer_medical_info` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `blood_type` VARCHAR(10),
  `allergies` TEXT,
  `medications` TEXT,
  `medical_conditions` TEXT,
  `physician_name` VARCHAR(200),
  `physician_phone` VARCHAR(20),
  `medical_clearance_date` DATE,
  `medical_clearance_file` VARCHAR(255),
  `emergency_notes` TEXT,
  `fitness_to_dive` BOOLEAN DEFAULT TRUE,
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_customer_medical` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travel Packets (for sending customer info to resorts)
CREATE TABLE IF NOT EXISTS `travel_packets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `packet_number` VARCHAR(50) NOT NULL UNIQUE,
  `trip_booking_id` INT UNSIGNED,
  `destination_name` VARCHAR(255) NOT NULL,
  `destination_contact_name` VARCHAR(200),
  `destination_email` VARCHAR(255),
  `destination_phone` VARCHAR(50),
  `departure_date` DATE,
  `return_date` DATE,
  `status` ENUM('draft', 'sent', 'confirmed', 'cancelled') DEFAULT 'draft',
  `sent_at` TIMESTAMP NULL,
  `confirmed_at` TIMESTAMP NULL,
  `packet_data` JSON,
  `notes` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`trip_booking_id`) REFERENCES `trip_bookings`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_departure_date` (`departure_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Travel Packet Participants
CREATE TABLE IF NOT EXISTS `travel_packet_participants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `travel_packet_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `include_passport` BOOLEAN DEFAULT TRUE,
  `include_medical` BOOLEAN DEFAULT TRUE,
  `include_certifications` BOOLEAN DEFAULT TRUE,
  `include_insurance` BOOLEAN DEFAULT TRUE,
  `flight_number` VARCHAR(50),
  `arrival_time` DATETIME,
  `departure_flight` VARCHAR(50),
  `departure_time` DATETIME,
  `special_requests` TEXT,
  FOREIGN KEY (`travel_packet_id`) REFERENCES `travel_packets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  INDEX `idx_travel_packet` (`travel_packet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Reminders System
CREATE TABLE IF NOT EXISTS `service_reminder_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `reminder_type` ENUM('tank_vip', 'tank_hydro', 'regulator_service', 'bcd_service', 'certification_renewal', 'course_followup', 'birthday', 'anniversary', 'custom') NOT NULL,
  `days_before` INT NOT NULL DEFAULT 30,
  `email_subject` VARCHAR(255),
  `email_body` TEXT,
  `sms_message` VARCHAR(320),
  `send_email` BOOLEAN DEFAULT TRUE,
  `send_sms` BOOLEAN DEFAULT FALSE,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service Reminders Queue
CREATE TABLE IF NOT EXISTS `service_reminders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `template_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `reminder_type` ENUM('tank_vip', 'tank_hydro', 'regulator_service', 'bcd_service', 'certification_renewal', 'course_followup', 'birthday', 'anniversary', 'custom') NOT NULL,
  `reference_type` VARCHAR(50),
  `reference_id` INT UNSIGNED,
  `due_date` DATE NOT NULL,
  `scheduled_send_date` DATE NOT NULL,
  `status` ENUM('pending', 'sent', 'failed', 'cancelled', 'completed') DEFAULT 'pending',
  `sent_at` TIMESTAMP NULL,
  `email_sent` BOOLEAN DEFAULT FALSE,
  `sms_sent` BOOLEAN DEFAULT FALSE,
  `error_message` TEXT,
  `completed_at` TIMESTAMP NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`template_id`) REFERENCES `service_reminder_templates`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  INDEX `idx_scheduled_send` (`scheduled_send_date`, `status`),
  INDEX `idx_customer` (`customer_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Equipment Service History (for tracking when equipment was serviced)
CREATE TABLE IF NOT EXISTS `equipment_service_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED,
  `equipment_type` ENUM('tank', 'regulator', 'bcd', 'computer', 'wetsuit', 'other') NOT NULL,
  `equipment_serial` VARCHAR(100),
  `equipment_brand` VARCHAR(100),
  `equipment_model` VARCHAR(100),
  `service_type` ENUM('vip', 'hydro', 'annual_service', 'repair', 'inspection') NOT NULL,
  `service_date` DATE NOT NULL,
  `next_service_due` DATE,
  `service_notes` TEXT,
  `work_order_id` INT UNSIGNED,
  `serviced_by` INT UNSIGNED,
  `cost` DECIMAL(10, 2),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`work_order_id`) REFERENCES `work_orders`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`serviced_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_customer` (`customer_id`),
  INDEX `idx_next_service_due` (`next_service_due`),
  INDEX `idx_equipment_serial` (`equipment_serial`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vendor Product Catalogs (for importing products from vendors)
CREATE TABLE IF NOT EXISTS `vendor_catalogs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_id` INT UNSIGNED NOT NULL,
  `catalog_name` VARCHAR(255) NOT NULL,
  `catalog_year` INT,
  `import_format` ENUM('csv', 'xml', 'json', 'api') DEFAULT 'csv',
  `import_url` VARCHAR(255),
  `last_import_date` TIMESTAMP NULL,
  `last_import_count` INT DEFAULT 0,
  `auto_import_enabled` BOOLEAN DEFAULT FALSE,
  `auto_import_schedule` VARCHAR(50),
  `field_mapping` JSON,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
  INDEX `idx_vendor` (`vendor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vendor Product Catalog Items (staging area before import)
CREATE TABLE IF NOT EXISTS `vendor_catalog_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vendor_catalog_id` INT UNSIGNED NOT NULL,
  `vendor_sku` VARCHAR(100) NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(100),
  `subcategory` VARCHAR(100),
  `brand` VARCHAR(100),
  `model` VARCHAR(100),
  `upc` VARCHAR(50),
  `wholesale_price` DECIMAL(10, 2),
  `msrp` DECIMAL(10, 2),
  `image_url` VARCHAR(500),
  `specifications` JSON,
  `stock_status` VARCHAR(50),
  `imported_to_product_id` INT UNSIGNED NULL,
  `import_status` ENUM('pending', 'imported', 'skipped', 'error') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`vendor_catalog_id`) REFERENCES `vendor_catalogs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`imported_to_product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  INDEX `idx_vendor_catalog` (`vendor_catalog_id`),
  INDEX `idx_vendor_sku` (`vendor_sku`),
  INDEX `idx_import_status` (`import_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add API configuration columns to integrations (for QuickBooks, etc.)
CREATE TABLE IF NOT EXISTS `integration_configs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `integration_name` VARCHAR(100) NOT NULL UNIQUE,
  `integration_type` ENUM('accounting', 'certification', 'weather', 'payment', 'shipping', 'other') NOT NULL,
  `is_enabled` BOOLEAN DEFAULT FALSE,
  `config_data` JSON,
  `api_endpoint` VARCHAR(255),
  `api_key_encrypted` VARCHAR(255),
  `oauth_token` TEXT,
  `oauth_refresh_token` TEXT,
  `token_expires_at` TIMESTAMP NULL,
  `last_sync_at` TIMESTAMP NULL,
  `sync_status` ENUM('idle', 'syncing', 'success', 'error') DEFAULT 'idle',
  `sync_error` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mobile Session Tokens (for mobile app authentication)
CREATE TABLE IF NOT EXISTS `mobile_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `device_type` VARCHAR(50),
  `device_name` VARCHAR(100),
  `device_id` VARCHAR(100),
  `fcm_token` VARCHAR(255),
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_token` (`token`),
  INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
