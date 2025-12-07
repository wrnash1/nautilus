
CREATE TABLE IF NOT EXISTS "coupons" (
  "id" SERIAL PRIMARY KEY,
  "code" VARCHAR(50) NOT NULL UNIQUE,
  "type" ENUM('percentage', 'fixed_amount', 'free_shipping') NOT NULL,
  "value" DECIMAL(10,2) NOT NULL,
  "minimum_purchase" DECIMAL(10,2) DEFAULT 0.00,
  "maximum_discount" DECIMAL(10,2),
  "usage_limit" INT,
  "usage_limit_per_customer" INT DEFAULT 1,
  "used_count" INT DEFAULT 0,
  "start_date" DATE,
  "expiry_date" DATE,
  "applies_to" ENUM('all', 'products', 'categories', 'customers') DEFAULT 'all',
  "applies_to_ids" JSON,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX "idx_code" ("code"),
  INDEX "idx_expiry_date" ("expiry_date")
);

CREATE TABLE IF NOT EXISTS "coupon_usage" (
  "id" SERIAL PRIMARY KEY,
  "coupon_id" INTEGER NOT NULL,
  "customer_id" INTEGER NOT NULL,
  "order_id" INT UNSIGNED,
  "transaction_id" BIGINT UNSIGNED,
  "discount_amount" DECIMAL(10,2) NOT NULL,
  "used_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("coupon_id") REFERENCES "coupons"("id") ON DELETE CASCADE,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id"),
  FOREIGN KEY ("order_id") REFERENCES "orders"("id") ON DELETE SET NULL,
  FOREIGN KEY ("transaction_id") REFERENCES "transactions"("id") ON DELETE SET NULL,
  INDEX "idx_coupon_id" ("coupon_id"),
  INDEX "idx_customer_id" ("customer_id")
);

CREATE TABLE IF NOT EXISTS "loyalty_programs" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(100) NOT NULL,
  "points_per_dollar" DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  "points_value" DECIMAL(5,2) NOT NULL DEFAULT 0.01,
  "min_points_redemption" INT DEFAULT 100,
  "expiry_days" INT,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "loyalty_tiers" (
  "id" SERIAL PRIMARY KEY,
  "program_id" INTEGER NOT NULL,
  "name" VARCHAR(100) NOT NULL,
  "min_points" INT NOT NULL,
  "max_points" INT,
  "multiplier" DECIMAL(5,2) NOT NULL DEFAULT 1.00,
  "benefits" JSON,
  "sort_order" INT DEFAULT 0,
  FOREIGN KEY ("program_id") REFERENCES "loyalty_programs"("id") ON DELETE CASCADE,
  INDEX "idx_program_id" ("program_id")
);

CREATE TABLE IF NOT EXISTS "loyalty_points" (
  "id" BIGSERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "transaction_type" ENUM('earned', 'redeemed', 'expired', 'adjustment') NOT NULL,
  "points" INT NOT NULL,
  "balance_before" INT NOT NULL,
  "balance_after" INT NOT NULL,
  "reference_type" VARCHAR(50),
  "reference_id" INT UNSIGNED,
  "expiry_date" DATE,
  "notes" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_created_at" ("created_at")
);

CREATE TABLE IF NOT EXISTS "referral_program" (
  "id" SERIAL PRIMARY KEY,
  "referrer_id" INTEGER NOT NULL,
  "referee_id" INTEGER NOT NULL,
  "referral_code" VARCHAR(50) NOT NULL UNIQUE,
  "status" ENUM('pending', 'completed', 'expired') DEFAULT 'pending',
  "referrer_reward_type" ENUM('points', 'credit', 'coupon') NOT NULL,
  "referrer_reward_value" DECIMAL(10,2) NOT NULL,
  "referee_reward_type" ENUM('points', 'credit', 'coupon') NOT NULL,
  "referee_reward_value" DECIMAL(10,2) NOT NULL,
  "referrer_rewarded_at" TIMESTAMP NULL,
  "referee_rewarded_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("referrer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("referee_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_referral_code" ("referral_code")
);

CREATE TABLE IF NOT EXISTS "email_campaigns" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "subject" VARCHAR(255) NOT NULL,
  "from_name" VARCHAR(100),
  "from_email" VARCHAR(255),
  "reply_to" VARCHAR(255),
  "template_id" INT UNSIGNED,
  "content" LONGTEXT,
  "type" ENUM('newsletter', 'promotional', 'transactional', 'automated') DEFAULT 'newsletter',
  "status" ENUM('draft', 'scheduled', 'sending', 'sent', 'paused') DEFAULT 'draft',
  "scheduled_at" TIMESTAMP NULL,
  "sent_at" TIMESTAMP NULL,
  "recipients_count" INT DEFAULT 0,
  "opened_count" INT DEFAULT 0,
  "clicked_count" INT DEFAULT 0,
  "bounced_count" INT DEFAULT 0,
  "created_by" INT UNSIGNED,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("created_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_status" ("status")
);

CREATE TABLE IF NOT EXISTS "email_templates" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "subject" VARCHAR(255),
  "content" LONGTEXT NOT NULL,
  "type" ENUM('transactional', 'marketing', 'system') DEFAULT 'marketing',
  "variables" JSON,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "email_campaign_recipients" (
  "id" BIGSERIAL PRIMARY KEY,
  "campaign_id" INTEGER NOT NULL,
  "customer_id" INTEGER NOT NULL,
  "status" ENUM('pending', 'sent', 'opened', 'clicked', 'bounced', 'failed') DEFAULT 'pending',
  "sent_at" TIMESTAMP NULL,
  "opened_at" TIMESTAMP NULL,
  "clicked_at" TIMESTAMP NULL,
  "bounced_at" TIMESTAMP NULL,
  "error_message" TEXT,
  FOREIGN KEY ("campaign_id") REFERENCES "email_campaigns"("id") ON DELETE CASCADE,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_campaign_id" ("campaign_id"),
  INDEX "idx_customer_id" ("customer_id")
);

CREATE TABLE IF NOT EXISTS "sms_messages" (
  "id" BIGSERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "phone_number" VARCHAR(20) NOT NULL,
  "message" TEXT NOT NULL,
  "message_type" ENUM('transactional', 'marketing', 'reminder', 'alert') DEFAULT 'transactional',
  "status" ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
  "external_id" VARCHAR(255),
  "cost" DECIMAL(5,4),
  "sent_at" TIMESTAMP NULL,
  "delivered_at" TIMESTAMP NULL,
  "error_message" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_status" ("status")
);

CREATE TABLE IF NOT EXISTS "automation_workflows" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "trigger_type" ENUM('customer_birthday', 'abandoned_cart', 'service_due', 'course_completion', 'order_placed', 'customer_inactive') NOT NULL,
  "trigger_conditions" JSON,
  "actions" JSON NOT NULL,
  "is_active" BOOLEAN DEFAULT TRUE,
  "last_run_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "automation_logs" (
  "id" BIGSERIAL PRIMARY KEY,
  "workflow_id" INTEGER NOT NULL,
  "customer_id" INT UNSIGNED,
  "status" ENUM('success', 'failed', 'skipped') NOT NULL,
  "actions_executed" JSON,
  "error_message" TEXT,
  "executed_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("workflow_id") REFERENCES "automation_workflows"("id") ON DELETE CASCADE,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
  INDEX "idx_workflow_id" ("workflow_id")
);
