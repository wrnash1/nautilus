-- =====================================================
-- Online Booking Portal & Mobile App APIs
-- Self-service booking, mobile app support, API tokens
-- =====================================================

-- Online Booking Settings
CREATE TABLE IF NOT EXISTS "online_booking_settings" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,

    -- General Settings
    "booking_enabled" BOOLEAN DEFAULT TRUE,
    "require_account" BOOLEAN DEFAULT FALSE,
    "allow_guest_booking" BOOLEAN DEFAULT TRUE,
    "require_deposit" BOOLEAN DEFAULT TRUE,
    "deposit_percentage" DECIMAL(5, 2) DEFAULT 25.00,

    -- Availability
    "booking_window_days" INT DEFAULT 90 COMMENT 'How far in advance bookings allowed',
    "min_advance_hours" INT DEFAULT 24 COMMENT 'Min hours before booking',
    "max_party_size" INT DEFAULT 8,

    -- Cancellation Policy
    "cancellation_allowed" BOOLEAN DEFAULT TRUE,
    "cancellation_hours_notice" INT DEFAULT 48,
    "cancellation_fee_percentage" DECIMAL(5, 2) DEFAULT 0.00,
    "refund_processing_days" INT DEFAULT 7,

    -- Modifications
    "modifications_allowed" BOOLEAN DEFAULT TRUE,
    "modification_hours_notice" INT DEFAULT 24,
    "modification_fee" DECIMAL(10, 2) DEFAULT 0.00,

    -- Communication
    "send_confirmation_email" BOOLEAN DEFAULT TRUE,
    "send_reminder_email" BOOLEAN DEFAULT TRUE,
    "reminder_hours_before" INT DEFAULT 24,
    "send_sms_confirmation" BOOLEAN DEFAULT FALSE,

    -- Website Integration
    "widget_embed_code" TEXT NULL,
    "booking_page_url" VARCHAR(500) NULL,
    "custom_domain" VARCHAR(255) NULL,

    -- Branding
    "primary_color" VARCHAR(7) DEFAULT '#0066CC',
    "logo_url" VARCHAR(500) NULL,
    "custom_css" TEXT NULL,

    -- Terms & Conditions
    "terms_required" BOOLEAN DEFAULT TRUE,
    "terms_text" TEXT NULL,
    "terms_url" VARCHAR(500) NULL,
    "waiver_required" BOOLEAN DEFAULT TRUE,
    "waiver_text" TEXT NULL,

    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE
);

-- Bookable Services/Items
CREATE TABLE IF NOT EXISTS "bookable_items" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,

    -- Item Details
    "item_type" ENUM('course', 'trip', 'rental', 'experience', 'private_lesson', 'boat_charter', 'service') NOT NULL,
    "item_name" VARCHAR(255) NOT NULL,
    "item_code" VARCHAR(50) NULL,

    -- Description
    "short_description" VARCHAR(500) NULL,
    "full_description" TEXT NULL,
    "highlights" JSON NULL,
    "requirements" JSON NULL COMMENT 'Prerequisites, age limits, etc.',

    -- Scheduling
    "duration_minutes" INT NULL,
    "schedule_type" ENUM('fixed', 'flexible', 'recurring') DEFAULT 'fixed',
    "recurring_schedule" JSON NULL COMMENT 'Days/times when available',
    "buffer_time_minutes" INT DEFAULT 0 COMMENT 'Time between bookings',

    -- Capacity
    "max_participants" INT DEFAULT 1,
    "min_participants" INT DEFAULT 1,
    "allow_overbooking" BOOLEAN DEFAULT FALSE,

    -- Pricing
    "base_price" DECIMAL(10, 2) NOT NULL,
    "price_per_person" BOOLEAN DEFAULT TRUE,
    "group_pricing" JSON NULL COMMENT 'Discounts for groups',
    "seasonal_pricing" JSON NULL,
    "dynamic_pricing_enabled" BOOLEAN DEFAULT FALSE,

    -- Add-ons
    "available_addons" JSON NULL COMMENT 'Extra equipment, insurance, etc.',

    -- Resources
    "requires_instructor" BOOLEAN DEFAULT FALSE,
    "requires_boat" BOOLEAN DEFAULT FALSE,
    "requires_equipment" BOOLEAN DEFAULT FALSE,
    "equipment_included" BOOLEAN DEFAULT FALSE,

    -- Online Booking
    "available_online" BOOLEAN DEFAULT TRUE,
    "requires_approval" BOOLEAN DEFAULT FALSE,
    "instant_confirmation" BOOLEAN DEFAULT TRUE,

    -- Media
    "featured_image_url" VARCHAR(500) NULL,
    "gallery_images" JSON NULL,
    "video_url" VARCHAR(500) NULL,

    -- SEO
    "url_slug" VARCHAR(255) NULL,
    "meta_title" VARCHAR(255) NULL,
    "meta_description" TEXT NULL,

    -- Stats
    "total_bookings" INT DEFAULT 0,
    "average_rating" DECIMAL(3, 2) DEFAULT 0.00,
    "review_count" INT DEFAULT 0,

    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    UNIQUE KEY unique_slug ("tenant_id", "url_slug"),
    INDEX idx_item_type ("item_type"),
    INDEX idx_available_online ("available_online")
);

