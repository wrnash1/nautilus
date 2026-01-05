
-- Demo Data for Nautilus Dive Shop
-- This file contains sample data for testing and demonstration purposes

-- Demo Users (password for all: password)
INSERT INTO users (id, first_name, last_name, email, password_hash, is_active, created_at) VALUES
(4, 'Jane', 'Manager', 'manager@nautilus.demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW()),
(5, 'Bob', 'Cashier', 'cashier@nautilus.demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

INSERT INTO user_roles (user_id, role_id) VALUES
(4, 2),
(5, 3);

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

-- Products (with image_url for POS display)
INSERT INTO products (category_id, sku, slug, name, description, cost_price, retail_price, stock_quantity, low_stock_threshold, track_inventory, is_active, image_url, created_at) VALUES
-- Masks
(1, 'MASK-001', 'cressi-big-eyes-evolution', 'Cressi Big Eyes Evolution', 'Wide-view diving mask with tempered glass and excellent field of vision', 45.00, 89.99, 25, 5, 1, 1, '/assets/img/products/mask.png', NOW()),
(1, 'MASK-002', 'scubapro-crystal-vu', 'Scubapro Crystal Vu', 'Single lens mask with optical clarity and low volume design', 60.00, 119.99, 15, 5, 1, 1, '/assets/img/products/mask.png', NOW()),
(1, 'MASK-003', 'atomic-venom-frameless', 'Atomic Venom Frameless', 'Ultra-clear frameless mask with superior fit and comfort', 75.00, 149.99, 12, 3, 1, 1, '/assets/img/products/mask.png', NOW()),
(1, 'MASK-004', 'aqualung-reveal-x2', 'Aqualung Reveal X2', 'Twin lens mask with easy adjustment and great vision', 50.00, 99.99, 18, 5, 1, 1, '/assets/img/products/mask.png', NOW()),

-- Fins
(2, 'FIN-001', 'mares-avanti-quattro-plus', 'Mares Avanti Quattro Plus', 'Full foot fins for warm water diving with excellent propulsion', 75.00, 149.99, 30, 5, 1, 1, '/assets/img/products/fins.png', NOW()),
(2, 'FIN-002', 'scubapro-jet-fins', 'Scubapro Jet Fins', 'Heavy duty diving fins for technical and professional diving', 95.00, 189.99, 20, 5, 1, 1, '/assets/img/products/fins.png', NOW()),
(2, 'FIN-003', 'hollis-f1-bat-fins', 'Hollis F1 Bat Fins', 'High performance dive fins with spring straps included', 115.00, 229.99, 8, 3, 1, 1, '/assets/img/products/fins.png', NOW()),
(2, 'FIN-004', 'atomic-smoke-on-the-water', 'Atomic Smoke on the Water', 'Split fin design for effortless kicking and maximum efficiency', 125.00, 249.99, 10, 3, 1, 1, '/assets/img/products/fins.png', NOW()),

-- Wetsuits
(3, 'SUIT-001', '3mm-shorty-wetsuit', '3mm Shorty Wetsuit', 'Warm water shorty wetsuit with back zip', 75.00, 149.99, 18, 5, 1, 1, '/assets/img/products/wetsuit.png', NOW()),
(3, 'SUIT-002', '5mm-full-wetsuit', '5mm Full Wetsuit', 'Full body 5mm wetsuit for temperate waters', 150.00, 299.99, 10, 3, 1, 1, '/assets/img/products/wetsuit.png', NOW()),
(3, 'SUIT-003', '7mm-semi-dry-suit', '7mm Semi-Dry Suit', 'Cold water semi-dry suit with wrist and ankle seals', 250.00, 499.99, 5, 2, 1, 1, '/assets/img/products/wetsuit.png', NOW()),
(3, 'SUIT-004', '3mm-full-wetsuit', '3mm Full Wetsuit', 'Tropical diving full wetsuit with comfortable fit', 100.00, 199.99, 12, 4, 1, 1, '/assets/img/products/wetsuit.png', NOW()),

-- BCDs
(4, 'BCD-001', 'cressi-start-bcd', 'Cressi Start BCD', 'Entry-level back inflation BCD with integrated weights', 175.00, 349.99, 8, 2, 1, 1, '/assets/img/products/bcd.png', NOW()),
(4, 'BCD-002', 'scubapro-hydros-pro', 'Scubapro Hydros Pro', 'Modular BCD system with gel padding and great fit', 450.00, 899.99, 6, 2, 1, 1, '/assets/img/products/bcd.png', NOW()),
(4, 'BCD-003', 'zeagle-ranger', 'Zeagle Ranger BCD', 'Rugged back-inflate BCD for technical diving', 400.00, 799.99, 4, 2, 1, 1, '/assets/img/products/bcd.png', NOW()),
(4, 'BCD-004', 'aqualung-axiom', 'Aqualung Axiom BCD', 'Lightweight jacket-style BCD with great lift', 300.00, 599.99, 7, 2, 1, 1, '/assets/img/products/bcd.png', NOW()),

-- Regulators
(5, 'REG-001', 'cressi-mc9-regulator', 'Cressi MC9 Regulator', 'Balanced piston first stage regulator with adjustable second stage', 175.00, 349.99, 10, 3, 1, 1, '/assets/img/products/regulator.png', NOW()),
(5, 'REG-002', 'scubapro-mk25-evo', 'Scubapro MK25 EVO', 'High performance regulator for all conditions', 450.00, 899.99, 4, 2, 1, 1, '/assets/img/products/regulator.png', NOW()),
(5, 'REG-003', 'atomic-z2', 'Atomic Z2 Regulator', 'Premium breathing regulator with comfort swivel', 350.00, 699.99, 6, 2, 1, 1, '/assets/img/products/regulator.png', NOW()),
(5, 'REG-004', 'aqualung-legend', 'Aqualung Legend LX', 'Legendary performance regulator with supreme comfort', 400.00, 799.99, 5, 2, 1, 1, '/assets/img/products/regulator.png', NOW()),

-- Accessories
(6, 'ACC-001', 'dive-computer-suunto-zoop', 'Dive Computer - Suunto Zoop', 'Entry level dive computer with nitrox capability', 150.00, 299.99, 15, 3, 1, 1, '/assets/img/products/computer.png', NOW()),
(6, 'ACC-002', 'smb-surface-marker-buoy', 'SMB Surface Marker Buoy', 'Safety surface marker buoy with oral inflator', 15.00, 29.99, 40, 10, 1, 1, '/assets/img/products/snorkel.png', NOW()),
(6, 'ACC-003', 'dive-light-1000-lumen', 'Dive Light 1000 Lumen', 'Powerful LED dive light with rechargeable battery', 75.00, 149.99, 12, 4, 1, 1, '/assets/img/products/light.png', NOW()),
(6, 'ACC-004', 'dive-knife-titanium', 'Titanium Dive Knife', 'Corrosion-resistant titanium dive knife with sheath', 60.00, 119.99, 20, 5, 1, 1, '/assets/img/products/light.png', NOW()),
(6, 'ACC-005', 'underwater-slate', 'Underwater Writing Slate', 'Waterproof slate for underwater communication', 8.00, 15.99, 35, 10, 1, 1, '/assets/img/products/snorkel.png', NOW()),
(6, 'ACC-006', 'mesh-gear-bag', 'Large Mesh Gear Bag', 'Heavy-duty mesh bag for dive equipment', 25.00, 49.99, 30, 8, 1, 1, '/assets/img/products/tank.png', NOW()),

-- Training
(7, 'TRAIN-001', 'open-water-certification', 'Open Water Certification', 'PADI Open Water Diver certification course', 250.00, 499.00, 0, 0, 0, 1, '/assets/img/products/tank.png', NOW()),
(7, 'TRAIN-002', 'advanced-open-water', 'Advanced Open Water', 'PADI Advanced Open Water certification', 200.00, 399.00, 0, 0, 0, 1, '/assets/img/products/tank.png', NOW()),
(7, 'TRAIN-003', 'rescue-diver', 'Rescue Diver Course', 'PADI Rescue Diver certification', 225.00, 449.00, 0, 0, 0, 1, '/assets/img/products/tank.png', NOW()),

-- Apparel
(8, 'APP-001', 'dive-shop-t-shirt', 'Nautilus T-Shirt', 'Comfortable cotton t-shirt with Nautilus logo', 8.00, 24.99, 50, 15, 1, 1, '/assets/img/products/snorkel.png', NOW()),
(8, 'APP-002', 'dive-shop-hoodie', 'Nautilus Hoodie', 'Warm fleece hoodie with embroidered logo', 20.00, 49.99, 25, 8, 1, 1, '/assets/img/products/snorkel.png', NOW()),
(8, 'APP-003', 'dive-hat', 'Nautilus Baseball Cap', 'Adjustable baseball cap with logo', 6.00, 19.99, 40, 12, 1, 1, '/assets/img/products/snorkel.png', NOW());


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

-- ================================================
-- 7. COURSE SCHEDULES (for enrollment testing)
-- ================================================

INSERT INTO course_schedules (id, course_id, instructor_id, start_date, end_date, start_time, end_time, location, max_students, current_enrollment, status) VALUES
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), '09:00:00', '17:00:00', 'Main Classroom / Pool', 6, 4, 'completed'),
(2, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '17:00:00', 'Main Classroom / Pool', 6, 3, 'in_progress'),
(3, 2, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), '08:00:00', '16:00:00', 'Blue Hole Dive Site', 6, 2, 'scheduled'),
(4, 5, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:00:00', '21:00:00', 'Classroom Only', 8, 4, 'scheduled'),
(5, 3, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 17 DAY), '09:00:00', '17:00:00', 'Pool / Open Water', 6, 1, 'scheduled');

