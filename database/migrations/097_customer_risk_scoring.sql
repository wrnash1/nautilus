-- Customer Risk Scoring and Purchase Prediction
-- Migration 097: Customer behavior analytics

-- Customer risk scores table
CREATE TABLE IF NOT EXISTS customer_risk_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    
    -- Return risk metrics
    return_risk_score DECIMAL(5,2) DEFAULT 0 COMMENT 'Risk score 0-100, higher = more likely to return',
    return_count INT DEFAULT 0,
    return_rate DECIMAL(5,4) DEFAULT 0 COMMENT 'Returns / Total purchases',
    last_return_date DATETIME NULL,
    avg_return_value DECIMAL(10,2) DEFAULT 0,
    
    -- Purchase likelihood metrics
    purchase_likelihood_score DECIMAL(5,2) DEFAULT 50 COMMENT 'Score 0-100, higher = more likely to buy',
    purchase_count INT DEFAULT 0,
    avg_order_value DECIMAL(10,2) DEFAULT 0,
    lifetime_value DECIMAL(12,2) DEFAULT 0,
    days_since_last_purchase INT DEFAULT 0,
    purchase_frequency_days DECIMAL(8,2) DEFAULT 0 COMMENT 'Average days between purchases',
    
    -- Engagement metrics
    website_visits INT DEFAULT 0,
    email_opens INT DEFAULT 0,
    email_clicks INT DEFAULT 0,
    courses_completed INT DEFAULT 0,
    certifications_earned INT DEFAULT 0,
    
    -- Risk flags
    is_flagged BOOLEAN DEFAULT FALSE,
    flag_reason VARCHAR(255) NULL,
    flagged_by INT NULL,
    flagged_at DATETIME NULL,
    
    -- AI predictions
    predicted_next_purchase_date DATE NULL,
    predicted_next_purchase_category VARCHAR(100) NULL,
    predicted_churn_risk DECIMAL(5,2) DEFAULT 0 COMMENT '0-100 churn probability',
    
    -- Timestamps
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_customer (customer_id),
    KEY idx_return_risk (return_risk_score DESC),
    KEY idx_purchase_likelihood (purchase_likelihood_score DESC),
    KEY idx_flagged (is_flagged),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customer return history
CREATE TABLE IF NOT EXISTS customer_returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    transaction_id INT NOT NULL,
    return_reason ENUM('customer_request', 'defective', 'wrong_item', 'changed_mind', 'price_match', 'other') DEFAULT 'customer_request',
    return_notes TEXT NULL,
    items_returned INT DEFAULT 1,
    refund_amount DECIMAL(10,2) DEFAULT 0,
    refund_method ENUM('original', 'cash', 'store_credit', 'gift_card', 'exchange') DEFAULT 'original',
    returned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_by INT NULL,
    
    KEY idx_customer (customer_id),
    KEY idx_transaction (transaction_id),
    KEY idx_date (returned_at),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customer behavior tags
CREATE TABLE IF NOT EXISTS customer_behavior_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    tag_value VARCHAR(255) NULL,
    confidence DECIMAL(5,2) DEFAULT 100,
    source ENUM('manual', 'ai', 'rule', 'import') DEFAULT 'rule',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    
    KEY idx_customer (customer_id),
    KEY idx_tag (tag_name),
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default behavior tags
INSERT IGNORE INTO customer_behavior_tags (customer_id, tag_name, tag_value, source) VALUES 
(0, 'HIGH_VALUE', 'VIP customer with high lifetime value', 'rule'),
(0, 'RETURN_RISK', 'Frequently returns items', 'ai'),
(0, 'CHURN_RISK', 'May not return for purchase', 'ai'),
(0, 'LOYAL', 'Regular repeat customer', 'rule'),
(0, 'NEW', 'First-time customer', 'rule'),
(0, 'PRICE_SENSITIVE', 'Often uses coupons/sales', 'ai'),
(0, 'UPSELL_CANDIDATE', 'Likely to purchase upgrades', 'ai'),
(0, 'COURSE_INTERESTED', 'Shows interest in courses', 'ai'),
(0, 'EQUIPMENT_BUYER', 'Primarily buys gear', 'ai'),
(0, 'DIVER_ACTIVE', 'Regular active diver', 'ai');

-- Trigger to update risk scores after transactions
DELIMITER //
CREATE TRIGGER IF NOT EXISTS update_customer_risk_after_transaction
AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    -- Update or insert risk score record
    INSERT INTO customer_risk_scores (customer_id, purchase_count, lifetime_value, calculated_at)
    VALUES (NEW.customer_id, 1, NEW.total, NOW())
    ON DUPLICATE KEY UPDATE 
        purchase_count = purchase_count + 1,
        lifetime_value = lifetime_value + NEW.total,
        days_since_last_purchase = 0,
        calculated_at = NOW();
END//
DELIMITER ;
