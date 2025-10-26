#!/bin/bash

###############################################################################
# Nautilus - Application Split Script
#
# This script splits the monolithic application into two separate applications:
# 1. nautilus-storefront (External - Customer facing)
# 2. nautilus-store (Internal - Staff management with role-based access)
#
# Both applications share the same database but are completely separate codebases.
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SOURCE_DIR="/home/wrnash1/Developer/nautilus"
TARGET_BASE="/home/wrnash1/Developer"
STOREFRONT_DIR="$TARGET_BASE/nautilus-storefront"
STORE_DIR="$TARGET_BASE/nautilus-store"

echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║     Nautilus - Application Split Script                ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Function to print section header
print_section() {
    echo -e "\n${YELLOW}═══ $1 ═══${NC}"
}

# Function to create directory structure
create_base_structure() {
    local APP_DIR=$1
    local APP_NAME=$2

    print_section "Creating base structure for $APP_NAME"

    mkdir -p "$APP_DIR"/{app,public,routes,storage,database,tests,docs}
    mkdir -p "$APP_DIR"/app/{Controllers,Core,Models,Services,Middleware,Views,Languages}
    mkdir -p "$APP_DIR"/public/{assets,uploads}
    mkdir -p "$APP_DIR"/public/assets/{css,js,images}
    mkdir -p "$APP_DIR"/storage/{logs,cache,sessions,backups,exports,waivers}
    mkdir -p "$APP_DIR"/database/{migrations,seeds}

    echo -e "${GREEN}✓ Base structure created${NC}"
}