-- ================================================
-- 8. COURSE ENROLLMENTS
-- ================================================

INSERT INTO course_enrollments (id, schedule_id, customer_id, enrollment_date, status, amount_paid, payment_status) VALUES
-- Completed class (schedule 1)
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'completed', 499.00, 'paid'),
(2, 1, 2, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'completed', 499.00, 'paid'),
(3, 1, 3, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'completed', 499.00, 'paid'),
(4, 1, 4, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'dropped', 250.00, 'refunded'),
-- Current class (schedule 2)
(5, 2, 5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'in_progress', 499.00, 'paid'),
(6, 2, 6, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'in_progress', 499.00, 'paid'),
(7, 2, 7, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'enrolled', 250.00, 'partial'),
-- Upcoming classes
(8, 3, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'enrolled', 399.00, 'paid'),
(9, 3, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'enrolled', 399.00, 'paid'),
(10, 4, 3, CURDATE(), 'enrolled', 199.00, 'paid'),
(11, 4, 4, CURDATE(), 'enrolled', 199.00, 'paid'),
(12, 4, 5, CURDATE(), 'enrolled', 199.00, 'paid'),
(13, 4, 8, CURDATE(), 'enrolled', 199.00, 'paid'),
(14, 5, 6, CURDATE(), 'enrolled', 449.00, 'paid');

