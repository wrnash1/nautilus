<?php
/**
 * Check Code Version on Server
 * Run this to verify which version of InstallService.php is deployed
 */

header('Content-Type: text/plain');

$file = __DIR__ . '/app/Services/Install/InstallService.php';

if (!file_exists($file)) {
    echo "✗ FILE NOT FOUND: $file\n";
    echo "\nThe InstallService.php file doesn't exist!\n";
    echo "You need to sync the code from ~/Developer/nautilus/\n";
    exit(1);
}

echo "=== CODE VERSION CHECK ===\n\n";
echo "File: $file\n";
echo "Modified: " . date('Y-m-d H:i:s', filemtime($file)) . "\n\n";

$content = file_get_contents($file);

// Check for the OLD buggy versions
if (preg_match('/preg_replace.*FOREIGN_KEY_CHECKS/', $content)) {
    echo "✗ OLD BUGGY VERSION #1 DETECTED\n\n";
    echo "This version has the regex that strips FK disable commands.\n";
    echo "You need to pull the latest code and sync to web directory.\n\n";
    echo "Run these commands:\n";
    echo "  cd ~/Developer/nautilus\n";
    echo "  git pull origin devin/1760111706-nautilus-v6-complete-skeleton\n";
    echo "  sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/\n";
    exit(1);
}

if (preg_match('/ALWAYS wrap with FK disable/', $content)) {
    echo "✗ OLD VERSION #2 DETECTED (doesn't work)\n\n";
    echo "This version tries to wrap SQL with FK disable, but it doesn't work\n";
    echo "because multi_query() processes SET commands too late.\n";
    echo "You need to pull the latest code (commit bcf2c49 or later).\n\n";
    echo "Run these commands:\n";
    echo "  cd ~/Developer/nautilus\n";
    echo "  git pull origin devin/1760111706-nautilus-v6-complete-skeleton\n";
    echo "  sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/\n";
    exit(1);
}

// Check for the LATEST fixed version
if (preg_match('/Disable FK checks at CONNECTION level BEFORE multi_query/', $content)) {
    echo "✓ LATEST FIXED VERSION DETECTED (bcf2c49)\n\n";
    echo "This version disables FK checks at connection level before multi_query.\n";
    echo "This is the correct approach that actually works.\n\n";

    // Show the relevant code
    $lines = explode("\n", $content);
    foreach ($lines as $i => $line) {
        if (strpos($line, 'CRITICAL FIX: Disable FK checks at CONNECTION level') !== false) {
            echo "Code snippet (lines " . ($i) . "-" . ($i+12) . "):\n";
            for ($j = $i; $j <= $i+12 && $j < count($lines); $j++) {
                echo sprintf("%4d: %s\n", $j+1, $lines[$j]);
            }
            break;
        }
    }

    echo "\n✓ Code is ready to test!\n";
    echo "\nExpected result: 0 FK errors (not 36!)\n";
    exit(0);
}

echo "⚠ UNKNOWN VERSION\n\n";
echo "Could not determine code version.\n";
echo "Please check the file manually.\n";
