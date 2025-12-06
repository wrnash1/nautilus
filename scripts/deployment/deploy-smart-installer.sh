#!/bin/bash
###############################################################################
# Deploy Smart Self-Healing Installer
# Copies the improved installer that auto-fixes permissions
###############################################################################

echo "=========================================="
echo "  Deploying Smart Installer"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

echo "1. Copying updated installer..."
cp /home/wrnash1/Developer/nautilus/public/install.php /var/www/html/nautilus/public/install.php
chown apache:apache /var/www/html/nautilus/public/install.php
chmod 644 /var/www/html/nautilus/public/install.php
echo "✓ Installer updated"
echo ""

echo "2. Setting initial permissions for auto-fix to work..."
chmod 775 /var/www/html/nautilus
chmod -R 775 /var/www/html/nautilus/storage
chown -R apache:apache /var/www/html/nautilus
echo "✓ Permissions set"
echo ""

echo "=========================================="
echo "  Deployment Complete!"
echo "=========================================="
echo ""
echo "✅ Smart installer is now active!"
echo ""
echo "🎯 What's new:"
echo "  • Auto-fixes permissions automatically"
echo "  • One-click permission repair button"
echo "  • Simplified error messages"
echo "  • Detects OS and provides correct commands"
echo "  • Non-technical language throughout"
echo ""
echo "🌐 Visit: https://nautilus.local/install.php"
echo ""
echo "The installer will now:"
echo "  1. Try to fix permissions automatically"
echo "  2. Show a friendly 'Try Auto-Fix' button if needed"
echo "  3. Provide simple copy-paste commands for hosting provider"
echo ""
echo "Perfect for dive shop owners with no Linux knowledge! 🤿"
echo ""
