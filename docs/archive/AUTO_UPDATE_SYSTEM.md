# Nautilus Auto-Update System

## Overview

Nautilus includes an automated update system that allows dive shops to easily receive and install updates without manual file transfers or disrupting operations.

## Update Mechanism

### 1. Update Check (Automated)

The system automatically checks for updates daily via cron:

```bash
# Add to crontab
0 2 * * * php /var/www/html/nautilus/bin/check-updates.php
```

### 2. Update Notification

Admins see a notification banner when updates are available:

```
🔔 Nautilus v2.1.0 is available! (Current: v2.0.5)
  [View Changelog] [Update Now] [Ignore]
```

### 3. One-Click Update

Click "Update Now" to:
1. Backup current installation
2. Download update package
3. Run database migrations
4. Clear caches
5. Verify installation

## Installation (For First-Time Setup)

### Enable Auto-Updates

```bash
cd /var/www/html/nautilus
php bin/setup-auto-updates.php
```

This creates:
- Update check cron job
- Backup directory structure
- Update configuration file

## Update Channels

| Channel | Description | Recommended For |
|---|---|---|
| **Stable** | Tested production releases | All dive shops (default) |
| **Beta** | Pre-release testing | Advanced users |
| **Dev** | Development builds | Developers only |

Configure in: **Admin → Settings → Updates**

## Manual Update Process

If automatic updates fail or you prefer manual control:

### Option 1: Git Pull (If using Git)

```bash
cd /var/www/html/nautilus
git pull origin main
composer install --no-dev
php bin/migrate.php
```

### Option 2: Download & Replace

```bash
# Backup current installation
cp -r /var/www/html/nautilus /var/backups/nautilus-$(date +%Y%m%d)

# Download latest release
wget https://github.com/yourusername/nautilus/releases/latest/download/nautilus.zip

# Extract
unzip nautilus.zip -d /tmp/nautilus-update

# Replace files (preserves .env and uploads)
rsync -av --exclude='.env' --exclude='storage' --exclude='public/uploads' \
    /tmp/nautilus-update/ /var/www/html/nautilus/

# Run migrations
cd /var/www/html/nautilus
php bin/migrate.php
```

### Option 3: Web-Based Updater

Visit: **https://yoursite.com/admin/updates**

Steps:
1. Click "Check for Updates"
2. Review changelog
3. Click "Download Update"
4. Click "Install Update"
5. Verify installation

## Migration System

### Automatic Migration Tracking

Nautilus tracks which migrations have been run:

```sql
CREATE TABLE migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Running Migrations

```bash
# Run all pending migrations
php bin/migrate.php

# Run specific migration
php bin/migrate.php --only=099_google_contacts_sync.sql

# Rollback last batch
php bin/migrate.php --rollback

# Check migration status
php bin/migrate.php --status
```

### Migration Validation

Before updating, validate all migrations:

```bash
bash scripts/validate-migrations.sh
```

## Rollback Procedure

If an update causes issues:

### Quick Rollback

```bash
# Restore from automatic backup
php bin/rollback-update.php

# Or manually:
rm -rf /var/www/html/nautilus
cp -r /var/backups/nautilus-latest /var/www/html/nautilus
```

### Database Rollback

```bash
# Restore database
mysql -u root -p nautilus < /var/backups/nautilus-db-latest.sql

# Or use migration rollback
php bin/migrate.php --rollback
```

## Update Safety Features

### 1. Pre-Update Checks

- PHP version compatibility
- MySQL version compatibility
- Required PHP extensions
- Disk space availability
- File permissions

### 2. Automatic Backups

Before every update:
- Full database dump
- Complete file backup
- Configuration backup

### 3. Maintenance Mode

During updates:
- Site enters maintenance mode
- Users see friendly message
- Critical operations queued

### 4. Verification

After update:
- Database integrity check
- File integrity check
- Service health check
- Migration status verification

## Multi-Store Updates

For franchises running multiple stores:

### Centralized Update Management

```bash
# Update all stores from central server
bash scripts/update-all-stores.sh
```

This script:
1. Connects to each store via SSH
2. Runs pre-update checks
3. Executes update
4. Verifies installation
5. Sends status report

### Staged Rollout

Update stores gradually:

```bash
# Update 10% of stores first
bash scripts/update-stores.sh --percentage=10

