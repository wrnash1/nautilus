# Nautilus Dive Shop - Deployment and Testing Guide

## Quick Start - Testing Workflow

Your standard testing workflow:

```bash
# 1. Deploy the application
cd ~/Developer
./deploy-to-test.sh

# 2. Test with curl (verify backend is working)
curl -k https://pangolin.local/store/login

# 3. Open in Google Chrome
# Navigate to: https://pangolin.local/store/login
```

---

## Application Overview

**Nautilus** is a comprehensive dive shop management system with two separate applications:

1. **Staff Management System** (`/store/*`) - Internal staff application with authentication
2. **Customer Storefront** (`/`) - Public-facing e-commerce website

---

## Deployment

### Prerequisites
- Apache web server running
- PHP 8.2+ installed
- MySQL/MariaDB database
- Composer for PHP dependencies

### Deploy Script

The `deploy-to-test.sh` script handles complete deployment:

```bash
cd ~/Developer
./deploy-to-test.sh
```

**What it does:**
1. ✅ Syncs files from `/home/wrnash1/Developer/nautilus/` to `/var/www/html/nautilus/`
2. ✅ Installs Composer dependencies if needed
3. ✅ Sets proper file permissions
4. ✅ Creates database if not exists
5. ✅ Runs database migrations
6. ✅ Seeds roles and permissions (admin, manager, cashier)
7. ✅ Creates admin user if not exists
8. ✅ Restarts Apache (clears opcache)

### Expected Output

```
==========================================
 Nautilus - Deploy to Test Server
==========================================

📦 Syncing files from development to web server...
✓ Vendor directory exists

🗄️  Database Setup
✓ Database 'nautilus' exists
✓ Skipped: 001_create_users_and_auth_tables.sql (already executed)
... (all migrations)

🌱 Checking if initial data needs to be seeded...
✓ Initial data already seeded (3 roles found)

👤 Setting up admin user...
✓ Admin user already exists

🔄 Restarting Apache (clears opcache)
✅ Apache restarted successfully

✅ Deployment Complete!

🌐 Test URLs:
   - Homepage: https://pangolin.local/
   - Staff Login: https://pangolin.local/store/login
   - Customer Login: https://pangolin.local/account/login
```

---

## Testing with Curl

### Test 1: Check Login Page Loads

```bash
curl -k https://pangolin.local/store/login
```

**Expected:** HTML page with login form and CSRF token

### Test 2: Verify CSRF Token Generation

```bash
curl -k https://pangolin.local/store/login 2>/dev/null | grep csrf_token
```

**Expected:**
```html
<input type="hidden" name="csrf_token" value="[64-character hex string]">
```

### Test 3: Test Login Flow

```bash
# Get login page and save cookies
curl -k -c /tmp/test_cookies.txt https://pangolin.local/store/login 2>/dev/null > /tmp/login.html

# Extract CSRF token
CSRF=$(grep -o 'csrf_token" value="[^"]*"' /tmp/login.html | cut -d'"' -f3)

# Attempt login
curl -k -b /tmp/test_cookies.txt -c /tmp/test_cookies.txt \
  -X POST https://pangolin.local/store/login \
  -d "email=admin@nautilus.local" \
  -d "password=password" \
  -d "csrf_token=$CSRF" \
  -i 2>&1 | grep -E "(HTTP|Location:)"
```

**Expected:**
```
HTTP/1.1 302 Found
Location: /store
```

### Test 4: Access Dashboard After Login

```bash
curl -k -b /tmp/test_cookies.txt https://pangolin.local/store 2>&1 | head -20
```

**Expected:** HTML with "Dashboard - Nautilus Dive Shop" in title

---

## Testing in Google Chrome

### Step 1: Clear Browser Cache

Before testing, always clear cache and cookies:
1. Press `Ctrl+Shift+Delete`
2. Select "Cookies and other site data"
3. Select "Cached images and files"
4. Click "Clear data"

