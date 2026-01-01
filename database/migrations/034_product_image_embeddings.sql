SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `visual_search_history`;
DROP TABLE IF EXISTS `product_image_embeddings`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `visual_search_history`;
DROP TABLE IF EXISTS `product_image_embeddings`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `visual_search_history`;
DROP TABLE IF EXISTS `product_image_embeddings`;

-- Product Image Embeddings for AI-powered visual search
-- Stores feature vectors extracted from product images using TensorFlow.js MobileNet

CREATE TABLE IF NOT EXISTS `product_image_embeddings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `embedding_vector` JSON NOT NULL COMMENT 'MobileNet feature vector (1024 dimensions)',
  `embedding_model` VARCHAR(50) DEFAULT 'mobilenet_v2' COMMENT 'Model used for embedding',
  `image_angle` ENUM('front', 'back', 'side', 'top', 'detail') DEFAULT 'front' COMMENT 'Photo angle for better matching',
  `embedding_quality_score` DECIMAL(3,2) DEFAULT 1.00 COMMENT 'Confidence score of embedding',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX `idx_product_id` (`product_id`),
  INDEX `idx_image_angle` (`image_angle`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create visual search history table for analytics
CREATE TABLE IF NOT EXISTS `visual_search_history` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `search_image_path` VARCHAR(255) NULL COMMENT 'Path to uploaded search image',
  `top_result_product_id` BIGINT UNSIGNED NULL,
  `similarity_score` DECIMAL(4,3) NULL COMMENT 'Similarity score of top result',
  `results_count` INT DEFAULT 0,
  `result_selected` BOOLEAN DEFAULT FALSE,
  `selected_product_id` BIGINT UNSIGNED NULL,
  `search_time_ms` INT NULL COMMENT 'Search execution time in milliseconds',
  `search_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`top_result_product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`selected_product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_search_date` (`search_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add visual search enabled flag to products
ALTER TABLE `products`
ADD COLUMN `visual_search_enabled` BOOLEAN DEFAULT TRUE AFTER `is_active`,
ADD COLUMN `last_embedding_generated` TIMESTAMP NULL AFTER `visual_search_enabled`;

-- Index for performance
ALTER TABLE `products` ADD INDEX `idx_visual_search` (`visual_search_enabled`, `is_active`);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;