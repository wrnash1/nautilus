# Nautilus Dive Shop - Enterprise Setup Guide

Complete installation and deployment guide for running Nautilus as a production enterprise application for scuba diving businesses.

---

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Quick Installation](#quick-installation)
3. [Manual Installation](#manual-installation)
4. [Multi-Server Deployment](#multi-server-deployment)
5. [Configuration](#configuration)
6. [User Management](#user-management)
7. [Troubleshooting](#troubleshooting)
8. [Maintenance & Backups](#maintenance--backups)
9. [Security Best Practices](#security-best-practices)

---

## System Requirements

### Server Requirements

- **Operating System**: Ubuntu 22.04+, Pop!_OS 22.04+, Fedora 38+, Debian 12+, RHEL/Rocky 9+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.1 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+ (MariaDB 10.11+ recommended)
- **Memory**: Minimum 2GB RAM (4GB+ recommended for production)
- **Storage**: 10GB+ available disk space

### Required PHP Extensions

```
- pdo
- pdo_mysql
- mbstring
- json
- openssl
- curl
- fileinfo
- gd
- zip
- bcmath
- intl
```

### Network Requirements

- **HTTPS**: SSL certificate (Let's Encrypt recommended)
- **Ports**: 80 (HTTP), 443 (HTTPS), 3306 (MySQL - internal only)
- **Firewall**: Configured to allow HTTP/HTTPS traffic

---

## Quick Installation

### Step 1: Upload Files

Upload all Nautilus files to your web server:

```bash
# Clone from repository
cd /var/www/html
sudo git clone https://github.com/yourusername/nautilus.git
cd nautilus

# Or sync from development machine
rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/
```

### Step 2: Run Setup Script

```bash
cd /var/www/html/nautilus
sudo bash setup.sh
```

The setup script will:
- Create required directories
- Set correct file permissions
- Check PHP version and extensions
- Test database connection
- Create `.env` file from template

### Step 3: Configure Database

Edit the `.env` file with your database credentials:

```bash
sudo nano .env
```

Update these lines:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=your_secure_password

APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### Step 4: Create Database

```bash
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_secure_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';"
mysql -u root -p -e "FLUSH PRIVILEGES;"
```

### Step 5: Install via Web Interface

Visit the installation page:

```
https://your-domain.com/install
```

**Important**: Use `/install` route (not old install.php files). The controller-based installer handles:
- 70+ database migrations
- Default permissions setup
- Admin user creation
- System configuration

Fill in the installation form:
- **Business Name**: Your dive shop name
- **Admin Email**: Your email address
- **Admin Password**: Strong password (8+ characters)
- **Role**: Administrator

Click "Install Nautilus" and wait for completion (this may take 1-2 minutes).

### Step 6: Verify Installation

After successful installation:

1. **Login**: Visit `https://your-domain.com/store/login`
2. **Check Dashboard**: Verify all sidebar menu items are visible
3. **Test Logout**: Click logout button (should redirect properly)
4. **Check Permissions**: Navigate to Admin → Roles & Permissions

---

## Manual Installation

### Step 1: Install System Packages

**Ubuntu / Pop!_OS / Debian:**

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php8.1 php8.1-cli php8.1-common \
    php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-gd \
    php8.1-zip php8.1-bcmath php8.1-json php8.1-intl composer

sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

**Fedora / RHEL / Rocky:**

```bash
sudo dnf install -y httpd mariadb-server php php-cli php-common \
    php-mysqlnd php-mbstring php-xml php-curl php-gd php-zip \
    php-bcmath php-json php-intl composer

sudo systemctl enable httpd mariadb
sudo systemctl start httpd mariadb

# Configure firewall
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### Step 2: Configure Web Server

**Apache Virtual Host:**

Create `/etc/apache2/sites-available/nautilus.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/your-domain.crt
    SSLCertificateKeyFile /etc/ssl/private/your-domain.key
    SSLCertificateChainFile /etc/ssl/certs/ca-bundle.crt

    <Directory /var/www/html/nautilus/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Security headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    ErrorLog ${APACHE_LOG_DIR}/nautilus-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-ssl-access.log combined
</VirtualHost>
```

Enable site:

```bash
sudo a2ensite nautilus
sudo systemctl reload apache2
```

**Nginx Configuration:**

Create `/etc/nginx/sites-available/nautilus`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/html/nautilus/public;
    index index.php index.html;

    ssl_certificate /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/nautilus /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 3: Set File Permissions

**Ubuntu/Debian/Pop!_OS:**

```bash
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chmod 640 /var/www/html/nautilus/.env
```

**Fedora/RHEL/Rocky:**

```bash
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chmod 640 /var/www/html/nautilus/.env

# SELinux contexts
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/nautilus/storage
sudo chcon -t httpd_sys_rw_content_t /var/www/html/nautilus/.env
```

### Step 4: Install Dependencies

```bash
cd /var/www/html/nautilus
composer install --no-dev --optimize-autoloader
```

### Step 5: Complete Installation

Visit `https://your-domain.com/install` and complete the web-based installation.

---

## Multi-Server Deployment

### Architecture Overview

For high-availability enterprise deployments:

```
[Load Balancer]
      |
      ├── [Web Server 1] ──┐
      ├── [Web Server 2] ──┼── [Database Server]
      └── [Web Server 3] ──┘
```

### Database Server Setup

**On dedicated database server:**

```sql
-- Create database
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with remote access
CREATE USER 'nautilus_user'@'192.168.1.%' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'192.168.1.%';
FLUSH PRIVILEGES;
```

**MySQL configuration** (`/etc/mysql/my.cnf`):

```ini
[mysqld]
bind-address = 0.0.0.0  # Allow remote connections
max_connections = 200
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
```

### Web Server Setup

**On each web server**, use the same `.env` configuration:

```env
DB_HOST=192.168.1.100  # Database server IP
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=strong_password
```

**Shared storage for uploads** (use NFS or object storage):

```bash
# Mount shared storage on all web servers
sudo mount -t nfs db-server:/var/www/uploads /var/www/html/nautilus/storage/uploads
```

### Load Balancer Configuration

**Nginx load balancer** (`/etc/nginx/nginx.conf`):

```nginx
upstream nautilus_backend {
    least_conn;
    server 192.168.1.10:80 max_fails=3 fail_timeout=30s;
    server 192.168.1.11:80 max_fails=3 fail_timeout=30s;
    server 192.168.1.12:80 max_fails=3 fail_timeout=30s;
}

server {
    listen 80;
    server_name your-domain.com;

    location / {
        proxy_pass http://nautilus_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Session Management

For multi-server deployments, use database sessions:

**In `.env`:**

```env
SESSION_DRIVER=database
```

**Create sessions table** (already included in migrations):

```sql
-- Table created by migration 001_create_users_and_auth_tables.sql
SELECT * FROM sessions LIMIT 1;
```

---

## Configuration

### Environment Variables

Key settings in `.env`:

```env
# Application
APP_NAME="Your Dive Shop Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=your_password

# Session & Security
SESSION_DRIVER=database
SESSION_LIFETIME=120
SECURE_COOKIES=true

# Email (for notifications)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Payment Gateway (if using)
PAYMENT_GATEWAY=stripe
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret

# Backups
BACKUP_PATH=/var/backups/nautilus
```

### System Settings

After installation, configure via Admin panel:

1. **Settings → General**
   - Business name, address, phone
   - Currency settings
   - Tax rates

2. **Settings → Point of Sale**
   - Receipt printer settings
   - Cash drawer configuration
   - Default payment methods

3. **Settings → Email**
   - SMTP configuration
   - Email templates

4. **Settings → Integrations**
   - Payment gateways
   - Third-party APIs

---

## User Management

### Permission System

Nautilus uses Role-Based Access Control (RBAC):

- **Roles**: Admin, Manager, Staff, Instructor, etc.
- **Permissions**: 31+ default permissions for modules
- **Assignment**: Users assigned one role, roles have many permissions

### Default Permissions

```
dashboard.view, analytics.view
pos.view, pos.access
products.view, products.create, products.edit, products.delete
customers.view, customers.create, customers.edit, customers.delete
courses.view, courses.manage
trips.view, trips.manage
rentals.view, rentals.manage
air_fills.view, air_fills.manage
reports.view
settings.view, settings.manage
users.view, users.create, users.edit, users.delete
roles.view, roles.manage
audit.view
```

### Creating New Users

**Via Admin Panel:**

1. Navigate to Admin → Users
2. Click "Add New User"
3. Fill in details:
   - Name, Email, Password
   - Select Role (Admin, Manager, Staff)
4. Click "Create User"

**Via Database (advanced):**

```sql
-- Create user with hashed password
INSERT INTO users (email, password, name, role_id, created_at)
VALUES (
    'user@example.com',
    '$2y$10$hashed_password_here',
    'John Doe',
    1,  -- 1 = Admin role
    NOW()
);
```

### Creating Custom Roles

1. Navigate to Admin → Roles & Permissions
2. Click "Add New Role"
3. Enter role name and description
4. Select permissions to assign
5. Save role

**Example roles:**

- **Dive Instructor**: courses.*, trips.*, customers.view
- **Sales Staff**: pos.*, products.view, customers.*
- **Accountant**: reports.view, analytics.view

---

## Troubleshooting

### Installation Issues

#### "Can't create table" (errno 150)

**Error**: Foreign key constraint errors during migration

**Solution**: This should be fixed in latest version. If still occurring:

```bash
# Verify all migrations use INT UNSIGNED for FK columns
grep -r "INT[^(]" database/migrations/*.sql | grep -v UNSIGNED

# Check database
mysql -u root -p nautilus -e "SHOW ENGINE INNODB STATUS\G" | grep "LATEST FOREIGN KEY ERROR"
```

#### Empty Dashboard Sidebar

**Error**: Only "Online Store" menu item visible

**Solution**: Permissions not assigned to your role

```sql
-- Check permissions exist
SELECT COUNT(*) FROM permissions;
-- Should return 31+

-- Check role_permissions
SELECT COUNT(*) FROM role_permissions WHERE role_id = 1;
-- Should return 31+

-- If missing, assign all permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;
```

Then logout and login again.

#### Logout Returns "Route not found"

**Error**: `{"error":"Route not found"}` when clicking logout

**Solution**: Fixed in latest version. Logout form now posts to `/store/logout`.

Verify in [app/Views/layouts/app.php](app/Views/layouts/app.php#L226):

```php
<form method="POST" action="/store/logout" class="d-inline">
```

### Performance Issues

#### Slow Database Queries

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;
```

#### High Memory Usage

```bash
# Check PHP memory limit
php -i | grep memory_limit

# Increase in php.ini
memory_limit = 512M
```

#### Session Problems

```bash
# Check session storage
ls -la /var/www/html/nautilus/storage/sessions

# Or check database sessions
mysql -u nautilus_user -p nautilus -e "SELECT COUNT(*) FROM sessions;"
```

### Common Errors

#### HTTP 500 Error

1. **Check error logs:**

```bash
# Apache
sudo tail -f /var/log/apache2/error.log

# Nginx
sudo tail -f /var/log/nginx/error.log
```

2. **Enable debug mode temporarily:**

```env
# In .env (ONLY for troubleshooting)
APP_DEBUG=true
```

3. **Check file permissions:**

```bash
ls -la /var/www/html/nautilus/storage
# Should be writable by www-data or apache
```

#### Database Connection Failed

```bash
# Test connection
mysql -h localhost -u nautilus_user -p nautilus -e "SELECT 1;"

# Check MySQL status
sudo systemctl status mysql  # or mariadb

# Verify credentials in .env
cat /var/www/html/nautilus/.env | grep DB_
```

#### Composer Dependencies Missing

```bash
cd /var/www/html/nautilus
composer install --no-dev
composer dump-autoload
```

---

## Maintenance & Backups

### Database Backups

**Automated daily backup script** (`/usr/local/bin/backup-nautilus.sh`):

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/nautilus"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="nautilus"
DB_USER="nautilus_user"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Dump database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/nautilus_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "nautilus_*.sql.gz" -mtime +30 -delete

echo "Backup completed: nautilus_$DATE.sql.gz"
```

**Schedule with cron:**

```bash
sudo crontab -e

# Add line for daily 2 AM backup
0 2 * * * /usr/local/bin/backup-nautilus.sh >> /var/log/nautilus-backup.log 2>&1
```

### Application Updates

```bash
# Pull latest code
cd /var/www/html/nautilus
sudo -u www-data git pull origin main

# Update dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Run migrations (if any)
# Visit: https://your-domain.com/install/migrate

# Clear cache
sudo rm -rf storage/cache/*
```

### Log Rotation

**Nautilus logs** (`/etc/logrotate.d/nautilus`):

```
/var/www/html/nautilus/storage/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## Security Best Practices

### Post-Installation Security

**1. Remove installer access** (after successful installation):

```bash
# The /install route is protected by middleware after first install
# Verify in storage/installed.lock
ls -la /var/www/html/nautilus/storage/installed.lock
```

**2. Secure .env file:**

```bash
chmod 640 /var/www/html/nautilus/.env
chown www-data:www-data /var/www/html/nautilus/.env
```

**3. Disable debug mode:**

```env
# In .env
APP_DEBUG=false
```

**4. Enable HTTPS:**

```bash
# Install Let's Encrypt
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com
```

**5. Configure firewall:**

```bash
# Ubuntu/Debian
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Fedora/RHEL
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### Application Security

**1. Strong passwords:**
- Minimum 12 characters
- Uppercase, lowercase, numbers, symbols
- Password reset every 90 days (configured in Settings)

**2. Two-factor authentication** (if enabled):
- Navigate to Admin → Security Settings
- Enable 2FA for admin users

**3. Audit logging:**
- All permission changes logged in `permission_audit_log`
- View in Admin → Audit Log

**4. Regular updates:**
- Monitor GitHub releases
- Test updates in staging environment first
- Backup before updating production

**5. Database security:**

```sql
-- Use least privilege for database user
REVOKE ALL PRIVILEGES ON nautilus.* FROM 'nautilus_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
```

### Monitoring

**Install monitoring tools:**

```bash
# Install Fail2ban (prevent brute force)
sudo apt install fail2ban

# Configure Nautilus jail
sudo nano /etc/fail2ban/jail.local
```

**Monitor logs:**

```bash
# Watch application logs
tail -f /var/www/html/nautilus/storage/logs/app.log

# Watch Apache access
tail -f /var/log/apache2/nautilus-access.log

# Watch for errors
grep ERROR /var/www/html/nautilus/storage/logs/*.log
```

---

## Getting Help

### Documentation

- **Installation Guide**: [INSTALL.md](INSTALL.md)
- **Quick Start**: [QUICKSTART.md](QUICKSTART.md)
- **Feature List**: [COMPLETE_FEATURE_LIST.md](COMPLETE_FEATURE_LIST.md)
- **Recent Fixes**: [FIXES-SUMMARY.md](FIXES-SUMMARY.md)
- **Migration Errors**: [MIGRATION-ERRORS-FIXED.md](MIGRATION-ERRORS-FIXED.md)

### Support Resources

- **GitHub Issues**: Report bugs and request features
- **System Status**: Visit `/check-requirements.php` before installing
- **Database Status**: Check migration log in storage/logs/install.log

### Common Support Questions

**Q: Can I migrate from Nautilus v5?**
A: Yes, use the migration tool in Admin → System → Migrate from v5

**Q: Does this support multi-currency?**
A: Yes, configure in Settings → General → Currency

**Q: Can I customize the POS interface?**
A: Yes, templates in `app/Views/pos/` can be modified

**Q: Is there a mobile app?**
A: The web interface is responsive and works on tablets/phones

**Q: How do I add custom fields?**
A: Use the Custom Fields module in Settings → Advanced

---

## License & Copyright

**Nautilus Dive Shop Management System v6**

Copyright © 2025 Nautilus Software. All rights reserved.

For licensing information, contact: support@nautilus-software.com

---

## Quick Reference Commands

### Installation

```bash
# Quick install
cd /var/www/html/nautilus && sudo bash setup.sh

# Create database
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Set permissions (Ubuntu)
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

### Maintenance

```bash
# Backup database
mysqldump -u nautilus_user -p nautilus | gzip > backup_$(date +%Y%m%d).sql.gz

# Update application
cd /var/www/html/nautilus && git pull && composer install --no-dev

# View logs
tail -f storage/logs/app.log
```

### Troubleshooting

```bash
# Check permissions
ls -la storage/

# Test database connection
mysql -h localhost -u nautilus_user -p nautilus -e "SELECT 1;"

# Check Apache errors
sudo tail -f /var/log/apache2/error.log

# Verify PHP extensions
php -m | grep -E "(pdo|mysql|mbstring|curl)"
```

---

**Installation complete!** Your Nautilus enterprise dive shop management system is ready for production use.

For ongoing support and updates, monitor the GitHub repository and join the community forum.
