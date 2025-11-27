-- =====================================================
-- Advanced Business Features
-- POS, Loyalty Programs, Gift Cards, Memberships, Online Booking
-- =====================================================

-- Point of Sale Terminals
CREATE TABLE IF NOT EXISTS "pos_terminals" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "terminal_name" VARCHAR(255) NOT NULL,
    "terminal_number" VARCHAR(50) NOT NULL UNIQUE,

    -- Location
    "location_id" INTEGER NULL,
    "register_number" INT NULL,

    -- Hardware
    "device_type" ENUM('ipad', 'android_tablet', 'desktop', 'mobile', 'kiosk') DEFAULT 'ipad',
    "device_id" VARCHAR(255) NULL,
    "ip_address" VARCHAR(45) NULL,
    "mac_address" VARCHAR(17) NULL,

    -- Payment Processing
    "payment_processor" VARCHAR(100) NULL COMMENT 'Square, Stripe, Clover, etc.',
    "processor_terminal_id" VARCHAR(255) NULL,
    "accepts_cash" BOOLEAN DEFAULT TRUE,
    "accepts_credit_card" BOOLEAN DEFAULT TRUE,
    "accepts_debit_card" BOOLEAN DEFAULT TRUE,
    "accepts_mobile_payment" BOOLEAN DEFAULT TRUE,
    "accepts_check" BOOLEAN DEFAULT FALSE,

    -- Cash Drawer
    "has_cash_drawer" BOOLEAN DEFAULT TRUE,
    "opening_cash_amount" DECIMAL(10, 2) DEFAULT 200.00,
    "current_cash_amount" DECIMAL(10, 2) NULL,

    -- Peripherals
    "has_barcode_scanner" BOOLEAN DEFAULT TRUE,
    "has_receipt_printer" BOOLEAN DEFAULT TRUE,
    "has_customer_display" BOOLEAN DEFAULT FALSE,
    "has_signature_pad" BOOLEAN DEFAULT FALSE,

    -- Status
    "status" ENUM('active', 'offline', 'maintenance', 'closed') DEFAULT 'active',
    "currently_open" BOOLEAN DEFAULT FALSE,
    "opened_at" TIMESTAMP NULL,
    "opened_by" INTEGER NULL,

    -- Stats
    "total_transactions_today" INT DEFAULT 0,
    "total_sales_today" DECIMAL(12, 2) DEFAULT 0.00,

    "is_active" BOOLEAN DEFAULT TRUE,
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("location_id") REFERENCES "inventory_locations"("id") ON DELETE SET NULL,
    INDEX idx_status ("status")
);

-- POS Transactions (separate from orders for detailed tracking)
CREATE TABLE IF NOT EXISTS "pos_transactions" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "transaction_number" VARCHAR(50) NOT NULL UNIQUE,
    "terminal_id" INTEGER NOT NULL,

    -- Transaction Details
    "transaction_type" ENUM('sale', 'return', 'exchange', 'void', 'refund') DEFAULT 'sale',
    "transaction_date" TIMESTAMP NOT NULL,

    -- Customer
    "customer_id" INTEGER NULL,
    "customer_name" VARCHAR(255) NULL,
    "customer_email" VARCHAR(255) NULL,
    "customer_phone" VARCHAR(20) NULL,

    -- Amounts
    "subtotal" DECIMAL(10, 2) NOT NULL,
    "discount_amount" DECIMAL(10, 2) DEFAULT 0.00,
    "tax_amount" DECIMAL(10, 2) DEFAULT 0.00,
    "tip_amount" DECIMAL(10, 2) DEFAULT 0.00,
    "total_amount" DECIMAL(10, 2) NOT NULL,

    -- Payment
    "payment_method" ENUM('cash', 'credit_card', 'debit_card', 'gift_card', 'store_credit', 'mobile_payment', 'check', 'split') NOT NULL,
    "payment_details" JSON NULL COMMENT 'Card type, last 4, etc.',
    "change_given" DECIMAL(10, 2) DEFAULT 0.00,

    -- Staff
    "cashier_id" INTEGER NOT NULL,
    "cashier_name" VARCHAR(255) NULL,

    -- Items
    "item_count" INT DEFAULT 0,
    "items" JSON NULL COMMENT 'Line items for quick reference',

    -- Discounts Applied
    "discount_codes" JSON NULL,
    "loyalty_points_earned" INT DEFAULT 0,
    "loyalty_points_redeemed" INT DEFAULT 0,

    -- Receipt
    "receipt_number" VARCHAR(50) NULL,
    "receipt_printed" BOOLEAN DEFAULT FALSE,
    "receipt_emailed" BOOLEAN DEFAULT FALSE,
    "receipt_url" VARCHAR(500) NULL,

    -- Related Records
    "order_id" INTEGER NULL,
    "original_transaction_id" BIGINT NULL COMMENT 'For returns/exchanges',

    -- Status
    "status" ENUM('completed', 'voided', 'refunded', 'pending') DEFAULT 'completed',
    "voided_at" TIMESTAMP NULL,
    "voided_by" INTEGER NULL,
    "void_reason" TEXT NULL,

    -- Notes
    "notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("terminal_id") REFERENCES "pos_terminals"("id") ON DELETE RESTRICT,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("cashier_id") REFERENCES "employees"("id") ON DELETE RESTRICT,
    INDEX idx_transaction_date ("transaction_date"),
    INDEX idx_customer ("customer_id"),
    INDEX idx_cashier ("cashier_id")
);

