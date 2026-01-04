-- ================================================
-- Nautilus - PADI Medical Forms Table (Controller Compatible)
-- Migration: 073_create_padi_medical_forms_table.sql
-- Description: Creates padi_medical_forms table to match MedicalFormController
-- ================================================

-- PADI Medical Forms (matching controller expectations)
CREATE TABLE IF NOT EXISTS "padi_medical_forms" (
    "id" SERIAL PRIMARY KEY,
    "customer_id" INTEGER NOT NULL,

    -- All 34 PADI Medical Questions (YES/NO)
    "q1_asthma" ENUM('yes', 'no') DEFAULT 'no',
    "q2_heart_disease" ENUM('yes', 'no') DEFAULT 'no',
    "q3_ear_problems" ENUM('yes', 'no') DEFAULT 'no',
    "q4_sinus_problems" ENUM('yes', 'no') DEFAULT 'no',
    "q5_diabetes" ENUM('yes', 'no') DEFAULT 'no',
    "q6_epilepsy" ENUM('yes', 'no') DEFAULT 'no',
    "q7_chest_pain" ENUM('yes', 'no') DEFAULT 'no',
    "q8_behavioral_health" ENUM('yes', 'no') DEFAULT 'no',
    "q9_head_injury" ENUM('yes', 'no') DEFAULT 'no',
    "q10_high_blood_pressure" ENUM('yes', 'no') DEFAULT 'no',
    "q11_lung_disease" ENUM('yes', 'no') DEFAULT 'no',
    "q12_nervous_system" ENUM('yes', 'no') DEFAULT 'no',
    "q13_back_problems" ENUM('yes', 'no') DEFAULT 'no',
    "q14_hernia" ENUM('yes', 'no') DEFAULT 'no',
    "q15_ulcers" ENUM('yes', 'no') DEFAULT 'no',
    "q16_pregnancy" ENUM('yes', 'no') DEFAULT 'no',
    "q17_smoking" ENUM('yes', 'no') DEFAULT 'no',
    "q18_age_over_45" ENUM('yes', 'no') DEFAULT 'no',
    "q19_difficulty_exercising" ENUM('yes', 'no') DEFAULT 'no',
    "q20_heart_surgery" ENUM('yes', 'no') DEFAULT 'no',
    "q21_dive_accident" ENUM('yes', 'no') DEFAULT 'no',
    "q22_medications" ENUM('yes', 'no') DEFAULT 'no',
    "q23_respiratory_problems" ENUM('yes', 'no') DEFAULT 'no',
    "q24_behavioral_meds" ENUM('yes', 'no') DEFAULT 'no',
    "q25_motion_sickness" ENUM('yes', 'no') DEFAULT 'no',
    "q26_dysentery" ENUM('yes', 'no') DEFAULT 'no',
    "q27_dehydration" ENUM('yes', 'no') DEFAULT 'no',
    "q28_family_history" ENUM('yes', 'no') DEFAULT 'no',
    "q29_fainting" ENUM('yes', 'no') DEFAULT 'no',
    "q30_insomnia" ENUM('yes', 'no') DEFAULT 'no',
    "q31_menstrual_problems" ENUM('yes', 'no') DEFAULT 'no',
    "q32_surgery_recent" ENUM('yes', 'no') DEFAULT 'no',
    "q33_blood_disorders" ENUM('yes', 'no') DEFAULT 'no',
    "q34_other_conditions" ENUM('yes', 'no') DEFAULT 'no',

    -- Clearance Requirements
    "requires_physician_clearance" BOOLEAN DEFAULT FALSE,
    "physician_clearance_obtained" BOOLEAN DEFAULT FALSE,
    "physician_clearance_file" VARCHAR(255) NULL,
    "physician_clearance_date" TIMESTAMP NULL,

    -- Signatures
    "participant_signature_data" LONGTEXT NULL COMMENT 'Base64 encoded signature image',
    "participant_signature_date" TIMESTAMP NULL,

    -- Submission Info
    "submitted_by_user_id" INTEGER NULL,
    "submitted_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Validity
    "expiry_date" DATE NOT NULL COMMENT 'Typically 1 year from submission',

    -- PDF Generation
    "pdf_generated" BOOLEAN DEFAULT FALSE,
    "pdf_file_path" VARCHAR(255) NULL,
    "pdf_generated_at" TIMESTAMP NULL,

    -- Timestamps
    "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY ("customer_id") REFERENCES "customers"("id") ON DELETE CASCADE,
    FOREIGN KEY ("submitted_by_user_id") REFERENCES "users"("id") ON DELETE SET NULL,

    INDEX "idx_customer" ("customer_id"),
    INDEX "idx_expiry" ("expiry_date"),
    INDEX "idx_clearance" ("requires_physician_clearance", "physician_clearance_obtained"),
    INDEX "idx_submitted" ("submitted_at")
);
