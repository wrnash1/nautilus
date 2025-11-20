-- Demo Data for Nautilus Dive Shop
-- This seed file creates sample data for testing and demonstration purposes

-- Insert demo tenant (if not exists)
INSERT IGNORE INTO tenants (id, tenant_uuid, company_name, subdomain, contact_email, status, created_at)
VALUES (1, UUID(), 'Demo Dive Shop', 'demo', 'demo@example.com', 'active', NOW());

-- Insert demo admin user (if not exists)
-- Password: demo123
INSERT IGNORE INTO users (id, tenant_id, role_id, email, password_hash, first_name, last_name, is_active, created_at)
VALUES (1, 1, 1, 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'Admin', 1, NOW());

-- Insert demo products
INSERT IGNORE INTO products (id, tenant_id, sku, name, description, category, price, cost, stock_quantity, min_stock_level, is_active, created_at)
VALUES
(1, 1, 'MASK-001', 'Professional Dive Mask', 'High-quality tempered glass dive mask with silicone skirt', 'Equipment', 89.99, 45.00, 25, 5, 1, NOW()),
(2, 1, 'FIN-001', 'Open Heel Fins', 'Adjustable open heel fins for diving', 'Equipment', 129.99, 65.00, 15, 3, 1, NOW()),
(3, 1, 'SNORKEL-001', 'Dry Top Snorkel', 'Dry top snorkel with purge valve', 'Equipment', 39.99, 20.00, 30, 10, 1, NOW()),
(4, 1, 'BCD-001', 'Buoyancy Control Device', 'Advanced BCD with integrated weight system', 'Equipment', 549.99, 275.00, 8, 2, 1, NOW()),
(5, 1, 'REG-001', 'Regulator Set', 'Professional regulator with octopus and gauges', 'Equipment', 799.99, 400.00, 5, 2, 1, NOW()),
(6, 1, 'SUIT-001', '3mm Wetsuit', 'Full 3mm wetsuit, various sizes', 'Apparel', 199.99, 100.00, 20, 5, 1, NOW()),
(7, 1, 'BOOT-001', 'Dive Boots 5mm', 'Neoprene dive boots with zipper', 'Apparel', 59.99, 30.00, 18, 6, 1, NOW()),
(8, 1, 'GLOVE-001', 'Dive Gloves', 'Neoprene dive gloves', 'Apparel', 29.99, 15.00, 25, 8, 1, NOW()),
(9, 1, 'COMP-001', 'Dive Computer', 'Advanced dive computer with air integration', 'Electronics', 449.99, 225.00, 12, 3, 1, NOW()),
(10, 1, 'LIGHT-001', 'Dive Light', 'LED dive light 1200 lumens', 'Electronics', 149.99, 75.00, 10, 3, 1, NOW()),
(11, 1, 'COURSE-OW', 'Open Water Certification', 'PADI Open Water Diver certification course', 'Training', 499.00, 250.00, 999, 0, 1, NOW()),
(12, 1, 'COURSE-AOW', 'Advanced Open Water', 'PADI Advanced Open Water certification', 'Training', 399.00, 200.00, 999, 0, 1, NOW()),
(13, 1, 'COURSE-RESCUE', 'Rescue Diver Course', 'PADI Rescue Diver certification', 'Training', 449.00, 225.00, 999, 0, 1, NOW()),
(14, 1, 'DIVE-LOCAL', 'Local Dive Trip', 'Single tank local dive trip', 'Services', 89.00, 45.00, 999, 0, 1, NOW()),
(15, 1, 'DIVE-BOAT', 'Boat Dive - 2 Tank', 'Two tank boat dive trip', 'Services', 159.00, 80.00, 999, 0, 1, NOW());

