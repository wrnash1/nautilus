# Nautilus Application Testing Checklist

**Date**: October 26, 2025
**Deployment Status**: ✅ Successfully deployed to `/var/www/html/nautilus/`

---

## Quick Start Testing

### Important URLs

Based on your Apache log showing `https://pangolin.local`, use these URLs:

#### Customer-Facing Storefront (Application 1)
- **Homepage**: `https://pangolin.local/nautilus/public/` or `https://pangolin.local/nautilus/public/index.php`
- **Shop**: `https://pangolin.local/nautilus/public/shop`
- **Customer Login**: `https://pangolin.local/nautilus/public/account/login`
- **Customer Register**: `https://pangolin.local/nautilus/public/account/register`

#### Staff Management System (Application 2)
- **Staff Login**: `https://pangolin.local/nautilus/public/store/login` (NOT `/store/loigin`)
- **Staff Dashboard**: `https://pangolin.local/nautilus/public/store`

---

## Pre-Testing Setup

### 1. Create Missing Directory

The deployment showed this error: `chmod: cannot access '/var/www/html/nautilus//public/uploads': No such file or directory`

Fix it:
```bash
sudo mkdir -p /var/www/html/nautilus/public/uploads
sudo chown www-data:www-data /var/www/html/nautilus/public/uploads
sudo chmod 755 /var/www/html/nautilus/public/uploads
```

### 2. Verify .env File Exists

```bash
ls -la /var/www/html/nautilus/.env
```

If it doesn't exist, copy from example:
```bash
sudo cp /var/www/html/nautilus/.env.example /var/www/html/nautilus/.env
sudo chown www-data:www-data /var/www/html/nautilus/.env
```

### 3. Configure Database

Edit the .env file:
```bash
sudo nano /var/www/html/nautilus/.env
```

Update these values:
```
DB_HOST=localhost
DB_DATABASE=nautilus
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

### 4. Create Database

```bash
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES;
EXIT;
```

### 5. Run Migrations

```bash
cd /var/www/html/nautilus
php scripts/migrate.php
```

This will create all 80+ database tables.

### 6. Create First Admin User

Option A - Via MySQL:
```bash
mysql -u root -p nautilus
```

```sql
INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, is_active, created_at, updated_at)
VALUES (
    'admin',
    'admin@nautilus.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- password: "password"
    'Admin',
    'User',
    1,
    1,
    NOW(),
    NOW()
);

-- Create default admin role if needed
INSERT INTO roles (name, description, created_at, updated_at)
VALUES ('Administrator', 'Full system access', NOW(), NOW())
ON DUPLICATE KEY UPDATE id=id;
```

Exit MySQL:
```sql
EXIT;
```

**Default Login Credentials**:
- Email: `admin@nautilus.local`
- Password: `password`

**⚠️ IMPORTANT**: Change this password immediately after first login!

---

## Testing Checklist

### Phase 1: Basic Application Access

#### Test 1: Homepage (Public)
- [ ] Navigate to: `https://pangolin.local/nautilus/public/`
- [ ] Expected: Homepage loads (may show installation wizard if database not setup)
- [ ] Expected: No authentication required
- [ ] Check for errors in Apache log: `sudo tail -f /var/log/apache2/error.log`

#### Test 2: Staff Login Page
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/login`
- [ ] Expected: Staff login form appears
- [ ] Expected: Email and password fields visible
- [ ] Note: The route `/store/loigin` (with typo) correctly shows error - routing works! ✅

#### Test 3: Customer Registration
- [ ] Navigate to: `https://pangolin.local/nautilus/public/account/register`
- [ ] Expected: Customer registration form appears
- [ ] Fields: First name, Last name, Email, Phone, Password, Password confirm

---

### Phase 2: Database Setup (If Not Done)

#### Test 4: Installation Wizard
If you see an installation wizard:
- [ ] Follow on-screen prompts
- [ ] Enter database credentials
- [ ] Create admin account
- [ ] Complete installation

#### Test 5: Manual Database Setup
If no wizard appears:
- [ ] Create database: `CREATE DATABASE nautilus;`
- [ ] Run migrations: `php /var/www/html/nautilus/scripts/migrate.php`
- [ ] Insert admin user (see SQL above)
- [ ] Verify tables: `SHOW TABLES;` should show 80+ tables

---

### Phase 3: Staff Application Testing (Application 2)

