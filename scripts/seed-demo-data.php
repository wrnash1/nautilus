#!/usr/bin/env php
<?php

/**
 * Nautilus Demo Data Seeder
 *
 * Populates database with realistic demo data for testing and demonstrations
 *
 * Usage: php scripts/seed-demo-data.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use App\Core\Database;

// Colors for output
define('GREEN', "\033[0;32m");
define('YELLOW', "\033[1;33m");
define('BLUE', "\033[0;34m");
define('RED', "\033[0;31m");
define('NC', "\033[0m");

function printHeader($text) {
    echo BLUE . "========================================\n" . NC;
    echo BLUE . $text . "\n" . NC;
    echo BLUE . "========================================\n" . NC;
}

function printSuccess($text) {
    echo GREEN . "✓ $text\n" . NC;
}

function printWarning($text) {
    echo YELLOW . "⚠ $text\n" . NC;
}

function printError($text) {
    echo RED . "✗ $text\n" . NC;
}

function printInfo($text) {
    echo BLUE . "ℹ $text\n" . NC;
}

// Connect to database
try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    printError("Database connection failed: " . $e->getMessage());
    exit(1);
}

printHeader("Nautilus Demo Data Seeder");
echo "\n";
echo "This will populate your database with demo data.\n";
echo "WARNING: This should only be used for development/testing!\n\n";

echo "Continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'yes') {
    printError("Aborted by user");
    exit(1);
}

################################################################################
# Seed Roles and Permissions
################################################################################

printHeader("Step 1: Creating Roles & Permissions");

try {
    // Roles
    $roles = [
        ['id' => 1, 'name' => 'Administrator', 'description' => 'Full system access'],
        ['id' => 2, 'name' => 'Manager', 'description' => 'Store manager - full operational access'],
        ['id' => 3, 'name' => 'Sales Staff', 'description' => 'POS and customer management'],
        ['id' => 4, 'name' => 'Instructor', 'description' => 'Course and certification management'],
        ['id' => 5, 'name' => 'Technician', 'description' => 'Equipment and work orders'],
    ];

    foreach ($roles as $role) {
        $stmt = $db->prepare(
            "INSERT IGNORE INTO roles (id, name, description, created_at, updated_at)
             VALUES (?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([$role['id'], $role['name'], $role['description']]);
    }

    printSuccess("Roles created");
} catch (Exception $e) {
    printError("Failed to create roles: " . $e->getMessage());
}

################################################################################
# Seed Staff Users
################################################################################

printHeader("Step 2: Creating Staff Users");

try {
    // Password: 'password' (hashed)
    $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    $users = [
        ['admin', 'admin@diveshop.com', $passwordHash, 'System', 'Administrator', 1],
        ['manager', 'manager@diveshop.com', $passwordHash, 'Sarah', 'Johnson', 2],
        ['sales1', 'mike@diveshop.com', $passwordHash, 'Mike', 'Thompson', 3],
        ['sales2', 'lisa@diveshop.com', $passwordHash, 'Lisa', 'Martinez', 3],
        ['instructor', 'john@diveshop.com', $passwordHash, 'John', 'Smith', 4],
        ['tech', 'dave@diveshop.com', $passwordHash, 'Dave', 'Wilson', 5],
    ];

    foreach ($users as $user) {
        $stmt = $db->prepare(
            "INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role_id, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())"
        );
        $stmt->execute($user);
    }

    printSuccess("Staff users created (password: 'password' for all)");
} catch (Exception $e) {
    printError("Failed to create users: " . $e->getMessage());
}

################################################################################
# Seed Categories
################################################################################

printHeader("Step 3: Creating Product Categories");

try {
    $categories = [
        ['Regulators', 'Breathing equipment and regulators'],
        ['BCDs', 'Buoyancy Control Devices'],
        ['Wetsuits', 'Wetsuits and exposure protection'],
        ['Fins', 'Fins and foot pockets'],
        ['Masks & Snorkels', 'Masks, snorkels, and accessories'],
        ['Dive Computers', 'Dive computers and gauges'],
        ['Tanks', 'Scuba tanks and cylinders'],
        ['Accessories', 'Dive bags, knives, lights, and more'],
        ['Training Materials', 'Books, slates, and learning materials'],
        ['Apparel', 'T-shirts, hats, and casual wear'],
    ];

    foreach ($categories as $cat) {
        $stmt = $db->prepare(
            "INSERT INTO categories (name, description, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())"
        );
        $stmt->execute($cat);
    }

    printSuccess(count($categories) . " categories created");
} catch (Exception $e) {
    printError("Failed to create categories: " . $e->getMessage());
}

################################################################################
# Seed Products
################################################################################

printHeader("Step 4: Creating Products");

try {
    $products = [
        // Regulators
        ['Scubapro MK25 EVO/S620 Ti', 'REG-001', 899.00, 5, 1, 'Professional-grade titanium regulator'],
        ['Atomic Aquatics T3', 'REG-002', 1299.00, 3, 1, 'Titanium high-performance regulator'],
        ['Aqualung Core Supreme', 'REG-003', 449.00, 8, 1, 'Reliable intermediate regulator'],

        // BCDs
        ['Scubapro Hydros Pro', 'BCD-001', 799.00, 6, 2, 'Modular BCD with gel padding'],
        ['Zeagle Ranger', 'BCD-002', 649.00, 4, 2, 'Back-inflate BCD for travel'],
        ['Aqualung Axiom i3', 'BCD-003', 899.00, 5, 2, 'Innovative inflation system'],

        // Wetsuits
        ['Bare 7mm Reactive', 'WET-001', 449.00, 10, 3, 'Cold water 7mm wetsuit'],
        ['Scubapro Definition 5mm', 'WET-002', 329.00, 12, 3, 'Warm water 5mm wetsuit'],
        ['Henderson 3mm Thermoprene', 'WET-003', 219.00, 15, 3, 'Tropical 3mm wetsuit'],

        // Fins
        ['Scubapro Jet Fins', 'FIN-001', 119.00, 20, 4, 'Classic paddle fins'],
        ['Mares Avanti Quattro Plus', 'FIN-002', 89.00, 25, 4, 'Versatile open-heel fins'],
        ['Atomic Split Fins', 'FIN-003', 239.00, 10, 4, 'Efficient split fin design'],

        // Masks
        ['Atomic Venom Frameless', 'MASK-001', 99.00, 30, 5, 'Ultra-clear frameless mask'],
        ['Scubapro Spectra', 'MASK-002', 79.00, 25, 5, 'Twin-lens dive mask'],
        ['Cressi Big Eyes Evolution', 'MASK-003', 69.00, 20, 5, 'Wide field of view'],

        // Dive Computers
        ['Shearwater Perdix AI', 'COMP-001', 1199.00, 4, 6, 'Advanced tech dive computer'],
        ['Suunto D5', 'COMP-002', 599.00, 6, 6, 'Compact wrist computer'],
        ['Mares Puck Pro Plus', 'COMP-003', 299.00, 10, 6, 'Entry-level wrist computer'],

        // Accessories
        ['Dive Bag - Mesh Rolling', 'ACC-001', 89.00, 15, 8, 'Large mesh roller bag'],
        ['LED Dive Light 1000 Lumen', 'ACC-002', 149.00, 12, 8, 'Powerful LED torch'],
        ['Titanium Dive Knife', 'ACC-003', 79.00, 20, 8, 'Corrosion-resistant knife'],

        // Training Materials
        ['PADI Open Water Manual', 'BOOK-001', 39.00, 50, 9, 'OW certification manual'],
        ['PADI Advanced Manual', 'BOOK-002', 45.00, 30, 9, 'AOW certification manual'],
        ['Dive Log Book', 'BOOK-003', 19.00, 100, 9, 'Professional dive log'],

        // Apparel
        ['Dive Shop Logo T-Shirt', 'APRL-001', 24.99, 50, 10, 'Cotton dive shop tee'],
        ['Dive Shop Hat', 'APRL-002', 19.99, 40, 10, 'Embroidered baseball cap'],
    ];

    foreach ($products as $prod) {
        $stmt = $db->prepare(
            "INSERT INTO products (name, sku, price, stock_quantity, category_id, description, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute($prod);
    }

    printSuccess(count($products) . " products created");
} catch (Exception $e) {
    printError("Failed to create products: " . $e->getMessage());
}

################################################################################
# Seed Customers
################################################################################

printHeader("Step 5: Creating Demo Customers");

try {
    // Password: 'password' (hashed)
    $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    $customers = [
        ['John', 'Doe', 'john.doe@example.com', '555-0101', 'B2C', 150],
        ['Jane', 'Smith', 'jane.smith@example.com', '555-0102', 'B2C', 280],
        ['Bob', 'Johnson', 'bob.j@example.com', '555-0103', 'B2C', 420],
        ['Alice', 'Williams', 'alice.w@example.com', '555-0104', 'B2C', 95],
        ['Charlie', 'Brown', 'charlie.b@example.com', '555-0105', 'B2C', 350],
        ['Ocean Adventures LLC', null, 'info@oceanadv.com', '555-0201', 'B2B', 1250],
        ['Coral Reef Divers', null, 'contact@coralreef.com', '555-0202', 'B2B', 890],
        ['Blue Water Tours', null, 'bookings@bluewater.com', '555-0203', 'B2B', 2100],
    ];

    foreach ($customers as $cust) {
        $stmt = $db->prepare(
            "INSERT INTO customers (first_name, last_name, email, phone, password_hash, customer_type, loyalty_points, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $cust[0],
            $cust[1],
            $cust[2],
            $cust[3],
            $passwordHash,
            $cust[4],
            $cust[5]
        ]);
    }

    printSuccess(count($customers) . " customers created");
} catch (Exception $e) {
    printError("Failed to create customers: " . $e->getMessage());
}

################################################################################
# Seed Rental Equipment
################################################################################

printHeader("Step 6: Creating Rental Equipment");

try {
    $rentalEquipment = [
        ['Regulator Set #1', 'REG-RENTAL-001', 'regulator', 'good', 25.00],
        ['Regulator Set #2', 'REG-RENTAL-002', 'regulator', 'excellent', 25.00],
        ['Regulator Set #3', 'REG-RENTAL-003', 'regulator', 'good', 25.00],
        ['BCD Size Medium #1', 'BCD-RENTAL-M1', 'bcd', 'good', 20.00],
        ['BCD Size Large #1', 'BCD-RENTAL-L1', 'bcd', 'excellent', 20.00],
        ['Wetsuit 3mm Medium', 'WET-RENTAL-3M1', 'wetsuit', 'fair', 15.00],
        ['Wetsuit 5mm Large', 'WET-RENTAL-5L1', 'wetsuit', 'good', 15.00],
        ['Fins Medium #1', 'FIN-RENTAL-M1', 'fins', 'excellent', 10.00],
        ['Fins Large #1', 'FIN-RENTAL-L1', 'fins', 'good', 10.00],
        ['Mask #1', 'MASK-RENTAL-01', 'mask', 'excellent', 8.00],
        ['Mask #2', 'MASK-RENTAL-02', 'mask', 'good', 8.00],
        ['Dive Computer #1', 'COMP-RENTAL-01', 'computer', 'excellent', 35.00],
        ['Dive Computer #2', 'COMP-RENTAL-02', 'computer', 'good', 35.00],
    ];

    foreach ($rentalEquipment as $equip) {
        $stmt = $db->prepare(
            "INSERT INTO rental_equipment (name, serial_number, equipment_type, condition_status, rental_price_per_day, is_available, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, TRUE, NOW(), NOW())"
        );
        $stmt->execute($equip);
    }

    printSuccess(count($rentalEquipment) . " rental items created");
} catch (Exception $e) {
    printError("Failed to create rental equipment: " . $e->getMessage());
}

################################################################################
# Seed Courses
################################################################################

printHeader("Step 7: Creating Training Courses");

try {
    $courses = [
        ['PADI Open Water Diver', 'OWD', 'Entry-level certification course', 399.00, 4, 6],
        ['PADI Advanced Open Water', 'AOW', 'Advanced skills and specialty dives', 349.00, 2, 6],
        ['PADI Rescue Diver', 'RES', 'Rescue techniques and emergency management', 299.00, 3, 5],
        ['PADI Divemaster', 'DM', 'Professional-level certification', 799.00, 2, 8],
        ['Enriched Air (Nitrox)', 'EAN', 'Nitrox specialty certification', 199.00, 1, 2],
        ['Deep Diver Specialty', 'DEEP', 'Deep diving techniques', 249.00, 2, 3],
        ['Wreck Diver Specialty', 'WRECK', 'Wreck penetration and exploration', 249.00, 2, 3],
        ['Night Diver Specialty', 'NIGHT', 'Night diving procedures', 199.00, 1, 2],
    ];

    foreach ($courses as $course) {
        $stmt = $db->prepare(
            "INSERT INTO courses (name, code, description, price, min_participants, max_participants, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute($course);
    }

    printSuccess(count($courses) . " courses created");
} catch (Exception $e) {
    printError("Failed to create courses: " . $e->getMessage());
}

################################################################################
# Seed Dive Trips
################################################################################

printHeader("Step 8: Creating Dive Trips");

try {
    $trips = [
        ['Catalina Island Weekend', 'Explore the beautiful kelp forests and marine life of Catalina Island', 299.00, 12, 2],
        ['Channel Islands Day Trip', 'Day trip to Channel Islands National Park', 149.00, 16, 1],
        ['Local Shore Diving', 'Guided shore diving at local sites', 79.00, 8, 1],
        ['Wreck Dive Adventure', 'Explore historic shipwrecks', 189.00, 10, 1],
    ];

    foreach ($trips as $trip) {
        $stmt = $db->prepare(
            "INSERT INTO trips (name, description, price, max_participants, duration_days, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute($trip);
    }

    printSuccess(count($trips) . " dive trips created");
} catch (Exception $e) {
    printError("Failed to create trips: " . $e->getMessage());
}

################################################################################
# Complete
################################################################################

printHeader("Demo Data Seeding Complete!");

echo "\n";
echo GREEN . "✓ Roles & Permissions\n" . NC;
echo GREEN . "✓ Staff Users\n" . NC;
echo GREEN . "✓ Product Categories\n" . NC;
echo GREEN . "✓ Products\n" . NC;
echo GREEN . "✓ Customers\n" . NC;
echo GREEN . "✓ Rental Equipment\n" . NC;
echo GREEN . "✓ Training Courses\n" . NC;
echo GREEN . "✓ Dive Trips\n" . NC;
echo "\n";
echo YELLOW . "Default Login Credentials:\n" . NC;
echo "\n";
echo "  Staff Portal (/store/login):\n";
echo "    admin@diveshop.com / password (Administrator)\n";
echo "    manager@diveshop.com / password (Manager)\n";
echo "    mike@diveshop.com / password (Sales Staff)\n";
echo "\n";
echo "  Customer Portal (/account/login):\n";
echo "    john.doe@example.com / password\n";
echo "    jane.smith@example.com / password\n";
echo "\n";
echo YELLOW . "IMPORTANT: Change these passwords in production!\n" . NC;
echo "\n";

exit(0);
