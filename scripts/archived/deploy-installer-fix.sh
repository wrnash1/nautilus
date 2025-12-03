#!/bin/bash
###############################################################################
# Deploy Updated Installer with Fix Instructions
###############################################################################

echo "=========================================="
echo "  Deploying Improved Installer"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

echo "1. Copying updated installer..."
cp /home/wrnash1/Developer/nautilus/public/install.php /var/www/html/nautilus/public/install.php
chmod 644 /var/www/html/nautilus/public/install.php
chown apache:apache /var/www/html/nautilus/public/install.php
echo "✓ Installer deployed"
echo ""

echo "2. Fixing storage permissions..."
chmod -R 775 /var/www/html/nautilus/storage
chown -R apache:apache /var/www/html/nautilus/storage
echo "✓ Storage is now writable"
echo ""

echo "3. Fixing root directory permissions for .env creation..."
chmod 775 /var/www/html/nautilus
chown apache:apache /var/www/html/nautilus
echo "✓ Root directory is now writable"
echo ""

echo "4. Setting SELinux contexts..."
restorecon -Rv /var/www/html/nautilus/storage 2>/dev/null || true
restorecon -Rv /var/www/html/nautilus/public 2>/dev/null || true
echo "✓ SELinux contexts updated"
echo ""

echo "=========================================="
echo "  Deployment Complete!"
echo "=========================================="
echo ""
echo "✅ The installer now shows:"
echo "  • Specific fix instructions for each failed requirement"
echo "  • OS-specific commands (Fedora/RHEL vs Debian/Ubuntu)"
echo "  • 'Re-check Requirements' button"
echo "  • Detailed explanations of why each requirement is needed"
echo ""
echo "✅ Permissions fixed:"
echo "  • Storage directory is writable"
echo "  • .env file can be created"
echo ""
echo "Refresh your browser at: https://nautilus.local/install.php"
echo ""
