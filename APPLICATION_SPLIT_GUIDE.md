# Nautilus V6 - Application Split Guide

## Overview

This guide explains the separation of Nautilus V6 into two independent applications:

1. **nautilus-storefront** - External customer-facing e-commerce website
2. **nautilus-store** - Internal staff management system with role-based access

Both applications share the **same database** but are completely separate codebases with independent routing, views, and deployment.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      SHARED DATABASE                         │
│  MySQL/MariaDB: customers, products, orders, etc.           │
└─────────────────────────────────────────────────────────────┘
          ▲                                    ▲
          │                                    │
    ┌─────┴─────────┐                  ┌──────┴─────────┐
    │  STOREFRONT   │                  │     STORE      │
    │  (External)   │                  │   (Internal)   │
    │               │                  │                │
    │ /             │                  │ /store/        │
    │ /shop         │                  │ /store/pos     │
    │ /account      │                  │ /store/crm     │
    │               │                  │ /store/reports │
    └───────────────┘                  └────────────────┘
    Public Access                      Auth Required
                                       + Role-Based
```

---

## Application Breakdown

### STOREFRONT (External)

**URL:** `https://yourdomain.com/`

**Purpose:** Customer-facing e-commerce and informational website

**Features:**
- Homepage with customizable sections
- Product browsing and online shopping
- Shopping cart and checkout
- Customer account portal
- Contact forms
- Installation wizard

**Controllers:**
- `HomeController` - Homepage, about, contact
- `Shop\ShopController` - Product browsing, cart, checkout
- `Customer\CustomerAuthController` - Customer login/registration
- `Customer\CustomerPortalController` - Customer dashboard, orders
- `Install\InstallController` - Initial setup

**Views:**
- `storefront/` - Homepage sections, layouts
- `shop/` - Product listings, cart, checkout
- `customer/` - Customer portal pages
- `install/` - Installation wizard

**Authentication:** Optional (only for customer portal)

---

### STORE (Internal)

**URL:** `https://yourdomain.com/store/`

**Purpose:** Staff management system for daily operations

**Features:**
- Dashboard with key metrics
- Point of Sale (POS)
- Customer Relationship Management (CRM)
- Inventory Management
- Equipment Rentals
- Air Tank Fills
- Training Courses
- Dive Trip Bookings
- Work Orders
- Reporting & Analytics
- Staff Management
- External Storefront Configuration

**Controllers:**
- `Admin\DashboardController` - Main dashboard
- `Auth\AuthController` - Staff login/logout
- `POS\*` - Point of sale transactions
- `CRM\*` - Customer management
- `Inventory\*` - Products, categories, vendors
- `Rentals\*` - Equipment rental management
- `AirFills\*` - Tank fill tracking
- `Courses\*` - Training course management
- `Trips\*` - Dive trip management
- `WorkOrders\*` - Service work orders
- `Reports\*` - Sales, inventory, customer reports
- `Staff\*` - Employee management
- `Admin\Storefront\*` - Configure external storefront

**Views:**
- `dashboard/` - Main dashboard and admin pages
- `auth/` - Staff login
- `pos/` - POS interface
- `customers/` - CRM views
- `products/`, `categories/`, `vendors/` - Inventory management
- `rentals/`, `air-fills/`, `courses/`, `trips/`, `workorders/` - Operations
- `reports/` - Analytics and reporting
- `staff/` - Employee management
- `storefront/` - External storefront configuration

**Authentication:** Required for all pages

**Role-Based Access:** Different views/permissions based on user role
- Manager - Full access
- Sales Staff - POS, CRM, limited reports
- Instructor - Courses, students
- Technician - Work orders, equipment

---

## Running the Split

### Step 1: Execute the Split Script

```bash
cd /home/wrnash1/Developer/nautilus-v6
chmod +x scripts/split-applications.sh
./scripts/split-applications.sh
```

This will create:
- `/home/wrnash1/Developer/nautilus-storefront/`
- `/home/wrnash1/Developer/nautilus-store/`

### Step 2: Install Dependencies

```bash
# Storefront
cd /home/wrnash1/Developer/nautilus-storefront
composer install

# Store
cd /home/wrnash1/Developer/nautilus-store
composer install
```

### Step 3: Configure Environment Files

Edit both `.env` files and ensure database credentials are correct:

**Storefront** (`/home/wrnash1/Developer/nautilus-storefront/.env`):
```env
APP_NAME="Nautilus Storefront"
APP_ENV=development
APP_URL=http://yourdomain.com

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

**Store** (`/home/wrnash1/Developer/nautilus-store/.env`):
```env
APP_NAME="Nautilus Store Management"
APP_ENV=development
APP_URL=http://yourdomain.com/store

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