-- Cash Drawer Operations
CREATE TABLE IF NOT EXISTS "cash_drawer_operations" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "terminal_id" INTEGER NOT NULL,

    -- Operation Details
    "operation_type" ENUM('open', 'close', 'deposit', 'withdrawal', 'payout', 'reconciliation') NOT NULL,
    "operation_time" TIMESTAMP NOT NULL,
    "performed_by" INTEGER NOT NULL,

    -- Amounts
    "amount" DECIMAL(10, 2) NOT NULL,
    "expected_amount" DECIMAL(10, 2) NULL,
    "variance" DECIMAL(10, 2) NULL COMMENT 'For closing/reconciliation',

    -- Cash Breakdown
    "cash_breakdown" JSON NULL COMMENT 'Bills and coins count',

    -- Reason
    "reason" TEXT NULL,
    "notes" TEXT NULL,

    -- Verification
    "verified_by" INTEGER NULL,
    "verified_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("terminal_id") REFERENCES "pos_terminals"("id") ON DELETE CASCADE,
    FOREIGN KEY ("performed_by") REFERENCES "employees"("id") ON DELETE CASCADE,
    INDEX idx_terminal_time ("terminal_id", "operation_time")
);

-- Loyalty Programs
CREATE TABLE IF NOT EXISTS "loyalty_programs" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "program_name" VARCHAR(255) NOT NULL,
    "program_type" ENUM('points', 'tiers', 'punch_card', 'subscription', 'hybrid') DEFAULT 'points',

    -- Program Rules
    "earn_rate" DECIMAL(5, 2) NOT NULL DEFAULT 1.00 COMMENT 'Points per dollar spent',
    "earn_rate_type" ENUM('per_dollar', 'per_transaction', 'per_visit', 'fixed') DEFAULT 'per_dollar',
    "redemption_rate" DECIMAL(5, 2) NOT NULL DEFAULT 0.01 COMMENT 'Dollar value per point',
    "min_points_to_redeem" INT DEFAULT 100,

    -- Point Expiration
    "points_expire" BOOLEAN DEFAULT FALSE,
    "points_expiry_months" INT NULL,
    "points_expiry_warning_days" INT DEFAULT 30,

    -- Tiers (if applicable)
    "has_tiers" BOOLEAN DEFAULT FALSE,
    "tiers_config" JSON NULL COMMENT 'Tier names, thresholds, benefits',

    -- Benefits
    "birthday_bonus_points" INT DEFAULT 0,
    "signup_bonus_points" INT DEFAULT 0,
    "referral_bonus_points" INT DEFAULT 0,
    "double_points_days" JSON NULL COMMENT 'Specific days for double points',

    -- Restrictions
    "excluded_products" JSON NULL,
    "excluded_categories" JSON NULL,
    "min_purchase_amount" DECIMAL(10, 2) NULL,

    -- Communication
    "send_welcome_email" BOOLEAN DEFAULT TRUE,
    "send_points_summary_monthly" BOOLEAN DEFAULT TRUE,
    "send_expiration_reminders" BOOLEAN DEFAULT TRUE,

    -- Status
    "start_date" DATE NULL,
    "end_date" DATE NULL,
    "is_active" BOOLEAN DEFAULT TRUE,

    -- Stats
    "total_members" INT DEFAULT 0,
    "total_points_issued" BIGINT DEFAULT 0,
    "total_points_redeemed" BIGINT DEFAULT 0,
    "total_value_redeemed" DECIMAL(12, 2) DEFAULT 0.00,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE
);