-- Availability Slots
CREATE TABLE IF NOT EXISTS "availability_slots" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "bookable_item_id" INTEGER NOT NULL,

    -- Slot Details
    "slot_date" DATE NOT NULL,
    "slot_time" TIME NOT NULL,
    "end_time" TIME NULL,

    -- Capacity
    "total_capacity" INT NOT NULL,
    "booked_count" INT DEFAULT 0,
    "available_count" INT NOT NULL,

    -- Resources
    "instructor_id" INTEGER NULL,
    "boat_id" INTEGER NULL,
    "location_id" INTEGER NULL,

    -- Pricing
    "slot_price" DECIMAL(10, 2) NULL COMMENT 'Override base price',
    "price_multiplier" DECIMAL(5, 2) DEFAULT 1.00,

    -- Status
    "status" ENUM('available', 'full', 'cancelled', 'blocked') DEFAULT 'available',
    "blocked_reason" TEXT NULL,

    -- Metadata
    "notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("bookable_item_id") REFERENCES "bookable_items"("id") ON DELETE CASCADE,
    FOREIGN KEY ("instructor_id") REFERENCES "employees"("id") ON DELETE SET NULL,
    UNIQUE KEY unique_item_datetime ("bookable_item_id", "slot_date", "slot_time"),
    INDEX idx_date_status ("slot_date", "status")
);

-- Online Bookings
CREATE TABLE IF NOT EXISTS "online_bookings" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "booking_reference" VARCHAR(50) NOT NULL UNIQUE,

    -- Customer
    "customer_id" INTEGER NULL,
    "guest_name" VARCHAR(255) NULL,
    "guest_email" VARCHAR(255) NULL,
    "guest_phone" VARCHAR(20) NULL,
    "is_guest_booking" BOOLEAN DEFAULT FALSE,

    -- Booking Details
    "bookable_item_id" INTEGER NOT NULL,
    "slot_id" BIGINT NULL,
    "booking_date" DATE NOT NULL,
    "booking_time" TIME NULL,
    "number_of_participants" INT NOT NULL DEFAULT 1,

    -- Participants
    "participants" JSON NULL COMMENT 'Names, ages, certifications',
    "special_requests" TEXT NULL,

    -- Pricing
    "base_price" DECIMAL(10, 2) NOT NULL,
    "addons_price" DECIMAL(10, 2) DEFAULT 0.00,
    "tax_amount" DECIMAL(10, 2) DEFAULT 0.00,
    "total_price" DECIMAL(10, 2) NOT NULL,

    -- Payment
    "deposit_amount" DECIMAL(10, 2) NULL,
    "deposit_paid" BOOLEAN DEFAULT FALSE,
    "deposit_paid_at" TIMESTAMP NULL,
    "balance_due" DECIMAL(10, 2) NULL,
    "payment_status" ENUM('pending', 'deposit_paid', 'paid_in_full', 'refunded', 'cancelled') DEFAULT 'pending',

    -- Status
    "booking_status" ENUM('pending', 'confirmed', 'cancelled', 'completed', 'no_show') DEFAULT 'pending',
    "requires_approval" BOOLEAN DEFAULT FALSE,
    "approved" BOOLEAN DEFAULT FALSE,
    "approved_by" INTEGER NULL,
    "approved_at" TIMESTAMP NULL,

    -- Confirmation
    "confirmed_at" TIMESTAMP NULL,
    "confirmation_sent_at" TIMESTAMP NULL,

    -- Cancellation
    "cancelled_at" TIMESTAMP NULL,
    "cancelled_by" VARCHAR(100) NULL COMMENT 'Customer or staff name',
    "cancellation_reason" TEXT NULL,
    "cancellation_fee" DECIMAL(10, 2) DEFAULT 0.00,

    -- Waiver
    "waiver_signed" BOOLEAN DEFAULT FALSE,
    "waiver_signed_at" TIMESTAMP NULL,
    "waiver_ip_address" VARCHAR(45) NULL,
    "waiver_signature_data" TEXT NULL,

    -- Communication
    "reminder_sent" BOOLEAN DEFAULT FALSE,
    "reminder_sent_at" TIMESTAMP NULL,
    "follow_up_sent" BOOLEAN DEFAULT FALSE,

    -- Source
    "booking_source" ENUM('website', 'mobile_app', 'phone', 'walk_in', 'email', 'social_media') DEFAULT 'website',
    "utm_source" VARCHAR(100) NULL,
    "utm_medium" VARCHAR(100) NULL,
    "utm_campaign" VARCHAR(100) NULL,

    -- Internal
    "internal_notes" TEXT NULL,
    "assigned_to" INTEGER NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("bookable_item_id") REFERENCES "bookable_items"("id") ON DELETE RESTRICT,
    FOREIGN KEY ("slot_id") REFERENCES "availability_slots"("id") ON DELETE SET NULL,
    INDEX idx_booking_date ("booking_date"),
    INDEX idx_status ("booking_status", "payment_status"),
    INDEX idx_customer ("customer_id")
);

