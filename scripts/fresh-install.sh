#!/bin/bash
#
# Fresh Installation Script for Nautilus
# This ensures a completely clean installation
#

set -e  # Exit on any error

echo "======================================================================"
echo "Nautilus Fresh Installation"
echo "======================================================================"
echo ""

# Step 1: Copy all files to server (including the VIEW fixes)
echo "[1/6] Copying migration files to server..."
sudo rsync -av --delete \
  /home/wrnash1/Developer/nautilus/database/migrations/ \
  /var/www/html/nautilus/database/migrations/

# Step 2: Set ownership
echo "[2/6] Setting file ownership..."
sudo chown -R www-data:www-data /var/www/html/nautilus/database/migrations/

# Step 3: Drop and recreate database
echo "[3/6] Dropping and recreating database..."
mysql -u root -pFrogman09! <<EOF
DROP DATABASE IF EXISTS nautilus;
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

#  Step 4: Clear any PHP opcache
echo "[4/6] Restarting Apache to clear caches..."
sudo systemctl restart apache2

# Step 5: Remove any installation progress files
echo "[5/6] Clearing installation progress..."
sudo rm -f /var/www/html/nautilus/storage/install_progress.json

# Step 6: Ready to install
echo "[6/6] Ready for installation!"
echo ""
echo "======================================================================"
echo "Setup Complete!"
echo "======================================================================"
echo ""
echo "Next step: Visit https://pangolin.local/simple-install.php"
echo ""
echo "Use these values in the form:"
echo "  Business Name: Nautilus Dive Shop"
echo "  App URL: https://pangolin.local"
echo "  Timezone: America/Chicago (or your timezone)"
echo "  Admin Email: admin@nautilus.local"
echo "  Password: (choose a secure password)"
echo ""
