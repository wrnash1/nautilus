-- ================================================
-- LOAD DEMO DATA - Nautilus Alpha v1
-- Can be run anytime to add demo data for testing
-- ================================================

USE nautilus;

-- Get the tenant ID (usually 1)
SET @tenant_id = 1;

-- ================================================
-- 1. DEMO CUSTOMERS
-- ================================================

INSERT IGNORE INTO customers (id, tenant_id, first_name, last_name, email, phone, customer_type, customer_since, is_active) VALUES
(1, @tenant_id, 'John', 'Doe', 'john.doe@example.com', '555-0101', 'B2C', '2024-01-15', 1),
(2, @tenant_id, 'Jane', 'Smith', 'jane.smith@example.com', '555-0102', 'B2C', '2024-02-20', 1),
(3, @tenant_id, 'Mike', 'Johnson', 'mike.johnson@example.com', '555-0103', 'B2C', '2024-03-10', 1),
(4, @tenant_id, 'Sarah', 'Williams', 'sarah.williams@example.com', '555-0104', 'B2C', '2024-04-05', 1),
(5, @tenant_id, 'David', 'Brown', 'david.brown@example.com', '555-0105', 'B2C', '2024-05-12', 1),
(6, @tenant_id, 'Emily', 'Davis', 'emily.davis@example.com', '555-0106', 'B2C', '2024-06-18', 1),
(7, @tenant_id, 'Chris', 'Miller', 'chris.miller@example.com', '555-0107', 'B2C', '2024-07-22', 1),
(8, @tenant_id, 'Lisa', 'Wilson', 'lisa.wilson@example.com', '555-0108', 'B2C', '2024-08-30', 1);

-- ================================================
-- 2. DEMO CERTIFICATIONS FOR CUSTOMERS
-- ================================================

-- Assuming certification_agencies and certifications exist
INSERT IGNORE INTO customer_certifications (customer_id, certification_id, certification_number, issue_date, expiry_date)
SELECT 1, id, 'OW123456', '2024-01-20', '2026-01-20' FROM certifications WHERE name LIKE '%Open Water%' LIMIT 1;

INSERT IGNORE INTO customer_certifications (customer_id, certification_id, certification_number, issue_date, expiry_date)
SELECT 2, id, 'AOW234567', '2024-02-25', '2026-02-25' FROM certifications WHERE name LIKE '%Advanced%' LIMIT 1;

INSERT IGNORE INTO customer_certifications (customer_id, certification_id, certification_number, issue_date, expiry_date)
SELECT 3, id, 'RD345678', '2024-03-15', '2026-03-15' FROM certifications WHERE name LIKE '%Rescue%' LIMIT 1;

INSERT IGNORE INTO customer_certifications (customer_id, certification_id, certification_number, issue_date, expiry_date)
SELECT 4, id, 'DM456789', '2024-04-10', '2026-04-10' FROM certifications WHERE name LIKE '%Divemaster%' LIMIT 1;

-- ================================================
-- 3. DEMO PRODUCTS
-- ================================================

-- Get category IDs
SET @cat_regulators = (SELECT id FROM product_categories WHERE name = 'Regulators' LIMIT 1);
SET @cat_bcds = (SELECT id FROM product_categories WHERE name = 'BCDs' OR name LIKE '%BCD%' LIMIT 1);
SET @cat_wetsuits = (SELECT id FROM product_categories WHERE name = 'Wetsuits' OR name LIKE '%Wetsuit%' LIMIT 1);
SET @cat_fins = (SELECT id FROM product_categories WHERE name = 'Fins' LIMIT 1);
SET @cat_masks = (SELECT id FROM product_categories WHERE name = 'Masks' OR name LIKE '%Mask%' LIMIT 1);
SET @cat_computers = (SELECT id FROM product_categories WHERE name = 'Dive Computers' OR name LIKE '%Computer%' LIMIT 1);

-- If categories don't exist, create them
INSERT IGNORE INTO product_categories (name, slug, is_active) VALUES
('Regulators', 'regulators', 1),
('BCDs', 'bcds', 1),
('Wetsuits', 'wetsuits', 1),
('Fins', 'fins', 1),
('Masks & Snorkels', 'masks-snorkels', 1),
('Dive Computers', 'dive-computers', 1);