-- Insert demo customers
INSERT IGNORE INTO customers (id, tenant_id, first_name, last_name, email, phone, address, city, state, zip, country, certification_level, certification_number, created_at)
VALUES
(1, 1, 'John', 'Smith', 'john.smith@example.com', '555-0101', '123 Ocean Ave', 'Miami', 'FL', '33101', 'USA', 'Advanced Open Water', 'PADI-123456', NOW()),
(2, 1, 'Sarah', 'Johnson', 'sarah.j@example.com', '555-0102', '456 Beach Blvd', 'Key Largo', 'FL', '33037', 'USA', 'Open Water', 'PADI-234567', NOW()),
(3, 1, 'Michael', 'Brown', 'mbrown@example.com', '555-0103', '789 Coral St', 'Fort Lauderdale', 'FL', '33301', 'USA', 'Rescue Diver', 'PADI-345678', NOW()),
(4, 1, 'Emily', 'Davis', 'emily.davis@example.com', '555-0104', '321 Reef Rd', 'West Palm Beach', 'FL', '33401', 'USA', 'Open Water', 'PADI-456789', NOW()),
(5, 1, 'David', 'Wilson', 'dwilson@example.com', '555-0105', '654 Marina Way', 'Miami Beach', 'FL', '33139', 'USA', 'Divemaster', 'PADI-567890', NOW());

-- Insert demo sales orders
INSERT IGNORE INTO sales_orders (id, tenant_id, customer_id, order_number, order_date, total_amount, tax_amount, discount_amount, payment_method, payment_status, order_status, created_at)
VALUES
(1, 1, 1, 'ORD-2024-001', DATE_SUB(NOW(), INTERVAL 30 DAY), 279.97, 19.60, 0.00, 'credit_card', 'paid', 'completed', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, 1, 2, 'ORD-2024-002', DATE_SUB(NOW(), INTERVAL 25 DAY), 499.00, 34.93, 0.00, 'credit_card', 'paid', 'completed', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(3, 1, 3, 'ORD-2024-003', DATE_SUB(NOW(), INTERVAL 20 DAY), 549.99, 38.50, 0.00, 'cash', 'paid', 'completed', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 1, 4, 'ORD-2024-004', DATE_SUB(NOW(), INTERVAL 15 DAY), 159.00, 11.13, 0.00, 'credit_card', 'paid', 'completed', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(5, 1, 5, 'ORD-2024-005', DATE_SUB(NOW(), INTERVAL 10 DAY), 899.00, 62.93, 0.00, 'credit_card', 'paid', 'completed', DATE_SUB(NOW(), INTERVAL 10 DAY));

-- Insert demo sales order items
INSERT IGNORE INTO sales_order_items (sales_order_id, product_id, quantity, unit_price, subtotal, created_at)
VALUES
(1, 1, 1, 89.99, 89.99, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 2, 1, 129.99, 129.99, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 3, 1, 39.99, 39.99, DATE_SUB(NOW(), INTERVAL 30 DAY)),
(2, 11, 1, 499.00, 499.00, DATE_SUB(NOW(), INTERVAL 25 DAY)),
(3, 4, 1, 549.99, 549.99, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 15, 1, 159.00, 159.00, DATE_SUB(NOW(), INTERVAL 15 DAY)),
(5, 12, 1, 399.00, 399.00, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 11, 1, 499.00, 499.00, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- Insert demo inventory adjustments
INSERT IGNORE INTO inventory_adjustments (tenant_id, product_id, adjustment_type, quantity, reason, adjusted_by, created_at)
VALUES
(1, 1, 'restock', 10, 'New shipment received', 1, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(1, 2, 'restock', 5, 'New shipment received', 1, DATE_SUB(NOW(), INTERVAL 45 DAY)),
(1, 9, 'adjustment', -1, 'Damaged unit - removed from inventory', 1, DATE_SUB(NOW(), INTERVAL 20 DAY));

-- Mark demo data as installed
CREATE TABLE IF NOT EXISTS demo_data_installed (
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO demo_data_installed (installed_at) VALUES (NOW());
