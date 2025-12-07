-- ================================================
-- Nautilus - Incident Reporting System
-- Migration: 081_incident_reporting_system.sql
-- Description: PADI Form 10120 compliant incident reporting with mobile support
-- ================================================

-- Incident Reports
CREATE TABLE IF NOT EXISTS "incident_reports" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    -- Incident Identification
    "incident_number" VARCHAR(50) NOT NULL COMMENT 'Auto-generated unique ID',
    "incident_date" DATE NOT NULL,
    "incident_time" TIME NULL,
    "reported_date" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Incident Type
    "incident_type" ENUM(
        'dci_injury', 'dci_fatality', 'non_dci_injury', 'non_dci_fatality',
        'equipment_malfunction', 'near_miss', 'boat_accident', 'other'
    ) NOT NULL,
    "severity" ENUM('minor', 'moderate', 'serious', 'critical', 'fatal') NOT NULL,

    -- Location
    "location_description" TEXT NOT NULL,
    "dive_site_id" INTEGER NULL,
    "country" VARCHAR(100) NULL,
    "gps_latitude" DECIMAL(10, 8) NULL,
    "gps_longitude" DECIMAL(11, 8) NULL,
    "water_depth_feet" INT NULL,

    -- Environmental Conditions
    "water_temp_fahrenheit" INT NULL,
    "visibility_feet" INT NULL,
    "current" ENUM('none', 'light', 'moderate', 'strong', 'very_strong') NULL,
    "sea_state" ENUM('calm', 'light', 'moderate', 'rough', 'very_rough') NULL,
    "weather_conditions" TEXT NULL,

    -- Diver Information
    "diver_customer_id" INTEGER NULL,
    "diver_name" VARCHAR(255) NOT NULL,
    "diver_age" INT NULL,
    "diver_gender" ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL,
    "diver_certification_level" VARCHAR(100) NULL,
    "diver_certification_number" VARCHAR(100) NULL,
    "diver_total_dives" INT NULL,
    "diver_dives_last_12_months" INT NULL,

    -- Dive Profile (if applicable)
    "dive_number_of_day" INT NULL,
    "planned_max_depth_feet" INT NULL,
    "actual_max_depth_feet" INT NULL,
    "planned_bottom_time_minutes" INT NULL,
    "actual_bottom_time_minutes" INT NULL,
    "surface_interval_minutes" INT NULL,
    "gas_mix" VARCHAR(50) NULL,
    "computer_used" VARCHAR(100) NULL,
    "computer_data_available" BOOLEAN DEFAULT FALSE,

    -- Medical Information
    "medical_conditions_known" TEXT NULL,
    "medications_taken" TEXT NULL,
    "medical_clearance_on_file" BOOLEAN DEFAULT FALSE,
    "medical_issues_before_dive" TEXT NULL,

    -- Incident Description
    "incident_description" TEXT NOT NULL,
    "contributing_factors" TEXT NULL,
    "diver_experience_level_adequate" ENUM('yes', 'no', 'unknown') NULL,
    "environmental_factors" TEXT NULL,
    "equipment_factors" TEXT NULL,
    "human_factors" TEXT NULL,

    -- Symptoms/Injuries
    "symptoms_injuries" JSON NULL COMMENT 'Array of symptoms/injuries',
    "injury_type" VARCHAR(255) NULL,
    "body_parts_affected" JSON NULL,
    "symptoms_onset" ENUM('immediate', 'within_minutes', 'within_hours', 'delayed') NULL,

    -- Equipment Involved
    "equipment_involved" JSON NULL COMMENT 'Serial numbers, types, conditions',
    "equipment_failure" BOOLEAN DEFAULT FALSE,
    "equipment_failure_description" TEXT NULL,

    -- Emergency Response
    "first_aid_provided" BOOLEAN DEFAULT FALSE,
    "first_aid_description" TEXT NULL,
    "oxygen_administered" BOOLEAN DEFAULT FALSE,
    "oxygen_start_time" TIME NULL,
    "oxygen_flow_rate" VARCHAR(50) NULL,
    "cpr_performed" BOOLEAN DEFAULT FALSE,
    "aed_used" BOOLEAN DEFAULT FALSE,

    -- Medical Treatment
    "medical_treatment_required" BOOLEAN DEFAULT FALSE,
    "treated_on_site" BOOLEAN DEFAULT FALSE,
    "transported_to_hospital" BOOLEAN DEFAULT FALSE,
    "hospital_name" VARCHAR(255) NULL,
    "hospital_address" TEXT NULL,
    "emergency_contact_notified" BOOLEAN DEFAULT FALSE,
    "chamber_treatment" BOOLEAN DEFAULT FALSE,
    "chamber_location" VARCHAR(255) NULL,
    "chamber_treatment_details" TEXT NULL,

    -- Outcome
    "outcome" ENUM('full_recovery', 'partial_recovery', 'ongoing_treatment', 'permanent_injury', 'fatality') NULL,
    "released_date" DATE NULL,
    "follow_up_required" BOOLEAN DEFAULT FALSE,
    "follow_up_notes" TEXT NULL,

    -- Witnesses
    "witnesses_present" BOOLEAN DEFAULT FALSE,
    "witness_count" INT NULL,

    -- Dive Operator/Instructor
    "trip_id" INTEGER NULL,
    "instructor_id" INTEGER NULL,
    "dive_operator_name" VARCHAR(255) NULL,
    "operator_padi_number" VARCHAR(100) NULL,
    "dive_boat_name" VARCHAR(255) NULL,

    -- Preventive Measures
    "could_incident_be_prevented" ENUM('yes', 'no', 'unknown') NULL,
    "preventive_measures" TEXT NULL,
    "recommendations" TEXT NULL,

    -- Reporting
    "reported_to_padi" BOOLEAN DEFAULT FALSE,
    "padi_report_date" DATE NULL,
    "padi_confirmation_number" VARCHAR(100) NULL,
    "reported_to_authorities" BOOLEAN DEFAULT FALSE,
    "authority_names" TEXT NULL,

    -- Internal Use
    "investigation_completed" BOOLEAN DEFAULT FALSE,
    "investigation_notes" TEXT NULL,
    "corrective_actions_taken" TEXT NULL,
    "reviewed_by_user_id" INTEGER NULL,
    "reviewed_at" TIMESTAMP NULL,

    -- Status
    "status" ENUM('draft', 'submitted', 'under_review', 'completed', 'archived') DEFAULT 'draft',

    -- Reporter
    "reported_by_user_id" INTEGER NULL,
    "reporter_name" VARCHAR(255) NULL,
    "reporter_email" VARCHAR(255) NULL,
    "reporter_phone" VARCHAR(50) NULL,

    -- Attachments
    "attachments" JSON NULL COMMENT 'Photos, medical records, computer data',

    -- Timestamps
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("diver_customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("dive_site_id") REFERENCES "dive_sites"("id") ON DELETE SET NULL,
    FOREIGN KEY ("trip_id") REFERENCES "trips"("id") ON DELETE SET NULL,
    FOREIGN KEY ("instructor_id") REFERENCES "users"("id") ON DELETE SET NULL,
    FOREIGN KEY ("reviewed_by_user_id") REFERENCES "users"("id") ON DELETE SET NULL,
    FOREIGN KEY ("reported_by_user_id") REFERENCES "users"("id") ON DELETE SET NULL,

    UNIQUE KEY "unique_incident_number" ("incident_number"),
    INDEX "idx_incident_date" ("incident_date"),
    INDEX "idx_incident_type" ("incident_type"),
    INDEX "idx_severity" ("severity"),
    INDEX "idx_status" ("status"),
    INDEX "idx_diver" ("diver_customer_id"),
    INDEX "idx_dive_site" ("dive_site_id")
);

