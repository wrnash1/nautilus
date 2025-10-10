
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `certification_id` INT UNSIGNED,
  `course_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `duration_days` INT NOT NULL,
  `max_students` INT DEFAULT 6,
  `price` DECIMAL(10,2) NOT NULL,
  `certification_fee` DECIMAL(10,2),
  `materials_fee` DECIMAL(10,2),
  `prerequisites` JSON,
  `minimum_age` INT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`certification_id`) REFERENCES `certifications`(`id`),
  INDEX `idx_course_code` (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_schedules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `instructor_id` INT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `start_time` TIME,
  `end_time` TIME,
  `location` VARCHAR(255),
  `max_students` INT,
  `current_enrollment` INT DEFAULT 0,
  `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`),
  FOREIGN KEY (`instructor_id`) REFERENCES `users`(`id`),
  INDEX `idx_course_id` (`course_id`),
  INDEX `idx_dates` (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_enrollments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `schedule_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `enrollment_date` DATE NOT NULL,
  `status` ENUM('enrolled', 'in_progress', 'completed', 'dropped', 'failed') DEFAULT 'enrolled',
  `completion_date` DATE,
  `certification_number` VARCHAR(100),
  `final_grade` VARCHAR(20),
  `amount_paid` DECIMAL(10,2) NOT NULL,
  `payment_status` ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`schedule_id`) REFERENCES `course_schedules`(`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  UNIQUE KEY `unique_enrollment` (`schedule_id`, `customer_id`),
  INDEX `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `course_attendance` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `enrollment_id` INT UNSIGNED NOT NULL,
  `session_date` DATE NOT NULL,
  `session_type` ENUM('classroom', 'pool', 'open_water') NOT NULL,
  `attended` BOOLEAN DEFAULT FALSE,
  `performance_notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`enrollment_id`) REFERENCES `course_enrollments`(`id`) ON DELETE CASCADE,
  INDEX `idx_enrollment_id` (`enrollment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trips` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `trip_code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `duration_days` INT NOT NULL,
  `max_participants` INT DEFAULT 20,
  `price` DECIMAL(10,2) NOT NULL,
  `deposit_amount` DECIMAL(10,2),
  `minimum_certification` INT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_trip_code` (`trip_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trip_schedules` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `trip_id` INT UNSIGNED NOT NULL,
  `departure_date` DATE NOT NULL,
  `return_date` DATE NOT NULL,
  `departure_location` VARCHAR(255),
  `max_participants` INT,
  `current_bookings` INT DEFAULT 0,
  `status` ENUM('scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`trip_id`) REFERENCES `trips`(`id`),
  INDEX `idx_trip_id` (`trip_id`),
  INDEX `idx_dates` (`departure_date`, `return_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trip_bookings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `schedule_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `booking_date` DATE NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
  `total_amount` DECIMAL(10,2) NOT NULL,
  `deposit_paid` DECIMAL(10,2) DEFAULT 0.00,
  `balance_paid` DECIMAL(10,2) DEFAULT 0.00,
  `payment_status` ENUM('pending', 'deposit', 'paid', 'refunded') DEFAULT 'pending',
  `special_requests` TEXT,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`schedule_id`) REFERENCES `trip_schedules`(`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  INDEX `idx_schedule_id` (`schedule_id`),
  INDEX `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `trip_participants` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT UNSIGNED NOT NULL,
  `participant_name` VARCHAR(200) NOT NULL,
  `certification_level` VARCHAR(100),
  `emergency_contact_name` VARCHAR(200),
  `emergency_contact_phone` VARCHAR(20),
  `medical_notes` TEXT,
  FOREIGN KEY (`booking_id`) REFERENCES `trip_bookings`(`id`) ON DELETE CASCADE,
  INDEX `idx_booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
