-- Search and Filtering System
-- Track searches and provide analytics

-- Search History (track all searches for analytics)
CREATE TABLE IF NOT EXISTS search_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    user_id INT UNSIGNED,
    search_query VARCHAR(500) NOT NULL,
    entity_type VARCHAR(50) NOT NULL COMMENT 'products, customers, transactions, etc.',
    result_count INT DEFAULT 0,
    filters_applied JSON,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_search_query (search_query(255)),
    INDEX idx_entity_type (entity_type),
    INDEX idx_searched_at (searched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saved Searches (user-saved search filters)
CREATE TABLE IF NOT EXISTS saved_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    user_id INT UNSIGNED NOT NULL,
    search_name VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    search_query VARCHAR(500),
    filters JSON NOT NULL COMMENT 'Saved filter configuration',
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_entity_type (entity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search Analytics (aggregated search metrics)
CREATE TABLE IF NOT EXISTS search_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    date DATE NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    total_searches INT DEFAULT 0,
    unique_users INT DEFAULT 0,
    avg_results DECIMAL(10, 2) DEFAULT 0,
    zero_result_searches INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_date_entity (tenant_id, date, entity_type),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_date (date),
    INDEX idx_entity_type (entity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Popular Searches (frequently searched terms)
CREATE TABLE IF NOT EXISTS popular_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED,
    search_term VARCHAR(500) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    search_count INT DEFAULT 1,
    last_searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_term_entity (tenant_id, search_term(255), entity_type),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_search_count (search_count),
    INDEX idx_entity_type (entity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create full-text indexes for better search performance (conditional)
-- These enable faster text searching on key fields
SET @dbname = DATABASE();

-- Products full-text search
SET @tablename = "products";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'ft_products_search') > 0,
  "SELECT 1",
  "ALTER TABLE products ADD FULLTEXT INDEX ft_products_search (name, sku, description)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Customers full-text search
SET @tablename = "customers";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'ft_customers_search') > 0,
  "SELECT 1",
  "ALTER TABLE customers ADD FULLTEXT INDEX ft_customers_search (first_name, last_name, email)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Courses full-text search
SET @tablename = "courses";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'ft_courses_search') > 0,
  "SELECT 1",
  "ALTER TABLE courses ADD FULLTEXT INDEX ft_courses_search (name, description)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Equipment full-text search
SET @tablename = "equipment";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname AND table_name = @tablename AND index_name = 'ft_equipment_search') > 0,
  "SELECT 1",
  "ALTER TABLE equipment ADD FULLTEXT INDEX ft_equipment_search (name, serial_number, description)"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