-- Incident Witnesses
CREATE TABLE IF NOT EXISTS "incident_witnesses" (
    "id" SERIAL PRIMARY KEY,
    "incident_report_id" INTEGER NOT NULL,

    -- Witness Information
    "witness_type" ENUM('buddy', 'instructor', 'divemaster', 'boat_crew', 'bystander', 'other') NOT NULL,
    "customer_id" INTEGER NULL,
    "user_id" INTEGER NULL,
    "witness_name" VARCHAR(255) NOT NULL,
    "witness_email" VARCHAR(255) NULL,
    "witness_phone" VARCHAR(50) NULL,

    -- Statement
    "statement" TEXT NULL,
    "statement_date" TIMESTAMP NULL,

    -- Signature
    "signature_data" TEXT NULL COMMENT 'Digital signature',
    "signed_at" TIMESTAMP NULL,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("incident_report_id") REFERENCES "incident_reports"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("user_id") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_incident" ("incident_report_id")
);

-- Incident Photos/Evidence
CREATE TABLE IF NOT EXISTS "incident_media" (
    "id" SERIAL PRIMARY KEY,
    "incident_report_id" INTEGER NOT NULL,

    -- Media Details
    "media_type" ENUM('photo', 'video', 'document', 'computer_data', 'audio', 'other') NOT NULL,
    "file_path" VARCHAR(500) NOT NULL,
    "file_name" VARCHAR(255) NOT NULL,
    "file_size_bytes" BIGINT NULL,
    "mime_type" VARCHAR(100) NULL,

    -- Metadata
    "caption" TEXT NULL,
    "taken_at" TIMESTAMP NULL,
    "taken_by" VARCHAR(255) NULL,

    -- GPS from photo EXIF
    "gps_latitude" DECIMAL(10, 8) NULL,
    "gps_longitude" DECIMAL(11, 8) NULL,

    -- Classification
    "is_sensitive" BOOLEAN DEFAULT FALSE,
    "is_evidence" BOOLEAN DEFAULT FALSE,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("incident_report_id") REFERENCES "incident_reports"("id") ON DELETE CASCADE,

    INDEX "idx_incident" ("incident_report_id"),
    INDEX "idx_media_type" ("media_type")
);

