-- Test Database Setup Script for Nautilus
-- Run this with: mysql -u root -p < test-database-setup.sql

-- Switch to nautilus database
USE nautilus;

-- Check if tables exist
SELECT 'Checking tables...' AS status;
SELECT COUNT(*) AS table_count FROM information_schema.tables
WHERE table_schema = 'nautilus';

-- Check if roles table has data
SELECT 'Checking roles...' AS status;
SELECT * FROM roles LIMIT 5;

-- Check if users table has data
SELECT 'Checking users...' AS status;
SELECT id, username, email, first_name, last_name, role_id, is_active, created_at
FROM users LIMIT 5;

-- If no admin user exists, create one
-- Note: This won't error if the user already exists due to the INSERT IGNORE
INSERT IGNORE INTO roles (id, name, description, created_at, updated_at)
VALUES (1, 'Administrator', 'Full system access', NOW(), NOW());

INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role_id, is_active, created_at, updated_at)
VALUES (
    'admin',
    'admin@nautilus.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    1,
    1,
    NOW(),
    NOW()
);

-- Verify admin user
SELECT 'Admin user verification:' AS status;
SELECT id, username, email, first_name, last_name, is_active
FROM users
WHERE email = 'admin@nautilus.local';

SELECT 'Database setup complete!' AS status;
