# Nautilus v6.0 - Quick Installation Guide

Simple step-by-step guide to install Nautilus v6.0 in production.

## Prerequisites

- Ubuntu 20.04+ or similar Linux server
- PHP 8.2+ with extensions: `mbstring, xml, pdo_mysql, curl, gd, zip`
- MySQL 8.0+
- Apache or Nginx web server
- Composer (PHP package manager)

## Step 1: Install System Requirements

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-curl php8.2-gd php8.2-zip php8.2-intl

# Install MySQL
sudo apt install -y mysql-server

# Install Apache (or use Nginx)
sudo apt install -y apache2 libapache2-mod-php8.2

# Install Composer
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
```

## Step 2: Clone and Setup Application

```bash
# Navigate to web root
cd /var/www

# Clone repository
sudo git clone https://github.com/wrnash1/nautilus-v6.git
cd nautilus-v6

# Checkout the correct branch
sudo git checkout devin/1760111706-nautilus-v6-complete-skeleton

# Install PHP dependencies
sudo composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data /var/www/nautilus-v6
sudo chmod -R 755 /var/www/nautilus-v6
sudo chmod -R 775 /var/www/nautilus-v6/storage
sudo chmod -R 775 /var/www/nautilus-v6/public/uploads
```

## Step 3: Configure Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Run these SQL commands:
CREATE DATABASE nautilus_v6;
CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON nautilus_v6.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 4: Configure Application

```bash
# Copy environment file
sudo cp .env.example .env

# Edit environment file
sudo nano .env
```

Update these critical settings in `.env`:

```env
APP_NAME="Nautilus Dive Shop"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_DATABASE=nautilus_v6
DB_USERNAME=nautilus_user
DB_PASSWORD=your_secure_password

# Generate a random 32-character string for APP_KEY
APP_KEY=your_random_32_character_key_here
```

## Step 5: Run Database Migrations

```bash
# Run migration script
sudo php scripts/migrate.php

# Load initial seed data (roles, permissions, demo users)
sudo mysql -u nautilus_user -p nautilus_v6 < database/seeds/001_seed_initial_data.sql
```

## Step 6: Configure Apache

```bash
# Create Apache virtual host
sudo nano /etc/apache2/sites-available/nautilus.conf
```

Add this configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/nautilus-v6/public

    <Directory /var/www/nautilus-v6/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
```

Enable site and restart Apache:

```bash
sudo a2enmod rewrite
sudo a2ensite nautilus.conf
sudo systemctl restart apache2
```

## Step 7: Setup SSL (Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache

# Get SSL certificate
sudo certbot --apache -d yourdomain.com
```

## Step 8: Setup Automated Backups (Optional)

```bash
# Edit crontab
sudo crontab -e

# Add these lines for automated maintenance:
# Daily backup at 2 AM
0 2 * * * cd /var/www/nautilus-v6 && php scripts/backup.php >> /var/log/nautilus-backup.log 2>&1

# Hourly session cleanup
0 * * * * cd /var/www/nautilus-v6 && php scripts/cleanup-sessions.php >> /var/log/nautilus-sessions.log 2>&1

# Weekly log rotation
0 0 * * 0 cd /var/www/nautilus-v6 && php scripts/rotate-logs.php >> /var/log/nautilus-logs.log 2>&1
```

## Step 9: Test Installation

Visit `https://yourdomain.com` in your browser.

**Default Admin Login:**
- Email: `admin@nautilus.com`
- Password: `admin123`

**⚠️ IMPORTANT:** Change the default password immediately after first login!

## Quick Security Checklist

- [ ] Changed default admin password
- [ ] Set `APP_DEBUG=false` in production
- [ ] Generated secure `APP_KEY`
- [ ] Secured MySQL with strong password
- [ ] Enabled SSL/HTTPS
- [ ] Set proper file permissions (755 for files, 775 for storage)
- [ ] Setup automated backups
- [ ] Disabled directory listing in Apache
- [ ] Setup firewall (UFW)

## Troubleshooting

**Problem:** White screen or 500 error
- **Solution:** Check Apache error logs: `sudo tail -f /var/log/apache2/nautilus-error.log`
- Verify file permissions: `sudo chown -R www-data:www-data /var/www/nautilus-v6`

**Problem:** Database connection failed
- **Solution:** Verify `.env` database credentials
- Test MySQL connection: `mysql -u nautilus_user -p nautilus_v6`

**Problem:** Cannot login
- **Solution:** Verify seed data was loaded: `SELECT * FROM users;` in MySQL
- Reset admin password if needed

## Getting Updates

```bash
cd /var/www/nautilus-v6
sudo git pull origin devin/1760111706-nautilus-v6-complete-skeleton
sudo composer install --no-dev --optimize-autoloader
sudo php scripts/migrate.php  # Run new migrations
sudo systemctl restart apache2
```

## Support

- **Repository:** https://github.com/wrnash1/nautilus-v6
- **Devin Session:** https://app.devin.ai/sessions/0a53533785e14a6f95aae83c5390ae8a
- **Created by:** Bill Nash (@wrnash1)

## Features Included

✅ Point of Sale (POS) with mock Stripe integration  
✅ Customer Management (CRM) with 360° view  
✅ Inventory Management with stock tracking  
✅ Rental Equipment with checkout/checkin workflow  
✅ Course Management with attendance tracking  
✅ Trip Bookings with capacity enforcement  
✅ Work Orders with staff assignment  
✅ E-commerce Shop with customer portal  
✅ Role-based access control  
✅ Comprehensive reporting and analytics  
✅ Automated backups and maintenance  
✅ CI/CD pipeline with GitHub Actions  

---

**Installation complete!** Your Nautilus dive shop management system is ready to use.
