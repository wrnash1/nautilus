
CREATE TABLE IF NOT EXISTS "customers" (
  "id" SERIAL PRIMARY KEY,
  "customer_type" ENUM('B2C', 'B2B') NOT NULL DEFAULT 'B2C',
  "company_name" VARCHAR(255),
  "first_name" VARCHAR(100) NOT NULL,
  "last_name" VARCHAR(100) NOT NULL,
  "email" VARCHAR(255) NOT NULL,
  "phone" VARCHAR(20),
  "mobile" VARCHAR(20),
  "birth_date" DATE,
  "photo_path" VARCHAR(255),
  "emergency_contact_name" VARCHAR(200),
  "emergency_contact_phone" VARCHAR(20),
  "marketing_opt_in" BOOLEAN DEFAULT FALSE,
  "marketing_opt_in_date" TIMESTAMP NULL,
  "sms_opt_in" BOOLEAN DEFAULT FALSE,
  "preferred_communication" ENUM('email', 'sms', 'phone') DEFAULT 'email',
  "customer_since" DATE,
  "last_purchase_date" DATE,
  "total_purchases" DECIMAL(10,2) DEFAULT 0.00,
  "lifetime_value" DECIMAL(10,2) DEFAULT 0.00,
  "loyalty_points" INT DEFAULT 0,
  "loyalty_tier" VARCHAR(50),
  "tax_exempt" BOOLEAN DEFAULT FALSE,
  "tax_exempt_number" VARCHAR(100),
  "credit_limit" DECIMAL(10,2) DEFAULT 0.00,
  "credit_terms" VARCHAR(50),
  "notes" TEXT,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX "idx_customer_type" ("customer_type"),
  INDEX "idx_email" ("email"),
  INDEX "idx_phone" ("phone"),
  INDEX "idx_last_purchase_date" ("last_purchase_date"),
  INDEX "idx_is_active" ("is_active")
);

CREATE TABLE IF NOT EXISTS "customer_addresses" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "address_type" ENUM('billing', 'shipping', 'both') NOT NULL DEFAULT 'both',
  "address_line1" VARCHAR(255) NOT NULL,
  "address_line2" VARCHAR(255),
  "city" VARCHAR(100) NOT NULL,
  "state" VARCHAR(50) NOT NULL,
  "postal_code" VARCHAR(20) NOT NULL,
  "country" VARCHAR(2) DEFAULT 'US',
  "is_default" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id")
);

CREATE TABLE IF NOT EXISTS "customer_tags" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(50) NOT NULL UNIQUE,
  "color" VARCHAR(7) DEFAULT '#3b82f6',
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "customer_tag_assignments" (
  "customer_id" INTEGER NOT NULL,
  "tag_id" INTEGER NOT NULL,
  "assigned_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ("customer_id", "tag_id"),
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("tag_id") REFERENCES "customer_tags"("id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "customer_notes" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "user_id" INT UNSIGNED,
  "note_type" ENUM('general', 'important', 'interaction', 'issue') DEFAULT 'general',
  "content" TEXT NOT NULL,
  "is_pinned" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_created_at" ("created_at")
);

CREATE TABLE IF NOT EXISTS "customer_documents" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "document_type" ENUM('c-card', 'id', 'insurance', 'medical', 'waiver', 'other') NOT NULL,
  "file_path" VARCHAR(255) NOT NULL,
  "file_name" VARCHAR(255) NOT NULL,
  "file_size" INT UNSIGNED,
  "mime_type" VARCHAR(100),
  "ocr_data" JSON,
  "expiry_date" DATE,
  "uploaded_by" INT UNSIGNED,
  "uploaded_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("uploaded_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_document_type" ("document_type")
);

CREATE TABLE IF NOT EXISTS "customer_communications" (
  "id" BIGSERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "communication_type" ENUM('email', 'sms', 'phone', 'in-person') NOT NULL,
  "direction" ENUM('inbound', 'outbound') NOT NULL,
  "subject" VARCHAR(255),
  "content" TEXT,
  "status" ENUM('sent', 'delivered', 'failed', 'bounced') DEFAULT 'sent',
  "user_id" INT UNSIGNED,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_created_at" ("created_at")
);

CREATE TABLE IF NOT EXISTS "b2b_contacts" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "first_name" VARCHAR(100) NOT NULL,
  "last_name" VARCHAR(100) NOT NULL,
  "email" VARCHAR(255) NOT NULL,
  "phone" VARCHAR(20),
  "title" VARCHAR(100),
  "is_primary" BOOLEAN DEFAULT FALSE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id")
);
