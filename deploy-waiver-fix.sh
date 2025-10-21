#!/bin/bash
# Deploy WaiverController fix to web server

echo "=== Deploying WaiverController Fix ==="

# Copy the fixed WaiverController
echo "Copying WaiverController.php..."
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Controllers/WaiverController.php /var/www/html/nautilus/app/Controllers/WaiverController.php

# Set proper permissions
echo "Setting permissions..."
sudo chown www-data:www-data /var/www/html/nautilus/app/Controllers/WaiverController.php
sudo chmod 644 /var/www/html/nautilus/app/Controllers/WaiverController.php

echo "✅ Deployment complete!"
echo ""
echo "Changes made:"
echo "  - Added 'use App\\Core\\Database;' import"
echo "  - Changed \$this->waiverService->db->query() to Database::query()"
echo ""
echo "You can now refresh your browser to test the Waivers page."
