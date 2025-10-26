#!/bin/bash

################################################################################
# Nautilus Enterprise Application Split Script
# Version: 2.0
# Purpose: Split monolithic application into Customer and Staff applications
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SOURCE_DIR="$(dirname "$SCRIPT_DIR")"
PARENT_DIR="$(dirname "$SOURCE_DIR")"
CUSTOMER_APP="$PARENT_DIR/nautilus-customer"
STAFF_APP="$PARENT_DIR/nautilus-staff"

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

################################################################################
# Main Script
################################################################################

print_header "Nautilus Enterprise Application Split"
echo ""
echo "This script will create two separate applications:"
echo "  1. Customer Application (Public Storefront)"
echo "  2. Staff Application (Internal Management)"
echo ""
echo "Source: $SOURCE_DIR"
echo "Target: $PARENT_DIR"
echo ""

# Confirm before proceeding
read -p "Do you want to continue? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_error "Aborted by user"
    exit 1
fi

################################################################################
# Step 1: Create Base Directory Structure
################################################################################

print_header "Step 1: Creating Directory Structure"

# Remove existing if present
if [ -d "$CUSTOMER_APP" ]; then
    print_warning "Removing existing customer app directory..."
    rm -rf "$CUSTOMER_APP"
fi

if [ -d "$STAFF_APP" ]; then
    print_warning "Removing existing staff app directory..."
    rm -rf "$STAFF_APP"
fi

# Create base directories
mkdir -p "$CUSTOMER_APP"
mkdir -p "$STAFF_APP"

print_success "Base directories created"

################################################################################
# Step 2: Copy Shared Core Files
################################################################################

print_header "Step 2: Copying Shared Core Components"

# Directories to copy to both apps
SHARED_DIRS=(
    "app/Core"
    "app/Models"
    "app/Services"
    "app/Middleware"
    "app/Languages"
    "database"
    "scripts"
    "storage"
)

# Files to copy to both apps
SHARED_FILES=(
    "app/helpers.php"
    "composer.json"
    "phpunit.xml"
    ".gitignore"
)

# Copy shared directories to both apps
for dir in "${SHARED_DIRS[@]}"; do
    if [ -d "$SOURCE_DIR/$dir" ]; then
        print_info "Copying $dir..."
        # Create parent directory if needed
        mkdir -p "$(dirname "$CUSTOMER_APP/$dir")"
        mkdir -p "$(dirname "$STAFF_APP/$dir")"
        cp -r "$SOURCE_DIR/$dir" "$CUSTOMER_APP/$dir"
        cp -r "$SOURCE_DIR/$dir" "$STAFF_APP/$dir"
    fi
done

# Copy shared files to both apps
for file in "${SHARED_FILES[@]}"; do
    if [ -f "$SOURCE_DIR/$file" ]; then
        print_info "Copying $file..."
        # Create parent directory if needed
        mkdir -p "$(dirname "$CUSTOMER_APP/$file")"
        mkdir -p "$(dirname "$STAFF_APP/$file")"
        cp "$SOURCE_DIR/$file" "$CUSTOMER_APP/$file"
        cp "$SOURCE_DIR/$file" "$STAFF_APP/$file"
    fi
done

print_success "Shared components copied"

################################################################################
# Step 3: Create Customer Application Structure
################################################################################

print_header "Step 3: Building Customer Application"

# Create customer app directories
mkdir -p "$CUSTOMER_APP/app/Controllers"
mkdir -p "$CUSTOMER_APP/app/Views"
mkdir -p "$CUSTOMER_APP/routes"
mkdir -p "$CUSTOMER_APP/public/assets"
mkdir -p "$CUSTOMER_APP/public/uploads"

# Copy customer-specific controllers
CUSTOMER_CONTROLLERS=(
    "HomeController.php"
    "ContactController.php"
    "Shop"
    "Customer"
    "Ecommerce"
    "Install"
    "CMS"
)

for controller in "${CUSTOMER_CONTROLLERS[@]}"; do
    if [ -e "$SOURCE_DIR/app/Controllers/$controller" ]; then
        print_info "Copying controller: $controller"
        cp -r "$SOURCE_DIR/app/Controllers/$controller" "$CUSTOMER_APP/app/Controllers/"
    fi