#### Test 6: Staff Login
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/login`
- [ ] Enter email: `admin@nautilus.local`
- [ ] Enter password: `password`
- [ ] Click login
- [ ] Expected: Redirect to `/store` dashboard

#### Test 7: Staff Dashboard
- [ ] After login, verify you're on dashboard
- [ ] Expected: Navigation menu visible
- [ ] Expected: Dashboard widgets/statistics visible
- [ ] Expected: Navigation links to all modules:
  - Dashboard
  - POS
  - Customers
  - Products
  - Rentals
  - Courses
  - Trips
  - Air Fills
  - Work Orders
  - Orders
  - Marketing
  - CMS
  - Staff
  - Reports
  - Settings

#### Test 8: Point of Sale (POS)
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/pos`
- [ ] Expected: POS interface loads
- [ ] Expected: Product search available
- [ ] Expected: Transaction interface visible

#### Test 9: Customer Management (CRM)
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/customers`
- [ ] Expected: Customer list page
- [ ] Click "Add Customer" button
- [ ] Expected: Customer creation form
- [ ] Try creating a test customer

#### Test 10: Product Management
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/products`
- [ ] Expected: Product list page
- [ ] Click "Add Product" button
- [ ] Expected: Product creation form
- [ ] Try creating a test product

#### Test 11: Settings Access
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/admin/settings`
- [ ] Expected: Settings dashboard
- [ ] Tabs visible: General, Tax, Email, Payment, etc.
- [ ] Try viewing different setting sections

#### Test 12: User Management
- [ ] Navigate to: `https://pangolin.local/nautilus/public/store/admin/users`
- [ ] Expected: User list page
- [ ] Should see admin user created earlier
- [ ] Click "Add User" to test form

---

### Phase 4: Customer Application Testing (Application 1)

#### Test 13: Shop Catalog (Public - No Login)
- [ ] Navigate to: `https://pangolin.local/nautilus/public/shop`
- [ ] Expected: Product catalog page loads
- [ ] Expected: Products displayed (if any exist)
- [ ] Expected: Can browse WITHOUT logging in

#### Test 14: Customer Registration
- [ ] Navigate to: `https://pangolin.local/nautilus/public/account/register`
- [ ] Fill in registration form:
  - First Name: Test
  - Last Name: Customer
  - Email: test@example.com
  - Phone: 555-1234
  - Password: password123
  - Confirm Password: password123
- [ ] Submit form
- [ ] Expected: Account created, redirected to `/account` dashboard

#### Test 15: Customer Login
- [ ] Navigate to: `https://pangolin.local/nautilus/public/account/login`
- [ ] Enter email: test@example.com
- [ ] Enter password: password123
- [ ] Click login
- [ ] Expected: Redirect to customer dashboard

#### Test 16: Customer Dashboard
- [ ] After login, verify you're on: `https://pangolin.local/nautilus/public/account`
- [ ] Expected: Customer dashboard visible
- [ ] Expected: Navigation links:
  - Dashboard
  - Orders
  - Profile
  - Addresses
- [ ] Try clicking "Orders" - should show order history (empty initially)

#### Test 17: Shopping Cart (Guest)
- [ ] Logout if logged in
- [ ] Navigate to: `https://pangolin.local/nautilus/public/shop`
- [ ] Click on a product (if any exist)
- [ ] Click "Add to Cart"
- [ ] Navigate to: `https://pangolin.local/nautilus/public/shop/cart`
- [ ] Expected: Shopping cart page with item

---

### Phase 5: Authentication Separation Testing

#### Test 18: Verify Staff/Customer Separation
- [ ] Login as customer at `/account/login`
- [ ] Try accessing: `https://pangolin.local/nautilus/public/store`
- [ ] Expected: Redirected to `/store/login` (staff login)
- [ ] Logout customer
- [ ] Login as staff at `/store/login`
- [ ] Verify staff dashboard access
- [ ] Try accessing: `https://pangolin.local/nautilus/public/account`
- [ ] Expected: Customer dashboard (different from staff)

This test confirms the two applications are properly separated! ✅

---

### Phase 6: Error Monitoring

#### Test 19: Check Application Logs
```bash
# Apache error log
sudo tail -f /var/log/apache2/error.log

# Application log (once created)
sudo tail -f /var/www/html/nautilus/storage/logs/app.log
```

Look for:
- [ ] No PHP fatal errors
- [ ] No database connection errors
- [ ] No file permission errors

#### Test 20: Check PHP Errors
Create a test file to verify PHP:
```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/nautilus/public/phpinfo.php
```

Visit: `https://pangolin.local/nautilus/public/phpinfo.php`
- [ ] PHP version: 8.2 or higher
- [ ] Extensions loaded: PDO, mysqli, mbstring, curl, openssl, gd
- [ ] Remove file after: `sudo rm /var/www/html/nautilus/public/phpinfo.php`

---

## Common Issues and Fixes