-- API Tokens (for mobile apps and integrations)
CREATE TABLE IF NOT EXISTS "api_tokens" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "token_name" VARCHAR(255) NOT NULL,
    "token_key" VARCHAR(64) NOT NULL UNIQUE COMMENT 'The actual API key',

    -- Token Type
    "token_type" ENUM('mobile_app', 'web_integration', 'third_party', 'webhook', 'internal') NOT NULL,
    "platform" ENUM('ios', 'android', 'web', 'server', 'other') NULL,

    -- Ownership
    "user_id" INTEGER NULL,
    "customer_id" INTEGER NULL,

    -- Permissions
    "scopes" JSON NOT NULL COMMENT 'Allowed endpoints/actions',
    "permissions_level" ENUM('read_only', 'read_write', 'full_access') DEFAULT 'read_only',

    -- Rate Limiting
    "rate_limit_per_minute" INT DEFAULT 60,
    "rate_limit_per_hour" INT DEFAULT 1000,
    "rate_limit_per_day" INT DEFAULT 10000,

    -- IP Restrictions
    "allowed_ips" JSON NULL COMMENT 'Whitelist of IPs',
    "blocked_ips" JSON NULL,

    -- Expiration
    "expires_at" TIMESTAMP NULL,
    "last_used_at" TIMESTAMP NULL,
    "last_used_ip" VARCHAR(45) NULL,

    -- Usage Stats
    "total_requests" BIGINT DEFAULT 0,
    "failed_requests" INT DEFAULT 0,
    "requests_today" INT DEFAULT 0,
    "usage_reset_date" DATE NULL,

    -- Status
    "is_active" BOOLEAN DEFAULT TRUE,
    "revoked" BOOLEAN DEFAULT FALSE,
    "revoked_at" TIMESTAMP NULL,
    "revoked_reason" TEXT NULL,

    -- Metadata
    "notes" TEXT NULL,

    "created_by" INTEGER NULL,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    INDEX idx_token_key ("token_key"),
    INDEX idx_active ("is_active", "revoked")
);

-- API Request Logs
CREATE TABLE IF NOT EXISTS "api_request_logs" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "api_token_id" INTEGER NULL,

    -- Request Details
    "request_method" VARCHAR(10) NOT NULL COMMENT 'GET, POST, PUT, DELETE',
    "request_endpoint" VARCHAR(500) NOT NULL,
    "request_params" JSON NULL,
    "request_body" TEXT NULL,

    -- Response
    "response_status" INT NOT NULL,
    "response_time_ms" INT NULL,
    "response_size_bytes" INT NULL,

    -- Client Info
    "ip_address" VARCHAR(45) NULL,
    "user_agent" TEXT NULL,
    "referer" VARCHAR(500) NULL,

    -- Authentication
    "authenticated" BOOLEAN DEFAULT FALSE,
    "user_id" INTEGER NULL,
    "customer_id" INTEGER NULL,

    -- Error Handling
    "is_error" BOOLEAN DEFAULT FALSE,
    "error_message" TEXT NULL,
    "error_code" VARCHAR(50) NULL,

    -- Timestamp
    "requested_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("api_token_id") REFERENCES "api_tokens"("id") ON DELETE SET NULL,
    INDEX idx_requested_at ("requested_at"),
    INDEX idx_endpoint ("request_endpoint"(255)),
    INDEX idx_token ("api_token_id")
);

