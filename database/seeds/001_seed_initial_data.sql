

INSERT INTO roles (name, description) VALUES
('admin', 'Full system access with all permissions'),
('manager', 'Manager access - can manage inventory, customers, and reports but not users'),
('cashier', 'Basic POS access - can process sales and view customers');


INSERT INTO permissions (name, display_name, module, description) VALUES
('dashboard.view', 'View Dashboard', 'dashboard', 'View dashboard'),
('dashboard.export', 'Export Reports', 'dashboard', 'Export reports'),

('pos.view', 'View POS', 'pos', 'View POS interface'),
('pos.create', 'Create Transaction', 'pos', 'Create transactions'),
('pos.void', 'Void Transaction', 'pos', 'Void transactions'),
('pos.refund', 'Refund Transaction', 'pos', 'Refund transactions'),

('customers.view', 'View Customers', 'customers', 'View customers'),
('customers.create', 'Create Customer', 'customers', 'Create customers'),
('customers.edit', 'Edit Customer', 'customers', 'Edit customers'),
('customers.delete', 'Delete Customer', 'customers', 'Delete customers'),
('customers.export', 'Export Customers', 'customers', 'Export customer data'),

('products.view', 'View Products', 'products', 'View products'),
('products.create', 'Create Product', 'products', 'Create products'),
('products.edit', 'Edit Product', 'products', 'Edit products'),
('products.delete', 'Delete Product', 'products', 'Delete products'),
('products.adjust_stock', 'Adjust Stock', 'products', 'Adjust stock levels'),

('categories.view', 'View Categories', 'categories', 'View categories'),
('categories.create', 'Create Category', 'categories', 'Create categories'),
('categories.edit', 'Edit Category', 'categories', 'Edit categories'),
('categories.delete', 'Delete Category', 'categories', 'Delete categories'),

('users.view', 'View Users', 'users', 'View users'),
('users.create', 'Create User', 'users', 'Create users'),
('users.edit', 'Edit User', 'users', 'Edit users'),
('users.delete', 'Delete User', 'users', 'Delete users'),

('rentals.view', 'View Rentals', 'rentals', 'View rental equipment and reservations'),
('rentals.create', 'Create Rental', 'rentals', 'Create reservations'),
('rentals.edit', 'Edit Rental', 'rentals', 'Edit equipment and reservations'),
('rentals.delete', 'Delete Rental', 'rentals', 'Delete equipment'),

('courses.view', 'View Courses', 'courses', 'View courses and schedules'),
('courses.create', 'Create Course', 'courses', 'Create courses and enrollments'),
('courses.edit', 'Edit Course', 'courses', 'Edit courses and schedules'),
('courses.delete', 'Delete Course', 'courses', 'Delete courses'),

('trips.view', 'View Trips', 'trips', 'View trips and bookings'),
('trips.create', 'Create Trip', 'trips', 'Create trips and bookings'),
('trips.edit', 'Edit Trip', 'trips', 'Edit trips and schedules'),
('trips.delete', 'Delete Trip', 'trips', 'Delete trips'),

('workorders.view', 'View Work Orders', 'workorders', 'View work orders'),
('workorders.create', 'Create Work Order', 'workorders', 'Create work orders'),
('workorders.edit', 'Edit Work Order', 'workorders', 'Edit work orders'),
('workorders.delete', 'Delete Work Order', 'workorders', 'Delete work orders'),

('orders.view', 'View Orders', 'orders', 'View orders'),
('orders.create', 'Create Order', 'orders', 'Create orders'),
('orders.edit', 'Edit Order', 'orders', 'Edit order details'),
('orders.ship', 'Ship Order', 'orders', 'Process shipments'),
('orders.cancel', 'Cancel Order', 'orders', 'Cancel orders'),

('shop.browse', 'Browse Shop', 'shop', 'Browse products in online store'),
('shop.checkout', 'Checkout', 'shop', 'Complete checkout and place orders');

INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE module != 'users' AND name != 'orders.cancel';

INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions 
WHERE name IN ('pos.view', 'pos.create', 'customers.view', 'dashboard.view', 'orders.view', 'shop.browse', 'shop.checkout');



