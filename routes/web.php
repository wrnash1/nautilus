<?php

use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

$router = new Router();

$router->get('/', 'Admin\DashboardController@index', [AuthMiddleware::class]);
$router->get('/login', 'Auth\AuthController@showLogin');
$router->post('/login', 'Auth\AuthController@login');
$router->post('/logout', 'Auth\AuthController@logout', [AuthMiddleware::class]);

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

return $router;
