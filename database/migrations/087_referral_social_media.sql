-- =====================================================
-- Referral Program & Social Media Integration
-- =====================================================

-- Referral Programs
CREATE TABLE IF NOT EXISTS `referral_programs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('active', 'paused', 'ended') DEFAULT 'active',

    -- Program Type
    `reward_type` ENUM('discount_percentage', 'discount_fixed', 'credit', 'free_course', 'free_product', 'points') NOT NULL,
    `referrer_reward_amount` DECIMAL(10, 2) NOT NULL COMMENT 'Reward for person who refers',
    `referee_reward_amount` DECIMAL(10, 2) NOT NULL COMMENT 'Reward for new customer',

    -- Conditions
    `min_purchase_amount` DECIMAL(10, 2) NULL COMMENT 'Referee must spend this much',
    `max_referrals_per_customer` INT UNSIGNED NULL,
    `reward_conversion_event` ENUM('signup', 'first_purchase', 'first_dive', 'certification_complete') DEFAULT 'first_purchase',

    -- Timing
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `reward_expiry_days` INT UNSIGNED DEFAULT 90,

    -- Sharing
    `share_message_template` TEXT NULL,
    `landing_page_url` VARCHAR(500) NULL,
    `terms_and_conditions` TEXT NULL,

    -- Performance
    `total_referrals` INT UNSIGNED DEFAULT 0,
    `successful_referrals` INT UNSIGNED DEFAULT 0,
    `total_revenue_generated` DECIMAL(10, 2) DEFAULT 0.00,
    `total_rewards_given` DECIMAL(10, 2) DEFAULT 0.00,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_status (`tenant_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Referral Codes
