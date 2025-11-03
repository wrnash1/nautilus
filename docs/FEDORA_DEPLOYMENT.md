# Nautilus - Fedora Server 43 Deployment Guide

This guide provides specific instructions for deploying Nautilus on Fedora Server 43 in a production environment.

## Prerequisites

- Fedora Server 43
- Root or sudo access
- Static IP address or domain name configured
- Firewall access to ports 80 and 443

## Installation Steps

### 1. System Update

```bash
sudo dnf update -y
sudo dnf upgrade -y
```

### 2. Install PHP 8.2 and Extensions

Fedora 43 includes PHP 8.2+ by default:

```bash
# Install PHP and required extensions
sudo dnf install -y php php-fpm php-mysqlnd php-mbstring php-xml \
    php-curl php-gd php-zip php-json php-opcache php-intl \
    php-pdo php-bcmath php-soap php-xmlrpc

# Verify PHP version
php --version
```

### 3. Install and Configure MariaDB

Fedora uses MariaDB as the MySQL implementation:

```bash
# Install MariaDB
sudo dnf install -y mariadb-server mariadb

# Start and enable MariaDB
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Secure MariaDB installation
sudo mysql_secure_installation
```

Answer the prompts:
- Set root password: **Yes** (choose a strong password)
- Remove anonymous users: **Yes**
- Disallow root login remotely: **Yes**
- Remove test database: **Yes**
- Reload privilege tables: **Yes**

### 4. Create Database and User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Install Composer

```bash
# Install Composer globally
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

# Verify installation
composer --version
```

### 6. Install Web Server

#### Option A: Apache (Recommended)

```bash
# Install Apache
sudo dnf install -y httpd

# Start and enable Apache
sudo systemctl start httpd
sudo systemctl enable httpd
```

#### Option B: Nginx

```bash
# Install Nginx
sudo dnf install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx
```

### 7. Deploy Nautilus Application

```bash
# Create web directory
sudo mkdir -p /var/www/nautilus
cd /var/www/nautilus

# Clone or upload application files
# If using git:
sudo dnf install -y git
sudo git clone https://github.com/your-repo/nautilus.git .

# Or upload files via SCP/SFTP to /var/www/nautilus

# Set ownership
sudo chown -R apache:apache /var/www/nautilus
# OR for Nginx:
# sudo chown -R nginx:nginx /var/www/nautilus

# Install PHP dependencies
cd /var/www/nautilus
sudo -u apache composer install --no-dev --optimize-autoloader
# OR for Nginx:
# sudo -u nginx composer install --no-dev --optimize-autoloader
```

### 8. Configure Environment

```bash
# Copy environment file
sudo cp .env.example .env

# Edit configuration
sudo nano .env
```

Update the following values:
```ini
DB_NAME=nautilus
DB_USER=nautilus
DB_PASS=YOUR_SECURE_PASSWORD_HERE
DB_HOST=localhost

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### 9. Set Permissions

```bash
# Create required directories
sudo mkdir -p storage/logs storage/cache storage/sessions
sudo mkdir -p public/uploads

# Set permissions
sudo chown -R apache:apache storage/ public/uploads/ logs/
sudo chmod -R 775 storage/ public/uploads/ logs/
sudo chmod -R 755 public/

