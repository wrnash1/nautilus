-- Demo Data for Nautilus Dive Shop
-- This file contains sample data for testing and demonstration purposes

-- Demo Users (password for all: password)
INSERT INTO users (role_id, first_name, last_name, email, password_hash, is_active, created_at) VALUES
(2, 'Jane', 'Manager', 'manager@nautilus.demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW()),
(3, 'Bob', 'Cashier', 'cashier@nautilus.demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

-- Rental Categories
INSERT INTO rental_categories (name, description) VALUES
('BCDs', 'Buoyancy Control Devices'),
('Regulators', 'Regulators and octopus'),
('Wetsuits', 'Wetsuits and thermal protection'),
('Tanks', 'Scuba tanks and cylinders'),
('Fins', 'Diving fins'),
('Masks', 'Masks and snorkels'),
('Weights', 'Weight belts and systems'),
('Accessories', 'Dive computers, gauges, and accessories');

-- Rental Equipment
INSERT INTO rental_equipment (category_id, equipment_code, name, size, daily_rate, weekly_rate, status, `condition`) VALUES
(1, 'BCD-001', 'Cressi Start BCD', 'Medium', 25.00, 125.00, 'available', 'good'),
(1, 'BCD-002', 'Scubapro Hydros Pro BCD', 'Large', 30.00, 150.00, 'available', 'excellent'),
(1, 'BCD-003', 'Aqualung Axiom BCD', 'Small', 25.00, 125.00, 'available', 'good'),
(2, 'REG-001', 'Cressi MC9 Regulator', NULL, 30.00, 150.00, 'available', 'excellent'),
(2, 'REG-002', 'Scubapro MK25 Regulator', NULL, 35.00, 175.00, 'available', 'good'),
(2, 'REG-003', 'Atomic Z2 Regulator', NULL, 40.00, 200.00, 'available', 'excellent'),
(3, 'SUIT-001', '5mm Full Wetsuit', 'Large', 15.00, 75.00, 'available', 'good'),
(3, 'SUIT-002', '3mm Shorty Wetsuit', 'Medium', 12.00, 60.00, 'available', 'fair'),
(3, 'SUIT-003', '7mm Semi-Dry Suit', 'Large', 20.00, 100.00, 'available', 'excellent'),
(4, 'TANK-001', 'Aluminum 80cf Tank', NULL, 10.00, 50.00, 'available', 'good'),
(4, 'TANK-002', 'Steel 100cf Tank', NULL, 12.00, 60.00, 'available', 'excellent'),
(4, 'TANK-003', 'Aluminum 80cf Tank', NULL, 10.00, 50.00, 'available', 'good'),
(5, 'FIN-001', 'Mares Avanti Quattro', 'Large', 8.00, 40.00, 'available', 'good'),
(5, 'FIN-002', 'Scubapro Jet Fins', 'Medium', 10.00, 50.00, 'available', 'excellent'),
(6, 'MASK-001', 'Cressi Big Eyes', 'One Size', 5.00, 25.00, 'available', 'good'),
(6, 'MASK-002', 'Atomic Frameless Mask', 'One Size', 8.00, 40.00, 'available', 'excellent');

-- Courses
INSERT INTO courses (course_code, name, description, duration_days, max_students, price) VALUES
('OW', 'Open Water Diver', 'PADI Open Water certification course for beginners. Learn the fundamentals of scuba diving in pool and open water environments.', 3, 6, 499.00),
('AOW', 'Advanced Open Water', 'PADI Advanced Open Water course. Expand your skills with 5 adventure dives including deep and navigation.', 2, 6, 399.00),
('RESCUE', 'Rescue Diver', 'PADI Rescue Diver course with emergency management. Learn to prevent and manage dive emergencies.', 3, 6, 449.00),
('DM', 'Divemaster', 'PADI Divemaster professional level course. First step in becoming a dive professional.', 14, 4, 999.00),
('NITROX', 'Enriched Air Nitrox', 'Learn to dive with enriched air nitrox for extended bottom times.', 1, 8, 199.00);

-- Trips
INSERT INTO trips (trip_code, name, destination, description, duration_days, max_participants, price) VALUES
('REEF-01', 'Local Reef Dive', 'Key Largo Reef', 'Explore vibrant coral reefs and marine life in the beautiful Florida Keys.', 1, 20, 149.00),
('WRECK-01', 'Wreck Diving Adventure', 'USS Spiegel Grove', 'Dive the famous USS Spiegel Grove wreck, one of the largest artificial reefs.', 1, 12, 199.00),
('BAH-01', 'Bahamas Dive Trip', 'Nassau, Bahamas', '7-day diving expedition in crystal clear waters with shark dives and blue holes.', 7, 16, 2499.00),
('COZ-01', 'Cozumel Diving', 'Cozumel, Mexico', '5-day trip exploring world-class drift diving in Cozumel.', 5, 20, 1799.00),
('CAVE-01', 'Cave Diving Experience', 'Ginnie Springs, FL', 'Cavern and cave diving in Florida\'s famous crystal-clear springs.', 2, 8, 599.00);

-- Product Categories
INSERT INTO product_categories (name, slug, description, is_active, created_at) VALUES
('Masks', 'masks', 'Diving masks and snorkels', 1, NOW()),
('Fins', 'fins', 'Diving fins and accessories', 1, NOW()),
('Wetsuits', 'wetsuits', 'Wetsuits, drysuits, and thermal protection', 1, NOW()),
('BCDs', 'bcds', 'Buoyancy Control Devices', 1, NOW()),
('Regulators', 'regulators', 'Regulators and octopus', 1, NOW()),
('Accessories', 'accessories', 'Dive accessories, gauges, and computers', 1, NOW()),
('Training', 'training', 'Training materials and certification fees', 1, NOW()),
('Apparel', 'apparel', 'Dive apparel and merchandise', 1, NOW());

-- Products
INSERT INTO products (category_id, sku, slug, name, description, cost_price, retail_price, stock_quantity, low_stock_threshold, track_inventory, is_active, created_at) VALUES
-- Masks
(1, 'MASK-001', 'cressi-big-eyes-evolution', 'Cressi Big Eyes Evolution', 'Wide-view diving mask with tempered glass and excellent field of vision', 45.00, 89.99, 25, 5, 1, 1, NOW()),
(1, 'MASK-002', 'scubapro-crystal-vu', 'Scubapro Crystal Vu', 'Single lens mask with optical clarity and low volume design', 60.00, 119.99, 15, 5, 1, 1, NOW()),
(1, 'MASK-003', 'atomic-venom-frameless', 'Atomic Venom Frameless', 'Ultra-clear frameless mask with superior fit and comfort', 75.00, 149.99, 12, 3, 1, 1, NOW()),
(1, 'MASK-004', 'aqualung-reveal-x2', 'Aqualung Reveal X2', 'Twin lens mask with easy adjustment and great vision', 50.00, 99.99, 18, 5, 1, 1, NOW()),

-- Fins
(2, 'FIN-001', 'mares-avanti-quattro-plus', 'Mares Avanti Quattro Plus', 'Full foot fins for warm water diving with excellent propulsion', 75.00, 149.99, 30, 5, 1, 1, NOW()),
(2, 'FIN-002', 'scubapro-jet-fins', 'Scubapro Jet Fins', 'Heavy duty diving fins for technical and professional diving', 95.00, 189.99, 20, 5, 1, 1, NOW()),
(2, 'FIN-003', 'hollis-f1-bat-fins', 'Hollis F1 Bat Fins', 'High performance dive fins with spring straps included', 115.00, 229.99, 8, 3, 1, 1, NOW()),
(2, 'FIN-004', 'atomic-smoke-on-the-water', 'Atomic Smoke on the Water', 'Split fin design for effortless kicking and maximum efficiency', 125.00, 249.99, 10, 3, 1, 1, NOW()),

-- Wetsuits
(3, 'SUIT-001', '3mm-shorty-wetsuit', '3mm Shorty Wetsuit', 'Warm water shorty wetsuit with back zip', 75.00, 149.99, 18, 5, 1, 1, NOW()),
(3, 'SUIT-002', '5mm-full-wetsuit', '5mm Full Wetsuit', 'Full body 5mm wetsuit for temperate waters', 150.00, 299.99, 10, 3, 1, 1, NOW()),
(3, 'SUIT-003', '7mm-semi-dry-suit', '7mm Semi-Dry Suit', 'Cold water semi-dry suit with wrist and ankle seals', 250.00, 499.99, 5, 2, 1, 1, NOW()),
(3, 'SUIT-004', '3mm-full-wetsuit', '3mm Full Wetsuit', 'Tropical diving full wetsuit with comfortable fit', 100.00, 199.99, 12, 4, 1, 1, NOW()),

-- BCDs
(4, 'BCD-001', 'cressi-start-bcd', 'Cressi Start BCD', 'Entry-level back inflation BCD with integrated weights', 175.00, 349.99, 8, 2, 1, 1, NOW()),
(4, 'BCD-002', 'scubapro-hydros-pro', 'Scubapro Hydros Pro', 'Modular BCD system with gel padding and great fit', 450.00, 899.99, 6, 2, 1, 1, NOW()),
(4, 'BCD-003', 'zeagle-ranger', 'Zeagle Ranger BCD', 'Rugged back-inflate BCD for technical diving', 400.00, 799.99, 4, 2, 1, 1, NOW()),
(4, 'BCD-004', 'aqualung-axiom', 'Aqualung Axiom BCD', 'Lightweight jacket-style BCD with great lift', 300.00, 599.99, 7, 2, 1, 1, NOW()),

-- Regulators
(5, 'REG-001', 'cressi-mc9-regulator', 'Cressi MC9 Regulator', 'Balanced piston first stage regulator with adjustable second stage', 175.00, 349.99, 10, 3, 1, 1, NOW()),
(5, 'REG-002', 'scubapro-mk25-evo', 'Scubapro MK25 EVO', 'High performance regulator for all conditions', 450.00, 899.99, 4, 2, 1, 1, NOW()),
(5, 'REG-003', 'atomic-z2', 'Atomic Z2 Regulator', 'Premium breathing regulator with comfort swivel', 350.00, 699.99, 6, 2, 1, 1, NOW()),
(5, 'REG-004', 'aqualung-legend', 'Aqualung Legend LX', 'Legendary performance regulator with supreme comfort', 400.00, 799.99, 5, 2, 1, 1, NOW()),

-- Accessories
(6, 'ACC-001', 'dive-computer-suunto-zoop', 'Dive Computer - Suunto Zoop', 'Entry level dive computer with nitrox capability', 150.00, 299.99, 15, 3, 1, 1, NOW()),
(6, 'ACC-002', 'smb-surface-marker-buoy', 'SMB Surface Marker Buoy', 'Safety surface marker buoy with oral inflator', 15.00, 29.99, 40, 10, 1, 1, NOW()),
(6, 'ACC-003', 'dive-light-1000-lumen', 'Dive Light 1000 Lumen', 'Powerful LED dive light with rechargeable battery', 75.00, 149.99, 12, 4, 1, 1, NOW()),
(6, 'ACC-004', 'dive-knife-titanium', 'Titanium Dive Knife', 'Corrosion-resistant titanium dive knife with sheath', 60.00, 119.99, 20, 5, 1, 1, NOW()),
(6, 'ACC-005', 'underwater-slate', 'Underwater Writing Slate', 'Waterproof slate for underwater communication', 8.00, 15.99, 35, 10, 1, 1, NOW()),
(6, 'ACC-006', 'mesh-gear-bag', 'Large Mesh Gear Bag', 'Heavy-duty mesh bag for dive equipment', 25.00, 49.99, 30, 8, 1, 1, NOW()),

-- Training
(7, 'TRAIN-001', 'open-water-certification', 'Open Water Certification', 'PADI Open Water Diver certification course', 250.00, 499.00, 0, 0, 0, 1, NOW()),
(7, 'TRAIN-002', 'advanced-open-water', 'Advanced Open Water', 'PADI Advanced Open Water certification', 200.00, 399.00, 0, 0, 0, 1, NOW()),
(7, 'TRAIN-003', 'rescue-diver', 'Rescue Diver Course', 'PADI Rescue Diver certification', 225.00, 449.00, 0, 0, 0, 1, NOW()),

-- Apparel
(8, 'APP-001', 'dive-shop-t-shirt', 'Nautilus T-Shirt', 'Comfortable cotton t-shirt with Nautilus logo', 8.00, 24.99, 50, 15, 1, 1, NOW()),
(8, 'APP-002', 'dive-shop-hoodie', 'Nautilus Hoodie', 'Warm fleece hoodie with embroidered logo', 20.00, 49.99, 25, 8, 1, 1, NOW()),
(8, 'APP-003', 'dive-hat', 'Nautilus Baseball Cap', 'Adjustable baseball cap with logo', 6.00, 19.99, 40, 12, 1, 1, NOW());

-- Demo Customers (B2C)
INSERT INTO customers (customer_type, first_name, last_name, email, phone, birth_date, emergency_contact_name, emergency_contact_phone, is_active, created_at) VALUES
('B2C', 'John', 'Smith', 'john.smith@example.com', '555-0101', '1985-03-15', 'Jane Smith', '555-0102', 1, NOW()),
('B2C', 'Sarah', 'Johnson', 'sarah.j@example.com', '555-0201', '1990-07-22', 'Mike Johnson', '555-0202', 1, NOW()),
('B2C', 'Michael', 'Williams', 'mwilliams@example.com', '555-0301', '1982-11-08', 'Lisa Williams', '555-0302', 1, NOW()),
('B2C', 'Emily', 'Brown', 'emily.brown@example.com', '555-0401', '1995-05-30', 'Tom Brown', '555-0402', 1, NOW()),
('B2C', 'David', 'Martinez', 'dmartinez@example.com', '555-0501', '1988-09-12', 'Maria Martinez', '555-0502', 1, NOW()),
('B2C', 'Jessica', 'Garcia', 'jgarcia@example.com', '555-0601', '1992-06-20', 'Carlos Garcia', '555-0602', 1, NOW()),
('B2C', 'Christopher', 'Lee', 'clee@example.com', '555-0701', '1987-04-18', 'Amy Lee', '555-0702', 1, NOW()),
('B2C', 'Ashley', 'Anderson', 'aanderson@example.com', '555-0801', '1994-09-25', 'Robert Anderson', '555-0802', 1, NOW());

-- Demo Customers (B2B)
INSERT INTO customers (customer_type, company_name, first_name, last_name, email, phone, tax_exempt_number, credit_terms, credit_limit, is_active, created_at) VALUES
('B2B', 'Coastal Dive Charters', 'Robert', 'Anderson', 'robert@coastaldive.com', '555-1001', '12-3456789', 'Net 30', 5000.00, 1, NOW()),
('B2B', 'Island Diving Adventures', 'Jennifer', 'Taylor', 'jennifer@islanddiving.com', '555-1101', '12-9876543', 'Net 30', 7500.00, 1, NOW()),
('B2B', 'Blue Water Excursions', 'William', 'Thomas', 'william@bluewater.com', '555-1201', '12-5555555', 'Net 15', 3000.00, 1, NOW()),
('B2B', 'Deep Sea Exploration Co', 'Jessica', 'Garcia', 'jessica@deepsea.com', '555-1301', '12-4444444', 'Net 30', 10000.00, 1, NOW());

-- Customer Addresses
INSERT INTO customer_addresses (customer_id, address_type, address_line1, city, state, postal_code, country, is_default) VALUES
(1, 'billing', '123 Ocean Drive', 'Miami', 'FL', '33139', 'US', 1),
(2, 'billing', '456 Beach Blvd', 'San Diego', 'CA', '92101', 'US', 1),
(3, 'billing', '789 Coral Way', 'Key West', 'FL', '33040', 'US', 1),
(4, 'billing', '321 Marina Circle', 'Tampa', 'FL', '33602', 'US', 1),
(5, 'billing', '654 Harbor Street', 'Fort Lauderdale', 'FL', '33301', 'US', 1),
(9, 'billing', '321 Harbor Street', 'Fort Lauderdale', 'FL', '33301', 'US', 1),
(10, 'billing', '654 Marina Drive', 'Honolulu', 'HI', '96815', 'US', 1);

-- Product Images (using placeholder images)
INSERT INTO product_images (product_id, file_path, file_name, alt_text, is_primary, sort_order) VALUES
(1, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-001.jpg', 'Cressi Big Eyes Evolution Diving Mask', 1, 1),
(2, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-002.jpg', 'Scubapro Crystal Vu Mask', 1, 1),
(3, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-003.jpg', 'Atomic Venom Frameless Mask', 1, 1),
(4, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-004.jpg', 'Aqualung Reveal X2 Mask', 1, 1),
(5, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-001.jpg', 'Mares Avanti Quattro Plus Fins', 1, 1),
(6, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-002.jpg', 'Scubapro Jet Fins', 1, 1),
(7, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-003.jpg', 'Hollis F1 Bat Fins', 1, 1),
(8, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-004.jpg', 'Atomic Smoke on the Water Fins', 1, 1),
(9, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-001.jpg', '3mm Shorty Wetsuit', 1, 1),
(10, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-002.jpg', '5mm Full Wetsuit', 1, 1),
(11, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-003.jpg', '7mm Semi-Dry Suit', 1, 1),
(12, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-004.jpg', '3mm Full Wetsuit', 1, 1),
(13, 'https://placehold.co/400x300/dc3545/ffffff?text=BCD', 'bcd-001.jpg', 'Cressi Start BCD', 1, 1),
(14, 'https://placehold.co/400x300/dc3545/ffffff?text=BCD', 'bcd-002.jpg', 'Scubapro Hydros Pro BCD', 1, 1),
(15, 'https://placehold.co/400x300/dc3545/ffffff?text=BCD', 'bcd-003.jpg', 'Zeagle Ranger BCD', 1, 1),
(16, 'https://placehold.co/400x300/dc3545/ffffff?text=BCD', 'bcd-004.jpg', 'Aqualung Axiom BCD', 1, 1),
(17, 'https://placehold.co/400x300/6f42c1/ffffff?text=Regulator', 'reg-001.jpg', 'Cressi MC9 Regulator', 1, 1),
(18, 'https://placehold.co/400x300/6f42c1/ffffff?text=Regulator', 'reg-002.jpg', 'Scubapro MK25 EVO Regulator', 1, 1),
(19, 'https://placehold.co/400x300/6f42c1/ffffff?text=Regulator', 'reg-003.jpg', 'Atomic Z2 Regulator', 1, 1),
(20, 'https://placehold.co/400x300/6f42c1/ffffff?text=Regulator', 'reg-004.jpg', 'Aqualung Legend LX Regulator', 1, 1),
(21, 'https://placehold.co/400x300/0dcaf0/ffffff?text=Dive+Computer', 'acc-001.jpg', 'Suunto Zoop Dive Computer', 1, 1),
(22, 'https://placehold.co/400x300/0dcaf0/ffffff?text=Safety+Buoy', 'acc-002.jpg', 'SMB Surface Marker Buoy', 1, 1),
(23, 'https://placehold.co/400x300/0dcaf0/ffffff?text=Dive+Light', 'acc-003.jpg', 'Dive Light 1000 Lumen', 1, 1),
(24, 'https://placehold.co/400x300/0dcaf0/ffffff?text=Dive+Knife', 'acc-004.jpg', 'Titanium Dive Knife', 1, 1),
(29, 'https://placehold.co/400x300/20c997/ffffff?text=T-Shirt', 'app-001.jpg', 'Nautilus T-Shirt', 1, 1),
(30, 'https://placehold.co/400x300/20c997/ffffff?text=Hoodie', 'app-002.jpg', 'Nautilus Hoodie', 1, 1);

-- Sample POS Transactions
INSERT INTO transactions (transaction_number, customer_id, transaction_type, subtotal, tax, total, status, transaction_date, cashier_id, created_at) VALUES
(CONCAT('TXN-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 5 DAY), '%Y%m%d'), '-001'), 1, 'sale', 159.98, 10.00, 169.98, 'completed', DATE_SUB(NOW(), INTERVAL 5 DAY), 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(CONCAT('TXN-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 4 DAY), '%Y%m%d'), '-002'), 2, 'sale', 239.99, 15.00, 254.99, 'completed', DATE_SUB(NOW(), INTERVAL 4 DAY), 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(CONCAT('TXN-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 3 DAY), '%Y%m%d'), '-003'), 3, 'sale', 499.00, 25.99, 524.99, 'completed', DATE_SUB(NOW(), INTERVAL 3 DAY), 1, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Sample POS Transaction Items
INSERT INTO transaction_items (transaction_id, product_id, item_name, item_sku, quantity, unit_price, total) VALUES
(1, 1, 'Cressi Big Eyes Evolution', 'MASK-001', 1, 89.99, 89.99),
(1, 22, 'SMB Surface Marker Buoy', 'ACC-002', 2, 29.99, 59.99),
(2, 5, 'Mares Avanti Quattro Plus', 'FIN-001', 1, 149.99, 149.99),
(2, 1, 'Cressi Big Eyes Evolution', 'MASK-001', 1, 89.99, 89.99),
(3, 27, 'Open Water Certification', 'TRAIN-001', 1, 499.00, 499.00);

-- Sample E-commerce Orders
INSERT INTO orders (order_number, customer_id, order_type, subtotal, shipping, tax, total, status, payment_status, shipping_address_line1, shipping_city, shipping_state, shipping_postal_code, shipping_country, created_at) VALUES
(CONCAT('ORD-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 10 DAY), '%Y%m%d'), '-ABC123'), 1, 'online', 299.97, 10.00, 21.70, 331.67, 'delivered', 'paid', '123 Ocean Drive', 'Miami', 'FL', '33139', 'US', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(CONCAT('ORD-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 7 DAY), '%Y%m%d'), '-DEF456'), 2, 'online', 149.99, 10.00, 11.20, 171.19, 'shipped', 'paid', '456 Beach Blvd', 'San Diego', 'CA', '92101', 'US', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(CONCAT('ORD-', DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 2 DAY), '%Y%m%d'), '-GHI789'), 3, 'online', 449.97, 0.00, 31.50, 481.47, 'processing', 'paid', '789 Coral Way', 'Key West', 'FL', '33040', 'US', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Sample Order Items
INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, unit_price, total) VALUES
(1, 1, 'Cressi Big Eyes Evolution', 'MASK-001', 2, 89.99, 179.98),
(1, 5, 'Mares Avanti Quattro Plus', 'FIN-001', 1, 119.99, 119.99),
(2, 2, 'Scubapro Crystal Vu', 'MASK-002', 1, 119.99, 119.99),
(3, 13, 'Cressi Start BCD', 'BCD-001', 1, 349.99, 349.99),
(3, 1, 'Cressi Big Eyes Evolution', 'MASK-001', 1, 89.99, 89.99);