CREATE TABLE IF NOT EXISTS `customer_referral_codes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `program_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,

    -- Referral Code
    `referral_code` VARCHAR(50) NOT NULL UNIQUE,
    `custom_url` VARCHAR(500) NULL COMMENT 'Personalized referral URL',

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `expires_at` DATE NULL,

    -- Performance
    `total_clicks` INT UNSIGNED DEFAULT 0,
    `total_referrals` INT UNSIGNED DEFAULT 0,
    `successful_referrals` INT UNSIGNED DEFAULT 0,
    `total_revenue_generated` DECIMAL(10, 2) DEFAULT 0.00,
    `total_rewards_earned` DECIMAL(10, 2) DEFAULT 0.00,
    `pending_rewards` DECIMAL(10, 2) DEFAULT 0.00,

    -- Sharing Stats
    `shared_via_email` INT UNSIGNED DEFAULT 0,
    `shared_via_sms` INT UNSIGNED DEFAULT 0,
    `shared_via_social` INT UNSIGNED DEFAULT 0,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`program_id`) REFERENCES `referral_programs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_referral_code (`referral_code`),
    INDEX idx_customer (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals (tracking individual referrals)
CREATE TABLE IF NOT EXISTS `referrals` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `program_id` INT UNSIGNED NOT NULL,
    `referrer_id` INT UNSIGNED NOT NULL COMMENT 'Customer who referred',
    `referee_id` INT UNSIGNED NULL COMMENT 'New customer (null until signup)',
    `tenant_id` INT UNSIGNED NOT NULL,

    -- Referral Details
    `referral_code_id` INT UNSIGNED NOT NULL,
    `referral_code` VARCHAR(50) NOT NULL,

    -- Contact Info (before signup)
    `referee_email` VARCHAR(255) NULL,
    `referee_phone` VARCHAR(20) NULL,
    `referee_name` VARCHAR(255) NULL,

    -- Tracking
    `clicked_at` DATETIME NULL,
    `signed_up_at` DATETIME NULL,
    `converted_at` DATETIME NULL COMMENT 'When they completed qualifying action',

    -- Status
    `status` ENUM('clicked', 'signed_up', 'qualified', 'rewarded', 'expired', 'rejected') DEFAULT 'clicked',

    -- Conversion Details
    `conversion_order_id` INT UNSIGNED NULL,
    `conversion_value` DECIMAL(10, 2) NULL,

    -- Rewards
    `referrer_reward_type` VARCHAR(50) NULL,
    `referrer_reward_amount` DECIMAL(10, 2) NULL,
    `referrer_rewarded` BOOLEAN DEFAULT FALSE,
    `referrer_rewarded_at` DATETIME NULL,

    `referee_reward_type` VARCHAR(50) NULL,
    `referee_reward_amount` DECIMAL(10, 2) NULL,
    `referee_rewarded` BOOLEAN DEFAULT FALSE,
    `referee_rewarded_at` DATETIME NULL,

    -- Attribution
    `referral_source` ENUM('direct_link', 'email', 'sms', 'social_media', 'qr_code') NULL,
    `utm_source` VARCHAR(100) NULL,
    `utm_medium` VARCHAR(100) NULL,
    `utm_campaign` VARCHAR(100) NULL,

    -- Geographic
    `ip_address` VARCHAR(45) NULL,
    `country` VARCHAR(2) NULL,
    `city` VARCHAR(100) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`program_id`) REFERENCES `referral_programs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`referrer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`referee_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`referral_code_id`) REFERENCES `customer_referral_codes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_referrer (`referrer_id`),
    INDEX idx_referee (`referee_id`),
    INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social Media Accounts
CREATE TABLE IF NOT EXISTS `social_media_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `platform` ENUM('facebook', 'instagram', 'twitter', 'linkedin', 'youtube', 'tiktok', 'pinterest') NOT NULL,
    `account_name` VARCHAR(255) NOT NULL,
    `account_handle` VARCHAR(100) NULL,
    `account_url` VARCHAR(500) NULL,

    -- API Integration
    `is_connected` BOOLEAN DEFAULT FALSE,
    `access_token` TEXT NULL COMMENT 'Encrypted',
    `refresh_token` TEXT NULL COMMENT 'Encrypted',
    `token_expires_at` DATETIME NULL,

    -- Configuration
    `auto_post_enabled` BOOLEAN DEFAULT FALSE,
    `post_types` JSON NULL COMMENT 'What to auto-post: courses, trips, promos',

    -- Performance
    `total_posts` INT UNSIGNED DEFAULT 0,
    `total_followers` INT UNSIGNED DEFAULT 0,
    `total_engagement` INT UNSIGNED DEFAULT 0,
    `last_sync_at` DATETIME NULL,

    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_platform (`tenant_id`, `platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social Media Posts
CREATE TABLE IF NOT EXISTS `social_media_posts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED NULL COMMENT 'Associated marketing campaign',

    -- Post Content
    `post_text` TEXT NOT NULL,
    `media_urls` JSON NULL COMMENT 'Images, videos',
    `link_url` VARCHAR(500) NULL,
    `hashtags` JSON NULL,

    -- Post Type
    `post_type` ENUM('promotional', 'educational', 'engagement', 'announcement', 'ugc') NOT NULL,

    -- Scheduling
    `status` ENUM('draft', 'scheduled', 'posted', 'failed') DEFAULT 'draft',
    `scheduled_for` DATETIME NULL,
    `posted_at` DATETIME NULL,

    -- Platform-specific IDs
    `platform_post_id` VARCHAR(255) NULL,
    `platform_url` VARCHAR(500) NULL,

    -- Performance Metrics
    `likes` INT UNSIGNED DEFAULT 0,
    `comments` INT UNSIGNED DEFAULT 0,
    `shares` INT UNSIGNED DEFAULT 0,
    `clicks` INT UNSIGNED DEFAULT 0,
    `reach` INT UNSIGNED DEFAULT 0,
    `impressions` INT UNSIGNED DEFAULT 0,
    `engagement_rate` DECIMAL(5, 2) DEFAULT 0.00,

    -- Error Handling
    `error_message` TEXT NULL,
    `retry_count` TINYINT UNSIGNED DEFAULT 0,

    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_synced_at` DATETIME NULL,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`account_id`) REFERENCES `social_media_accounts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`campaign_id`) REFERENCES `marketing_campaigns`(`id`) ON DELETE SET NULL,
    INDEX idx_account_status (`account_id`, `status`),
    INDEX idx_scheduled (`scheduled_for`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social Media Lead Forms (Facebook/Instagram Lead Ads)
CREATE TABLE IF NOT EXISTS `social_media_leads` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `platform` VARCHAR(50) NOT NULL,

    -- Lead Information
    `platform_lead_id` VARCHAR(255) NULL,
    `full_name` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,

    -- Lead Data
    `form_name` VARCHAR(255) NULL,
    `ad_name` VARCHAR(255) NULL,
    `campaign_name` VARCHAR(255) NULL,
    `custom_fields` JSON NULL COMMENT 'Additional form fields',

    -- Processing
    `customer_id` INT UNSIGNED NULL COMMENT 'Created customer record',
    `processed` BOOLEAN DEFAULT FALSE,
    `processed_at` DATETIME NULL,
    `assigned_to` INT UNSIGNED NULL COMMENT 'Staff member',

    -- Follow-up
    `follow_up_status` ENUM('new', 'contacted', 'qualified', 'converted', 'disqualified') DEFAULT 'new',
    `notes` TEXT NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`account_id`) REFERENCES `social_media_accounts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    INDEX idx_platform_lead (`platform`, `platform_lead_id`),
    INDEX idx_email (`email`),
    INDEX idx_processed (`processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Influencer Partnerships
