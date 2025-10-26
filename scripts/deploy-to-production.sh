#!/bin/bash

################################################################################
# Nautilus Production Deployment Script
# Version: 2.0
# Purpose: Deploy both applications to production web server
################################################################################

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
SOURCE_CUSTOMER="/home/wrnash1/development/nautilus-customer"
SOURCE_STAFF="/home/wrnash1/development/nautilus-staff"
TARGET_CUSTOMER="/var/www/html/nautilus-customer"
TARGET_STAFF="/var/www/html/nautilus-staff"
WEB_USER="www-data"
WEB_GROUP="www-data"

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

################################################################################
# Pre-flight Checks
################################################################################

print_header "Nautilus Production Deployment"
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    print_error "Please run with sudo"
    exit 1
fi

# Check source directories exist
if [ ! -d "$SOURCE_CUSTOMER" ]; then
    print_error "Customer app not found: $SOURCE_CUSTOMER"
    print_info "Run ./scripts/split-enterprise-apps.sh first"
    exit 1
fi

if [ ! -d "$SOURCE_STAFF" ]; then
    print_error "Staff app not found: $SOURCE_STAFF"
    print_info "Run ./scripts/split-enterprise-apps.sh first"
    exit 1
fi

# Confirm deployment
echo "This will deploy:"
echo "  Customer: $SOURCE_CUSTOMER → $TARGET_CUSTOMER"
echo "  Staff:    $SOURCE_STAFF → $TARGET_STAFF"
echo ""
read -p "Continue with production deployment? (yes/no) " -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    print_error "Deployment cancelled"
    exit 1
fi

################################################################################
# Backup Existing Installation
################################################################################

print_header "Step 1: Creating Backups"

BACKUP_DIR="/var/backups/nautilus/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

if [ -d "$TARGET_CUSTOMER" ]; then
    print_info "Backing up customer app..."
    tar -czf "$BACKUP_DIR/customer-backup.tar.gz" -C /var/www/html nautilus-customer
    print_success "Customer app backed up"
fi

if [ -d "$TARGET_STAFF" ]; then
    print_info "Backing up staff app..."
    tar -czf "$BACKUP_DIR/staff-backup.tar.gz" -C /var/www/html nautilus-staff
    print_success "Staff app backed up"
fi

print_success "Backups stored in: $BACKUP_DIR"

################################################################################
# Deploy Customer Application
################################################################################

print_header "Step 2: Deploying Customer Application"

# Create target directory
mkdir -p "$TARGET_CUSTOMER"

# Sync files (excluding vendor, .git, node_modules)
print_info "Syncing files..."
rsync -av --delete \
    --exclude='vendor/' \
    --exclude='.git/' \
    --exclude='node_modules/' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    --exclude='storage/sessions/*' \
    "$SOURCE_CUSTOMER/" "$TARGET_CUSTOMER/"

# Install composer dependencies
print_info "Installing dependencies..."
cd "$TARGET_CUSTOMER"
composer install --no-dev --optimize-autoloader --quiet

# Create necessary directories
mkdir -p storage/logs storage/cache storage/sessions storage/backups
mkdir -p public/uploads

# Set permissions
chown -R $WEB_USER:$WEB_GROUP "$TARGET_CUSTOMER"
chmod -R 755 "$TARGET_CUSTOMER"
chmod -R 775 "$TARGET_CUSTOMER/storage"
chmod -R 775 "$TARGET_CUSTOMER/public/uploads"

print_success "Customer application deployed"

################################################################################
# Deploy Staff Application
################################################################################

print_header "Step 3: Deploying Staff Application"

# Create target directory
mkdir -p "$TARGET_STAFF"

# Sync files
print_info "Syncing files..."
rsync -av --delete \
    --exclude='vendor/' \
    --exclude='.git/' \
    --exclude='node_modules/' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    --exclude='storage/sessions/*' \
    "$SOURCE_STAFF/" "$TARGET_STAFF/"

# Install composer dependencies
print_info "Installing dependencies..."
cd "$TARGET_STAFF"
composer install --no-dev --optimize-autoloader --quiet

# Create necessary directories
mkdir -p storage/logs storage/cache storage/sessions storage/backups
mkdir -p public/uploads

# Set permissions
chown -R $WEB_USER:$WEB_GROUP "$TARGET_STAFF"
chmod -R 755 "$TARGET_STAFF"
chmod -R 775 "$TARGET_STAFF/storage"
chmod -R 775 "$TARGET_STAFF/public/uploads"

print_success "Staff application deployed"

################################################################################
# Check Configuration
################################################################################

print_header "Step 4: Checking Configuration"

# Check if .env files exist
if [ ! -f "$TARGET_CUSTOMER/.env" ]; then
    print_warning "Customer .env not found - creating from example"
    cp "$TARGET_CUSTOMER/.env.example" "$TARGET_CUSTOMER/.env"
    print_warning "IMPORTANT: Edit $TARGET_CUSTOMER/.env with your settings!"
else
    print_success "Customer .env exists"
fi

if [ ! -f "$TARGET_STAFF/.env" ]; then
    print_warning "Staff .env not found - creating from example"
    cp "$TARGET_STAFF/.env.example" "$TARGET_STAFF/.env"
    print_warning "IMPORTANT: Edit $TARGET_STAFF/.env with your settings!"
else
    print_success "Staff .env exists"
fi

################################################################################
# Clear Caches
################################################################################

print_header "Step 5: Clearing Caches"

rm -rf "$TARGET_CUSTOMER/storage/cache/*"
rm -rf "$TARGET_STAFF/storage/cache/*"

print_success "Caches cleared"

################################################################################
# Restart Services
################################################################################

print_header "Step 6: Restarting Web Server"

if command -v apache2ctl &> /dev/null; then
    apache2ctl configtest && systemctl restart apache2
    print_success "Apache restarted"
elif command -v nginx &> /dev/null; then
    nginx -t && systemctl restart nginx
    print_success "Nginx restarted"
fi

################################################################################
# Final Summary
################################################################################

print_header "Deployment Complete!"

echo ""
echo -e "${GREEN}✓ Customer App:${NC} $TARGET_CUSTOMER"
echo -e "${GREEN}✓ Staff App:${NC} $TARGET_STAFF"
echo -e "${GREEN}✓ Backup:${NC} $BACKUP_DIR"
echo ""
echo -e "${YELLOW}Post-Deployment Checklist:${NC}"
echo ""
echo "1. Verify .env configuration:"
echo "   sudo nano $TARGET_CUSTOMER/.env"
echo "   sudo nano $TARGET_STAFF/.env"
echo ""
echo "2. Run database migrations (if needed):"
echo "   cd $TARGET_CUSTOMER && php scripts/migrate.php"
echo ""
echo "3. Test both applications:"
echo "   Customer: https://yourdomain.com"
echo "   Staff:    https://yourdomain.com/store"
echo ""
echo "4. Monitor error logs:"
echo "   sudo tail -f /var/log/apache2/error.log"
echo "   sudo tail -f $TARGET_STAFF/storage/logs/app.log"
echo ""

exit 0
