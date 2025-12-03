#!/bin/bash
###############################################################################
# Fix Nautilus File Permissions
###############################################################################

echo "=========================================="
echo "  Fixing Nautilus Permissions"
echo "=========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    echo "Usage: sudo bash fix-permissions.sh"
    exit 1
fi

echo "1. Fixing permissions in source directory..."
chmod 644 /home/wrnash1/Developer/nautilus/public/install.php
echo "✓ Source file permissions fixed"
echo ""

echo "2. Checking if public directory exists in web root..."
if [ -d /var/www/html/nautilus/public ]; then
    echo "✓ Public directory exists"
else
    echo "✗ Public directory missing - copying now..."
    cp -R /home/wrnash1/Developer/nautilus/public /var/www/html/nautilus/
fi
echo ""

echo "3. Setting proper permissions on web files..."
chmod -R 755 /var/www/html/nautilus/public
chmod 644 /var/www/html/nautilus/public/*.php
chmod 644 /var/www/html/nautilus/public/*.html 2>/dev/null || true
chown -R apache:apache /var/www/html/nautilus/public
echo "✓ Permissions set"
echo ""

echo "4. Verifying install.php..."
if [ -f /var/www/html/nautilus/public/install.php ]; then
    ls -lZ /var/www/html/nautilus/public/install.php
    echo "✓ install.php is ready"
else
    echo "✗ install.php still missing - copying directly..."
    cp /home/wrnash1/Developer/nautilus/public/install.php /var/www/html/nautilus/public/
    chmod 644 /var/www/html/nautilus/public/install.php
    chown apache:apache /var/www/html/nautilus/public/install.php
fi
echo ""

echo "5. Fixing SELinux contexts..."
restorecon -Rv /var/www/html/nautilus/public 2>/dev/null || true
echo "✓ SELinux contexts restored"
echo ""

echo "6. Restarting Apache..."
systemctl restart httpd
echo "✓ Apache restarted"
echo ""

echo "=========================================="
echo "  Fix Complete!"
echo "=========================================="
echo ""
echo "Now try accessing:"
echo "  http://localhost/install.php"
echo ""
