-- =====================================================
-- Communication Integrations
-- Google Voice, WhatsApp Business, unified messaging
-- =====================================================

-- Communication Channels
CREATE TABLE IF NOT EXISTS "communication_channels" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "channel_name" VARCHAR(255) NOT NULL,
    "channel_type" ENUM('google_voice', 'whatsapp', 'sms', 'email', 'phone', 'webchat', 'facebook_messenger', 'instagram_dm') NOT NULL,

    -- Channel Details
    "phone_number" VARCHAR(20) NULL,
    "email_address" VARCHAR(255) NULL,
    "username" VARCHAR(255) NULL,

    -- API Configuration
    "api_provider" VARCHAR(100) NULL,
    "api_key" VARCHAR(255) NULL COMMENT 'Encrypted',
    "api_secret" VARCHAR(255) NULL COMMENT 'Encrypted',
    "access_token" TEXT NULL COMMENT 'Encrypted',
    "refresh_token" TEXT NULL COMMENT 'Encrypted',
    "token_expires_at" TIMESTAMP NULL,
    "webhook_url" VARCHAR(500) NULL,
    "webhook_secret" VARCHAR(255) NULL,

    -- Settings
    "is_primary" BOOLEAN DEFAULT FALSE,
    "auto_response_enabled" BOOLEAN DEFAULT FALSE,
    "auto_response_message" TEXT NULL,
    "business_hours_only" BOOLEAN DEFAULT FALSE,
    "business_hours" JSON NULL,

    -- Routing
    "route_to_department" VARCHAR(100) NULL,
    "route_to_users" JSON NULL COMMENT 'User IDs to notify',
    "round_robin_enabled" BOOLEAN DEFAULT FALSE,

    -- Status
    "status" ENUM('active', 'inactive', 'error', 'suspended') DEFAULT 'active',
    "last_sync_at" TIMESTAMP NULL,
    "last_message_at" TIMESTAMP NULL,
    "error_message" TEXT NULL,

    -- Usage Limits
    "daily_message_limit" INT NULL,
    "daily_messages_sent" INT DEFAULT 0,
    "monthly_message_limit" INT NULL,
    "monthly_messages_sent" INT DEFAULT 0,
    "limit_reset_date" DATE NULL,

    -- Stats
    "total_messages_sent" BIGINT DEFAULT 0,
    "total_messages_received" BIGINT DEFAULT 0,

    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_tenant_type ("tenant_id", "channel_type"),
    INDEX idx_phone ("phone_number")
);

-- Unified Conversations
CREATE TABLE IF NOT EXISTS "conversations" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "conversation_identifier" VARCHAR(255) NOT NULL COMMENT 'Phone, WhatsApp ID, etc.',

    -- Contact
    "customer_id" INTEGER NULL,
    "contact_name" VARCHAR(255) NULL,
    "contact_phone" VARCHAR(20) NULL,
    "contact_email" VARCHAR(255) NULL,
    "contact_type" ENUM('customer', 'lead', 'vendor', 'unknown') DEFAULT 'unknown',

    -- Channel
    "channel_id" INTEGER NULL,
    "channel_type" ENUM('google_voice', 'whatsapp', 'sms', 'email', 'phone', 'webchat', 'facebook_messenger', 'instagram_dm') NOT NULL,

    -- Conversation Details
    "subject" VARCHAR(500) NULL,
    "first_message_at" TIMESTAMP NOT NULL,
    "last_message_at" TIMESTAMP NULL,
    "last_message_preview" VARCHAR(200) NULL,

    -- Status
    "status" ENUM('open', 'pending', 'resolved', 'closed', 'spam') DEFAULT 'open',
    "priority" ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',

    -- Assignment
    "assigned_to" INTEGER NULL,
    "assigned_at" TIMESTAMP NULL,
    "assigned_by" INTEGER NULL,
    "department" VARCHAR(100) NULL,

    -- Metrics
    "message_count" INT DEFAULT 0,
    "unread_count" INT DEFAULT 0,
    "response_time_minutes" INT NULL COMMENT 'Avg response time',
    "resolution_time_minutes" INT NULL,

    -- Tags & Categories
    "tags" JSON NULL,
    "category" VARCHAR(100) NULL,
    "sentiment" ENUM('positive', 'neutral', 'negative') NULL,

    -- Follow-up
    "requires_follow_up" BOOLEAN DEFAULT FALSE,
    "follow_up_date" TIMESTAMP NULL,
    "follow_up_notes" TEXT NULL,

    -- Customer Satisfaction
    "satisfaction_rating" SMALLINT NULL COMMENT '1-5 stars',
    "satisfaction_feedback" TEXT NULL,

    -- Notes
    "internal_notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("channel_id") REFERENCES "communication_channels"("id") ON DELETE SET NULL,
    FOREIGN KEY ("assigned_to") REFERENCES "employees"("id") ON DELETE SET NULL,
    INDEX idx_status_priority ("status", "priority"),
    INDEX idx_assigned ("assigned_to"),
    INDEX idx_customer ("customer_id"),
    INDEX idx_conversation_id ("conversation_identifier")
);

