#!/bin/bash
##############################################################################
# Sync Installer Fixes to Production
# Run this to copy the fixed installer files to /var/www/html/nautilus/
##############################################################################

echo "Syncing installer fixes to production..."

sudo rsync -av /home/wrnash1/development/nautilus/public/install/ /var/www/html/nautilus/public/install/

echo "✓ Installer files synced!"
echo ""
echo "Fixes applied:"
echo "  1. Warnings no longer block installation (only errors do)"
echo "  2. Improved mod_rewrite detection for Fedora"
echo "  3. mod_rewrite check now non-critical (warning instead of error)"
echo ""
echo "Refresh your browser and click 'Retry Checks' or reload the installer page."
