SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `dynamic_pricing_recommendations`;
DROP TABLE IF EXISTS `nlp_extracted_entities`;
DROP TABLE IF EXISTS `ai_training_data`;
DROP TABLE IF EXISTS `predictive_maintenance_alerts`;
DROP TABLE IF EXISTS `ai_chatbot_messages`;
DROP TABLE IF EXISTS `ai_chatbot_conversations`;
DROP TABLE IF EXISTS `customer_ai_insights`;
DROP TABLE IF EXISTS `inventory_demand_forecasts`;
DROP TABLE IF EXISTS `ai_predictions`;
DROP TABLE IF EXISTS `ai_models`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `dynamic_pricing_recommendations`;
DROP TABLE IF EXISTS `nlp_extracted_entities`;
DROP TABLE IF EXISTS `ai_training_data`;
DROP TABLE IF EXISTS `predictive_maintenance_alerts`;
DROP TABLE IF EXISTS `ai_chatbot_messages`;
DROP TABLE IF EXISTS `ai_chatbot_conversations`;
DROP TABLE IF EXISTS `customer_ai_insights`;
DROP TABLE IF EXISTS `inventory_demand_forecasts`;
DROP TABLE IF EXISTS `ai_predictions`;
DROP TABLE IF EXISTS `ai_models`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `dynamic_pricing_recommendations`;
DROP TABLE IF EXISTS `nlp_extracted_entities`;
DROP TABLE IF EXISTS `ai_training_data`;
DROP TABLE IF EXISTS `predictive_maintenance_alerts`;
DROP TABLE IF EXISTS `ai_chatbot_messages`;
DROP TABLE IF EXISTS `ai_chatbot_conversations`;
DROP TABLE IF EXISTS `customer_ai_insights`;
DROP TABLE IF EXISTS `inventory_demand_forecasts`;
DROP TABLE IF EXISTS `ai_predictions`;
DROP TABLE IF EXISTS `ai_models`;

-- ================================================
-- Nautilus - AI & Machine Learning Systems
-- Migration: 082_ai_ml_systems.sql
-- Description: AI-powered features, ML models, training data, predictions
-- ================================================

