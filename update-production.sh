#!/bin/bash
#
# Update Production Nautilus Installation
# Copies updated files from development to production
#
# Usage: sudo bash update-production.sh
#

DEV_DIR="/home/wrnash1/development/nautilus"
PROD_DIR="/var/www/html/nautilus"

echo "════════════════════════════════════════════════════════════"
echo "  Update Production Nautilus"
echo "════════════════════════════════════════════════════════════"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Error: Please run as root (use sudo)"
    echo "   Example: sudo bash update-production.sh"
    exit 1
fi

echo "📁 Development: $DEV_DIR"
echo "📁 Production:  $PROD_DIR"
echo ""

# Copy updated installer
echo "→ Copying updated install.php..."
cp "$DEV_DIR/install.php" "$PROD_DIR/install.php"
echo "  ✓ install.php updated"
echo ""

# Copy permission fix script
echo "→ Copying fix-permissions.sh..."
cp "$DEV_DIR/fix-permissions.sh" "$PROD_DIR/fix-permissions.sh"
chmod +x "$PROD_DIR/fix-permissions.sh"
echo "  ✓ fix-permissions.sh updated"
echo ""

# Set ownership
echo "→ Setting ownership..."
chown apache:apache "$PROD_DIR/install.php"
chown apache:apache "$PROD_DIR/fix-permissions.sh"
echo "  ✓ Ownership set"
echo ""

echo "════════════════════════════════════════════════════════════"
echo "✅ Production files updated!"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "Now run the permission fix script:"
echo "  sudo bash $PROD_DIR/fix-permissions.sh"
echo ""