-- Messages (unified across all channels)
CREATE TABLE IF NOT EXISTS "messages" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "conversation_id" BIGINT NOT NULL,
    "channel_id" INTEGER NULL,

    -- Message Details
    "message_type" ENUM('text', 'image', 'video', 'audio', 'file', 'location', 'contact', 'sticker', 'voice_note') DEFAULT 'text',
    "direction" ENUM('inbound', 'outbound') NOT NULL,
    "message_content" TEXT NULL,

    -- Sender/Receiver
    "from_number" VARCHAR(20) NULL,
    "to_number" VARCHAR(20) NULL,
    "from_name" VARCHAR(255) NULL,
    "sender_type" ENUM('customer', 'employee', 'system', 'bot') NOT NULL,
    "sent_by_user_id" INTEGER NULL,

    -- External IDs
    "external_message_id" VARCHAR(255) NULL COMMENT 'WhatsApp/Google Voice message ID',
    "external_conversation_id" VARCHAR(255) NULL,

    -- Media
    "media_url" VARCHAR(500) NULL,
    "media_type" VARCHAR(100) NULL COMMENT 'image/jpeg, video/mp4, etc.',
    "media_size_bytes" BIGINT NULL,
    "thumbnail_url" VARCHAR(500) NULL,

    -- Status
    "status" ENUM('queued', 'sent', 'delivered', 'read', 'failed', 'deleted') DEFAULT 'sent',
    "sent_at" TIMESTAMP NULL,
    "delivered_at" TIMESTAMP NULL,
    "read_at" TIMESTAMP NULL,
    "failed_at" TIMESTAMP NULL,
    "error_message" TEXT NULL,

    -- Read Receipts
    "read_by_customer" BOOLEAN DEFAULT FALSE,
    "read_by_agent" BOOLEAN DEFAULT FALSE,

    -- Rich Content (WhatsApp Business)
    "has_buttons" BOOLEAN DEFAULT FALSE,
    "buttons" JSON NULL,
    "has_quick_replies" BOOLEAN DEFAULT FALSE,
    "quick_replies" JSON NULL,
    "template_name" VARCHAR(255) NULL,
    "template_params" JSON NULL,

    -- AI/Bot
    "generated_by_ai" BOOLEAN DEFAULT FALSE,
    "ai_confidence" DECIMAL(5, 2) NULL,
    "bot_session_id" VARCHAR(100) NULL,

    -- Metadata
    "metadata" JSON NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("conversation_id") REFERENCES "conversations"("id") ON DELETE CASCADE,
    FOREIGN KEY ("channel_id") REFERENCES "communication_channels"("id") ON DELETE SET NULL,
    INDEX idx_conversation ("conversation_id"),
    INDEX idx_external_id ("external_message_id"),
    INDEX idx_created_at ("created_at")
);

-- WhatsApp Business Templates
CREATE TABLE IF NOT EXISTS "whatsapp_templates" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "template_name" VARCHAR(255) NOT NULL,
    "template_category" ENUM('marketing', 'transactional', 'authentication', 'service') NOT NULL,
    "language_code" VARCHAR(10) DEFAULT 'en',

    -- Template Content
    "header_type" ENUM('none', 'text', 'image', 'video', 'document') DEFAULT 'none',
    "header_content" TEXT NULL,
    "body_content" TEXT NOT NULL,
    "footer_content" TEXT NULL,

    -- Buttons
    "has_buttons" BOOLEAN DEFAULT FALSE,
    "buttons" JSON NULL COMMENT 'Call to action, quick reply, URL buttons',

    -- Variables
    "variables" JSON NULL COMMENT 'Placeholders in template',
    "variable_count" INT DEFAULT 0,

    -- Status
    "status" ENUM('draft', 'pending_approval', 'approved', 'rejected', 'disabled') DEFAULT 'draft',
    "whatsapp_template_id" VARCHAR(255) NULL,
    "approved_at" TIMESTAMP NULL,
    "rejected_reason" TEXT NULL,

    -- Usage
    "total_sent" BIGINT DEFAULT 0,
    "total_delivered" BIGINT DEFAULT 0,
    "total_read" BIGINT DEFAULT 0,
    "last_used_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_status ("status")
);