-- Refresh category IDs
SET @cat_regulators = (SELECT id FROM product_categories WHERE name = 'Regulators' LIMIT 1);
SET @cat_bcds = (SELECT id FROM product_categories WHERE name = 'BCDs' LIMIT 1);
SET @cat_wetsuits = (SELECT id FROM product_categories WHERE name = 'Wetsuits' LIMIT 1);
SET @cat_fins = (SELECT id FROM product_categories WHERE name = 'Fins' LIMIT 1);
SET @cat_masks = (SELECT id FROM product_categories WHERE name = 'Masks & Snorkels' LIMIT 1);
SET @cat_computers = (SELECT id FROM product_categories WHERE name = 'Dive Computers' LIMIT 1);

-- Insert demo products
INSERT IGNORE INTO products (tenant_id, category_id, sku, name, slug, description, retail_price, cost_price, stock_quantity, is_active, is_featured) VALUES
(@tenant_id, @cat_regulators, 'REG-SP-001', 'Scubapro MK25 EVO Regulator', 'scubapro-mk25-evo', 'Professional-grade regulator with balanced piston design', 599.99, 400.00, 5, 1, 1),
(@tenant_id, @cat_regulators, 'REG-AT-001', 'Atomic Z2 Regulator', 'atomic-z2-regulator', 'High-performance regulator with titanium components', 549.99, 370.00, 3, 1, 1),
(@tenant_id, @cat_regulators, 'REG-AP-001', 'Apeks XTX50 Regulator', 'apeks-xtx50', 'Cold water regulator with excellent breathing performance', 489.99, 330.00, 4, 1, 0),
(@tenant_id, @cat_bcds, 'BCD-SP-001', 'Scubapro Hydros Pro BCD', 'scubapro-hydros-pro', 'Modular BCD with gel padding and quick-release system', 799.99, 540.00, 6, 1, 1),
(@tenant_id, @cat_bcds, 'BCD-AQ-001', 'Aqualung Rogue BCD', 'aqualung-rogue-bcd', 'Travel-friendly BCD with integrated weight system', 649.99, 440.00, 4, 1, 0),
(@tenant_id, @cat_bcds, 'BCD-ZE-001', 'Zeagle Ranger BCD', 'zeagle-ranger', 'Rugged back-inflate BCD perfect for technical diving', 729.99, 490.00, 3, 1, 0),
(@tenant_id, @cat_wetsuits, 'WET-SP-001', 'Scubapro Everflex 5mm Wetsuit', 'scubapro-everflex-5mm', '5mm wetsuit with super-stretch neoprene', 399.99, 270.00, 8, 1, 1),
(@tenant_id, @cat_wetsuits, 'WET-AQ-002', 'Aqualung Dive 7mm Wetsuit', 'aqualung-dive-7mm', '7mm cold-water wetsuit with glideskin seal', 449.99, 300.00, 5, 1, 0),
(@tenant_id, @cat_wetsuits, 'WET-CR-001', 'Cressi Morea 3mm Wetsuit', 'cressi-morea-3mm', 'Warm water wetsuit with anatomic cut', 279.99, 190.00, 10, 1, 0),
(@tenant_id, @cat_fins, 'FIN-SP-001', 'Scubapro Jet Fins', 'scubapro-jet-fins', 'Classic rubber fins, perfect for frog kicking', 129.99, 85.00, 15, 1, 1),
(@tenant_id, @cat_fins, 'FIN-MA-001', 'Mares Avanti Quattro Plus', 'mares-avanti-quattro', 'High-efficiency fins with channel thrust technology', 149.99, 100.00, 12, 1, 0),
(@tenant_id, @cat_fins, 'FIN-AQ-001', 'Aqualung Storm Fins', 'aqualung-storm-fins', 'Powerful fins for strong currents', 139.99, 95.00, 10, 1, 0),
(@tenant_id, @cat_masks, 'MSK-SP-001', 'Scubapro Crystal Vu Mask', 'scubapro-crystal-vu', 'Single-lens mask with excellent field of vision', 89.99, 60.00, 20, 1, 1),
(@tenant_id, @cat_masks, 'MSK-AT-001', 'Atomic Venom Frameless Mask', 'atomic-venom-mask', 'Ultra-clear frameless mask with great seal', 119.99, 80.00, 15, 1, 1),
(@tenant_id, @cat_masks, 'MSK-CR-001', 'Cressi Big Eyes Evolution Mask', 'cressi-big-eyes', 'Wide-view mask perfect for photography', 79.99, 54.00, 18, 1, 0),
(@tenant_id, @cat_computers, 'COM-SU-001', 'Suunto D5 Dive Computer', 'suunto-d5', 'Color screen dive computer with wireless charging', 699.99, 470.00, 4, 1, 1),
(@tenant_id, @cat_computers, 'COM-SH-001', 'Shearwater Peregrine', 'shearwater-peregrine', 'Recreational dive computer with vibrant display', 449.99, 305.00, 6, 1, 1),
(@tenant_id, @cat_computers, 'COM-MA-001', 'Mares Puck Pro', 'mares-puck-pro', 'Entry-level wrist computer with easy interface', 229.99, 155.00, 10, 1, 0),
(@tenant_id, @cat_computers, 'COM-CR-001', 'Cressi Leonardo', 'cressi-leonardo', 'Simple, reliable dive computer for beginners', 199.99, 135.00, 12, 1, 0),
(@tenant_id, @cat_computers, 'COM-AQ-001', 'Aqualung i300C', 'aqualung-i300c', 'Bluetooth-enabled computer with app connectivity', 349.99, 235.00, 8, 1, 0);

