<?php
/**
 * Fix .env database configuration
 * Run once via browser: https://nautilus.local/fix-env.php
 */

$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die("ERROR: .env file not found at: $envFile");
}

echo "<h2>Fixing .env Database Configuration</h2>";
echo "<pre>";

// Read current content
$content = file_get_contents($envFile);

// Show current setting
if (preg_match('/DB_DATABASE=(.+)$/m', $content, $matches)) {
    echo "Current DB_DATABASE: " . trim($matches[1]) . "\n";
}

// Update to nautilus_dev
$newContent = preg_replace('/^DB_DATABASE=nautilus$/m', 'DB_DATABASE=nautilus_dev', $content);

// Save
if (file_put_contents($envFile, $newContent)) {
    echo "✓ Updated .env file\n\n";

    // Verify
    $verify = file_get_contents($envFile);
    if (preg_match('/DB_DATABASE=(.+)$/m', $verify, $matches)) {
        echo "New DB_DATABASE: " . trim($matches[1]) . "\n";
    }

    echo "\n✓ Configuration updated successfully!\n\n";
    echo "Now test login at: <a href='/debug-login.php'>/debug-login.php</a>\n";
} else {
    echo "✗ ERROR: Could not write to .env file\n";
    echo "File permissions may be wrong.\n";
    echo "\nManually edit: $envFile\n";
    echo "Change: DB_DATABASE=nautilus\n";
    echo "To:     DB_DATABASE=nautilus_dev\n";
}

echo "</pre>";
