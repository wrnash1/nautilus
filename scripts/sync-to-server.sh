#!/bin/bash

###############################################################################
# Nautilus - Sync to Local Test Server
# Copies files from development directory to /var/www/html/nautilus
###############################################################################

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

echo -e "${BLUE}═══════════════════════════════════════════${NC}"
echo -e "${BLUE}  Syncing Nautilus to Test Server${NC}"
echo -e "${BLUE}═══════════════════════════════════════════${NC}"
echo ""

SOURCE_DIR="/home/wrnash1/Developer/nautilus"
DEST_DIR="/var/www/html/nautilus"

# Check if source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    echo "Error: Source directory not found: $SOURCE_DIR"
    exit 1
fi

# Check if destination directory exists
if [ ! -d "$DEST_DIR" ]; then
    print_warning "Destination directory doesn't exist: $DEST_DIR"
    print_info "Creating destination directory..."
    sudo mkdir -p "$DEST_DIR"
fi

print_info "Copying files from $SOURCE_DIR to $DEST_DIR..."

# Copy files with rsync (preserves permissions, excludes git and vendor)
sudo rsync -av --delete \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    --exclude='storage/sessions/*' \
    "$SOURCE_DIR/" "$DEST_DIR/"

print_success "Files copied successfully"

# Set ownership
print_info "Setting ownership to www-data:www-data..."
sudo chown -R www-data:www-data "$DEST_DIR"
print_success "Ownership set"

# Set permissions
print_info "Setting permissions..."
sudo chmod -R 755 "$DEST_DIR"
sudo chmod -R 775 "$DEST_DIR/storage"
sudo chmod 640 "$DEST_DIR/.env"
print_success "Permissions set"

echo ""
echo -e "${GREEN}═══════════════════════════════════════════${NC}"
print_success "Sync complete!"
echo -e "${GREEN}═══════════════════════════════════════════${NC}"
echo ""
print_info "Test server location: $DEST_DIR"
print_info "You can now test at: https://pangolin.local/"
echo ""
