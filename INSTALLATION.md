# Nautilus Dive Shop - Installation Guide

This guide provides instructions for installing the Nautilus dive shop management system on a production server.

## Installation Methods

### Method 1: Automated Installation Script (Recommended)

The easiest way to install Nautilus is using the automated installation script.

#### Prerequisites
- Ubuntu 20.04 or higher (or similar Linux distribution)
- Sudo privileges
- Internet connection

#### Installation Steps

1. **Download or clone the repository:**
   ```bash
   cd /var/www
   git clone https://github.com/your-repo/nautilus.git
   cd nautilus
   ```

2. **Run the installation script:**
   ```bash
   chmod +x install.sh
   ./install.sh
   ```

3. **Follow the prompts:**
   - Database name
   - MySQL username and password
   - Domain name
   - Admin user credentials

4. **Access the application:**
   - Navigate to `http://your-domain` in your web browser
   - Log in with the admin credentials you created

### Method 2: Manual Installation

If you prefer manual installation or the automated script doesn't work for your setup:

#### 1. Install Prerequisites

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-gd php8.2-zip -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Apache or Nginx
sudo apt install apache2 -y
# OR
sudo apt install nginx -y
```

#### 2. Configure Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. Install Application

```bash
# Navigate to web directory
cd /var/www/html

# Clone or copy application files
git clone https://github.com/your-repo/nautilus.git
cd nautilus

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set up environment
cp .env.example .env
nano .env  # Edit database credentials
```

#### 4. Run Migrations

```bash
php scripts/migrate.php
```

#### 5. Set Permissions

```bash
sudo chown -R www-data:www-data storage/ public/uploads/ logs/
chmod -R 775 storage/ public/uploads/ logs/
chmod -R 755 public/
```

#### 6. Configure Web Server

**Apache:**

Create `/etc/apache2/sites-available/nautilus.conf`:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite nautilus.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

**Nginx:**

Create `/etc/nginx/sites-available/nautilus`:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/nautilus/public;
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

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/nautilus /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 7. Create Admin User

Use the provided script or manually:
```bash
php scripts/create-admin.php
```

## Post-Installation

### 1. Enable HTTPS (Recommended)

```bash
sudo apt install certbot python3-certbot-apache -y
# OR for Nginx
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --apache  # or --nginx
```

### 2. Configure Application Settings

1. Log in as admin
2. Navigate to Settings
3. Configure:
   - Company information
   - Logo and branding
   - Email settings
   - Tax rates
   - Payment methods

### 3. Import Data from DiveShop360

See [DIVESHOP360_FIELD_MAPPING.md](docs/DIVESHOP360_FIELD_MAPPING.md) for detailed migration instructions.

### 4. Run Migration 035

To add the new product fields:
```bash
php scripts/migrate.php
```

This adds:
- QR code field
- Barcode field
- Weight and dimensions
- Color, material, manufacturer
- Warranty information
- Store location
- Supplier information
- Expiration date

## Troubleshooting

### Database Connection Errors

- Check `.env` file has correct database credentials
- Verify MySQL service is running: `sudo systemctl status mysql`
- Check firewall rules

### Permission Errors

```bash
sudo chown -R www-data:www-data storage/ public/uploads/ logs/
chmod -R 775 storage/ public/uploads/ logs/
```

### Apache/Nginx Not Working

- Check error logs:
  - Apache: `/var/log/apache2/error.log`
  - Nginx: `/var/log/nginx/error.log`
- Verify PHP-FPM is running: `sudo systemctl status php8.2-fpm`

### Can't Access Admin Panel

- Clear browser cache
- Check user role in database
- Reset admin password:
  ```bash
  php scripts/reset-password.php
  ```

## System Requirements

### Minimum Requirements
- PHP 8.2 or higher
- MySQL 8.0 or higher
- 1GB RAM
- 5GB disk space
- Apache 2.4+ or Nginx 1.18+

### Recommended Requirements
- PHP 8.2 or higher
- MySQL 8.0 or higher
- 4GB RAM
- 20GB SSD storage
- Apache 2.4+ or Nginx 1.18+
- SSL certificate

## Support

For assistance:
- Check documentation in `/docs` directory
- Review error logs
- Contact development team

## Security Checklist

- [ ] Change default admin password
- [ ] Enable HTTPS
- [ ] Set strong database password
- [ ] Configure firewall
- [ ] Set up regular backups
- [ ] Keep system updated
- [ ] Restrict file permissions
- [ ] Enable two-factor authentication (if available)

## Backup

Set up automated backups:
```bash
# Database backup
mysqldump -u nautilus_user -p nautilus > backup_$(date +%Y%m%d).sql

# File backup
tar -czf nautilus_files_$(date +%Y%m%d).tar.gz /var/www/html/nautilus
```

Consider setting up a cron job for daily backups.
