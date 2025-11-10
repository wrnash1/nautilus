#!/bin/bash
#
# Nautilus Permission Fix Script
# Run this after copying files to /var/www/html/nautilus/
#
# Usage: sudo bash fix-permissions.sh
#

INSTALL_DIR="/var/www/html/nautilus"

echo "════════════════════════════════════════════════════════════"
echo "  Nautilus Permission Fix Script"
echo "════════════════════════════════════════════════════════════"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Error: Please run as root (use sudo)"
    echo "   Example: sudo bash fix-permissions.sh"
    exit 1
fi

# Check if installation directory exists
if [ ! -d "$INSTALL_DIR" ]; then
    echo "❌ Error: Installation directory not found: $INSTALL_DIR"
    exit 1
fi

echo "📁 Installation directory: $INSTALL_DIR"
echo ""

# Set ownership
echo "→ Setting ownership to apache:apache..."
chown -R apache:apache "$INSTALL_DIR"
echo "  ✓ Ownership set"
echo ""

# Set base permissions
echo "→ Setting base permissions..."
find "$INSTALL_DIR" -type f -exec chmod 644 {} \;
find "$INSTALL_DIR" -type d -exec chmod 755 {} \;
echo "  ✓ Base permissions set (files: 644, directories: 755)"
echo ""

# Set writable directories
echo "→ Setting writable directories..."
chmod -R 775 "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/public/uploads"
echo "  ✓ Writable directories configured"
echo ""

# Create directories if they don't exist
echo "→ Creating required directories..."
mkdir -p "$INSTALL_DIR/storage/cache"
mkdir -p "$INSTALL_DIR/storage/logs"
mkdir -p "$INSTALL_DIR/storage/exports"
mkdir -p "$INSTALL_DIR/storage/backups"
mkdir -p "$INSTALL_DIR/public/uploads"
echo "  ✓ Directories created"
echo ""

# Set ownership again to ensure new directories are owned correctly
echo "→ Setting ownership on new directories..."
chown -R apache:apache "$INSTALL_DIR/storage"
chown -R apache:apache "$INSTALL_DIR/public/uploads"
echo "  ✓ Ownership confirmed"
echo ""

# Set SELinux context (if SELinux is enabled)
if command -v setenforce &> /dev/null; then
    echo "→ Configuring SELinux contexts..."
    semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/storage(/.*)?" 2>/dev/null || true
    semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/public/uploads(/.*)?" 2>/dev/null || true
    restorecon -Rv "$INSTALL_DIR/storage" 2>/dev/null || true
    restorecon -Rv "$INSTALL_DIR/public/uploads" 2>/dev/null || true
    echo "  ✓ SELinux contexts configured"
    echo ""
fi

# Verify permissions
echo "════════════════════════════════════════════════════════════"
echo "  Verification"
echo "════════════════════════════════════════════════════════════"
echo ""

echo "Storage directory permissions:"
ls -ld "$INSTALL_DIR/storage"
ls -ld "$INSTALL_DIR/storage/logs"
ls -ld "$INSTALL_DIR/storage/cache"
echo ""

echo "Uploads directory permissions:"
ls -ld "$INSTALL_DIR/public/uploads"
echo ""

echo "════════════════════════════════════════════════════════════"
echo "✅ Permission fix complete!"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "You can now access the installer at:"
echo "  https://nautilus.local/install.php"
echo ""