-- ================================================
-- 4. DEMO COURSES
-- ================================================

INSERT IGNORE INTO courses (tenant_id, name, description, price, duration_days, max_students, is_active) VALUES
(@tenant_id, 'Open Water Diver', 'PADI Open Water certification course - your first step into the underwater world', 399.99, 3, 8, 1),
(@tenant_id, 'Advanced Open Water', 'PADI Advanced Open Water course - expand your skills with specialty dives', 349.99, 2, 8, 1),
(@tenant_id, 'Rescue Diver', 'PADI Rescue Diver course - learn to prevent and manage dive emergencies', 450.00, 3, 6, 1),
(@tenant_id, 'Divemaster', 'PADI Divemaster course - become a professional dive leader', 850.00, 14, 4, 1),
(@tenant_id, 'Enriched Air (Nitrox)', 'PADI Enriched Air Diver specialty - dive longer with nitrox', 200.00, 1, 10, 1);

-- ================================================
-- 5. ASSIGN DEMO CUSTOMER TAGS
-- ================================================

-- Assign tags to customers
INSERT IGNORE INTO customer_tag_assignments (customer_id, tag_id) VALUES
(1, 3), -- John Doe: New Customer
(2, 2), -- Jane Smith: Regular
(3, 2), -- Mike Johnson: Regular
(4, 4), -- Sarah Williams: Instructor
(5, 1), -- David Brown: VIP
(6, 3), -- Emily Davis: New Customer
(7, 5), -- Chris Miller: Wholesale
(8, 2); -- Lisa Wilson: Regular

-- ================================================
-- 6. UPDATE SYSTEM SETTINGS
-- ================================================

UPDATE system_settings SET setting_value = 'true' WHERE setting_key = 'demo_data_loaded' AND tenant_id = @tenant_id;

-- ================================================
-- VERIFICATION
-- ================================================

SELECT
    'Demo Data Loaded Successfully!' as status,
    (SELECT COUNT(*) FROM customers WHERE tenant_id = @tenant_id) as total_customers,
    (SELECT COUNT(*) FROM products WHERE tenant_id = @tenant_id) as total_products,
    (SELECT COUNT(*) FROM courses WHERE tenant_id = @tenant_id) as total_courses,
    (SELECT COUNT(*) FROM customer_tag_assignments) as total_tag_assignments;