CREATE TABLE IF NOT EXISTS `influencer_partnerships` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `influencer_name` VARCHAR(255) NOT NULL,
    `platform` VARCHAR(50) NOT NULL,
    `handle` VARCHAR(100) NULL,

    -- Contact
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `manager_name` VARCHAR(255) NULL,
    `manager_email` VARCHAR(255) NULL,

    -- Partnership Details
    `partnership_type` ENUM('sponsored_post', 'affiliate', 'ambassador', 'collaboration') NOT NULL,
    `status` ENUM('prospect', 'negotiating', 'active', 'paused', 'ended') DEFAULT 'prospect',
    `start_date` DATE NULL,
    `end_date` DATE NULL,

    -- Metrics
    `follower_count` INT UNSIGNED NULL,
    `avg_engagement_rate` DECIMAL(5, 2) NULL,
    `niche` VARCHAR(100) NULL COMMENT 'diving, travel, adventure, etc.',

    -- Compensation
    `compensation_type` ENUM('flat_fee', 'per_post', 'commission', 'free_product', 'hybrid') NOT NULL,
    `compensation_amount` DECIMAL(10, 2) NULL,
    `commission_rate` DECIMAL(5, 2) NULL,

    -- Affiliate Tracking
    `affiliate_code` VARCHAR(50) NULL,
    `total_referrals` INT UNSIGNED DEFAULT 0,
    `total_revenue` DECIMAL(10, 2) DEFAULT 0.00,
    `total_commission_paid` DECIMAL(10, 2) DEFAULT 0.00,

    -- Performance
    `total_posts` INT UNSIGNED DEFAULT 0,
    `total_reach` BIGINT UNSIGNED DEFAULT 0,
    `total_engagement` BIGINT UNSIGNED DEFAULT 0,
    `total_conversions` INT UNSIGNED DEFAULT 0,

    `notes` TEXT NULL,
    `contract_url` VARCHAR(500) NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    INDEX idx_tenant_status (`tenant_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pre-seeded Data
-- =====================================================

-- Sample Referral Program
INSERT INTO `referral_programs` (
    `tenant_id`, `program_name`, `description`, `status`, `reward_type`,
    `referrer_reward_amount`, `referee_reward_amount`, `reward_conversion_event`, `start_date`
) VALUES
(1, 'Dive Buddy Referral Program',
    'Refer a friend and you both get $50 off your next course!',
    'active', 'credit', 50.00, 50.00, 'first_purchase', '2024-01-01'),

(1, 'Open Water Graduate Referral',
    'Share your certification success! Get 20% off Advanced course for each referral.',
    'active', 'discount_percentage', 20.00, 15.00, 'certification_complete', '2024-01-01');

-- Social Media Accounts (placeholders)
INSERT INTO `social_media_accounts` (
    `tenant_id`, `platform`, `account_name`, `account_handle`, `account_url`, `is_active`
) VALUES
(1, 'facebook', 'Dive Shop Facebook', '@diveshop', 'https://facebook.com/diveshop', TRUE),
(1, 'instagram', 'Dive Shop Instagram', '@diveshop', 'https://instagram.com/diveshop', TRUE),
(1, 'youtube', 'Dive Shop YouTube', '@diveshop', 'https://youtube.com/@diveshop', TRUE);