# If successful, update remaining
bash scripts/update-stores.sh --percentage=100
```

## Developer Workflow

### Creating Update Packages

```bash
# 1. Update version number
echo "2.1.0" > VERSION

# 2. Tag release
git tag -a v2.1.0 -m "Release v2.1.0"
git push origin v2.1.0

# 3. Create release package
bash scripts/create-release.sh
```

### Testing Updates

```bash
# Test update on staging server
bash scripts/test-update.sh --server=staging

# Run automated tests
bash scripts/run-update-tests.sh
```

## Update Configuration

Edit `/var/www/html/nautilus/config/updates.php`:

```php
return [
    // Update channel
    'channel' => 'stable', // stable, beta, dev
    
    // Auto-check frequency
    'check_frequency' => 'daily', // hourly, daily, weekly
    
    // Auto-install updates
    'auto_install' => false, // true for automated updates
    
    // Backup retention
    'keep_backups' => 5, // Number of backups to keep
    
    // Update server
    'update_server' => 'https://updates.nautilus-diving.com',
    
    // Maintenance mode message
    'maintenance_message' => 'We\'re updating Nautilus. We\'ll be back in 5 minutes!',
];
```

## Troubleshooting

### Update Failed

```bash
# Check update log
cat /var/www/html/nautilus/storage/logs/update.log

# Verify permissions
bash scripts/fix-permissions.sh

# Retry update
php bin/update.php --force
```

### Database Migration Failed

```bash
# Check migration log
cat /var/www/html/nautilus/storage/logs/migration.log

# Fix migration
php bin/migrate.php --fix

# Skip problematic migration (use with caution)
php bin/migrate.php --skip=099
```

### Rollback Failed

```bash
# List available backups
ls -la /var/backups/nautilus-*

# Restore specific backup
bash scripts/restore-backup.sh /var/backups/nautilus-20251126
```

## API for Custom Update Tools

```php
// Check for updates
$updater = new \App\Services\UpdateService();
$available = $updater->checkForUpdates();

// Download update
$updater->downloadUpdate($available['version']);

// Install update
$updater->installUpdate([
    'skip_backup' => false,
    'run_migrations' => true,
    'clear_cache' => true
]);

// Rollback
$updater->rollback();
```

## Security

### Update Verification

All updates are cryptographically signed:

```bash
# Verify update signature
php bin/verify-update.php nautilus-2.1.0.zip
```

### HTTPS Required

Updates only download over HTTPS to prevent man-in-the-middle attacks.

### Checksum Validation

Every file in the update package includes an MD5 checksum for integrity verification.

## Notification System

### Email Alerts

Admins receive emails for:
- Update available
- Update completed
- Update failed
- Rollback performed

### Dashboard Notifications

Real-time notifications in admin dashboard:
- Update progress
- Migration status
- Error messages

## Best Practices

1. **Always backup before updating** - Even with automatic backups
2. **Test on staging first** - If you have a staging environment
3. **Update during off-hours** - Minimize disruption to users
4. **Review changelog** - Understand what's changing
5. **Verify after update** - Check critical functionality
6. **Keep backups** - Retain at least 5 recent backups
7. **Monitor logs** - Watch `/storage/logs/` after updates

## Scheduled Maintenance Windows

Configure automatic update windows:

```php
// config/updates.php
'maintenance_windows' => [
    [
        'day' => 'sunday',
        'start' => '02:00',
        'end' => '04:00'
    ]
],
```

Updates will only install during these windows if auto-install is enabled.

## Support

For update issues:
- **Documentation**: `/docs/UPDATE_GUIDE.md`
- **Forum**: https://community.nautilus-diving.com
- **Support**: support@nautilus-diving.com
- **Emergency**: Use rollback procedure

---

**Last Updated**: November 26, 2025  
**Next Review**: December 26, 2025