done

# Copy customer-specific views
CUSTOMER_VIEWS=(
    "layouts"
    "home"
    "shop"
    "customer"
    "ecommerce"
    "cart"
    "checkout"
    "contact"
    "cms"
    "emails"
    "components"
    "install"
)

for view in "${CUSTOMER_VIEWS[@]}"; do
    if [ -d "$SOURCE_DIR/app/Views/$view" ]; then
        print_info "Copying view: $view"
        cp -r "$SOURCE_DIR/app/Views/$view" "$CUSTOMER_APP/app/Views/"
    fi
done

# Copy public assets
if [ -d "$SOURCE_DIR/public" ]; then
    cp -r "$SOURCE_DIR/public"/* "$CUSTOMER_APP/public/"
fi

print_success "Customer application structure created"

################################################################################
# Step 4: Create Staff Application Structure
################################################################################

print_header "Step 4: Building Staff Application"

# Create staff app directories
mkdir -p "$STAFF_APP/app/Controllers"
mkdir -p "$STAFF_APP/app/Views"
mkdir -p "$STAFF_APP/routes"
mkdir -p "$STAFF_APP/public/assets"
mkdir -p "$STAFF_APP/public/uploads"

# Copy staff-specific controllers
STAFF_CONTROLLERS=(
    "Admin"
    "Auth"
    "POS"
    "CRM"
    "Inventory"
    "Rentals"
    "Courses"
    "Trips"
    "WorkOrders"
    "AirFills"
    "Reports"
    "Staff"
    "Marketing"
    "API"
    "Integrations"
    "Waivers"
    "DiveSites"
    "SerialNumbers"
)

for controller in "${STAFF_CONTROLLERS[@]}"; do
    if [ -e "$SOURCE_DIR/app/Controllers/$controller" ]; then
        print_info "Copying controller: $controller"
        cp -r "$SOURCE_DIR/app/Controllers/$controller" "$STAFF_APP/app/Controllers/"
    fi
done

# Copy staff-specific views
STAFF_VIEWS=(
    "layouts"
    "dashboard"
    "auth"
    "admin"
    "pos"
    "customers"
    "products"
    "categories"
    "vendors"
    "inventory"
    "rentals"
    "courses"
    "trips"
    "workorders"
    "air-fills"
    "orders"
    "reports"
    "staff"
    "marketing"
    "storefront"
    "cms"
    "integrations"
    "waivers"
    "dive-sites"
    "emails"
    "components"
)

for view in "${STAFF_VIEWS[@]}"; do
    if [ -d "$SOURCE_DIR/app/Views/$view" ]; then
        print_info "Copying view: $view"
        cp -r "$SOURCE_DIR/app/Views/$view" "$STAFF_APP/app/Views/"
    fi
done

# Copy public assets
if [ -d "$SOURCE_DIR/public" ]; then
    cp -r "$SOURCE_DIR/public"/* "$STAFF_APP/public/"
fi

print_success "Staff application structure created"

################################################################################
# Step 5: Create Customer Routes
################################################################################

print_header "Step 5: Creating Customer Routes"

cat > "$CUSTOMER_APP/routes/web.php" << 'EOF'
<?php

use App\Core\Router;

$router = new Router();

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Homepage
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'ContactController@index');
$router->post('/contact', 'ContactController@submit', ['CsrfMiddleware']);

// Shop / E-commerce
$router->get('/shop', 'Shop\ShopController@index');
$router->get('/shop/category/{id}', 'Shop\ShopController@category');
$router->get('/shop/product/{id}', 'Shop\ShopController@product');

// Shopping Cart
$router->get('/cart', 'Ecommerce\CartController@index');
$router->post('/cart/add', 'Ecommerce\CartController@add', ['CsrfMiddleware']);
$router->post('/cart/update', 'Ecommerce\CartController@update', ['CsrfMiddleware']);
$router->post('/cart/remove', 'Ecommerce\CartController@remove', ['CsrfMiddleware']);

// Checkout
$router->get('/checkout', 'Ecommerce\CheckoutController@index');
$router->post('/checkout/process', 'Ecommerce\CheckoutController@process', ['CsrfMiddleware']);
$router->get('/checkout/success', 'Ecommerce\CheckoutController@success');

// ============================================================================
// CUSTOMER AUTHENTICATION
// ============================================================================

$router->get('/account/register', 'Customer\CustomerAuthController@showRegister');
$router->post('/account/register', 'Customer\CustomerAuthController@register', ['CsrfMiddleware']);
$router->get('/account/login', 'Customer\CustomerAuthController@showLogin');
$router->post('/account/login', 'Customer\CustomerAuthController@login', ['CsrfMiddleware']);
$router->get('/account/logout', 'Customer\CustomerAuthController@logout');

// Password Reset
$router->get('/account/forgot-password', 'Customer\CustomerAuthController@showForgotPassword');
$router->post('/account/forgot-password', 'Customer\CustomerAuthController@sendResetLink', ['CsrfMiddleware']);
$router->get('/account/reset-password/{token}', 'Customer\CustomerAuthController@showResetPassword');
$router->post('/account/reset-password', 'Customer\CustomerAuthController@resetPassword', ['CsrfMiddleware']);

// ============================================================================
// CUSTOMER PORTAL (Authentication Required)
// ============================================================================

$router->get('/account', 'Customer\CustomerPortalController@dashboard', ['CustomerAuthMiddleware']);
$router->get('/account/profile', 'Customer\CustomerPortalController@profile', ['CustomerAuthMiddleware']);
$router->post('/account/profile', 'Customer\CustomerPortalController@updateProfile', ['CustomerAuthMiddleware', 'CsrfMiddleware']);
$router->get('/account/orders', 'Customer\CustomerPortalController@orders', ['CustomerAuthMiddleware']);
$router->get('/account/orders/{id}', 'Customer\CustomerPortalController@orderDetail', ['CustomerAuthMiddleware']);
$router->get('/account/addresses', 'Customer\CustomerPortalController@addresses', ['CustomerAuthMiddleware']);
$router->post('/account/addresses', 'Customer\CustomerPortalController@addAddress', ['CustomerAuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// CMS PAGES
// ============================================================================

$router->get('/page/{slug}', 'CMS\PageController@show');
$router->get('/blog', 'CMS\BlogController@index');
$router->get('/blog/{slug}', 'CMS\BlogController@show');

// ============================================================================
// INSTALLATION (First Run Only)
// ============================================================================

$router->get('/install', 'Install\InstallController@index');
$router->post('/install/configure', 'Install\InstallController@configure', ['CsrfMiddleware']);
$router->post('/install/database', 'Install\InstallController@database', ['CsrfMiddleware']);
$router->post('/install/admin', 'Install\InstallController@createAdmin', ['CsrfMiddleware']);
$router->get('/install/complete', 'Install\InstallController@complete');

return $router;
EOF

print_success "Customer routes created"

################################################################################
# Step 6: Create Staff Routes
################################################################################

print_header "Step 6: Creating Staff Routes"

cat > "$STAFF_APP/routes/web.php" << 'EOF'
<?php

use App\Core\Router;

$router = new Router();

// ============================================================================
// AUTHENTICATION
// ============================================================================

$router->get('/store/login', 'Auth\AuthController@showLogin');
$router->post('/store/login', 'Auth\AuthController@login', ['CsrfMiddleware']);
$router->get('/store/logout', 'Auth\AuthController@logout');

// Two-Factor Authentication
$router->get('/store/2fa', 'Auth\TwoFactorController@show');
$router->post('/store/2fa', 'Auth\TwoFactorController@verify', ['CsrfMiddleware']);

// ============================================================================
// DASHBOARD (All routes below require authentication)
// ============================================================================

$router->get('/store', 'Admin\DashboardController@index', ['AuthMiddleware']);

// ============================================================================
// POINT OF SALE
// ============================================================================

$router->get('/store/pos', 'POS\POSController@index', ['AuthMiddleware']);
$router->post('/store/pos/transaction', 'POS\POSController@createTransaction', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/pos/receipt/{id}', 'POS\POSController@receipt', ['AuthMiddleware']);

// ============================================================================
// CUSTOMER MANAGEMENT (CRM)
// ============================================================================

$router->get('/store/customers', 'CRM\CustomerController@index', ['AuthMiddleware']);
$router->get('/store/customers/create', 'CRM\CustomerController@create', ['AuthMiddleware']);
$router->post('/store/customers', 'CRM\CustomerController@store', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/customers/{id}', 'CRM\CustomerController@show', ['AuthMiddleware']);
$router->get('/store/customers/{id}/edit', 'CRM\CustomerController@edit', ['AuthMiddleware']);
$router->post('/store/customers/{id}', 'CRM\CustomerController@update', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/store/customers/{id}/delete', 'CRM\CustomerController@delete', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/customers/search', 'CRM\CustomerController@search', ['AuthMiddleware']);

// ============================================================================
// INVENTORY MANAGEMENT
// ============================================================================

// Products
$router->get('/store/products', 'Inventory\ProductController@index', ['AuthMiddleware']);
$router->get('/store/products/create', 'Inventory\ProductController@create', ['AuthMiddleware']);
$router->post('/store/products', 'Inventory\ProductController@store', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/products/{id}', 'Inventory\ProductController@show', ['AuthMiddleware']);
$router->get('/store/products/{id}/edit', 'Inventory\ProductController@edit', ['AuthMiddleware']);
$router->post('/store/products/{id}', 'Inventory\ProductController@update', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/store/products/{id}/delete', 'Inventory\ProductController@delete', ['AuthMiddleware', 'CsrfMiddleware']);

// Categories
$router->get('/store/categories', 'Inventory\CategoryController@index', ['AuthMiddleware']);
$router->post('/store/categories', 'Inventory\CategoryController@store', ['AuthMiddleware', 'CsrfMiddleware']);

// Vendors
$router->get('/store/vendors', 'Inventory\VendorController@index', ['AuthMiddleware']);
$router->post('/store/vendors', 'Inventory\VendorController@store', ['AuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// RENTALS
// ============================================================================

$router->get('/store/rentals', 'Rentals\RentalController@index', ['AuthMiddleware']);
$router->get('/store/rentals/equipment', 'Rentals\RentalController@equipment', ['AuthMiddleware']);
$router->get('/store/rentals/reservations', 'Rentals\RentalController@reservations', ['AuthMiddleware']);
$router->post('/store/rentals/checkout', 'Rentals\RentalController@checkout', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/store/rentals/checkin', 'Rentals\RentalController@checkin', ['AuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// COURSES
// ============================================================================

$router->get('/store/courses', 'Courses\CourseController@index', ['AuthMiddleware']);
$router->get('/store/courses/create', 'Courses\CourseController@create', ['AuthMiddleware']);
$router->post('/store/courses', 'Courses\CourseController@store', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/courses/{id}/enrollments', 'Courses\EnrollmentController@index', ['AuthMiddleware']);
$router->post('/store/courses/{id}/enroll', 'Courses\EnrollmentController@enroll', ['AuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// TRIPS
// ============================================================================

$router->get('/store/trips', 'Trips\TripController@index', ['AuthMiddleware']);
$router->get('/store/trips/create', 'Trips\TripController@create', ['AuthMiddleware']);
$router->post('/store/trips', 'Trips\TripController@store', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/trips/{id}/bookings', 'Trips\TripController@bookings', ['AuthMiddleware']);

// ============================================================================
// WORK ORDERS
// ============================================================================

$router->get('/store/workorders', 'WorkOrders\WorkOrderController@index', ['AuthMiddleware']);
$router->get('/store/workorders/create', 'WorkOrders\WorkOrderController@create', ['AuthMiddleware']);
$router->post('/store/workorders', 'WorkOrders\WorkOrderController@store', ['AuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// AIR FILLS
// ============================================================================

$router->get('/store/air-fills', 'AirFills\AirFillController@index', ['AuthMiddleware']);
$router->post('/store/air-fills', 'AirFills\AirFillController@store', ['AuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// REPORTS
// ============================================================================

$router->get('/store/reports', 'Reports\ReportController@index', ['AuthMiddleware']);
$router->get('/store/reports/sales', 'Reports\SalesReportController@index', ['AuthMiddleware']);
$router->get('/store/reports/inventory', 'Reports\InventoryReportController@index', ['AuthMiddleware']);
$router->get('/store/reports/customers', 'Reports\CustomerReportController@index', ['AuthMiddleware']);
$router->get('/store/reports/export', 'Reports\ReportController@export', ['AuthMiddleware']);

// ============================================================================
// MARKETING
// ============================================================================

$router->get('/store/marketing/loyalty', 'Marketing\LoyaltyController@index', ['AuthMiddleware']);
$router->get('/store/marketing/coupons', 'Marketing\CouponController@index', ['AuthMiddleware']);
$router->get('/store/marketing/campaigns', 'Marketing\CampaignController@index', ['AuthMiddleware']);
$router->get('/store/marketing/referrals', 'Marketing\ReferralController@index', ['AuthMiddleware']);

// ============================================================================
// STAFF MANAGEMENT
// ============================================================================

$router->get('/store/staff', 'Staff\StaffController@index', ['AuthMiddleware']);
$router->get('/store/staff/schedules', 'Staff\ScheduleController@index', ['AuthMiddleware']);
$router->get('/store/staff/timeclock', 'Staff\TimeClockController@index', ['AuthMiddleware']);
$router->get('/store/staff/commissions', 'Staff\CommissionController@index', ['AuthMiddleware']);

// ============================================================================
// STOREFRONT CONFIGURATION
// ============================================================================

$router->get('/store/storefront', 'Admin\Storefront\StorefrontController@index', ['AuthMiddleware']);
$router->post('/store/storefront/theme', 'Admin\Storefront\ThemeController@update', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/store/storefront/homepage', 'Admin\Storefront\HomepageController@update', ['AuthMiddleware', 'CsrfMiddleware']);

// ============================================================================
// ADMINISTRATION
// ============================================================================

$router->get('/store/admin/settings', 'Admin\SettingsController@index', ['AuthMiddleware']);
$router->post('/store/admin/settings', 'Admin\SettingsController@update', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/admin/users', 'Admin\UserController@index', ['AuthMiddleware']);
$router->post('/store/admin/users', 'Admin\UserController@store', ['AuthMiddleware', 'CsrfMiddleware']);
$router->get('/store/admin/roles', 'Admin\RoleController@index', ['AuthMiddleware']);

// ============================================================================
// INTEGRATIONS
// ============================================================================

$router->get('/store/integrations/wave', 'Integrations\WaveController@index', ['AuthMiddleware']);
$router->get('/store/integrations/quickbooks', 'Integrations\QuickBooksController@index', ['AuthMiddleware']);
$router->get('/store/integrations/google', 'Integrations\GoogleWorkspaceController@index', ['AuthMiddleware']);

// ============================================================================
// API ROUTES
// ============================================================================

$router->get('/api/v1/customers', 'API\CustomerAPIController@index', ['ApiAuthMiddleware']);
$router->get('/api/v1/products', 'API\ProductAPIController@index', ['ApiAuthMiddleware']);
$router->get('/api/v1/orders', 'API\OrderAPIController@index', ['ApiAuthMiddleware']);

return $router;
EOF

print_success "Staff routes created"

################################################################################
# Step 7: Create Environment Files
################################################################################

print_header "Step 7: Creating Environment Configuration Files"

# Customer .env
cat > "$CUSTOMER_APP/.env.example" << 'EOF'
# Application
APP_NAME="Your Dive Shop"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=
APP_BASE_PATH=

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=file

# Email
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Payment Gateways
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=

# File Upload
MAX_UPLOAD_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf
EOF

# Staff .env
cat > "$STAFF_APP/.env.example" << 'EOF'
# Application
APP_NAME="Your Dive Shop - Staff Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/store
APP_KEY=
APP_BASE_PATH=/store

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=nautilus_user
DB_PASSWORD=

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=480

# Cache
CACHE_DRIVER=file

# Email
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Integrations
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://yourdomain.com/store/auth/google/callback

# SMS
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_PHONE_NUMBER=
EOF

print_success "Environment files created"

################################################################################
# Step 8: Set Permissions
################################################################################

print_header "Step 8: Setting File Permissions"

chmod -R 755 "$CUSTOMER_APP"
chmod -R 755 "$STAFF_APP"

# Storage directories need to be writable
if [ -d "$CUSTOMER_APP/storage" ]; then
    chmod -R 775 "$CUSTOMER_APP/storage"
fi

if [ -d "$STAFF_APP/storage" ]; then
    chmod -R 775 "$STAFF_APP/storage"
fi

print_success "Permissions set"

################################################################################
# Step 9: Create README files
################################################################################

print_header "Step 9: Creating Application README Files"

# Customer README
cat > "$CUSTOMER_APP/README.md" << 'EOF'
# Nautilus Customer Application

Public-facing e-commerce storefront for your dive shop.

## Features

- Product catalog and online shopping
- Customer account portal
- Shopping cart and checkout
- Contact forms
- CMS pages and blog

## Installation

See the main deployment guide: `/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md`

## Routes

- `/` - Homepage
- `/shop` - Product catalog
- `/account` - Customer portal
- `/cart` - Shopping cart
- `/checkout` - Checkout process

## Environment

Copy `.env.example` to `.env` and configure your settings.

**Important**: Set `APP_BASE_PATH=` (empty)
EOF

# Staff README
cat > "$STAFF_APP/README.md" << 'EOF'
# Nautilus Staff Application

Internal management system for dive shop operations.

## Features

- Point of Sale (POS)
- Customer Relationship Management (CRM)
- Inventory Management
- Equipment Rentals
- Training Courses
- Dive Trips
- Reports & Analytics
- Staff Management
- Storefront Configuration

## Installation

See the main deployment guide: `/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md`

## Routes

All routes are prefixed with `/store/`:

- `/store/` - Dashboard
- `/store/pos` - Point of Sale
- `/store/customers` - CRM
- `/store/products` - Inventory
- `/store/reports` - Analytics

## Environment

Copy `.env.example` to `.env` and configure your settings.

**Important**: Set `APP_BASE_PATH=/store`

## Security

All routes require staff authentication and support role-based access control.
EOF

print_success "README files created"

################################################################################
# Step 10: Create Documentation
################################################################################

print_header "Step 10: Creating Documentation Structure"

mkdir -p "$CUSTOMER_APP/docs"
mkdir -p "$STAFF_APP/docs"

# Copy main documentation if exists
if [ -f "$SOURCE_DIR/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md" ]; then
    cp "$SOURCE_DIR/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md" "$CUSTOMER_APP/docs/"
    cp "$SOURCE_DIR/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md" "$STAFF_APP/docs/"
fi

print_success "Documentation structure created"

################################################################################
# Final Summary
################################################################################

print_header "Application Split Complete!"

echo ""
echo -e "${GREEN}✓ Customer Application:${NC} $CUSTOMER_APP"
echo -e "${GREEN}✓ Staff Application:${NC} $STAFF_APP"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo ""
echo "1. Install dependencies:"
echo "   cd $CUSTOMER_APP && composer install"
echo "   cd $STAFF_APP && composer install"
echo ""
echo "2. Configure environment files:"
echo "   cp $CUSTOMER_APP/.env.example $CUSTOMER_APP/.env"
echo "   cp $STAFF_APP/.env.example $STAFF_APP/.env"
echo ""
echo "3. Create database:"
echo "   mysql -u root -p -e \"CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\""
echo ""
echo "4. Run migrations (once from either app):"
echo "   cd $CUSTOMER_APP && php scripts/migrate.php"
echo ""
echo "5. Deploy to web server (see docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)"
echo ""
echo -e "${BLUE}For detailed instructions, see:${NC}"
echo "   $CUSTOMER_APP/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md"
echo ""

exit 0
