<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$router = new Router();

// Installation Routes (no middleware - accessible before installation)
$router->get('/install', 'Install\InstallController@index');
$router->get('/install/configure', 'Install\InstallController@configure');
$router->post('/install/test-database', 'Install\InstallController@testDatabase');
$router->post('/install/process', 'Install\InstallController@install');
$router->get('/install/progress', 'Install\InstallController@progress');
$router->get('/install/complete', 'Install\InstallController@complete');

// Deployment Route (for running migrations and seeders)
$router->get('/deploy', 'Install\DeploymentController@run');

// ============================================================================
// PUBLIC STOREFRONT ROUTES (Customer-facing website)
// ============================================================================

// Homepage - Public storefront (NO SIDEBAR - Public facing)
$router->get('/', 'PublicController@index');
$router->get('/about', 'PublicController@about');
$router->get('/contact', 'PublicController@contact');
$router->post('/contact', 'PublicController@submitContact', [CsrfMiddleware::class]);

// Shop & Products (Public)
$router->get('/shop', 'PublicController@shop');
$router->get('/product/{id}', 'PublicController@productDetail');

// Courses & Trips (Public)
$router->get('/courses', 'PublicController@courses');
$router->get('/course/{id}', 'PublicController@courseDetail');
$router->get('/trips', 'PublicController@trips');
$router->get('/trip/{id}', 'PublicController@tripDetail');

// Shopping Cart (Public)
$router->get('/cart', 'Storefront\ModernStorefrontController@cart');
$router->get('/checkout', 'Storefront\ModernStorefrontController@checkout');
$router->post('/checkout/process', 'Storefront\ModernStorefrontController@processCheckout');
$router->get('/checkout/success', 'Storefront\ModernStorefrontController@checkoutSuccess');

// API Endpoints - Cart (Public)
$router->post('/api/cart/add', 'API\CartController@add');
$router->get('/api/cart/count', 'API\CartController@count');
$router->get('/api/cart', 'API\CartController@get');
$router->post('/api/cart/update', 'API\CartController@update');
$router->post('/api/cart/remove', 'API\CartController@remove');
$router->post('/api/cart/clear', 'API\CartController@clear');

// ============================================================================
// STORE/STAFF ROUTES (Employee backend - Internal Application)
// ============================================================================

// Convenience redirects for common URLs
$router->get('/login', function() {
    redirect('/store/login');
});
$router->get('/logout', function() {
    redirect('/store/logout');
});
$router->get('/dashboard', function() {
    redirect('/store');
});
$router->get('/waivers', function() {
    redirect('/store/waivers');
});

// Store Dashboard & Auth
$router->get('/store', 'Admin\DashboardController@index', [AuthMiddleware::class]);
$router->get('/store/login', 'Auth\AuthController@showLogin');
$router->post('/store/login', 'Auth\AuthController@login');
$router->post('/store/logout', 'Auth\AuthController@logout', [AuthMiddleware::class]);

