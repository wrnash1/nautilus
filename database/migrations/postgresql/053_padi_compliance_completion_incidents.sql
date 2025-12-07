-- ================================================
-- Nautilus V6 - PADI Compliance: Training Completion & Incidents
-- Migration: 053_padi_compliance_completion_incidents.sql
-- Description: Training completion forms and incident reporting
-- ================================================

-- Training completion forms (PADI Form 10234)
CREATE TABLE IF NOT EXISTS "training_completion_forms" (
    "id" SERIAL PRIMARY KEY,
    "customer_id" INTEGER NOT NULL,
    "course_id" INTEGER NOT NULL,
    "enrollment_id" INT UNSIGNED,
    "student_record_id" INT UNSIGNED,

    -- Student Information
    "student_name" VARCHAR(255) NOT NULL,
    "student_birthdate" DATE,
    "student_address" TEXT,
    "student_email" VARCHAR(255),
    "student_phone" VARCHAR(50),

    -- Course Details
    "course_name" VARCHAR(255),
    "certification_level" VARCHAR(100),
    "course_start_date" DATE,
    "course_completion_date" DATE,

    -- Certification Issued
    "certification_number" VARCHAR(50) COMMENT 'PADI certification number',
    "certification_issue_date" DATE,
    "ecard_issued" BOOLEAN DEFAULT FALSE,
    "physical_card_issued" BOOLEAN DEFAULT FALSE,
    "temp_card_issued" BOOLEAN DEFAULT FALSE,

    -- Training Performance
    "knowledge_development_completed" BOOLEAN DEFAULT FALSE,
    "confined_water_completed" BOOLEAN DEFAULT FALSE,
    "open_water_completed" BOOLEAN DEFAULT FALSE,
    "overall_performance" ENUM('excellent', 'good', 'adequate', 'needs_improvement'),
    "instructor_recommendations" TEXT,

    -- Instructor Information
    "instructor_id" INT UNSIGNED,
    "instructor_name" VARCHAR(255),
    "instructor_number" VARCHAR(50) COMMENT 'PADI instructor number',
    "instructor_signature_path" VARCHAR(255),
    "instructor_signature_date" DATE,

    -- Dive Center Information
    "dive_center_name" VARCHAR(255),
    "dive_center_number" VARCHAR(50) COMMENT 'PADI store number',
    "dive_center_location" VARCHAR(255),

    -- Submitted to PADI
    "submitted_to_padi" BOOLEAN DEFAULT FALSE,
    "padi_submission_date" DATE,
    "padi_confirmation_number" VARCHAR(100),

    -- Document
    "pdf_path" VARCHAR(255),
    "pdf_generated_at" TIMESTAMP NULL,

    -- Timestamps
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("course_id") REFERENCES "courses"("id") ON DELETE CASCADE,
    FOREIGN KEY ("enrollment_id") REFERENCES "course_enrollments"("id") ON DELETE SET NULL,
    FOREIGN KEY ("student_record_id") REFERENCES "course_student_records"("id") ON DELETE SET NULL,
    FOREIGN KEY ("instructor_id") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_customer" ("customer_id"),
    INDEX "idx_course" ("course_id"),
    INDEX "idx_cert_number" ("certification_number"),
    INDEX "idx_instructor" ("instructor_id"),
    INDEX "idx_completion_date" ("course_completion_date")
);

