#!/bin/bash
#
# Comprehensive Production Deployment Script
# Deploys Nautilus from development to production safely
#
# Usage: sudo bash scripts/deploy-to-production.sh
#

set -e  # Exit on any error

DEV_DIR="/home/wrnash1/development/nautilus"
PROD_DIR="/var/www/html/nautilus"
BACKUP_DIR="/home/wrnash1/backups/nautilus-$(date +%Y%m%d-%H%M%S)"
LOG_FILE="/tmp/nautilus-deploy-$(date +%Y%m%d-%H%M%S).log"

echo "════════════════════════════════════════════════════════════" | tee -a "$LOG_FILE"
echo "  Nautilus Production Deployment" | tee -a "$LOG_FILE"
echo "  $(date)" | tee -a "$LOG_FILE"
echo "════════════════════════════════════════════════════════════" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Error: Please run as root (use sudo)" | tee -a "$LOG_FILE"
    exit 1
fi

# Check if development directory exists
if [ ! -d "$DEV_DIR" ]; then
    echo "❌ Error: Development directory not found: $DEV_DIR" | tee -a "$LOG_FILE"
    exit 1
fi

# Create backup directory
echo "→ Creating backup directory..." | tee -a "$LOG_FILE"
mkdir -p "$BACKUP_DIR"
echo "  ✓ Backup directory: $BACKUP_DIR" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Backup production if it exists
if [ -d "$PROD_DIR" ]; then
    echo "→ Backing up current production..." | tee -a "$LOG_FILE"

    # Backup .env file
    if [ -f "$PROD_DIR/.env" ]; then
        cp "$PROD_DIR/.env" "$BACKUP_DIR/.env"
        echo "  ✓ Backed up .env" | tee -a "$LOG_FILE"
    fi

    # Backup uploads
    if [ -d "$PROD_DIR/public/uploads" ]; then
        cp -r "$PROD_DIR/public/uploads" "$BACKUP_DIR/uploads"
        echo "  ✓ Backed up uploads" | tee -a "$LOG_FILE"
    fi

    # Backup storage (logs, cache, exports, backups)
    if [ -d "$PROD_DIR/storage" ]; then
        mkdir -p "$BACKUP_DIR/storage"
        for dir in logs exports backups; do
            if [ -d "$PROD_DIR/storage/$dir" ]; then
                cp -r "$PROD_DIR/storage/$dir" "$BACKUP_DIR/storage/"
                echo "  ✓ Backed up storage/$dir" | tee -a "$LOG_FILE"
            fi
        done
    fi

    echo "" | tee -a "$LOG_FILE"
fi

# Deploy new code
echo "→ Deploying new code..." | tee -a "$LOG_FILE"

# Create production directory if it doesn't exist
mkdir -p "$PROD_DIR"

# Sync files (excluding sensitive and generated files)
rsync -av --delete \
    --exclude='.git' \
    --exclude='.gitignore' \
    --exclude='.env' \
    --exclude='.env.local' \
    --exclude='.installed' \
    --exclude='storage/cache/*' \
    --exclude='storage/logs/*' \
    --exclude='public/uploads/*' \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='backups' \
    --exclude='*.log' \
    "$DEV_DIR/" "$PROD_DIR/" >> "$LOG_FILE" 2>&1

echo "  ✓ Code synced" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Restore .env if it was backed up, otherwise copy example
echo "→ Configuring environment..." | tee -a "$LOG_FILE"
if [ -f "$BACKUP_DIR/.env" ]; then
    cp "$BACKUP_DIR/.env" "$PROD_DIR/.env"
    echo "  ✓ Restored .env from backup" | tee -a "$LOG_FILE"
elif [ -f "$PROD_DIR/.env.example" ]; then
    cp "$PROD_DIR/.env.example" "$PROD_DIR/.env"
    echo "  ⚠ Created .env from example - Please configure manually" | tee -a "$LOG_FILE"
fi
echo "" | tee -a "$LOG_FILE"

# Restore uploads
echo "→ Restoring uploads..." | tee -a "$LOG_FILE"
if [ -d "$BACKUP_DIR/uploads" ]; then
    cp -r "$BACKUP_DIR/uploads/"* "$PROD_DIR/public/uploads/" 2>/dev/null || true
    echo "  ✓ Uploads restored" | tee -a "$LOG_FILE"
else
    echo "  ℹ No uploads to restore" | tee -a "$LOG_FILE"
fi
echo "" | tee -a "$LOG_FILE"

