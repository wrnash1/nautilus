-- ==========================================
-- Migration: Customer Travel and Contact Information
-- Description: Add travel fields, multiple addresses, phones, emails, and contacts
-- ==========================================

-- Add travel and medical fields to customers table
-- Note: shoe_size and wetsuit_size already added in migration 033
ALTER TABLE customers
ADD COLUMN IF NOT EXISTS passport_number VARCHAR(50) AFTER notes,
ADD COLUMN IF NOT EXISTS passport_expiration DATE AFTER passport_number,
ADD COLUMN IF NOT EXISTS passport_country VARCHAR(3) AFTER passport_expiration,
ADD COLUMN IF NOT EXISTS weight DECIMAL(5,2) AFTER passport_country,
ADD COLUMN IF NOT EXISTS weight_unit ENUM('lb', 'kg') DEFAULT 'lb' AFTER weight,
ADD COLUMN IF NOT EXISTS height DECIMAL(5,2) AFTER weight_unit,
ADD COLUMN IF NOT EXISTS height_unit ENUM('in', 'cm') DEFAULT 'in' AFTER height,
ADD COLUMN IF NOT EXISTS allergies TEXT AFTER height_unit,
ADD COLUMN IF NOT EXISTS medications TEXT AFTER allergies,
ADD COLUMN IF NOT EXISTS medical_notes TEXT AFTER medications,
ADD COLUMN IF NOT EXISTS shoe_size VARCHAR(20) AFTER medical_notes,
ADD COLUMN IF NOT EXISTS wetsuit_size VARCHAR(20) AFTER shoe_size;

-- Create indexes for travel fields
ALTER TABLE customers ADD INDEX IF NOT EXISTS idx_passport_expiration (passport_expiration);

-- Create multiple addresses table
CREATE TABLE IF NOT EXISTS customer_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    address_type ENUM('billing', 'shipping', 'home', 'work', 'other') NOT NULL DEFAULT 'billing',
    label VARCHAR(100) COMMENT 'Custom label for address',
    is_default BOOLEAN DEFAULT FALSE,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(3) DEFAULT 'US',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_type (address_type),
    INDEX idx_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create multiple phones table
