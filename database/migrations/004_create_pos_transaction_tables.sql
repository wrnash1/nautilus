
SET FOREIGN_KEY_CHECKS=0;

-- Migration: 004 Create POS Transaction Tables

CREATE TABLE IF NOT EXISTS `cash_registers` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `location` VARCHAR(100),
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tax_rates` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `rate` DECIMAL(5,4) NOT NULL,
  `country` VARCHAR(2) DEFAULT 'US',
  `state` VARCHAR(50),
  `city` VARCHAR(100),
  `postal_code` VARCHAR(20),
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_location` (`country`, `state`, `city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pos_transactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` BIGINT UNSIGNED DEFAULT 1,
  `transaction_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` BIGINT UNSIGNED,
  `transaction_type` ENUM('sale', 'return', 'exchange', 'quote', 'layaway') DEFAULT 'sale',
  `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `change_given` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending', 'completed', 'voided', 'refunded', 'partial_refund') DEFAULT 'completed',
  `original_transaction_id` BIGINT UNSIGNED,
  `notes` TEXT,
  `cashier_id` BIGINT UNSIGNED,
  `quote_id` BIGINT UNSIGNED,
  `register_id` BIGINT UNSIGNED,
  `voided_by` BIGINT UNSIGNED,
  `voided_at` TIMESTAMP NULL,
  `void_reason` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`original_transaction_id`) REFERENCES `pos_transactions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cashier_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`voided_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_transaction_number` (`transaction_number`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_transaction_date` (`transaction_date`),
  INDEX `idx_status` (`status`),
  INDEX `idx_tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`register_id`) REFERENCES `cash_registers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaction_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED,
  `product_id` BIGINT UNSIGNED,
  `variant_id` BIGINT UNSIGNED,
  `item_name` VARCHAR(255) NOT NULL,
  `item_sku` VARCHAR(100),
  `quantity` INT NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0.00,
  `tax` DECIMAL(10,2) DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `is_taxable` BOOLEAN DEFAULT TRUE,
  `notes` TEXT,
  FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  INDEX `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` BIGINT UNSIGNED NOT NULL,
  `payment_method` ENUM('cash', 'credit_card', 'debit_card', 'check', 'gift_card', 'store_credit', 'crypto', 'other') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `card_last_four` VARCHAR(4),
  `card_type` VARCHAR(20),
  `check_number` VARCHAR(50),
  `authorization_code` VARCHAR(100),
  `transaction_id_external` VARCHAR(255),
  `payment_gateway` VARCHAR(50),
  `crypto_currency` VARCHAR(20),
  `crypto_transaction_hash` VARCHAR(255),
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
  `processed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions`(`id`) ON DELETE CASCADE,
  INDEX `idx_transaction_id` (`transaction_id`),
  INDEX `idx_payment_method` (`payment_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refunds` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` BIGINT UNSIGNED NOT NULL,
  `payment_id` BIGINT UNSIGNED,
  `refund_amount` DECIMAL(10,2) NOT NULL,
  `refund_method` ENUM('original_payment', 'cash', 'store_credit', 'exchange') NOT NULL,
  `reason` TEXT,
  `processed_by` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gift_cards` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `card_number` VARCHAR(50) NOT NULL UNIQUE,
  `pin` VARCHAR(10),
  `customer_id` BIGINT UNSIGNED,
  `initial_balance` DECIMAL(10,2) NOT NULL,
  `current_balance` DECIMAL(10,2) NOT NULL,
  `card_type` ENUM('physical', 'digital') DEFAULT 'physical',
  `status` ENUM('active', 'inactive', 'expired', 'depleted') DEFAULT 'active',
  `expiry_date` DATE,
  `issued_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` TIMESTAMP NULL,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  INDEX `idx_card_number` (`card_number`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gift_card_transactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `gift_card_id` BIGINT UNSIGNED NOT NULL,
  `transaction_id` BIGINT UNSIGNED,
  `transaction_type` ENUM('issue', 'reload', 'redemption', 'void') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `balance_before` DECIMAL(10,2) NOT NULL,
  `balance_after` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`gift_card_id`) REFERENCES `gift_cards`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions`(`id`) ON DELETE SET NULL,
  INDEX `idx_gift_card_id` (`gift_card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `store_credits` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `current_balance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `lifetime_earned` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `lifetime_spent` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  INDEX `idx_customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `store_credit_transactions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `store_credit_id` BIGINT UNSIGNED NOT NULL,
  `transaction_id` BIGINT UNSIGNED,
  `transaction_type` ENUM('credit', 'debit', 'adjustment', 'expiry') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `balance_before` DECIMAL(10,2) NOT NULL,
  `balance_after` DECIMAL(10,2) NOT NULL,
  `reason` TEXT,
  `created_by` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`store_credit_id`) REFERENCES `store_credits`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_store_credit_id` (`store_credit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `layaways` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `layaway_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` BIGINT UNSIGNED NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `deposit_percentage` DECIMAL(5,2) NOT NULL,
  `deposit_amount` DECIMAL(10,2) NOT NULL,
  `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `balance_remaining` DECIMAL(10,2) NOT NULL,
  `status` ENUM('active', 'completed', 'cancelled', 'expired') DEFAULT 'active',
  `expiry_date` DATE,
  `created_by` BIGINT UNSIGNED,
  `completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_layaway_number` (`layaway_number`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `layaway_items` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `layaway_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `variant_id` BIGINT UNSIGNED,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`layaway_id`) REFERENCES `layaways`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE SET NULL,
  INDEX `idx_layaway_id` (`layaway_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `layaway_payments` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `layaway_id` BIGINT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'credit_card', 'debit_card', 'check') NOT NULL,
  `processed_by` BIGINT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`layaway_id`) REFERENCES `layaways`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_layaway_id` (`layaway_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cash_drawer_sessions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `register_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `opening_float` DECIMAL(10,2) NOT NULL,
  `closing_cash` DECIMAL(10,2),
  `expected_cash` DECIMAL(10,2),
  `variance` DECIMAL(10,2),
  `opened_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `closed_at` TIMESTAMP NULL,
  `notes` TEXT,
  FOREIGN KEY (`register_id`) REFERENCES `cash_registers`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  INDEX `idx_register_id` (`register_id`),
  INDEX `idx_opened_at` (`opened_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