**Both apps use the SAME database!**

### Step 4: Deploy to Web Server

#### Option A: Copy to Web Server

```bash
# Storefront
sudo rsync -av --delete --exclude='vendor/' \
  /home/wrnash1/Developer/nautilus-storefront/ \
  /var/www/html/nautilus-storefront/

# Store
sudo rsync -av --delete --exclude='vendor/' \
  /home/wrnash1/Developer/nautilus-store/ \
  /var/www/html/nautilus-store/

# Install dependencies on server
cd /var/www/html/nautilus-storefront && composer install
cd /var/www/html/nautilus-store && composer install

# Set permissions
sudo chown -R www-data:www-data /var/www/html/nautilus-storefront
sudo chown -R www-data:www-data /var/www/html/nautilus-store
sudo chmod -R 755 /var/www/html/nautilus-storefront
sudo chmod -R 755 /var/www/html/nautilus-store
```

### Step 5: Configure Apache

#### Option 1: Single VirtualHost with Alias (Recommended)

Edit your Apache VirtualHost configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com

    # STOREFRONT (External)
    DocumentRoot /var/www/html/nautilus-storefront/public

    <Directory /var/www/html/nautilus-storefront/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # STORE (Internal) - Accessible at /store
    Alias /store /var/www/html/nautilus-store/public

    <Directory /var/www/html/nautilus-store/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/nautilus_error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus_access.log combined
</VirtualHost>
```

#### Option 2: Separate Subdomains

```apache
# External Storefront
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/nautilus-storefront/public

    <Directory /var/www/html/nautilus-storefront/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Internal Store
<VirtualHost *:80>
    ServerName store.yourdomain.com
    DocumentRoot /var/www/html/nautilus-store/public

    <Directory /var/www/html/nautilus-store/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Enable and Restart Apache

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## URL Structure

### Storefront (External)

| URL | Purpose |
|-----|---------|
| `/` | Homepage |
| `/about` | About page |
| `/contact` | Contact form |
| `/shop` | Product browsing |
| `/shop/product/{id}` | Product details |
| `/shop/cart` | Shopping cart |
| `/shop/checkout` | Checkout process |
| `/account/login` | Customer login |
| `/account/register` | Customer registration |
| `/account/dashboard` | Customer portal |
| `/account/orders` | Order history |
| `/install` | Installation wizard (first run) |

### Store (Internal)

| URL | Purpose |
|-----|---------|
| `/store/` | Dashboard |
| `/store/login` | Staff login |
| `/store/pos` | Point of Sale |
| `/store/customers` | CRM |
| `/store/products` | Inventory management |
| `/store/categories` | Product categories |
| `/store/vendors` | Vendor management |
| `/store/rentals` | Equipment rentals |
| `/store/air-fills` | Tank fill tracking |
| `/store/courses` | Training courses |
| `/store/trips` | Dive trip management |
| `/store/workorders` | Service orders |
| `/store/reports/sales` | Sales reports |
| `/store/reports/inventory` | Inventory reports |
| `/store/reports/customers` | Customer reports |
| `/store/storefront` | Configure external storefront |
| `/store/staff` | Staff management |

---

## Shared Code

Both applications share:

- **Core Classes** - `Database`, `Router`, `Auth`, `ErrorHandler`, etc.
- **Models** - All database models (Customer, Product, Order, etc.)
- **Services** - Business logic (ProductService, CustomerService, etc.)
- **Middleware** - Security, CSRF, Authentication
- **Migrations** - Database schema (both apps use same DB)

These are **copied** to both applications. In the future, they can be extracted into a shared Composer package.

---

## Role-Based Access (Store App)

The internal Store application supports role-based views and permissions.

### Default Roles

| Role | Access Level |
|------|-------------|
| **Owner/Manager** | Full access to all features |
| **Sales Staff** | POS, CRM, basic reports |
| **Instructor** | Courses, enrollments, student management |
| **Technician** | Work orders, equipment, rentals |
| **Bookkeeper** | Reports, financial data (read-only) |

### Implementing Role Checks

Roles are checked in:

1. **Middleware** - `RoleMiddleware` (to be implemented)
2. **Controllers** - Check role before rendering views
3. **Views** - Conditionally show/hide menu items and features

Example controller code:
```php
public function index()
{
    $user = Auth::user();

    // Role-based view selection
    if ($user->hasRole('manager')) {
        return $this->renderManagerDashboard();
    } elseif ($user->hasRole('sales_staff')) {
        return $this->renderSalesDashboard();
    }
    // etc...
}
```

