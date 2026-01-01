SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `course_interest_queue`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `course_interest_queue`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `course_interest_queue`;

CREATE TABLE IF NOT EXISTS `course_interest_queue` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` BIGINT UNSIGNED NOT NULL,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'converted', 'cancelled') DEFAULT 'pending',
  `notes` TEXT,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  INDEX `idx_course_queue` (`course_id`, `status`),
  INDEX `idx_customer_queue` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;