# Create required directories
echo "→ Creating required directories..." | tee -a "$LOG_FILE"
mkdir -p "$PROD_DIR/storage/cache"
mkdir -p "$PROD_DIR/storage/logs"
mkdir -p "$PROD_DIR/storage/exports"
mkdir -p "$PROD_DIR/storage/backups"
mkdir -p "$PROD_DIR/public/uploads"
mkdir -p "$PROD_DIR/public/uploads/logos"
echo "  ✓ Directories created" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Set ownership
echo "→ Setting ownership..." | tee -a "$LOG_FILE"
chown -R apache:apache "$PROD_DIR"
echo "  ✓ Owner set to apache:apache" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Set permissions
echo "→ Setting permissions..." | tee -a "$LOG_FILE"
find "$PROD_DIR" -type f -exec chmod 644 {} \;
find "$PROD_DIR" -type d -exec chmod 755 {} \;
chmod -R 775 "$PROD_DIR/storage"
chmod -R 775 "$PROD_DIR/public/uploads"
chmod 600 "$PROD_DIR/.env" 2>/dev/null || true
echo "  ✓ Permissions set" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Install/update composer dependencies
echo "→ Installing composer dependencies..." | tee -a "$LOG_FILE"
cd "$PROD_DIR"
if [ -f "composer.json" ]; then
    sudo -u apache composer install --no-dev --optimize-autoloader >> "$LOG_FILE" 2>&1 || \
    composer install --no-dev --optimize-autoloader >> "$LOG_FILE" 2>&1
    echo "  ✓ Dependencies installed" | tee -a "$LOG_FILE"
else
    echo "  ⚠ No composer.json found" | tee -a "$LOG_FILE"
fi
echo "" | tee -a "$LOG_FILE"

# Configure SELinux if enabled
if command -v setenforce &> /dev/null; then
    echo "→ Configuring SELinux..." | tee -a "$LOG_FILE"
    semanage fcontext -a -t httpd_sys_rw_content_t "$PROD_DIR/storage(/.*)?" 2>/dev/null || true
    semanage fcontext -a -t httpd_sys_rw_content_t "$PROD_DIR/public/uploads(/.*)?" 2>/dev/null || true
    restorecon -Rv "$PROD_DIR/storage" 2>/dev/null || true
    restorecon -Rv "$PROD_DIR/public/uploads" 2>/dev/null || true
    echo "  ✓ SELinux configured" | tee -a "$LOG_FILE"
    echo "" | tee -a "$LOG_FILE"
fi

# Clear cache
echo "→ Clearing cache..." | tee -a "$LOG_FILE"
rm -rf "$PROD_DIR/storage/cache/"* 2>/dev/null || true
echo "  ✓ Cache cleared" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Verify deployment
echo "→ Verifying deployment..." | tee -a "$LOG_FILE"
VERIFY_ERRORS=0

# Check critical files exist
for file in "public/index.php" "app/Core/Router.php" "routes/web.php"; do
    if [ ! -f "$PROD_DIR/$file" ]; then
        echo "  ✗ Missing: $file" | tee -a "$LOG_FILE"
        VERIFY_ERRORS=$((VERIFY_ERRORS + 1))
    fi
done

# Check critical directories exist
for dir in "app" "public" "storage" "database"; do
    if [ ! -d "$PROD_DIR/$dir" ]; then
        echo "  ✗ Missing: $dir/" | tee -a "$LOG_FILE"
        VERIFY_ERRORS=$((VERIFY_ERRORS + 1))
    fi
done

if [ $VERIFY_ERRORS -eq 0 ]; then
    echo "  ✓ All critical files present" | tee -a "$LOG_FILE"
else
    echo "  ⚠ Found $VERIFY_ERRORS missing critical files/directories" | tee -a "$LOG_FILE"
fi
echo "" | tee -a "$LOG_FILE"

# Summary
echo "════════════════════════════════════════════════════════════" | tee -a "$LOG_FILE"
echo "✅ Deployment Complete!" | tee -a "$LOG_FILE"
echo "════════════════════════════════════════════════════════════" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"
echo "Deployment Summary:" | tee -a "$LOG_FILE"
echo "  Production: $PROD_DIR" | tee -a "$LOG_FILE"
echo "  Backup: $BACKUP_DIR" | tee -a "$LOG_FILE"
echo "  Log: $LOG_FILE" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"
echo "Next Steps:" | tee -a "$LOG_FILE"
echo "  1. Verify .env configuration: nano $PROD_DIR/.env" | tee -a "$LOG_FILE"
echo "  2. Run new migrations: Visit https://nautilus.local/install.php" | tee -a "$LOG_FILE"
echo "  3. Test the application: https://nautilus.local" | tee -a "$LOG_FILE"
echo "  4. Check logs: tail -f $PROD_DIR/storage/logs/*.log" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"
echo "If issues occur, restore from backup:" | tee -a "$LOG_FILE"
echo "  sudo rsync -av $BACKUP_DIR/ $PROD_DIR/" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"
