#!/bin/bash
################################################################################
# Fix Login Issues - Nautilus
# Copies corrected files and sets up proper permissions
################################################################################

echo "=== Fixing Nautilus Login Issues ==="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "ERROR: Run with sudo"
    exit 1
fi

echo "[1/4] Copying fixed AuthController..."
cp /home/wrnash1/Developer/nautilus/app/Controllers/Auth/AuthController.php \
   /var/www/html/nautilus/app/Controllers/Auth/AuthController.php
echo "✓ AuthController updated"

echo""
echo "[2/4] Setting up log directory..."
mkdir -p /var/www/html/nautilus/storage/logs
chown -R apache:apache /var/www/html/nautilus/storage
chmod -R 775 /var/www/html/nautilus/storage

# SELinux contexts
if command -v getenforce &> /dev/null && [ "$(getenforce)" != "Disabled" ]; then
    chcon -R -t httpd_sys_rw_content_t /var/www/html/nautilus/storage
    echo "✓ Storage permissions and SELinux contexts set"
else
    echo "✓ Storage permissions set"
fi

echo ""
echo "[3/4] Verifying database..."
TABLES=$(mysql -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='nautilus';" 2>/dev/null || echo "0")
echo "  Tables in database: $TABLES"

USER_COUNT=$(mysql -u root -N -e "SELECT COUNT(*) FROM nautilus.users;" 2>/dev/null || echo "0")
echo "  Users in database: $USER_COUNT"

if [ "$USER_COUNT" = "0" ]; then
    echo "  WARNING: No users found! Database may not be properly installed."
    echo "  Run: https://nautilus.local/install/ to complete database setup"
fi

echo ""
echo "[4/4] Restarting Apache..."
systemctl restart httpd
echo "✓ Apache restarted"

echo ""
echo "=== Fix Complete ===" 
echo ""
echo "Login Credentials:"
echo "  Email/Username: admin@nautilus.local  (or just 'admin')"
echo "  Password: admin123"
echo ""
echo "Next steps:"
echo "1. Go to: https://nautilus.local/ (or http://nautilus.local/)"
echo "2. Click 'Store' or 'Login'"
echo "3. Use the credentials above"
echo ""
echo "If still having issues, check debug logs:"
echo "  sudo tail -f /var/www/html/nautilus/storage/logs/debug_login.log"
echo "  sudo tail -f /var/www/html/nautilus/storage/logs/debug_auth.log"
echo ""
