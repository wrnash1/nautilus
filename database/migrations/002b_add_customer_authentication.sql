SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customers' 
    AND COLUMN_NAME = 'password');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `customers` ADD COLUMN `password` VARCHAR(255) AFTER `email`',
    'SELECT "Column password already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customers' 
    AND COLUMN_NAME = 'email_verified_at');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `customers` ADD COLUMN `email_verified_at` TIMESTAMP NULL AFTER `password`',
    'SELECT "Column email_verified_at already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customers' 
    AND COLUMN_NAME = 'remember_token');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE `customers` ADD COLUMN `remember_token` VARCHAR(100) NULL AFTER `email_verified_at`',
    'SELECT "Column remember_token already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'customers' 
    AND INDEX_NAME = 'idx_unique_email');

SET @sql = IF(@index_exists = 0, 
    'ALTER TABLE `customers` ADD UNIQUE INDEX `idx_unique_email` (`email`)',
    'SELECT "Index idx_unique_email already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
