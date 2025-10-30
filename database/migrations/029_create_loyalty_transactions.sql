-- ============================================================================
-- Migration: Create Loyalty Program Transaction and Rewards System
-- Created: 2024
-- Description: Enhanced loyalty program with points ledger, rewards catalog, and redemptions
-- Note: loyalty_programs, loyalty_tiers, loyalty_points tables already exist from 011_create_marketing_tables.sql
-- ============================================================================

-- Loyalty Points Transaction Ledger (detailed history)
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,  -- 'earn', 'redeem', 'expire', 'bonus', 'adjustment'
    points_amount INTEGER NOT NULL,  -- Positive for earn, negative for redeem/expire
    points_balance_after INTEGER NOT NULL,  -- Running balance
    source_type VARCHAR(50),  -- 'purchase', 'referral', 'birthday', 'review', 'manual'
    source_id INTEGER,  -- ID of related transaction/order
    description TEXT,
    expires_at TIMESTAMP,  -- When these points expire
    created_by INTEGER,  -- user_id if manual adjustment
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_customer ON loyalty_transactions(customer_id);
CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_type ON loyalty_transactions(transaction_type);
CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_date ON loyalty_transactions(created_at);
CREATE INDEX IF NOT EXISTS idx_loyalty_transactions_expires ON loyalty_transactions(expires_at);

-- Rewards Catalog
CREATE TABLE IF NOT EXISTS loyalty_rewards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    points_required INTEGER NOT NULL,
    reward_type VARCHAR(50) NOT NULL,  -- 'discount_percentage', 'discount_fixed', 'free_product', 'free_shipping', 'gift_card'
    reward_value DECIMAL(10,2),  -- Value of reward (e.g., 10.00 for $10 discount)
    product_id INTEGER,  -- If reward_type is 'free_product'
    min_purchase_amount DECIMAL(10,2),  -- Minimum purchase to use reward
    max_discount_amount DECIMAL(10,2),  -- Maximum discount for percentage-based rewards
    is_active BOOLEAN DEFAULT 1,
    start_date DATE,
    end_date DATE,
    usage_limit INTEGER,  -- How many times this can be redeemed total
    usage_limit_per_customer INTEGER DEFAULT 1,
    times_redeemed INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    image_url VARCHAR(500),
    terms_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_loyalty_rewards_active ON loyalty_rewards(is_active);
CREATE INDEX IF NOT EXISTS idx_loyalty_rewards_points ON loyalty_rewards(points_required);
CREATE INDEX IF NOT EXISTS idx_loyalty_rewards_dates ON loyalty_rewards(start_date, end_date);

-- Reward Redemptions
CREATE TABLE IF NOT EXISTS loyalty_reward_claims (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_id INTEGER NOT NULL,
    reward_id INTEGER NOT NULL,
    points_spent INTEGER NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- 'pending', 'approved', 'redeemed', 'expired', 'cancelled'
    redemption_code VARCHAR(50) UNIQUE,  -- Unique code for customer to use
    order_id INTEGER,  -- If used in an order
    expires_at TIMESTAMP,  -- When redemption code expires
    redeemed_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (reward_id) REFERENCES loyalty_rewards(id) ON DELETE RESTRICT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_loyalty_reward_claims_customer ON loyalty_reward_claims(customer_id);
CREATE INDEX IF NOT EXISTS idx_loyalty_reward_claims_reward ON loyalty_reward_claims(reward_id);
CREATE INDEX IF NOT EXISTS idx_loyalty_reward_claims_status ON loyalty_reward_claims(status);
CREATE INDEX IF NOT EXISTS idx_loyalty_reward_claims_code ON loyalty_reward_claims(redemption_code);

-- Referral Tracking (enhanced from existing)
CREATE TABLE IF NOT EXISTS customer_referrals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    referrer_customer_id INTEGER NOT NULL,  -- Customer who referred
    referred_customer_id INTEGER NOT NULL,  -- New customer
    referral_code VARCHAR(50),
    status VARCHAR(20) NOT NULL DEFAULT 'pending',  -- 'pending', 'completed', 'paid'
    referrer_points_awarded INTEGER DEFAULT 0,
    referred_points_awarded INTEGER DEFAULT 0,
    first_purchase_amount DECIMAL(10,2),
    first_purchase_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    FOREIGN KEY (referrer_customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_customer_referrals_referrer ON customer_referrals(referrer_customer_id);
CREATE INDEX IF NOT EXISTS idx_customer_referrals_referred ON customer_referrals(referred_customer_id);
CREATE INDEX IF NOT EXISTS idx_customer_referrals_status ON customer_referrals(status);

-- Insert default rewards
INSERT OR IGNORE INTO loyalty_rewards (id, name, description, points_required, reward_type, reward_value, is_active, sort_order) VALUES
(1, '$5 Off Any Purchase', 'Get $5 off your next purchase', 500, 'discount_fixed', 5.00, 1, 1),
(2, '$10 Off Any Purchase', 'Get $10 off your next purchase', 1000, 'discount_fixed', 10.00, 1, 2),
(3, '$25 Off Any Purchase', 'Get $25 off your next purchase', 2500, 'discount_fixed', 25.00, 1, 3),
(4, '10% Off Next Order', 'Save 10% on your entire order', 750, 'discount_percentage', 10.00, 1, 4),
(5, 'Free Shipping', 'Get free shipping on your next order', 300, 'free_shipping', NULL, 1, 5);

-- ============================================================================
-- Migration Complete
-- ============================================================================
