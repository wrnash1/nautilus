-- =====================================================
-- Marketing Campaigns System
-- Enables creation and management of multi-channel marketing campaigns
-- =====================================================

-- Marketing Campaigns Table
CREATE TABLE IF NOT EXISTS "marketing_campaigns" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "description" TEXT NULL,
    "campaign_type" ENUM('email', 'sms', 'multi_channel', 'social', 'retargeting') NOT NULL DEFAULT 'email',
    "status" ENUM('draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    "objective" ENUM('awareness', 'engagement', 'conversion', 'retention', 'reactivation') NOT NULL,

    -- Scheduling
    "start_date" TIMESTAMP NULL,
    "end_date" TIMESTAMP NULL,
    "timezone" VARCHAR(50) DEFAULT 'UTC',

    -- Targeting
    "target_audience" JSON NULL COMMENT 'Segmentation criteria',
    "estimated_reach" INTEGER DEFAULT 0,
    "actual_reach" INTEGER DEFAULT 0,

    -- Budget & ROI
    "budget" DECIMAL(10, 2) DEFAULT 0.00,
    "spent" DECIMAL(10, 2) DEFAULT 0.00,
    "revenue_generated" DECIMAL(10, 2) DEFAULT 0.00,

    -- Performance Metrics
    "total_sent" INTEGER DEFAULT 0,
    "total_delivered" INTEGER DEFAULT 0,
    "total_opened" INTEGER DEFAULT 0,
    "total_clicked" INTEGER DEFAULT 0,
    "total_conversions" INTEGER DEFAULT 0,
    "total_unsubscribes" INTEGER DEFAULT 0,

    -- Rates (calculated)
    "delivery_rate" DECIMAL(5, 2) DEFAULT 0.00,
    "open_rate" DECIMAL(5, 2) DEFAULT 0.00,
    "click_rate" DECIMAL(5, 2) DEFAULT 0.00,
    "conversion_rate" DECIMAL(5, 2) DEFAULT 0.00,
    "roi" DECIMAL(10, 2) DEFAULT 0.00,

    -- A/B Testing
    "is_ab_test" BOOLEAN DEFAULT FALSE,
    "ab_test_variant" ENUM('A', 'B', 'control') NULL,
    "ab_test_metric" VARCHAR(50) NULL COMMENT 'Primary metric being tested',

    -- Ownership
    "created_by" INTEGER NULL,
    "updated_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tenant_status ("tenant_id", "status"),
    INDEX idx_dates ("start_date", "end_date"),
    INDEX idx_campaign_type ("campaign_type"),
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE
);

-- Campaign Email Content
CREATE TABLE IF NOT EXISTS "campaign_emails" (
    "id" SERIAL PRIMARY KEY,
    "campaign_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,
    "variant_name" VARCHAR(50) DEFAULT 'default' COMMENT 'For A/B testing',

    -- Email Details
    "subject_line" VARCHAR(255) NOT NULL,
    "preview_text" VARCHAR(150) NULL,
    "from_name" VARCHAR(100) NOT NULL,
    "from_email" VARCHAR(255) NOT NULL,
    "reply_to_email" VARCHAR(255) NULL,

    -- Content
    "html_content" LONGTEXT NOT NULL,
    "plain_text_content" TEXT NULL,
    "template_id" INTEGER NULL COMMENT 'Reference to email_templates',

    -- Personalization
    "personalization_tags" JSON NULL COMMENT 'Available merge tags',
    "dynamic_content_rules" JSON NULL COMMENT 'Conditional content rules',

    -- Attachments
    "attachments" JSON NULL COMMENT 'File paths and metadata',

    -- Tracking
    "track_opens" BOOLEAN DEFAULT TRUE,
    "track_clicks" BOOLEAN DEFAULT TRUE,
    "tracking_domain" VARCHAR(255) NULL,

    -- Performance (for this variant)
    "sent_count" INTEGER DEFAULT 0,
    "open_count" INTEGER DEFAULT 0,
    "click_count" INTEGER DEFAULT 0,
    "conversion_count" INTEGER DEFAULT 0,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("campaign_id") REFERENCES "marketing_campaigns"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_campaign ("campaign_id")
);

-- Campaign SMS Content
CREATE TABLE IF NOT EXISTS "campaign_sms" (
    "id" SERIAL PRIMARY KEY,
    "campaign_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,
    "variant_name" VARCHAR(50) DEFAULT 'default',

    -- SMS Details
    "message_content" VARCHAR(1600) NOT NULL COMMENT 'Max 10 SMS segments',
    "sender_id" VARCHAR(11) NULL COMMENT 'Alphanumeric sender ID',

    -- Personalization
    "personalization_tags" JSON NULL,

    -- Link Tracking
    "short_url" VARCHAR(255) NULL,
    "track_clicks" BOOLEAN DEFAULT TRUE,

    -- Performance
    "sent_count" INTEGER DEFAULT 0,
    "delivered_count" INTEGER DEFAULT 0,
    "click_count" INTEGER DEFAULT 0,
    "conversion_count" INTEGER DEFAULT 0,
    "segment_count" SMALLINT UNSIGNED DEFAULT 1 COMMENT 'Number of SMS segments',

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("campaign_id") REFERENCES "marketing_campaigns"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_campaign ("campaign_id")
);