### Issue 1: "Route not found" Error

**Symptom**: JSON error like `{"error":"Route not found"}`

**Causes**:
1. Typo in URL (like `/store/loigin` instead of `/store/login`)
2. Missing .htaccess or mod_rewrite not enabled

**Fix**:
```bash
# Check if mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verify .htaccess exists
ls -la /var/www/html/nautilus/public/.htaccess
```

### Issue 2: Database Connection Error

**Symptom**: "Could not connect to database" or PDO errors

**Fix**:
```bash
# Verify .env file has correct credentials
sudo cat /var/www/html/nautilus/.env | grep DB_

# Test MySQL connection
mysql -u [username] -p -h localhost [database_name]
```

### Issue 3: Permission Errors

**Symptom**: Cannot write to logs, cache, or uploads

**Fix**:
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus/storage
sudo chmod -R 755 /var/www/html/nautilus/public/uploads
```

### Issue 4: Blank Page / White Screen

**Symptom**: No error, just blank page

**Fix**:
```bash
# Check PHP error log
sudo tail -100 /var/log/apache2/error.log

# Enable display_errors temporarily in .env
# Change APP_DEBUG=false to APP_DEBUG=true
```

### Issue 5: "Class not found" Errors

**Symptom**: PHP Fatal error: Class 'X' not found

**Fix**:
```bash
# Run composer install
cd /var/www/html/nautilus
composer install

# Or if composer not in PATH
php /usr/local/bin/composer install
```

### Issue 6: Session Errors

**Symptom**: Session-related errors or can't stay logged in

**Fix**:
```bash
# Ensure sessions directory exists
sudo mkdir -p /var/www/html/nautilus/storage/sessions
sudo chown www-data:www-data /var/www/html/nautilus/storage/sessions
sudo chmod 755 /var/www/html/nautilus/storage/sessions
```

---

## Testing Progress Tracker

Mark each section as you complete testing:

- [ ] Phase 1: Basic Application Access (6 tests)
- [ ] Phase 2: Database Setup
- [ ] Phase 3: Staff Application Testing (12 tests)
- [ ] Phase 4: Customer Application Testing (7 tests)
- [ ] Phase 5: Authentication Separation Testing
- [ ] Phase 6: Error Monitoring (2 tests)

---

## Quick Command Reference

### Deployment
```bash
cd ~/Developer
./deploy-to-test.sh
```

### View Logs
```bash
# Apache error log
sudo tail -f /var/log/apache2/error.log

# Application log
sudo tail -f /var/www/html/nautilus/storage/logs/app.log
```

### Database Operations
```bash
# Access MySQL
mysql -u root -p

# Run migrations
php /var/www/html/nautilus/scripts/migrate.php

# Rollback migrations
php /var/www/html/nautilus/scripts/migrate-rollback.php
```

### Permission Fixes
```bash
# Fix all permissions
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus/storage
sudo chmod -R 755 /var/www/html/nautilus/public/uploads
```

### Restart Services
```bash
# Restart Apache
sudo systemctl restart apache2

# Check Apache status
sudo systemctl status apache2

# Restart MySQL
sudo systemctl restart mysql
```

---

## Success Criteria

The application is working correctly when:

✅ **Customer Storefront (Application 1)**:
- Homepage loads without errors
- Product catalog is accessible without login
- Customers can register accounts
- Customers can login and access their dashboard
- Shopping cart works
- Checkout process functions

✅ **Staff Management (Application 2)**:
- Staff login page is accessible
- Staff can login with credentials
- Dashboard loads after login
- All modules are accessible (POS, CRM, Products, etc.)
- Can create/edit customers and products
- Settings are accessible
- Reports are viewable

✅ **Security**:
- Staff and customer authentication are separate
- Cannot access staff areas without staff login
- Cannot access customer dashboard without customer login
- CSRF protection working on forms
- Session management working

---

## Next Steps After Testing

Once testing is complete:

1. **Create Additional Admin Users**: Use User Management in staff application
2. **Configure Settings**: Set store name, tax rates, email settings
3. **Add Products**: Use Product Management to add inventory
4. **Configure Storefront**: Use Storefront Theme Designer
5. **Setup Payment Gateways**: Configure Stripe/Square in settings
6. **Setup Email**: Configure SMTP settings for notifications
7. **Add Course Templates**: If using PADI courses
8. **Import Vendor Catalogs**: If applicable

---

**Testing Date**: _______________
**Tested By**: _______________
**Status**: _______________

---

*For detailed information about the application architecture and features, see [APPLICATION_VERIFICATION_REPORT.md](APPLICATION_VERIFICATION_REPORT.md)*
