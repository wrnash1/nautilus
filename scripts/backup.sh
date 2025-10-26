#!/bin/bash

################################################################################
# Nautilus Automated Backup Script
# Version: 2.0
# Purpose: Backup database and application files
# Schedule: Run daily via cron
################################################################################

set -e  # Exit on error

# Configuration
DB_NAME="${DB_DATABASE:-nautilus}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-}"
BACKUP_DIR="/var/backups/nautilus"
RETENTION_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)
DATE_FOLDER=$(date +%Y%m)

# Application directories
CUSTOMER_APP="/var/www/html/nautilus-customer"
STAFF_APP="/var/www/html/nautilus-staff"

# Log file
LOG_FILE="$BACKUP_DIR/backup.log"

################################################################################
# Helper Functions
################################################################################

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

error() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" | tee -a "$LOG_FILE"
}

################################################################################
# Create Backup Directories
################################################################################

mkdir -p "$BACKUP_DIR/$DATE_FOLDER/database"
mkdir -p "$BACKUP_DIR/$DATE_FOLDER/files"

log "Starting backup process"
log "Backup directory: $BACKUP_DIR/$DATE_FOLDER"

################################################################################
# Database Backup
################################################################################

log "Backing up database: $DB_NAME"

if [ -z "$DB_PASS" ]; then
    # No password
    mysqldump -u"$DB_USER" "$DB_NAME" | gzip > "$BACKUP_DIR/$DATE_FOLDER/database/${DB_NAME}_${DATE}.sql.gz"
else
    # With password
    mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/$DATE_FOLDER/database/${DB_NAME}_${DATE}.sql.gz"
fi

if [ $? -eq 0 ]; then
    DB_SIZE=$(du -sh "$BACKUP_DIR/$DATE_FOLDER/database/${DB_NAME}_${DATE}.sql.gz" | cut -f1)
    log "Database backup complete: $DB_SIZE"
else
    error "Database backup failed"
    exit 1
fi

################################################################################
# Application Files Backup
################################################################################

# Backup customer app storage
if [ -d "$CUSTOMER_APP/storage" ]; then
    log "Backing up customer app storage..."
    tar -czf "$BACKUP_DIR/$DATE_FOLDER/files/customer_storage_${DATE}.tar.gz" \
        -C "$CUSTOMER_APP" storage/ 2>/dev/null || true

    CUSTOMER_SIZE=$(du -sh "$BACKUP_DIR/$DATE_FOLDER/files/customer_storage_${DATE}.tar.gz" | cut -f1)
    log "Customer storage backup complete: $CUSTOMER_SIZE"
fi

# Backup customer app uploads
if [ -d "$CUSTOMER_APP/public/uploads" ]; then
    log "Backing up customer app uploads..."
    tar -czf "$BACKUP_DIR/$DATE_FOLDER/files/customer_uploads_${DATE}.tar.gz" \
        -C "$CUSTOMER_APP/public" uploads/ 2>/dev/null || true

    UPLOADS_SIZE=$(du -sh "$BACKUP_DIR/$DATE_FOLDER/files/customer_uploads_${DATE}.tar.gz" | cut -f1)
    log "Customer uploads backup complete: $UPLOADS_SIZE"
fi

# Backup staff app storage
if [ -d "$STAFF_APP/storage" ]; then
    log "Backing up staff app storage..."
    tar -czf "$BACKUP_DIR/$DATE_FOLDER/files/staff_storage_${DATE}.tar.gz" \
        -C "$STAFF_APP" storage/ 2>/dev/null || true

    STAFF_SIZE=$(du -sh "$BACKUP_DIR/$DATE_FOLDER/files/staff_storage_${DATE}.tar.gz" | cut -f1)
    log "Staff storage backup complete: $STAFF_SIZE"
fi

# Backup staff app uploads
if [ -d "$STAFF_APP/public/uploads" ]; then
    log "Backing up staff app uploads..."
    tar -czf "$BACKUP_DIR/$DATE_FOLDER/files/staff_uploads_${DATE}.tar.gz" \
        -C "$STAFF_APP/public" uploads/ 2>/dev/null || true

    STAFF_UPLOADS_SIZE=$(du -sh "$BACKUP_DIR/$DATE_FOLDER/files/staff_uploads_${DATE}.tar.gz" | cut -f1)
    log "Staff uploads backup complete: $STAFF_UPLOADS_SIZE"
fi

# Backup .env files (important for disaster recovery)
log "Backing up configuration files..."
if [ -f "$CUSTOMER_APP/.env" ]; then
    cp "$CUSTOMER_APP/.env" "$BACKUP_DIR/$DATE_FOLDER/files/customer.env" 2>/dev/null || true
fi
if [ -f "$STAFF_APP/.env" ]; then
    cp "$STAFF_APP/.env" "$BACKUP_DIR/$DATE_FOLDER/files/staff.env" 2>/dev/null || true
fi

################################################################################
# Cleanup Old Backups
################################################################################

log "Cleaning up backups older than $RETENTION_DAYS days..."

find "$BACKUP_DIR" -type f -name "*.gz" -mtime +$RETENTION_DAYS -delete 2>/dev/null || true
find "$BACKUP_DIR" -type f -name "*.env" -mtime +$RETENTION_DAYS -delete 2>/dev/null || true

# Remove empty directories
find "$BACKUP_DIR" -type d -empty -delete 2>/dev/null || true

log "Cleanup complete"

################################################################################
# Backup Summary
################################################################################

TOTAL_SIZE=$(du -sh "$BACKUP_DIR/$DATE_FOLDER" | cut -f1)
BACKUP_COUNT=$(find "$BACKUP_DIR" -type f -name "*.gz" | wc -l)

log "==================================="
log "Backup completed successfully"
log "Total size: $TOTAL_SIZE"
log "Total backups on disk: $BACKUP_COUNT"
log "Backup location: $BACKUP_DIR/$DATE_FOLDER"
log "==================================="

################################################################################
# Optional: Upload to Remote Storage
################################################################################

# Uncomment and configure for remote backup (S3, Google Cloud Storage, etc.)

# Example: AWS S3
# if command -v aws &> /dev/null; then
#     log "Uploading to S3..."
#     aws s3 sync "$BACKUP_DIR/$DATE_FOLDER" "s3://your-bucket/nautilus-backups/$DATE_FOLDER/"
#     log "S3 upload complete"
# fi

# Example: rsync to remote server
# if command -v rsync &> /dev/null; then
#     log "Syncing to remote server..."
#     rsync -avz "$BACKUP_DIR/$DATE_FOLDER" user@remote-server:/backups/nautilus/
#     log "Remote sync complete"
# fi

exit 0
