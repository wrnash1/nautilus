#!/bin/bash
# Deploy Controller base class fix

echo "=== Deploying Base Controller Class ==="

# Create the Core directory if it doesn't exist
echo "Ensuring Core directory exists..."
sudo mkdir -p /var/www/html/nautilus/app/Core

# Copy the Controller base class
echo "Copying Controller.php..."
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Core/Controller.php /var/www/html/nautilus/app/Core/Controller.php

# Set proper permissions
echo "Setting permissions..."
sudo chown www-data:www-data /var/www/html/nautilus/app/Core/Controller.php
sudo chmod 644 /var/www/html/nautilus/app/Core/Controller.php

echo "✅ Deployment complete!"
echo ""
echo "Created base Controller class with:"
echo "  - Database access (\$this->db)"
echo "  - Permission checking (checkPermission())"
echo "  - View rendering (view())"
echo "  - Vendor fetching (getVendors())"
echo "  - JSON response helper (json())"
echo "  - Redirect helper (redirect())"
echo ""
echo "This fixes controllers that extend Controller:"
echo "  - VendorCatalogController"
echo "  - SettingsController"
echo "  - QuickBooksController"
echo ""
echo "You can now refresh your browser."