-- Campaign Recipients
CREATE TABLE IF NOT EXISTS "campaign_recipients" (
    "id" BIGSERIAL PRIMARY KEY,
    "campaign_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,

    -- Contact Info (denormalized for historical record)
    "email" VARCHAR(255) NULL,
    "phone" VARCHAR(20) NULL,

    -- Segmentation
    "segment_id" INTEGER NULL,
    "segment_match_criteria" JSON NULL COMMENT 'Why this recipient was selected',

    -- Delivery Status
    "status" ENUM('pending', 'sent', 'delivered', 'bounced', 'failed', 'unsubscribed') DEFAULT 'pending',
    "sent_at" TIMESTAMP NULL,
    "delivered_at" TIMESTAMP NULL,
    "bounced_at" TIMESTAMP NULL,
    "bounce_reason" TEXT NULL,

    -- Engagement
    "opened_at" TIMESTAMP NULL,
    "first_click_at" TIMESTAMP NULL,
    "total_opens" INTEGER DEFAULT 0,
    "total_clicks" INTEGER DEFAULT 0,
    "last_activity_at" TIMESTAMP NULL,

    -- Conversion
    "converted" BOOLEAN DEFAULT FALSE,
    "converted_at" TIMESTAMP NULL,
    "conversion_value" DECIMAL(10, 2) NULL,
    "conversion_type" VARCHAR(50) NULL COMMENT 'e.g., booking, purchase, signup',

    -- A/B Testing
    "variant_assigned" VARCHAR(50) DEFAULT 'default',

    -- Unsubscribe
    "unsubscribed" BOOLEAN DEFAULT FALSE,
    "unsubscribed_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("campaign_id") REFERENCES "marketing_campaigns"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    INDEX idx_campaign_customer ("campaign_id", "customer_id"),
    INDEX idx_status ("status"),
    INDEX idx_engagement ("opened_at", "first_click_at"),
    UNIQUE KEY unique_campaign_customer ("campaign_id", "customer_id")
);

-- Campaign Click Tracking
CREATE TABLE IF NOT EXISTS "campaign_link_clicks" (
    "id" BIGSERIAL PRIMARY KEY,
    "campaign_id" INTEGER NOT NULL,
    "recipient_id" BIGINT NOT NULL,
    "tenant_id" INTEGER NOT NULL,

    -- Link Details
    "original_url" TEXT NOT NULL,
    "tracked_url" TEXT NULL,
    "link_label" VARCHAR(255) NULL COMMENT 'CTA button text or link name',
    "link_position" VARCHAR(50) NULL COMMENT 'e.g., header, body, footer',

    -- Click Details
    "clicked_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "ip_address" VARCHAR(45) NULL,
    "user_agent" TEXT NULL,
    "device_type" ENUM('desktop', 'mobile', 'tablet', 'unknown') DEFAULT 'unknown',
    "browser" VARCHAR(100) NULL,
    "os" VARCHAR(100) NULL,

    -- Geographic
    "country" VARCHAR(2) NULL,
    "region" VARCHAR(100) NULL,
    "city" VARCHAR(100) NULL,

    FOREIGN KEY ("campaign_id") REFERENCES "marketing_campaigns"("id") ON DELETE CASCADE,
    FOREIGN KEY ("recipient_id") REFERENCES "campaign_recipients"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_campaign_recipient ("campaign_id", "recipient_id"),
    INDEX idx_clicked_at ("clicked_at")
);

-- Campaign Performance by Day
CREATE TABLE IF NOT EXISTS "campaign_daily_stats" (
    "id" SERIAL PRIMARY KEY,
    "campaign_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,
    "stat_date" DATE NOT NULL,

    -- Daily Metrics
    "sent" INTEGER DEFAULT 0,
    "delivered" INTEGER DEFAULT 0,
    "opened" INTEGER DEFAULT 0,
    "clicked" INTEGER DEFAULT 0,
    "conversions" INTEGER DEFAULT 0,
    "bounces" INTEGER DEFAULT 0,
    "unsubscribes" INTEGER DEFAULT 0,

    -- Revenue
    "revenue" DECIMAL(10, 2) DEFAULT 0.00,
    "cost" DECIMAL(10, 2) DEFAULT 0.00,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("campaign_id") REFERENCES "marketing_campaigns"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_date ("campaign_id", "stat_date"),
    INDEX idx_stat_date ("stat_date")
);

-- =====================================================
-- Sample Marketing Campaigns (Pre-seeded)
-- =====================================================

INSERT INTO "marketing_campaigns" (
    "tenant_id", "name", "description", "campaign_type", "status", "objective",
    "start_date", "budget", "estimated_reach"
) VALUES
(1, 'Spring Open Water Promotion', 'Promote discounted Open Water certification courses for spring season', 'email', 'draft', 'conversion', '2024-03-01 09:00:00', 500.00, 2500),
(1, 'Certification Expiry Reminders', 'Automated reminders for customers with expiring certifications', 'multi_channel', 'active', 'retention', '2024-01-01 00:00:00', 200.00, 800),
(1, 'Equipment Sale - Summer Clearance', 'Clear summer inventory with 30% off sale', 'email', 'completed', 'conversion', '2024-08-15 09:00:00', 750.00, 3200),
(1, 'Dive Trip - Maldives Adventure', 'Promote exclusive Maldives dive trip package', 'multi_channel', 'scheduled', 'conversion', '2024-12-01 09:00:00', 1500.00, 500),
(1, 'Win-Back Inactive Customers', 'Re-engage customers who haven\'t booked in 12+ months', 'email', 'draft', 'reactivation', '2024-11-20 09:00:00', 300.00, 1200),
(1, 'New Customer Welcome Series', 'Automated 5-email welcome series for new customers', 'email', 'active', 'engagement', '2024-01-01 00:00:00', 150.00, 5000);
