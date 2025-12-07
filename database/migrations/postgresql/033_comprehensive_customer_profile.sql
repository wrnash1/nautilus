-- Comprehensive customer profile enhancement for dive shop operations
-- This migration adds all necessary fields for a professional scuba diving business

-- Add core customer profile fields
-- Note: photo_path already added in migration 014
ALTER TABLE "customers"
ADD COLUMN IF NOT EXISTS "middle_name" VARCHAR(100) NULL AFTER "last_name",
ADD COLUMN IF NOT EXISTS "gender" ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL AFTER "birth_date",
ADD COLUMN IF NOT EXISTS "photo_path" VARCHAR(255) NULL AFTER "gender",
ADD COLUMN IF NOT EXISTS "signature_path" VARCHAR(255) NULL AFTER "photo_path",
ADD COLUMN IF NOT EXISTS "height_cm" DECIMAL(5,2) NULL COMMENT 'Height in centimeters' AFTER "signature_path",
ADD COLUMN IF NOT EXISTS "weight_kg" DECIMAL(5,2) NULL COMMENT 'Weight in kilograms' AFTER "height_cm",
ADD COLUMN IF NOT EXISTS "shoe_size" VARCHAR(10) NULL AFTER "weight_kg",
ADD COLUMN IF NOT EXISTS "wetsuit_size" VARCHAR(10) NULL AFTER "shoe_size",
ADD COLUMN IF NOT EXISTS "bcd_size" VARCHAR(10) NULL AFTER "wetsuit_size";

-- Add contact information fields
ALTER TABLE "customers"
ADD COLUMN IF NOT EXISTS "home_phone" VARCHAR(20) NULL AFTER "phone",
ADD COLUMN IF NOT EXISTS "work_phone" VARCHAR(20) NULL AFTER "home_phone",
ADD COLUMN IF NOT EXISTS "preferred_contact_method" ENUM('email', 'mobile', 'home_phone', 'work_phone', 'sms') DEFAULT 'email' AFTER "work_phone",
ADD COLUMN IF NOT EXISTS "preferred_language" VARCHAR(10) DEFAULT 'en' AFTER "preferred_contact_method";

-- Add personal information
ALTER TABLE "customers"
ADD COLUMN IF NOT EXISTS "occupation" VARCHAR(100) NULL AFTER "preferred_language",
ADD COLUMN IF NOT EXISTS "marital_status" ENUM('single', 'married', 'divorced', 'widowed', 'other') NULL AFTER "occupation",
ADD COLUMN IF NOT EXISTS "spouse_name" VARCHAR(200) NULL AFTER "marital_status",
ADD COLUMN IF NOT EXISTS "number_of_children" INT DEFAULT 0 AFTER "spouse_name",
ADD COLUMN IF NOT EXISTS "how_did_you_hear" VARCHAR(255) NULL COMMENT 'How did you hear about us' AFTER "number_of_children";

-- Add membership and loyalty fields
ALTER TABLE "customers"
ADD COLUMN IF NOT EXISTS "is_loyalty_member" BOOLEAN DEFAULT FALSE AFTER "how_did_you_hear",
ADD COLUMN IF NOT EXISTS "loyalty_tier" ENUM('bronze', 'silver', 'gold', 'platinum') NULL AFTER "is_loyalty_member",
ADD COLUMN IF NOT EXISTS "club_membership_start" DATE NULL AFTER "loyalty_tier",
ADD COLUMN IF NOT EXISTS "club_membership_end" DATE NULL AFTER "club_membership_start",
ADD COLUMN IF NOT EXISTS "newsletter_opt_in" BOOLEAN DEFAULT FALSE AFTER "club_membership_end";

-- Add status and tracking
ALTER TABLE "customers"
ADD COLUMN IF NOT EXISTS "status" ENUM('active', 'inactive', 'suspended', 'archived') DEFAULT 'active' AFTER "newsletter_opt_in",
ADD COLUMN IF NOT EXISTS "deactivation_date" TIMESTAMP NULL AFTER "status",
ADD COLUMN IF NOT EXISTS "deactivation_reason" TEXT NULL AFTER "deactivation_date",
ADD COLUMN IF NOT EXISTS "last_visit_date" TIMESTAMP NULL AFTER "deactivation_reason",
ADD COLUMN IF NOT EXISTS "total_visits" INT DEFAULT 0 AFTER "last_visit_date",
ADD COLUMN IF NOT EXISTS "lifetime_value" DECIMAL(10,2) DEFAULT 0.00 AFTER "total_visits";

-- Create customer medical information table
CREATE TABLE IF NOT EXISTS "customer_medical_info" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "has_allergies" BOOLEAN DEFAULT FALSE,
  "allergies_details" TEXT NULL,
  "has_medical_conditions" BOOLEAN DEFAULT FALSE,
  "medical_conditions_details" TEXT NULL,
  "has_injuries" BOOLEAN DEFAULT FALSE,
  "injuries_details" TEXT NULL,
  "medications" TEXT NULL,
  "physician_name" VARCHAR(200) NULL,
  "physician_phone" VARCHAR(20) NULL,
  "medical_clearance_date" DATE NULL,
  "medical_clearance_expires" DATE NULL,
  "medical_form_path" VARCHAR(255) NULL,
  "fitness_level" ENUM('beginner', 'intermediate', 'advanced', 'expert') NULL,
  "fitness_goals" TEXT NULL,
  "last_physical_exam" DATE NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id")
);

