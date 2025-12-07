-- ================================================
-- Nautilus - Pre-Dive Safety Checks (BWRAF)
-- Migration: 075_pre_dive_safety_checks.sql
-- Description: BWRAF+ safety check system for dive operations
-- ================================================

-- Pre-Dive Safety Checks Table
CREATE TABLE IF NOT EXISTS "pre_dive_safety_checks" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    -- Diver & Dive Information
    "customer_id" INTEGER NOT NULL,
    "buddy_customer_id" INTEGER NULL COMMENT 'Dive buddy',
    "dive_site_id" INTEGER NULL,
    "trip_id" INTEGER NULL,

    -- Dive Details
    "dive_type" ENUM('training', 'recreational', 'advanced', 'technical') DEFAULT 'recreational',
    "planned_depth_feet" INT NULL,
    "planned_duration_minutes" INT NULL,
    "dive_number_today" INT DEFAULT 1,

    -- BWRAF Checklist (Standard PADI Pre-Dive Safety Check)
    -- B = BCD
    "bcd_inflator_works" BOOLEAN DEFAULT FALSE,
    "bcd_deflator_works" BOOLEAN DEFAULT FALSE,
    "bcd_overpressure_valve_clear" BOOLEAN DEFAULT FALSE,
    "bcd_straps_secure" BOOLEAN DEFAULT FALSE,
    "bcd_integrated_weights_secure" BOOLEAN DEFAULT FALSE,

    -- W = Weights
    "weights_adequate" BOOLEAN DEFAULT FALSE,
    "weights_secure" BOOLEAN DEFAULT FALSE,
    "weights_releasable" BOOLEAN DEFAULT FALSE,
    "weight_amount_kg" DECIMAL(5,2) NULL,

    -- R = Releases
    "bcd_releases_located" BOOLEAN DEFAULT FALSE,
    "weight_releases_located" BOOLEAN DEFAULT FALSE,
    "all_releases_functional" BOOLEAN DEFAULT FALSE,

    -- A = Air
    "tank_valve_fully_open" BOOLEAN DEFAULT FALSE,
    "air_on_and_breathable" BOOLEAN DEFAULT FALSE,
    "pressure_gauge_working" BOOLEAN DEFAULT FALSE,
    "starting_pressure_psi" INT NULL,
    "air_quality_good" BOOLEAN DEFAULT FALSE,
    "reserve_pressure_adequate" BOOLEAN DEFAULT FALSE COMMENT 'Minimum 500 PSI',

    -- F = Final Check
    "mask_fits_properly" BOOLEAN DEFAULT FALSE,
    "mask_defogged" BOOLEAN DEFAULT FALSE,
    "fins_secure" BOOLEAN DEFAULT FALSE,
    "snorkel_attached" BOOLEAN DEFAULT FALSE,
    "computer_functioning" BOOLEAN DEFAULT FALSE,
    "compass_functioning" BOOLEAN DEFAULT FALSE,
    "knife_accessible" BOOLEAN DEFAULT FALSE,
    "smb_accessible" BOOLEAN DEFAULT FALSE COMMENT 'Surface Marker Buoy',

    -- Additional Safety Items
    "dive_plan_reviewed" BOOLEAN DEFAULT FALSE,
    "hand_signals_reviewed" BOOLEAN DEFAULT FALSE,
    "emergency_procedures_reviewed" BOOLEAN DEFAULT FALSE,
    "entry_exit_points_identified" BOOLEAN DEFAULT FALSE,

    -- Environmental Conditions
    "water_temp_fahrenheit" INT NULL,
    "visibility_feet" INT NULL,
    "current_strength" ENUM('none', 'light', 'moderate', 'strong') NULL,
    "wave_height_feet" DECIMAL(3,1) NULL,
    "weather_conditions" VARCHAR(255) NULL,

    -- Medical & Fitness
    "diver_feels_well" BOOLEAN DEFAULT FALSE,
    "diver_not_fatigued" BOOLEAN DEFAULT FALSE,
    "no_alcohol_24hrs" BOOLEAN DEFAULT FALSE,
    "no_medications_affecting_diving" BOOLEAN DEFAULT FALSE,
    "surface_interval_adequate" BOOLEAN DEFAULT FALSE COMMENT 'If repeat dive',

    -- Equipment Serial Numbers (for tracking)
    "bcd_serial" VARCHAR(100) NULL,
    "regulator_serial" VARCHAR(100) NULL,
    "computer_serial" VARCHAR(100) NULL,
    "tank_serial" VARCHAR(100) NULL,

    -- Overall Status
    "all_checks_passed" BOOLEAN DEFAULT FALSE,
    "issues_noted" TEXT NULL,
    "check_status" ENUM('incomplete', 'passed', 'failed', 'waived') DEFAULT 'incomplete',

    -- Verification
    "checked_by_user_id" INTEGER NULL COMMENT 'Staff/instructor who verified',
    "buddy_confirmed" BOOLEAN DEFAULT FALSE,
    "checked_at" TIMESTAMP NULL,

    -- GPS Location
    "gps_latitude" DECIMAL(10, 8) NULL,
    "gps_longitude" DECIMAL(11, 8) NULL,

    -- Post-Dive Information (filled after dive)
    "actual_max_depth_feet" INT NULL,
    "actual_duration_minutes" INT NULL,
    "ending_pressure_psi" INT NULL,
    "dive_completed_at" TIMESTAMP NULL,
    "post_dive_condition" ENUM('excellent', 'good', 'tired', 'issue') NULL,
    "post_dive_notes" TEXT NULL,

    -- Timestamps
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,
    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("buddy_customer_id") REFERENCES "customers"("id") ON DELETE SET NULL,
    FOREIGN KEY ("dive_site_id") REFERENCES "dive_sites"("id") ON DELETE SET NULL,
    FOREIGN KEY ("trip_id") REFERENCES "trips"("id") ON DELETE SET NULL,
    FOREIGN KEY ("checked_by_user_id") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_customer" ("customer_id"),
    INDEX "idx_dive_site" ("dive_site_id"),
    INDEX "idx_trip" ("trip_id"),
    INDEX "idx_status" ("check_status"),
    INDEX "idx_checked_at" ("checked_at"),
    INDEX "idx_created" ("created_at")
);