-- Customer Loyalty Accounts
CREATE TABLE IF NOT EXISTS "customer_loyalty_accounts" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "program_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,

    -- Account Details
    "loyalty_number" VARCHAR(50) NOT NULL UNIQUE,
    "enrolled_date" DATE NOT NULL,
    "enrollment_source" VARCHAR(100) NULL COMMENT 'In-store, online, mobile app',

    -- Points Balance
    "points_balance" INT DEFAULT 0,
    "lifetime_points_earned" BIGINT DEFAULT 0,
    "lifetime_points_redeemed" BIGINT DEFAULT 0,
    "points_pending" INT DEFAULT 0 COMMENT 'Not yet credited',

    -- Tier Status
    "current_tier" VARCHAR(100) NULL,
    "tier_start_date" DATE NULL,
    "tier_expires_date" DATE NULL,
    "tier_qualifying_spend" DECIMAL(12, 2) DEFAULT 0.00,
    "tier_qualifying_period" VARCHAR(50) NULL,

    -- Activity
    "last_activity_date" DATE NULL,
    "last_earn_date" DATE NULL,
    "last_redemption_date" DATE NULL,

    -- Stats
    "total_visits" INT DEFAULT 0,
    "total_spend" DECIMAL(12, 2) DEFAULT 0.00,
    "avg_transaction_amount" DECIMAL(10, 2) NULL,

    -- Communication Preferences
    "opt_in_sms" BOOLEAN DEFAULT TRUE,
    "opt_in_email" BOOLEAN DEFAULT TRUE,
    "opt_in_push" BOOLEAN DEFAULT TRUE,

    -- Status
    "status" ENUM('active', 'inactive', 'suspended', 'closed') DEFAULT 'active',
    "status_reason" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("program_id") REFERENCES "loyalty_programs"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    UNIQUE KEY unique_program_customer ("program_id", "customer_id"),
    INDEX idx_loyalty_number ("loyalty_number")
);

-- Loyalty Points Transactions
CREATE TABLE IF NOT EXISTS "loyalty_points_transactions" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "loyalty_account_id" BIGINT NOT NULL,
    "customer_id" INTEGER NOT NULL,

    -- Transaction Details
    "transaction_type" ENUM('earned', 'redeemed', 'expired', 'adjusted', 'bonus', 'refunded') NOT NULL,
    "transaction_date" TIMESTAMP NOT NULL,
    "points_amount" INT NOT NULL COMMENT 'Positive for earn, negative for redeem',

    -- Source
    "source_type" ENUM('purchase', 'return', 'signup', 'birthday', 'referral', 'promotion', 'manual', 'expiration') NOT NULL,
    "source_reference" VARCHAR(255) NULL COMMENT 'Order ID, transaction ID, etc.',

    -- Purchase Details (if applicable)
    "purchase_amount" DECIMAL(10, 2) NULL,
    "pos_transaction_id" BIGINT NULL,
    "order_id" INTEGER NULL,

    -- Redemption Details (if applicable)
    "redemption_value" DECIMAL(10, 2) NULL,

    -- Expiration
    "expires_at" DATE NULL,

    -- Balance After
    "balance_after" INT NULL,

    -- Notes
    "notes" TEXT NULL,
    "created_by" INTEGER NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("loyalty_account_id") REFERENCES "customer_loyalty_accounts"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    INDEX idx_customer_date ("customer_id", "transaction_date"),
    INDEX idx_transaction_type ("transaction_type")
);

