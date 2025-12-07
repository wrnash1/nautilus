-- ==========================================
-- Migration: Create Course Requirements System
-- Description: Track student requirements for courses (waivers, e-learning, photos, etc.)
-- ==========================================

-- Course Requirement Types
CREATE TABLE IF NOT EXISTS course_requirement_types (
    id INTEGER PRIMARY KEY ,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    requirement_type ENUM('waiver', 'elearning', 'photo', 'medical', 'certification', 'document', 'other') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_type (requirement_type)
);

-- Course Requirements (what each course needs)
CREATE TABLE IF NOT EXISTS course_requirements (
    id INTEGER PRIMARY KEY ,
    course_id INTEGER NOT NULL,
    requirement_type_id INTEGER NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    due_before_start_days INT DEFAULT 0 COMMENT 'Days before course start that this must be completed',
    auto_send_reminder BOOLEAN DEFAULT TRUE,
    reminder_days_before INT DEFAULT 7,
    instructions TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_type_id) REFERENCES course_requirement_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_requirement (course_id, requirement_type_id),
    INDEX idx_course (course_id)
);

-- Student Requirement Status (tracking individual student progress)
CREATE TABLE IF NOT EXISTS enrollment_requirements (
    id INTEGER PRIMARY KEY ,
    enrollment_id INTEGER NOT NULL,
    requirement_type_id INTEGER NOT NULL,

    -- Status
    status ENUM('pending', 'in_progress', 'completed', 'waived', 'expired') DEFAULT 'pending',
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,

    -- Data storage for different requirement types
    waiver_id INTEGER NULL COMMENT 'Reference to signed_waivers table',
    elearning_completion_date DATE NULL,
    elearning_certificate_url VARCHAR(500) NULL,
    photo_path VARCHAR(500) NULL,
    document_path VARCHAR(500) NULL,
    certification_id INTEGER NULL,
    notes TEXT,

    -- Reminders
    reminder_sent BOOLEAN DEFAULT FALSE,
    reminder_sent_at TIMESTAMP NULL,
    last_reminder_at TIMESTAMP NULL,
    reminder_count INT DEFAULT 0,

    -- Verification
    verified_by INTEGER NULL COMMENT 'Staff user who verified',
    verified_at TIMESTAMP NULL,

    -- Waiver (if waived)
    waived_by INTEGER NULL,
    waived_at TIMESTAMP NULL,
    waiver_reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (requirement_type_id) REFERENCES course_requirement_types(id) ON DELETE CASCADE,
    FOREIGN KEY (waiver_id) REFERENCES signed_waivers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_enrollment_requirement (enrollment_id, requirement_type_id),
    INDEX idx_enrollment (enrollment_id),
    INDEX idx_status (status),
    INDEX idx_completed (is_completed)
);

-- E-Learning Modules
CREATE TABLE IF NOT EXISTS elearning_modules (
    id INTEGER PRIMARY KEY ,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    module_code VARCHAR(50) NOT NULL UNIQUE,
    provider VARCHAR(100) COMMENT 'e.g., PADI eLearning, SSI Online',
    external_url VARCHAR(500),
    estimated_hours DECIMAL(4,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (module_code)
);

-- Link courses to required e-learning modules
CREATE TABLE IF NOT EXISTS course_elearning_modules (
    id INTEGER PRIMARY KEY ,
    course_id INTEGER NOT NULL,
    elearning_module_id INTEGER NOT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (elearning_module_id) REFERENCES elearning_modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_module (course_id, elearning_module_id),
    INDEX idx_course (course_id)
);

-- Instructor Notifications
CREATE TABLE IF NOT EXISTS instructor_notifications (
    id INTEGER PRIMARY KEY ,
    instructor_id INTEGER NOT NULL,
    schedule_id INTEGER NOT NULL,
    notification_type ENUM('assignment', 'enrollment', 'requirement_complete', 'course_start_reminder', 'roster_update') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    sent_via_email BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES course_schedules(id) ON DELETE CASCADE,
    INDEX idx_instructor (instructor_id),
    INDEX idx_read (is_read),
    INDEX idx_schedule (schedule_id)
);

-- Insert Default Requirement Types
INSERT INTO course_requirement_types (name, code, description, requirement_type) VALUES
('Liability Waiver', 'waiver_liability', 'Standard liability waiver for scuba training', 'waiver'),
('Medical Questionnaire', 'medical_questionnaire', 'RSTC medical questionnaire and physician approval if needed', 'medical'),
('Student Photo', 'photo_student', 'Photo for certification card', 'photo'),
('E-Learning Completion', 'elearning_completion', 'Online theory portion completion certificate', 'elearning'),
('Previous Certification Card', 'cert_card', 'Copy of prerequisite certification', 'certification'),
('Government ID', 'govt_id', 'Valid government-issued identification', 'document'),
('Passport Photo', 'photo_passport', 'Passport-style photo for international certifications', 'photo'),
('Medical Clearance', 'medical_clearance', 'Physician approval for diving (if required)', 'medical'),
('Equipment Waiver', 'waiver_equipment', 'Equipment rental or personal equipment waiver', 'waiver'),
('Minor Release', 'waiver_minor', 'Parental consent for students under 18', 'waiver');

-- Insert Default E-Learning Modules
INSERT INTO elearning_modules (title, description, module_code, provider, estimated_hours) VALUES
('Open Water Diver Online', 'PADI Open Water Diver eLearning course', 'PADI_OWD', 'PADI eLearning', 12.00),
('Advanced Open Water Online', 'PADI Advanced Open Water eLearning', 'PADI_AOW', 'PADI eLearning', 8.00),
('Rescue Diver Online', 'PADI Rescue Diver eLearning course', 'PADI_RESCUE', 'PADI eLearning', 12.00),
('Enriched Air (Nitrox) Online', 'PADI Enriched Air Diver eLearning', 'PADI_EAN', 'PADI eLearning', 4.00),
('Divemaster Online', 'PADI Divemaster eLearning', 'PADI_DM', 'PADI eLearning', 20.00);

-- Comments
ALTER TABLE course_requirement_types COMMENT = 'Types of requirements students must complete';
ALTER TABLE course_requirements COMMENT = 'Requirements needed for each course';
ALTER TABLE enrollment_requirements COMMENT = 'Individual student requirement completion tracking';
ALTER TABLE elearning_modules COMMENT = 'E-learning modules available';
ALTER TABLE course_elearning_modules COMMENT = 'E-learning modules required for each course';
ALTER TABLE instructor_notifications COMMENT = 'Notifications sent to instructors about their courses';
