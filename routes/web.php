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

// ============================================================================
// PUBLIC STOREFRONT ROUTES (Customer-facing website)
// ============================================================================

// Homepage - Public storefront
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@submitContact', [CsrfMiddleware::class]);

// ============================================================================
// ADMIN/STAFF ROUTES (Employee backend)
// ============================================================================

// Admin Dashboard & Auth
$router->get('/admin', 'Admin\DashboardController@index', [AuthMiddleware::class]);
$router->get('/login', 'Auth\AuthController@showLogin');
$router->post('/login', 'Auth\AuthController@login');
$router->post('/logout', 'Auth\AuthController@logout', [AuthMiddleware::class]);

// Storefront Configuration (Admin only)
$router->get('/admin/storefront', 'Admin\Storefront\StorefrontController@index', [AuthMiddleware::class]);
$router->get('/admin/storefront/theme-designer', 'Admin\Storefront\StorefrontController@themeDesigner', [AuthMiddleware::class]);
$router->get('/admin/storefront/theme', 'Admin\Storefront\StorefrontController@getTheme', [AuthMiddleware::class]);
$router->post('/admin/storefront/theme', 'Admin\Storefront\StorefrontController@updateTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/storefront/theme/create', 'Admin\Storefront\StorefrontController@createTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/storefront/theme/activate', 'Admin\Storefront\StorefrontController@setActiveTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/storefront/theme/delete', 'Admin\Storefront\StorefrontController@deleteTheme', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/storefront/homepage-builder', 'Admin\Storefront\StorefrontController@homepageBuilder', [AuthMiddleware::class]);
$router->post('/admin/storefront/sections', 'Admin\Storefront\StorefrontController@createSection', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/storefront/sections/update', 'Admin\Storefront\StorefrontController@updateSection', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/storefront/sections/delete', 'Admin\Storefront\StorefrontController@deleteSection', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/storefront/sections/reorder', 'Admin\Storefront\StorefrontController@reorderSections', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/storefront/settings', 'Admin\Storefront\StorefrontController@settings', [AuthMiddleware::class]);
$router->post('/admin/storefront/settings', 'Admin\Storefront\StorefrontController@updateSettings', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/storefront/navigation', 'Admin\Storefront\StorefrontController@navigationManager', [AuthMiddleware::class]);
$router->post('/admin/storefront/assets/upload', 'Admin\Storefront\StorefrontController@uploadAsset', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/storefront/preview', 'Admin\Storefront\StorefrontController@previewTheme', [AuthMiddleware::class]);

$router->get('/pos', 'POS\TransactionController@index', [AuthMiddleware::class]);
$router->get('/pos/search', 'POS\TransactionController@searchProducts', [AuthMiddleware::class]);
$router->post('/pos/checkout', 'POS\TransactionController@checkout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/pos/receipt/{id}', 'POS\TransactionController@receipt', [AuthMiddleware::class]);

$router->get('/customers', 'CRM\CustomerController@index', [AuthMiddleware::class]);
$router->get('/customers/create', 'CRM\CustomerController@create', [AuthMiddleware::class]);
$router->post('/customers', 'CRM\CustomerController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/customers/search', 'CRM\CustomerController@search', [AuthMiddleware::class]);
$router->get('/customers/export', 'CRM\CustomerController@exportCsv', [AuthMiddleware::class]);
$router->get('/customers/{id}', 'CRM\CustomerController@show', [AuthMiddleware::class]);
$router->get('/customers/{id}/edit', 'CRM\CustomerController@edit', [AuthMiddleware::class]);
$router->post('/customers/{id}', 'CRM\CustomerController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/customers/{id}/delete', 'CRM\CustomerController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/customers/{id}/addresses', 'CRM\CustomerController@createAddress', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/customers/{id}/addresses/{address_id}', 'CRM\CustomerController@updateAddress', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/customers/{id}/addresses/{address_id}/delete', 'CRM\CustomerController@deleteAddress', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/products', 'Inventory\ProductController@index', [AuthMiddleware::class]);
$router->get('/products/create', 'Inventory\ProductController@create', [AuthMiddleware::class]);
$router->post('/products', 'Inventory\ProductController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/products/search', 'Inventory\ProductController@search', [AuthMiddleware::class]);
$router->get('/products/{id}', 'Inventory\ProductController@show', [AuthMiddleware::class]);
$router->get('/products/{id}/edit', 'Inventory\ProductController@edit', [AuthMiddleware::class]);
$router->post('/products/{id}', 'Inventory\ProductController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/products/{id}/delete', 'Inventory\ProductController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/products/{id}/adjust-stock', 'Inventory\ProductController@adjustStock', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/categories', 'Inventory\CategoryController@index', [AuthMiddleware::class]);
$router->get('/categories/create', 'Inventory\CategoryController@create', [AuthMiddleware::class]);
$router->post('/categories', 'Inventory\CategoryController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/categories/{id}/edit', 'Inventory\CategoryController@edit', [AuthMiddleware::class]);
$router->post('/categories/{id}', 'Inventory\CategoryController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/categories/{id}/delete', 'Inventory\CategoryController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/vendors', 'Inventory\VendorController@index', [AuthMiddleware::class]);
$router->get('/vendors/create', 'Inventory\VendorController@create', [AuthMiddleware::class]);
$router->post('/vendors', 'Inventory\VendorController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/vendors/{id}', 'Inventory\VendorController@show', [AuthMiddleware::class]);
$router->get('/vendors/{id}/edit', 'Inventory\VendorController@edit', [AuthMiddleware::class]);
$router->post('/vendors/{id}', 'Inventory\VendorController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/vendors/{id}/delete', 'Inventory\VendorController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/reports/low-stock', 'Inventory\ReportController@lowStock', [AuthMiddleware::class]);
$router->get('/reports/inventory', 'Inventory\ReportController@inventory', [AuthMiddleware::class]);
$router->get('/reports/inventory/export', 'Inventory\ReportController@exportInventoryCsv', [AuthMiddleware::class]);

$router->get('/reports/sales', 'Reports\SalesReportController@index', [AuthMiddleware::class]);
$router->get('/reports/sales/export', 'Reports\SalesReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/reports/customers', 'Reports\CustomerReportController@index', [AuthMiddleware::class]);
$router->get('/reports/customers/export', 'Reports\CustomerReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/reports/products', 'Reports\ProductReportController@index', [AuthMiddleware::class]);
$router->get('/reports/products/export', 'Reports\ProductReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/reports/payments', 'Reports\PaymentReportController@index', [AuthMiddleware::class]);
$router->get('/reports/payments/export', 'Reports\PaymentReportController@exportCsv', [AuthMiddleware::class]);

$router->get('/rentals', 'Rentals\RentalController@index', [AuthMiddleware::class]);
$router->get('/rentals/equipment/create', 'Rentals\RentalController@createEquipment', [AuthMiddleware::class]);
$router->post('/rentals/equipment', 'Rentals\RentalController@storeEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/rentals/equipment/{id}', 'Rentals\RentalController@showEquipment', [AuthMiddleware::class]);
$router->get('/rentals/equipment/{id}/edit', 'Rentals\RentalController@editEquipment', [AuthMiddleware::class]);
$router->post('/rentals/equipment/{id}', 'Rentals\RentalController@updateEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/rentals/equipment/{id}/delete', 'Rentals\RentalController@deleteEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/rentals/reservations', 'Rentals\RentalController@reservations', [AuthMiddleware::class]);
$router->get('/rentals/reservations/create', 'Rentals\RentalController@createReservation', [AuthMiddleware::class]);
$router->get('/rentals/reservations/{id}', 'Rentals\RentalController@showReservation', [AuthMiddleware::class]);
$router->get('/rentals/available-equipment', 'Rentals\RentalController@searchAvailableEquipment', [AuthMiddleware::class]);
$router->post('/rentals/reservations/{id}/checkout', 'Rentals\RentalController@checkout', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/rentals/reservations/{id}/checkin', 'Rentals\RentalController@checkin', [AuthMiddleware::class, CsrfMiddleware::class]);

// Air Fills
$router->get('/air-fills', 'AirFills\AirFillController@index', [AuthMiddleware::class]);
$router->get('/air-fills/create', 'AirFills\AirFillController@create', [AuthMiddleware::class]);
$router->post('/air-fills', 'AirFills\AirFillController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/air-fills/quick-fill', 'AirFills\AirFillController@quickFill', [AuthMiddleware::class]);
$router->post('/air-fills/quick-fill', 'AirFills\AirFillController@processQuickFill', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/air-fills/export', 'AirFills\AirFillController@export', [AuthMiddleware::class]);
$router->get('/air-fills/pricing', 'AirFills\AirFillController@getPricing', [AuthMiddleware::class]);
$router->get('/air-fills/{id}', 'AirFills\AirFillController@show', [AuthMiddleware::class]);
$router->get('/air-fills/{id}/edit', 'AirFills\AirFillController@edit', [AuthMiddleware::class]);
$router->post('/air-fills/{id}', 'AirFills\AirFillController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/air-fills/{id}/delete', 'AirFills\AirFillController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/courses', 'Courses\CourseController@index', [AuthMiddleware::class]);
$router->get('/courses/create', 'Courses\CourseController@create', [AuthMiddleware::class]);
$router->get('/courses/schedules', 'Courses\CourseController@schedules', [AuthMiddleware::class]);
$router->get('/courses/schedules/create', 'Courses\CourseController@createSchedule', [AuthMiddleware::class]);
$router->post('/courses/schedules', 'Courses\CourseController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/courses/schedules/{id}', 'Courses\CourseController@showSchedule', [AuthMiddleware::class]);
$router->get('/courses/enrollments', 'Courses\CourseController@enrollments', [AuthMiddleware::class]);
$router->get('/courses/enrollments/{id}', 'Courses\CourseController@showEnrollment', [AuthMiddleware::class]);
$router->post('/courses/enrollments/{id}/attendance', 'Courses\CourseController@markAttendance', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/courses/enrollments/{id}/grade', 'Courses\CourseController@updateGrade', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/courses', 'Courses\CourseController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/courses/{id}', 'Courses\CourseController@show', [AuthMiddleware::class]);
$router->get('/courses/{id}/edit', 'Courses\CourseController@edit', [AuthMiddleware::class]);
$router->post('/courses/{id}', 'Courses\CourseController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/courses/{id}/delete', 'Courses\CourseController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/trips', 'Trips\TripController@index', [AuthMiddleware::class]);
$router->get('/trips/create', 'Trips\TripController@create', [AuthMiddleware::class]);
$router->get('/trips/schedules', 'Trips\TripController@schedules', [AuthMiddleware::class]);
$router->get('/trips/schedules/create', 'Trips\TripController@createSchedule', [AuthMiddleware::class]);
$router->post('/trips/schedules', 'Trips\TripController@storeSchedule', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/trips/schedules/{id}', 'Trips\TripController@showSchedule', [AuthMiddleware::class]);
$router->get('/trips/bookings', 'Trips\TripController@bookings', [AuthMiddleware::class]);
$router->get('/trips/bookings/create', 'Trips\TripController@createBooking', [AuthMiddleware::class]);
$router->post('/trips/bookings', 'Trips\TripController@storeBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/trips/bookings/{id}', 'Trips\TripController@showBooking', [AuthMiddleware::class]);
$router->post('/trips/bookings/{id}/confirm', 'Trips\TripController@confirmBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/trips/bookings/{id}/cancel', 'Trips\TripController@cancelBooking', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/trips', 'Trips\TripController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/trips/{id}', 'Trips\TripController@show', [AuthMiddleware::class]);
$router->get('/trips/{id}/edit', 'Trips\TripController@edit', [AuthMiddleware::class]);
$router->post('/trips/{id}', 'Trips\TripController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/trips/{id}/delete', 'Trips\TripController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/workorders', 'WorkOrders\WorkOrderController@index', [AuthMiddleware::class]);
$router->get('/workorders/create', 'WorkOrders\WorkOrderController@create', [AuthMiddleware::class]);
$router->post('/workorders', 'WorkOrders\WorkOrderController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/workorders/{id}', 'WorkOrders\WorkOrderController@show', [AuthMiddleware::class]);
$router->get('/workorders/{id}/edit', 'WorkOrders\WorkOrderController@edit', [AuthMiddleware::class]);
$router->post('/workorders/{id}', 'WorkOrders\WorkOrderController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/workorders/{id}/status', 'WorkOrders\WorkOrderController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/workorders/{id}/assign', 'WorkOrders\WorkOrderController@assign', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/workorders/{id}/delete', 'WorkOrders\WorkOrderController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/workorders/{id}/notes', 'WorkOrders\WorkOrderController@addNote', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/shop', 'Shop\ShopController@index');
$router->get('/shop/product/{id}', 'Shop\ShopController@productDetail');
$router->post('/shop/cart/add', 'Shop\ShopController@addToCart', [CsrfMiddleware::class]);
$router->get('/shop/cart', 'Shop\ShopController@cart');
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

$router->get('/orders', 'Ecommerce\OrderController@index', [AuthMiddleware::class]);
$router->get('/orders/{id}', 'Ecommerce\OrderController@show', [AuthMiddleware::class]);
$router->post('/orders/{id}/status', 'Ecommerce\OrderController@updateStatus', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/orders/{id}/ship', 'Ecommerce\OrderController@ship', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/marketing/loyalty', 'Marketing\LoyaltyController@index', [AuthMiddleware::class]);
$router->get('/marketing/loyalty/create', 'Marketing\LoyaltyController@create', [AuthMiddleware::class]);
$router->post('/marketing/loyalty', 'Marketing\LoyaltyController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/marketing/loyalty/{id}', 'Marketing\LoyaltyController@show', [AuthMiddleware::class]);
$router->get('/marketing/loyalty/{id}/edit', 'Marketing\LoyaltyController@edit', [AuthMiddleware::class]);
$router->post('/marketing/loyalty/{id}', 'Marketing\LoyaltyController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/loyalty/{id}/delete', 'Marketing\LoyaltyController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/loyalty/adjust-points', 'Marketing\LoyaltyController@adjustPoints', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/marketing/coupons', 'Marketing\CouponController@index', [AuthMiddleware::class]);
$router->get('/marketing/coupons/create', 'Marketing\CouponController@create', [AuthMiddleware::class]);
$router->post('/marketing/coupons', 'Marketing\CouponController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/marketing/coupons/{id}', 'Marketing\CouponController@show', [AuthMiddleware::class]);
$router->get('/marketing/coupons/{id}/edit', 'Marketing\CouponController@edit', [AuthMiddleware::class]);
$router->post('/marketing/coupons/{id}', 'Marketing\CouponController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/coupons/{id}/delete', 'Marketing\CouponController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/coupons/validate', 'Marketing\CouponController@validate', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/marketing/campaigns', 'Marketing\CampaignController@index', [AuthMiddleware::class]);
$router->get('/marketing/campaigns/create', 'Marketing\CampaignController@create', [AuthMiddleware::class]);
$router->post('/marketing/campaigns', 'Marketing\CampaignController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/marketing/campaigns/{id}', 'Marketing\CampaignController@show', [AuthMiddleware::class]);
$router->get('/marketing/campaigns/{id}/edit', 'Marketing\CampaignController@edit', [AuthMiddleware::class]);
$router->post('/marketing/campaigns/{id}', 'Marketing\CampaignController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/campaigns/{id}/delete', 'Marketing\CampaignController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/campaigns/{id}/send', 'Marketing\CampaignController@send', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/marketing/templates', 'Marketing\CampaignController@templates', [AuthMiddleware::class]);

$router->get('/marketing/referrals', 'Marketing\ReferralController@index', [AuthMiddleware::class]);
$router->get('/marketing/referrals/history', 'Marketing\ReferralController@history', [AuthMiddleware::class]);
$router->get('/marketing/referrals/settings', 'Marketing\ReferralController@settings', [AuthMiddleware::class]);
$router->post('/marketing/referrals/settings', 'Marketing\ReferralController@updateSettings', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/marketing/referrals/process', 'Marketing\ReferralController@process', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/cms/pages', 'CMS\PageController@index', [AuthMiddleware::class]);
$router->get('/cms/pages/create', 'CMS\PageController@create', [AuthMiddleware::class]);
$router->post('/cms/pages', 'CMS\PageController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/cms/pages/{id}', 'CMS\PageController@show', [AuthMiddleware::class]);
$router->get('/cms/pages/{id}/edit', 'CMS\PageController@edit', [AuthMiddleware::class]);
$router->post('/cms/pages/{id}', 'CMS\PageController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/cms/pages/{id}/delete', 'CMS\PageController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/cms/pages/{id}/publish', 'CMS\PageController@publish', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/cms/blog', 'CMS\BlogController@index', [AuthMiddleware::class]);
$router->get('/cms/blog/create', 'CMS\BlogController@create', [AuthMiddleware::class]);
$router->post('/cms/blog', 'CMS\BlogController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/cms/blog/{id}', 'CMS\BlogController@show', [AuthMiddleware::class]);
$router->get('/cms/blog/{id}/edit', 'CMS\BlogController@edit', [AuthMiddleware::class]);
$router->post('/cms/blog/{id}', 'CMS\BlogController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/cms/blog/{id}/delete', 'CMS\BlogController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/cms/blog/{id}/publish', 'CMS\BlogController@publish', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/cms/blog/categories', 'CMS\BlogController@categories', [AuthMiddleware::class]);
$router->get('/cms/blog/tags', 'CMS\BlogController@tags', [AuthMiddleware::class]);

$router->get('/staff', 'Staff\StaffController@index', [AuthMiddleware::class]);
$router->get('/staff/{id}', 'Staff\StaffController@show', [AuthMiddleware::class]);
$router->get('/staff/{id}/performance', 'Staff\StaffController@performance', [AuthMiddleware::class]);

$router->get('/staff/schedules', 'Staff\ScheduleController@index', [AuthMiddleware::class]);
$router->get('/staff/schedules/create', 'Staff\ScheduleController@create', [AuthMiddleware::class]);
$router->post('/staff/schedules', 'Staff\ScheduleController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/staff/schedules/{id}/delete', 'Staff\ScheduleController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/staff/timeclock', 'Staff\TimeClockController@index', [AuthMiddleware::class]);
$router->post('/staff/timeclock/clockin', 'Staff\TimeClockController@clockIn', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/staff/timeclock/clockout', 'Staff\TimeClockController@clockOut', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/staff/timeclock/reports', 'Staff\TimeClockController@reports', [AuthMiddleware::class]);

$router->get('/staff/commissions', 'Staff\CommissionController@index', [AuthMiddleware::class]);
$router->get('/staff/commissions/staff/{id}', 'Staff\CommissionController@staff', [AuthMiddleware::class]);
$router->get('/staff/commissions/reports', 'Staff\CommissionController@reports', [AuthMiddleware::class]);

// Admin Settings
$router->get('/admin/settings', 'Admin\SettingsController@index', [AuthMiddleware::class]);
$router->get('/admin/settings/general', 'Admin\SettingsController@general', [AuthMiddleware::class]);
$router->get('/admin/settings/tax', 'Admin\SettingsController@tax', [AuthMiddleware::class]);
$router->get('/admin/settings/email', 'Admin\SettingsController@email', [AuthMiddleware::class]);
$router->get('/admin/settings/payment', 'Admin\SettingsController@payment', [AuthMiddleware::class]);
$router->get('/admin/settings/rental', 'Admin\SettingsController@rental', [AuthMiddleware::class]);
$router->get('/admin/settings/air-fills', 'Admin\SettingsController@airFills', [AuthMiddleware::class]);
$router->get('/admin/settings/integrations', 'Admin\SettingsController@integrations', [AuthMiddleware::class]);
$router->post('/admin/settings/update', 'Admin\SettingsController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/settings/upload-logo', 'Admin\SettingsController@uploadLogo', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/settings/tax/rates', 'Admin\SettingsController@storeTaxRate', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/settings/tax/rates/{id}', 'Admin\SettingsController@updateTaxRate', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/settings/tax/rates/{id}/delete', 'Admin\SettingsController@deleteTaxRate', [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin User Management
$router->get('/admin/users', 'Admin\UserController@index', [AuthMiddleware::class]);
$router->get('/admin/users/create', 'Admin\UserController@create', [AuthMiddleware::class]);
$router->post('/admin/users', 'Admin\UserController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/users/{id}', 'Admin\UserController@show', [AuthMiddleware::class]);
$router->get('/admin/users/{id}/edit', 'Admin\UserController@edit', [AuthMiddleware::class]);
$router->post('/admin/users/{id}', 'Admin\UserController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/delete', 'Admin\UserController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/reset-password', 'Admin\UserController@resetPassword', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{id}/toggle-status', 'Admin\UserController@toggleStatus', [AuthMiddleware::class, CsrfMiddleware::class]);

// Admin Role Management
$router->get('/admin/roles', 'Admin\RoleController@index', [AuthMiddleware::class]);
$router->get('/admin/roles/create', 'Admin\RoleController@create', [AuthMiddleware::class]);
$router->post('/admin/roles', 'Admin\RoleController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/roles/{id}/edit', 'Admin\RoleController@edit', [AuthMiddleware::class]);
$router->post('/admin/roles/{id}', 'Admin\RoleController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/roles/{id}/delete', 'Admin\RoleController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// Wave Apps Integration
$router->get('/integrations/wave', 'Integrations\WaveController@index', [AuthMiddleware::class]);
$router->get('/integrations/wave/test-connection', 'Integrations\WaveController@testConnection', [AuthMiddleware::class]);
$router->post('/integrations/wave/bulk-sync', 'Integrations\WaveController@bulkSync', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/integrations/wave/export-csv', 'Integrations\WaveController@exportCSV', [AuthMiddleware::class]);
$router->post('/integrations/wave/sync/{id}', 'Integrations\WaveController@syncTransaction', [AuthMiddleware::class, CsrfMiddleware::class]);

// QuickBooks Integration
$router->get('/integrations/quickbooks', 'Integrations\QuickBooksController@index', [AuthMiddleware::class]);
$router->post('/integrations/quickbooks/config', 'Integrations\QuickBooksController@saveConfig', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/integrations/quickbooks/export', 'Integrations\QuickBooksController@exportPage', [AuthMiddleware::class]);
$router->post('/integrations/quickbooks/download', 'Integrations\QuickBooksController@download', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/integrations/quickbooks/preview', 'Integrations\QuickBooksController@preview', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/integrations/quickbooks/delete/{id}', 'Integrations\QuickBooksController@deleteExport', [AuthMiddleware::class, CsrfMiddleware::class]);

// Google Workspace Integration
$router->get('/integrations/google-workspace', 'Integrations\GoogleWorkspaceController@index', [AuthMiddleware::class]);
$router->post('/integrations/google-workspace/config', 'Integrations\GoogleWorkspaceController@saveConfig', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/integrations/google-workspace/test', 'Integrations\GoogleWorkspaceController@testConnection', [AuthMiddleware::class, CsrfMiddleware::class]);

// Dive Sites Management
$router->get('/dive-sites', 'DiveSitesController@index', [AuthMiddleware::class]);
$router->get('/dive-sites/create', 'DiveSitesController@create', [AuthMiddleware::class]);
$router->post('/dive-sites', 'DiveSitesController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/dive-sites/{id}', 'DiveSitesController@show', [AuthMiddleware::class]);
$router->get('/dive-sites/{id}/edit', 'DiveSitesController@edit', [AuthMiddleware::class]);
$router->post('/dive-sites/{id}', 'DiveSitesController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/dive-sites/{id}/delete', 'DiveSitesController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/dive-sites/{id}/weather', 'DiveSitesController@getWeather', [AuthMiddleware::class]);

// Serial Number Tracking
$router->get('/serial-numbers', 'SerialNumberController@index', [AuthMiddleware::class]);
$router->get('/serial-numbers/create', 'SerialNumberController@create', [AuthMiddleware::class]);
$router->post('/serial-numbers', 'SerialNumberController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/serial-numbers/{id}', 'SerialNumberController@show', [AuthMiddleware::class]);
$router->get('/serial-numbers/{id}/edit', 'SerialNumberController@edit', [AuthMiddleware::class]);
$router->post('/serial-numbers/{id}', 'SerialNumberController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/serial-numbers/{id}/delete', 'SerialNumberController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/serial-numbers/search', 'SerialNumberController@search', [AuthMiddleware::class]);
$router->get('/serial-numbers/{id}/history', 'SerialNumberController@history', [AuthMiddleware::class]);

// Vendor Catalog Import
$router->get('/vendor-catalog/import', 'VendorCatalogController@import', [AuthMiddleware::class]);
$router->post('/vendor-catalog/upload', 'VendorCatalogController@upload', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/vendor-catalog/preview', 'VendorCatalogController@preview', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/vendor-catalog/process', 'VendorCatalogController@process', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/vendor-catalog/templates', 'VendorCatalogController@templates', [AuthMiddleware::class]);
$router->get('/vendor-catalog/download-template/{vendor}', 'VendorCatalogController@downloadTemplate', [AuthMiddleware::class]);

// API Token Management
$router->get('/api/tokens', 'API\TokenController@index', [AuthMiddleware::class]);
$router->get('/api/tokens/create', 'API\TokenController@create', [AuthMiddleware::class]);
$router->post('/api/tokens', 'API\TokenController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/api/tokens/{id}/revoke', 'API\TokenController@revoke', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/api/tokens/{id}/delete', 'API\TokenController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/api/docs', 'API\DocumentationController@index', [AuthMiddleware::class]);

// Waiver Management
$router->get('/waivers', 'WaiverController@index', [AuthMiddleware::class]);
$router->get('/waivers/{id}', 'WaiverController@show', [AuthMiddleware::class]);
$router->get('/waivers/{id}/pdf', 'WaiverController@downloadPDF', [AuthMiddleware::class]);

// Public Waiver Signing (no auth required - accessed via email link)
$router->get('/waivers/sign/{token}', 'WaiverController@sign');
$router->post('/waivers/sign/{token}', 'WaiverController@submitSignature');

// API Settings (for AJAX calls)
$router->get('/api/settings/tax-rate', 'Admin\SettingsController@getTaxRate', [AuthMiddleware::class]);

return $router;
