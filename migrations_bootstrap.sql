-- Create Migrations Table
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    error_message TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed known completed migrations to prevent re-running
INSERT IGNORE INTO migrations (filename, status, executed_at) VALUES ('000_CORE_SCHEMA.sql', 'completed', NOW());
INSERT IGNORE INTO migrations (filename, status, executed_at) VALUES ('001_create_migrations_table.sql', 'completed', NOW());
-- We know this ran because we verified the tables exist
INSERT IGNORE INTO migrations (filename, status, executed_at) VALUES ('100_add_air_fill_deep_dive.sql', 'completed', NOW());

SELECT 'Migrations table bootstrapped successfully.' as result;
SELECT * FROM migrations;
