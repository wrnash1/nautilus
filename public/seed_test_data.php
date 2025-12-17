<?php
// Seed Test Data Script
// Usage: php public/seed_test_data.php

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

// Load config
$config = require __DIR__ . '/../config/database.php';

// Connect manually if App/Core/Database doesn't self-init on standalone
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    echo "Connected to database successfully.\n";

    // 1. Insert Product
    $stmt = $pdo->prepare("SELECT id FROM products WHERE sku = 'TESTSKU001'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO products (name, sku, description, cost_price, retail_price, stock_quantity, is_active, created_at) 
                VALUES ('Test Snorkel', 'TESTSKU001', 'A test snorkel', 20.00, 50.00, 100, 1, NOW())";
        $pdo->exec($sql);
        echo "Created Product: Test Snorkel\n";
    } else {
        echo "Product Test Snorkel already exists.\n";
    }

    // 2. Insert Course Type
    $stmt = $pdo->prepare("SELECT id FROM course_types WHERE name = 'Open Water Diver'");
    $stmt->execute();
    $courseTypeId = $stmt->fetchColumn();

    if (!$courseTypeId) {
        $sql = "INSERT INTO course_types (name, description, price, duration_days, created_at) 
                VALUES ('Open Water Diver', 'Entry level course', 350.00, 3, NOW())";
        $pdo->exec($sql);
        $courseTypeId = $pdo->lastInsertId();
        echo "Created Course Type: Open Water Diver\n";
    } else {
        echo "Course Type Open Water Diver already exists (ID: $courseTypeId).\n";
    }

    // 3. Insert Course Instance
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_type_id = ? AND start_date = '2026-06-04'");
    $stmt->execute([$courseTypeId]);
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO courses (course_type_id, start_date, end_date, max_students, created_at) 
                VALUES (?, '2026-06-04', '2026-06-07', 8, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$courseTypeId]);
        echo "Created Course Instance: 2026-06-04\n";
    } else {
        echo "Course Instance for 2026-06-04 already exists.\n";
    }

    // 4. Insert Trip
    $stmt = $pdo->prepare("SELECT id FROM trips WHERE destination = 'Bahamas Dive Trip'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO trips (destination, description, start_date, end_date, price, max_spots, created_at) 
                VALUES ('Bahamas Dive Trip', 'Luxury dive trip', '2026-07-01', '2026-07-08', 1500.00, 12, NOW())";
        $pdo->exec($sql);
        echo "Created Trip: Bahamas Dive Trip\n";
    } else {
        echo "Trip Bahamas Dive Trip already exists.\n";
    }

    // 5. Insert Rescue Diver Course Type (for Inquiry scenario)
    $stmt = $pdo->prepare("SELECT id FROM course_types WHERE name = 'Rescue Diver'");
    $stmt->execute();
    $rescueId = $stmt->fetchColumn();
    
    if (!$rescueId) {
        $sql = "INSERT INTO course_types (name, description, price, duration_days, created_at) 
                VALUES ('Rescue Diver', 'Rescue course', 400.00, 2, NOW())";
        $pdo->exec($sql);
        $rescueId = $pdo->lastInsertId();
        echo "Created Course Type: Rescue Diver\n";
    }

    // 6. Insert Rescue Diver in August 2026
    $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_type_id = ? AND start_date LIKE '2026-08%'");
    $stmt->execute([$rescueId]);
    if (!$stmt->fetch()) {
        $sql = "INSERT INTO courses (course_type_id, start_date, end_date, max_students, created_at) 
                VALUES (?, '2026-08-15', '2026-08-16', 6, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rescueId]);
        echo "Created Rescue Diver Course for August 2026\n";
    }

    echo "Seeding completed successfully.\n";

} catch (Exception $e) {
    echo "Seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