-- Create customer preferences table
CREATE TABLE IF NOT EXISTS "customer_preferences" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "wants_personal_training" BOOLEAN DEFAULT FALSE,
  "wants_group_classes" BOOLEAN DEFAULT FALSE,
  "preferred_time_of_day" ENUM('morning', 'afternoon', 'evening', 'weekend') NULL,
  "preferred_days" JSON NULL COMMENT 'Array of preferred days (Monday, Tuesday, etc)',
  "preferred_instructors" JSON NULL COMMENT 'Array of preferred instructor IDs',
  "preferred_dive_sites" JSON NULL COMMENT 'Array of preferred dive site IDs',
  "preferred_equipment" TEXT NULL,
  "diving_interests" JSON NULL COMMENT 'wreck, reef, deep, technical, photography, etc',
  "communication_preferences" JSON NULL COMMENT 'email, SMS, phone call preferences',
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  UNIQUE KEY "idx_customer_id" ("customer_id")
);

-- Create customer documents table
CREATE TABLE IF NOT EXISTS "customer_documents" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "document_type" ENUM('medical_form', 'liability_waiver', 'certification_card', 'photo_id', 'insurance', 'other') NOT NULL,
  "document_name" VARCHAR(255) NOT NULL,
  "file_path" VARCHAR(255) NOT NULL,
  "file_size" INT NULL COMMENT 'File size in bytes',
  "mime_type" VARCHAR(100) NULL,
  "upload_date" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "expiration_date" DATE NULL,
  "is_verified" BOOLEAN DEFAULT FALSE,
  "verified_by" INTEGER NULL,
  "verified_at" TIMESTAMP NULL,
  "notes" TEXT NULL,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("verified_by") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_document_type" ("document_type"),
  INDEX "idx_expiration_date" ("expiration_date")
);

-- Create customer interactions table (for tracking communications)
CREATE TABLE IF NOT EXISTS "customer_interactions" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "user_id" INTEGER NULL COMMENT 'Staff member who had the interaction',
  "interaction_type" ENUM('call', 'email', 'in_person', 'sms', 'note', 'complaint', 'feedback') NOT NULL,
  "subject" VARCHAR(255) NULL,
  "description" TEXT NOT NULL,
  "sentiment" ENUM('positive', 'neutral', 'negative') DEFAULT 'neutral',
  "follow_up_required" BOOLEAN DEFAULT FALSE,
  "follow_up_date" DATE NULL,
  "interaction_date" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_interaction_type" ("interaction_type"),
  INDEX "idx_follow_up" ("follow_up_required", "follow_up_date")
);

-- Create customer family members table
CREATE TABLE IF NOT EXISTS "customer_family_members" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL COMMENT 'Primary customer',
  "family_member_customer_id" INTEGER NULL COMMENT 'If family member is also a customer',
  "first_name" VARCHAR(100) NOT NULL,
  "last_name" VARCHAR(100) NOT NULL,
  "relationship" ENUM('spouse', 'child', 'parent', 'sibling', 'other') NOT NULL,
  "birth_date" DATE NULL,
  "email" VARCHAR(255) NULL,
  "phone" VARCHAR(20) NULL,
  "is_emergency_contact" BOOLEAN DEFAULT FALSE,
  "notes" TEXT NULL,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  FOREIGN KEY ("family_member_customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_emergency_contact" ("is_emergency_contact")
);

-- Create customer satisfaction surveys table
CREATE TABLE IF NOT EXISTS "customer_satisfaction_surveys" (
  "id" SERIAL PRIMARY KEY,
  "customer_id" INTEGER NOT NULL,
  "transaction_id" INTEGER NULL,
  "course_id" INTEGER NULL,
  "trip_id" INTEGER NULL,
  "overall_rating" INT NOT NULL COMMENT '1-5 star rating',
  "instructor_rating" INT NULL,
  "equipment_rating" INT NULL,
  "facility_rating" INT NULL,
  "value_rating" INT NULL,
  "would_recommend" BOOLEAN DEFAULT TRUE,
  "comments" TEXT NULL,
  "improvement_suggestions" TEXT NULL,
  "survey_date" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
  INDEX "idx_customer_id" ("customer_id"),
  INDEX "idx_overall_rating" ("overall_rating")
);

-- Add indexes for performance
ALTER TABLE "customers" ADD INDEX "idx_status" ("status");
ALTER TABLE "customers" ADD INDEX "idx_loyalty_member" ("is_loyalty_member");
ALTER TABLE "customers" ADD INDEX "idx_club_membership" ("club_membership_end");
ALTER TABLE "customers" ADD INDEX "idx_photo_path" ("photo_path");