-- Gift Cards
CREATE TABLE IF NOT EXISTS "gift_cards" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "card_number" VARCHAR(50) NOT NULL UNIQUE,
    "card_pin" VARCHAR(255) NULL COMMENT 'Encrypted',

    -- Card Type
    "card_type" ENUM('physical', 'digital', 'virtual') DEFAULT 'physical',
    "design_template" VARCHAR(100) NULL,

    -- Purchase Details
    "purchased_by_customer_id" INTEGER NULL,
    "purchase_date" DATE NULL,
    "purchase_amount" DECIMAL(10, 2) NOT NULL,
    "purchase_order_id" INTEGER NULL,

    -- Recipient (if gift)
    "recipient_name" VARCHAR(255) NULL,
    "recipient_email" VARCHAR(255) NULL,
    "recipient_phone" VARCHAR(20) NULL,
    "gift_message" TEXT NULL,
    "delivery_method" ENUM('email', 'sms', 'physical', 'print_at_home') NULL,
    "delivered_at" TIMESTAMP NULL,

    -- Balance
    "original_balance" DECIMAL(10, 2) NOT NULL,
    "current_balance" DECIMAL(10, 2) NOT NULL,
    "total_spent" DECIMAL(10, 2) DEFAULT 0.00,
    "total_reloaded" DECIMAL(10, 2) DEFAULT 0.00,

    -- Activation
    "is_activated" BOOLEAN DEFAULT TRUE,
    "activation_date" DATE NULL,
    "activated_by" INTEGER NULL,

    -- Expiration
    "expiration_date" DATE NULL,
    "never_expires" BOOLEAN DEFAULT FALSE,

    -- Usage
    "first_used_date" DATE NULL,
    "last_used_date" DATE NULL,
    "times_used" INT DEFAULT 0,

    -- Status
    "status" ENUM('active', 'depleted', 'expired', 'cancelled', 'lost_stolen') DEFAULT 'active',
    "status_changed_at" TIMESTAMP NULL,
    "status_reason" TEXT NULL,

    -- Restrictions
    "can_reload" BOOLEAN DEFAULT TRUE,
    "min_reload_amount" DECIMAL(10, 2) DEFAULT 10.00,
    "max_reload_amount" DECIMAL(10, 2) DEFAULT 500.00,
    "restricted_to_products" JSON NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("purchased_by_customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    INDEX idx_card_number ("card_number"),
    INDEX idx_status ("status")
);

-- Gift Card Transactions
CREATE TABLE IF NOT EXISTS "gift_card_transactions" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "gift_card_id" BIGINT NOT NULL,

    -- Transaction Details
    "transaction_type" ENUM('purchase', 'reload', 'redemption', 'refund', 'void', 'adjustment') NOT NULL,
    "transaction_date" TIMESTAMP NOT NULL,
    "amount" DECIMAL(10, 2) NOT NULL,

    -- Related Records
    "pos_transaction_id" BIGINT NULL,
    "order_id" INTEGER NULL,

    -- Balance
    "balance_before" DECIMAL(10, 2) NOT NULL,
    "balance_after" DECIMAL(10, 2) NOT NULL,

    -- Staff
    "performed_by" INTEGER NULL,

    -- Notes
    "notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("gift_card_id") REFERENCES "gift_cards"("id") ON DELETE CASCADE,
    FOREIGN KEY ("pos_transaction_id") REFERENCES "pos_transactions"("id") ON DELETE SET NULL,
    INDEX idx_gift_card ("gift_card_id"),
    INDEX idx_transaction_date ("transaction_date")
);

