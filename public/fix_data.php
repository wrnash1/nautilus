<?php
// Quick diagnostic and data insertion script
require_once __DIR__ . '/bootstrap/app.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "=== DIAGNOSTIC REPORT ===\n\n";

// Check system_settings table
echo "1. Checking system_settings table:\n";
$stmt = $db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'stats_%' OR setting_key LIKE '%business%' OR setting_key LIKE '%certification%' LIMIT 20");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($settings)) {
    echo "   ❌ No settings found! Inserting default data...\n\n";

    // Insert all required settings
    $defaultSettings = [
        ['business_name', 'Nautilus Dive Shop', 'Business name'],
        ['business_phone', '817-406-4080', 'Business phone'],
        ['business_email', 'info@nautilus.local', 'Business email'],
        ['business_address', '149 W Main Street', 'Business address'],
        ['business_city', 'Azle', 'Business city'],
        ['business_state', 'TX', 'Business state'],
        ['business_zip', '76020', 'Business ZIP code'],
        ['stats_certified_divers', '5000', 'Number of certified divers'],
        ['stats_years_experience', '25', 'Years in business'],
        ['stats_dive_destinations', '100', 'Number of dive destinations'],
        ['stats_customer_rating', '4.9', 'Customer rating out of 5'],
        ['primary_certification_org', 'PADI', 'Primary certification organization'],
        ['certification_level', '5-Star Center', 'Certification level'],
        ['secondary_certifications', 'SSI,NAUI', 'Secondary certifications'],
    ];

    foreach ($defaultSettings as [$key, $value, $desc]) {
        $stmt = $db->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_type, description, created_at)
            VALUES (?, ?, 'text', ?, NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");
        $stmt->execute([$key, $value, $desc]);
        echo "   ✓ Inserted: $key = $value\n";
    }
    echo "\n";
} else {
    echo "   ✓ Found " . count($settings) . " settings:\n";
    foreach ($settings as $setting) {
        echo "   - {$setting['setting_key']}: {$setting['setting_value']}\n";
    }
    echo "\n";
}

// Check storefront_service_boxes
echo "2. Checking storefront_service_boxes:\n";
$stmt = $db->query("SELECT COUNT(*) as count FROM storefront_service_boxes WHERE is_active = 1");
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "   ✓ Found $count active service boxes\n\n";

if ($count == 0) {
    echo "   Inserting default service boxes...\n";
    $serviceBoxes = [
        ['PADI Courses', 'Professional diving certification from beginner to instructor', 'bi bi-award', '/courses', 1],
        ['Equipment Shop', 'Top-quality diving gear and accessories', 'bi bi-shop', '/shop', 2],
        ['Dive Trips', 'Guided adventures to amazing dive sites worldwide', 'bi bi-geo-alt', '/trips', 3],
        ['Equipment Rental', 'Professional-grade rental equipment available', 'bi bi-tools', '/rentals', 4],
        ['DAN Courses', 'Divers Alert Network training for dive safety', 'bi bi-heart-pulse', '/courses/dan', 5],
        ['Dive Insurance', 'Comprehensive coverage for peace of mind', 'bi bi-shield-check', '/insurance', 6],
    ];

    foreach ($serviceBoxes as [$title, $desc, $icon, $link, $order]) {
        $stmt = $db->prepare("
            INSERT INTO storefront_service_boxes (title, description, icon, link, display_order, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$title, $desc, $icon, $link, $order]);
        echo "   ✓ Inserted: $title\n";
    }
}

echo "\n=== ALL DATA INSERTED SUCCESSFULLY ===\n";
echo "Please refresh your browser to see the changes.\n";
