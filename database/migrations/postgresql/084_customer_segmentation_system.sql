-- =====================================================
-- Customer Segmentation System
-- Advanced customer segmentation for targeted marketing
-- =====================================================

-- Customer Segments
CREATE TABLE IF NOT EXISTS "customer_segments" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "description" TEXT NULL,
    "segment_type" ENUM('static', 'dynamic', 'predictive', 'behavioral', 'demographic') NOT NULL DEFAULT 'dynamic',
    "status" ENUM('active', 'inactive', 'archived') DEFAULT 'active',

    -- Segmentation Rules (JSON criteria)
    "criteria" JSON NOT NULL COMMENT 'Segmentation rules and conditions',
    "logic" ENUM('AND', 'OR') DEFAULT 'AND' COMMENT 'How to combine multiple criteria',

    -- Membership
    "current_member_count" INTEGER DEFAULT 0,
    "estimated_value" DECIMAL(10, 2) DEFAULT 0.00 COMMENT 'Total LTV of segment',

    -- Refresh Settings
    "auto_refresh" BOOLEAN DEFAULT TRUE,
    "refresh_frequency" ENUM('realtime', 'hourly', 'daily', 'weekly') DEFAULT 'daily',
    "last_refreshed_at" TIMESTAMP NULL,
    "next_refresh_at" TIMESTAMP NULL,

    -- Performance Tracking
    "campaigns_sent" INTEGER DEFAULT 0,
    "total_revenue" DECIMAL(10, 2) DEFAULT 0.00,
    "avg_open_rate" DECIMAL(5, 2) DEFAULT 0.00,
    "avg_click_rate" DECIMAL(5, 2) DEFAULT 0.00,
    "avg_conversion_rate" DECIMAL(5, 2) DEFAULT 0.00,

    -- Ownership
    "created_by" INTEGER NULL,
    "updated_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_tenant_status ("tenant_id", "status"),
    INDEX idx_type ("segment_type")
);

-- Segment Membership (many-to-many)
CREATE TABLE IF NOT EXISTS "segment_members" (
    "id" BIGSERIAL PRIMARY KEY,
    "segment_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,

    -- Match Details
    "matched_criteria" JSON NULL COMMENT 'Which criteria this customer matched',
    "match_score" DECIMAL(5, 2) NULL COMMENT 'Relevance score 0-100',

    -- Membership Tracking
    "added_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "removed_at" TIMESTAMP NULL,
    "is_active" BOOLEAN DEFAULT TRUE,

    -- Customer Snapshot (denormalized for segment analysis)
    "customer_ltv" DECIMAL(10, 2) NULL,
    "total_bookings" INTEGER DEFAULT 0,
    "engagement_score" INTEGER DEFAULT 0,
    "last_purchase_date" DATE NULL,

    FOREIGN KEY ("segment_id") REFERENCES "customer_segments"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    UNIQUE KEY unique_segment_customer ("segment_id", "customer_id"),
    INDEX idx_customer ("customer_id"),
    INDEX idx_active ("is_active")
);

-- Segment Criteria Library (reusable criteria)
CREATE TABLE IF NOT EXISTS "segment_criteria_library" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "name" VARCHAR(255) NOT NULL,
    "category" ENUM('demographic', 'behavioral', 'transactional', 'engagement', 'predictive') NOT NULL,
    "description" TEXT NULL,

    -- Criteria Definition
    "field" VARCHAR(100) NOT NULL COMMENT 'Database field or calculated metric',
    "operator" ENUM('equals', 'not_equals', 'greater_than', 'less_than', 'contains', 'in_list', 'between', 'is_null', 'is_not_null') NOT NULL,
    "value" VARCHAR(255) NULL,
    "value_type" ENUM('string', 'number', 'date', 'boolean', 'array') DEFAULT 'string',

    -- SQL Template
    "sql_template" TEXT NULL COMMENT 'SQL WHERE clause template',

    -- Usage Stats
    "times_used" INTEGER DEFAULT 0,
    "last_used_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_category ("category")
);