# Function to copy shared/core files (both apps need these)
copy_shared_files() {
    local APP_DIR=$1
    local APP_NAME=$2

    print_section "Copying shared files to $APP_NAME"

    # Core framework files
    cp -r "$SOURCE_DIR"/app/Core/* "$APP_DIR"/app/Core/

    # Models (both apps need access to all database tables)
    cp -r "$SOURCE_DIR"/app/Models/* "$APP_DIR"/app/Models/ 2>/dev/null || true

    # Services (business logic used by both apps)
    cp -r "$SOURCE_DIR"/app/Services/* "$APP_DIR"/app/Services/

    # Middleware
    cp -r "$SOURCE_DIR"/app/Middleware/* "$APP_DIR"/app/Middleware/

    # Languages
    cp -r "$SOURCE_DIR"/app/Languages/* "$APP_DIR"/app/Languages/

    # Helper functions
    cp "$SOURCE_DIR"/app/helpers.php "$APP_DIR"/app/helpers.php 2>/dev/null || true

    # Database migrations and seeds
    cp -r "$SOURCE_DIR"/database/migrations/* "$APP_DIR"/database/migrations/
    cp -r "$SOURCE_DIR"/database/seeds/* "$APP_DIR"/database/seeds/ 2>/dev/null || true

    # Public assets (CSS, JS, images)
    cp -r "$SOURCE_DIR"/public/assets/* "$APP_DIR"/public/assets/

    # Composer files
    cp "$SOURCE_DIR"/composer.json "$APP_DIR"/
    cp "$SOURCE_DIR"/composer.lock "$APP_DIR"/ 2>/dev/null || true

    # Documentation
    cp "$SOURCE_DIR"/*.md "$APP_DIR"/docs/ 2>/dev/null || true

    echo -e "${GREEN}✓ Shared files copied${NC}"
}

###############################################################################
# STOREFRONT (External) Application Setup
###############################################################################

setup_storefront() {
    print_section "Setting up STOREFRONT (External) Application"

    create_base_structure "$STOREFRONT_DIR" "Storefront"
    copy_shared_files "$STOREFRONT_DIR" "Storefront"

    # Copy EXTERNAL controllers only
    print_section "Copying External controllers"
    cp "$SOURCE_DIR"/app/Controllers/HomeController.php "$STOREFRONT_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Shop "$STOREFRONT_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Customer "$STOREFRONT_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Install "$STOREFRONT_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Ecommerce "$STOREFRONT_DIR"/app/Controllers/ 2>/dev/null || true

    # Copy EXTERNAL views only
    print_section "Copying External views"
    mkdir -p "$STOREFRONT_DIR"/app/Views/{storefront,shop,customer,install}
    cp -r "$SOURCE_DIR"/app/Views/storefront/* "$STOREFRONT_DIR"/app/Views/storefront/
    cp -r "$SOURCE_DIR"/app/Views/shop/* "$STOREFRONT_DIR"/app/Views/shop/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/customer/* "$STOREFRONT_DIR"/app/Views/customer/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/install/* "$STOREFRONT_DIR"/app/Views/install/

    echo -e "${GREEN}✓ Storefront application setup complete${NC}"
}

###############################################################################
# STORE (Internal) Application Setup
###############################################################################

setup_store() {
    print_section "Setting up STORE (Internal) Application"

    create_base_structure "$STORE_DIR" "Store"
    copy_shared_files "$STORE_DIR" "Store"

    # Copy INTERNAL controllers only
    print_section "Copying Internal controllers"
    cp -r "$SOURCE_DIR"/app/Controllers/Admin "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Auth "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/POS "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/CRM "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Inventory "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Rentals "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/AirFills "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Courses "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Trips "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/WorkOrders "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Reports "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Staff "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/Marketing "$STORE_DIR"/app/Controllers/
    cp -r "$SOURCE_DIR"/app/Controllers/CMS "$STORE_DIR"/app/Controllers/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Controllers/Integrations "$STORE_DIR"/app/Controllers/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Controllers/API "$STORE_DIR"/app/Controllers/ 2>/dev/null || true

    # Copy INTERNAL views only
    print_section "Copying Internal views"
    mkdir -p "$STORE_DIR"/app/Views/{auth,dashboard,pos,customers,products,categories,vendors}
    mkdir -p "$STORE_DIR"/app/Views/{rentals,air-fills,courses,trips,workorders,reports,staff}
    mkdir -p "$STORE_DIR"/app/Views/{marketing,cms,settings,integrations,waivers}

    cp -r "$SOURCE_DIR"/app/Views/admin/* "$STORE_DIR"/app/Views/dashboard/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/auth/* "$STORE_DIR"/app/Views/auth/
    cp -r "$SOURCE_DIR"/app/Views/pos/* "$STORE_DIR"/app/Views/pos/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/customers/* "$STORE_DIR"/app/Views/customers/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/products/* "$STORE_DIR"/app/Views/products/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/categories/* "$STORE_DIR"/app/Views/categories/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/vendors/* "$STORE_DIR"/app/Views/vendors/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/rentals/* "$STORE_DIR"/app/Views/rentals/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/air-fills/* "$STORE_DIR"/app/Views/air-fills/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/courses/* "$STORE_DIR"/app/Views/courses/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/trips/* "$STORE_DIR"/app/Views/trips/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/workorders/* "$STORE_DIR"/app/Views/workorders/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/reports/* "$STORE_DIR"/app/Views/reports/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/staff/* "$STORE_DIR"/app/Views/staff/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/marketing/* "$STORE_DIR"/app/Views/marketing/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/cms/* "$STORE_DIR"/app/Views/cms/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/settings/* "$STORE_DIR"/app/Views/settings/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/integrations/* "$STORE_DIR"/app/Views/integrations/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/waivers/* "$STORE_DIR"/app/Views/waivers/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/dive_sites/* "$STORE_DIR"/app/Views/dive_sites/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/orders/* "$STORE_DIR"/app/Views/orders/ 2>/dev/null || true

    # Copy layout/component views
    cp -r "$SOURCE_DIR"/app/Views/layouts/* "$STORE_DIR"/app/Views/layouts/ 2>/dev/null || true
    cp -r "$SOURCE_DIR"/app/Views/components/* "$STORE_DIR"/app/Views/components/ 2>/dev/null || true

    echo -e "${GREEN}✓ Store application setup complete${NC}"
}

###############################################################################
# Create Route Files
###############################################################################

create_storefront_routes() {
    print_section "Creating Storefront routes"

    cat > "$STOREFRONT_DIR"/routes/web.php << 'EOF'
<?php

use App\Core\Router;
use App\Middleware\CsrfMiddleware;

$router = new Router();

// ============================================================================
// INSTALLATION ROUTES
// ============================================================================
$router->get('/install', 'Install\InstallController@index');
$router->get('/install/configure', 'Install\InstallController@configure');
$router->post('/install/test-database', 'Install\InstallController@testDatabase');
$router->post('/install/process', 'Install\InstallController@install');
$router->get('/install/progress', 'Install\InstallController@progress');
$router->get('/install/complete', 'Install\InstallController@complete');

// ============================================================================
// PUBLIC STOREFRONT ROUTES
// ============================================================================

// Homepage
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@submitContact', [CsrfMiddleware::class]);

// Online Shop
$router->get('/shop', 'Shop\ShopController@index');
$router->get('/shop/product/{id}', 'Shop\ShopController@productDetail');
$router->post('/shop/cart/add', 'Shop\ShopController@addToCart', [CsrfMiddleware::class]);
$router->get('/shop/cart', 'Shop\ShopController@cart');
$router->post('/shop/cart/update', 'Shop\ShopController@updateCart', [CsrfMiddleware::class]);
$router->get('/shop/checkout', 'Shop\ShopController@checkout');
$router->post('/shop/checkout', 'Shop\ShopController@processCheckout', [CsrfMiddleware::class]);

// Customer Portal
$router->get('/account/register', 'Customer\CustomerAuthController@showRegister');
$router->post('/account/register', 'Customer\CustomerAuthController@register', [CsrfMiddleware::class]);
$router->get('/account/login', 'Customer\CustomerAuthController@showLogin');
$router->post('/account/login', 'Customer\CustomerAuthController@login', [CsrfMiddleware::class]);
$router->post('/account/logout', 'Customer\CustomerAuthController@logout');
$router->get('/account/dashboard', 'Customer\CustomerPortalController@dashboard');
$router->get('/account/orders', 'Customer\CustomerPortalController@orders');
$router->get('/account/orders/{id}', 'Customer\CustomerPortalController@orderDetail');

return $router;
EOF

    echo -e "${GREEN}✓ Storefront routes created${NC}"
}

create_store_routes() {
    print_section "Creating Store routes"

    cat > "$STORE_DIR"/routes/web.php << 'EOF'
<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$router = new Router();

// ============================================================================
// AUTHENTICATION
// ============================================================================
$router->get('/store/login', 'Auth\AuthController@showLogin');
$router->post('/store/login', 'Auth\AuthController@login');
$router->post('/store/logout', 'Auth\AuthController@logout', [AuthMiddleware::class]);

// ============================================================================
// DASHBOARD
// ============================================================================
$router->get('/store', 'Admin\DashboardController@index', [AuthMiddleware::class]);
$router->get('/store/dashboard', 'Admin\DashboardController@index', [AuthMiddleware::class]);

// ============================================================================
// POS (Point of Sale)
// ============================================================================
$router->get('/store/pos', 'POS\TransactionController@index', [AuthMiddleware::class]);
$router->get('/store/pos/search', 'POS\TransactionController@searchProducts', [AuthMiddleware::class]);
$router->post('/store/pos/checkout', 'POS\TransactionController@checkout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/pos/receipt/{id}', 'POS\TransactionController@receipt', [AuthMiddleware::class]);

// ============================================================================
// CRM (Customer Relationship Management)
// ============================================================================
$router->get('/store/customers', 'CRM\CustomerController@index', [AuthMiddleware::class]);
$router->get('/store/customers/create', 'CRM\CustomerController@create', [AuthMiddleware::class]);
$router->post('/store/customers', 'CRM\CustomerController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/customers/search', 'CRM\CustomerController@search', [AuthMiddleware::class]);
$router->get('/store/customers/export', 'CRM\CustomerController@exportCsv', [AuthMiddleware::class]);
$router->get('/store/customers/{id}', 'CRM\CustomerController@show', [AuthMiddleware::class]);
$router->get('/store/customers/{id}/edit', 'CRM\CustomerController@edit', [AuthMiddleware::class]);
$router->post('/store/customers/{id}', 'CRM\CustomerController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/delete', 'CRM\CustomerController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// INVENTORY MANAGEMENT
// ============================================================================

// Products
$router->get('/store/products', 'Inventory\ProductController@index', [AuthMiddleware::class]);
$router->get('/store/products/create', 'Inventory\ProductController@create', [AuthMiddleware::class]);
$router->post('/store/products', 'Inventory\ProductController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/products/search', 'Inventory\ProductController@search', [AuthMiddleware::class]);
$router->get('/store/products/{id}', 'Inventory\ProductController@show', [AuthMiddleware::class]);
$router->get('/store/products/{id}/edit', 'Inventory\ProductController@edit', [AuthMiddleware::class]);
$router->post('/store/products/{id}', 'Inventory\ProductController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/products/{id}/delete', 'Inventory\ProductController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/products/{id}/adjust-stock', 'Inventory\ProductController@adjustStock', [AuthMiddleware::class, CsrfMiddleware::class]);

// Categories
$router->get('/store/categories', 'Inventory\CategoryController@index', [AuthMiddleware::class]);
$router->get('/store/categories/create', 'Inventory\CategoryController@create', [AuthMiddleware::class]);
$router->post('/store/categories', 'Inventory\CategoryController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/categories/{id}/edit', 'Inventory\CategoryController@edit', [AuthMiddleware::class]);
$router->post('/store/categories/{id}', 'Inventory\CategoryController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/categories/{id}/delete', 'Inventory\CategoryController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// Vendors
$router->get('/store/vendors', 'Inventory\VendorController@index', [AuthMiddleware::class]);
$router->get('/store/vendors/create', 'Inventory\VendorController@create', [AuthMiddleware::class]);
$router->post('/store/vendors', 'Inventory\VendorController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/vendors/{id}', 'Inventory\VendorController@show', [AuthMiddleware::class]);
$router->get('/store/vendors/{id}/edit', 'Inventory\VendorController@edit', [AuthMiddleware::class]);
$router->post('/store/vendors/{id}', 'Inventory\VendorController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/vendors/{id}/delete', 'Inventory\VendorController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// RENTALS
// ============================================================================
$router->get('/store/rentals', 'Rentals\RentalController@index', [AuthMiddleware::class]);
$router->get('/store/rentals/equipment/create', 'Rentals\RentalController@createEquipment', [AuthMiddleware::class]);
$router->post('/store/rentals/equipment', 'Rentals\RentalController@storeEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/rentals/equipment/{id}', 'Rentals\RentalController@showEquipment', [AuthMiddleware::class]);
$router->get('/store/rentals/equipment/{id}/edit', 'Rentals\RentalController@editEquipment', [AuthMiddleware::class]);
$router->post('/store/rentals/equipment/{id}', 'Rentals\RentalController@updateEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/rentals/equipment/{id}/delete', 'Rentals\RentalController@deleteEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/rentals/reservations', 'Rentals\RentalController@reservations', [AuthMiddleware::class]);
$router->get('/store/rentals/reservations/create', 'Rentals\RentalController@createReservation', [AuthMiddleware::class]);
$router->get('/store/rentals/reservations/{id}', 'Rentals\RentalController@showReservation', [AuthMiddleware::class]);
$router->post('/store/rentals/reservations/{id}/checkout', 'Rentals\RentalController@checkout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/rentals/reservations/{id}/checkin', 'Rentals\RentalController@checkin', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// AIR FILLS
// ============================================================================
$router->get('/store/air-fills', 'AirFills\AirFillController@index', [AuthMiddleware::class]);
$router->get('/store/air-fills/create', 'AirFills\AirFillController@create', [AuthMiddleware::class]);
$router->post('/store/air-fills', 'AirFills\AirFillController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/air-fills/quick-fill', 'AirFills\AirFillController@quickFill', [AuthMiddleware::class]);
$router->post('/store/air-fills/quick-fill', 'AirFills\AirFillController@processQuickFill', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/air-fills/{id}', 'AirFills\AirFillController@show', [AuthMiddleware::class]);
$router->get('/store/air-fills/{id}/edit', 'AirFills\AirFillController@edit', [AuthMiddleware::class]);
$router->post('/store/air-fills/{id}', 'AirFills\AirFillController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// COURSES
// ============================================================================
$router->get('/store/courses', 'Courses\CourseController@index', [AuthMiddleware::class]);
$router->get('/store/courses/create', 'Courses\CourseController@create', [AuthMiddleware::class]);
$router->post('/store/courses', 'Courses\CourseController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/courses/schedules', 'Courses\CourseController@schedules', [AuthMiddleware::class]);
$router->get('/store/courses/schedules/create', 'Courses\CourseController@createSchedule', [AuthMiddleware::class]);
$router->post('/store/courses/schedules', 'Courses\CourseController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/courses/schedules/{id}', 'Courses\CourseController@showSchedule', [AuthMiddleware::class]);
$router->get('/store/courses/enrollments', 'Courses\CourseController@enrollments', [AuthMiddleware::class]);
$router->get('/store/courses/enrollments/{id}', 'Courses\CourseController@showEnrollment', [AuthMiddleware::class]);
$router->get('/store/courses/{id}', 'Courses\CourseController@show', [AuthMiddleware::class]);
$router->get('/store/courses/{id}/edit', 'Courses\CourseController@edit', [AuthMiddleware::class]);
$router->post('/store/courses/{id}', 'Courses\CourseController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// TRIPS
// ============================================================================
$router->get('/store/trips', 'Trips\TripController@index', [AuthMiddleware::class]);
$router->get('/store/trips/create', 'Trips\TripController@create', [AuthMiddleware::class]);
$router->post('/store/trips', 'Trips\TripController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/schedules', 'Trips\TripController@schedules', [AuthMiddleware::class]);
$router->get('/store/trips/schedules/create', 'Trips\TripController@createSchedule', [AuthMiddleware::class]);
$router->post('/store/trips/schedules', 'Trips\TripController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/schedules/{id}', 'Trips\TripController@showSchedule', [AuthMiddleware::class]);
$router->get('/store/trips/bookings', 'Trips\TripController@bookings', [AuthMiddleware::class]);
$router->get('/store/trips/bookings/create', 'Trips\TripController@createBooking', [AuthMiddleware::class]);
$router->post('/store/trips/bookings', 'Trips\TripController@storeBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/bookings/{id}', 'Trips\TripController@showBooking', [AuthMiddleware::class]);
$router->get('/store/trips/{id}', 'Trips\TripController@show', [AuthMiddleware::class]);
$router->get('/store/trips/{id}/edit', 'Trips\TripController@edit', [AuthMiddleware::class]);
$router->post('/store/trips/{id}', 'Trips\TripController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// WORK ORDERS
// ============================================================================
$router->get('/store/workorders', 'WorkOrders\WorkOrderController@index', [AuthMiddleware::class]);
$router->get('/store/workorders/create', 'WorkOrders\WorkOrderController@create', [AuthMiddleware::class]);
$router->post('/store/workorders', 'WorkOrders\WorkOrderController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/workorders/{id}', 'WorkOrders\WorkOrderController@show', [AuthMiddleware::class]);
$router->get('/store/workorders/{id}/edit', 'WorkOrders\WorkOrderController@edit', [AuthMiddleware::class]);
$router->post('/store/workorders/{id}', 'WorkOrders\WorkOrderController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// REPORTS
// ============================================================================
$router->get('/store/reports/sales', 'Reports\SalesReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/sales/export', 'Reports\SalesReportController@exportCsv', [AuthMiddleware::class]);
$router->get('/store/reports/customers', 'Reports\CustomerReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/customers/export', 'Reports\CustomerReportController@exportCsv', [AuthMiddleware::class]);
$router->get('/store/reports/products', 'Reports\ProductReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/products/export', 'Reports\ProductReportController@exportCsv', [AuthMiddleware::class]);
$router->get('/store/reports/inventory', 'Inventory\ReportController@inventory', [AuthMiddleware::class]);
$router->get('/store/reports/low-stock', 'Inventory\ReportController@lowStock', [AuthMiddleware::class]);

// ============================================================================
// STOREFRONT CONFIGURATION (Admin manages external storefront from here)
// ============================================================================
$router->get('/store/storefront', 'Admin\Storefront\StorefrontController@index', [AuthMiddleware::class]);
$router->get('/store/storefront/theme-designer', 'Admin\Storefront\StorefrontController@themeDesigner', [AuthMiddleware::class]);
$router->post('/store/storefront/theme', 'Admin\Storefront\StorefrontController@updateTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/storefront/homepage-builder', 'Admin\Storefront\StorefrontController@homepageBuilder', [AuthMiddleware::class]);
$router->get('/store/storefront/settings', 'Admin\Storefront\StorefrontController@settings', [AuthMiddleware::class]);
$router->post('/store/storefront/settings', 'Admin\Storefront\StorefrontController@updateSettings', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// STAFF MANAGEMENT (Role-based access required)
// ============================================================================
$router->get('/store/staff', 'Staff\StaffController@index', [AuthMiddleware::class]);
$router->get('/store/staff/create', 'Staff\StaffController@create', [AuthMiddleware::class]);
$router->post('/store/staff', 'Staff\StaffController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/staff/{id}', 'Staff\StaffController@show', [AuthMiddleware::class]);
$router->get('/store/staff/{id}/edit', 'Staff\StaffController@edit', [AuthMiddleware::class]);
$router->post('/store/staff/{id}', 'Staff\StaffController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

return $router;
EOF

    echo -e "${GREEN}✓ Store routes created${NC}"
}

###############################################################################
# Create Index.php files
###############################################################################

create_storefront_index() {
    print_section "Creating Storefront index.php"

    cat > "$STOREFRONT_DIR"/public/index.php << 'EOF'
<?php

/**
 * Nautilus Storefront - Customer Facing Application
 * Entry Point
 */

// Load environment variables
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set error reporting based on environment
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/New_York');

// Register autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load helper functions
require __DIR__ . '/../app/helpers.php';

// Initialize error handler
$errorHandler = new App\Core\ErrorHandler();
$errorHandler->register();

// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Dispatch request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);
EOF

    echo -e "${GREEN}✓ Storefront index.php created${NC}"
}