# For Nginx, use nginx:nginx instead of apache:apache
```

### 10. Run Database Migrations

```bash
cd /var/www/nautilus
sudo -u apache php scripts/migrate.php
```

### 11. Configure SELinux

Fedora uses SELinux by default. Configure it for the web application:

```bash
# Set SELinux context for web files
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/nautilus/public/uploads(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/nautilus/logs(/.*)?"
sudo restorecon -Rv /var/www/nautilus

# Allow Apache to connect to database
sudo setsebool -P httpd_can_network_connect_db 1

# Allow Apache to send emails (if using email features)
sudo setsebool -P httpd_can_sendmail 1
```

### 12. Configure Apache Virtual Host

```bash
sudo nano /etc/httpd/conf.d/nautilus.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/nautilus/public

    <Directory /var/www/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/nautilus-error.log
    CustomLog /var/log/httpd/nautilus-access.log combined
</VirtualHost>
```

Test and restart Apache:
```bash
sudo apachectl configtest
sudo systemctl restart httpd
```

### 13. Configure Nginx (If Using Nginx)

```bash
sudo nano /etc/nginx/conf.d/nautilus.conf
```

Add:
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/nautilus/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    error_log /var/log/nginx/nautilus-error.log;
    access_log /var/log/nginx/nautilus-access.log;
}
```

Configure PHP-FPM:
```bash
sudo nano /etc/php-fpm.d/www.conf
```

Ensure these settings:
```ini
user = nginx
group = nginx
listen = /run/php-fpm/www.sock
listen.owner = nginx
listen.group = nginx
```

Start services:
```bash
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
sudo systemctl restart nginx
```

### 14. Configure Firewall

```bash
# Allow HTTP and HTTPS
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload

# Verify
sudo firewall-cmd --list-all
```

### 15. Install SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo dnf install -y certbot

# For Apache:
sudo dnf install -y python3-certbot-apache
sudo certbot --apache -d your-domain.com -d www.your-domain.com

# For Nginx:
sudo dnf install -y python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

Follow the prompts and select option 2 to redirect HTTP to HTTPS.

### 16. Create Admin User

```bash
cd /var/www/nautilus
sudo -u apache php -r "
require 'vendor/autoload.php';
use App\Core\Database;

Database::connect();

\$email = 'admin@your-domain.com';
\$password = password_hash('YOUR_SECURE_ADMIN_PASSWORD', PASSWORD_DEFAULT);

Database::query(\"
    INSERT INTO users (first_name, last_name, email, password, role, is_active, created_at)
    VALUES ('Admin', 'User', ?, ?, 'admin', 1, NOW())
    ON DUPLICATE KEY UPDATE email = email
\", [\$email, \$password]);

echo 'Admin user created successfully';
"
```

### 17. Configure Automated Backups

Create backup script:
```bash
sudo nano /usr/local/bin/nautilus-backup.sh
```

Add:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/nautilus"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u nautilus -p'YOUR_DB_PASSWORD' nautilus | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/nautilus/public/uploads /var/www/nautilus/storage

# Keep only last 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable and add to cron:
```bash
sudo chmod +x /usr/local/bin/nautilus-backup.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

Add line:
```
0 2 * * * /usr/local/bin/nautilus-backup.sh >> /var/log/nautilus-backup.log 2>&1
```

### 18. Optimize PHP-FPM

Edit PHP-FPM configuration:
```bash
sudo nano /etc/php-fpm.d/www.conf
```

Optimize settings based on your server resources:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

Edit PHP configuration:
```bash
sudo nano /etc/php.ini
```

Recommended production settings:
```ini
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 64M
upload_max_filesize = 64M
display_errors = Off
log_errors = On
error_log = /var/log/php-errors.log
```

Restart PHP-FPM:
```bash
sudo systemctl restart php-fpm
```

## Fedora-Specific Notes

### System Differences from Ubuntu/Debian

1. **Package Manager**: Use `dnf` instead of `apt`
2. **Service Manager**: `systemctl` (same as Ubuntu)
3. **Web Server User**: `apache` or `nginx` (not `www-data`)
4. **SELinux**: Enabled by default (requires additional configuration)
5. **Firewall**: `firewalld` instead of `ufw`
6. **PHP-FPM Socket**: `/run/php-fpm/www.sock`

### SELinux Troubleshooting

If you encounter permission issues:

```bash
# Check SELinux denials
sudo ausearch -m avc -ts recent

# Temporarily disable SELinux (for testing only!)
sudo setenforce 0

# Re-enable SELinux
sudo setenforce 1

# Generate SELinux policy from denials
sudo ausearch -m avc -ts recent | audit2allow -M nautilus_policy
sudo semodule -i nautilus_policy.pp
```

### Log Locations

- Apache: `/var/log/httpd/`
- Nginx: `/var/log/nginx/`
- PHP-FPM: `/var/log/php-fpm/`
- MariaDB: `/var/log/mariadb/`
- SELinux: `/var/log/audit/audit.log`

## Performance Tuning

### Enable OPcache

```bash
sudo nano /etc/php.d/10-opcache.ini
```

Add/modify:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### MariaDB Optimization

```bash
sudo nano /etc/my.cnf.d/server.cnf
```

Add under `[mysqld]`:
```ini
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_size = 0
query_cache_type = 0
```

Restart MariaDB:
```bash
sudo systemctl restart mariadb
```

## Maintenance Tasks

### Update Application

```bash
cd /var/www/nautilus
sudo -u apache git pull
sudo -u apache composer install --no-dev --optimize-autoloader
sudo -u apache php scripts/migrate.php
sudo systemctl restart httpd  # or nginx
```

### Monitor Logs

```bash
# Real-time Apache error log
sudo tail -f /var/log/httpd/nautilus-error.log

# Real-time application log
sudo tail -f /var/www/nautilus/logs/app.log

# PHP errors
sudo tail -f /var/log/php-errors.log
```

### Check System Status

```bash
# Service status
sudo systemctl status httpd mariadb php-fpm

# Disk usage
df -h

# Memory usage
free -h

# Database status
sudo mysqladmin -u root -p status

# Active connections
sudo netstat -tulpn | grep :80
```

## Security Checklist

- [ ] SSL certificate installed and auto-renewal configured
- [ ] Firewall configured (only ports 22, 80, 443 open)
- [ ] SELinux enabled and configured
- [ ] Strong database passwords
- [ ] Admin user password changed from default
- [ ] File permissions set correctly (775 for storage, 755 for public)
- [ ] PHP display_errors disabled
- [ ] Automated backups configured
- [ ] Log rotation configured
- [ ] Keep system updated: `sudo dnf update`

## Troubleshooting

### 403 Forbidden Errors

Check SELinux:
```bash
sudo tail -f /var/log/audit/audit.log | grep denied
```

### 502 Bad Gateway (Nginx)

Check PHP-FPM status:
```bash
sudo systemctl status php-fpm
sudo tail -f /var/log/php-fpm/www-error.log
```

### Database Connection Errors

Verify database credentials and SELinux:
```bash
mysql -u nautilus -p
sudo getsebool httpd_can_network_connect_db
```

### File Upload Issues

Check permissions and PHP settings:
```bash
ls -la /var/www/nautilus/public/uploads/
php -i | grep upload_max_filesize
```

## Support

For Fedora-specific issues:
- Fedora Documentation: https://docs.fedoraproject.org/
- SELinux Guide: https://fedoraproject.org/wiki/SELinux

For Nautilus application issues:
- Check application logs in `/var/www/nautilus/logs/`
- Review documentation in `/var/www/nautilus/docs/`