-- ================================================
-- 9. COURSE STUDENT RECORDS (PADI Progress Tracking)
-- ================================================

INSERT INTO course_student_records (enrollment_id, form_type, knowledge_status, confined_water_status, open_water_status, overall_status, instructor_id, final_exam_score) VALUES
-- Completed students
(1, 'course_record', 'completed', 'completed', 'completed', 'completed', 1, 92.00),
(2, 'course_record', 'completed', 'completed', 'completed', 'completed', 1, 88.00),
(3, 'course_record', 'completed', 'completed', 'completed', 'completed', 1, 95.00),
-- Current students in training
(5, 'course_record', 'completed', 'in_progress', 'pending', 'in_training', 1, 85.00),
(6, 'course_record', 'completed', 'in_progress', 'pending', 'in_training', 1, 90.00),
(7, 'course_record', 'in_progress', 'pending', 'pending', 'enrolled', 1, NULL);

-- ================================================
-- 10. WORK ORDERS (Equipment Service)
-- ================================================

INSERT INTO work_orders (work_order_number, customer_id, equipment_type, brand, model, serial_number, issue_description, estimated_cost, actual_cost, status, priority, assigned_to, created_by) VALUES
('WO-2026-001', 1, 'Regulator', 'Scubapro', 'MK25 EVO', 'SCB-12345', 'Annual regulator service and inspection', 125.00, 125.00, 'completed', 'medium', 1, 1),
('WO-2026-002', 2, 'BCD', 'Aqualung', 'Axiom', 'AQL-67890', 'Inflate/deflate valve leaking, needs replacement', 85.00, 92.00, 'completed', 'high', 1, 1),
('WO-2026-003', 3, 'Tank', 'Luxfer', '80cf Aluminum', 'LUX-11111', 'Hydrostatic testing due', 45.00, NULL, 'in_progress', 'medium', 1, 1),
('WO-2026-004', 4, 'Tank', 'Faber', '100cf Steel', 'FAB-22222', 'Hydrostatic testing and VIP', 55.00, NULL, 'pending', 'low', 1, 1),
('WO-2026-005', 5, 'Regulator', 'Atomic', 'Z2', 'ATM-33333', 'Second stage free-flow issue', 75.00, NULL, 'waiting_parts', 'high', 1, 1),
('WO-2026-006', 1, 'Dive Computer', 'Suunto', 'D5', 'SUN-44444', 'Battery replacement', 35.00, 35.00, 'ready_pickup', 'low', 1, 1),
('WO-2026-007', 6, 'Wetsuit', 'Scubapro', 'Everflex 5mm', NULL, 'Zipper repair and seam resealing', 65.00, NULL, 'in_progress', 'medium', 1, 1),
('WO-2026-008', 7, 'Tank', 'Worthington', '80cf Aluminum', 'WOR-55555', 'Hydro test - 4 tanks', 180.00, NULL, 'pending', 'medium', 1, 1),
('WO-2026-009', 8, 'Regulator', 'Cressi', 'MC9', 'CRS-66666', 'Complete overhaul and parts kit', 150.00, NULL, 'pending', 'low', 1, 1),
('WO-2026-010', 2, 'BCD', 'Zeagle', 'Ranger', 'ZGL-77777', 'Bladder leak inspection', 45.00, NULL, 'in_progress', 'high', 1, 1);

