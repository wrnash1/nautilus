-- Help Articles Table
-- Stores help center articles and documentation

CREATE TABLE IF NOT EXISTS help_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,

    -- Article Information
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    excerpt TEXT,
    content LONGTEXT,

    -- Publishing
    is_published BOOLEAN DEFAULT 1,
    published_at TIMESTAMP NULL,

    -- Metadata
    author_id INT,
    views INT DEFAULT 0,
    helpful_count INT DEFAULT 0,
    not_helpful_count INT DEFAULT 0,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_tenant_slug (tenant_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for faster lookups and searches
CREATE INDEX idx_help_tenant ON help_articles(tenant_id);
CREATE INDEX idx_help_category ON help_articles(category);
CREATE INDEX idx_help_published ON help_articles(is_published);
CREATE INDEX idx_help_views ON help_articles(views);
CREATE FULLTEXT INDEX idx_help_search ON help_articles(title, content, excerpt);

-- Insert default help articles
INSERT INTO help_articles (tenant_id, title, slug, category, excerpt, content, is_published, published_at) VALUES
(1, 'Getting Started with Nautilus', 'getting-started', 'Getting Started', 'Learn the basics of using Nautilus for your dive shop.', '<h2>Welcome to Nautilus!</h2><p>This guide will help you get started with managing your dive shop.</p><h3>First Steps</h3><ul><li>Add your company information</li><li>Set up payment methods</li><li>Add your first products</li><li>Create dive courses</li></ul>', 1, NOW()),
(1, 'How to Process a Sale', 'process-sale', 'Point of Sale', 'Step-by-step guide to processing sales transactions.', '<h2>Processing a Sale</h2><p>Navigate to Store → POS to access the point of sale system.</p><ol><li>Search for products or scan barcodes</li><li>Add items to cart</li><li>Enter customer information</li><li>Process payment</li><li>Print receipt</li></ol>', 1, NOW()),
(1, 'Managing Inventory', 'managing-inventory', 'Inventory', 'Learn how to manage your product inventory effectively.', '<h2>Inventory Management</h2><p>Keep track of your products and stock levels.</p><h3>Adding Products</h3><p>Go to Store → Products → Add New Product...</p>', 1, NOW()),
(1, 'Creating Dive Courses', 'creating-courses', 'Courses', 'How to create and schedule dive courses.', '<h2>Course Management</h2><p>Nautilus makes it easy to manage dive courses and certifications.</p><h3>Creating a Course</h3><p>Navigate to Store → Courses → Add Course...</p>', 1, NOW()),
(1, 'Equipment Rentals', 'equipment-rentals', 'Rentals', 'How to manage equipment rentals.', '<h2>Rental Management</h2><p>Track rental equipment and reservations.</p><h3>Creating a Rental</h3><p>Go to Store → Rentals → Create Reservation...</p>', 1, NOW());