---

## Database Setup

**Important:** Both applications use the **same database**!

1. Run migrations **once** from either application:

```bash
cd /var/www/html/nautilus-storefront
# Or: cd /var/www/html/nautilus-store

# Run migrations
php scripts/migrate.php
```

2. The database `nautilus` will be used by both apps

---

## Security Considerations

### Storefront (External)
- Public access to most pages
- Customer authentication optional
- Rate limiting on forms
- CSRF protection on all POST requests
- Input validation/sanitization

### Store (Internal)
- **All pages require authentication**
- Session-based authentication with secure cookies
- Optional 2FA for staff
- Role-based access control
- IP whitelisting (optional)
- Activity logging
- CSRF protection on all state-changing requests

---

## Deployment Checklist

- [ ] Run split script to create both applications
- [ ] Install Composer dependencies in both apps
- [ ] Configure `.env` files with database credentials
- [ ] Copy both apps to web server
- [ ] Set proper file permissions (www-data:www-data)
- [ ] Configure Apache VirtualHost
- [ ] Enable mod_rewrite in Apache
- [ ] Run database migrations (once)
- [ ] Test external storefront at `/`
- [ ] Test internal store at `/store/`
- [ ] Create initial staff user accounts
- [ ] Configure role permissions
- [ ] Test authentication and session handling
- [ ] Enable HTTPS in production
- [ ] Set up automated backups
- [ ] Configure error logging

---

## Development Workflow

### Making Changes

When developing:

1. Work in the development directories:
   - `/home/wrnash1/Developer/nautilus-storefront/`
   - `/home/wrnash1/Developer/nautilus-store/`

2. Test locally

3. Deploy to web server:
```bash
# Sync changes
sudo rsync -av --delete --exclude='vendor/' --exclude='.git/' \
  /home/wrnash1/Developer/nautilus-storefront/ \
  /var/www/html/nautilus-storefront/

sudo rsync -av --delete --exclude='vendor/' --exclude='.git/' \
  /home/wrnash1/Developer/nautilus-store/ \
  /var/www/html/nautilus-store/
```

### Shared Code Updates

If you update shared code (Core, Models, Services):

1. Update in **one** application's development directory
2. Copy to the **other** application:
```bash
# Example: Copy Models from Storefront to Store
cp -r /home/wrnash1/Developer/nautilus-storefront/app/Models/* \
      /home/wrnash1/Developer/nautilus-store/app/Models/
```

3. Eventually extract to a Composer package to avoid duplication

---

## Testing

### Storefront
- Visit: `http://yourdomain.com/`
- Test: Homepage, shop browsing, cart, checkout
- Test: Customer registration and login
- Test: Mobile responsiveness

### Store
- Visit: `http://yourdomain.com/store/`
- Should redirect to `/store/login` if not authenticated
- Test: Staff login with different roles
- Test: POS transaction flow
- Test: Customer lookup and management
- Test: Report generation

---

## Troubleshooting

### Issue: 404 on all routes

**Solution:** Enable mod_rewrite and check .htaccess
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Issue: Database connection failed

**Solution:** Check .env files have correct database credentials. Both apps must use the same database.

### Issue: /store routes not working

**Solution:** Check Apache Alias configuration is correct. The Alias must point to the Store's `public/` directory.

### Issue: CSS/JS not loading

**Solution:** Check file permissions:
```bash
sudo chmod -R 755 /var/www/html/nautilus-storefront/public
sudo chmod -R 755 /var/www/html/nautilus-store/public
```

### Issue: Session not persisting

**Solution:** Check storage/sessions directory permissions:
```bash
sudo chown -R www-data:www-data /var/www/html/nautilus-store/storage
sudo chmod -R 775 /var/www/html/nautilus-store/storage
```

---

## Future Enhancements

1. **Extract Shared Code to Composer Package**
   - Create `nautilus/core` package
   - Include Models, Services, Core classes
   - Version independently

2. **API for Communication**
   - Store app provides REST API
   - Storefront consumes API for product data
   - Further decouples the applications

3. **Separate Databases (Optional)**
   - Read-replica for Storefront (read-only)
   - Master DB for Store (read-write)
   - Improved security and performance

4. **Microservices Architecture (Optional)**
   - Split into more granular services
   - Each service has its own database
   - Event-driven communication

---

## Questions?

If you have questions about the application split, refer to:
- Original README: `docs/README.md`
- Installation Guide: `docs/INSTALLATION.md`
- Security Guide: `docs/SECURITY.md`

---

**Last Updated:** 2025-10-25