-- ================================================
-- 11. WORK ORDER ITEMS (Service Line Items)
-- ================================================

INSERT INTO work_order_items (work_order_id, item_type, description, quantity, unit_price, total) VALUES
(1, 'service', 'Regulator Annual Service', 1, 95.00, 95.00),
(1, 'part', 'Service Kit - MK25', 1, 30.00, 30.00),
(2, 'service', 'BCD Valve Diagnosis', 1, 35.00, 35.00),
(2, 'part', 'Dump Valve Assembly', 1, 42.00, 42.00),
(2, 'labor', 'Installation Labor', 1, 15.00, 15.00),
(3, 'service', 'Hydrostatic Test', 1, 45.00, 45.00),
(6, 'service', 'Battery Replacement', 1, 15.00, 15.00),
(6, 'part', 'Suunto D5 Battery Kit', 1, 20.00, 20.00);

-- ================================================
-- 12. TODAY'S TRANSACTIONS (for Dashboard Testing)
-- ================================================

INSERT INTO transactions (transaction_number, customer_id, transaction_type, subtotal, tax, total, status, transaction_date, cashier_id, created_at) VALUES
(CONCAT('TXN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-101'), 1, 'sale', 289.98, 23.95, 313.93, 'completed', NOW(), 1, NOW()),
(CONCAT('TXN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-102'), 2, 'sale', 149.99, 12.37, 162.36, 'completed', NOW(), 1, NOW()),
(CONCAT('TXN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-103'), 3, 'sale', 499.00, 41.17, 540.17, 'completed', NOW(), 1, NOW()),
(CONCAT('TXN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-104'), 4, 'sale', 89.99, 7.42, 97.41, 'completed', NOW(), 1, NOW()),
(CONCAT('TXN-', DATE_FORMAT(NOW(), '%Y%m%d'), '-105'), 5, 'sale', 649.99, 53.62, 703.61, 'completed', NOW(), 1, NOW());