-- RFM Analysis (Recency, Frequency, Monetary)
CREATE TABLE IF NOT EXISTS "customer_rfm_scores" (
    "id" BIGSERIAL PRIMARY KEY,
    "customer_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,
    "analysis_date" DATE NOT NULL,

    -- Recency (days since last purchase)
    "recency_days" INTEGER NOT NULL,
    "recency_score" SMALLINT UNSIGNED NOT NULL COMMENT '1-5 scale',

    -- Frequency (number of purchases)
    "frequency_count" INTEGER NOT NULL,
    "frequency_score" SMALLINT UNSIGNED NOT NULL COMMENT '1-5 scale',

    -- Monetary (total spend)
    "monetary_value" DECIMAL(10, 2) NOT NULL,
    "monetary_score" SMALLINT UNSIGNED NOT NULL COMMENT '1-5 scale',

    -- Combined Score
    "rfm_score" VARCHAR(3) NOT NULL COMMENT 'e.g., "555" for best customers',
    "rfm_segment" VARCHAR(50) NOT NULL COMMENT 'e.g., Champions, Loyal, At Risk',

    -- Score Interpretation
    "customer_value" ENUM('high', 'medium', 'low') NOT NULL,
    "churn_risk" ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    "recommended_action" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    UNIQUE KEY unique_customer_date ("customer_id", "analysis_date"),
    INDEX idx_rfm_segment ("rfm_segment"),
    INDEX idx_analysis_date ("analysis_date")
);

-- Customer Lifecycle Stages
CREATE TABLE IF NOT EXISTS "customer_lifecycle_stages" (
    "id" BIGSERIAL PRIMARY KEY,
    "customer_id" INTEGER NOT NULL,
    "tenant_id" INTEGER NOT NULL,

    -- Current Stage
    "current_stage" ENUM('prospect', 'new', 'active', 'loyal', 'at_risk', 'dormant', 'lost', 'win_back') NOT NULL,
    "stage_entered_at" TIMESTAMP NOT NULL,

    -- Previous Stage
    "previous_stage" VARCHAR(50) NULL,
    "previous_stage_duration_days" INTEGER NULL,

    -- Stage Metrics
    "days_in_current_stage" INTEGER DEFAULT 0,
    "total_value_in_stage" DECIMAL(10, 2) DEFAULT 0.00,
    "interactions_in_stage" INTEGER DEFAULT 0,

    -- Progression Probability
    "next_likely_stage" VARCHAR(50) NULL,
    "progression_probability" DECIMAL(5, 2) NULL COMMENT 'AI predicted probability',

    -- Automated Actions
    "automation_triggered" BOOLEAN DEFAULT FALSE,
    "automation_campaign_id" INTEGER NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_current_stage ("current_stage"),
    INDEX idx_customer ("customer_id"),
    UNIQUE KEY unique_customer_current ("customer_id")
);

-- =====================================================
-- Pre-seeded Segments
-- =====================================================

INSERT INTO "customer_segments" (
    "tenant_id", "name", "description", "segment_type", "criteria", "logic", "auto_refresh", "refresh_frequency"
) VALUES
(1, 'VIP Customers', 'High-value customers with $5000+ lifetime spend', 'dynamic',
    '{"rules": [{"field": "lifetime_value", "operator": "greater_than", "value": 5000}]}',
    'AND', TRUE, 'daily'),

(1, 'Recent Divers', 'Customers who completed a dive in the last 30 days', 'dynamic',
    '{"rules": [{"field": "last_dive_date", "operator": "greater_than", "value": "DATE_SUB(NOW(), INTERVAL 30 DAY)"}]}',
    'AND', TRUE, 'daily'),

(1, 'Certification Due', 'Customers with certifications expiring within 60 days', 'dynamic',
    '{"rules": [{"field": "cert_expiry_date", "operator": "between", "value": ["NOW()", "DATE_ADD(NOW(), INTERVAL 60 DAY)"]}]}',
    'AND', TRUE, 'daily'),

