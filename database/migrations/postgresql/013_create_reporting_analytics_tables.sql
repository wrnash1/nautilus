
CREATE TABLE IF NOT EXISTS "reports" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "type" ENUM('sales', 'inventory', 'customer', 'financial', 'operational', 'custom') NOT NULL,
  "description" TEXT,
  "query" TEXT,
  "parameters" JSON,
  "schedule" ENUM('none', 'daily', 'weekly', 'monthly') DEFAULT 'none',
  "recipients" JSON,
  "created_by" INT UNSIGNED,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("created_by") REFERENCES "users"("id") ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS "dashboards" (
  "id" SERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "name" VARCHAR(255) NOT NULL,
  "widgets" JSON NOT NULL,
  "layout" JSON,
  "is_default" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  INDEX "idx_user_id" ("user_id")
);

CREATE TABLE IF NOT EXISTS "documents" (
  "id" SERIAL PRIMARY KEY,
  "document_type" VARCHAR(100) NOT NULL,
  "title" VARCHAR(255) NOT NULL,
  "description" TEXT,
  "file_path" VARCHAR(255) NOT NULL,
  "file_name" VARCHAR(255) NOT NULL,
  "file_size" BIGINT UNSIGNED,
  "mime_type" VARCHAR(100),
  "version" INT DEFAULT 1,
  "parent_id" INT UNSIGNED,
  "google_drive_id" VARCHAR(255),
  "tags" JSON,
  "uploaded_by" INT UNSIGNED,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("parent_id") REFERENCES "documents"("id") ON DELETE SET NULL,
  FOREIGN KEY ("uploaded_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_document_type" ("document_type"),
  FULLTEXT "idx_search" ("title", "description")
);

CREATE TABLE IF NOT EXISTS "appointments" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "appointment_type" ENUM('fitting', 'consultation', 'pickup', 'other') NOT NULL,
  "start_time" TIMESTAMP NOT NULL,
  "end_time" TIMESTAMP NOT NULL,
  "assigned_to" INT UNSIGNED,
  "location" VARCHAR(255),
  "status" ENUM('scheduled', 'confirmed', 'completed', 'no_show', 'cancelled') DEFAULT 'scheduled',
  "notes" TEXT,
  "google_calendar_id" VARCHAR(255),
  "reminder_sent" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id"),
  FOREIGN KEY ("assigned_to") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_start_time" ("start_time"),
  INDEX "idx_status" ("status")
);

CREATE TABLE IF NOT EXISTS "integrations" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(100) NOT NULL UNIQUE,
  "type" ENUM('payment', 'shipping', 'accounting', 'marketing', 'communication', 'certification', 'other') NOT NULL,
  "credentials" TEXT,
  "settings" JSON,
  "is_active" BOOLEAN DEFAULT TRUE,
  "last_sync_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "settings" (
  "id" SERIAL PRIMARY KEY,
  "category" VARCHAR(50) NOT NULL,
  "key" VARCHAR(100) NOT NULL,
  "value" TEXT,
  "type" ENUM('string', 'integer', 'boolean', 'json', 'encrypted') DEFAULT 'string',
  "description" TEXT,
  "updated_by" INT UNSIGNED,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY "unique_setting" ("category", "key"),
  FOREIGN KEY ("updated_by") REFERENCES "users"("id") ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS "notifications" (
  "id" BIGSERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "type" VARCHAR(50) NOT NULL,
  "title" VARCHAR(255) NOT NULL,
  "message" TEXT NOT NULL,
  "action_url" VARCHAR(255),
  "is_read" BOOLEAN DEFAULT FALSE,
  "read_at" TIMESTAMP NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
  INDEX "idx_user_id" ("user_id"),
  INDEX "idx_is_read" ("is_read")
);
