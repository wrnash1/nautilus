#!/usr/bin/env php
<?php
/**
 * Fix Database Type Hints Script
 * This script fixes the type hint mismatch where Database::getInstance() returns PDO
 * but properties are typed as Database.
 */

echo "=== Nautilus Type Hint Fixer ===\n\n";

// Files to fix
$files = [
    '/var/www/html/nautilus/app/Services/Waiver/WaiverService.php',
    '/var/www/html/nautilus/app/Services/Notifications/NotificationService.php',
    '/var/www/html/nautilus/app/Services/Reports/CustomReportService.php',
    '/var/www/html/nautilus/app/Services/DiveSites/WeatherService.php',
    '/var/www/html/nautilus/app/Services/Auth/TwoFactorService.php',
    '/var/www/html/nautilus/app/Services/Admin/BackupService.php',
    '/var/www/html/nautilus/app/Services/Security/SecurityService.php',
    '/var/www/html/nautilus/app/Services/Inventory/VendorImportService.php',
    '/var/www/html/nautilus/app/Services/Inventory/SerialNumberService.php',
    '/var/www/html/nautilus/app/Controllers/API/TokenController.php',
    '/var/www/html/nautilus/app/Middleware/RateLimitMiddleware.php',
    '/var/www/html/nautilus/app/Middleware/BruteForceProtectionMiddleware.php',
];

$fixedCount = 0;
$skippedCount = 0;
$errors = [];

foreach ($files as $file) {
    echo "Processing: $file\n";

    if (!file_exists($file)) {
        echo "  ⚠️  File not found, skipping...\n";
        $skippedCount++;
        continue;
    }

    // Read file
    $content = file_get_contents($file);
    $originalContent = $content;

    // Fix 1: Change private Database $db to private PDO $db
    $content = preg_replace('/private Database \$db;/', 'private PDO $db;', $content);

    // Fix 2: Add PDO use statement if not present
    if (strpos($content, 'private PDO $db') !== false) {
        if (strpos($content, 'use PDO;') === false) {
            // Find the position after "use App\Core\Database;"
            $content = preg_replace(
                '/(use App\\\\Core\\\\Database;)/',
                "$1\nuse PDO;",
                $content
            );
        }
    }

    // Check if changes were made
    if ($content !== $originalContent) {
        // Try to write the file
        if (@file_put_contents($file, $content)) {
            echo "  ✅ Fixed!\n";
            $fixedCount++;
        } else {
            echo "  ❌ Permission denied - unable to write\n";
            $errors[] = $file;
        }
    } else {
        echo "  ℹ️  Already fixed or no changes needed\n";
    }
}

echo "\n=== Summary ===\n";
echo "Fixed: $fixedCount files\n";
echo "Skipped: $skippedCount files\n";

if (count($errors) > 0) {
    echo "\n⚠️  Failed to fix " . count($errors) . " file(s) due to permissions:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    echo "\nRun this script with sudo:\n";
    echo "  sudo php " . __FILE__ . "\n";
    exit(1);
}

echo "\n✅ All files fixed successfully!\n";
echo "You can now refresh your browser.\n";
