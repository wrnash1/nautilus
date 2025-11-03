-- ==========================================
-- Migration: Cash Drawer Management
-- Description: Track cash drawers, sessions, and cash transactions
-- ==========================================

-- Cash drawers (physical register drawers)
CREATE TABLE IF NOT EXISTS cash_drawers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) COMMENT 'Physical location (Front Counter, Back Office, etc.)',
    drawer_number VARCHAR(20) COMMENT 'Physical drawer number or identifier',
    current_balance DECIMAL(10,2) DEFAULT 0.00,
    starting_float DECIMAL(10,2) DEFAULT 200.00 COMMENT 'Default starting cash amount',
    is_active BOOLEAN DEFAULT TRUE,
    requires_count_in BOOLEAN DEFAULT TRUE COMMENT 'Require counting cash when opening',
    requires_count_out BOOLEAN DEFAULT TRUE COMMENT 'Require counting cash when closing',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_active (is_active),
    INDEX idx_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cash drawer sessions (shift/day sessions)
CREATE TABLE IF NOT EXISTS cash_drawer_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique session identifier',
    drawer_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL COMMENT 'Staff member operating drawer',

    -- Opening information
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    starting_balance DECIMAL(10,2) NOT NULL COMMENT 'Cash at start of session',
    starting_bills_100 INT DEFAULT 0,
    starting_bills_50 INT DEFAULT 0,
    starting_bills_20 INT DEFAULT 0,
    starting_bills_10 INT DEFAULT 0,
    starting_bills_5 INT DEFAULT 0,
    starting_bills_2 INT DEFAULT 0,
    starting_bills_1 INT DEFAULT 0,
    starting_coins_dollar INT DEFAULT 0,
    starting_coins_quarter INT DEFAULT 0,
    starting_coins_dime INT DEFAULT 0,
    starting_coins_nickel INT DEFAULT 0,
    starting_coins_penny INT DEFAULT 0,
    starting_notes TEXT COMMENT 'Notes when opening drawer',

    -- Closing information
    closed_at TIMESTAMP NULL,
    ending_balance DECIMAL(10,2) NULL COMMENT 'Actual counted cash at end',
    ending_bills_100 INT DEFAULT 0,
    ending_bills_50 INT DEFAULT 0,
    ending_bills_20 INT DEFAULT 0,
    ending_bills_10 INT DEFAULT 0,
    ending_bills_5 INT DEFAULT 0,
    ending_bills_2 INT DEFAULT 0,
    ending_bills_1 INT DEFAULT 0,
    ending_coins_dollar INT DEFAULT 0,
    ending_coins_quarter INT DEFAULT 0,
    ending_coins_dime INT DEFAULT 0,
    ending_coins_nickel INT DEFAULT 0,
    ending_coins_penny INT DEFAULT 0,
    ending_notes TEXT COMMENT 'Notes when closing drawer',

    -- Calculated values
    expected_balance DECIMAL(10,2) NULL COMMENT 'Expected based on transactions',
    total_sales DECIMAL(10,2) DEFAULT 0.00,
    total_refunds DECIMAL(10,2) DEFAULT 0.00,
    total_deposits DECIMAL(10,2) DEFAULT 0.00,
    total_withdrawals DECIMAL(10,2) DEFAULT 0.00,
    difference DECIMAL(10,2) NULL COMMENT 'Difference between expected and actual',
    difference_reason TEXT COMMENT 'Explanation for cash difference',

    -- Status
    status ENUM('open', 'closed', 'balanced', 'over', 'short') DEFAULT 'open',
    closed_by INT UNSIGNED COMMENT 'Staff member who closed session',
    approved_by INT UNSIGNED COMMENT 'Manager who approved closing',
    approved_at TIMESTAMP NULL,

    FOREIGN KEY (drawer_id) REFERENCES cash_drawers(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_drawer (drawer_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_opened (opened_at),
    INDEX idx_closed (closed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cash drawer transactions (detailed transaction log)
CREATE TABLE IF NOT EXISTS cash_drawer_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    transaction_type ENUM('sale', 'return', 'refund', 'deposit', 'withdrawal', 'adjustment', 'payout', 'till_loan', 'till_payback') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'check', 'money_order', 'other') DEFAULT 'cash',

    -- Reference to original transaction
    reference_type VARCHAR(50) COMMENT 'Type of reference (sale, expense, etc.)',
    reference_id INT UNSIGNED COMMENT 'ID of referenced record',

    -- Check information
    check_number VARCHAR(50),
    check_bank VARCHAR(100),
    check_maker VARCHAR(150),

    -- Transaction details
    description VARCHAR(255) NOT NULL,
    notes TEXT,
    requires_approval BOOLEAN DEFAULT FALSE,
    approved_by INT UNSIGNED,
    approved_at TIMESTAMP NULL,

    -- Audit trail
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,

    FOREIGN KEY (session_id) REFERENCES cash_drawer_sessions(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_type (transaction_type),
    INDEX idx_created (created_at),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cash deposits (when money is taken to bank or safe)
CREATE TABLE IF NOT EXISTS cash_deposits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deposit_number VARCHAR(50) NOT NULL UNIQUE,
    session_id INT UNSIGNED COMMENT 'Related session if applicable',
    deposit_type ENUM('bank', 'safe', 'manager', 'other') NOT NULL DEFAULT 'bank',

    -- Deposit details
    deposit_date DATE NOT NULL,
    deposit_time TIME NOT NULL,
    amount DECIMAL(10,2) NOT NULL,

    -- Bill/coin breakdown
    bills_100 INT DEFAULT 0,
    bills_50 INT DEFAULT 0,
    bills_20 INT DEFAULT 0,
    bills_10 INT DEFAULT 0,
    bills_5 INT DEFAULT 0,
    bills_2 INT DEFAULT 0,
    bills_1 INT DEFAULT 0,
    coins_dollar INT DEFAULT 0,
    coins_quarter INT DEFAULT 0,
    coins_dime INT DEFAULT 0,
    coins_nickel INT DEFAULT 0,
    coins_penny INT DEFAULT 0,

    -- Bank details
    bank_name VARCHAR(150),
    bank_account VARCHAR(50),
    deposit_slip_number VARCHAR(50),

    -- Verification
    deposited_by INT UNSIGNED NOT NULL,
    verified_by INT UNSIGNED COMMENT 'Person who verified deposit',
    verified_at TIMESTAMP NULL,

    -- Status
    status ENUM('pending', 'deposited', 'verified', 'discrepancy') DEFAULT 'pending',
    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (session_id) REFERENCES cash_drawer_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (deposited_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_deposit_date (deposit_date),
    INDEX idx_status (status),
    INDEX idx_deposited_by (deposited_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cash variances/discrepancies tracking
CREATE TABLE IF NOT EXISTS cash_variances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    variance_type ENUM('overage', 'shortage', 'counterfeit', 'damaged', 'other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL,

    -- Investigation
    investigated_by INT UNSIGNED,
    investigated_at TIMESTAMP NULL,
    investigation_notes TEXT,
    resolution TEXT,

    -- Status
    status ENUM('reported', 'investigating', 'resolved', 'written_off') DEFAULT 'reported',
    resolved_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,

    FOREIGN KEY (session_id) REFERENCES cash_drawer_sessions(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (investigated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_session (session_id),
    INDEX idx_type (variance_type),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default cash drawer
INSERT INTO cash_drawers (name, location, drawer_number, current_balance, starting_float, is_active, requires_count_in, requires_count_out, notes, created_at)
VALUES
('Main Register', 'Front Counter', '001', 200.00, 200.00, TRUE, TRUE, TRUE, 'Primary POS register', NOW()),
('Back Office', 'Back Office', '002', 100.00, 100.00, FALSE, TRUE, TRUE, 'Backup register for busy periods', NOW());

-- Create view for current open sessions
CREATE OR REPLACE VIEW cash_drawer_sessions_open AS
SELECT
    cds.id,
    cds.session_number,
    cds.drawer_id,
    cd.name as drawer_name,
    cd.location as drawer_location,
    cds.user_id,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    cds.opened_at,
    cds.starting_balance,
    cds.status,
    COALESCE(SUM(CASE WHEN cdt.transaction_type = 'sale' THEN cdt.amount ELSE 0 END), 0) as total_sales_today,
    COALESCE(SUM(CASE WHEN cdt.transaction_type = 'withdrawal' THEN cdt.amount ELSE 0 END), 0) as total_withdrawals_today,
    COALESCE(SUM(CASE WHEN cdt.transaction_type = 'deposit' THEN cdt.amount ELSE 0 END), 0) as total_deposits_today,
    cds.starting_balance +
    COALESCE(SUM(CASE
        WHEN cdt.transaction_type IN ('sale', 'deposit', 'till_payback') THEN cdt.amount
        WHEN cdt.transaction_type IN ('return', 'refund', 'withdrawal', 'payout', 'till_loan') THEN -cdt.amount
        ELSE 0
    END), 0) as expected_current_balance,
    COUNT(cdt.id) as transaction_count,
    TIMESTAMPDIFF(HOUR, cds.opened_at, NOW()) as hours_open
FROM cash_drawer_sessions cds
INNER JOIN cash_drawers cd ON cds.drawer_id = cd.id
INNER JOIN users u ON cds.user_id = u.id
LEFT JOIN cash_drawer_transactions cdt ON cds.id = cdt.session_id
WHERE cds.status = 'open'
GROUP BY cds.id;

-- Create view for session summaries
CREATE OR REPLACE VIEW cash_drawer_session_summary AS
SELECT
    cds.id,
    cds.session_number,
    cds.drawer_id,
    cd.name as drawer_name,
    cds.user_id,
    CONCAT(u.first_name, ' ', u.last_name) as user_name,
    cds.opened_at,
    cds.closed_at,
    cds.starting_balance,
    cds.ending_balance,
    cds.expected_balance,
    cds.difference,
    cds.status,
    CASE
        WHEN cds.status = 'open' THEN 'Active'
        WHEN cds.difference = 0 THEN 'Balanced'
        WHEN cds.difference > 0 THEN CONCAT('Over $', ABS(cds.difference))
        WHEN cds.difference < 0 THEN CONCAT('Short $', ABS(cds.difference))
        ELSE 'Unknown'
    END as balance_status,
    cds.total_sales,
    cds.total_refunds,
    cds.total_deposits,
    cds.total_withdrawals,
    COUNT(DISTINCT cdt.id) as transaction_count,
    TIMESTAMPDIFF(HOUR, cds.opened_at, COALESCE(cds.closed_at, NOW())) as session_duration_hours
FROM cash_drawer_sessions cds
INNER JOIN cash_drawers cd ON cds.drawer_id = cd.id
INNER JOIN users u ON cds.user_id = u.id
LEFT JOIN cash_drawer_transactions cdt ON cds.id = cdt.session_id
GROUP BY cds.id;

-- Comments
ALTER TABLE cash_drawers COMMENT = 'Physical cash register drawers';
ALTER TABLE cash_drawer_sessions COMMENT = 'Shift/day sessions for cash drawers';
ALTER TABLE cash_drawer_transactions COMMENT = 'Detailed log of all cash movements';
ALTER TABLE cash_deposits COMMENT = 'Cash deposits to bank or safe';
ALTER TABLE cash_variances COMMENT = 'Cash discrepancies and investigations';
