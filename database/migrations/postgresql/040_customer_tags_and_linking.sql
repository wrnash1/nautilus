-- ==========================================
-- Migration: Customer Tags and Linking
-- Description: Customer tagging system and customer relationship linking
-- ==========================================

-- Customer tags table - already exists from migration 002, adding missing columns
-- Note: customer_tags table created in migration 002 with: id, name, color, created_at
ALTER TABLE customer_tags
ADD COLUMN IF NOT EXISTS slug VARCHAR(50) AFTER name,
ADD COLUMN IF NOT EXISTS icon VARCHAR(50) AFTER color,
ADD COLUMN IF NOT EXISTS description VARCHAR(255) AFTER icon,
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER description,
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0 AFTER is_active,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_at,
ADD COLUMN IF NOT EXISTS created_by INTEGER AFTER updated_at;

-- Add indexes if they don't exist
ALTER TABLE customer_tags ADD INDEX IF NOT EXISTS idx_active (is_active);
ALTER TABLE customer_tags ADD INDEX IF NOT EXISTS idx_slug (slug);
ALTER TABLE customer_tags ADD INDEX IF NOT EXISTS idx_order (display_order);

-- Customer tag assignments - already exists from migration 002, adding missing columns
-- Note: customer_tag_assignments table created in migration 002 with PK (customer_id, tag_id) and assigned_at
ALTER TABLE customer_tag_assignments
ADD COLUMN IF NOT EXISTS assigned_by INTEGER AFTER assigned_at,
ADD COLUMN IF NOT EXISTS notes VARCHAR(255) COMMENT 'Reason for tag assignment' AFTER assigned_by;

-- Customer relationships (linking customers together)
CREATE TABLE IF NOT EXISTS customer_relationships (
    id INTEGER  PRIMARY KEY,
    customer_id_1 INTEGER NOT NULL COMMENT 'Primary customer',
    customer_id_2 INTEGER NOT NULL COMMENT 'Related customer',
    relationship_type ENUM('family', 'business_partner', 'friend', 'spouse', 'parent', 'child', 'sibling', 'employee', 'employer', 'other') NOT NULL,
    relationship_label VARCHAR(100) COMMENT 'Custom relationship label',
    is_bidirectional BOOLEAN DEFAULT TRUE COMMENT 'If true, relationship applies both ways',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id_1) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id_2) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CHECK (customer_id_1 != customer_id_2),
    UNIQUE KEY unique_relationship (customer_id_1, customer_id_2),
    INDEX idx_customer_1 (customer_id_1),
    INDEX idx_customer_2 (customer_id_2),
    INDEX idx_type (relationship_type)
);

-- Customer groups (for bulk operations, mailing lists, etc.)
CREATE TABLE IF NOT EXISTS customer_groups (
    id INTEGER  PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    group_type ENUM('static', 'dynamic') DEFAULT 'static' COMMENT 'Static: manually added, Dynamic: rule-based',
    rules TEXT COMMENT 'JSON rules for dynamic groups',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active (is_active),
    INDEX idx_slug (slug),
    INDEX idx_type (group_type)
);

-- Customer group memberships
CREATE TABLE IF NOT EXISTS customer_group_memberships (
    id INTEGER  PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    group_id INTEGER NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    added_by INT UNSIGNED,
    notes VARCHAR(255),

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES customer_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_customer_group (customer_id, group_id),
    INDEX idx_customer (customer_id),
    INDEX idx_group (group_id)
);

-- Customer notes - already exists from migration 002, adding missing columns
-- Note: customer_notes table created in migration 002 with: id, customer_id, user_id, note_type (different values), content, is_pinned, created_at, updated_at
ALTER TABLE customer_notes
ADD COLUMN IF NOT EXISTS subject VARCHAR(255) AFTER note_type,
ADD COLUMN IF NOT EXISTS note_text TEXT AFTER subject,
ADD COLUMN IF NOT EXISTS is_important BOOLEAN DEFAULT FALSE AFTER note_text,
ADD COLUMN IF NOT EXISTS is_visible_to_customer BOOLEAN DEFAULT FALSE COMMENT 'Show in customer portal' AFTER is_important,
ADD COLUMN IF NOT EXISTS created_by INTEGER AFTER created_at,
ADD COLUMN IF NOT EXISTS updated_by INTEGER AFTER updated_at;