-- AI Model Registry
CREATE TABLE IF NOT EXISTS `ai_models` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Model Identity
    `model_name` VARCHAR(255) NOT NULL,
    `model_type` ENUM('forecasting', 'classification', 'regression', 'nlp', 'computer_vision', 'recommendation') NOT NULL,
    `model_version` VARCHAR(50) NOT NULL,
    `algorithm` VARCHAR(100) NULL COMMENT 'Random Forest, Neural Network, etc.',

    -- Model Purpose
    `purpose` VARCHAR(500) NOT NULL COMMENT 'What this model predicts/classifies',
    `use_case` VARCHAR(255) NULL COMMENT 'inventory_forecast, customer_churn, etc.',

    -- Model Files
    `model_file_path` VARCHAR(500) NULL COMMENT 'Path to serialized model',
    `training_script_path` VARCHAR(500) NULL,
    `model_format` VARCHAR(50) NULL COMMENT 'pickle, h5, onnx, etc.',

    -- Performance Metrics
    `accuracy` DECIMAL(5,4) NULL COMMENT 'Classification accuracy',
    `precision_score` DECIMAL(5,4) NULL,
    `recall_score` DECIMAL(5,4) NULL,
    `f1_score` DECIMAL(5,4) NULL,
    `mse` DECIMAL(10,4) NULL COMMENT 'Mean Squared Error for regression',
    `rmse` DECIMAL(10,4) NULL COMMENT 'Root Mean Squared Error',
    `mae` DECIMAL(10,4) NULL COMMENT 'Mean Absolute Error',
    `r2_score` DECIMAL(5,4) NULL COMMENT 'R-squared for regression',

    -- Training Details
    `training_data_size` INT NULL COMMENT 'Number of training samples',
    `validation_data_size` INT NULL,
    `test_data_size` INT NULL,
    `features_used` JSON NULL COMMENT 'List of feature names',
    `hyperparameters` JSON NULL COMMENT 'Model configuration',

    -- Training Metadata
    `trained_at` TIMESTAMP NULL,
    `trained_by_user_id` BIGINT UNSIGNED NULL,
    `training_duration_seconds` INT NULL,
    `training_notes` TEXT NULL,

    -- Deployment
    `status` ENUM('training', 'trained', 'deployed', 'retired', 'failed') DEFAULT 'training',
    `deployed_at` TIMESTAMP NULL,
    `last_prediction_at` TIMESTAMP NULL,
    `total_predictions` INT DEFAULT 0,

    -- Model Monitoring
    `prediction_accuracy_30d` DECIMAL(5,4) NULL COMMENT 'Real-world accuracy',
    `drift_detected` BOOLEAN DEFAULT FALSE,
    `last_drift_check` TIMESTAMP NULL,

    -- Retraining Schedule
    `retrain_frequency_days` INT DEFAULT 30,
    `last_retrained_at` TIMESTAMP NULL,
    `next_retrain_due` DATE NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`trained_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_model_type` (`model_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_use_case` (`use_case`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Predictions & Forecasts
CREATE TABLE IF NOT EXISTS `ai_predictions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    `model_id` BIGINT UNSIGNED NOT NULL,
    `prediction_type` VARCHAR(100) NOT NULL COMMENT 'inventory_demand, customer_churn, etc.',

    -- Prediction Input
    `input_features` JSON NOT NULL COMMENT 'Features used for this prediction',

    -- Prediction Output
    `predicted_value` VARCHAR(500) NULL COMMENT 'Numeric or categorical prediction',
    `confidence_score` DECIMAL(5,4) NULL COMMENT '0-1 confidence level',
    `prediction_range_min` DECIMAL(15,2) NULL,
    `prediction_range_max` DECIMAL(15,2) NULL,

    -- Related Entity
    `entity_type` VARCHAR(100) NULL COMMENT 'product, customer, course, etc.',
    `entity_id` BIGINT UNSIGNED NULL,

    -- Prediction Metadata
    `prediction_date` DATE NOT NULL,
    `prediction_horizon_days` INT NULL COMMENT 'How far into future',
    `predicted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Actual Outcome (for accuracy tracking)
    `actual_value` VARCHAR(500) NULL,
    `actual_recorded_at` TIMESTAMP NULL,
    `prediction_error` DECIMAL(15,4) NULL COMMENT 'Difference from actual',

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `was_accurate` BOOLEAN NULL COMMENT 'TRUE if within acceptable range',

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`model_id`) REFERENCES `ai_models`(`id`) ON DELETE CASCADE,

    INDEX `idx_model` (`model_id`),
    INDEX `idx_prediction_type` (`prediction_type`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_prediction_date` (`prediction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Demand Forecasts (AI-powered)
CREATE TABLE IF NOT EXISTS `inventory_demand_forecasts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    `product_id` BIGINT UNSIGNED NOT NULL,
    `forecast_date` DATE NOT NULL,

    -- AI Predictions
    `predicted_demand` DECIMAL(10,2) NOT NULL,
    `confidence_level` DECIMAL(5,4) NOT NULL,
    `prediction_range_low` DECIMAL(10,2) NULL,
    `prediction_range_high` DECIMAL(10,2) NULL,

    -- Seasonality & Trends
    `seasonal_factor` DECIMAL(5,4) NULL COMMENT 'Seasonal multiplier',
    `trend_direction` ENUM('increasing', 'stable', 'decreasing') NULL,
    `trend_strength` DECIMAL(5,4) NULL,

    -- Contributing Factors
    `weather_factor` DECIMAL(5,4) NULL,
    `holiday_factor` DECIMAL(5,4) NULL,
    `promotion_factor` DECIMAL(5,4) NULL,
    `historical_pattern` VARCHAR(255) NULL,

    -- Recommended Actions
    `recommended_order_quantity` DECIMAL(10,2) NULL,
    `recommended_reorder_date` DATE NULL,
    `stock_out_probability` DECIMAL(5,4) NULL COMMENT '0-1 probability',
    `overstock_probability` DECIMAL(5,4) NULL,

    -- Actual Demand (for accuracy tracking)
    `actual_demand` DECIMAL(10,2) NULL,
    `forecast_accuracy` DECIMAL(5,4) NULL COMMENT 'Percentage accuracy',

    -- Model Used
    `model_id` BIGINT UNSIGNED NULL,
    `algorithm_used` VARCHAR(100) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`model_id`) REFERENCES `ai_models`(`id`) ON DELETE SET NULL,

    INDEX `idx_product_date` (`product_id`, `forecast_date`),
    INDEX `idx_forecast_date` (`forecast_date`),
    INDEX `idx_reorder_date` (`recommended_reorder_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Intelligence & Insights
CREATE TABLE IF NOT EXISTS `customer_ai_insights` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    `customer_id` BIGINT UNSIGNED NOT NULL,

    -- Churn Prediction
    `churn_probability` DECIMAL(5,4) NULL COMMENT '0-1 probability of churning',
    `churn_risk_level` ENUM('low', 'medium', 'high', 'critical') NULL,
    `predicted_churn_date` DATE NULL,
    `churn_factors` JSON NULL COMMENT 'Reasons contributing to churn risk',

    -- Lifetime Value Prediction
    `predicted_ltv` DECIMAL(10,2) NULL COMMENT 'Predicted lifetime value',
    `ltv_confidence` DECIMAL(5,4) NULL,
    `ltv_forecast_period_months` INT NULL,

    -- Next Purchase Prediction
    `next_purchase_probability` DECIMAL(5,4) NULL,
    `predicted_next_purchase_date` DATE NULL,
    `predicted_next_purchase_category` VARCHAR(100) NULL,
    `predicted_next_purchase_value` DECIMAL(10,2) NULL,

    -- Course Recommendations
    `recommended_courses` JSON NULL COMMENT 'AI-recommended courses with scores',
    `course_recommendation_reason` TEXT NULL,

    -- Engagement Score
    `engagement_score` DECIMAL(5,2) NULL COMMENT '0-100 engagement level',
    `engagement_trend` ENUM('increasing', 'stable', 'decreasing') NULL,
    `last_engagement_date` DATE NULL,

    -- Behavioral Segments
    `customer_segment` VARCHAR(100) NULL COMMENT 'AI-identified segment',
    `segment_characteristics` JSON NULL,
    `segment_confidence` DECIMAL(5,4) NULL,

    -- Satisfaction Prediction
    `predicted_satisfaction_score` DECIMAL(3,2) NULL COMMENT '1-5 predicted rating',
    `satisfaction_trend` ENUM('improving', 'stable', 'declining') NULL,

    -- Recommended Actions
    `recommended_actions` JSON NULL COMMENT 'Personalized retention strategies',
    `next_best_action` VARCHAR(500) NULL,
    `action_priority` ENUM('low', 'medium', 'high', 'urgent') NULL,

    -- Model Metadata
    `model_id` BIGINT UNSIGNED NULL,
    `last_analyzed_at` TIMESTAMP NULL,
    `next_analysis_due` DATE NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`model_id`) REFERENCES `ai_models`(`id`) ON DELETE SET NULL,

    UNIQUE KEY `unique_customer_insights` (`customer_id`),
    INDEX `idx_churn_risk` (`churn_risk_level`),
    INDEX `idx_engagement` (`engagement_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Chatbot Conversations
CREATE TABLE IF NOT EXISTS `ai_chatbot_conversations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    `session_id` VARCHAR(100) NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL COMMENT 'If staff member',

    -- Conversation Metadata
    `channel` ENUM('website', 'facebook', 'whatsapp', 'sms', 'email') DEFAULT 'website',
    `language` VARCHAR(10) DEFAULT 'en',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ended_at` TIMESTAMP NULL,

    -- AI Model
    `ai_model` VARCHAR(100) DEFAULT 'gpt-4' COMMENT 'OpenAI, Claude, etc.',
    `total_messages` INT DEFAULT 0,
    `ai_messages` INT DEFAULT 0,
    `human_messages` INT DEFAULT 0,

    -- Intent Detection
    `detected_intents` JSON NULL COMMENT 'Array of detected user intents',
    `primary_intent` VARCHAR(100) NULL COMMENT 'booking, question, complaint, etc.',
    `intent_confidence` DECIMAL(5,4) NULL,

    -- Sentiment Analysis
    `overall_sentiment` ENUM('very_positive', 'positive', 'neutral', 'negative', 'very_negative') NULL,
    `sentiment_score` DECIMAL(5,4) NULL COMMENT '-1 to 1',

    -- Outcomes
    `resolved` BOOLEAN DEFAULT FALSE,
    `escalated_to_human` BOOLEAN DEFAULT FALSE,
    `escalated_at` TIMESTAMP NULL,
    `escalated_to_user_id` BIGINT UNSIGNED NULL,
    `resolution_type` VARCHAR(100) NULL COMMENT 'booking_made, question_answered, etc.',

    -- Satisfaction
    `customer_feedback` ENUM('thumbs_up', 'thumbs_down', 'none') DEFAULT 'none',
    `feedback_comment` TEXT NULL,
    `satisfaction_score` INT NULL COMMENT '1-5 rating',

    -- Context
    `context_data` JSON NULL COMMENT 'Conversation context, entities extracted',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`escalated_to_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_session` (`session_id`),
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_started` (`started_at`),
    INDEX `idx_intent` (`primary_intent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chatbot Messages
CREATE TABLE IF NOT EXISTS `ai_chatbot_messages` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `conversation_id` BIGINT UNSIGNED NOT NULL,
    `message_type` ENUM('user', 'ai', 'system') NOT NULL,

    -- Message Content
    `message` TEXT NOT NULL,
    `message_html` TEXT NULL COMMENT 'Formatted HTML version',

    -- AI Response Metadata
    `prompt_tokens` INT NULL COMMENT 'Tokens used in prompt',
    `completion_tokens` INT NULL COMMENT 'Tokens in response',
    `total_tokens` INT NULL,
    `model_used` VARCHAR(100) NULL,
    `response_time_ms` INT NULL,

    -- Intent & Entities
    `detected_intent` VARCHAR(100) NULL,
    `confidence_score` DECIMAL(5,4) NULL,
    `extracted_entities` JSON NULL COMMENT 'Names, dates, products, etc.',

    -- Sentiment
    `sentiment` ENUM('positive', 'neutral', 'negative') NULL,
    `sentiment_score` DECIMAL(5,4) NULL,

    -- Actions Triggered
    `action_taken` VARCHAR(255) NULL COMMENT 'booking_created, email_sent, etc.',
    `action_result` JSON NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`conversation_id`) REFERENCES `ai_chatbot_conversations`(`id`) ON DELETE CASCADE,

    INDEX `idx_conversation` (`conversation_id`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_intent` (`detected_intent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Predictive Maintenance
CREATE TABLE IF NOT EXISTS `predictive_maintenance_alerts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    -- Equipment
    `equipment_type` VARCHAR(100) NOT NULL COMMENT 'compressor, tank, regulator, etc.',
    `equipment_id` BIGINT UNSIGNED NULL,
    `serial_number` VARCHAR(255) NULL,

    -- Prediction
    `failure_probability` DECIMAL(5,4) NOT NULL COMMENT '0-1 probability of failure',
    `risk_level` ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    `predicted_failure_date` DATE NULL,
    `confidence_level` DECIMAL(5,4) NULL,

    -- Failure Indicators
    `anomaly_detected` BOOLEAN DEFAULT FALSE,
    `anomaly_type` VARCHAR(255) NULL COMMENT 'vibration, temperature, pressure, etc.',
    `anomaly_score` DECIMAL(10,4) NULL,
    `contributing_factors` JSON NULL,

    -- Recommendations
    `recommended_action` TEXT NOT NULL,
    `recommended_date` DATE NULL,
    `estimated_cost` DECIMAL(10,2) NULL,
    `downtime_if_ignored_days` INT NULL,

    -- Usage Patterns
    `usage_hours_total` DECIMAL(10,2) NULL,
    `usage_hours_since_service` DECIMAL(10,2) NULL,
    `usage_intensity` ENUM('light', 'normal', 'heavy') NULL,

    -- Status
    `status` ENUM('active', 'acknowledged', 'scheduled', 'completed', 'ignored') DEFAULT 'active',
    `acknowledged_at` TIMESTAMP NULL,
    `acknowledged_by_user_id` BIGINT UNSIGNED NULL,
    `work_order_id` BIGINT UNSIGNED NULL,

    -- Model Used
    `model_id` BIGINT UNSIGNED NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`model_id`) REFERENCES `ai_models`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`acknowledged_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_risk_level` (`risk_level`),
    INDEX `idx_status` (`status`),
    INDEX `idx_equipment` (`equipment_type`, `equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Training Data
CREATE TABLE IF NOT EXISTS `ai_training_data` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    `model_id` BIGINT UNSIGNED NULL,
    `data_type` VARCHAR(100) NOT NULL COMMENT 'sales, inventory, customer_behavior, etc.',

    -- Data
    `features` JSON NOT NULL COMMENT 'Input features',
    `target` VARCHAR(500) NULL COMMENT 'Target/label for supervised learning',

    -- Data Quality
    `is_validated` BOOLEAN DEFAULT FALSE,
    `validation_score` DECIMAL(5,4) NULL,
    `has_outliers` BOOLEAN DEFAULT FALSE,
    `has_missing_values` BOOLEAN DEFAULT FALSE,

    -- Data Splits
    `split_type` ENUM('train', 'validation', 'test') NULL,

    -- Metadata
    `data_source` VARCHAR(255) NULL COMMENT 'Where this data came from',
    `collection_date` DATE NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`model_id`) REFERENCES `ai_models`(`id`) ON DELETE SET NULL,

    INDEX `idx_model` (`model_id`),
    INDEX `idx_data_type` (`data_type`),
    INDEX `idx_split` (`split_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Natural Language Processing (NLP) Entities
CREATE TABLE IF NOT EXISTS `nlp_extracted_entities` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `source_type` VARCHAR(100) NOT NULL COMMENT 'review, chatbot, email, etc.',
    `source_id` BIGINT UNSIGNED NOT NULL,
    `text_content` TEXT NOT NULL,

    -- Extracted Entities
    `entities` JSON NOT NULL COMMENT 'Names, dates, products, locations, etc.',

    -- Named Entity Recognition
    `persons` JSON NULL,
    `organizations` JSON NULL,
    `locations` JSON NULL,
    `dates` JSON NULL,
    `products` JSON NULL,
    `courses` JSON NULL,

    -- Sentiment
    `sentiment` ENUM('very_positive', 'positive', 'neutral', 'negative', 'very_negative') NULL,
    `sentiment_score` DECIMAL(5,4) NULL,

    -- Topics
    `topics` JSON NULL COMMENT 'Main topics discussed',
    `keywords` JSON NULL,

    -- Language
    `detected_language` VARCHAR(10) NULL,
    `language_confidence` DECIMAL(5,4) NULL,

    `processed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_source` (`source_type`, `source_id`),
    INDEX `idx_sentiment` (`sentiment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI-Powered Pricing Optimization
CREATE TABLE IF NOT EXISTS `dynamic_pricing_recommendations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` BIGINT UNSIGNED NULL,

    `product_id` BIGINT UNSIGNED NOT NULL,
    `current_price` DECIMAL(10,2) NOT NULL,

    -- AI Recommendations
    `recommended_price` DECIMAL(10,2) NOT NULL,
    `price_change_percentage` DECIMAL(5,2) NOT NULL,
    `confidence_score` DECIMAL(5,4) NOT NULL,

    -- Reasoning
    `recommendation_factors` JSON NULL COMMENT 'Demand, competition, seasonality, etc.',
    `demand_elasticity` DECIMAL(5,4) NULL,
    `competitive_position` VARCHAR(100) NULL,

    -- Expected Impact
    `expected_sales_increase_pct` DECIMAL(5,2) NULL,
    `expected_revenue_increase` DECIMAL(10,2) NULL,
    `expected_margin_impact` DECIMAL(5,2) NULL,

    -- Market Conditions
    `competitor_average_price` DECIMAL(10,2) NULL,
    `market_demand_level` ENUM('very_low', 'low', 'normal', 'high', 'very_high') NULL,
    `seasonal_factor` DECIMAL(5,4) NULL,

    -- Status
    `status` ENUM('pending', 'approved', 'applied', 'rejected') DEFAULT 'pending',
    `applied_at` TIMESTAMP NULL,
    `approved_by_user_id` BIGINT UNSIGNED NULL,

    -- Effectiveness (tracked after implementation)
    `actual_sales_change_pct` DECIMAL(5,2) NULL,
    `actual_revenue_change` DECIMAL(10,2) NULL,
    `recommendation_accuracy` DECIMAL(5,4) NULL,

    `valid_from` DATE NULL,
    `valid_to` DATE NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,

    INDEX `idx_product` (`product_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_valid_dates` (`valid_from`, `valid_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Initial AI Models
INSERT INTO `ai_models` (`model_name`, `model_type`, `model_version`, `purpose`, `use_case`, `status`) VALUES
('Inventory Demand Forecaster', 'forecasting', '1.0', 'Predict product demand for next 30 days', 'inventory_forecast', 'deployed'),
('Customer Churn Predictor', 'classification', '1.0', 'Identify customers at risk of churning', 'customer_churn', 'deployed'),
('Course Recommendation Engine', 'recommendation', '1.0', 'Recommend next courses for customers', 'course_recommendation', 'deployed'),
('Price Optimization Model', 'regression', '1.0', 'Optimize product pricing based on demand', 'pricing_optimization', 'training'),
('Equipment Failure Predictor', 'classification', '1.0', 'Predict equipment failures before they occur', 'predictive_maintenance', 'deployed'),
('Sentiment Analyzer', 'nlp', '1.0', 'Analyze customer review sentiment', 'sentiment_analysis', 'deployed'),
('Chatbot Intent Classifier', 'nlp', '1.0', 'Classify customer intent from messages', 'chatbot_intent', 'deployed');


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;