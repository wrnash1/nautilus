#!/bin/bash
##############################################################################
# Deploy Nautilus to Production (/var/www/html/nautilus)
# Run this script to copy files from development to production location
##############################################################################

set -e

echo "=========================================================================="
echo " Nautilus Dive Shop - Deploy to Production"
echo "=========================================================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Source and destination
SOURCE="/home/wrnash1/development/nautilus/"
DEST="/var/www/html/nautilus/"

echo "Source: $SOURCE"
echo "Destination: $DEST"
echo ""

# Check if source exists
if [ ! -d "$SOURCE" ]; then
    echo -e "${RED}✗ Error: Source directory not found: $SOURCE${NC}"
    exit 1
fi

# Create destination if it doesn't exist
if [ ! -d "$DEST" ]; then
    echo "Creating destination directory..."
    sudo mkdir -p "$DEST"
fi

# Rsync files (exclude git, vendor, logs, cache)
echo "Copying files..."
sudo rsync -av --delete \
    --exclude='.git' \
    --exclude='.git/*' \
    --exclude='vendor' \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    --exclude='storage/sessions/*' \
    --exclude='.installed' \
    --exclude='node_modules' \
    "$SOURCE" "$DEST"

echo -e "${GREEN}✓ Files copied${NC}"
echo ""

# Set ownership to apache
echo "Setting ownership to apache:apache..."
sudo chown -R apache:apache "$DEST"
echo -e "${GREEN}✓ Ownership set${NC}"
echo ""

# Set permissions
echo "Setting permissions..."
sudo chmod -R 755 "$DEST"
sudo chmod -R 775 "${DEST}storage"
sudo chmod -R 775 "${DEST}public/uploads"
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Install composer dependencies if needed
if [ ! -d "${DEST}vendor" ]; then
    echo "Installing Composer dependencies..."
    cd "$DEST"
    sudo -u apache composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
    echo ""
fi

# Check if .env exists
if [ ! -f "${DEST}.env" ]; then
    echo -e "${YELLOW}⚠ Warning: .env file not found${NC}"
    echo "The installer will guide you through configuration."
    echo ""
fi

# Check if .installed exists
if [ -f "${DEST}.installed" ]; then
    echo -e "${YELLOW}⚠ Warning: Installation marker found${NC}"
    echo "To reinstall, delete: ${DEST}.installed"
    echo ""
fi

echo "=========================================================================="
echo -e "${GREEN}✓ Deployment Complete!${NC}"
echo "=========================================================================="
echo ""
echo "Next steps:"
echo "1. Visit: https://nautilus.local/install/"
echo "2. Complete the 4-step installation wizard"
echo "3. Login with default credentials: admin@nautilus.local / admin123"
echo ""
echo "File locations:"
echo "  Application: $DEST"
echo "  Logs: ${DEST}storage/logs/"
echo "  Uploads: ${DEST}public/uploads/"
echo ""
echo "=========================================================================="
