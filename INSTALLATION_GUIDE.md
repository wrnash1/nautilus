# Nautilus Installation Guide

## Quick Start

Run the automated installer:

```bash
cd /home/wrnash1/development/nautilus
./install.sh
```

The installer will:
- ✅ Check system requirements
- ✅ Configure environment
- ✅ Install dependencies
- ✅ Set up database
- ✅ Run migrations
- ✅ Configure cron jobs automatically
- ✅ Create admin user
- ✅ Run tests

## System Requirements

### Required Software
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.3+)
- **Composer**: Latest version
- **Web Server**: Apache 2.4+ or Nginx 1.18+

### PHP Extensions
- PDO
- PDO_MySQL
- JSON
- cURL
- mbstring
- OpenSSL
- GD

### Optional
- Redis (for session storage and caching)
- Memcached (alternative caching)

## Automated Installation

### Step 1: Clone/Download

```bash
cd /home/wrnash1/development
# If not already there
git clone <repository-url> nautilus
cd nautilus
```

### Step 2: Run Installer

```bash
./install.sh
```

Follow the prompts to configure:
- Database credentials
- Admin user
- Email settings (after installation)

### Step 3: Post-Installation

Review and complete the checklist created at:
```
POST_INSTALL_CHECKLIST.txt
```

## Manual Installation

If you prefer to install manually:

### 1. Environment Setup

```bash
cp .env.example .env
```

Edit `.env` with your settings:
```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdiveshop.com
MAIL_FROM_NAME="Nautilus Dive Shop"
```

### 2. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
for file in database/migrations/*.sql; do
    echo "Running $(basename $file)..."
    mysql -u username -p nautilus < "$file"
done
```

### 4. Directory Permissions

```bash
mkdir -p storage/{logs,cache,sessions,uploads,backups}
mkdir -p public/uploads
chmod -R 755 storage public/uploads
```

### 5. Cron Jobs Setup

```bash
crontab -e
```

Add these lines:

```cron
# Automated Notifications - Every hour
0 * * * * cd /home/wrnash1/development/nautilus && php app/Jobs/SendAutomatedNotificationsJob.php >> storage/logs/notifications.log 2>&1

# Daily Analytics - 1:00 AM daily
0 1 * * * cd /home/wrnash1/development/nautilus && php app/Jobs/CalculateDailyAnalyticsJob.php >> storage/logs/analytics.log 2>&1

# Cache Warmup - Every 6 hours
0 */6 * * * cd /home/wrnash1/development/nautilus && php app/Jobs/CacheWarmupJob.php >> storage/logs/cache.log 2>&1

# Database Backup - 2:00 AM daily
0 2 * * * cd /home/wrnash1/development/nautilus && php app/Jobs/DatabaseBackupJob.php >> storage/logs/backup.log 2>&1

# Data Cleanup - 3:00 AM every Sunday
0 3 * * 0 cd /home/wrnash1/development/nautilus && php app/Jobs/CleanupOldDataJob.php >> storage/logs/cleanup.log 2>&1

# Scheduled Reports - 9:00 AM every Monday
0 9 * * 1 cd /home/wrnash1/development/nautilus && php app/Jobs/SendScheduledReportsJob.php >> storage/logs/reports.log 2>&1
```

## Web Server Configuration

### Apache

Create VirtualHost configuration:

```apache
<VirtualHost *:80>
    ServerName nautilus.yourdomain.com
    DocumentRoot /home/wrnash1/development/nautilus/public

    <Directory /home/wrnash1/development/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