-- Message Templates (general, not just WhatsApp)
CREATE TABLE IF NOT EXISTS "message_templates" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "template_name" VARCHAR(255) NOT NULL,
    "channel_type" ENUM('sms', 'email', 'whatsapp', 'google_voice', 'any') DEFAULT 'any',

    -- Content
    "subject" VARCHAR(500) NULL COMMENT 'For email',
    "message_content" TEXT NOT NULL,
    "content_type" ENUM('text', 'html') DEFAULT 'text',

    -- Variables
    "available_variables" JSON NULL,

    -- Category
    "category" VARCHAR(100) NULL,
    "use_case" VARCHAR(255) NULL COMMENT 'Booking confirmation, reminder, etc.',

    -- Usage
    "times_used" INT DEFAULT 0,
    "last_used_at" TIMESTAMP NULL,

    -- Status
    "is_active" BOOLEAN DEFAULT TRUE,
    "created_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    INDEX idx_category ("category")
);

-- Automated Responses
CREATE TABLE IF NOT EXISTS "automated_responses" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "response_name" VARCHAR(255) NOT NULL,
    "channel_type" ENUM('sms', 'whatsapp', 'google_voice', 'email', 'webchat', 'any') DEFAULT 'any',

    -- Trigger
    "trigger_type" ENUM('keyword', 'time_based', 'no_agent_available', 'outside_hours', 'new_conversation') NOT NULL,
    "trigger_keywords" JSON NULL,
    "trigger_conditions" JSON NULL,

    -- Response
    "response_message" TEXT NOT NULL,
    "response_delay_seconds" INT DEFAULT 0,

    -- Actions
    "create_ticket" BOOLEAN DEFAULT FALSE,
    "assign_to" INTEGER NULL,
    "tag_conversation" VARCHAR(100) NULL,
    "escalate" BOOLEAN DEFAULT FALSE,

    -- Schedule
    "active_days" JSON NULL COMMENT 'Days of week',
    "active_hours_start" TIME NULL,
    "active_hours_end" TIME NULL,

    -- Priority
    "priority_order" INT DEFAULT 100,

    -- Stats
    "total_triggered" INT DEFAULT 0,
    "last_triggered_at" TIMESTAMP NULL,

    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("assign_to") REFERENCES "employees"("id") ON DELETE SET NULL
);

-- Call Logs (for Google Voice/phone calls)
CREATE TABLE IF NOT EXISTS "call_logs" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "channel_id" INTEGER NULL,
    "conversation_id" BIGINT NULL,

    -- Call Details
    "call_type" ENUM('inbound', 'outbound', 'internal') NOT NULL,
    "call_status" ENUM('completed', 'missed', 'voicemail', 'busy', 'failed', 'rejected') NOT NULL,
    "call_direction" ENUM('inbound', 'outbound') NOT NULL,

    -- Parties
    "from_number" VARCHAR(20) NOT NULL,
    "to_number" VARCHAR(20) NOT NULL,
    "caller_name" VARCHAR(255) NULL,
    "customer_id" INTEGER NULL,

    -- Handling
    "answered_by" INTEGER NULL COMMENT 'Employee who answered',
    "transferred_to" JSON NULL COMMENT 'If call was transferred',
    "forwarded_from" VARCHAR(20) NULL,

    -- Timing
    "started_at" TIMESTAMP NOT NULL,
    "answered_at" TIMESTAMP NULL,
    "ended_at" TIMESTAMP NULL,
    "duration_seconds" INT NULL,
    "talk_time_seconds" INT NULL,
    "hold_time_seconds" INT NULL,
    "queue_time_seconds" INT NULL,

    -- Recording
    "recording_available" BOOLEAN DEFAULT FALSE,
    "recording_url" VARCHAR(500) NULL,
    "recording_duration_seconds" INT NULL,
    "transcription" TEXT NULL,

    -- Voicemail
    "is_voicemail" BOOLEAN DEFAULT FALSE,
    "voicemail_url" VARCHAR(500) NULL,
    "voicemail_transcription" TEXT NULL,
    "voicemail_duration_seconds" INT NULL,

    -- Quality
    "call_quality" ENUM('excellent', 'good', 'fair', 'poor') NULL,
    "dropped" BOOLEAN DEFAULT FALSE,

    -- External IDs
    "external_call_id" VARCHAR(255) NULL,
    "provider_call_sid" VARCHAR(255) NULL,

    -- Cost
    "call_cost" DECIMAL(8, 4) NULL,

    -- Notes
    "notes" TEXT NULL,
    "disposition" VARCHAR(100) NULL COMMENT 'Outcome: booked, callback, no answer, etc.',

    -- Tags
    "tags" JSON NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("channel_id") REFERENCES "communication_channels"("id") ON DELETE SET NULL,
    FOREIGN KEY ("conversation_id") REFERENCES "conversations"("id") ON DELETE SET NULL,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("answered_by") REFERENCES "employees"("id") ON DELETE SET NULL,
    INDEX idx_call_time ("started_at"),
    INDEX idx_customer ("customer_id"),
    INDEX idx_from_number ("from_number")
);

