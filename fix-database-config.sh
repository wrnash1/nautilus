#!/bin/bash
# Fix database configuration mismatch

echo "Fixing database configuration..."
echo ""

# Option 1: Update web server .env to use nautilus_dev
echo "Updating /var/www/html/nautilus/.env to use nautilus_dev database..."
sudo sed -i 's/DB_DATABASE=nautilus$/DB_DATABASE=nautilus_dev/' /var/www/html/nautilus/.env

echo "✓ Configuration updated"
echo ""

# Verify the change
echo "Current database configuration:"
sudo grep "DB_DATABASE" /var/www/html/nautilus/.env

echo ""
echo "Testing login now..."
echo "Visit: https://nautilus.local/debug-login.php"