```

Enable required modules:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Nginx

Create server block:

```nginx
server {
    listen 80;
    server_name nautilus.yourdomain.com;
    root /home/wrnash1/development/nautilus/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Scheduled Jobs Overview

| Job | Schedule | Purpose | Log File |
|-----|----------|---------|----------|
| SendAutomatedNotificationsJob | Every hour | Sends email notifications | notifications.log |
| CalculateDailyAnalyticsJob | 1:00 AM daily | Calculates KPIs and metrics | analytics.log |
| CacheWarmupJob | Every 6 hours | Pre-calculates dashboard metrics | cache.log |
| DatabaseBackupJob | 2:00 AM daily | Creates database backups | backup.log |
| CleanupOldDataJob | 3:00 AM Sunday | Removes old logs and data | cleanup.log |
| SendScheduledReportsJob | 9:00 AM Monday | Sends weekly reports | reports.log |

## Testing the Installation

### 1. Run Test Suite

```bash
composer test
```

### 2. Test Email Configuration

```bash
php test_email.php
```

### 3. Test Cron Jobs

Run jobs manually to verify they work:

```bash
# Test notifications
php app/Jobs/SendAutomatedNotificationsJob.php

# Test analytics
php app/Jobs/CalculateDailyAnalyticsJob.php

# Test cache warmup
php app/Jobs/CacheWarmupJob.php

# Test backup
php app/Jobs/DatabaseBackupJob.php
```

### 4. Access the Application

Open your browser and navigate to:
```
http://nautilus.yourdomain.com
```

Or if running locally:
```
http://localhost/nautilus/public
```

## Verification Checklist

After installation, verify these items:

- [ ] Application loads without errors
- [ ] Database connection works
- [ ] Admin login works
- [ ] Email configuration works (test_email.php)
- [ ] Cron jobs are scheduled (crontab -l)
- [ ] Log files are being created in storage/logs/
- [ ] Backups directory exists and is writable
- [ ] All migrations have run successfully
- [ ] Test suite passes

## Troubleshooting

### Database Connection Failed

1. Check credentials in `.env`
2. Verify MySQL is running: `systemctl status mysql`
3. Test connection: `mysql -h localhost -u username -p`

### Permission Denied Errors

```bash
chmod -R 755 storage
chmod -R 755 public/uploads
chown -R www-data:www-data storage public/uploads  # For Apache
```

### Cron Jobs Not Running

1. Check crontab is set: `crontab -l`
2. Check cron service: `systemctl status cron`
3. Check log files for errors
4. Verify PHP path: `which php`

### Email Not Sending

1. Test SMTP credentials
2. Check firewall allows outbound port 587/465
3. Review `storage/logs/application.log`
4. Verify PHPMailer is installed: `composer show | grep phpmailer`

### 500 Internal Server Error

1. Check Apache/Nginx error logs
2. Verify .htaccess exists in public/
3. Check file permissions
4. Enable PHP error display temporarily

## Upgrading

To upgrade to a new version:

```bash
# Backup database first
php app/Jobs/DatabaseBackupJob.php

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev

# Run new migrations
mysql -u username -p nautilus < database/migrations/NEW_MIGRATION.sql

# Clear cache
rm -rf storage/cache/*

# Run tests
composer test
```

## Security Hardening

### 1. Change Default Credentials

```sql
UPDATE users SET password = PASSWORD_HASH_HERE WHERE username = 'admin';
```

### 2. Restrict File Permissions

```bash
chmod 600 .env
chmod 755 storage
```

### 3. Enable HTTPS

Use Let's Encrypt for free SSL:

```bash
sudo certbot --apache -d nautilus.yourdomain.com
```

### 4. Configure Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 5. Disable Directory Listing

Already configured in .htaccess:
```apache
Options -Indexes
```

## Performance Tuning

### PHP Configuration

Edit `php.ini`:
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
opcache.enable = 1
opcache.memory_consumption = 128
```

### MySQL Tuning

```sql
-- Adjust based on your server
SET GLOBAL query_cache_size = 67108864;
SET GLOBAL innodb_buffer_pool_size = 1G;
```

### Enable OPcache

Ensure in `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
```

## Support

For issues or questions:

1. Check documentation in `docs/` directory
2. Review logs in `storage/logs/`
3. Run tests to identify issues: `composer test`
4. Check GitHub issues (if applicable)

## Maintenance

### Daily
- Monitor log files
- Check backup completion

### Weekly
- Review analytics dashboard
- Check disk space
- Review notification statistics

### Monthly
- Update dependencies: `composer update`
- Review and optimize database
- Test backup restoration
- Review security logs

## Additional Resources

- [Analytics Dashboard Documentation](docs/ANALYTICS_DASHBOARD.md)
- [Automated Notifications Documentation](docs/AUTOMATED_NOTIFICATIONS.md)
- [Development Summary](DEVELOPMENT_SUMMARY.md)
- [Post-Install Checklist](POST_INSTALL_CHECKLIST.txt)
