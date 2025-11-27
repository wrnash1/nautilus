# Nautilus Installation Testing Guide

**Purpose:** Complete guide for testing fresh installation
**Date:** 2025-01-22

---

## Pre-Installation Steps

### 1. Deploy Application Files

Run the deployment script:
```bash
cd /home/wrnash1/development/nautilus/scripts
./deploy-to-production.sh
```

This will:
- Copy all files to `/var/www/html/nautilus/`
- Set proper ownership (apache:apache)
- Set proper permissions (755 for files, 775 for storage/uploads)
- Install Composer dependencies
- Preserve .env if it exists

### 2. Verify Database is Clean

```bash
mysql -u root -p'Frogman09!' -e "DROP DATABASE IF EXISTS nautilus_dev; CREATE DATABASE nautilus_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 3. Remove Installation Marker (if exists)

```bash
sudo rm -f /var/www/html/nautilus/.installed
```

---

## Installation Testing

### Step 1: Access Installer

**URL:** `https://nautilus.local/install/`

**Expected Result:**
- Modern 4-step installer displays
- Step 1 (System Requirements) is active
- Beautiful gradient design
- Progress indicators at top

### Step 2: System Requirements Check

**Action:** Wait for automatic system checks to complete

**Expected Results:**
All checks should show status:
- ✅ PHP Version (>= 8.1) - Green check
- ✅ Web Server - Green check (Apache/Nginx)
- ✅ PDO Extension - Green check
- ✅ PDO MySQL Driver - Green check
- ✅ OpenSSL Extension - Green check
- ✅ MBString Extension - Green check
- ✅ JSON Extension - Green check
- ✅ Curl Extension - Green check
- ✅ GD Extension - Green check
- ✅ Zip Extension - Green check
- ✅ Storage Directory Writable - Green check
- ✅ Uploads Directory Writable - Green check
- ✅ .htaccess File - Green check
- ✅ Apache mod_rewrite - Green check
- ⚠️ SELinux Status - Yellow warning (if Enforcing - OK)
- ✅ Firewall Configuration - Green check
- ⚠️ PHP Memory Limit - Yellow warning (if 128M - OK, but recommends 256M)

**Button Text:** Should change to "Continue to Configuration"

**Action:** Click "Continue to Configuration"

### Step 3: Configuration Form

**Expected Result:**
- Form displays with all fields
- Timezone dropdown has all timezones

**Fill In:**
- App URL: `https://nautilus.local`
- Business Name: `Nautilus Dive Shop`
- Admin Email: `admin@nautilus.local`
- Timezone: Select your timezone (e.g., America/New_York)
- Database Host: `localhost`
- Database Name: `nautilus_dev`
- Database Username: `root`
- Database Password: `Frogman09!`

**Action:** Click "Continue to Database Setup"

### Step 4: Database Installation

**Expected Result:**
- Progress bar appears
- Shows "Creating 27 tables..."
- Progress animates from 0% to 100%
- Takes 2-5 seconds

**Success Indicators:**
- ✅ Green checkmark appears
- "Database installed successfully"
- Shows statistics:
  - 27 tables created
  - 1 user created
  - 4 roles created
  - 5 certification agencies created

**Action:** Click "Continue to Complete"

### Step 5: Success Screen

**Expected Result:**
- Success message displays
- Shows default credentials:
  - Email: admin@nautilus.local
  - Password: admin123
  - Warning to change password
- "Go to Admin Panel" button visible

**Action:** Click "Go to Admin Panel"

---

## Post-Installation Testing

### Test 1: Admin Login

**URL:** `https://nautilus.local/login`

**Credentials:**
- Email: `admin@nautilus.local`
- Password: `admin123`

**Expected Result:**
- Login successful
- Redirects to admin dashboard
- No errors
- Sidebar navigation visible

### Test 2: Check Database

```bash
mysql -u root -p'Frogman09!' nautilus_dev -e "SHOW TABLES;"
```

**Expected Result:** 27 tables:
1. audit_logs
2. categories
3. certification_agencies
4. company_settings
5. customer_addresses
6. customer_certifications
7. customer_tag_assignments
8. customer_tags
9. customers
10. feedback
11. feedback_attachments
12. feedback_comments
13. migrations
14. password_resets
15. permissions
16. products
17. role_permissions
18. roles
19. sessions
20. settings
21. storefront_carousel_slides
22. storefront_service_boxes
23. tenants
24. transaction_items
25. transactions
26. users
27. vendors

### Test 3: Verify Default Data

```bash
mysql -u root -p'Frogman09!' nautilus_dev -e "SELECT * FROM users;"
mysql -u root -p'Frogman09!' nautilus_dev -e "SELECT * FROM roles;"
mysql -u root -p'Frogman09!' nautilus_dev -e "SELECT * FROM certification_agencies;"
```

**Expected Results:**
- 1 admin user (admin@nautilus.local)
- 4 roles (Admin, Manager, Staff, Instructor)
- 5 certification agencies (PADI, SSI, NAUI, SDI, TDI)

### Test 4: Check Files Created

```bash
ls -la /var/www/html/nautilus/.installed
ls -la /var/www/html/nautilus/.env
```

**Expected Result:**
- `.installed` file exists with timestamp
- `.env` file exists with configuration