-- Mobile App Sessions
CREATE TABLE IF NOT EXISTS "mobile_app_sessions" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,

    -- Session Details
    "session_token" VARCHAR(255) NOT NULL UNIQUE,
    "customer_id" INTEGER NOT NULL,

    -- Device Info
    "device_type" ENUM('ios', 'android', 'tablet', 'other') NOT NULL,
    "device_id" VARCHAR(255) NULL,
    "device_name" VARCHAR(255) NULL,
    "os_version" VARCHAR(50) NULL,
    "app_version" VARCHAR(50) NULL,

    -- Push Notifications
    "push_token" VARCHAR(500) NULL,
    "push_enabled" BOOLEAN DEFAULT TRUE,

    -- Location
    "last_latitude" DECIMAL(10, 8) NULL,
    "last_longitude" DECIMAL(11, 8) NULL,
    "location_permission" BOOLEAN DEFAULT FALSE,

    -- Session Tracking
    "started_at" TIMESTAMP NOT NULL,
    "last_activity_at" TIMESTAMP NULL,
    "expires_at" TIMESTAMP NULL,

    -- Network
    "ip_address" VARCHAR(45) NULL,
    "user_agent" TEXT NULL,

    -- Status
    "is_active" BOOLEAN DEFAULT TRUE,
    "logged_out_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    INDEX idx_session_token ("session_token"),
    INDEX idx_customer ("customer_id"),
    INDEX idx_active ("is_active")
);

-- Push Notifications
CREATE TABLE IF NOT EXISTS "push_notifications" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,

    -- Recipient
    "customer_id" INTEGER NULL,
    "session_id" BIGINT NULL,
    "device_token" VARCHAR(500) NULL,

    -- Notification Content
    "title" VARCHAR(255) NOT NULL,
    "body" TEXT NOT NULL,
    "image_url" VARCHAR(500) NULL,
    "action_url" VARCHAR(500) NULL COMMENT 'Deep link',

    -- Type
    "notification_type" ENUM('booking_confirmation', 'reminder', 'promotion', 'alert', 'message', 'general') NOT NULL,
    "category" VARCHAR(100) NULL,

    -- Payload
    "custom_data" JSON NULL,

    -- Scheduling
    "scheduled_for" TIMESTAMP NULL,
    "sent_at" TIMESTAMP NULL,

    -- Status
    "status" ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    "delivery_status" VARCHAR(100) NULL,
    "error_message" TEXT NULL,

    -- Engagement
    "opened" BOOLEAN DEFAULT FALSE,
    "opened_at" TIMESTAMP NULL,
    "clicked" BOOLEAN DEFAULT FALSE,
    "clicked_at" TIMESTAMP NULL,

    -- External IDs
    "provider" VARCHAR(50) NULL COMMENT 'FCM, APNS, etc.',
    "provider_message_id" VARCHAR(255) NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("session_id") REFERENCES "mobile_app_sessions"("id") ON DELETE CASCADE,
    INDEX idx_customer ("customer_id"),
    INDEX idx_status ("status"),
    INDEX idx_scheduled ("scheduled_for")
);

-- =====================================================
-- Sample Data
-- =====================================================

-- Sample Online Booking Settings
INSERT INTO "online_booking_settings" (
    "tenant_id", "booking_enabled", "require_deposit", "deposit_percentage",
    "send_confirmation_email", "send_reminder_email", "terms_required"
) VALUES
(1, TRUE, TRUE, 25.00, TRUE, TRUE, TRUE);

-- Sample Bookable Items
INSERT INTO "bookable_items" (
    "tenant_id", "item_type", "item_name", "duration_minutes", "max_participants",
    "base_price", "available_online", "instant_confirmation"
) VALUES
(1, 'course', 'Discover Scuba Diving', 180, 4, 149.00, TRUE, FALSE),
(1, 'experience', 'Two-Tank Boat Dive', 240, 12, 125.00, TRUE, TRUE),
(1, 'rental', 'Full Equipment Rental', 1440, 1, 75.00, TRUE, TRUE),
(1, 'trip', 'Weekend Catalina Island Dive Trip', 2880, 20, 299.00, TRUE, FALSE);

-- Sample API Token for Mobile App
INSERT INTO "api_tokens" (
    "tenant_id", "token_name", "token_key", "token_type", "platform",
    "scopes", "permissions_level", "is_active"
) VALUES
(1, 'iOS Mobile App', SHA2(CONCAT('mobile_app_', UNIX_TIMESTAMP(), RAND()), 256), 'mobile_app', 'ios',
    '["bookings.read", "bookings.create", "profile.read", "profile.update", "courses.read", "equipment.read"]',
    'read_write', TRUE),

(1, 'Android Mobile App', SHA2(CONCAT('mobile_app_android_', UNIX_TIMESTAMP(), RAND()), 256), 'mobile_app', 'android',
    '["bookings.read", "bookings.create", "profile.read", "profile.update", "courses.read", "equipment.read"]',
    'read_write', TRUE);
