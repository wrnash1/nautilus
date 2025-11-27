-- ============================================================================
-- Migration: Create Enhanced Communication System
-- Created: 2024
-- Description: SMS, Push notifications, campaign tracking, and communication logs
-- Note: customer_communications table already exists from 002_create_customer_tables.sql
-- ============================================================================

-- Communication Campaigns (created first so communication_log can reference it)
CREATE TABLE IF NOT EXISTS communication_campaigns (
    id INTEGER  PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    campaign_type VARCHAR(20) NOT NULL,  -- 'sms', 'push', 'email', 'multi'
    status VARCHAR(20) NOT NULL DEFAULT 'draft',  -- 'draft', 'scheduled', 'sending', 'completed', 'cancelled'
    target_audience VARCHAR(20) NOT NULL,  -- 'all', 'segment', 'individual', 'tier'
    target_segment TEXT,  -- JSON criteria for segmentation
    message_subject VARCHAR(200),
    message_body TEXT NOT NULL,
    scheduled_at TIMESTAMP,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    total_recipients INT DEFAULT 0,
    total_sent INT DEFAULT 0,
    total_delivered INT DEFAULT 0,
    total_failed INT DEFAULT 0,
    total_opened INT DEFAULT 0,
    total_clicked INT DEFAULT 0,
    total_cost DECIMAL(10,2) DEFAULT 0.00,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_communication_campaigns_status ON communication_campaigns(status);
CREATE INDEX IF NOT EXISTS idx_communication_campaigns_type ON communication_campaigns(campaign_type);
CREATE INDEX IF NOT EXISTS idx_communication_campaigns_scheduled ON communication_campaigns(scheduled_at);

-- Communication Message Log (created after campaigns so it can reference campaign_id)
CREATE TABLE IF NOT EXISTS communication_log (
    id INTEGER  PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    message_type VARCHAR(20) NOT NULL,  -- 'sms', 'push', 'email'
    campaign_id INT UNSIGNED,  -- If part of a campaign
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- 'pending', 'sent', 'delivered', 'failed', 'bounced'
    provider VARCHAR(50),  -- 'twilio', 'firebase', 'sendgrid', etc.
    provider_message_id VARCHAR(200),  -- External provider's message ID
    sent_at TIMESTAMP,
    delivered_at TIMESTAMP,
    opened_at TIMESTAMP,  -- For emails and push
    clicked_at TIMESTAMP,  -- For tracking links
    failed_reason TEXT,
    cost DECIMAL(10,4),  -- Cost per message
    metadata TEXT,  -- JSON for additional data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_communication_log_customer ON communication_log(customer_id);
CREATE INDEX IF NOT EXISTS idx_communication_log_type ON communication_log(message_type);
CREATE INDEX IF NOT EXISTS idx_communication_log_status ON communication_log(status);
CREATE INDEX IF NOT EXISTS idx_communication_log_campaign ON communication_log(campaign_id);
CREATE INDEX IF NOT EXISTS idx_communication_log_sent_at ON communication_log(sent_at);

-- Customer Device Tokens (for Push Notifications)
CREATE TABLE IF NOT EXISTS customer_devices (
    id INTEGER  PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    device_type VARCHAR(20) NOT NULL,  -- 'ios', 'android', 'web'
    device_token TEXT NOT NULL,  -- FCM/APNs token
    device_name VARCHAR(100),  -- User-friendly device name
    app_version VARCHAR(20),
    os_version VARCHAR(20),
    is_active SMALLINT DEFAULT 1,
    last_used_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_customer_devices_customer ON customer_devices(customer_id);
CREATE INDEX IF NOT EXISTS idx_customer_devices_active ON customer_devices(is_active);
CREATE INDEX IF NOT EXISTS idx_customer_devices_token ON customer_devices(device_token);

-- Communication Templates
CREATE TABLE IF NOT EXISTS communication_templates (
    id INTEGER  PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    template_type VARCHAR(20) NOT NULL,  -- 'sms', 'push', 'email'
    category VARCHAR(50),  -- 'transactional', 'marketing', 'notification'
    subject VARCHAR(200),
    body TEXT NOT NULL,
    variables TEXT,  -- JSON array of available variables
    is_active SMALLINT DEFAULT 1,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_communication_templates_type ON communication_templates(template_type);
CREATE INDEX IF NOT EXISTS idx_communication_templates_active ON communication_templates(is_active);

-- Customer Communication Preferences (enhanced)
CREATE TABLE IF NOT EXISTS customer_communication_preferences (
    id INTEGER  PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    email_enabled SMALLINT DEFAULT 1,
    sms_enabled SMALLINT DEFAULT 1,
    push_enabled SMALLINT DEFAULT 1,
    marketing_email SMALLINT DEFAULT 1,
    marketing_sms SMALLINT DEFAULT 0,  -- Opt-in required for SMS marketing
    marketing_push SMALLINT DEFAULT 1,
    transactional_email SMALLINT DEFAULT 1,  -- Order confirmations, receipts
    transactional_sms SMALLINT DEFAULT 1,  -- Booking reminders, alerts
    reminder_notifications SMALLINT DEFAULT 1,  -- Appointment/trip reminders
    promotional_notifications SMALLINT DEFAULT 1,
    newsletter SMALLINT DEFAULT 1,
    preferred_contact_method VARCHAR(20) DEFAULT 'email',  -- 'email', 'sms', 'push'
    quiet_hours_start TIME,  -- Don't send between these hours
    quiet_hours_end TIME,
    timezone VARCHAR(50) DEFAULT 'America/New_York',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(customer_id)
);

CREATE INDEX IF NOT EXISTS idx_customer_comm_prefs_customer ON customer_communication_preferences(customer_id);

-- Insert default templates
INSERT IGNORE INTO communication_templates (id, name, template_type, category, subject, body, variables) VALUES
(1, 'Welcome SMS', 'sms', 'transactional', NULL, 'Welcome to {{shop_name}}! Your account is ready. Reply STOP to opt-out.', '["shop_name", "customer_name"]'),
(2, 'Order Confirmation', 'sms', 'transactional', NULL, 'Thanks {{customer_name}}! Order #{{order_number}} confirmed. Total: ${{order_total}}', '["customer_name", "order_number", "order_total"]'),
(3, 'Appointment Reminder', 'sms', 'transactional', NULL, 'Reminder: Your {{appointment_type}} is tomorrow at {{appointment_time}}. See you soon!', '["appointment_type", "appointment_time", "shop_name"]'),
(4, 'Course Starting Soon', 'sms', 'transactional', NULL, 'Your {{course_name}} starts on {{start_date}}. Location: {{location}}. Questions? Call us!', '["course_name", "start_date", "location"]'),
(5, 'Trip Reminder', 'push', 'transactional', 'Trip Reminder', 'Your {{trip_name}} departs in {{days_until}} days! Are you ready?', '["trip_name", "days_until", "departure_date"]');

-- ============================================================================
-- Migration Complete
-- ============================================================================