create_store_index() {
    print_section "Creating Store index.php"

    cat > "$STORE_DIR"/public/index.php << 'EOF'
<?php

/**
 * Nautilus Store - Internal Staff Management Application
 * Entry Point
 */

// Load environment variables
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Set error reporting based on environment
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/New_York');

// Register autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load helper functions
require __DIR__ . '/../app/helpers.php';

// Initialize error handler
$errorHandler = new App\Core\ErrorHandler();
$errorHandler->register();

// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Dispatch request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);
EOF

    echo -e "${GREEN}✓ Store index.php created${NC}"
}

###############################################################################
# Create .env files
###############################################################################

create_env_files() {
    print_section "Creating environment files"

    # Storefront .env
    cp "$SOURCE_DIR"/.env.example "$STOREFRONT_DIR"/.env 2>/dev/null || cp "$SOURCE_DIR"/.env "$STOREFRONT_DIR"/.env
    sed -i 's/APP_NAME=.*/APP_NAME="Nautilus Storefront"/' "$STOREFRONT_DIR"/.env

    # Store .env
    cp "$SOURCE_DIR"/.env.example "$STORE_DIR"/.env 2>/dev/null || cp "$SOURCE_DIR"/.env "$STORE_DIR"/.env
    sed -i 's/APP_NAME=.*/APP_NAME="Nautilus Store Management"/' "$STORE_DIR"/.env

    echo -e "${GREEN}✓ Environment files created${NC}"
}

