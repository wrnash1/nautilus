# Nautilus Enterprise Deployment Guide

**Version**: 2.0
**Last Updated**: 2025-10-26
**Application Architecture**: Two Independent Applications (Customer + Staff)

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Prerequisites](#prerequisites)
3. [Application Structure](#application-structure)
4. [Installation Steps](#installation-steps)
5. [Configuration](#configuration)
6. [Deployment](#deployment)
7. [Security](#security)
8. [Maintenance](#maintenance)
9. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

Nautilus is an enterprise-grade scuba diving shop management system split into two independent applications:

```
┌─────────────────────────────────────────────────────────────┐
│                    SHARED DATABASE LAYER                     │
│              MySQL 8.0+ (50+ tables, ACID compliant)        │
└─────────────────────────────────────────────────────────────┘
         ▲                                          ▲
         │                                          │
┌────────┴─────────────┐                ┌──────────┴────────────┐
│   CUSTOMER APP       │                │   STAFF APP           │
│   (nautilus-customer)│                │   (nautilus-staff)    │
├──────────────────────┤                ├───────────────────────┤
│ Public Storefront    │                │ Internal Management   │
│ E-commerce           │                │ POS, CRM, Inventory   │
│ Customer Portal      │                │ Reports, Admin        │
│                      │                │                       │
│ Routes: /*, /shop/*  │                │ Routes: /store/*      │
│ Auth: Optional       │                │ Auth: REQUIRED+RBAC   │
└──────────────────────┘                └───────────────────────┘
```

### Design Principles

- **Separation of Concerns**: Customer-facing and staff operations are completely separate
- **Shared Database**: Single source of truth for all business data
- **Role-Based Access**: Granular permissions for staff users
- **Security First**: Different security models for public vs internal access
- **Scalability**: Each application can scale independently

---

## Prerequisites

### Server Requirements

- **OS**: Linux (Ubuntu 20.04+ or Fedora 35+)
- **Web Server**: Apache 2.4+ with mod_rewrite
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Memory**: Minimum 2GB RAM (4GB+ recommended)
- **Storage**: Minimum 10GB free space

### PHP Extensions Required

```bash
# Check installed extensions
php -m

# Required extensions:
- mysqli
- pdo
- pdo_mysql
- json
- curl
- mbstring
- openssl
- gd
- xml
- zip
```

### Install Missing Extensions (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
  php8.2-curl php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip \
  php8.2-bcmath php8.2-intl
```

### Install Missing Extensions (Fedora/RHEL)

```bash
sudo dnf install -y php php-cli php-fpm php-mysqlnd php-curl \
  php-gd php-mbstring php-xml php-zip php-bcmath php-intl
```

---

## Application Structure

### Directory Layout

```
/var/www/html/
│
├── nautilus-customer/              ← Customer-Facing Application
│   ├── app/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── Shop/
│   │   │   └── Customer/
│   │   ├── Core/                   (Database, Router, Auth)
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Middleware/
│   │   └── Views/
│   │       ├── layouts/
│   │       ├── shop/
│   │       └── customer/
│   ├── public/                     ← Web Root
│   │   ├── index.php
│   │   ├── .htaccess
│   │   └── assets/
│   ├── routes/web.php
│   ├── storage/
│   ├── .env
│   └── composer.json
│
└── nautilus-staff/                 ← Staff Management Application
    ├── app/
    │   ├── Controllers/
    │   │   ├── Admin/
    │   │   ├── POS/
    │   │   ├── CRM/
    │   │   ├── Inventory/
    │   │   ├── Rentals/
    │   │   ├── Courses/
    │   │   └── Reports/
    │   ├── Core/                   (Same as Customer app)
    │   ├── Models/
    │   ├── Services/
    │   ├── Middleware/
    │   └── Views/
    │       ├── layouts/
    │       ├── dashboard/
    │       ├── pos/
    │       └── reports/
    ├── public/                     ← Web Root
    │   ├── index.php
    │   ├── .htaccess
    │   └── assets/
    ├── routes/web.php
    ├── storage/
    ├── .env
    └── composer.json
```

---

## Installation Steps

### Step 1: Clone and Prepare Applications

```bash
# Navigate to web directory
cd /var/www/html

# Clone the repository
git clone https://github.com/yourusername/nautilus.git nautilus-source
cd nautilus-source

# Run the application split script
chmod +x scripts/split-enterprise-apps.sh
./scripts/split-enterprise-apps.sh

# This creates:
# - /var/www/html/nautilus-customer/
# - /var/www/html/nautilus-staff/
```

### Step 2: Install Dependencies

```bash
# Install Composer dependencies for Customer app
cd /var/www/html/nautilus-customer
composer install --no-dev --optimize-autoloader

# Install Composer dependencies for Staff app
cd /var/www/html/nautilus-staff
composer install --no-dev --optimize-autoloader
```

### Step 3: Set File Permissions

```bash
# Customer app
sudo chown -R www-data:www-data /var/www/html/nautilus-customer
sudo chmod -R 755 /var/www/html/nautilus-customer
sudo chmod -R 775 /var/www/html/nautilus-customer/storage
sudo chmod -R 775 /var/www/html/nautilus-customer/public/uploads

# Staff app
sudo chown -R www-data:www-data /var/www/html/nautilus-staff
sudo chmod -R 755 /var/www/html/nautilus-staff
sudo chmod -R 775 /var/www/html/nautilus-staff/storage
sudo chmod -R 775 /var/www/html/nautilus-staff/public/uploads
```

### Step 4: Create Database

```bash
# Access MySQL
mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create database user (recommended for production)
CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
EXIT;
```

### Step 5: Run Database Migrations

```bash
# Run migrations (only need to run once - from either app)
cd /var/www/html/nautilus-customer
php scripts/migrate.php

# Verify tables were created
mysql -u root -p nautilus -e "SHOW TABLES;"
```

This creates 50+ tables including:
- Authentication & Authorization (users, roles, permissions)
- Customer Management
- Products & Inventory
- POS Transactions
- Rentals, Courses, Trips
- E-commerce Orders
- Marketing & CMS
- Staff Management
- Reporting & Analytics

### Step 6: Create Initial Admin User

```bash
mysql -u root -p nautilus
```

```sql
-- Insert Administrator role
INSERT INTO roles (id, name, description, created_at, updated_at)
VALUES (1, 'Administrator', 'Full system access', NOW(), NOW());

-- Insert admin user (password: 'admin123')
INSERT INTO users (username, email, password_hash, first_name, last_name,
                  role_id, is_active, created_at, updated_at)
VALUES ('admin', 'admin@yourdomain.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'System', 'Administrator', 1, 1, NOW(), NOW());

EXIT;
```

**IMPORTANT**: Change this password immediately after first login!

---

## Configuration

### Customer Application (.env)

```bash
sudo nano /var/www/html/nautilus-customer/.env
```

```env
# Application
APP_NAME="Your Dive Shop Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=base64:YOUR_32_BYTE_KEY_HERE
APP_BASE_PATH=

# Database (shared with staff app)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=your_secure_password_here

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file

# Email
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Payment Gateways
STRIPE_PUBLIC_KEY=pk_live_...
STRIPE_SECRET_KEY=sk_live_...

# File Upload
MAX_UPLOAD_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx
```

### Staff Application (.env)

```bash
sudo nano /var/www/html/nautilus-staff/.env
```

```env
# Application
APP_NAME="Your Dive Shop - Staff Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/store
APP_KEY=base64:YOUR_32_BYTE_KEY_HERE
APP_BASE_PATH=/store

# Database (shared with customer app)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=your_secure_password_here

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=480

# Cache
CACHE_DRIVER=file

# Email (same as customer app)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Integrations
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/store/auth/google/callback

QUICKBOOKS_CLIENT_ID=your-qb-client-id
QUICKBOOKS_CLIENT_SECRET=your-qb-client-secret

# SMS
TWILIO_ACCOUNT_SID=your-twilio-sid
TWILIO_AUTH_TOKEN=your-twilio-token
TWILIO_PHONE_NUMBER=+1234567890
```

### Generate Application Keys

```bash
# Generate secure random keys
php -r "echo 'base64:' . base64_encode(random_bytes(32)) . PHP_EOL;"

# Copy the output and paste into APP_KEY in both .env files
```

---

## Deployment

### Apache Configuration (Option 1: Single Domain with /store path)

```bash
sudo nano /etc/apache2/sites-available/nautilus.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    # Redirect to HTTPS
    Redirect permanent / https://yourdomain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    # Customer-Facing Application (Default)
    DocumentRoot /var/www/html/nautilus-customer/public

    <Directory /var/www/html/nautilus-customer/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Staff Application (Accessible at /store)
    Alias /store /var/www/html/nautilus-staff/public

    <Directory /var/www/html/nautilus-staff/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Optional: IP whitelist for added security
        # Require ip 192.168.1.0/24
    </Directory>

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/nautilus_error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus_access.log combined
</VirtualHost>
```

### Apache Configuration (Option 2: Separate Subdomains)

```apache
# Customer Application
<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    DocumentRoot /var/www/html/nautilus-customer/public

    <Directory /var/www/html/nautilus-customer/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus_customer_error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus_customer_access.log combined
</VirtualHost>

# Staff Application
<VirtualHost *:443>
    ServerName staff.yourdomain.com

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    DocumentRoot /var/www/html/nautilus-staff/public

    <Directory /var/www/html/nautilus-staff/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Optional: IP whitelist
        # Require ip 192.168.1.0/24
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus_staff_error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus_staff_access.log combined
</VirtualHost>
```

### Enable Site and Restart Apache

```bash
# Enable required modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers

# Enable site
sudo a2ensite nautilus.conf

# Test configuration
sudo apache2ctl configtest

# Restart Apache
sudo systemctl restart apache2
```

### SSL Certificate Setup (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is configured automatically
# Test renewal:
sudo certbot renew --dry-run
```

---

## Security

### Production Security Checklist

- [ ] Change default admin password
- [ ] Set APP_DEBUG=false in production
- [ ] Use strong APP_KEY (32+ random bytes)
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall (ufw/firewalld)
- [ ] Restrict database user privileges
- [ ] Set proper file permissions (755 directories, 644 files)
- [ ] Enable security headers
- [ ] Configure session timeout appropriately
- [ ] Regular security updates (OS, PHP, packages)
- [ ] Database backups configured
- [ ] Monitor error logs
- [ ] Implement IP whitelisting for staff app (optional)
- [ ] Enable fail2ban for brute force protection

### Firewall Configuration (UFW)

```bash
# Enable firewall
sudo ufw enable

# Allow SSH (IMPORTANT: Do this first!)
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Check status
sudo ufw status
```

### Database Security

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Answers:
# - Set root password: YES
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES
```

---

## Maintenance

### Automated Backups

```bash
# Create backup script
sudo nano /usr/local/bin/nautilus-backup.sh
```

```bash
#!/bin/bash

# Configuration
DB_NAME="nautilus"
DB_USER="nautilus_user"
DB_PASS="your_secure_password_here"
BACKUP_DIR="/var/backups/nautilus"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_customer_$DATE.tar.gz /var/www/html/nautilus-customer/storage/
tar -czf $BACKUP_DIR/files_staff_$DATE.tar.gz /var/www/html/nautilus-staff/storage/

# Delete backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable
sudo chmod +x /usr/local/bin/nautilus-backup.sh

# Schedule daily backups (2 AM)
sudo crontab -e
# Add line:
0 2 * * * /usr/local/bin/nautilus-backup.sh >> /var/log/nautilus-backup.log 2>&1
```

### Log Rotation

```bash
sudo nano /etc/logrotate.d/nautilus
```

```
/var/www/html/nautilus-customer/storage/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    notifempty
    missingok
    create 0644 www-data www-data
}

/var/www/html/nautilus-staff/storage/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    missingok
    create 0644 www-data www-data
}
```

### Monitoring

```bash
# View application logs in real-time
tail -f /var/www/html/nautilus-customer/storage/logs/app.log
tail -f /var/www/html/nautilus-staff/storage/logs/app.log

# View Apache logs
tail -f /var/log/apache2/nautilus_error.log
tail -f /var/log/apache2/nautilus_access.log

# Check disk usage
df -h

# Check memory usage
free -m

# Check running processes
ps aux | grep php
ps aux | grep apache2
```

---

## Troubleshooting

### Issue: "Route not found" or 404 errors

**Cause**: mod_rewrite not enabled or .htaccess not working

**Solution**:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verify AllowOverride is set to "All" in Apache config
```

### Issue: Database connection failed

**Cause**: Incorrect credentials or database doesn't exist

**Solution**:
```bash
# Test database connection
mysql -u nautilus_user -p nautilus

# Verify .env settings match database credentials
# Check both customer and staff .env files
```

### Issue: 500 Internal Server Error

**Cause**: PHP errors, permissions, or configuration issues

**Solution**:
```bash
# Check Apache error log
sudo tail -50 /var/log/apache2/error.log

# Check application log
sudo tail -50 /var/www/html/nautilus-customer/storage/logs/app.log

# Enable debug mode temporarily
# Edit .env: APP_DEBUG=true
# Remember to disable after fixing!
```

### Issue: Session not persisting / Can't stay logged in

**Cause**: Storage directory not writable

**Solution**:
```bash
sudo chown -R www-data:www-data /var/www/html/nautilus-staff/storage
sudo chmod -R 775 /var/www/html/nautilus-staff/storage
```

### Issue: File upload not working

**Cause**: Upload directory permissions or PHP upload limits

**Solution**:
```bash
# Fix permissions
sudo chmod -R 775 /var/www/html/nautilus-customer/public/uploads
sudo chown -R www-data:www-data /var/www/html/nautilus-customer/public/uploads

# Check PHP limits
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Edit php.ini if needed
sudo nano /etc/php/8.2/apache2/php.ini
# Set:
# upload_max_filesize = 20M
# post_max_size = 20M

sudo systemctl restart apache2
```

### Issue: Email not sending

**Cause**: SMTP configuration incorrect

**Solution**:
```bash
# Test SMTP connection
telnet smtp.gmail.com 587

# For Gmail, use App Password, not regular password
# Generate at: https://myaccount.google.com/apppasswords

# Verify .env settings:
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

### Issue: Slow performance

**Cause**: No caching, database not optimized

**Solution**:
```bash
# Enable PHP OPcache
sudo nano /etc/php/8.2/apache2/php.ini
# Set:
# opcache.enable=1
# opcache.memory_consumption=128
# opcache.max_accelerated_files=10000

# Restart Apache
sudo systemctl restart apache2

# Optimize database tables
mysql -u root -p nautilus -e "OPTIMIZE TABLE products, customers, orders;"

# Consider Redis/Memcached for caching in high-traffic scenarios
```

---

## URLs and Access

### Customer Application

| URL | Purpose | Authentication |
|-----|---------|----------------|
| `/` | Homepage | None |
| `/shop` | Product catalog | None |
| `/shop/product/{id}` | Product details | None |
| `/shop/cart` | Shopping cart | None |
| `/shop/checkout` | Checkout | Customer (optional guest) |
| `/account/register` | Customer registration | None |
| `/account/login` | Customer login | Customer |
| `/account` | Customer dashboard | Customer |
| `/account/orders` | Order history | Customer |
| `/contact` | Contact form | None |

### Staff Application

| URL | Purpose | Role Required |
|-----|---------|---------------|
| `/store/login` | Staff login | None |
| `/store` | Dashboard | Any Staff |
| `/store/pos` | Point of Sale | Sales, Manager |
| `/store/customers` | CRM | Sales, Manager |
| `/store/products` | Inventory | Manager, Inventory |
| `/store/rentals` | Equipment rentals | Sales, Manager |
| `/store/courses` | Training courses | Instructor, Manager |
| `/store/trips` | Dive trips | Manager |
| `/store/reports/*` | Reports & analytics | Manager |
| `/store/admin/settings` | Settings | Manager |
| `/store/admin/users` | User management | Administrator |
| `/store/storefront` | Storefront config | Manager |

---

## Additional Resources

- **API Documentation**: `/docs/API_DOCUMENTATION.md`
- **Developer Guide**: `/docs/DEVELOPER_GUIDE.md`
- **Testing Guide**: `/docs/TESTING_GUIDE.md`

---

## Support

For issues, questions, or feature requests:
- Email: support@yourdomain.com
- Documentation: https://docs.yourdomain.com
- GitHub: https://github.com/yourusername/nautilus

---

**Deployment Guide Version**: 2.0
**Last Updated**: 2025-10-26
**Compatible with**: Nautilus v2.0+
