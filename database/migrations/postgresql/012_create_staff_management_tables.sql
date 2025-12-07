
CREATE TABLE IF NOT EXISTS "staff_schedules" (
  "id" SERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "schedule_date" DATE NOT NULL,
  "shift_start" TIME NOT NULL,
  "shift_end" TIME NOT NULL,
  "break_duration" INT DEFAULT 0,
  "role" VARCHAR(100),
  "location" VARCHAR(100),
  "notes" TEXT,
  "created_by" INT UNSIGNED,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  FOREIGN KEY ("created_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_schedule_date" ("schedule_date")
);

CREATE TABLE IF NOT EXISTS "time_clock" (
  "id" BIGSERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "clock_in" TIMESTAMP NOT NULL,
  "clock_out" TIMESTAMP NULL,
  "break_start" TIMESTAMP NULL,
  "break_end" TIMESTAMP NULL,
  "total_hours" DECIMAL(5,2),
  "notes" TEXT,
  "approved_by" INT UNSIGNED,
  "approved_at" TIMESTAMP NULL,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  FOREIGN KEY ("approved_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_clock_in" ("clock_in")
);

CREATE TABLE IF NOT EXISTS "commissions" (
  "id" SERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "transaction_id" BIGINT UNSIGNED,
  "order_id" INT UNSIGNED,
  "commission_type" ENUM('sale', 'course', 'trip', 'rental', 'service') NOT NULL,
  "sale_amount" DECIMAL(10,2) NOT NULL,
  "commission_rate" DECIMAL(5,2) NOT NULL,
  "commission_amount" DECIMAL(10,2) NOT NULL,
  "status" ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
  "paid_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  FOREIGN KEY ("transaction_id") REFERENCES "transactions"("id") ON DELETE SET NULL,
  FOREIGN KEY ("order_id") REFERENCES "orders"("id") ON DELETE SET NULL,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_status" ("status")
);

CREATE TABLE IF NOT EXISTS "staff_performance_metrics" (
  "id" SERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "metric_date" DATE NOT NULL,
  "sales_count" INT DEFAULT 0,
  "sales_total" DECIMAL(10,2) DEFAULT 0.00,
  "courses_taught" INT DEFAULT 0,
  "customer_satisfaction" DECIMAL(3,2),
  "hours_worked" DECIMAL(5,2) DEFAULT 0.00,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_metric_date" ("metric_date")
);