### Test 5: Storefront

**URL:** `https://nautilus.local/`

**Expected Result:**
- Beautiful modern homepage displays
- Hero carousel visible (default 3 slides)
- 6 service boxes displayed
- Customer Portal button visible
- Staff button visible
- Footer displays

### Test 6: Customer Registration

**URL:** `https://nautilus.local/account/register`

**Test Registration:**
- First Name: `John`
- Last Name: `Doe`
- Email: `john@example.com`
- Password: `password123`
- Password Confirm: `password123`

**Expected Result:**
- Registration successful
- Auto-login
- Redirects to customer dashboard
- Shows welcome message

### Test 7: Customer Portal

**Expected Result:**
- Dashboard displays
- Stats cards visible (orders, spending, courses, trips)
- Navigation menu works
- Can view profile
- Can change password
- Can logout

### Test 8: Staff Features

**Login as Admin:**
- URL: `https://nautilus.local/login`
- Email: `admin@nautilus.local`
- Password: `admin123`

**Test:**
- Dashboard loads
- Sidebar navigation visible
- Can access Customers
- Can access Products
- Can access Settings
- Can access Storefront Configuration

---

## Feature Testing Checklist

### Core Features
- [ ] Installer completes successfully
- [ ] Database creates 27 tables
- [ ] Default admin user created
- [ ] .installed marker created
- [ ] .env file generated

### Security
- [ ] Passwords are hashed (bcrypt)
- [ ] CSRF protection working
- [ ] Session management working
- [ ] .env file protected from web access

### Storefront
- [ ] Homepage displays correctly
- [ ] Carousel animates
- [ ] Service boxes display
- [ ] Navigation links work
- [ ] Footer displays
- [ ] Responsive design works on mobile

### Customer Portal
- [ ] Registration works
- [ ] Login works
- [ ] Dashboard displays
- [ ] Profile can be updated
- [ ] Password can be changed
- [ ] Logout works

### Admin Panel
- [ ] Login works
- [ ] Dashboard displays
- [ ] Sidebar navigation visible
- [ ] Customer management accessible
- [ ] Product management accessible
- [ ] Settings accessible
- [ ] Storefront config accessible

### Feedback System (Tables Created)
- [ ] `feedback` table exists
- [ ] `feedback_attachments` table exists
- [ ] `feedback_comments` table exists
- [ ] Can submit feedback (once views created)

---

## Common Issues & Solutions

### Issue 1: System Checks Don't Complete

**Symptoms:**
- Checks stay as "Checking..."
- No green/red indicators appear

**Solution:**
```bash
# Check browser console for errors
# Verify check.php is accessible:
curl https://nautilus.local/install/check.php
```

### Issue 2: Database Connection Failed

**Symptoms:**
- "Database connection failed" error

**Solution:**
```bash
# Verify MySQL is running:
sudo systemctl status mariadb

# Test connection:
mysql -u root -p'Frogman09!' -e "SELECT 1;"

# Check database exists:
mysql -u root -p'Frogman09!' -e "SHOW DATABASES LIKE 'nautilus_dev';"
```

### Issue 3: Permission Errors

**Symptoms:**
- "Permission denied" errors
- Can't write to storage/
- Can't upload files

**Solution:**
```bash
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chmod -R 775 /var/www/html/nautilus/public/uploads
```

### Issue 4: 404 Errors

**Symptoms:**
- Pages show 404
- Routes not working

**Solution:**
```bash
# Verify .htaccess exists:
ls -la /var/www/html/nautilus/public/.htaccess

# Check Apache mod_rewrite:
sudo apachectl -M | grep rewrite

# If not enabled:
sudo a2enmod rewrite
sudo systemctl restart httpd
```

### Issue 5: Blank Page

**Symptoms:**
- White/blank page
- No error message

**Solution:**
```bash
# Check error logs:
sudo tail -50 /var/log/httpd/error_log

# Check PHP errors:
tail -50 /var/www/html/nautilus/storage/logs/app.log

# Enable error display temporarily:
# Edit public/index.php and set:
# error_reporting(E_ALL);
# ini_set('display_errors', '1');
```

---

## Success Criteria

Installation is successful when ALL of the following are true:

✅ Installer completes all 4 steps
✅ Database has 27 tables
✅ Default admin user can login
✅ Customer can register and login
✅ Storefront displays correctly
✅ Admin panel is accessible
✅ No PHP errors in logs
✅ All file permissions correct
✅ .env file created and secure

---

## Next Steps After Successful Installation

1. **Change Admin Password**
   - Login → Profile → Change Password

2. **Configure Business Settings**
   - Admin → Settings → Company Settings

3. **Customize Storefront**
   - Admin → Storefront Configuration
   - Add carousel slides
   - Configure service boxes

4. **Add Products/Services**
   - Admin → Products → Add Product
   - Admin → Courses → Add Course

5. **Test Customer Features**
   - Register test customer
   - Test ordering process
   - Test customer portal

6. **Set Up Email (Optional)**
   - Configure SMTP in .env
   - Test email notifications

7. **Add Demo Data (Optional)**
   ```bash
   cd /var/www/html/nautilus
   php scripts/seed-demo-data.php
   ```

---

**Ready for production deployment after successful testing!**
