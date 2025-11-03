-- ==========================================
-- Migration: Customer Tags and Linking
-- Description: Customer tagging system and customer relationship linking
-- ==========================================

-- Customer tags table
CREATE TABLE IF NOT EXISTS customer_tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#3498db' COMMENT 'Hex color code for badge',
    icon VARCHAR(50) COMMENT 'Bootstrap icon class name',
    description VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active (is_active),
    INDEX idx_slug (slug),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer tag assignments (many-to-many relationship)
CREATE TABLE IF NOT EXISTS customer_tag_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT UNSIGNED,
    notes VARCHAR(255) COMMENT 'Reason for tag assignment',

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES customer_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_customer_tag (customer_id, tag_id),
    INDEX idx_customer (customer_id),
    INDEX idx_tag (tag_id),
    INDEX idx_assigned_at (assigned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer relationships (linking customers together)
CREATE TABLE IF NOT EXISTS customer_relationships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id_1 INT UNSIGNED NOT NULL COMMENT 'Primary customer',
    customer_id_2 INT UNSIGNED NOT NULL COMMENT 'Related customer',
    relationship_type ENUM('family', 'business_partner', 'friend', 'spouse', 'parent', 'child', 'sibling', 'employee', 'employer', 'other') NOT NULL,
    relationship_label VARCHAR(100) COMMENT 'Custom relationship label',
    is_bidirectional BOOLEAN DEFAULT TRUE COMMENT 'If true, relationship applies both ways',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id_1) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id_2) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CHECK (customer_id_1 != customer_id_2),
    UNIQUE KEY unique_relationship (customer_id_1, customer_id_2),
    INDEX idx_customer_1 (customer_id_1),
    INDEX idx_customer_2 (customer_id_2),
    INDEX idx_type (relationship_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer groups (for bulk operations, mailing lists, etc.)
CREATE TABLE IF NOT EXISTS customer_groups (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    group_type ENUM('static', 'dynamic') DEFAULT 'static' COMMENT 'Static: manually added, Dynamic: rule-based',
    rules TEXT COMMENT 'JSON rules for dynamic groups',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active (is_active),
    INDEX idx_slug (slug),
    INDEX idx_type (group_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer group memberships
CREATE TABLE IF NOT EXISTS customer_group_memberships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    group_id INT UNSIGNED NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    added_by INT UNSIGNED,
    notes VARCHAR(255),

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES customer_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (added_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_customer_group (customer_id, group_id),
    INDEX idx_customer (customer_id),
    INDEX idx_group (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer notes (separate from main notes field for better tracking)
CREATE TABLE IF NOT EXISTS customer_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    note_type ENUM('general', 'sales', 'service', 'billing', 'complaint', 'preference', 'medical', 'other') DEFAULT 'general',
    subject VARCHAR(255),
    note_text TEXT NOT NULL,
    is_important BOOLEAN DEFAULT FALSE,
    is_visible_to_customer BOOLEAN DEFAULT FALSE COMMENT 'Show in customer portal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_type (note_type),
    INDEX idx_important (is_important),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer reminders/follow-ups
CREATE TABLE IF NOT EXISTS customer_reminders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    reminder_type ENUM('follow_up', 'appointment', 'payment', 'renewal', 'birthday', 'anniversary', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    reminder_time TIME,
    assigned_to INT UNSIGNED COMMENT 'Staff member responsible',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('pending', 'completed', 'cancelled', 'snoozed') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    completed_by INT UNSIGNED,
    snoozed_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (completed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_reminder_date (reminder_date),
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