(1, 'Open Water Graduates', 'Customers who completed Open Water but no advanced courses', 'static',
    '{"rules": [{"field": "courses_completed", "operator": "contains", "value": "Open Water"}, {"field": "courses_completed", "operator": "not_contains", "value": "Advanced"}]}',
    'AND', TRUE, 'weekly'),

(1, 'At-Risk Customers', 'No activity in 6+ months, previously active', 'predictive',
    '{"rules": [{"field": "last_interaction_date", "operator": "less_than", "value": "DATE_SUB(NOW(), INTERVAL 6 MONTH)"}, {"field": "total_bookings", "operator": "greater_than", "value": 2}]}',
    'AND', TRUE, 'weekly'),

(1, 'New Subscribers', 'Signed up for newsletter in last 7 days', 'dynamic',
    '{"rules": [{"field": "newsletter_subscribed_at", "operator": "greater_than", "value": "DATE_SUB(NOW(), INTERVAL 7 DAY)"}]}',
    'AND', TRUE, 'hourly'),

(1, 'Equipment Buyers', 'Customers who purchased equipment in last 12 months', 'behavioral',
    '{"rules": [{"field": "product_purchases", "operator": "greater_than", "value": 0}, {"field": "last_equipment_purchase", "operator": "greater_than", "value": "DATE_SUB(NOW(), INTERVAL 12 MONTH)"}]}',
    'AND', TRUE, 'daily'),

(1, 'Dive Trip Enthusiasts', 'Booked 2+ dive trips in the past', 'behavioral',
    '{"rules": [{"field": "dive_trips_booked", "operator": "greater_than", "value": 1}]}',
    'AND', TRUE, 'weekly'),

(1, 'Birthday This Month', 'Customers celebrating birthdays this month', 'dynamic',
    '{"rules": [{"field": "MONTH(date_of_birth)", "operator": "equals", "value": "MONTH(NOW())"}]}',
    'AND', TRUE, 'daily'),

(1, 'High Engagement', 'Email open rate > 50% and 5+ interactions', 'behavioral',
    '{"rules": [{"field": "avg_email_open_rate", "operator": "greater_than", "value": 50}, {"field": "total_interactions", "operator": "greater_than", "value": 5}]}',
    'AND', TRUE, 'weekly');

-- =====================================================
-- Pre-seeded Criteria Library
-- =====================================================

INSERT INTO "segment_criteria_library" (
    "tenant_id", "name", "category", "field", "operator", "value", "value_type", "sql_template"
) VALUES
(1, 'High Lifetime Value', 'transactional', 'lifetime_value', 'greater_than', '5000', 'number', 'lifetime_value > 5000'),
(1, 'Recent Purchase', 'behavioral', 'last_purchase_date', 'greater_than', 'DATE_SUB(NOW(), INTERVAL 30 DAY)', 'date', 'last_purchase_date > DATE_SUB(NOW(), INTERVAL 30 DAY)'),
(1, 'Email Subscriber', 'engagement', 'newsletter_subscribed', 'equals', '1', 'boolean', 'newsletter_subscribed = 1'),
(1, 'Inactive 6+ Months', 'behavioral', 'last_interaction_date', 'less_than', 'DATE_SUB(NOW(), INTERVAL 6 MONTH)', 'date', 'last_interaction_date < DATE_SUB(NOW(), INTERVAL 6 MONTH)'),
(1, 'Multiple Bookings', 'transactional', 'total_bookings', 'greater_than', '3', 'number', 'total_bookings > 3'),
(1, 'Lives in California', 'demographic', 'state', 'equals', 'CA', 'string', 'state = "CA"'),
(1, 'Age 25-40', 'demographic', 'age', 'between', '25,40', 'number', 'age BETWEEN 25 AND 40'),
(1, 'High Email Engagement', 'engagement', 'avg_email_open_rate', 'greater_than', '40', 'number', 'avg_email_open_rate > 40'),
(1, 'Certification Holder', 'demographic', 'certifications_count', 'greater_than', '0', 'number', 'certifications_count > 0'),
(1, 'Churn Risk - High', 'predictive', 'churn_risk_score', 'greater_than', '0.7', 'number', 'churn_risk_score > 0.7');
