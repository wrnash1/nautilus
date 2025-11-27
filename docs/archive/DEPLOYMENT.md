# Nautilus v6.0 Deployment Guide

## Prerequisites

- Ubuntu 20.04 LTS or Fedora 35+ server
- PHP 8.2 or higher with required extensions
- MySQL 8.0+ or MariaDB 10.6+
- Apache 2.4+ with mod_rewrite and mod_ssl
- Composer
- SSL certificate (Let's Encrypt recommended)
- Minimum 4GB RAM, 2 CPU cores, 50GB storage

## Production Deployment Steps

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apache2 mysql-server php8.2 php8.2-{cli,fpm,mysql,xml,mbstring,curl,gd,zip,intl,opcache}

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Clone Repository

```bash
cd /var/www
sudo git clone <repository-url> nautilus
cd nautilus
sudo chown -R www-data:www-data /var/www/nautilus
```

### 3. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configure Environment

```bash
cp .env.example .env
nano .env
# Update all production settings
```

### 5. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
for file in database/migrations/*.sql; do
    mysql -u root -p nautilus < "$file"
done
```

### 6. Set Permissions

```bash
sudo chown -R www-data:www-data storage public/uploads
sudo chmod -R 755 storage public/uploads
```

### 7. Configure Apache

```bash
sudo nano /etc/apache2/sites-available/nautilus.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName nautilus.yourdomain.com
    Redirect permanent / https://nautilus.yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName nautilus.yourdomain.com
    DocumentRoot /var/www/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/nautilus.yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/nautilus.yourdomain.com/privkey.pem

    <Directory /var/www/nautilus/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
```

```bash
# Enable site and required modules
sudo a2ensite nautilus
sudo a2enmod rewrite ssl headers
sudo systemctl restart apache2
```

### 8. SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d nautilus.yourdomain.com
```

### 9. Performance Optimization

```bash
# Enable PHP OPcache
sudo nano /etc/php/8.2/apache2/php.ini
```

Add/Update:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### 10. Setup Cron Jobs

```bash
sudo crontab -e -u www-data
```

Add:
```
# Backup database daily at 2 AM
0 2 * * * /usr/bin/php /var/www/nautilus/scripts/backup.php

# Clear expired sessions hourly
0 * * * * /usr/bin/php /var/www/nautilus/scripts/cleanup-sessions.php

# Send scheduled emails
*/5 * * * * /usr/bin/php /var/www/nautilus/scripts/process-emails.php
```

### 11. Security Hardening

```bash
# Disable unnecessary PHP functions
sudo nano /etc/php/8.2/apache2/php.ini
```

Add:
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

### 12. Monitoring Setup

Install monitoring tools for system health, database performance, and application logs.

## Backup Strategy

- Daily automated database backups
- Weekly full system backups
- Backup retention: 30 days local, 90 days offsite
- Test restore procedures monthly

## Maintenance

- Keep system packages updated
- Monitor logs regularly
- Review security patches weekly
- Performance tuning quarterly
