#!/bin/bash
# Deploy Dive Sites Views

echo "=== Deploying Dive Sites Views ==="

# Create dive_sites directory in production
echo "Creating dive_sites directory..."
sudo mkdir -p /var/www/html/nautilus/app/Views/dive_sites

# Copy view files
echo "Copying view files..."
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Views/dive_sites/index.php /var/www/html/nautilus/app/Views/dive_sites/index.php
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Views/dive_sites/show.php /var/www/html/nautilus/app/Views/dive_sites/show.php
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Views/dive_sites/create.php /var/www/html/nautilus/app/Views/dive_sites/create.php

# Set permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data /var/www/html/nautilus/app/Views/dive_sites
sudo chmod -R 644 /var/www/html/nautilus/app/Views/dive_sites/*.php

echo "✅ Deployment complete!"
echo ""
echo "Created views:"
echo "  - index.php  (List all dive sites)"
echo "  - show.php   (View dive site with weather)"
echo "  - create.php (Add new dive site)"
echo ""
echo "You can now access:"
echo "  - /dive-sites          (List)"
echo "  - /dive-sites/create   (Add new)"
echo "  - /dive-sites/{id}     (View details)"