INSERT INTO users (id, first_name, last_name, email, password_hash, is_active, created_at) VALUES
(1, 'Admin', 'User', 'admin@nautilus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW()),
(2, 'Manager', 'Smith', 'manager@nautilus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW()),
(3, 'Cashier', 'Jones', 'cashier@nautilus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1),
(2, 2),
(3, 3);


INSERT INTO rental_categories (name, description) VALUES
('BCDs', 'Buoyancy Control Devices'),
('Regulators', 'Regulators and octopus'),
('Wetsuits', 'Wetsuits and thermal protection'),
('Tanks', 'Scuba tanks and cylinders'),
('Fins', 'Diving fins'),
('Masks', 'Masks and snorkels'),
('Weights', 'Weight belts and systems'),
('Accessories', 'Dive computers, gauges, and accessories');

INSERT INTO rental_equipment (category_id, equipment_code, name, size, daily_rate, weekly_rate, status, `condition`) VALUES
(1, 'BCD-001', 'Cressi Start BCD', 'Medium', 25.00, 125.00, 'available', 'good'),
(1, 'BCD-002', 'Scubapro Hydros Pro BCD', 'Large', 30.00, 150.00, 'available', 'excellent'),
(2, 'REG-001', 'Cressi MC9 Regulator', NULL, 30.00, 150.00, 'available', 'excellent'),
(2, 'REG-002', 'Scubapro MK25 Regulator', NULL, 35.00, 175.00, 'available', 'good'),
(3, 'SUIT-001', '5mm Full Wetsuit', 'Large', 15.00, 75.00, 'available', 'good'),
(3, 'SUIT-002', '3mm Shorty Wetsuit', 'Medium', 12.00, 60.00, 'available', 'fair'),
(4, 'TANK-001', 'Aluminum 80cf Tank', NULL, 10.00, 50.00, 'available', 'good'),
(4, 'TANK-002', 'Steel 100cf Tank', NULL, 12.00, 60.00, 'available', 'excellent');

INSERT INTO courses (course_code, name, description, duration_days, max_students, price) VALUES
('OW', 'Open Water Diver', 'PADI Open Water certification course for beginners', 3, 6, 499.00),
('AOW', 'Advanced Open Water', 'PADI Advanced Open Water course', 2, 6, 399.00),
('RESCUE', 'Rescue Diver', 'PADI Rescue Diver course with emergency management', 3, 6, 449.00);

INSERT INTO trips (trip_code, name, destination, description, duration_days, max_participants, price) VALUES
('REEF-01', 'Local Reef Dive', 'Key Largo Reef', 'Explore vibrant coral reefs and marine life', 1, 20, 149.00),
('WRECK-01', 'Wreck Diving Adventure', 'USS Spiegel Grove', 'Dive the famous USS Spiegel Grove wreck', 1, 12, 199.00),
('BAH-01', 'Bahamas Dive Trip', 'Nassau, Bahamas', '7-day diving expedition in crystal clear waters', 7, 16, 2499.00);


INSERT INTO product_categories (name, slug, description, is_active, created_at) VALUES
('Masks', 'masks', 'Diving masks and snorkels', 1, NOW()),
('Fins', 'fins', 'Diving fins and accessories', 1, NOW()),
('Wetsuits', 'wetsuits', 'Wetsuits, drysuits, and thermal protection', 1, NOW()),
('BCDs', 'bcds', 'Buoyancy Control Devices', 1, NOW()),
('Regulators', 'regulators', 'Regulators and octopus', 1, NOW()),
('Accessories', 'accessories', 'Dive accessories, gauges, and computers', 1, NOW()),
('Training', 'training', 'Training materials and certification fees', 1, NOW());


INSERT INTO products (category_id, sku, slug, name, description, cost_price, retail_price, stock_quantity, low_stock_threshold, track_inventory, is_active, created_at) VALUES
(1, 'MASK-001', 'cressi-big-eyes-evolution', 'Cressi Big Eyes Evolution', 'Wide-view diving mask with tempered glass', 45.00, 89.99, 25, 5, 1, 1, NOW()),
(1, 'MASK-002', 'scubapro-crystal-vu', 'Scubapro Crystal Vu', 'Single lens mask with optical clarity', 60.00, 119.99, 15, 5, 1, 1, NOW()),
(1, 'MASK-003', 'atomic-venom-frameless', 'Atomic Venom Frameless', 'Ultra-clear frameless mask', 75.00, 149.99, 12, 3, 1, 1, NOW()),

(2, 'FIN-001', 'mares-avanti-quattro-plus', 'Mares Avanti Quattro Plus', 'Full foot fins for warm water diving', 75.00, 149.99, 30, 5, 1, 1, NOW()),
(2, 'FIN-002', 'scubapro-jet-fins', 'Scubapro Jet Fins', 'Heavy duty diving fins', 95.00, 189.99, 20, 5, 1, 1, NOW()),
(2, 'FIN-003', 'hollis-f1-bat-fins', 'Hollis F1 Bat Fins', 'High performance dive fins', 115.00, 229.99, 8, 3, 1, 1, NOW()),

(3, 'SUIT-001', '3mm-shorty-wetsuit', '3mm Shorty Wetsuit', 'Warm water shorty wetsuit', 75.00, 149.99, 18, 5, 1, 1, NOW()),
(3, 'SUIT-002', '5mm-full-wetsuit', '5mm Full Wetsuit', 'Full body 5mm wetsuit', 150.00, 299.99, 10, 3, 1, 1, NOW()),
(3, 'SUIT-003', '7mm-semi-dry-suit', '7mm Semi-Dry Suit', 'Cold water semi-dry suit', 250.00, 499.99, 5, 2, 1, 1, NOW()),

(4, 'BCD-001', 'cressi-start-bcd', 'Cressi Start BCD', 'Entry-level back inflation BCD', 175.00, 349.99, 8, 2, 1, 1, NOW()),
(4, 'BCD-002', 'scubapro-hydros-pro', 'Scubapro Hydros Pro', 'Modular BCD system', 450.00, 899.99, 6, 2, 1, 1, NOW()),

(5, 'REG-001', 'cressi-mc9-regulator', 'Cressi MC9 Regulator', 'Balanced piston first stage regulator', 175.00, 349.99, 10, 3, 1, 1, NOW()),
(5, 'REG-002', 'scubapro-mk25-evo', 'Scubapro MK25 EVO', 'High performance regulator', 450.00, 899.99, 4, 2, 1, 1, NOW()),

(6, 'ACC-001', 'dive-computer-suunto-zoop', 'Dive Computer - Suunto Zoop', 'Entry level dive computer', 150.00, 299.99, 15, 3, 1, 1, NOW()),
(6, 'ACC-002', 'smb-surface-marker-buoy', 'SMB Surface Marker Buoy', 'Safety surface marker', 15.00, 29.99, 40, 10, 1, 1, NOW()),

(7, 'TRAIN-001', 'open-water-certification', 'Open Water Certification', 'PADI Open Water Diver certification course', 250.00, 499.00, 0, 0, 0, 1, NOW());


INSERT INTO customers (customer_type, first_name, last_name, email, phone, birth_date, emergency_contact_name, emergency_contact_phone, is_active, created_at) VALUES
('B2C', 'John', 'Smith', 'john.smith@email.com', '555-0101', '1985-03-15', 'Jane Smith', '555-0102', 1, NOW()),
('B2C', 'Sarah', 'Johnson', 'sarah.j@email.com', '555-0201', '1990-07-22', 'Mike Johnson', '555-0202', 1, NOW()),
('B2C', 'Michael', 'Williams', 'mwilliams@email.com', '555-0301', '1982-11-08', 'Lisa Williams', '555-0302', 1, NOW()),
('B2C', 'Emily', 'Brown', 'emily.brown@email.com', '555-0401', '1995-05-30', 'Tom Brown', '555-0402', 1, NOW()),
('B2C', 'David', 'Martinez', 'dmartinez@email.com', '555-0501', '1988-09-12', 'Maria Martinez', '555-0502', 1, NOW());

INSERT INTO customers (customer_type, company_name, first_name, last_name, email, phone, tax_exempt_number, credit_terms, credit_limit, is_active, created_at) VALUES
('B2B', 'Coastal Dive Charters', 'Robert', 'Anderson', 'robert@coastaldive.com', '555-1001', '12-3456789', 'Net 30', 5000.00, 1, NOW()),
('B2B', 'Island Diving Adventures', 'Jennifer', 'Taylor', 'jennifer@islanddiving.com', '555-1101', '12-9876543', 'Net 30', 7500.00, 1, NOW()),
('B2B', 'Blue Water Excursions', 'William', 'Thomas', 'william@bluewater.com', '555-1201', '12-5555555', 'Net 15', 3000.00, 1, NOW()),
('B2B', 'Deep Sea Exploration Co', 'Jessica', 'Garcia', 'jessica@deepsea.com', '555-1301', '12-4444444', 'Net 30', 10000.00, 1, NOW()),
('B2B', 'Adventure Diving Inc', 'Christopher', 'Lee', 'chris@adventurediving.com', '555-1401', '12-3333333', 'Net 45', 15000.00, 1, NOW());

INSERT INTO customer_addresses (customer_id, address_type, address_line1, city, state, postal_code, country, is_default) VALUES
(1, 'billing', '123 Ocean Drive', 'Miami', 'FL', '33139', 'US', 1),
(2, 'billing', '456 Beach Blvd', 'San Diego', 'CA', '92101', 'US', 1),
(3, 'billing', '789 Coral Way', 'Key West', 'FL', '33040', 'US', 1),
(6, 'billing', '321 Harbor Street', 'Fort Lauderdale', 'FL', '33301', 'US', 1),
(7, 'billing', '654 Marina Drive', 'Honolulu', 'HI', '96815', 'US', 1);

INSERT INTO product_images (product_id, file_path, file_name, alt_text, is_primary, sort_order) VALUES
(1, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-001.jpg', 'Cressi Big Eyes Evolution Diving Mask', 1, 1),
(2, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-002.jpg', 'Scubapro Crystal Vu Mask', 1, 1),
(3, 'https://placehold.co/400x300/0d6efd/ffffff?text=Diving+Mask', 'mask-003.jpg', 'Atomic Venom Frameless Mask', 1, 1),
(4, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-001.jpg', 'Mares Avanti Quattro Plus Fins', 1, 1),
(5, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-002.jpg', 'Scubapro Jet Fins', 1, 1),
(6, 'https://placehold.co/400x300/198754/ffffff?text=Diving+Fins', 'fin-003.jpg', 'Hollis F1 Bat Fins', 1, 1),
(7, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-001.jpg', '3mm Shorty Wetsuit', 1, 1),
(8, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-002.jpg', '5mm Full Wetsuit', 1, 1),
(9, 'https://placehold.co/400x300/fd7e14/ffffff?text=Wetsuit', 'suit-003.jpg', '7mm Semi-Dry Suit', 1, 1),
(10, 'https://placehold.co/400x300/dc3545/ffffff?text=BCD', 'bcd-001.jpg', 'Cressi Start BCD', 1, 1),
(11, 'https://placehold.co/400x300/dc3545/ffffff?text=BCD', 'bcd-002.jpg', 'Scubapro Hydros Pro BCD', 1, 1),
(12, 'https://placehold.co/400x300/6f42c1/ffffff?text=Regulator', 'reg-001.jpg', 'Cressi MC9 Regulator', 1, 1),
(13, 'https://placehold.co/400x300/6f42c1/ffffff?text=Regulator', 'reg-002.jpg', 'Scubapro MK25 EVO Regulator', 1, 1),
(14, 'https://placehold.co/400x300/0dcaf0/ffffff?text=Dive+Computer', 'acc-001.jpg', 'Suunto Zoop Dive Computer', 1, 1),
(15, 'https://placehold.co/400x300/0dcaf0/ffffff?text=Safety+Buoy', 'acc-002.jpg', 'SMB Surface Marker Buoy', 1, 1),
(16, 'https://placehold.co/400x300/ffc107/ffffff?text=Training', 'train-001.jpg', 'Open Water Certification Course', 1, 1);

INSERT INTO orders (order_number, customer_id, order_type, subtotal, shipping, tax, total, status, payment_status, shipping_address_line1, shipping_city, shipping_state, shipping_postal_code, shipping_country, created_at) VALUES
('ORD-20251010-ABC123', 1, 'online', 299.97, 10.00, 21.70, 331.67, 'delivered', 'paid', '123 Ocean Drive', 'Miami', 'FL', '33139', 'US', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('ORD-20251009-DEF456', 2, 'online', 149.99, 10.00, 11.20, 171.19, 'shipped', 'paid', '456 Beach Blvd', 'San Diego', 'CA', '92101', 'US', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('ORD-20251011-GHI789', 3, 'online', 449.97, 0.00, 31.50, 481.47, 'processing', 'paid', '789 Coral Way', 'Key West', 'FL', '33040', 'US', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, unit_price, total) VALUES
(1, 1, 'Cressi Big Eyes Evolution', 'MASK-001', 2, 89.99, 179.98),
(1, 4, 'Mares Avanti Quattro Plus', 'FIN-001', 1, 119.99, 119.99),
(2, 2, 'Scubapro Crystal Vu', 'MASK-002', 1, 119.99, 119.99),
(3, 11, 'Cressi Start BCD', 'BCD-001', 1, 349.99, 349.99),
(3, 1, 'Cressi Big Eyes Evolution', 'MASK-001', 1, 89.99, 89.99);
