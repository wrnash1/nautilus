-- Club Membership System Database Schema

-- Membership Tiers Table
CREATE TABLE IF NOT EXISTS membership_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_months INT NOT NULL DEFAULT 12,
    benefits TEXT,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    max_rentals_per_month INT DEFAULT NULL,
    priority_booking BOOLEAN DEFAULT FALSE,
    free_air_fills INT DEFAULT 0,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customer Memberships Table
CREATE TABLE IF NOT EXISTS customer_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    membership_tier_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'pending',
    auto_renew BOOLEAN DEFAULT FALSE,
    payment_method VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (membership_tier_id) REFERENCES membership_tiers(id) ON DELETE RESTRICT,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Membership Benefits Usage Tracking
CREATE TABLE IF NOT EXISTS membership_benefits_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_membership_id INT NOT NULL,
    benefit_type ENUM('rental', 'air_fill', 'discount', 'course') NOT NULL,
    used_date DATE NOT NULL,
    reference_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_membership_id) REFERENCES customer_memberships(id) ON DELETE CASCADE,
    INDEX idx_membership (customer_membership_id),
    INDEX idx_date (used_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
