<?php
require_once __DIR__ . '/bootstrap/app.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

// Insert stats settings
$statsSettings = [
    ['stats_certified_divers', '5000', 'Number of certified divers'],
    ['stats_years_experience', '25', 'Years in business'],
    ['stats_dive_destinations', '100', 'Number of dive destinations'],
    ['stats_customer_rating', '4.9', 'Customer rating out of 5'],
    ['primary_certification_org', 'PADI', 'Primary certification organization'],
    ['certification_level', '5-Star Center', 'Certification level/designation'],
    ['secondary_certifications', 'SSI,NAUI', 'Comma-separated list of other certifications'],
];

foreach ($statsSettings as [$key, $value, $desc]) {
    $stmt = $db->prepare("
        INSERT INTO system_settings (setting_key, setting_value, setting_type, description, created_at)
        VALUES (?, ?, 'text', ?, NOW())
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
    ");
    $stmt->execute([$key, $value, $desc]);
    echo "✓ Inserted/Updated: $key = $value\n";
}

// Insert service boxes
$serviceBoxes = [
    ['PADI Courses', 'Professional diving certification from beginner to instructor', 'bi bi-award', '/courses', 1],
    ['Equipment Shop', 'Top-quality diving gear and accessories', 'bi bi-shop', '/shop', 2],
    ['Dive Trips', 'Guided adventures to amazing dive sites worldwide', 'bi bi-geo-alt', '/trips', 3],
    ['Equipment Rental', 'Professional-grade rental equipment available', 'bi bi-tools', '/rentals', 4],
    ['DAN Courses', 'Divers Alert Network training for dive safety and emergency response', 'bi bi-heart-pulse', '/courses/dan', 5],
    ['Dive Insurance', 'Comprehensive dive insurance coverage for your peace of mind', 'bi bi-shield-check', '/insurance', 6],
];

foreach ($serviceBoxes as [$title, $desc, $icon, $link, $order]) {
    $stmt = $db->prepare("
        INSERT INTO storefront_service_boxes (title, description, icon, link, display_order, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE 
            description = VALUES(description),
            icon = VALUES(icon),
            link = VALUES(link),
            display_order = VALUES(display_order),
            updated_at = NOW()
    ");
    $stmt->execute([$title, $desc, $icon, $link, $order]);
    echo "✓ Inserted/Updated service box: $title\n";
}

echo "\n✅ All data inserted successfully!\n";