-- Incident reports (PADI Form 10120)
CREATE TABLE IF NOT EXISTS "incident_reports" (
    "id" SERIAL PRIMARY KEY,
    "incident_number" VARCHAR(50) UNIQUE NOT NULL COMMENT 'Unique incident identifier',

    -- Incident Basic Information
    "incident_date" DATE NOT NULL,
    "incident_time" TIME,
    "incident_location" VARCHAR(255) NOT NULL,
    "incident_site_name" VARCHAR(255),
    "incident_gps_coordinates" VARCHAR(100),

    -- Incident Type
    "incident_type" ENUM(
        'injury',
        'illness',
        'equipment_failure',
        'near_miss',
        'decompression_illness',
        'drowning',
        'lost_diver',
        'boat_accident',
        'marine_life_injury',
        'other'
    ) NOT NULL,
    "incident_severity" ENUM('minor', 'moderate', 'serious', 'critical', 'fatal'),

    -- People Involved
    "primary_person_type" ENUM('student', 'certified_diver', 'instructor', 'staff', 'other'),
    "customer_id" INTEGER COMMENT 'If customer involved',
    "person_name" VARCHAR(255),
    "person_age" INT,
    "person_certification_level" VARCHAR(100),
    "person_certification_number" VARCHAR(50),

    -- Dive Details (if applicable)
    "dive_number_of_day" INT,
    "max_depth" DECIMAL(6,2),
    "dive_time" INT COMMENT 'Minutes',
    "water_temperature" DECIMAL(4,1),
    "visibility" VARCHAR(100),
    "current_conditions" VARCHAR(100),

    -- Equipment Information
    "equipment_involved" TEXT,
    "equipment_failure_description" TEXT,

    -- Incident Description
    "incident_description" TEXT NOT NULL,
    "immediate_actions_taken" TEXT,
    "emergency_services_called" BOOLEAN DEFAULT FALSE,
    "emergency_service_details" TEXT,

    -- Medical Response
    "first_aid_provided" BOOLEAN DEFAULT FALSE,
    "first_aid_details" TEXT,
    "oxygen_administered" BOOLEAN DEFAULT FALSE,
    "hospital_transport" BOOLEAN DEFAULT FALSE,
    "hospital_name" VARCHAR(255),
    "medical_outcome" TEXT,

    -- Witnesses
    "witnesses" JSON COMMENT 'Array of witness information',
    "witness_statements" TEXT,

    -- Staff/Instructor Involved
    "instructor_id" INT UNSIGNED,
    "instructor_name" VARCHAR(255),
    "instructor_number" VARCHAR(50),
    "staff_members_present" TEXT,

    -- Course/Activity Information
    "course_id" INT UNSIGNED,
    "activity_type" VARCHAR(100) COMMENT 'training, fun dive, trip, etc.',

    -- Report Details
    "reported_by" INTEGER NOT NULL,
    "reported_by_name" VARCHAR(255),
    "reported_by_role" VARCHAR(100),
    "report_date" DATE NOT NULL,

    -- Follow-up
    "follow_up_required" BOOLEAN DEFAULT FALSE,
    "follow_up_notes" TEXT,
    "follow_up_completed" BOOLEAN DEFAULT FALSE,
    "follow_up_completed_date" DATE,

    -- Insurance/Legal
    "insurance_notified" BOOLEAN DEFAULT FALSE,
    "insurance_claim_number" VARCHAR(100),
    "legal_action_potential" BOOLEAN DEFAULT FALSE,

    -- PADI Reporting
    "reported_to_padi" BOOLEAN DEFAULT FALSE,
    "padi_report_date" DATE,
    "padi_case_number" VARCHAR(100),

    -- Documents
    "photos" JSON COMMENT 'Array of photo paths',
    "attachments" JSON COMMENT 'Array of attachment paths',
    "pdf_path" VARCHAR(255),

    -- Status
    "status" ENUM('draft', 'submitted', 'under_review', 'closed') DEFAULT 'draft',
    "reviewed_by" INT UNSIGNED,
    "reviewed_at" TIMESTAMP NULL,

    -- Timestamps
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("instructor_id") REFERENCES "users"("id") ON DELETE SET NULL,
    FOREIGN KEY ("course_id") REFERENCES "courses"("id") ON DELETE SET NULL,
    FOREIGN KEY ("reported_by") REFERENCES "users"("id") ON DELETE RESTRICT,
    FOREIGN KEY ("reviewed_by") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_incident_date" ("incident_date"),
    INDEX "idx_incident_type" ("incident_type"),
    INDEX "idx_severity" ("incident_severity"),
    INDEX "idx_customer" ("customer_id"),
    INDEX "idx_status" ("status"),
    INDEX "idx_padi_reported" ("reported_to_padi")
);

-- Pre-dive safety checks (PADI Form 752DT - BWRAF)
CREATE TABLE IF NOT EXISTS "predive_safety_checks" (
    "id" SERIAL PRIMARY KEY,
    "dive_date" DATE NOT NULL,
    "dive_time" TIME,
    "dive_site" VARCHAR(255),

    -- Diver Information
    "customer_id" INT UNSIGNED,
    "diver_name" VARCHAR(255) NOT NULL,
    "buddy_name" VARCHAR(255),

    -- BWRAF Checklist
    "bcd_check" BOOLEAN DEFAULT FALSE COMMENT 'B - BCD',
    "weights_check" BOOLEAN DEFAULT FALSE COMMENT 'W - Weights',
    "releases_check" BOOLEAN DEFAULT FALSE COMMENT 'R - Releases',
    "air_check" BOOLEAN DEFAULT FALSE COMMENT 'A - Air',
    "final_check" BOOLEAN DEFAULT FALSE COMMENT 'F - Final OK',

    -- Detailed Checks
    "bcd_inflates" BOOLEAN DEFAULT FALSE,
    "bcd_deflates" BOOLEAN DEFAULT FALSE,
    "low_pressure_inflator_works" BOOLEAN DEFAULT FALSE,
    "weights_secure" BOOLEAN DEFAULT FALSE,
    "weight_quick_release_works" BOOLEAN DEFAULT FALSE,
    "all_releases_located" BOOLEAN DEFAULT FALSE,
    "tank_pressure" INT COMMENT 'PSI or BAR',
    "air_on" BOOLEAN DEFAULT FALSE,
    "regulator_breathes_ok" BOOLEAN DEFAULT FALSE,
    "alternate_air_works" BOOLEAN DEFAULT FALSE,
    "gauges_working" BOOLEAN DEFAULT FALSE,
    "computer_on" BOOLEAN DEFAULT FALSE,
    "mask_defog" BOOLEAN DEFAULT FALSE,
    "fins_secure" BOOLEAN DEFAULT FALSE,

    -- Environmental
    "water_temperature" DECIMAL(4,1),
    "visibility" VARCHAR(100),
    "current" VARCHAR(100),

    -- Course/Activity
    "course_id" INT UNSIGNED,
    "activity_type" VARCHAR(100),
    "instructor_id" INT UNSIGNED,

    -- Verification
    "checked_by" INTEGER COMMENT 'Instructor or buddy who verified',
    "checked_by_name" VARCHAR(255),
    "all_checks_passed" BOOLEAN DEFAULT FALSE,
    "notes" TEXT,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("course_id") REFERENCES "courses"("id") ON DELETE SET NULL,
    FOREIGN KEY ("instructor_id") REFERENCES "users"("id") ON DELETE SET NULL,
    FOREIGN KEY ("checked_by") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_dive_date" ("dive_date"),
    INDEX "idx_customer" ("customer_id"),
    INDEX "idx_instructor" ("instructor_id")
);
