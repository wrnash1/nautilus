-- ================================================
-- Nautilus - Specialty Courses System
-- Migration: 076_specialty_courses_system.sql
-- Description: Advanced Open Water, Rescue Diver, Divemaster, and specialty certifications
-- ================================================

-- Specialty Courses Catalog
CREATE TABLE IF NOT EXISTS "specialty_courses" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    -- Course Identification
    "course_code" VARCHAR(50) NOT NULL COMMENT 'PADI course code (e.g., AOW, RED, DM)',
    "course_name" VARCHAR(255) NOT NULL,
    "course_type" ENUM('specialty', 'advanced', 'professional', 'tech') DEFAULT 'specialty',

    -- Certification Level
    "certification_level" ENUM('beginner', 'advanced', 'rescue', 'divemaster', 'instructor', 'technical') NOT NULL,
    "prerequisite_cert_level" VARCHAR(100) NULL COMMENT 'Required cert before enrollment',

    -- Course Details
    "description" TEXT NULL,
    "objectives" TEXT NULL,
    "duration_days" INT NULL,
    "minimum_dives" INT NULL,
    "classroom_hours" INT NULL,
    "pool_hours" INT NULL,
    "open_water_dives" INT NULL,

    -- Pricing
    "base_price" DECIMAL(10,2) NULL,
    "materials_price" DECIMAL(10,2) NULL,
    "certification_fee" DECIMAL(10,2) NULL,

    -- Requirements
    "minimum_age" INT DEFAULT 12,
    "medical_clearance_required" BOOLEAN DEFAULT TRUE,
    "prerequisites_json" JSON NULL COMMENT 'List of prerequisite courses/certs',

    -- Course Content
    "skills_required" JSON NULL COMMENT 'List of skills to be mastered',
    "knowledge_topics" JSON NULL COMMENT 'Theory topics covered',

    -- PADI/SSI Specific
    "padi_course_number" VARCHAR(50) NULL,
    "ssi_course_number" VARCHAR(50) NULL,
    "certification_agency" ENUM('PADI', 'SSI', 'NAUI', 'SDI', 'OTHER') DEFAULT 'PADI',

    -- E-Learning
    "elearning_available" BOOLEAN DEFAULT FALSE,
    "elearning_url" VARCHAR(500) NULL,
    "elearning_cost" DECIMAL(10,2) NULL,

    -- Status
    "is_active" BOOLEAN DEFAULT TRUE,
    "is_featured" BOOLEAN DEFAULT FALSE,
    "display_order" INT DEFAULT 0,

    -- Media
    "image_url" VARCHAR(500) NULL,
    "brochure_pdf_url" VARCHAR(500) NULL,

    -- Timestamps
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,

    UNIQUE KEY "unique_course_code" ("tenant_id", "course_code"),
    INDEX "idx_course_type" ("course_type"),
    INDEX "idx_cert_level" ("certification_level"),
    INDEX "idx_active" ("is_active"),
    INDEX "idx_display_order" ("display_order")
);

-- Seed Specialty Courses
INSERT INTO "specialty_courses" ("course_code", "course_name", "course_type", "certification_level", "description", "duration_days", "minimum_dives", "open_water_dives", "base_price", "minimum_age", "prerequisites_json", "certification_agency") VALUES
-- Advanced Open Water
('AOW', 'Advanced Open Water Diver', 'advanced', 'advanced',
 'Expand your diving knowledge and skills through five Adventure Dives.',
 2, 5, 5, 399.00, 12,
 '["Open Water Diver"]', 'PADI'),

-- Rescue Diver
('RED', 'Rescue Diver', 'specialty', 'rescue',
 'Learn to prevent and manage problems in the water, and become more confident in your diving abilities.',
 3, 0, 4, 499.00, 12,
 '["Advanced Open Water Diver", "CPR/First Aid within 24 months"]', 'PADI'),

-- Divemaster
('DM', 'Divemaster', 'professional', 'divemaster',
 'The first professional level certification. Become a leader and guide certified divers.',
 30, 40, 20, 1299.00, 18,
 '["Rescue Diver", "Minimum 40 logged dives", "CPR/First Aid within 24 months"]', 'PADI'),

-- Specialty Courses
('DEEP', 'Deep Diver', 'specialty', 'advanced',
 'Gain the knowledge and skills to plan and make dives beyond 18 meters/60 feet.',
 2, 0, 4, 299.00, 15,
 '["Adventure Diver or Junior Adventure Diver"]', 'PADI'),

('WRECK', 'Wreck Diver', 'specialty', 'advanced',
 'Learn to explore shipwrecks safely and respectfully.',
 2, 0, 4, 299.00, 15,
 '["Adventure Diver"]', 'PADI'),

('NITROX', 'Enriched Air (Nitrox) Diver', 'specialty', 'advanced',
 'Extend your dive time and reduce surface intervals with enriched air.',
 1, 0, 2, 199.00, 12,
 '["Open Water Diver"]', 'PADI'),

('NIGHT', 'Night Diver', 'specialty', 'advanced',
 'Discover the underwater world after dark.',
 2, 0, 3, 249.00, 12,
 '["Open Water Diver"]', 'PADI'),

('UW_NAV', 'Underwater Navigator', 'specialty', 'advanced',
 'Fine-tune your observation skills and navigate with precision.',
 2, 0, 3, 249.00, 10,
 '["Open Water Diver"]', 'PADI'),

