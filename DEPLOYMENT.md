# Nautilus Deployment Guide

## Server Requirements

### Minimum Requirements
- **OS:** Fedora 43 (or compatible RHEL-based Linux)
- **Web Server:** Apache 2.4+ with mod_rewrite
- **Database:** MariaDB 10.11+ or MySQL 8.0+
- **PHP:** 8.4.14+ with extensions:
  - pdo_mysql
  - mbstring
  - json
  - openssl
  - curl
  - gd (for image processing)
  - zip
  - xml

### Recommended Resources
- **RAM:** 4GB minimum, 8GB recommended
- **Storage:** 20GB minimum for application + database
- **CPU:** 2+ cores

## Installation Steps

### 1. Install Dependencies

```bash
# Update system
sudo dnf update -y

# Install Apache, MariaDB, PHP 8.4
sudo dnf install -y httpd mariadb-server php php-mysqlnd php-mbstring \
    php-json php-openssl php-curl php-gd php-zip php-xml

# Enable and start services
sudo systemctl enable httpd mariadb
sudo systemctl start httpd mariadb
```

### 2. Configure MariaDB

```bash
# Secure installation
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clone and Configure Application

```bash
# Clone repository
cd /var/www/html
sudo git clone https://github.com/yourusername/nautilus.git
cd nautilus

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Set up environment
cp .env.example .env
nano .env
```

**Edit .env file:**
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_DATABASE=nautilus
DB_USERNAME=nautilus
DB_PASSWORD=YOUR_STRONG_PASSWORD

# Generate these with: php -r "echo bin2hex(random_bytes(32));"
APP_KEY=your-64-character-hex-key-here
JWT_SECRET=your-64-character-hex-key-here
```

### 4. Run Migrations

```bash
# Run database migrations
php database/migrate.php

# Seed initial data (roles, permissions)
php database/seed.php
```

### 5. Configure Apache

```bash
sudo nano /etc/httpd/conf.d/nautilus.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/nautilus-error.log
    CustomLog /var/log/httpd/nautilus-access.log combined

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAdmin admin@yourdomain.com
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    SSLCertificateChainFile /path/to/your/chain.crt

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/nautilus-ssl-error.log
    CustomLog /var/log/httpd/nautilus-ssl-access.log combined
</VirtualHost>
```

### 6. Set Permissions

```bash
# Set ownership
sudo chown -R apache:apache /var/www/html/nautilus

# Set directory permissions
sudo find /var/www/html/nautilus -type d -exec chmod 755 {} \;
sudo find /var/www/html/nautilus -type f -exec chmod 644 {} \;

# Storage directories need write access
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chmod -R 775 /var/www/html/nautilus/public/uploads
```

### 7. Configure SELinux (Fedora/RHEL)

```bash
# Allow Apache to write to storage
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/public/uploads(/.*)?"
sudo restorecon -Rv /var/www/html/nautilus/storage
sudo restorecon -Rv /var/www/html/nautilus/public/uploads

# Allow network connections if needed
sudo setsebool -P httpd_can_network_connect 1
```

### 8. Configure Firewall

```bash
# Open HTTP and HTTPS ports
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 9. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo dnf install -y certbot python3-certbot-apache

# Obtain certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
# Test renewal:
sudo certbot renew --dry-run
```

### 10. Restart Services

```bash
sudo systemctl restart httpd
```

## Post-Installation

### Create Admin User

Visit: `https://yourdomain.com/store/login`

Default credentials (CHANGE IMMEDIATELY):
- Email: `admin@nautilus.local`
- Password: `password`

### Configure Settings

1. Go to **Settings** in the admin panel
2. Update:
   - Store name and logo
   - Tax rates
   - Email settings (SMTP)
   - Payment processors (Stripe, Square)
   - Integration keys (PADI API, etc.)

## Backup Strategy

### Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/nautilus-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/nautilus"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u nautilus -p'YOUR_PASSWORD' nautilus | gzip > $BACKUP_DIR/nautilus_db_$DATE.sql.gz

# Backup uploads
tar -czf $BACKUP_DIR/nautilus_uploads_$DATE.tar.gz /var/www/html/nautilus/public/uploads

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/nautilus-backup.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
0 2 * * * /usr/local/bin/nautilus-backup.sh
```

## Security Checklist

- [ ] Changed default admin password
- [ ] Set `APP_ENV=production` in .env
- [ ] Set `APP_DEBUG=false` in .env
- [ ] Removed all debug files from public/
- [ ] SSL certificate installed and working
- [ ] Strong database password
- [ ] Unique APP_KEY and JWT_SECRET generated
- [ ] File permissions set correctly (755/644)
- [ ] SELinux contexts configured
- [ ] Firewall configured
- [ ] Regular backups scheduled
- [ ] Error logs monitored
- [ ] Updated all integration API keys

## Monitoring

### Check Logs

```bash
# Application logs
tail -f /var/www/html/nautilus/storage/logs/app.log

# Apache error logs
tail -f /var/log/httpd/nautilus-error.log

# Apache access logs
tail -f /var/log/httpd/nautilus-access.log
```

### Database Monitoring

```bash
# Check database size
sudo mysql -u root -p -e "SELECT table_schema AS 'Database', \
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' \
    FROM information_schema.TABLES \
    WHERE table_schema = 'nautilus';"
```

## Updating the Application

```bash
# Backup first!
/usr/local/bin/nautilus-backup.sh

# Pull latest changes
cd /var/www/html/nautilus
sudo -u apache git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php database/migrate.php

# Clear cache (if implemented)
# php artisan cache:clear

# Restart Apache
sudo systemctl restart httpd
```

## Troubleshooting

### Check PHP Version
```bash
php -v
```

### Check PHP Extensions
```bash
php -m | grep -E 'pdo_mysql|mbstring|json'
```

### Check Apache Configuration
```bash
sudo apachectl configtest
```

### Check Database Connection
```bash
mysql -u nautilus -p nautilus
```

### Permission Issues
```bash
# Reset permissions
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

## Performance Optimization

### Enable OpCache

Edit `/etc/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### MariaDB Optimization

Edit `/etc/my.cnf.d/server.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size=2G
max_connections=200
query_cache_size=32M
```

### Apache MPM Configuration

```bash
sudo nano /etc/httpd/conf.modules.d/00-mpm.conf
```

Enable event MPM for better performance.

## Support

For issues or questions:
- GitHub Issues: https://github.com/yourusername/nautilus/issues
- Documentation: https://docs.nautilus.local

---

**Version:** 2.0 Alpha
**Last Updated:** November 5, 2025
