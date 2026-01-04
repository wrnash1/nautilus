
CREATE TABLE IF NOT EXISTS "work_orders" (
  "id" SERIAL PRIMARY KEY,
  "work_order_number" VARCHAR(50) NOT NULL UNIQUE,
  "customer_id" INT UNSIGNED,
  "equipment_type" VARCHAR(100) NOT NULL,
  "brand" VARCHAR(100),
  "model" VARCHAR(100),
  "serial_number" VARCHAR(100),
  "issue_description" TEXT NOT NULL,
  "estimated_cost" DECIMAL(10,2),
  "actual_cost" DECIMAL(10,2),
  "status" ENUM('pending', 'in_progress', 'waiting_parts', 'completed', 'cancelled', 'ready_pickup') DEFAULT 'pending',
  "priority" ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  "assigned_to" INT UNSIGNED,
  "created_by" INT UNSIGNED,
  "completed_at" TIMESTAMP NULL,
  "picked_up_at" TIMESTAMP NULL,
  "notes" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
  FOREIGN KEY ("assigned_to") REFERENCES "users"("id") ON DELETE SET NULL,
  FOREIGN KEY ("created_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_work_order_number" ("work_order_number"),
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_status" ("status")
);

CREATE TABLE IF NOT EXISTS "work_order_items" (
  "id" SERIAL PRIMARY KEY,
  "work_order_id" INTEGER NOT NULL,
  "item_type" ENUM('labor', 'part', 'service') NOT NULL,
  "product_id" INT UNSIGNED,
  "description" VARCHAR(255) NOT NULL,
  "quantity" INT NOT NULL DEFAULT 1,
  "unit_price" DECIMAL(10,2) NOT NULL,
  "total" DECIMAL(10,2) NOT NULL,
  FOREIGN KEY ("work_order_id") REFERENCES "work_orders"("id") ON DELETE CASCADE,
  FOREIGN KEY ("product_id") REFERENCES "products"("id") ON DELETE SET NULL,
  INDEX "idx_work_order_id" ("work_order_id")
);

CREATE TABLE IF NOT EXISTS "work_order_notes" (
  "id" SERIAL PRIMARY KEY,
  "work_order_id" INTEGER NOT NULL,
  "user_id" INT UNSIGNED,
  "note" TEXT NOT NULL,
  "is_customer_visible" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("work_order_id") REFERENCES "work_orders"("id") ON DELETE CASCADE,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_work_order_id" ("work_order_id")
);