###############################################################################
# Create .htaccess files
###############################################################################

create_htaccess_files() {
    print_section "Creating .htaccess files"

    # Storefront .htaccess
    cat > "$STOREFRONT_DIR"/public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirect to HTTPS (uncomment in production)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]

    # Redirect all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
EOF

    # Store .htaccess
    cat > "$STORE_DIR"/public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /store/

    # Redirect to HTTPS (uncomment in production)
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}/store/$1 [R=301,L]

    # Redirect all requests to index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
EOF

    echo -e "${GREEN}✓ .htaccess files created${NC}"
}

###############################################################################
# Main Execution
###############################################################################

main() {
    echo -e "${YELLOW}This script will split the application into two separate applications:${NC}"
    echo -e "  1. ${GREEN}nautilus-storefront${NC} - Customer facing (external)"
    echo -e "  2. ${GREEN}nautilus-store${NC} - Staff management (internal)"
    echo ""
    echo -e "${YELLOW}Target directories:${NC}"
    echo -e "  - $STOREFRONT_DIR"
    echo -e "  - $STORE_DIR"
    echo ""
    read -p "Continue? (y/n) " -n 1 -r
    echo ""

    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${RED}Aborted${NC}"
        exit 1
    fi

    # Check if target directories already exist
    if [ -d "$STOREFRONT_DIR" ] || [ -d "$STORE_DIR" ]; then
        echo -e "${RED}Warning: Target directories already exist!${NC}"
        read -p "Remove existing directories and continue? (y/n) " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm -rf "$STOREFRONT_DIR" "$STORE_DIR"
        else
            echo -e "${RED}Aborted${NC}"
            exit 1
        fi
    fi

    # Setup applications
    setup_storefront
    setup_store

    # Create route files
    create_storefront_routes
    create_store_routes

    # Create index.php files
    create_storefront_index
    create_store_index

    # Create environment files
    create_env_files

    # Create .htaccess files
    create_htaccess_files

    # Summary
    echo ""
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║              Application Split Complete!                  ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${YELLOW}Next Steps:${NC}"
    echo ""
    echo -e "1. Install composer dependencies in both applications:"
    echo -e "   ${GREEN}cd $STOREFRONT_DIR && composer install${NC}"
    echo -e "   ${GREEN}cd $STORE_DIR && composer install${NC}"
    echo ""
    echo -e "2. Configure your .env files:"
    echo -e "   ${GREEN}$STOREFRONT_DIR/.env${NC}"
    echo -e "   ${GREEN}$STORE_DIR/.env${NC}"
    echo ""
    echo -e "3. Set up web server (Apache/Nginx) configuration:"
    echo -e "   - Storefront: Point to ${GREEN}$STOREFRONT_DIR/public${NC}"
    echo -e "   - Store: Create alias /store → ${GREEN}$STORE_DIR/public${NC}"
    echo ""
    echo -e "4. Set proper permissions:"
    echo -e "   ${GREEN}sudo chown -R www-data:www-data $STOREFRONT_DIR${NC}"
    echo -e "   ${GREEN}sudo chown -R www-data:www-data $STORE_DIR${NC}"
    echo -e "   ${GREEN}sudo chmod -R 755 $STOREFRONT_DIR${NC}"
    echo -e "   ${GREEN}sudo chmod -R 755 $STORE_DIR${NC}"
    echo ""
    echo -e "5. Both applications share the same database - no migration needed"
    echo ""
    echo -e "${YELLOW}Testing URLs:${NC}"
    echo -e "   External: ${GREEN}http://yourdomain.com/${NC}"
    echo -e "   Internal: ${GREEN}http://yourdomain.com/store/${NC}"
    echo ""
}

# Run main
main