-- Communication Analytics
CREATE TABLE IF NOT EXISTS "communication_analytics" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "analytics_date" DATE NOT NULL,
    "channel_id" INTEGER NULL,
    "channel_type" VARCHAR(50) NULL,

    -- Message Metrics
    "messages_sent" INT DEFAULT 0,
    "messages_received" INT DEFAULT 0,
    "messages_delivered" INT DEFAULT 0,
    "messages_failed" INT DEFAULT 0,
    "avg_response_time_minutes" DECIMAL(8, 2) NULL,

    -- Call Metrics
    "calls_inbound" INT DEFAULT 0,
    "calls_outbound" INT DEFAULT 0,
    "calls_answered" INT DEFAULT 0,
    "calls_missed" INT DEFAULT 0,
    "avg_call_duration_seconds" INT NULL,
    "total_talk_time_minutes" INT DEFAULT 0,

    -- Conversation Metrics
    "conversations_started" INT DEFAULT 0,
    "conversations_resolved" INT DEFAULT 0,
    "avg_resolution_time_minutes" DECIMAL(8, 2) NULL,

    -- Satisfaction
    "satisfaction_responses" INT DEFAULT 0,
    "avg_satisfaction_score" DECIMAL(3, 2) NULL,

    -- Costs
    "total_cost" DECIMAL(10, 2) DEFAULT 0.00,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("channel_id") REFERENCES "communication_channels"("id") ON DELETE CASCADE,
    UNIQUE KEY unique_date_channel ("tenant_id", "analytics_date", "channel_id"),
    INDEX idx_analytics_date ("analytics_date")
);

-- =====================================================
-- Pre-seeded Data
-- =====================================================

-- Sample Communication Channels
INSERT INTO "communication_channels" (
    "tenant_id", "channel_name", "channel_type", "phone_number", "is_primary", "status"
) VALUES
(1, 'Main Business Line - Google Voice', 'google_voice', '+1-555-DIVE-123', TRUE, 'active'),
(1, 'WhatsApp Business', 'whatsapp', '+1-555-DIVE-123', FALSE, 'active'),
(1, 'SMS Marketing Line', 'sms', '+1-555-DIVE-456', FALSE, 'active');

-- Sample Message Templates
INSERT INTO "message_templates" (
    "tenant_id", "template_name", "channel_type", "message_content", "category", "use_case"
) VALUES
(1, 'Booking Confirmation', 'any',
    'Hi {{first_name}}! Your {{service}} is confirmed for {{date}} at {{time}}. We look forward to seeing you!',
    'booking', 'Booking confirmation'),

(1, 'Appointment Reminder', 'any',
    'Reminder: You have a {{service}} scheduled for tomorrow at {{time}}. Reply YES to confirm or CALL to reschedule.',
    'reminder', 'Appointment reminder'),

(1, 'Thank You Message', 'any',
    'Thank you for choosing {{shop_name}}, {{first_name}}! We hope you had an amazing experience. Please share your feedback: {{review_link}}',
    'follow_up', 'Post-service thank you');

-- Sample WhatsApp Templates
INSERT INTO "whatsapp_templates" (
    "tenant_id", "template_name", "template_category", "body_content", "status"
) VALUES
(1, 'booking_confirmation', 'transactional',
    'Hi {{1}}! Your {{2}} is confirmed for {{3}} at {{4}}. See you soon!',
    'approved'),

(1, 'dive_trip_reminder', 'transactional',
    'Your dive trip to {{1}} departs in {{2}} days! Make sure you have all required documents. Check-in time: {{3}}.',
    'approved');

-- Sample Automated Responses
INSERT INTO "automated_responses" (
    "tenant_id", "response_name", "channel_type", "trigger_type", "trigger_keywords",
    "response_message", "is_active"
) VALUES
(1, 'After Hours Auto-Reply', 'any', 'outside_hours', NULL,
    'Thanks for contacting us! We''re currently closed. Our hours are Mon-Sat 9am-6pm. We''ll respond when we open. For emergencies, call 555-911-DIVE.',
    TRUE),

(1, 'Hours Inquiry', 'any', 'keyword', '["hours", "open", "closed", "schedule"]',
    'We''re open Monday-Saturday 9am-6pm, Sunday 10am-4pm. Visit us at 123 Ocean Ave or call 555-DIVE-123!',
    TRUE),

(1, 'Pricing Inquiry', 'any', 'keyword', '["price", "cost", "how much", "pricing"]',
    'Our Open Water course is $399, Advanced is $299. Equipment rental packages start at $75/day. Would you like details on a specific course or service?',
    TRUE);
