#!/bin/bash

# Verify FK Fix Script
# Run this on your Pop!_OS server to check if the fix was applied

echo "=== Verifying FK Fix Installation ==="
echo ""

FILE="/var/www/html/nautilus/app/Services/Install/InstallService.php"

if [ ! -f "$FILE" ]; then
    echo "✗ FILE NOT FOUND: $FILE"
    echo ""
    echo "The InstallService.php file doesn't exist on the server!"
    echo "Make sure you copied all files from ~/Developer/nautilus/"
    exit 1
fi

echo "✓ File exists: $FILE"
echo ""

# Check for the FK disable code
if grep -q "Disable FK checks BEFORE running migration" "$FILE"; then
    echo "✓ FK disable code FOUND in InstallService.php"
    echo ""
    echo "Showing the relevant code:"
    grep -A 2 "Disable FK checks BEFORE running migration" "$FILE"
    echo ""
    echo "✓ The fix is installed correctly!"
else
    echo "✗ FK disable code NOT FOUND"
    echo ""
    echo "The InstallService.php file is missing the FK check fix!"
    echo "You need to copy the updated file from ~/Developer/nautilus/"
    echo ""
    echo "Run this command:"
    echo "sudo cp ~/Developer/nautilus/app/Services/Install/InstallService.php /var/www/html/nautilus/app/Services/Install/"
    exit 1
fi
