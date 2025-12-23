SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `popular_searches`;
DROP TABLE IF EXISTS `search_analytics`;
DROP TABLE IF EXISTS `saved_searches`;
DROP TABLE IF EXISTS `search_history`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `popular_searches`;
DROP TABLE IF EXISTS `search_analytics`;
DROP TABLE IF EXISTS `saved_searches`;
DROP TABLE IF EXISTS `search_history`;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `popular_searches`;
DROP TABLE IF EXISTS `search_analytics`;
DROP TABLE IF EXISTS `saved_searches`;
DROP TABLE IF EXISTS `search_history`;

-- Search and Filtering System
-- Track searches and provide analytics

-- Search History (track all searches for analytics)
CREATE TABLE IF NOT EXISTS search_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED,
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
    tenant_id BIGINT UNSIGNED,
    user_id BIGINT UNSIGNED NOT NULL,
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
    tenant_id BIGINT UNSIGNED,
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
    tenant_id BIGINT UNSIGNED,
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
-- Products full-text search
CREATE FULLTEXT INDEX IF NOT EXISTS ft_products_search ON products(name, sku, description);

-- Customers full-text search
CREATE FULLTEXT INDEX IF NOT EXISTS ft_customers_search ON customers(first_name, last_name, email);

-- Courses full-text search
CREATE FULLTEXT INDEX IF NOT EXISTS ft_courses_search ON courses(name, description);

-- Equipment full-text search
CREATE FULLTEXT INDEX IF NOT EXISTS ft_equipment_search ON equipment(name, serial_number, description);


SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;