-- Safety Check Templates (for different dive types)
CREATE TABLE IF NOT EXISTS "safety_check_templates" (
    "id" SERIAL PRIMARY KEY,
    "tenant_id" INTEGER NULL,

    "template_name" VARCHAR(255) NOT NULL,
    "dive_type" ENUM('training', 'recreational', 'advanced', 'technical') NOT NULL,
    "description" TEXT NULL,

    -- Required Checks (JSON array of check names)
    "required_checks" JSON NOT NULL,

    -- Optional Checks
    "optional_checks" JSON NULL,

    -- Minimum Equipment Requirements
    "min_starting_pressure_psi" INT DEFAULT 2500,
    "min_reserve_pressure_psi" INT DEFAULT 500,
    "requires_computer" BOOLEAN DEFAULT TRUE,
    "requires_smb" BOOLEAN DEFAULT FALSE,
    "requires_compass" BOOLEAN DEFAULT TRUE,

    "is_active" BOOLEAN DEFAULT TRUE,
    "is_default" BOOLEAN DEFAULT FALSE,

    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("tenant_id") REFERENCES "tenants"("id") ON DELETE CASCADE,

    INDEX "idx_dive_type" ("dive_type"),
    INDEX "idx_active" ("is_active")
);

-- Seed default BWRAF template
INSERT INTO "safety_check_templates" ("template_name", "dive_type", "description", "required_checks", "is_default") VALUES
('Standard BWRAF', 'recreational', 'Standard PADI BWRAF pre-dive safety check',
'["bcd_inflator_works", "bcd_deflator_works", "weights_secure", "weights_releasable", "bcd_releases_located", "weight_releases_located", "tank_valve_fully_open", "air_on_and_breathable", "pressure_gauge_working", "mask_fits_properly", "fins_secure", "computer_functioning"]',
TRUE);
