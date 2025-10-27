# Nautilus Quick Start Guide

**Get up and running in 15 minutes**

---

## Overview

Nautilus is split into two enterprise applications:
- **Customer App** (Public storefront)
- **Staff App** (Internal management)

Both share a single MySQL database.

---

## Prerequisites

Ensure you have:
- ✅ Linux server (Ubuntu/Fedora)
- ✅ PHP 8.2+
- ✅ MySQL 8.0+ or MariaDB 10.6+
- ✅ Apache 2.4+ with mod_rewrite
- ✅ Composer

---

## Quick Installation (15 Minutes)

### Step 1: Split the Application (2 minutes)

```bash
cd /home/wrnash1/development/nautilus

# Run the split script
./scripts/split-enterprise-apps.sh
```

This creates:
- `/home/wrnash1/development/nautilus-customer/`
- `/home/wrnash1/development/nautilus-staff/`

### Step 2: Install Dependencies (3 minutes)

```bash
# Customer app
cd /home/wrnash1/development/nautilus-customer
composer install

# Staff app
cd /home/wrnash1/development/nautilus-staff
composer install
```

### Step 3: Configure Environment (2 minutes)

```bash
# Customer app
cd /home/wrnash1/development/nautilus-customer
cp .env.example .env
nano .env
```

**Critical settings:**
```env
APP_NAME="Your Dive Shop"
APP_BASE_PATH=
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
# Staff app
cd /home/wrnash1/development/nautilus-staff
cp .env.example .env
nano .env
```

**Critical settings:**
```env
APP_NAME="Your Dive Shop - Staff"
APP_BASE_PATH=/store
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Create Database (2 minutes)

```bash
mysql -u root -p
```

```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 5: Run Migrations (3 minutes)

```bash
cd /home/wrnash1/development/nautilus-customer
php scripts/migrate.php
```

### Step 6: Create Admin User (2 minutes)

```bash
mysql -u root -p nautilus
```

```sql
INSERT INTO roles (id, name, description, created_at, updated_at)
VALUES (1, 'Administrator', 'Full system access', NOW(), NOW());

INSERT INTO users (username, email, password_hash, first_name, last_name,
                  role_id, is_active, created_at, updated_at)
VALUES ('admin', 'admin@yourdomain.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'System', 'Administrator', 1, 1, NOW(), NOW());

EXIT;
```

**Login:** admin@yourdomain.com / password

### Step 7: Test Locally (1 minute)

```bash
# Customer app
cd /home/wrnash1/development/nautilus-customer/public
php -S localhost:8000

# Staff app (in another terminal)
cd /home/wrnash1/development/nautilus-staff/public
php -S localhost:8001
```

**Access:**
- Customer: http://localhost:8000
- Staff: http://localhost:8001/store/login

---

## Production Deployment (Optional)

### Copy to Web Server

```bash
sudo mkdir -p /var/www/html
sudo cp -r /home/wrnash1/development/nautilus-customer /var/www/html/
sudo cp -r /home/wrnash1/development/nautilus-staff /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/nautilus-customer
sudo chown -R www-data:www-data /var/www/html/nautilus-staff
sudo chmod -R 755 /var/www/html/nautilus-customer
sudo chmod -R 755 /var/www/html/nautilus-staff
sudo chmod -R 775 /var/www/html/nautilus-customer/storage
sudo chmod -R 775 /var/www/html/nautilus-staff/storage
```

### Configure Apache

```bash
sudo nano /etc/apache2/sites-available/nautilus.conf
```

```apache
<VirtualHost *:80>
    ServerName yourdomain.com

    # Customer Application (default)
    DocumentRoot /var/www/html/nautilus-customer/public

    <Directory /var/www/html/nautilus-customer/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Staff Application (at /store)
    Alias /store /var/www/html/nautilus-staff/public

    <Directory /var/www/html/nautilus-staff/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus_error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus_access.log combined
</VirtualHost>
```

```bash
# Enable site
sudo a2enmod rewrite
sudo a2ensite nautilus.conf
sudo systemctl restart apache2
```

---

## URLs

### Customer Application

| URL | Purpose |
|-----|---------|
| `/` | Homepage |
| `/shop` | Product catalog |
| `/account/register` | Customer signup |
| `/account/login` | Customer login |
| `/cart` | Shopping cart |

### Staff Application

| URL | Purpose |
|-----|---------|
| `/store/login` | Staff login |
| `/store` | Dashboard |
| `/store/pos` | Point of Sale |
| `/store/customers` | CRM |
| `/store/products` | Inventory |

---

## Next Steps

1. **Change admin password** (Critical!)
2. **Configure settings** at `/store/admin/settings`
3. **Add products** at `/store/products`
4. **Customize storefront** at `/store/storefront`
5. **Create staff users** at `/store/admin/users`

---

## Troubleshooting

### Route not found
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Database connection failed
Check `.env` files have correct credentials

### Can't login
```bash
# Check sessions directory is writable
sudo chmod -R 775 /var/www/html/nautilus-staff/storage/sessions
sudo chown -R www-data:www-data /var/www/html/nautilus-staff/storage
```

---

## Documentation

For detailed information:
- **Deployment Guide**: `docs/ENTERPRISE_DEPLOYMENT_GUIDE.md`
- **Developer Guide**: `docs/DEVELOPER_GUIDE.md`

---

**You're all set! Happy diving! 🤿**