-- Today's transaction items
INSERT INTO transaction_items (transaction_id, product_id, item_name, item_sku, quantity, unit_price, total) VALUES
((SELECT MAX(id)-4 FROM transactions), 1, 'Cressi Big Eyes Evolution', 'MASK-001', 1, 89.99, 89.99),
((SELECT MAX(id)-4 FROM transactions), 5, 'Mares Avanti Quattro Plus', 'FIN-001', 1, 149.99, 149.99),
((SELECT MAX(id)-4 FROM transactions), 22, 'SMB Surface Marker Buoy', 'ACC-002', 1, 29.99, 29.99),
((SELECT MAX(id)-3 FROM transactions), 2, 'Scubapro Crystal Vu', 'MASK-002', 1, 119.99, 119.99),
((SELECT MAX(id)-3 FROM transactions), 22, 'SMB Surface Marker Buoy', 'ACC-002', 1, 29.99, 29.99),
((SELECT MAX(id)-2 FROM transactions), 27, 'Open Water Certification', 'TRAIN-001', 1, 499.00, 499.00),
((SELECT MAX(id)-1 FROM transactions), 1, 'Cressi Big Eyes Evolution', 'MASK-001', 1, 89.99, 89.99),
((SELECT MAX(id) FROM transactions), 14, 'Scubapro Hydros Pro', 'BCD-002', 1, 649.99, 649.99);

-- ================================================
-- 13. TRIP SCHEDULES & BOOKINGS
-- ================================================

INSERT INTO trip_schedules (id, trip_id, departure_date, return_date, departure_location, max_participants, current_bookings, status) VALUES
(1, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Nautilus Dive Shop', 20, 6, 'confirmed'),
(2, 2, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Nautilus Dive Shop', 12, 4, 'confirmed'),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 37 DAY), 'DFW Airport', 16, 2, 'scheduled'),
(4, 4, DATE_ADD(CURDATE(), INTERVAL 45 DAY), DATE_ADD(CURDATE(), INTERVAL 50 DAY), 'Houston Airport', 20, 8, 'confirmed');

INSERT INTO trip_bookings (schedule_id, customer_id, booking_date, status, total_amount, deposit_paid, balance_paid, payment_status) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'confirmed', 149.00, 149.00, 0.00, 'paid'),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'confirmed', 149.00, 149.00, 0.00, 'paid'),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'confirmed', 149.00, 149.00, 0.00, 'paid'),
(1, 7, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'confirmed', 149.00, 149.00, 0.00, 'paid'),
(1, 8, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'confirmed', 149.00, 75.00, 0.00, 'deposit'),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'pending', 149.00, 0.00, 0.00, 'pending'),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 21 DAY), 'confirmed', 199.00, 199.00, 0.00, 'paid'),
(2, 5, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 'confirmed', 199.00, 199.00, 0.00, 'paid'),
(2, 6, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'confirmed', 199.00, 100.00, 0.00, 'deposit'),
(2, 3, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'pending', 199.00, 0.00, 0.00, 'pending'),
(3, 1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'confirmed', 2499.00, 500.00, 0.00, 'deposit'),
(3, 2, DATE_SUB(CURDATE(), INTERVAL 28 DAY), 'confirmed', 2499.00, 500.00, 0.00, 'deposit');

