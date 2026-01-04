-- ==========================================
-- Migration: Create Waivers System
-- Description: Digital waiver system for rentals, repairs, and air fills
-- ==========================================

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `waiver_templates`;
DROP TABLE IF EXISTS `signed_waivers`;
DROP TABLE IF EXISTS `waiver_requirements`;
DROP TABLE IF EXISTS `waiver_email_queue`;

-- Waiver Templates
CREATE TABLE IF NOT EXISTS waiver_templates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('rental', 'repair', 'air_fill', 'general', 'training', 'trip') NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    legal_text TEXT NOT NULL,
    requires_signature BOOLEAN DEFAULT TRUE,
    requires_witness BOOLEAN DEFAULT FALSE,
    requires_emergency_contact BOOLEAN DEFAULT TRUE,
    requires_medical_info BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    version INT DEFAULT 1,
    effective_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,
    INDEX idx_type (type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Signed Waivers
CREATE TABLE IF NOT EXISTS signed_waivers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    waiver_template_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,

    -- Reference Information
    reference_type ENUM('rental', 'repair', 'air_fill', 'course', 'trip', 'general') NOT NULL,
    reference_id BIGINT UNSIGNED NULL COMMENT 'ID of rental, work order, air fill, etc.',

    -- Signature Information
    signature_data TEXT NOT NULL COMMENT 'Base64 encoded signature image',
    signature_ip VARCHAR(45),
    signature_user_agent VARCHAR(255),
    signed_at TIMESTAMP NOT NULL,

    -- Witness Information (if required)
    witness_name VARCHAR(100),
    witness_signature_data TEXT,
    witness_signed_at TIMESTAMP NULL,

    -- Emergency Contact
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),

    -- Medical Information
    has_medical_conditions BOOLEAN DEFAULT FALSE,
    medical_conditions TEXT,
    medications TEXT,
    allergies TEXT,

    -- Additional Information
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    customer_dob DATE,

    -- Document Management
    pdf_path VARCHAR(255) COMMENT 'Path to generated PDF',
    email_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,

    -- Status
    status ENUM('pending', 'signed', 'expired', 'voided') DEFAULT 'signed',
    valid_until DATE NULL,
    voided_at TIMESTAMP NULL,
    voided_by BIGINT UNSIGNED NULL,
    void_reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (waiver_template_id) REFERENCES waiver_templates(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_signed_at (signed_at),
    INDEX idx_status (status),
    INDEX idx_valid_until (valid_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waiver Requirements (auto-send rules)
CREATE TABLE IF NOT EXISTS waiver_requirements (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    waiver_template_id BIGINT UNSIGNED NOT NULL,
    service_type ENUM('rental', 'repair', 'air_fill', 'course', 'trip') NOT NULL,
    auto_send BOOLEAN DEFAULT TRUE,
    send_method ENUM('email', 'sms', 'both') DEFAULT 'email',
    reminder_days INT DEFAULT 0 COMMENT 'Days before service to send reminder',
    is_required BOOLEAN DEFAULT TRUE,
    grace_period_days INT DEFAULT 0 COMMENT 'Days after expiration before requiring new waiver',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (waiver_template_id) REFERENCES waiver_templates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_service_type (service_type),
    INDEX idx_service_type (service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Waiver Email Queue
CREATE TABLE IF NOT EXISTS waiver_email_queue (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    waiver_template_id BIGINT UNSIGNED NOT NULL,
    reference_type ENUM('rental', 'repair', 'air_fill', 'course', 'trip') NOT NULL,
    reference_id BIGINT UNSIGNED,

    email_to VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT,

    unique_token VARCHAR(64) NOT NULL COMMENT 'Unique token for waiver signing URL',
    waiver_url VARCHAR(500),

    status ENUM('pending', 'sent', 'failed', 'signed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    signed_at TIMESTAMP NULL,
    error_message TEXT,
    attempts INT DEFAULT 0,

    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (waiver_template_id) REFERENCES waiver_templates(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_token (unique_token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Waiver Templates

-- Rental Equipment Waiver
INSERT INTO waiver_templates (name, type, title, content, legal_text, requires_signature, requires_witness, requires_emergency_contact, requires_medical_info, is_active, version, effective_date) VALUES
('Standard Rental Waiver', 'rental', 'Dive Equipment Rental Agreement & Liability Waiver',
'I understand that scuba diving and the use of scuba equipment involves inherent risks, including but not limited to decompression sickness, embolism, oxygen toxicity, and other injuries that can result in serious injury or death.

I certify that:
1. I am a certified diver in good standing with a recognized certification agency
2. I am physically and mentally fit to dive
3. I am familiar with the operation of the equipment being rented
4. I will inspect all equipment before use and will not use equipment I deem unsafe
5. I will dive within the limits of my training and experience
6. I will not dive while under the influence of alcohol or drugs

I agree to:
- Return all equipment in the same condition as received (normal wear excepted)
- Pay for any lost, stolen, or damaged equipment at replacement cost
- Notify the dive shop immediately of any equipment malfunction or damage
- Follow all manufacturer guidelines and safe diving practices',

'RELEASE OF LIABILITY: I hereby release, waive, discharge and covenant not to sue Nautilus Dive Shop, its owners, employees, agents, and affiliated organizations from any and all liability, claims, demands, actions and causes of action whatsoever arising out of or related to any loss, damage, or injury, including death, that may be sustained by me while using rented equipment or participating in diving activities.

I understand that this waiver is intended to be as broad and inclusive as permitted by law. If any portion is held invalid, the remainder shall continue in full legal force and effect.

INDEMNIFICATION: I agree to indemnify and hold harmless Nautilus Dive Shop from any loss, liability, damage, or costs that may occur due to my use of rented equipment.

EQUIPMENT CONDITION: I acknowledge that I have inspected the equipment and accept it in good working condition. I understand I am responsible for the equipment until it is returned and checked in.

I HAVE READ THIS WAIVER AND RELEASE, UNDERSTAND IT, AND SIGN IT VOLUNTARILY.',
TRUE, FALSE, TRUE, TRUE, TRUE, 1, CURDATE());

-- Equipment Repair/Service Waiver
INSERT INTO waiver_templates (name, type, title, content, legal_text, requires_signature, requires_witness, requires_emergency_contact, requires_medical_info, is_active, version, effective_date) VALUES
('Equipment Service Waiver', 'repair', 'Equipment Repair & Service Agreement',
'I am submitting my dive equipment for inspection, service, or repair to Nautilus Dive Shop.

I understand and agree that:
1. All work is performed by qualified technicians following manufacturer guidelines
2. Service and repair estimates are approximations and final costs may vary
3. Equipment must be claimed within 30 days of completion or storage fees may apply
4. The dive shop is not responsible for equipment left longer than 90 days
5. I will inspect and test all equipment after service before using it for diving

Equipment Description:
I am submitting the following equipment for service/repair and acknowledge its current condition.',

'LIMITATION OF LIABILITY: While Nautilus Dive Shop uses reasonable care in servicing equipment, I understand that:
- No guarantee is made that serviced equipment will prevent diving accidents or injuries
- The dive shop is not liable for equipment failure during use
- I am responsible for inspecting and testing equipment before each use
- I release the dive shop from any claims arising from equipment use after service

AUTHORIZATION: I authorize Nautilus Dive Shop to:
- Inspect, test, and repair my equipment as necessary
- Contact me if repairs exceed the estimated cost by more than 20%
- Dispose of equipment left unclaimed for more than 90 days after notification

PAYMENT: I agree to pay for all authorized services upon completion and before equipment is returned to me.

I HAVE READ AND UNDERSTAND THIS AGREEMENT.',
TRUE, FALSE, FALSE, FALSE, TRUE, 1, CURDATE());

-- Air Fill Waiver
INSERT INTO waiver_templates (name, type, title, content, legal_text, requires_signature, requires_witness, requires_emergency_contact, requires_medical_info, is_active, version, effective_date) VALUES
('Air Fill Service Waiver', 'air_fill', 'Compressed Gas Fill Service Agreement',
'I am requesting compressed gas fill services for my scuba cylinder(s).

I certify that:
1. I am a certified diver trained in the use of compressed gas
2. My cylinder(s) are within the current hydrostatic test date (visible on cylinder)
3. My cylinder(s) have passed visual inspection within the past 12 months
4. The cylinder(s) are free from damage, corrosion, or defects
5. I understand the gas mixture being provided and am qualified to use it

I understand that:
- The dive shop reserves the right to refuse service to any cylinder not meeting safety standards
- Cylinders must have current hydrostatic and visual inspection certifications
- I am responsible for verifying the gas analysis before use
- I will use the filled cylinder(s) only for diving within my certification limits',

'RELEASE OF LIABILITY: I release Nautilus Dive Shop, its owners, and employees from any liability for injury, death, or property damage arising from:
- The use of compressed gas provided by the dive shop
- My use of filled cylinders for diving activities
- Any equipment failure or gas-related incidents

CYLINDER OWNERSHIP AND CONDITION: I certify that I am the owner or authorized user of the cylinder(s) being filled and that they meet all applicable DOT, CGA, and industry safety standards.

ASSUMPTION OF RISK: I understand that diving with compressed gas involves inherent risks and I assume all risks associated with my diving activities.

HOLD HARMLESS: I agree to indemnify and hold harmless Nautilus Dive Shop from any claims, damages, or expenses arising from my use of compressed gas or cylinders.

I HAVE READ THIS WAIVER, UNDERSTAND IT, AND SIGN IT VOLUNTARILY.',
TRUE, FALSE, FALSE, FALSE, TRUE, 1, CURDATE());

-- Training Course Waiver
INSERT INTO waiver_templates (name, type, title, content, legal_text, requires_signature, requires_witness, requires_emergency_contact, requires_medical_info, is_active, version, effective_date) VALUES
('Scuba Training Waiver', 'training', 'Scuba Diving Training Liability Release and Assumption of Risk',
'I am enrolling in scuba diving training provided by Nautilus Dive Shop.

MEDICAL ACKNOWLEDGMENT:
I confirm that I have completed a medical questionnaire and have been cleared for diving by a physician (if required). I understand that certain medical conditions can pose risks while diving.

TRAINING ACKNOWLEDGMENT:
I understand that scuba diving training involves:
- Classroom instruction
- Confined water (pool) training
- Open water training dives
- Physical exertion and stress
- Use of specialized equipment

I agree to:
- Attend all required sessions
- Complete all academic requirements
- Follow all instructor directions and safety procedures
- Not dive beyond my level of training
- Continue my education to improve my skills',

'ASSUMPTION OF RISK: I acknowledge that skin and scuba diving have inherent risks including but not limited to: decompression sickness, embolism, oxygen toxicity, nitrogen narcosis, hypoxia, hypercapnia, drowning, marine life injuries, equipment failure, boat accidents, and other injuries that can result in permanent disability or death.

RELEASE OF LIABILITY: I hereby release, waive, discharge Nautilus Dive Shop, its instructors, employees, agents, and affiliated organizations from any and all liability, claims, demands arising from my participation in diving training.

MEDICAL: I affirm that I am in good mental and physical fitness for diving and have no conditions that would prevent safe participation. I will immediately inform my instructor of any changes to my medical condition.

INSURANCE: I understand that I am responsible for my own medical, travel, and dive insurance.

STANDARDS: I understand that training will be conducted according to recognized agency standards and I must meet all performance requirements to receive certification.

I HAVE CAREFULLY READ THIS AGREEMENT AND FULLY UNDERSTAND ITS CONTENTS. I AM AWARE THAT THIS IS A RELEASE OF LIABILITY AND I SIGN IT OF MY OWN FREE WILL.',
TRUE, TRUE, TRUE, TRUE, TRUE, 1, CURDATE());

-- Insert Default Waiver Requirements
INSERT INTO waiver_requirements (waiver_template_id, service_type, auto_send, send_method, reminder_days, is_required, grace_period_days) VALUES
(1, 'rental', TRUE, 'email', 1, TRUE, 30),
(2, 'repair', TRUE, 'email', 0, TRUE, 0),
(3, 'air_fill', TRUE, 'email', 0, TRUE, 90),
(4, 'course', TRUE, 'email', 3, TRUE, 0);

-- Indexes already created in table definition above

-- Comments
ALTER TABLE waiver_templates COMMENT = 'Waiver document templates for different services';
ALTER TABLE signed_waivers COMMENT = 'Digital signatures and completed waivers';
ALTER TABLE waiver_requirements COMMENT = 'Auto-send rules for different service types';
ALTER TABLE waiver_email_queue COMMENT = 'Queue for sending waiver signature requests';

SET FOREIGN_KEY_CHECKS=1;
