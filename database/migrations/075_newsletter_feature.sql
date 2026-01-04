SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `newsletter_subscriptions`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `newsletter_subscriptions`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `newsletter_subscriptions`;

-- Migration: 075_newsletter_feature.sql
-- Description: Add support for newsletter segments and subscriptions

-- Ensure email_campaigns has segment column
SET @dbname = DATABASE();
SET @tablename = "email_campaigns";
SET @columnname = "segment";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE email_campaigns ADD COLUMN segment VARCHAR(50) DEFAULT 'all' AFTER name"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Ensure email_campaign_recipients exists
CREATE TABLE IF NOT EXISTS `email_campaign_recipients` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `campaign_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `email` VARCHAR(255) NOT NULL,
    `status` ENUM('sent', 'failed', 'opened', 'clicked') DEFAULT 'sent',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`campaign_id`) REFERENCES `email_campaigns`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_subscriptions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `name` VARCHAR(255) NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` TIMESTAMP NULL,
    `source` VARCHAR(100) DEFAULT 'website',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;