**Or use Incognito Mode:** `Ctrl+Shift+N`

### Step 2: Navigate to Login

```
https://pangolin.local/store/login
```

### Step 3: Login with Admin Credentials

**Email:** `admin@nautilus.local`
**Password:** `password`

### Step 4: Verify Redirect to Dashboard

After login, you should be redirected to:
```
https://pangolin.local/store
```

You should see:
- ✅ Dashboard with metrics (Today's Sales, Total Customers, etc.)
- ✅ Sidebar navigation on the left
- ✅ Top navbar with user info

### Step 5: Test Navigation Links

Click each sidebar link to verify they work:

**Core Features:**
- [ ] Dashboard - `https://pangolin.local/store`
- [ ] Point of Sale - `https://pangolin.local/store/pos`
- [ ] Customers - `https://pangolin.local/store/customers`
- [ ] Products - `https://pangolin.local/store/products`
- [ ] Categories - `https://pangolin.local/store/categories`
- [ ] Vendors - `https://pangolin.local/store/vendors`

**Reports:**
- [ ] Sales Report - `https://pangolin.local/store/reports/sales`
- [ ] Customer Report - `https://pangolin.local/store/reports/customers`
- [ ] Product Report - `https://pangolin.local/store/reports/products`

**Rentals:**
- [ ] Equipment - `https://pangolin.local/store/rentals`
- [ ] Reservations - `https://pangolin.local/store/rentals/reservations`

**Services:**
- [ ] Air Fills - `https://pangolin.local/store/air-fills`
- [ ] Waivers - `https://pangolin.local/store/waivers`

**Training:**
- [ ] Courses - `https://pangolin.local/store/courses`
- [ ] Course Schedules - `https://pangolin.local/store/courses/schedules`
- [ ] Enrollments - `https://pangolin.local/store/courses/enrollments`

**Travel:**
- [ ] Trips - `https://pangolin.local/store/trips`
- [ ] Trip Schedules - `https://pangolin.local/store/trips/schedules`
- [ ] Bookings - `https://pangolin.local/store/trips/bookings`

**Service:**
- [ ] Work Orders - `https://pangolin.local/store/workorders`

---

## Known Issues & Troubleshooting

### Issue: "Route not found" Error

**Symptom:** JSON response `{"error":"Route not found"}`

**Causes:**
1. Using wrong URL (e.g., `/login` instead of `/store/login`)
2. Route not defined in `routes/web.php`
3. Controller or method doesn't exist

**Solution:**
- Verify you're using the correct URL with `/store` prefix
- Check `routes/web.php` for the route definition
- Check that the controller exists in `app/Controllers/`

### Issue: Login Page Refreshes, No Redirect

**Symptom:** After clicking "Sign In", page just refreshes

**Causes:**
1. CSRF token validation failing
2. Session not working
3. Authentication failing

**Solution:**
```bash
# Check if CSRF token is being generated
curl -k https://pangolin.local/store/login 2>/dev/null | grep csrf_token

# Restart Apache to clear sessions
sudo systemctl restart apache2

# Check Apache error log
sudo tail -50 /var/log/apache2/error.log
```

### Issue: Sidebar Links Don't Work

**Symptom:** Clicking sidebar links results in 404 or "Route not found"

**Cause:** Links not using the `url()` helper function

**Solution:** Verify `app/Views/layouts/app.php` has:
```php
href="<?= url('/store/path') ?>"
```

Not:
```php
href="/path"
```

### Issue: Browser Shows Wrong URL

**Symptom:** Browser shows `/login` even though curl shows `/store`

**Cause:** Browser cache with old redirects

**Solution:**
1. Clear browser cache completely
2. Use Incognito mode
3. Hard refresh: `Ctrl+Shift+R`

---

## Database Setup

### Users and Roles

**Default Roles:**
- `admin` - Full system access
- `manager` - Manage inventory, customers, reports (no user management)
- `cashier` - POS and customer view only

**Default Admin User:**
- Email: `admin@nautilus.local`
- Password: `password`
- Role: Admin

**Other Test Users:**
- `manager@nautilus.demo` / `password` - Manager role
- `cashier@nautilus.demo` / `password` - Cashier role

### Database Tables

**Authentication:**
- `users` - Staff users
- `roles` - User roles
- `permissions` - System permissions
- `role_permissions` - Role-permission mappings

**Core Business:**
- `customers` - Customer records
- `products` - Product catalog
- `categories` - Product categories
- `vendors` - Suppliers
- `transactions` - Sales transactions
- `transaction_items` - Line items

**Inventory:**
- `inventory_adjustments` - Stock adjustments
- `stock_alerts` - Low stock notifications

**Rentals:**
- `rental_equipment` - Rental inventory
- `rental_reservations` - Bookings
- `rental_reservation_items` - Items in reservation

**Services:**
- `air_fills` - Air fill services
- `work_orders` - Equipment repairs

**Training:**
- `courses` - Course catalog
- `course_schedules` - Class schedules
- `course_enrollments` - Student enrollments
- `certifications` - Student certifications

**Travel:**
- `trips` - Dive trip catalog
- `trip_schedules` - Trip dates
- `trip_bookings` - Customer bookings

**Reports:**
- `reports` - Saved report configurations
- `report_schedules` - Automated report generation

---

## Important Files

### Configuration
- `.env` - Environment configuration (database, app settings)
- `composer.json` - PHP dependencies

### Entry Points
- `public/index.php` - Main application entry (includes session start)

### Routing
- `routes/web.php` - All application routes

### Core Files
- `app/helpers.php` - Helper functions (redirect, url, etc.)
- `app/Core/Router.php` - Request routing
- `app/Core/Database.php` - Database connection
- `app/Core/Auth.php` - Authentication

### Controllers
- `app/Controllers/Auth/AuthController.php` - Login/logout
- `app/Controllers/Admin/DashboardController.php` - Dashboard
- `app/Controllers/POS/POSController.php` - Point of Sale
- `app/Controllers/Customer/CustomerController.php` - Customer management

### Views
- `app/Views/layouts/app.php` - Staff app layout (with sidebar)
- `app/Views/auth/login.php` - Login page
- `app/Views/dashboard/index.php` - Dashboard view

### Middleware
- `app/Middleware/AuthMiddleware.php` - Protect staff routes
- `app/Middleware/CustomerAuthMiddleware.php` - Protect customer routes

---

## Fixes Applied (Recent Session)

### 1. Session Initialization ✅
**File:** `public/index.php`
**Added:** `session_start()` and CSRF token generation
**Impact:** Login now works, CSRF tokens generated

### 2. Login Form Action ✅
**File:** `app/Views/auth/login.php`
**Changed:** Form action from `/login` to `/store/login`
**Impact:** Login form submits to correct URL

### 3. Helper Functions ✅
**File:** `app/helpers.php`
**Changed:** Path calculation from `str_replace()` to `dirname()`
**Impact:** Redirects work correctly

### 4. Sidebar Navigation ✅
**File:** `app/Views/layouts/app.php`
**Changed:** All `href="/path"` to `href="<?= url('/store/path') ?>"`
**Impact:** All sidebar links now work

### 5. Auth Controller Redirects ✅
**File:** `app/Controllers/Auth/AuthController.php`
**Changed:** All redirect paths to use `/store/login` and `/store`
**Impact:** Login/logout redirect to correct locations

### 6. Deploy Script Enhancement ✅
**File:** `deploy-to-test.sh`
**Added:** Automatic role seeding and admin user creation
**Impact:** Deployment now fully automated

---

## Architecture

### URL Structure

**Staff Application (`/store`):**
```
/store              → Dashboard
/store/login        → Staff login
/store/logout       → Staff logout
/store/pos          → Point of Sale
/store/customers    → Customer management
/store/products     → Product management
/store/reports/*    → Reports
/store/rentals/*    → Rental management
/store/courses/*    → Course management
/store/trips/*      → Trip management
```

**Customer Storefront (`/`):**
```
/                   → Homepage
/shop               → Product catalog
/shop/cart          → Shopping cart
/account/login      → Customer login
/account/register   → Customer registration
/account            → Customer dashboard
```

**System:**
```
/install            → Installation wizard
```

### Authentication Flow

1. User visits `/store/login`
2. Form loads with CSRF token from session
3. User submits email + password + CSRF token
4. `AuthController::login()` validates credentials
5. If valid: `Auth::login()` sets session, redirect to `/store`
6. If invalid: Redirect back to `/store/login` with error message

### Session Management

- Sessions stored in: `/var/www/html/nautilus/storage/sessions/`
- Session lifetime: 2 hours
- CSRF token regenerated on each page load
- User data cached in session after first authentication

---

## Development Workflow

### Making Code Changes

1. Edit files in: `/home/wrnash1/Developer/nautilus/`
2. Run deploy script: `./deploy-to-test.sh`
3. Test with curl
4. Test in browser

### Adding New Routes

Edit `routes/web.php`:

```php
// Public route
$router->get('/new-page', 'NewController@index');

// Protected route (staff only)
$router->get('/store/new-page', 'Admin\NewController@index', [AuthMiddleware::class]);

// Protected route (customer only)
$router->get('/account/new-page', 'Customer\NewController@index', [CustomerAuthMiddleware::class]);
```

### Creating Controllers

```php
<?php
namespace App\Controllers\Admin;

class NewController
{
    public function index()
    {
        $data = ['key' => 'value'];
        require __DIR__ . '/../../Views/admin/new-page.php';
    }
}
```

---

## Performance Tips

### Opcache

PHP opcache is enabled in production. After deploying code changes, always restart Apache:

```bash
sudo systemctl restart apache2
```

The deploy script does this automatically.

### Database Queries

- Use prepared statements for all queries
- Use indexes on frequently queried columns
- Batch operations when possible

### Session Storage

Sessions are stored in files. For better performance at scale, consider:
- Redis for session storage
- Database session storage
- Memcached

---

## Security Notes

### Current State
- ✅ CSRF protection enabled
- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (output escaping)
- ✅ Authentication middleware
- ✅ Role-based access control

### Recommendations
- Change default admin password
- Use HTTPS only in production
- Set secure session cookie flags
- Implement rate limiting for login attempts
- Regular security audits

---

## Support

### Check Logs

**Apache Error Log:**
```bash
sudo tail -f /var/log/apache2/error.log
```

**Application Logs:**
```bash
ls -la /var/www/html/nautilus/storage/logs/
```

### Database Access

```bash
mysql -u root -p
use nautilus;
SHOW TABLES;
SELECT * FROM users;
```

### File Permissions

If you get permission errors:
```bash
sudo chown -R www-data:www-data /var/www/html/nautilus/
sudo chmod -R 755 /var/www/html/nautilus/storage/
```

---

## Quick Reference

### URLs
- Staff Login: `https://pangolin.local/store/login`
- Dashboard: `https://pangolin.local/store`
- Public Home: `https://pangolin.local/`

### Credentials
- Admin: `admin@nautilus.local` / `password`
- Manager: `manager@nautilus.demo` / `password`
- Cashier: `cashier@nautilus.demo` / `password`

### Commands
```bash
# Deploy
./deploy-to-test.sh

# Test login
curl -k https://pangolin.local/store/login

# Restart Apache
sudo systemctl restart apache2

# Check logs
sudo tail -f /var/log/apache2/error.log
```

---

## Last Updated

This guide reflects the state of the application as of October 27, 2025.

All core functionality is working:
- ✅ Login and authentication
- ✅ Session management
- ✅ Navigation
- ✅ Database setup
- ✅ Role-based access control

Ready for feature development and testing!
