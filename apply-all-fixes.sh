#!/bin/bash
# Apply all fixes to Nautilus

echo "=========================================="
echo "Applying All Nautilus Fixes"
echo "=========================================="
echo ""

APP_DEV="/home/wrnash1/development/nautilus"
APP_WEB="/var/www/html/nautilus"

# 1. Fix PHP 8.4 compatibility
echo "[1/5] Fixing PHP 8.4 nullable parameter warnings..."
cd "$APP_DEV"
./fix-php84-compatibility.sh

# 2. Sync routes file (already fixed customer tags routing)
echo ""
echo "[2/5] Syncing fixed routes..."
cp "$APP_DEV/routes/web.php" "$APP_WEB/routes/"

# 3. Sync all controllers and views
echo ""
echo "[3/5] Syncing controllers and views..."
rsync -av --exclude='.git' --exclude='vendor' --exclude='.env' \
    "$APP_DEV/app/" "$APP_WEB/app/"

# 4. Set permissions
echo ""
echo "[4/5] Setting permissions..."
chown -R apache:apache "$APP_WEB"
chmod -R 755 "$APP_WEB"
chmod -R 775 "$APP_WEB/storage"
chmod -R 775 "$APP_WEB/public/uploads"

# 5. Summary
echo ""
echo "[5/5] Fixes Applied:"
echo "  ✓ PHP 8.4 nullable parameter types fixed"
echo "  ✓ Customer tags route ordering fixed"
echo "  ✓ Controllers and views synced"
echo "  ✓ Permissions set"
echo ""
echo "=========================================="
echo "✓ All Fixes Applied!"
echo "=========================================="
echo ""
echo "Test the following URLs:"
echo "  https://nautilus.local/store"
echo "  https://nautilus.local/store/customers/tags"
echo "  https://nautilus.local/store/customers"
echo ""
