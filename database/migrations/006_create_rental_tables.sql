
CREATE TABLE IF NOT EXISTS `rental_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_equipment` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `equipment_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `size` VARCHAR(50),
  `manufacturer` VARCHAR(100),
  `model` VARCHAR(100),
  `serial_number` VARCHAR(100),
  `purchase_date` DATE,
  `purchase_cost` DECIMAL(10,2),
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `weekly_rate` DECIMAL(10,2),
  `status` ENUM('available', 'rented', 'maintenance', 'damaged', 'retired') DEFAULT 'available',
  `condition` ENUM('excellent', 'good', 'fair', 'poor') DEFAULT 'good',
  `last_inspection_date` DATE,
  `next_inspection_due` DATE,
  `vip_date` DATE,
  `hydro_date` DATE,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `rental_categories`(`id`),
  INDEX `idx_equipment_code` (`equipment_code`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_reservations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reservation_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `pickup_date` DATETIME,
  `return_date` DATETIME,
  `status` ENUM('pending', 'confirmed', 'picked_up', 'returned', 'cancelled') DEFAULT 'pending',
  `total_cost` DECIMAL(10,2) NOT NULL,
  `deposit_amount` DECIMAL(10,2),
  `notes` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_reservation_number` (`reservation_number`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_reservation_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reservation_id` INT UNSIGNED NOT NULL,
  `equipment_id` INT UNSIGNED NOT NULL,
  `daily_rate` DECIMAL(10,2) NOT NULL,
  `quantity_days` INT NOT NULL,
  `total_cost` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`reservation_id`) REFERENCES `rental_reservations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`equipment_id`) REFERENCES `rental_equipment`(`id`),
  INDEX `idx_reservation_id` (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rental_checkouts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reservation_id` INT UNSIGNED NOT NULL,
  `equipment_id` INT UNSIGNED NOT NULL,
  `checkout_date` DATETIME NOT NULL,
  `expected_return_date` DATETIME NOT NULL,
  `actual_return_date` DATETIME,
  `condition_at_checkout` ENUM('excellent', 'good', 'fair', 'poor') NOT NULL,
  `condition_at_return` ENUM('excellent', 'good', 'fair', 'poor'),
  `damage_notes` TEXT,
  `damage_fee` DECIMAL(10,2),
  `checked_out_by` INT UNSIGNED,
  `checked_in_by` INT UNSIGNED,
  FOREIGN KEY (`reservation_id`) REFERENCES `rental_reservations`(`id`),
  FOREIGN KEY (`equipment_id`) REFERENCES `rental_equipment`(`id`),
  FOREIGN KEY (`checked_out_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`checked_in_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_reservation_id` (`reservation_id`),
  INDEX `idx_equipment_id` (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `equipment_inspections` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `equipment_id` INT UNSIGNED NOT NULL,
  `inspection_type` ENUM('daily', 'vip', 'hydro', 'maintenance') NOT NULL,
  `inspection_date` DATE NOT NULL,
  `inspector_id` INT UNSIGNED,
  `passed` BOOLEAN NOT NULL,
  `findings` TEXT,
  `next_inspection_due` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`equipment_id`) REFERENCES `rental_equipment`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`inspector_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_equipment_id` (`equipment_id`),
  INDEX `idx_inspection_date` (`inspection_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `air_fills` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED,
  `equipment_id` INT UNSIGNED,
  `fill_type` ENUM('air', 'nitrox', 'trimix', 'oxygen') NOT NULL,
  `fill_pressure` INT NOT NULL,
  `nitrox_percentage` DECIMAL(5,2),
  `cost` DECIMAL(10,2) NOT NULL,
  `filled_by` INT UNSIGNED,
  `transaction_id` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`equipment_id`) REFERENCES `rental_equipment`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`filled_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE SET NULL,
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
