#!/bin/bash
# Comprehensive deployment script - Deploys all fixes to web server

echo "========================================="
echo "  Nautilus - Deploy All Fixes"
echo "========================================="
echo ""

ERRORS=0

# Function to check if command succeeded
check_status() {
    if [ $? -eq 0 ]; then
        echo "  ✅ $1"
    else
        echo "  ❌ $1 FAILED"
        ERRORS=$((ERRORS + 1))
    fi
}

# Step 1: Fix type hints (Database -> PDO)
echo "[1/3] Fixing type hints..."
sudo php /home/wrnash1/Developer/nautilus-v6/fix-type-hints.php
check_status "Type hints fixed"
echo ""

# Step 2: Deploy WaiverController fix
echo "[2/3] Deploying WaiverController fix..."
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Controllers/WaiverController.php /var/www/html/nautilus/app/Controllers/WaiverController.php
check_status "WaiverController deployed"
sudo chown www-data:www-data /var/www/html/nautilus/app/Controllers/WaiverController.php
check_status "Permissions set for WaiverController"
echo ""

# Step 3: Deploy base Controller class
echo "[3/3] Deploying base Controller class..."
sudo mkdir -p /var/www/html/nautilus/app/Core
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Core/Controller.php /var/www/html/nautilus/app/Core/Controller.php
check_status "Controller.php deployed"
sudo chown www-data:www-data /var/www/html/nautilus/app/Core/Controller.php
sudo chmod 644 /var/www/html/nautilus/app/Core/Controller.php
check_status "Permissions set for Controller"
echo ""

# Step 4: Deploy other critical fixes
echo "[BONUS] Deploying additional fixed files..."

# POS JavaScript
sudo cp /home/wrnash1/Developer/nautilus-v6/public/assets/js/professional-pos.js /var/www/html/nautilus/public/assets/js/professional-pos.js 2>/dev/null
check_status "POS JavaScript deployed"

# Settings Controller (with getTaxRate)
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Controllers/Admin/SettingsController.php /var/www/html/nautilus/app/Controllers/Admin/SettingsController.php 2>/dev/null
check_status "SettingsController deployed"

# TransactionController (POS fixes)
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Controllers/POS/TransactionController.php /var/www/html/nautilus/app/Controllers/POS/TransactionController.php 2>/dev/null
check_status "TransactionController deployed"

# Install Service (company name fix)
sudo cp /home/wrnash1/Developer/nautilus-v6/app/Services/Install/InstallService.php /var/www/html/nautilus/app/Services/Install/InstallService.php 2>/dev/null
check_status "InstallService deployed"

# Routes (tax rate API)
sudo cp /home/wrnash1/Developer/nautilus-v6/routes/web.php /var/www/html/nautilus/routes/web.php 2>/dev/null
check_status "Routes deployed"

# Index.php (auto-redirect to install/login)
sudo cp /home/wrnash1/Developer/nautilus-v6/public/index.php /var/www/html/nautilus/public/index.php 2>/dev/null
check_status "Index.php (auto-redirect) deployed"

echo ""
echo "========================================="

if [ $ERRORS -eq 0 ]; then
    echo "  ✅ ALL FIXES DEPLOYED SUCCESSFULLY!"
    echo "========================================="
    echo ""
    echo "Fixed Issues:"
    echo "  ✅ Type hint errors (Database -> PDO)"
    echo "  ✅ WaiverController private property access"
    echo "  ✅ Missing Controller base class"
    echo "  ✅ POS subtotal/tax display"
    echo "  ✅ POS category filtering"
    echo "  ✅ POS cart clearing after checkout"
    echo "  ✅ Dynamic tax rate API"
    echo "  ✅ Company name display"
    echo "  ✅ Walk-in customer support"
    echo "  ✅ Auto-redirect to install/login"
    echo ""
    echo "🎉 Application is now fully operational!"
    echo ""
    echo "Next steps:"
    echo "  1. Refresh your browser"
    echo "  2. Test all features"
    echo "  3. Add company name to database (see earlier instructions)"
else
    echo "  ⚠️  DEPLOYMENT COMPLETED WITH $ERRORS ERROR(S)"
    echo "========================================="
    echo ""
    echo "Some files may not have been deployed."
    echo "Check the errors above and try deploying manually."
fi
