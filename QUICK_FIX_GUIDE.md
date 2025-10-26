# Quick Fix Guide - Get Nautilus Working Now

## Current Status

✅ **Homepage Works**: `https://pangolin.local/` shows the storefront
❌ **Login Routes Fail**: `/store/login` and `/account/login` return "Route not found"
❌ **Cart Count Missing**: `/shop/cart/count` returns 404

## The Problem

Apache is serving the app at the root (`https://pangolin.local/`) but the router doesn't know the base path is empty.

## The Solution (5 Minutes)

### Step 1: Deploy Updated Files

Run this in your terminal:
```bash
cd ~/Developer
./deploy-to-test.sh
```

### Step 2: Configure .env File

```bash
# Check if .env exists
ls -la /var/www/html/nautilus/.env

# If it doesn't exist, copy from example
sudo cp /var/www/html/nautilus/.env.example /var/www/html/nautilus/.env

# Edit the .env file
sudo nano /var/www/html/nautilus/.env
```

### Step 3: Set These Values in .env

Make sure these lines are at the top of the file:

```env
# Application Settings
APP_NAME="Nautilus"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://pangolin.local
APP_TIMEZONE=America/New_York
APP_BASE_PATH=

# Database Configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=your_mysql_password_here

# Security (generate these later)
APP_KEY=base64:randomkey123456789
JWT_SECRET=your-secret-key-here
SESSION_LIFETIME=120
PASSWORD_MIN_LENGTH=8
```

**IMPORTANT**:
- `APP_BASE_PATH=` must be **empty** (no value after the equals sign)
- Replace `your_mysql_password_here` with your actual MySQL password

Save the file (Ctrl+X, Y, Enter).

### Step 4: Set Permissions

```bash
sudo chown www-data:www-data /var/www/html/nautilus/.env
sudo chmod 644 /var/www/html/nautilus/.env
```

### Step 5: Test the Routes

Now try these URLs in your browser:

**Staff Login:**
```
https://pangolin.local/store/login
```
Should show a login form, not "Route not found"

**Customer Login:**
```
https://pangolin.local/account/login
```
Should show a login form

**Cart Count API:**
```
https://pangolin.local/shop/cart/count
```
Should show JSON: `{"count":0}`

## What We Fixed

1. **Added `APP_BASE_PATH=` to .env** - Tells router the app is at root level
2. **Added `/shop/cart/count` route** - Fixes 404 error in browser console
3. **Added `cartCount()` method** - Returns cart item count as JSON

## If Routes Still Don't Work

### Check mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Check AllowOverride

```bash
sudo nano /etc/apache2/apache2.conf
```

Find this section:
```apache
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride All    # Make sure this is "All", not "None"
    Require all granted
</Directory>
```

If you changed it, restart Apache:
```bash
sudo systemctl restart apache2
```

### Check .htaccess is Present

```bash
ls -la /var/www/html/nautilus/public/.htaccess
cat /var/www/html/nautilus/public/.htaccess
```

Should show the rewrite rules.

## Create Database (If Not Done)

```bash
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

## Run Migrations

```bash
php /var/www/html/nautilus/scripts/migrate.php
```

This will create all the tables.

## Create First Admin User

```bash
mysql -u root -p nautilus
```

In MySQL:
```sql
-- Create admin role first
INSERT INTO roles (id, name, description, created_at, updated_at)
VALUES (1, 'Administrator', 'Full system access', NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Create admin user (password is "password")
INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, is_active, created_at, updated_at)
VALUES (
    'admin',
    'admin@nautilus.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    1,
    1,
    NOW(),
    NOW()
);

EXIT;
```

**Login Credentials:**
- Email: `admin@nautilus.local`
- Password: `password`

## Test Staff Login

1. Go to: `https://pangolin.local/store/login`
2. Enter email: `admin@nautilus.local`
3. Enter password: `password`
4. Click Login
5. Should redirect to: `https://pangolin.local/store` (dashboard)

## Test Customer Registration

1. Go to: `https://pangolin.local/account/register`
2. Fill in the form:
   - First Name: Test
   - Last Name: Customer
   - Email: test@example.com
   - Phone: 555-1234
   - Password: password123
   - Confirm: password123
3. Click Register
4. Should redirect to: `https://pangolin.local/account` (dashboard)

## Expected Behavior After Fix

### These should work:
✅ `https://pangolin.local/` - Homepage
✅ `https://pangolin.local/shop` - Product catalog
✅ `https://pangolin.local/shop/cart` - Shopping cart
✅ `https://pangolin.local/shop/cart/count` - Cart count API
✅ `https://pangolin.local/store/login` - Staff login
✅ `https://pangolin.local/account/login` - Customer login
✅ `https://pangolin.local/account/register` - Customer registration

### After logging in as staff:
✅ `https://pangolin.local/store` - Dashboard
✅ `https://pangolin.local/store/pos` - Point of Sale
✅ `https://pangolin.local/store/customers` - Customer management
✅ `https://pangolin.local/store/products` - Product management
✅ `https://pangolin.local/store/admin/settings` - Settings

## Troubleshooting

### Still getting "Route not found"?

1. **Check .env was created:**
```bash
cat /var/www/html/nautilus/.env | grep APP_BASE_PATH
```
Should show: `APP_BASE_PATH=`

2. **Check Apache error log:**
```bash
sudo tail -50 /var/log/apache2/error.log
```

3. **Enable debug mode:**
Edit .env and set:
```env
APP_DEBUG=true
APP_ENV=local
```

4. **Clear any cached routes (if applicable):**
```bash
sudo rm -rf /var/www/html/nautilus/storage/cache/*
```

5. **Restart Apache:**
```bash
sudo systemctl restart apache2
```

### Getting database errors?

Make sure:
- Database exists: `SHOW DATABASES;` in MySQL
- Migrations ran: `php /var/www/html/nautilus/scripts/migrate.php`
- .env has correct DB credentials

### Can't see application logs?

```bash
# Check if logs directory exists
ls -la /var/www/html/nautilus/storage/logs/

# Create if missing
sudo mkdir -p /var/www/html/nautilus/storage/logs
sudo chown www-data:www-data /var/www/html/nautilus/storage/logs
sudo chmod 755 /var/www/html/nautilus/storage/logs
```

## Summary

**The main fix is setting `APP_BASE_PATH=` (empty) in the .env file.**

This tells the router that the application is at the root of the domain, not in a subdirectory.

After setting this and deploying the updated files, all routes should work correctly!

---

**Time Required**: 5-10 minutes
**Difficulty**: Easy
**Success Rate**: High (assuming database is configured)