// Customer Portal - Requires customer authentication
// If not logged in, redirects to customer login page
$router->get('/portal', 'Portal\PortalController@index', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->get('/portal/certifications', 'Portal\PortalController@certifications', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->get('/portal/bookings', 'Portal\PortalController@bookings', [\App\Middleware\CustomerAuthMiddleware::class]);

// Storefront Configuration (Manager only)
$router->get('/store/storefront', 'Admin\Storefront\StorefrontController@index', [AuthMiddleware::class]);
$router->get('/store/storefront/builder', 'Admin\Storefront\StorefrontController@visualBuilder', [AuthMiddleware::class]);
$router->post('/store/storefront/save-builder', 'Admin\Storefront\StorefrontController@saveBuilder', [AuthMiddleware::class]);
$router->get('/store/storefront/theme-designer', 'Admin\Storefront\StorefrontController@themeDesigner', [AuthMiddleware::class]);
$router->get('/store/storefront/theme', 'Admin\Storefront\StorefrontController@getTheme', [AuthMiddleware::class]);
$router->post('/store/storefront/theme', 'Admin\Storefront\StorefrontController@updateTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/storefront/theme/create', 'Admin\Storefront\StorefrontController@createTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/storefront/theme/activate', 'Admin\Storefront\StorefrontController@setActiveTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/storefront/theme/delete', 'Admin\Storefront\StorefrontController@deleteTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/storefront/homepage-builder', 'Admin\Storefront\StorefrontController@homepageBuilder', [AuthMiddleware::class]);
$router->post('/store/storefront/sections', 'Admin\Storefront\StorefrontController@createSection', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/storefront/sections/update', 'Admin\Storefront\StorefrontController@updateSection', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/storefront/sections/delete', 'Admin\Storefront\StorefrontController@deleteSection', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/storefront/sections/reorder', 'Admin\Storefront\StorefrontController@reorderSections', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/storefront/settings', 'Admin\Storefront\StorefrontController@settings', [AuthMiddleware::class]);
$router->post('/store/storefront/settings', 'Admin\Storefront\StorefrontController@updateSettings', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/storefront/navigation', 'Admin\Storefront\StorefrontController@navigationManager', [AuthMiddleware::class]);
$router->post('/store/storefront/assets/upload', 'Admin\Storefront\StorefrontController@uploadAsset', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/storefront/preview', 'Admin\Storefront\StorefrontController@previewTheme', [AuthMiddleware::class]);

// POS (Point of Sale)
$router->get('/store/pos', 'POS\TransactionController@index', [AuthMiddleware::class]);
$router->get('/store/pos/search', 'POS\TransactionController@searchProducts', [AuthMiddleware::class]);
$router->post('/store/pos/checkout', 'POS\TransactionController@checkout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/pos/receipt/{id}', 'POS\TransactionController@receipt', [AuthMiddleware::class]);

// CRM - Customers
$router->get('/store/customers', 'CRM\CustomerController@index', [AuthMiddleware::class]);
$router->get('/store/customers/create', 'CRM\CustomerController@create', [AuthMiddleware::class]);
$router->get('/store/customers/tags', 'CRM\CustomerTagController@index', [AuthMiddleware::class]); // Must be before {id}
$router->get('/store/customers/tags/create', 'CRM\CustomerTagController@create', [AuthMiddleware::class]);
$router->post('/store/customers', 'CRM\CustomerController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/customers/search', 'CRM\CustomerController@search', [AuthMiddleware::class]);
$router->get('/store/customers/export', 'CRM\CustomerController@exportCsv', [AuthMiddleware::class]);
$router->get('/store/customers/{id}', 'CRM\CustomerController@show', [AuthMiddleware::class]);
$router->get('/store/customers/{id}/edit', 'CRM\CustomerController@edit', [AuthMiddleware::class]);
$router->post('/store/customers/{id}', 'CRM\CustomerController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/delete', 'CRM\CustomerController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/addresses', 'CRM\CustomerController@createAddress', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/addresses/{address_id}', 'CRM\CustomerController@updateAddress', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/addresses/{address_id}/delete', 'CRM\CustomerController@deleteAddress', [AuthMiddleware::class, CsrfMiddleware::class]);

// Inventory - Products
$router->get('/store/products', 'Inventory\ProductController@index', [AuthMiddleware::class]);
$router->get('/store/products/create', 'Inventory\ProductController@create', [AuthMiddleware::class]);
$router->post('/store/products', 'Inventory\ProductController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/products/search', 'Inventory\ProductController@search', [AuthMiddleware::class]);
$router->get('/store/products/{id}', 'Inventory\ProductController@show', [AuthMiddleware::class]);
$router->get('/store/products/{id}/edit', 'Inventory\ProductController@edit', [AuthMiddleware::class]);
$router->post('/store/products/{id}', 'Inventory\ProductController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/products/{id}/delete', 'Inventory\ProductController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/products/{id}/adjust-stock', 'Inventory\ProductController@adjustStock', [AuthMiddleware::class, CsrfMiddleware::class]);

// Inventory - Categories
$router->get('/store/categories', 'Inventory\CategoryController@index', [AuthMiddleware::class]);
$router->get('/store/categories/create', 'Inventory\CategoryController@create', [AuthMiddleware::class]);
$router->post('/store/categories', 'Inventory\CategoryController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/categories/{id}/edit', 'Inventory\CategoryController@edit', [AuthMiddleware::class]);
$router->post('/store/categories/{id}', 'Inventory\CategoryController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/categories/{id}/delete', 'Inventory\CategoryController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// Inventory - Vendors
$router->get('/store/vendors', 'Inventory\VendorController@index', [AuthMiddleware::class]);
$router->get('/store/vendors/create', 'Inventory\VendorController@create', [AuthMiddleware::class]);
$router->post('/store/vendors', 'Inventory\VendorController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/vendors/{id}', 'Inventory\VendorController@show', [AuthMiddleware::class]);
$router->get('/store/vendors/{id}/edit', 'Inventory\VendorController@edit', [AuthMiddleware::class]);
$router->post('/store/vendors/{id}', 'Inventory\VendorController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/vendors/{id}/delete', 'Inventory\VendorController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// Reports
$router->get('/store/reports/low-stock', 'Inventory\ReportController@lowStock', [AuthMiddleware::class]);
$router->get('/store/reports/inventory', 'Inventory\ReportController@inventory', [AuthMiddleware::class]);
$router->get('/store/reports/inventory/export', 'Inventory\ReportController@exportInventoryCsv', [AuthMiddleware::class]);

$router->get('/store/reports/sales', 'Reports\SalesReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/sales/export', 'Reports\SalesReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/store/reports/customers', 'Reports\CustomerReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/customers/export', 'Reports\CustomerReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/store/reports/products', 'Reports\ProductReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/products/export', 'Reports\ProductReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/store/reports/payments', 'Reports\PaymentReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/payments/export', 'Reports\PaymentReportController@exportCsv', [AuthMiddleware::class]);

// Rentals
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
$router->get('/store/rentals/available-equipment', 'Rentals\RentalController@searchAvailableEquipment', [AuthMiddleware::class]);
$router->post('/store/rentals/reservations/{id}/checkout', 'Rentals\RentalController@checkout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/rentals/reservations/{id}/checkin', 'Rentals\RentalController@checkin', [AuthMiddleware::class, CsrfMiddleware::class]);

// Air Fills
$router->get('/store/air-fills', 'AirFills\AirFillController@index', [AuthMiddleware::class]);
$router->get('/store/air-fills/create', 'AirFills\AirFillController@create', [AuthMiddleware::class]);
$router->post('/store/air-fills', 'AirFills\AirFillController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/air-fills/quick-fill', 'AirFills\AirFillController@quickFill', [AuthMiddleware::class]);
$router->post('/store/air-fills/quick-fill', 'AirFills\AirFillController@processQuickFill', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/air-fills/export', 'AirFills\AirFillController@export', [AuthMiddleware::class]);
$router->get('/store/air-fills/pricing', 'AirFills\AirFillController@getPricing', [AuthMiddleware::class]);
$router->get('/store/air-fills/{id}', 'AirFills\AirFillController@show', [AuthMiddleware::class]);
$router->get('/store/air-fills/{id}/edit', 'AirFills\AirFillController@edit', [AuthMiddleware::class]);
$router->post('/store/air-fills/{id}', 'AirFills\AirFillController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/air-fills/{id}/delete', 'AirFills\AirFillController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/courses', 'Courses\CourseController@index', [AuthMiddleware::class]);
$router->get('/store/courses/create', 'Courses\CourseController@create', [AuthMiddleware::class]);
$router->get('/store/courses/schedules', 'Courses\CourseController@schedules', [AuthMiddleware::class]);
$router->get('/store/courses/schedules/create', 'Courses\CourseController@createSchedule', [AuthMiddleware::class]);
$router->post('/store/courses/schedules', 'Courses\CourseController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/courses/schedules/{id}', 'Courses\CourseController@showSchedule', [AuthMiddleware::class]);
$router->post('/store/courses/transfer-student', 'Courses\CourseController@transferStudent', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/courses/enrollments', 'Courses\CourseController@enrollments', [AuthMiddleware::class]);
$router->get('/store/courses/enrollments/{id}', 'Courses\CourseController@showEnrollment', [AuthMiddleware::class]);
$router->post('/store/courses/enrollments/{id}/attendance', 'Courses\CourseController@markAttendance', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/courses/enrollments/{id}/grade', 'Courses\CourseController@updateGrade', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/courses', 'Courses\CourseController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/courses/{id}', 'Courses\CourseController@show', [AuthMiddleware::class]);
$router->get('/store/courses/{id}/edit', 'Courses\CourseController@edit', [AuthMiddleware::class]);
$router->post('/store/courses/{id}', 'Courses\CourseController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/courses/{id}/delete', 'Courses\CourseController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/trips', 'Trips\TripController@index', [AuthMiddleware::class]);
$router->get('/store/trips/create', 'Trips\TripController@create', [AuthMiddleware::class]);
$router->get('/store/trips/schedules', 'Trips\TripController@schedules', [AuthMiddleware::class]);
$router->get('/store/trips/schedules/create', 'Trips\TripController@createSchedule', [AuthMiddleware::class]);
$router->post('/store/trips/schedules', 'Trips\TripController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/schedules/{id}', 'Trips\TripController@showSchedule', [AuthMiddleware::class]);
$router->get('/store/trips/bookings', 'Trips\TripController@bookings', [AuthMiddleware::class]);
$router->get('/store/trips/bookings/create', 'Trips\TripController@createBooking', [AuthMiddleware::class]);
$router->post('/store/trips/bookings', 'Trips\TripController@storeBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/bookings/{id}', 'Trips\TripController@showBooking', [AuthMiddleware::class]);
$router->post('/store/trips/bookings/{id}/confirm', 'Trips\TripController@confirmBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/trips/bookings/{id}/cancel', 'Trips\TripController@cancelBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/trips', 'Trips\TripController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/{id}', 'Trips\TripController@show', [AuthMiddleware::class]);
$router->get('/store/trips/{id}/edit', 'Trips\TripController@edit', [AuthMiddleware::class]);
$router->post('/store/trips/{id}', 'Trips\TripController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/trips/{id}/delete', 'Trips\TripController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/workorders', 'WorkOrders\WorkOrderController@index', [AuthMiddleware::class]);
$router->get('/store/workorders/create', 'WorkOrders\WorkOrderController@create', [AuthMiddleware::class]);
$router->post('/store/workorders', 'WorkOrders\WorkOrderController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/workorders/{id}', 'WorkOrders\WorkOrderController@show', [AuthMiddleware::class]);
$router->get('/store/workorders/{id}/edit', 'WorkOrders\WorkOrderController@edit', [AuthMiddleware::class]);
$router->post('/store/workorders/{id}', 'WorkOrders\WorkOrderController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/workorders/{id}/status', 'WorkOrders\WorkOrderController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/workorders/{id}/assign', 'WorkOrders\WorkOrderController@assign', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/workorders/{id}/delete', 'WorkOrders\WorkOrderController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/workorders/{id}/notes', 'WorkOrders\WorkOrderController@addNote', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/shop', 'Shop\ShopController@index');
$router->get('/shop/product/{id}', 'Shop\ShopController@productDetail');
$router->post('/shop/cart/add', 'Shop\ShopController@addToCart', [CsrfMiddleware::class]);
$router->get('/shop/cart', 'Shop\ShopController@cart');
$router->get('/shop/cart/count', 'Shop\ShopController@cartCount');
$router->post('/shop/cart/update', 'Shop\ShopController@updateCart', [CsrfMiddleware::class]);
$router->get('/shop/checkout', 'Shop\ShopController@checkout');
$router->post('/shop/checkout', 'Shop\ShopController@processCheckout', [CsrfMiddleware::class]);

$router->get('/account/register', 'Customer\CustomerAuthController@showRegister');
$router->post('/account/register', 'Customer\CustomerAuthController@register', [CsrfMiddleware::class]);
$router->get('/account/login', 'Customer\CustomerAuthController@showLogin');
$router->post('/account/login', 'Customer\CustomerAuthController@login', [CsrfMiddleware::class]);
$router->post('/account/logout', 'Customer\CustomerAuthController@logout', [CsrfMiddleware::class]);

$router->get('/account', 'Customer\AccountController@dashboard', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->get('/account/orders', 'Customer\AccountController@orders', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->get('/account/orders/{id}', 'Customer\AccountController@orderDetail', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->get('/account/profile', 'Customer\AccountController@profile', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->post('/account/profile', 'Customer\AccountController@updateProfile', [\App\Middleware\CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/account/addresses', 'Customer\AccountController@addresses', [\App\Middleware\CustomerAuthMiddleware::class]);
$router->post('/account/addresses', 'Customer\AccountController@createAddress', [\App\Middleware\CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/account/addresses/{id}', 'Customer\AccountController@updateAddress', [\App\Middleware\CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/account/addresses/{id}/delete', 'Customer\AccountController@deleteAddress', [\App\Middleware\CustomerAuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/orders', 'Ecommerce\OrderController@index', [AuthMiddleware::class]);
$router->get('/store/orders/{id}', 'Ecommerce\OrderController@show', [AuthMiddleware::class]);
$router->post('/store/orders/{id}/status', 'Ecommerce\OrderController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/orders/{id}/ship', 'Ecommerce\OrderController@ship', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/marketing/loyalty', 'Marketing\LoyaltyController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/loyalty/create', 'Marketing\LoyaltyController@create', [AuthMiddleware::class]);
$router->post('/store/marketing/loyalty', 'Marketing\LoyaltyController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/marketing/loyalty/{id}', 'Marketing\LoyaltyController@show', [AuthMiddleware::class]);
$router->get('/store/marketing/loyalty/{id}/edit', 'Marketing\LoyaltyController@edit', [AuthMiddleware::class]);
$router->post('/store/marketing/loyalty/{id}', 'Marketing\LoyaltyController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/loyalty/{id}/delete', 'Marketing\LoyaltyController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/loyalty/adjust-points', 'Marketing\LoyaltyController@adjustPoints', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/marketing/coupons', 'Marketing\CouponController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/coupons/create', 'Marketing\CouponController@create', [AuthMiddleware::class]);
$router->post('/store/marketing/coupons', 'Marketing\CouponController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/marketing/coupons/{id}', 'Marketing\CouponController@show', [AuthMiddleware::class]);
$router->get('/store/marketing/coupons/{id}/edit', 'Marketing\CouponController@edit', [AuthMiddleware::class]);
$router->post('/store/marketing/coupons/{id}', 'Marketing\CouponController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/coupons/{id}/delete', 'Marketing\CouponController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/coupons/validate', 'Marketing\CouponController@validate', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/marketing/campaigns', 'Marketing\CampaignController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/campaigns/create', 'Marketing\CampaignController@create', [AuthMiddleware::class]);
$router->post('/store/marketing/campaigns', 'Marketing\CampaignController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/marketing/campaigns/{id}', 'Marketing\CampaignController@show', [AuthMiddleware::class]);
$router->get('/store/marketing/campaigns/{id}/edit', 'Marketing\CampaignController@edit', [AuthMiddleware::class]);
$router->post('/store/marketing/campaigns/{id}', 'Marketing\CampaignController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/campaigns/{id}/delete', 'Marketing\CampaignController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/campaigns/{id}/send', 'Marketing\CampaignController@send', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/marketing/templates', 'Marketing\CampaignController@templates', [AuthMiddleware::class]);

$router->get('/store/marketing/referrals', 'Marketing\ReferralController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/referrals/history', 'Marketing\ReferralController@history', [AuthMiddleware::class]);
$router->get('/store/marketing/referrals/settings', 'Marketing\ReferralController@settings', [AuthMiddleware::class]);
$router->post('/store/marketing/referrals/settings', 'Marketing\ReferralController@updateSettings', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/marketing/referrals/process', 'Marketing\ReferralController@process', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/cms/pages', 'CMS\PageController@index', [AuthMiddleware::class]);
$router->get('/store/cms/pages/create', 'CMS\PageController@create', [AuthMiddleware::class]);
$router->post('/store/cms/pages', 'CMS\PageController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/cms/pages/{id}', 'CMS\PageController@show', [AuthMiddleware::class]);
$router->get('/store/cms/pages/{id}/edit', 'CMS\PageController@edit', [AuthMiddleware::class]);
$router->post('/store/cms/pages/{id}', 'CMS\PageController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/cms/pages/{id}/delete', 'CMS\PageController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/cms/pages/{id}/publish', 'CMS\PageController@publish', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/cms/blog', 'CMS\BlogController@index', [AuthMiddleware::class]);
$router->get('/store/cms/blog/create', 'CMS\BlogController@create', [AuthMiddleware::class]);
$router->post('/store/cms/blog', 'CMS\BlogController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/cms/blog/{id}', 'CMS\BlogController@show', [AuthMiddleware::class]);
$router->get('/store/cms/blog/{id}/edit', 'CMS\BlogController@edit', [AuthMiddleware::class]);
$router->post('/store/cms/blog/{id}', 'CMS\BlogController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/cms/blog/{id}/delete', 'CMS\BlogController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/cms/blog/{id}/publish', 'CMS\BlogController@publish', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/cms/blog/categories', 'CMS\BlogController@categories', [AuthMiddleware::class]);
$router->get('/store/cms/blog/tags', 'CMS\BlogController@tags', [AuthMiddleware::class]);

$router->get('/store/staff', 'Staff\StaffController@index', [AuthMiddleware::class]);
$router->get('/store/staff/{id}', 'Staff\StaffController@show', [AuthMiddleware::class]);
$router->get('/store/staff/{id}/performance', 'Staff\StaffController@performance', [AuthMiddleware::class]);

$router->get('/store/staff/schedules', 'Staff\ScheduleController@index', [AuthMiddleware::class]);
$router->get('/store/staff/schedules/create', 'Staff\ScheduleController@create', [AuthMiddleware::class]);
$router->post('/store/staff/schedules', 'Staff\ScheduleController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/staff/schedules/{id}/delete', 'Staff\ScheduleController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/store/staff/timeclock', 'Staff\TimeClockController@index', [AuthMiddleware::class]);
$router->post('/store/staff/timeclock/clockin', 'Staff\TimeClockController@clockIn', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/staff/timeclock/clockout', 'Staff\TimeClockController@clockOut', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/staff/timeclock/reports', 'Staff\TimeClockController@reports', [AuthMiddleware::class]);

$router->get('/store/staff/commissions', 'Staff\CommissionController@index', [AuthMiddleware::class]);
$router->get('/store/staff/commissions/staff/{id}', 'Staff\CommissionController@staff', [AuthMiddleware::class]);
$router->get('/store/staff/commissions/reports', 'Staff\CommissionController@reports', [AuthMiddleware::class]);

// Admin Settings
$router->get('/store/admin/settings', 'Admin\SettingsController@index', [AuthMiddleware::class]);

// Demo Data Management
$router->get('/store/admin/demo-data', 'Admin\DemoDataController@index', [AuthMiddleware::class]);
$router->post('/store/admin/demo-data/load', 'Admin\DemoDataController@load', [AuthMiddleware::class]);
$router->post('/store/admin/demo-data/clear', 'Admin\DemoDataController@clear', [AuthMiddleware::class]);

// Error Logs
$router->get('/store/admin/errors', 'Admin\ErrorLogController@index', [AuthMiddleware::class]);
$router->get('/store/admin/errors/{id}', 'Admin\ErrorLogController@show', [AuthMiddleware::class]);
$router->post('/store/admin/errors/{id}/resolve', 'Admin\ErrorLogController@resolve', [AuthMiddleware::class]);

$router->get('/store/admin/settings/general', 'Admin\SettingsController@general', [AuthMiddleware::class]);
$router->get('/store/admin/settings/tax', 'Admin\SettingsController@tax', [AuthMiddleware::class]);
$router->get('/store/admin/settings/email', 'Admin\SettingsController@email', [AuthMiddleware::class]);
$router->get('/store/admin/settings/payment', 'Admin\SettingsController@payment', [AuthMiddleware::class]);
$router->get('/store/admin/settings/rental', 'Admin\SettingsController@rental', [AuthMiddleware::class]);
$router->get('/store/admin/settings/air-fills', 'Admin\SettingsController@airFills', [AuthMiddleware::class]);
$router->get('/store/admin/settings/integrations', 'Admin\SettingsController@integrations', [AuthMiddleware::class]);
$router->post('/store/admin/settings/update', 'Admin\SettingsController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/settings/update-integrations', 'Admin\SettingsController@updateIntegrations', [AuthMiddleware::class]);
$router->post('/store/admin/settings/upload-logo', 'Admin\SettingsController@uploadLogo', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/settings/tax/rates', 'Admin\SettingsController@storeTaxRate', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/settings/tax/rates/{id}', 'Admin\SettingsController@updateTaxRate', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/settings/tax/rates/{id}/delete', 'Admin\SettingsController@deleteTaxRate', [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin User Management
$router->get('/store/admin/users', 'Admin\UserController@index', [AuthMiddleware::class]);
$router->get('/store/admin/users/create', 'Admin\UserController@create', [AuthMiddleware::class]);
$router->post('/store/admin/users', 'Admin\UserController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/admin/users/{id}', 'Admin\UserController@show', [AuthMiddleware::class]);
$router->get('/store/admin/users/{id}/edit', 'Admin\UserController@edit', [AuthMiddleware::class]);
$router->post('/store/admin/users/{id}', 'Admin\UserController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/users/{id}/delete', 'Admin\UserController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/users/{id}/reset-password', 'Admin\UserController@resetPassword', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/users/{id}/toggle-status', 'Admin\UserController@toggleStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin Role Management
$router->get('/store/admin/roles', 'Admin\RoleController@index', [AuthMiddleware::class]);
$router->get('/store/admin/roles/create', 'Admin\RoleController@create', [AuthMiddleware::class]);
$router->post('/store/admin/roles', 'Admin\RoleController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/admin/roles/{id}/edit', 'Admin\RoleController@edit', [AuthMiddleware::class]);
$router->post('/store/admin/roles/{id}', 'Admin\RoleController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/roles/{id}/delete', 'Admin\RoleController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// Wave Apps Integration
$router->get('/store/integrations/wave', 'Integrations\WaveController@index', [AuthMiddleware::class]);
$router->get('/store/integrations/wave/test-connection', 'Integrations\WaveController@testConnection', [AuthMiddleware::class]);
$router->post('/store/integrations/wave/bulk-sync', 'Integrations\WaveController@bulkSync', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/integrations/wave/export-csv', 'Integrations\WaveController@exportCSV', [AuthMiddleware::class]);
$router->post('/store/integrations/wave/sync/{id}', 'Integrations\WaveController@syncTransaction', [AuthMiddleware::class, CsrfMiddleware::class]);

// QuickBooks Integration
$router->get('/store/integrations/quickbooks', 'Integrations\QuickBooksController@index', [AuthMiddleware::class]);
$router->post('/store/integrations/quickbooks/config', 'Integrations\QuickBooksController@saveConfig', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/integrations/quickbooks/export', 'Integrations\QuickBooksController@exportPage', [AuthMiddleware::class]);
$router->post('/store/integrations/quickbooks/download', 'Integrations\QuickBooksController@download', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/integrations/quickbooks/preview', 'Integrations\QuickBooksController@preview', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/integrations/quickbooks/delete/{id}', 'Integrations\QuickBooksController@deleteExport', [AuthMiddleware::class, CsrfMiddleware::class]);

// Google Workspace Integration
$router->get('/store/integrations/google-workspace', 'Integrations\GoogleWorkspaceController@index', [AuthMiddleware::class]);
$router->post('/store/integrations/google-workspace/config', 'Integrations\GoogleWorkspaceController@saveConfig', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/integrations/google-workspace/test', 'Integrations\GoogleWorkspaceController@testConnection', [AuthMiddleware::class, CsrfMiddleware::class]);

// Dive Sites Management
$router->get('/store/dive-sites', 'DiveSitesController@index', [AuthMiddleware::class]);
$router->get('/store/dive-sites/create', 'DiveSitesController@create', [AuthMiddleware::class]);
$router->post('/store/dive-sites', 'DiveSitesController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/dive-sites/{id}', 'DiveSitesController@show', [AuthMiddleware::class]);
$router->get('/store/dive-sites/{id}/edit', 'DiveSitesController@edit', [AuthMiddleware::class]);
$router->post('/store/dive-sites/{id}', 'DiveSitesController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/dive-sites/{id}/delete', 'DiveSitesController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/dive-sites/{id}/weather', 'DiveSitesController@getWeather', [AuthMiddleware::class]);

// Serial Number Tracking
$router->get('/store/serial-numbers', 'SerialNumberController@index', [AuthMiddleware::class]);
$router->get('/store/serial-numbers/create', 'SerialNumberController@create', [AuthMiddleware::class]);
$router->post('/store/serial-numbers', 'SerialNumberController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/serial-numbers/{id}', 'SerialNumberController@show', [AuthMiddleware::class]);
$router->get('/store/serial-numbers/{id}/edit', 'SerialNumberController@edit', [AuthMiddleware::class]);
$router->post('/store/serial-numbers/{id}', 'SerialNumberController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/serial-numbers/{id}/delete', 'SerialNumberController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/serial-numbers/search', 'SerialNumberController@search', [AuthMiddleware::class]);
$router->get('/store/serial-numbers/{id}/history', 'SerialNumberController@history', [AuthMiddleware::class]);

// Vendor Catalog Import
$router->get('/store/vendor-catalog/import', 'VendorCatalogController@index', [AuthMiddleware::class]);
$router->post('/store/vendor-catalog/upload', 'VendorCatalogController@upload', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/vendor-catalog/preview', 'VendorCatalogController@preview', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/vendor-catalog/process', 'VendorCatalogController@commit', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/vendor-catalog/templates', 'VendorCatalogController@templates', [AuthMiddleware::class]);
$router->get('/store/vendor-catalog/download-template/{vendor}', 'VendorCatalogController@downloadTemplate', [AuthMiddleware::class]);

// API Token Management
$router->get('/store/api/tokens', 'API\TokenController@index', [AuthMiddleware::class]);
$router->get('/store/api/tokens/create', 'API\TokenController@create', [AuthMiddleware::class]);
$router->post('/store/api/tokens', 'API\TokenController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/api/tokens/{id}/revoke', 'API\TokenController@revoke', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/api/tokens/{id}/delete', 'API\TokenController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/api/docs', 'API\DocumentationController@index', [AuthMiddleware::class]);

// API - Course Schedules (for POS)
$router->get('/store/api/courses/{id}/schedules', 'API\CourseScheduleController@getAvailableSchedules', [AuthMiddleware::class]);

// Waiver Management (Staff)
$router->get('/store/waivers', 'WaiverController@index', [AuthMiddleware::class]);
$router->get('/store/waivers/{id}', 'WaiverController@show', [AuthMiddleware::class]);
$router->get('/store/waivers/{id}/pdf', 'WaiverController@downloadPDF', [AuthMiddleware::class]);

// Public Waiver Signing (no auth required - accessed via email link)
$router->get('/waivers/sign/{token}', 'WaiverController@sign');
$router->post('/waivers/sign/{token}', 'WaiverController@submitSignature');

// API Settings (for AJAX calls)
$router->get('/store/api/settings/tax-rate', 'Admin\SettingsController@getTaxRate', [AuthMiddleware::class]);

// AI Image Search API
$router->get('/store/api/product-embeddings', 'API\ProductEmbeddingsController@getEmbeddings', [AuthMiddleware::class]);
$router->post('/store/api/product-embeddings', 'API\ProductEmbeddingsController@saveEmbedding', [AuthMiddleware::class]);
$router->post('/store/api/visual-search-log', 'API\ProductEmbeddingsController@logSearch', [AuthMiddleware::class]);
$router->get('/store/api/products-without-embeddings', 'API\ProductEmbeddingsController@getProductsWithoutEmbeddings', [AuthMiddleware::class]);

// Customer Info API for POS
$router->get('/store/api/customers/{id}/pos-info', 'API\CustomerInfoController@getPosInfo', [AuthMiddleware::class]);

// ============================================================================
// PHASE 4, 5, 6 ROUTES - Advanced Features
// ============================================================================

// Notifications
$router->get('/store/notifications', 'NotificationsController@index', [AuthMiddleware::class]);
$router->get('/store/notifications/unread', 'NotificationsController@getUnread', [AuthMiddleware::class]);
$router->post('/store/notifications/{id}/mark-read', 'NotificationsController@markAsRead', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/notifications/mark-all-read', 'NotificationsController@markAllAsRead', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/notifications/{id}/delete', 'NotificationsController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// Appointments
$router->get('/store/appointments', 'AppointmentsController@index', [AuthMiddleware::class]);
$router->get('/store/appointments/create', 'AppointmentsController@create', [AuthMiddleware::class]);
$router->post('/store/appointments', 'AppointmentsController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/appointments/{id}', 'AppointmentsController@show', [AuthMiddleware::class]);
$router->get('/store/appointments/{id}/edit', 'AppointmentsController@edit', [AuthMiddleware::class]);
$router->post('/store/appointments/{id}', 'AppointmentsController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/appointments/{id}/delete', 'AppointmentsController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/appointments/{id}/confirm', 'AppointmentsController@confirm', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/appointments/{id}/cancel', 'AppointmentsController@cancel', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/appointments/calendar', 'AppointmentsController@calendar', [AuthMiddleware::class]);

// Documents
$router->get('/store/documents', 'DocumentsController@index', [AuthMiddleware::class]);
$router->get('/store/documents/create', 'DocumentsController@create', [AuthMiddleware::class]);
$router->post('/store/documents/upload', 'DocumentsController@upload', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/documents/{id}', 'DocumentsController@show', [AuthMiddleware::class]);
$router->get('/store/documents/{id}/download', 'DocumentsController@download', [AuthMiddleware::class]);
$router->post('/store/documents/{id}/delete', 'DocumentsController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/documents/{id}/share', 'DocumentsController@share', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/documents/customer/{customerId}', 'DocumentsController@customerDocuments', [AuthMiddleware::class]);

// Reports Dashboard
$router->get('/store/reports', 'Reports\ReportsDashboardController@index', [AuthMiddleware::class]);
$router->get('/store/reports/custom/create', 'Reports\ReportsDashboardController@create', [AuthMiddleware::class]);
$router->post('/store/reports/custom', 'Reports\ReportsDashboardController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/reports/custom/{id}', 'Reports\ReportsDashboardController@show', [AuthMiddleware::class]);
$router->post('/store/reports/custom/{id}/run', 'Reports\ReportsDashboardController@run', [AuthMiddleware::class, CsrfMiddleware::class]);

// Audit Logs
$router->get('/store/admin/audit', 'Admin\AuditLogController@index', [AuthMiddleware::class]);
$router->get('/store/admin/audit/export', 'Admin\AuditLogController@export', [AuthMiddleware::class]);
$router->get('/store/admin/audit/{id}', 'Admin\AuditLogController@show', [AuthMiddleware::class]);
$router->get('/store/admin/audit/user/{userId}', 'Admin\AuditLogController@userActivity', [AuthMiddleware::class]);

// System Settings (Advanced)
$router->get('/store/admin/system-settings', 'Admin\SystemSettingsController@index', [AuthMiddleware::class]);
$router->post('/store/admin/system-settings/update', 'Admin\SystemSettingsController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/admin/system-settings/cache/clear', 'Admin\SystemSettingsController@clearCache', [AuthMiddleware::class]);
$router->get('/store/admin/system-settings/logs', 'Admin\SystemSettingsController@viewLogs', [AuthMiddleware::class]);

// Customer Portal
$router->get('/customer/portal', 'Customer\PortalDashboardController@index');
$router->get('/customer/portal/dashboard', 'Customer\PortalDashboardController@dashboard');
$router->get('/customer/portal/certifications', 'Customer\PortalDashboardController@certifications');
$router->get('/customer/portal/rental-history', 'Customer\PortalDashboardController@rentalHistory');
$router->get('/customer/portal/trip-history', 'Customer\PortalDashboardController@tripHistory');
$router->get('/customer/portal/documents', 'Customer\PortalDashboardController@documents');

// Global Search
$router->get('/store/search', 'SearchController@index', [AuthMiddleware::class]);
$router->get('/store/search/results', 'SearchController@search', [AuthMiddleware::class]);
$router->get('/store/search/quick', 'SearchController@quickSearch', [AuthMiddleware::class]);

// Dashboard Widgets
$router->get('/store/dashboard/widgets', 'Dashboard\WidgetController@index', [AuthMiddleware::class]);
$router->get('/store/dashboard/widgets/available', 'Dashboard\WidgetController@available', [AuthMiddleware::class]);
$router->post('/store/dashboard/widgets/add', 'Dashboard\WidgetController@add', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/dashboard/widgets/remove', 'Dashboard\WidgetController@remove', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/dashboard/widgets/update-layout', 'Dashboard\WidgetController@updateLayout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/dashboard/widgets/{id}/config', 'Dashboard\WidgetController@updateConfig', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/dashboard/widgets/{id}/data', 'Dashboard\WidgetController@getData', [AuthMiddleware::class]);

// Backup & Restore
$router->get('/store/admin/backups', 'Admin\BackupController@index', [AuthMiddleware::class]);
$router->post('/store/admin/backups/create', 'Admin\BackupController@create', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/admin/backups/{filename}/download', 'Admin\BackupController@download', [AuthMiddleware::class]);
$router->post('/store/admin/backups/{filename}/restore', 'Admin\BackupController@restore', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/admin/backups/{filename}/delete', 'Admin\BackupController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/admin/backups/statistics', 'Admin\BackupController@statistics', [AuthMiddleware::class]);
$router->post('/store/admin/backups/schedule', 'Admin\BackupController@schedule', [AuthMiddleware::class, CsrfMiddleware::class]);

// Communication Center
$router->get('/store/communication', 'CommunicationController@index', [AuthMiddleware::class]);
$router->post('/store/communication/sms/send', 'CommunicationController@sendSMS', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/communication/push/send', 'CommunicationController@sendPush', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/communication/bulk/sms', 'CommunicationController@sendBulkSMS', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/communication/bulk/push', 'CommunicationController@sendBulkPush', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/communication/history', 'CommunicationController@history', [AuthMiddleware::class]);
$router->get('/store/communication/campaigns', 'CommunicationController@campaigns', [AuthMiddleware::class]);
$router->get('/store/communication/preferences/{customerId}', 'CommunicationController@getPreferences', [AuthMiddleware::class]);
$router->post('/store/communication/preferences/{customerId}', 'CommunicationController@updatePreferences', [AuthMiddleware::class, CsrfMiddleware::class]);

// Equipment Maintenance
$router->get('/store/maintenance', 'MaintenanceController@index', [AuthMiddleware::class]);
$router->get('/store/maintenance/create', 'MaintenanceController@create', [AuthMiddleware::class]);
$router->post('/store/maintenance/record', 'MaintenanceController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/maintenance/schedule', 'MaintenanceController@schedule', [AuthMiddleware::class]);
$router->post('/store/maintenance/schedule', 'MaintenanceController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/maintenance/cost-analysis', 'MaintenanceController@costAnalysis', [AuthMiddleware::class]);
$router->get('/store/maintenance/equipment/{id}/history', 'MaintenanceController@equipmentHistory', [AuthMiddleware::class]);
$router->post('/store/maintenance/schedule/{id}/complete', 'MaintenanceController@completeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/maintenance/schedule/{id}/cancel', 'MaintenanceController@cancelSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);

// Advanced Inventory Management
$router->get('/store/inventory/advanced', 'Inventory\AdvancedInventoryController@index', [AuthMiddleware::class]);
$router->get('/store/inventory/reorder-alerts', 'Inventory\AdvancedInventoryController@reorderAlerts', [AuthMiddleware::class]);
$router->post('/store/inventory/reorder-rules', 'Inventory\AdvancedInventoryController@setReorderRule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/inventory/auto-po/{id}', 'Inventory\AdvancedInventoryController@createAutoPO', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/inventory/turnover', 'Inventory\AdvancedInventoryController@turnover', [AuthMiddleware::class]);
$router->get('/store/inventory/valuation', 'Inventory\AdvancedInventoryController@valuation', [AuthMiddleware::class]);
$router->get('/store/inventory/slow-moving', 'Inventory\AdvancedInventoryController@slowMoving', [AuthMiddleware::class]);
$router->get('/store/inventory/fast-moving', 'Inventory\AdvancedInventoryController@fastMoving', [AuthMiddleware::class]);
$router->post('/store/inventory/cycle-count', 'Inventory\AdvancedInventoryController@recordCycleCount', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/inventory/forecast', 'Inventory\AdvancedInventoryController@forecast', [AuthMiddleware::class]);
$router->get('/store/inventory/purchase-orders', 'Inventory\AdvancedInventoryController@purchaseOrders', [AuthMiddleware::class]);

// Loyalty Program Dashboard
$router->get('/store/loyalty', 'LoyaltyController@index', [AuthMiddleware::class]);
$router->get('/store/loyalty/dashboard', 'LoyaltyController@dashboard', [AuthMiddleware::class]);
$router->get('/store/loyalty/members', 'LoyaltyController@members', [AuthMiddleware::class]);
$router->get('/store/loyalty/members/{id}', 'LoyaltyController@memberDetails', [AuthMiddleware::class]);
$router->post('/store/loyalty/points/award', 'LoyaltyController@awardPoints', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/loyalty/points/redeem', 'LoyaltyController@redeemPoints', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/loyalty/rewards', 'LoyaltyController@rewards', [AuthMiddleware::class]);
$router->post('/store/loyalty/rewards/create', 'LoyaltyController@createReward', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/loyalty/tiers', 'LoyaltyController@tiers', [AuthMiddleware::class]);
$router->get('/store/loyalty/statistics', 'LoyaltyController@statistics', [AuthMiddleware::class]);
$router->get('/store/loyalty/transactions', 'LoyaltyController@transactions', [AuthMiddleware::class]);

// Analytics Dashboard
$router->get('/store/analytics', 'AnalyticsController@index', [AuthMiddleware::class]);
$router->get('/store/analytics/sales', 'AnalyticsController@sales', [AuthMiddleware::class]);
$router->get('/store/analytics/customers', 'AnalyticsController@customers', [AuthMiddleware::class]);
$router->get('/store/analytics/products', 'AnalyticsController@products', [AuthMiddleware::class]);
$router->get('/store/analytics/courses', 'AnalyticsController@courses', [AuthMiddleware::class]);
$router->get('/store/analytics/trips', 'AnalyticsController@trips', [AuthMiddleware::class]);
$router->get('/store/analytics/rentals', 'AnalyticsController@rentals', [AuthMiddleware::class]);
$router->get('/store/analytics/export', 'AnalyticsController@export', [AuthMiddleware::class]);
$router->get('/store/analytics/dashboard-metrics', 'AnalyticsController@dashboardMetrics', [AuthMiddleware::class]);

// Customer Tags
$router->get('/store/customers/tags', 'CRM\CustomerTagController@index', [AuthMiddleware::class]);
$router->get('/store/customers/tags/create', 'CRM\CustomerTagController@create', [AuthMiddleware::class]);
$router->post('/store/customers/tags', 'CRM\CustomerTagController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/tags/{id}', 'CRM\CustomerTagController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/tags/{id}/delete', 'CRM\CustomerTagController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/tags/assign', 'CRM\CustomerTagController@assignToCustomer', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/tags/{tagId}/remove', 'CRM\CustomerTagController@removeFromCustomer', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/customers/{id}/tags', 'CRM\CustomerTagController@getCustomerTags', [AuthMiddleware::class]);

// Customer Additional Contact Info
$router->post('/store/customers/{id}/phones', 'CRM\CustomerController@addPhone', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/phones/{phoneId}', 'CRM\CustomerController@updatePhone', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/phones/{phoneId}/delete', 'CRM\CustomerController@deletePhone', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/emails/add', 'CRM\CustomerController@addEmail', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/emails/{emailId}', 'CRM\CustomerController@updateEmail', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/emails/{emailId}/delete', 'CRM\CustomerController@deleteEmail', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/contacts', 'CRM\CustomerController@addContact', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/contacts/{contactId}', 'CRM\CustomerController@updateContact', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/contacts/{contactId}/delete', 'CRM\CustomerController@deleteContact', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/certifications', 'CRM\CustomerController@addCertification', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/customers/{id}/certifications/{certId}/delete', 'CRM\CustomerController@deleteCertification', [AuthMiddleware::class, CsrfMiddleware::class]);

// Cash Drawer Management
$router->get('/store/cash-drawer', 'CashDrawer\CashDrawerController@index', [AuthMiddleware::class]);
$router->get('/store/cash-drawer/{id}/open', 'CashDrawer\CashDrawerController@open', [AuthMiddleware::class]);
$router->post('/store/cash-drawer/open', 'CashDrawer\CashDrawerController@processOpen', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/cash-drawer/session/{id}/close', 'CashDrawer\CashDrawerController@close', [AuthMiddleware::class]);
$router->post('/store/cash-drawer/session/{id}/close', 'CashDrawer\CashDrawerController@processClose', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/cash-drawer/transaction', 'CashDrawer\CashDrawerController@addTransaction', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/cash-drawer/history', 'CashDrawer\CashDrawerController@history', [AuthMiddleware::class]);
$router->get('/store/cash-drawer/session/{id}', 'CashDrawer\CashDrawerController@viewSession', [AuthMiddleware::class]);

// ============================================================================
// FEEDBACK & SUPPORT TICKET SYSTEM (Beta 1)
// ============================================================================

// Feedback Submission (Public - no auth required for easy access)
$router->get('/feedback/create', 'FeedbackController@create');
$router->post('/feedback/submit', 'FeedbackController@store', [CsrfMiddleware::class]);
$router->get('/feedback/success', 'FeedbackController@success');

// Feedback Management (Staff only)
$router->get('/store/feedback', 'FeedbackController@index', [AuthMiddleware::class]);
$router->get('/store/feedback/{id}', 'FeedbackController@show', [AuthMiddleware::class]);
$router->post('/store/feedback/{id}/comment', 'FeedbackController@addComment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/feedback/{id}/status', 'FeedbackController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/feedback/{id}/assign', 'FeedbackController@assign', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/feedback/{id}/vote', 'FeedbackController@vote', [CsrfMiddleware::class]);
$router->get('/store/feedback/category/{slug}', 'FeedbackController@byCategory', [AuthMiddleware::class]);
$router->get('/store/feedback/export', 'FeedbackController@export', [AuthMiddleware::class]);

// ============================================================================
// PADI MEDICAL FORMS (Phase 1 - Week 1)
// ============================================================================

// Medical Form Submission
$router->get('/medical/create', 'MedicalFormController@create', [AuthMiddleware::class]);
$router->post('/medical/submit', 'MedicalFormController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/medical/{id}', 'MedicalFormController@show', [AuthMiddleware::class]);
$router->post('/medical/upload-clearance', 'MedicalFormController@uploadClearance', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// PADI TRAINING COMPLETION (Phase 1 - Week 3)
// ============================================================================

// Training Completion (PADI Form 10234)
$router->get('/training/complete', 'TrainingCompletionController@create', [AuthMiddleware::class]);
$router->post('/training/submit-completion', 'TrainingCompletionController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/training/completion/{id}', 'TrainingCompletionController@show', [AuthMiddleware::class]);
$router->post('/training/submit-to-padi', 'TrainingCompletionController@submitToPadi', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// PADI INCIDENT REPORTING (Phase 1 - Week 3)
// ============================================================================

// Incident Reports (PADI Form 10120) - Mobile optimized
$router->get('/incidents/report', 'IncidentReportController@create', [AuthMiddleware::class]);
$router->post('/incidents/submit', 'IncidentReportController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/incidents', 'IncidentReportController@index', [AuthMiddleware::class]);
$router->get('/store/incidents/{id}', 'IncidentReportController@show', [AuthMiddleware::class]);

// ============================================================================
// COMPANY SETTINGS (Critical - Added Nov 2025)
// ============================================================================

$router->get('/store/admin/settings/company', 'Admin\CompanySettingsController@index', [AuthMiddleware::class]);
$router->post('/store/admin/settings/company', 'Admin\CompanySettingsController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/api/settings/company', 'Admin\CompanySettingsController@getSettings', [AuthMiddleware::class]);

// ============================================================================
// NEWSLETTER SUBSCRIPTION (Critical - Added Nov 2025)
// ============================================================================

// Public newsletter routes
$router->get('/newsletter/subscribe', 'NewsletterController@showSubscribe');
$router->post('/newsletter/subscribe', 'NewsletterController@subscribe', [CsrfMiddleware::class]);
$router->get('/newsletter/confirm/{token}', 'NewsletterController@confirm');
$router->get('/newsletter/unsubscribe/{token}', 'NewsletterController@unsubscribe');

// Admin newsletter management
$router->get('/store/marketing/newsletter', 'NewsletterController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/newsletter/export', 'NewsletterController@export', [AuthMiddleware::class]);

// ============================================================================
// HELP CENTER & SUPPORT (Critical - Added Nov 2025)
// ============================================================================

// Public help routes
$router->get('/help', 'HelpController@index');
$router->get('/help/faq', 'HelpController@faq');
$router->get('/help/article/{slug}', 'HelpController@article');
$router->get('/help/search', 'HelpController@search');
$router->get('/help/contact', 'HelpController@contact');
$router->post('/help/contact', 'HelpController@submitTicket', [CsrfMiddleware::class]);

// Admin help management
$router->get('/store/admin/help', 'HelpController@admin', [AuthMiddleware::class]);

// ============================================================================
// SERIAL NUMBER SCANNING (Missing route - Added Nov 2025)
// ============================================================================

$router->get('/store/serial-numbers/scan', 'SerialNumberController@scan', [AuthMiddleware::class]);
$router->post('/store/serial-numbers/scan', 'SerialNumberController@processScan', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// LAYAWAY SYSTEM (Payment Plans)
// ============================================================================

$router->get('/store/layaway', 'Financial\LayawayController@index', [AuthMiddleware::class]);
$router->get('/store/layaway/create', 'Financial\LayawayController@create', [AuthMiddleware::class]);
$router->post('/store/layaway', 'Financial\LayawayController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/layaway/plans', 'Financial\LayawayController@plans', [AuthMiddleware::class]);
$router->post('/store/layaway/plans', 'Financial\LayawayController@savePlan', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/layaway/upcoming', 'Financial\LayawayController@upcomingPayments', [AuthMiddleware::class]);
$router->get('/store/layaway/{id}', 'Financial\LayawayController@show', [AuthMiddleware::class]);
$router->post('/store/layaway/{id}/payment', 'Financial\LayawayController@recordPayment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/layaway/{id}/cancel', 'Financial\LayawayController@cancel', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// DIVING CLUBS
// ============================================================================

$router->get('/store/clubs', 'Club\ClubController@index', [AuthMiddleware::class]);
$router->get('/store/clubs/dashboard', 'Club\ClubController@dashboard', [AuthMiddleware::class]);
$router->get('/store/clubs/create', 'Club\ClubController@create', [AuthMiddleware::class]);
$router->post('/store/clubs', 'Club\ClubController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/clubs/{id}', 'Club\ClubController@show', [AuthMiddleware::class]);
$router->post('/store/clubs/{id}/member', 'Club\ClubController@addMember', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/clubs/{id}/events', 'Club\ClubController@events', [AuthMiddleware::class]);
$router->post('/store/clubs/{id}/events', 'Club\ClubController@createEvent', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// BUDDY SYSTEM (Dive Partner Management)
// ============================================================================

$router->get('/store/buddies', 'Buddy\BuddyController@index', [AuthMiddleware::class]);
$router->get('/store/buddies/create', 'Buddy\BuddyController@create', [AuthMiddleware::class]);
$router->get('/store/buddies/find-match', 'Buddy\BuddyController@findMatch', [AuthMiddleware::class]);
$router->post('/store/buddies', 'Buddy\BuddyController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/buddies/{id}', 'Buddy\BuddyController@show', [AuthMiddleware::class]);
$router->post('/store/buddies/{id}/status', 'Buddy\BuddyController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/buddies/{id}/dive', 'Buddy\BuddyController@recordDive', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// CONSERVATION TRACKING
// ============================================================================

$router->get('/store/conservation', 'Conservation\ConservationController@index', [AuthMiddleware::class]);
$router->get('/store/conservation/dashboard', 'Conservation\ConservationController@dashboard', [AuthMiddleware::class]);
$router->get('/store/conservation/create', 'Conservation\ConservationController@create', [AuthMiddleware::class]);
$router->post('/store/conservation', 'Conservation\ConservationController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/conservation/{id}', 'Conservation\ConservationController@show', [AuthMiddleware::class]);
$router->post('/store/conservation/{id}/participant', 'Conservation\ConservationController@addParticipant', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/conservation/{id}/hours', 'Conservation\ConservationController@logHours', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// INSURANCE MANAGEMENT
// ============================================================================

$router->get('/store/insurance', 'Insurance\InsuranceController@index', [AuthMiddleware::class]);
$router->get('/store/insurance/dashboard', 'Insurance\InsuranceController@dashboard', [AuthMiddleware::class]);
$router->get('/store/insurance/expiring', 'Insurance\InsuranceController@expiring', [AuthMiddleware::class]);
$router->get('/store/insurance/create', 'Insurance\InsuranceController@create', [AuthMiddleware::class]);
$router->post('/store/insurance', 'Insurance\InsuranceController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/insurance/{id}', 'Insurance\InsuranceController@show', [AuthMiddleware::class]);
$router->get('/store/insurance/{id}/edit', 'Insurance\InsuranceController@edit', [AuthMiddleware::class]);
$router->post('/store/insurance/{id}', 'Insurance\InsuranceController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// DIVE LOGS
// ============================================================================

$router->get('/store/dive-logs', 'DiveLog\DiveLogController@index', [AuthMiddleware::class]);
$router->get('/store/dive-logs/dashboard', 'DiveLog\DiveLogController@dashboard', [AuthMiddleware::class]);
$router->get('/store/dive-logs/create', 'DiveLog\DiveLogController@create', [AuthMiddleware::class]);
$router->post('/store/dive-logs', 'DiveLog\DiveLogController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/dive-logs/customer/{id}', 'DiveLog\DiveLogController@customerLogs', [AuthMiddleware::class]);
$router->get('/store/dive-logs/{id}', 'DiveLog\DiveLogController@show', [AuthMiddleware::class]);

// ============================================================================
// GIFT CARDS
// ============================================================================

$router->get('/store/gift-cards', 'GiftCard\GiftCardController@index', [AuthMiddleware::class]);
$router->get('/store/gift-cards/create', 'GiftCard\GiftCardController@create', [AuthMiddleware::class]);
$router->get('/store/gift-cards/check-balance', 'GiftCard\GiftCardController@checkBalance', [AuthMiddleware::class]);
$router->post('/store/gift-cards', 'GiftCard\GiftCardController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/gift-cards/{id}', 'GiftCard\GiftCardController@show', [AuthMiddleware::class]);
$router->post('/store/gift-cards/{id}/reload', 'GiftCard\GiftCardController@reload', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/gift-cards/{id}/deactivate', 'GiftCard\GiftCardController@deactivate', [AuthMiddleware::class, CsrfMiddleware::class]);

// ============================================================================
// PRE-DIVE SAFETY CHECKS
// ============================================================================

$router->get('/store/safety-checks', 'Safety\SafetyCheckController@index', [AuthMiddleware::class]);
$router->get('/store/safety-checks/dashboard', 'Safety\SafetyCheckController@dashboard', [AuthMiddleware::class]);
$router->get('/store/safety-checks/create', 'Safety\SafetyCheckController@create', [AuthMiddleware::class]);
$router->post('/store/safety-checks', 'Safety\SafetyCheckController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/safety-checks/{id}', 'Safety\SafetyCheckController@show', [AuthMiddleware::class]);

return $router;