-- Memberships/Subscriptions
CREATE TABLE IF NOT EXISTS "membership_plans" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "plan_name" VARCHAR(255) NOT NULL,
    "plan_code" VARCHAR(50) NULL,

    -- Plan Type
    "plan_type" ENUM('dive_club', 'equipment_rental', 'unlimited_air', 'vip', 'boat_access', 'training', 'custom') NOT NULL,
    "billing_cycle" ENUM('monthly', 'quarterly', 'semi_annual', 'annual', 'lifetime') NOT NULL,

    -- Pricing
    "price" DECIMAL(10, 2) NOT NULL,
    "setup_fee" DECIMAL(10, 2) DEFAULT 0.00,
    "trial_period_days" INT DEFAULT 0,
    "trial_price" DECIMAL(10, 2) DEFAULT 0.00,

    -- Benefits
    "benefits" JSON NOT NULL COMMENT 'List of membership benefits',
    "included_services" JSON NULL,
    "discount_percentage" DECIMAL(5, 2) DEFAULT 0.00,

    -- Limits
    "max_rentals_per_month" INT NULL,
    "max_air_fills_per_month" INT NULL,
    "max_boat_dives_per_month" INT NULL,

    -- Restrictions
    "requires_certification" BOOLEAN DEFAULT FALSE,
    "min_certification_level" VARCHAR(100) NULL,
    "age_restriction" INT NULL,

    -- Auto-renewal
    "auto_renew_default" BOOLEAN DEFAULT TRUE,
    "cancellation_notice_days" INT DEFAULT 30,

    -- Status
    "is_active" BOOLEAN DEFAULT TRUE,
    "available_from" DATE NULL,
    "available_to" DATE NULL,

    -- Stats
    "total_members" INT DEFAULT 0,
    "monthly_recurring_revenue" DECIMAL(12, 2) DEFAULT 0.00,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE
);

-- Customer Memberships
CREATE TABLE IF NOT EXISTS "customer_memberships" (
    "id" BIGSERIAL PRIMARY KEY,
    "tenant_id" INTEGER NOT NULL,
    "plan_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,

    -- Membership Details
    "membership_number" VARCHAR(50) NOT NULL UNIQUE,
    "start_date" DATE NOT NULL,
    "end_date" DATE NULL,
    "next_billing_date" DATE NULL,

    -- Status
    "status" ENUM('active', 'trial', 'cancelled', 'expired', 'suspended', 'pending') DEFAULT 'active',
    "status_changed_at" TIMESTAMP NULL,

    -- Billing
    "current_price" DECIMAL(10, 2) NOT NULL,
    "auto_renew" BOOLEAN DEFAULT TRUE,
    "payment_method_id" INTEGER NULL,

    -- Usage Tracking
    "rentals_used_this_period" INT DEFAULT 0,
    "air_fills_used_this_period" INT DEFAULT 0,
    "boat_dives_used_this_period" INT DEFAULT 0,
    "usage_period_start" DATE NULL,

    -- Cancellation
    "cancel_at_period_end" BOOLEAN DEFAULT FALSE,
    "cancelled_at" TIMESTAMP NULL,
    "cancellation_reason" TEXT NULL,

    -- Notes
    "notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("plan_id") REFERENCES "membership_plans"("id") ON DELETE RESTRICT,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    INDEX idx_customer ("customer_id"),
    INDEX idx_status ("status"),
    INDEX idx_next_billing ("next_billing_date")
);

-- =====================================================
-- Sample Data
-- =====================================================

-- Sample POS Terminal
INSERT INTO "pos_terminals" (
    "tenant_id", "terminal_name", "terminal_number", "register_number",
    "device_type", "has_cash_drawer", "has_barcode_scanner", "status"
) VALUES
(1, 'Main Register', 'POS-001', 1, 'ipad', TRUE, TRUE, 'active'),
(1, 'Rental Desk', 'POS-002', 2, 'ipad', FALSE, TRUE, 'active');

-- Sample Loyalty Program
INSERT INTO "loyalty_programs" (
    "tenant_id", "program_name", "program_type", "earn_rate", "redemption_rate",
    "signup_bonus_points", "birthday_bonus_points", "is_active"
) VALUES
(1, 'Dive Rewards', 'points', 1.00, 0.01, 100, 50, TRUE);

-- Sample Membership Plans
INSERT INTO "membership_plans" (
    "tenant_id", "plan_name", "plan_type", "billing_cycle", "price",
    "benefits", "discount_percentage", "is_active"
) VALUES
(1, 'Dive Club Monthly', 'dive_club', 'monthly', 49.99,
    '["10% off all courses", "Free air fills", "Priority booking", "Monthly newsletter"]',
    10.00, TRUE),

(1, 'VIP Annual Membership', 'vip', 'annual', 499.99,
    '["20% off courses", "Free equipment rental (1/month)", "Unlimited air fills", "Free boat dives (2/month)", "Private events access"]',
    20.00, TRUE),

(1, 'Unlimited Air Fills', 'unlimited_air', 'monthly', 29.99,
    '["Unlimited air fills", "10% off equipment purchases"]',
    10.00, TRUE);