('DRY_SUIT', 'Dry Suit Diver', 'specialty', 'advanced',
 'Learn to dive comfortably in cold water using a dry suit.',
 1, 0, 2, 249.00, 10,
 '["Open Water Diver"]', 'PADI'),

('SEARCH', 'Search and Recovery Diver', 'specialty', 'advanced',
 'Learn effective techniques for finding and recovering objects underwater.',
 2, 0, 4, 299.00, 12,
 '["Adventure Diver", "Underwater Navigator Adventure Dive"]', 'PADI'),

('UW_PHOTO', 'Underwater Photographer', 'specialty', 'advanced',
 'Capture amazing underwater images.',
 2, 0, 2, 349.00, 10,
 '["Open Water Diver"]', 'PADI'),

('UW_VIDEO', 'Underwater Videographer', 'specialty', 'advanced',
 'Learn to create stunning underwater videos.',
 2, 0, 3, 349.00, 10,
 '["Open Water Diver"]', 'PADI'),

('BOAT', 'Boat Diver', 'specialty', 'advanced',
 'Learn the ins and outs of boat diving.',
 1, 0, 2, 199.00, 10,
 '["Open Water Diver"]', 'PADI'),

('DRIFT', 'Drift Diver', 'specialty', 'advanced',
 'Relax and let the current carry you along colorful reefs.',
 1, 0, 2, 199.00, 12,
 '["Open Water Diver"]', 'PADI'),

('ALTITUDE', 'Altitude Diver', 'specialty', 'advanced',
 'Learn the skills and knowledge to dive at altitudes above 300 meters/1000 feet.',
 1, 0, 2, 199.00, 10,
 '["Open Water Diver"]', 'PADI'),

('ICE', 'Ice Diver', 'specialty', 'advanced',
 'Experience the thrill of diving under ice.',
 1, 0, 3, 299.00, 18,
 '["Adventure Diver", "Dry Suit Diver"]', 'PADI');

-- Specialty Course Schedules
CREATE TABLE IF NOT EXISTS "specialty_course_schedules" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    "specialty_course_id" INTEGER NOT NULL,
    "instructor_id" INTEGER NOT NULL,

    -- Schedule Details
    "start_date" DATE NOT NULL,
    "end_date" DATE NOT NULL,
    "schedule_name" VARCHAR(255) NULL,

    -- Capacity
    "max_students" INT DEFAULT 8,
    "enrolled_count" INT DEFAULT 0,
    "waitlist_count" INT DEFAULT 0,

    -- Location
    "location" VARCHAR(255) NULL,
    "meeting_point" TEXT NULL,

    -- Sessions (JSON array of dates/times)
    "sessions" JSON NULL,

    -- Pricing (can override course default)
    "price_override" DECIMAL(10,2) NULL,

    -- Status
    "status" ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',

    -- Notes
    "notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("specialty_course_id") REFERENCES "specialty_courses"("id") ON DELETE CASCADE,
    FOREIGN KEY ("instructor_id") REFERENCES "users"("id") ON DELETE CASCADE,

    INDEX "idx_start_date" ("start_date"),
    INDEX "idx_status" ("status"),
    INDEX "idx_instructor" ("instructor_id")
);

-- Specialty Course Enrollments
CREATE TABLE IF NOT EXISTS "specialty_course_enrollments" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    "specialty_course_schedule_id" INTEGER NOT NULL,
    "customer_id" INTEGER NOT NULL,

    -- Enrollment Status
    "status" ENUM('enrolled', 'in_progress', 'completed', 'dropped', 'failed') DEFAULT 'enrolled',
    "enrollment_date" DATE NOT NULL,

    -- Prerequisites Verified
    "prerequisites_verified" BOOLEAN DEFAULT FALSE,
    "verified_by_user_id" INTEGER NULL,
    "verified_at" TIMESTAMP NULL,

    -- Progress Tracking
    "classroom_hours_completed" DECIMAL(5,2) DEFAULT 0,
    "pool_hours_completed" DECIMAL(5,2) DEFAULT 0,
    "open_water_dives_completed" INT DEFAULT 0,

    -- Skills Completion
    "skills_completed" JSON NULL COMMENT 'Array of completed skills',
    "knowledge_tests_passed" JSON NULL,

    -- Completion
    "completion_date" DATE NULL,
    "certification_number" VARCHAR(100) NULL,
    "ecard_issued" BOOLEAN DEFAULT FALSE,
    "physical_card_issued" BOOLEAN DEFAULT FALSE,

    -- Payment
    "amount_paid" DECIMAL(10,2) DEFAULT 0,
    "payment_status" ENUM('pending', 'partial', 'paid', 'refunded') DEFAULT 'pending',

    -- Notes
    "instructor_notes" TEXT NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("specialty_course_schedule_id") REFERENCES "specialty_course_schedules"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("verified_by_user_id") REFERENCES "users"("id") ON DELETE SET NULL,

    UNIQUE KEY "unique_enrollment" ("specialty_course_schedule_id", "customer_id"),
    INDEX "idx_customer" ("customer_id"),
    INDEX "idx_status" ("status"),
    INDEX "idx_enrollment_date" ("enrollment_date")
);