CREATE TABLE IF NOT EXISTS customer_phones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    phone_type ENUM('home', 'work', 'mobile', 'fax', 'other') NOT NULL DEFAULT 'mobile',
    label VARCHAR(100) COMMENT 'Custom label for phone',
    phone_number VARCHAR(20) NOT NULL,
    extension VARCHAR(10),
    is_default BOOLEAN DEFAULT FALSE,
    can_sms BOOLEAN DEFAULT TRUE,
    can_call BOOLEAN DEFAULT TRUE,
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_type (phone_type),
    INDEX idx_default (is_default),
    INDEX idx_phone (phone_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create multiple emails table
CREATE TABLE IF NOT EXISTS customer_emails (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    email_type ENUM('personal', 'work', 'other') NOT NULL DEFAULT 'personal',
    label VARCHAR(100) COMMENT 'Custom label for email',
    email VARCHAR(255) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL,
    can_market BOOLEAN DEFAULT TRUE COMMENT 'Can send marketing emails',
    notes VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_type (email_type),
    INDEX idx_default (is_default),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer contacts table (emergency contacts, etc.)
CREATE TABLE IF NOT EXISTS customer_contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    contact_type ENUM('spouse', 'emergency', 'assistant', 'parent', 'child', 'other') NOT NULL DEFAULT 'emergency',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    relationship VARCHAR(100),
    is_primary_emergency BOOLEAN DEFAULT FALSE,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(3) DEFAULT 'US',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_type (contact_type),
    INDEX idx_primary_emergency (is_primary_emergency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer custom fields table (flexible key-value storage)
CREATE TABLE IF NOT EXISTS customer_custom_fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(100) NOT NULL UNIQUE,
    field_label VARCHAR(150) NOT NULL,
    field_type ENUM('text', 'number', 'date', 'boolean', 'select', 'textarea') DEFAULT 'text',
    field_options TEXT COMMENT 'JSON array of options for select type',
    is_required BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create customer custom field values table
CREATE TABLE IF NOT EXISTS customer_custom_field_values (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    field_id INT UNSIGNED NOT NULL,
    field_value TEXT,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES customer_custom_fields(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_field (customer_id, field_id),
    INDEX idx_customer (customer_id),
    INDEX idx_field (field_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some default custom fields
INSERT INTO customer_custom_fields (field_name, field_label, field_type, field_options, is_required, display_order) VALUES
('num_employees', 'Number of Employees', 'number', NULL, FALSE, 1),
('owns_truck', 'Owns a Truck', 'boolean', NULL, FALSE, 2),
('preferred_contact_time', 'Preferred Contact Time', 'select', '["Morning","Afternoon","Evening","Anytime"]', FALSE, 3),
('how_did_you_hear', 'How Did You Hear About Us?', 'select', '["Search Engine","Social Media","Friend Referral","Advertisement","Walk-in","Other"]', FALSE, 4),
('dive_experience_level', 'Dive Experience Level', 'select', '["Beginner","Intermediate","Advanced","Professional"]', FALSE, 5),
('preferred_dive_style', 'Preferred Dive Style', 'select', '["Recreational","Technical","Cave","Wreck","Deep","Night","All"]', FALSE, 6);

-- Comments
ALTER TABLE customer_addresses COMMENT = 'Multiple addresses per customer (billing, shipping, home, work)';
ALTER TABLE customer_phones COMMENT = 'Multiple phone numbers per customer';
ALTER TABLE customer_emails COMMENT = 'Multiple email addresses per customer';
ALTER TABLE customer_contacts COMMENT = 'Emergency contacts and other related contacts';
ALTER TABLE customer_custom_fields COMMENT = 'Definition of custom fields for customers';
ALTER TABLE customer_custom_field_values COMMENT = 'Values of custom fields for each customer';

-- Note: Data migration skipped - customers table does not have address columns.
-- Address data should already be in customer_addresses table from migration 002.
-- INSERT INTO customer_addresses ... (migration skipped)

-- Note: Data migration for phone/email/emergency contacts preserved as these columns exist in customers table.
-- Migrate existing customer phone data to customer_phones table (if not already migrated)
INSERT IGNORE INTO customer_phones (customer_id, phone_type, phone_number, is_default, created_at)
SELECT
    id,
    'home' as phone_type,
    phone as phone_number,
    TRUE as is_default,
    created_at
FROM customers
WHERE phone IS NOT NULL AND phone != '';

-- Migrate existing customer mobile data to customer_phones table (if not already migrated)
INSERT IGNORE INTO customer_phones (customer_id, phone_type, phone_number, is_default, created_at)
SELECT
    id,
    'mobile' as phone_type,
    mobile as phone_number,
    FALSE as is_default,
    created_at
FROM customers
WHERE mobile IS NOT NULL AND mobile != '';

-- Migrate existing customer email data to customer_emails table (if not already migrated)
INSERT IGNORE INTO customer_emails (customer_id, email_type, email, is_default, is_verified, created_at)
SELECT
    id,
    'personal' as email_type,
    email as email,
    TRUE as is_default,
    TRUE as is_verified,
    created_at
FROM customers
WHERE email IS NOT NULL AND email != '';

-- Migrate existing emergency contact data to customer_contacts table (if not already migrated)
INSERT IGNORE INTO customer_contacts (customer_id, contact_type, first_name, last_name, phone, is_primary_emergency, created_at)
SELECT
    id,
    'emergency' as contact_type,
    SUBSTRING_INDEX(emergency_contact_name, ' ', 1) as first_name,
    SUBSTRING_INDEX(emergency_contact_name, ' ', -1) as last_name,
    emergency_contact_phone as phone,
    TRUE as is_primary_emergency,
    created_at
FROM customers
WHERE emergency_contact_name IS NOT NULL AND emergency_contact_name != '';
