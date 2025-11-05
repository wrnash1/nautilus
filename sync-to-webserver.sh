#!/bin/bash
# Sync Nautilus development code to Apache web server

echo "=== Syncing Nautilus to Web Server ==="

# Source and destination
SRC="/home/wrnash1/development/nautilus/"
DEST="/var/www/html/nautilus/"

echo "Source: $SRC"
echo "Destination: $DEST"
echo ""

# Check if running as root or need sudo
if [ "$EUID" -ne 0 ]; then
    echo "This script requires sudo privileges to write to $DEST"
    echo "Running with sudo..."
    SUDO="sudo"
else
    SUDO=""
fi

# Sync files (exclude .git, vendor, and .env to preserve existing config)
$SUDO rsync -av --delete \
    --exclude='.git' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    "$SRC" "$DEST"

echo ""
echo "=== Syncing Composer vendor directory ==="
$SUDO rsync -av "$SRC/vendor/" "$DEST/vendor/"

echo ""
echo "=== Setting correct permissions ==="
$SUDO chown -R apache:apache "$DEST"
$SUDO chmod -R 755 "$DEST"
$SUDO chmod -R 775 "$DEST/storage"
$SUDO chmod -R 775 "$DEST/public/uploads"

echo ""
echo "✓ Sync complete!"
echo ""
echo "Access your application at: http://localhost/nautilus/public"
