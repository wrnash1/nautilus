

INSERT INTO roles (name, display_name, description) VALUES
('admin', 'Admin', 'Full system access with all permissions'),
('manager', 'Manager', 'Manager access - can manage inventory, customers, and reports but not users'),
('cashier', 'Cashier', 'Basic POS access - can process sales and view customers');

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
('workorders.delete', 'Delete Work Order', 'workorders', 'Delete work orders');

INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE module != 'users';

INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions 
WHERE name IN ('pos.view', 'pos.create', 'customers.view', 'dashboard.view');



INSERT INTO users (role_id, first_name, last_name, email, password_hash, is_active, created_at) VALUES
(1, 'Admin', 'User', 'admin@nautilus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW()),
(2, 'Manager', 'Smith', 'manager@nautilus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW()),
(3, 'Cashier', 'Jones', 'cashier@nautilus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());


INSERT INTO rental_categories (name, description) VALUES
('BCDs', 'Buoyancy Control Devices'),
('Regulators', 'Regulators and octopus'),
('Wetsuits', 'Wetsuits and thermal protection'),
('Tanks', 'Scuba tanks and cylinders'),
('Fins', 'Diving fins'),
('Masks', 'Masks and snorkels'),
('Weights', 'Weight belts and systems'),
('Accessories', 'Dive computers, gauges, and accessories');

INSERT INTO rental_equipment (category_id, equipment_code, name, size, daily_rate, weekly_rate, status, `condition`, created_by) VALUES
(1, 'BCD-001', 'Cressi Start BCD', 'Medium', 25.00, 125.00, 'available', 'good', 1),
(1, 'BCD-002', 'Scubapro Hydros Pro BCD', 'Large', 30.00, 150.00, 'available', 'excellent', 1),
(2, 'REG-001', 'Cressi MC9 Regulator', NULL, 30.00, 150.00, 'available', 'excellent', 1),
(2, 'REG-002', 'Scubapro MK25 Regulator', NULL, 35.00, 175.00, 'available', 'good', 1),
(3, 'SUIT-001', '5mm Full Wetsuit', 'Large', 15.00, 75.00, 'available', 'good', 1),
(3, 'SUIT-002', '3mm Shorty Wetsuit', 'Medium', 12.00, 60.00, 'available', 'fair', 1),
(4, 'TANK-001', 'Aluminum 80cf Tank', NULL, 10.00, 50.00, 'available', 'good', 1),
(4, 'TANK-002', 'Steel 100cf Tank', NULL, 12.00, 60.00, 'available', 'excellent', 1);

INSERT INTO courses (course_code, name, description, duration_days, max_students, price, created_by) VALUES
('OW', 'Open Water Diver', 'PADI Open Water certification course for beginners', 3, 6, 499.00, 1),
('AOW', 'Advanced Open Water', 'PADI Advanced Open Water course', 2, 6, 399.00, 1),
('RESCUE', 'Rescue Diver', 'PADI Rescue Diver course with emergency management', 3, 6, 449.00, 1);

INSERT INTO trips (trip_code, name, destination, description, duration_days, max_participants, price, created_by) VALUES
('REEF-01', 'Local Reef Dive', 'Key Largo Reef', 'Explore vibrant coral reefs and marine life', 1, 20, 149.00, 1),
('WRECK-01', 'Wreck Diving Adventure', 'USS Spiegel Grove', 'Dive the famous USS Spiegel Grove wreck', 1, 12, 199.00, 1),
('BAH-01', 'Bahamas Dive Trip', 'Nassau, Bahamas', '7-day diving expedition in crystal clear waters', 7, 16, 2499.00, 1);


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