-- Rename content to note_text if it exists (migration 002 calls it 'content')
-- This can't be done with ALTER IF NOT EXISTS, so skip for safety

-- Add indexes
ALTER TABLE customer_notes ADD INDEX IF NOT EXISTS idx_type (note_type);
ALTER TABLE customer_notes ADD INDEX IF NOT EXISTS idx_important (is_important);

-- Customer reminders/follow-ups
CREATE TABLE IF NOT EXISTS customer_reminders (
    id INTEGER  PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    reminder_type ENUM('follow_up', 'appointment', 'payment', 'renewal', 'birthday', 'anniversary', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    reminder_time TIME,
    assigned_to INTEGER COMMENT 'Staff member responsible',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('pending', 'completed', 'cancelled', 'snoozed') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    completed_by INT UNSIGNED,
    snoozed_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER NOT NULL,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_reminder_date (reminder_date),
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_priority (priority)
);

-- Insert default customer tags
INSERT INTO customer_tags (name, slug, color, icon, description, display_order, is_active) VALUES
('VIP', 'vip', '#f39c12', 'bi-star-fill', 'VIP Customer - Highest priority', 1, TRUE),
('Wholesale', 'wholesale', '#3498db', 'bi-briefcase-fill', 'Wholesale Customer - Special pricing', 2, TRUE),
('Instructor', 'instructor', '#2ecc71', 'bi-mortarboard-fill', 'Certified Diving Instructor', 3, TRUE),
('DiveMaster', 'divemaster', '#16a085', 'bi-award-fill', 'Certified DiveMaster', 4, TRUE),
('Regular', 'regular', '#95a5a6', 'bi-person-check-fill', 'Regular Customer', 5, TRUE),
('New', 'new', '#e74c3c', 'bi-person-plus-fill', 'New Customer', 6, TRUE),
('Inactive', 'inactive', '#7f8c8d', 'bi-person-dash', 'Inactive Customer (no activity 12+ months)', 7, TRUE),
('High Value', 'high-value', '#9b59b6', 'bi-gem', 'High Value Customer (lifetime value)', 8, TRUE),
('At Risk', 'at-risk', '#e67e22', 'bi-exclamation-triangle-fill', 'At Risk of Churning', 9, TRUE),
('Payment Issue', 'payment-issue', '#c0392b', 'bi-exclamation-circle-fill', 'Has payment or billing issues', 10, TRUE);

-- Insert default customer groups
INSERT INTO customer_groups (name, slug, description, group_type, is_active) VALUES
('Newsletter Subscribers', 'newsletter', 'Customers who opted in to receive newsletters', 'static', TRUE),
('VIP Members', 'vip-members', 'VIP customers with special privileges', 'static', TRUE),
('Certification Students', 'cert-students', 'Customers currently enrolled in certification courses', 'dynamic', TRUE),
('Frequent Divers', 'frequent-divers', 'Customers who dive regularly (5+ dives per year)', 'dynamic', TRUE),
('Equipment Buyers', 'equipment-buyers', 'Customers who purchase equipment (not just rentals)', 'dynamic', TRUE),
('Trip Participants', 'trip-participants', 'Customers who have participated in dive trips', 'dynamic', TRUE);

-- Comments
ALTER TABLE customer_tags COMMENT = 'Tags for categorizing customers (VIP, Wholesale, etc.)';
ALTER TABLE customer_tag_assignments COMMENT = 'Assignment of tags to customers (many-to-many)';
ALTER TABLE customer_relationships COMMENT = 'Links between related customers (family, business, etc.)';
ALTER TABLE customer_groups COMMENT = 'Customer groups for segmentation and bulk operations';
ALTER TABLE customer_group_memberships COMMENT = 'Membership of customers in groups';
ALTER TABLE customer_notes COMMENT = 'Detailed notes about customers with categories';
ALTER TABLE customer_reminders COMMENT = 'Follow-up reminders and tasks for customers';