-- Incident Follow-up Actions
CREATE TABLE IF NOT EXISTS "incident_follow_ups" (
    "id" SERIAL PRIMARY KEY,
    "incident_report_id" INTEGER NOT NULL,

    -- Action Details
    "action_type" ENUM('medical_checkup', 'equipment_inspection', 'policy_review', 'training', 'investigation', 'legal', 'other') NOT NULL,
    "action_description" TEXT NOT NULL,

    -- Assignment
    "assigned_to_user_id" INTEGER NULL,
    "due_date" DATE NULL,

    -- Status
    "status" ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    "completed_at" TIMESTAMP NULL,
    "completion_notes" TEXT NULL,

    -- Priority
    "priority" ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "created_by_user_id" INTEGER NULL,

    FOREIGN KEY ("incident_report_id") REFERENCES "incident_reports"("id") ON DELETE CASCADE,
    FOREIGN KEY ("assigned_to_user_id") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_incident" ("incident_report_id"),
    INDEX "idx_status" ("status"),
    INDEX "idx_assigned_to" ("assigned_to_user_id")
);

-- Incident Statistics (for safety dashboard)
CREATE TABLE IF NOT EXISTS "incident_statistics" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    -- Time Period
    "year" INT NOT NULL,
    "month" INT NULL,

    -- Counts by Type
    "total_incidents" INT DEFAULT 0,
    "dci_injuries" INT DEFAULT 0,
    "dci_fatalities" INT DEFAULT 0,
    "non_dci_injuries" INT DEFAULT 0,
    "non_dci_fatalities" INT DEFAULT 0,
    "equipment_failures" INT DEFAULT 0,
    "near_misses" INT DEFAULT 0,

    -- Severity Distribution
    "minor_incidents" INT DEFAULT 0,
    "moderate_incidents" INT DEFAULT 0,
    "serious_incidents" INT DEFAULT 0,
    "critical_incidents" INT DEFAULT 0,
    "fatal_incidents" INT DEFAULT 0,

    -- Total Dive Activity (for rate calculation)
    "total_dives_conducted" INT DEFAULT 0,
    "total_students_trained" INT DEFAULT 0,

    -- Safety Metrics
    "incident_rate_per_1000_dives" DECIMAL(10,4) NULL,
    "preventable_incidents_count" INT DEFAULT 0,
    "preventable_percentage" DECIMAL(5,2) NULL,

    "calculated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,

    UNIQUE KEY "unique_period" ("tenant_id", "year", "month"),
    INDEX "idx_year" ("year"),
    INDEX "idx_year_month" ("year", "month")
);
