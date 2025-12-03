#!/bin/bash
###############################################################################
# Update Web Installer with Improved Version
###############################################################################

echo "=========================================="
echo "  Updating Nautilus Web Installer"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

echo "1. Backing up current installer..."
if [ -f /var/www/html/nautilus/public/install.php ]; then
    cp /var/www/html/nautilus/public/install.php /var/www/html/nautilus/public/install.php.old
    echo "✓ Backed up to install.php.old"
else
    echo "⚠ No existing installer found"
fi
echo ""

echo "2. Copying improved installer..."
cp /home/wrnash1/Developer/nautilus/public/install.php /var/www/html/nautilus/public/install.php
chmod 644 /var/www/html/nautilus/public/install.php
chown apache:apache /var/www/html/nautilus/public/install.php
echo "✓ Installer updated"
echo ""

echo "3. Removing .installed file to allow reinstall..."
if [ -f /var/www/html/nautilus/.installed ]; then
    rm /var/www/html/nautilus/.installed
    echo "✓ Removed .installed file"
else
    echo "✓ No .installed file found"
fi
echo ""

echo "4. Clearing any existing .env file..."
if [ -f /var/www/html/nautilus/.env ]; then
    mv /var/www/html/nautilus/.env /var/www/html/nautilus/.env.old
    echo "✓ Backed up .env to .env.old"
else
    echo "✓ No .env file found"
fi
echo ""

echo "=========================================="
echo "  Update Complete!"
echo "=========================================="
echo ""
echo "✅ Improved installer is now active!"
echo ""
echo "New features:"
echo "  • Application Name configuration (used throughout)"
echo "  • Company Name configuration (saved to database & .env)"
echo "  • Timezone setting"
echo "  • Better .env file with all settings"
echo "  • HTTPS-aware APP_URL"
echo "  • Enhanced security settings"
echo "  • More detailed status messages"
echo ""
echo "Visit: https://nautilus.local/install.php"
echo ""
