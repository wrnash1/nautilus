
CREATE TABLE IF NOT EXISTS "certification_agencies" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(100) NOT NULL UNIQUE,
  "abbreviation" VARCHAR(20) NOT NULL,
  "website" VARCHAR(255),
  "api_endpoint" VARCHAR(255),
  "api_key_encrypted" VARCHAR(255),
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "certifications" (
  "id" SERIAL PRIMARY KEY,
  "agency_id" INTEGER NOT NULL,
  "name" VARCHAR(255) NOT NULL,
  "level" INT NOT NULL,
  "code" VARCHAR(50),
  "description" TEXT,
  "prerequisites" JSON,
  "is_active" BOOLEAN DEFAULT TRUE,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("agency_id") REFERENCES "certification_agencies"("id"),
  INDEX "idx_agency_id" ("agency_id"),
  INDEX "idx_level" ("level")
);

CREATE TABLE IF NOT EXISTS "customer_certifications" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "certification_id" INTEGER NOT NULL,
  "certification_number" VARCHAR(100),
  "issue_date" DATE,
  "instructor_name" VARCHAR(200),
  "verification_status" ENUM('pending', 'verified', 'expired', 'invalid') DEFAULT 'pending',
  "c_card_front_path" VARCHAR(255),
  "c_card_back_path" VARCHAR(255),
  "notes" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("certification_id") REFERENCES "certifications"("id"),
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_certification_number" ("certification_number")
);
