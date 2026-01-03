<?php
/**
 * Additional Routes for All Controllers
 * Add these routes to routes/web.php
 */

use App\Middleware\AuthMiddleware;
use App\Middleware\CustomerAuthMiddleware;
use App\Middleware\CsrfMiddleware;

// =====================================================
// AIR FILLS MANAGEMENT (Admin)
// =====================================================
$router->get('/store/air-fills', 'AirFills\\AirFillController@index', [AuthMiddleware::class]);
$router->get('/store/air-fills/create', 'AirFills\\AirFillController@create', [AuthMiddleware::class]);
$router->post('/store/air-fills', 'AirFills\\AirFillController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/air-fills/{id}/edit', 'AirFills\\AirFillController@edit', [AuthMiddleware::class]);
$router->post('/store/air-fills/{id}', 'AirFills\\AirFillController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/air-fills/{id}/delete', 'AirFills\\AirFillController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// BUDDY SYSTEM (Customer Portal)
// =====================================================
$router->get('/account/buddies', 'Buddy\\BuddyController@index', [CustomerAuthMiddleware::class]);
$router->get('/account/buddies/find', 'Buddy\\BuddyController@find', [CustomerAuthMiddleware::class]);
$router->post('/account/buddies/request', 'Buddy\\BuddyController@sendRequest', [CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/account/buddies/accept', 'Buddy\\BuddyController@acceptRequest', [CustomerAuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// CASH DRAWER (Admin)
// =====================================================
$router->get('/store/cash-drawer', 'CashDrawer\\CashDrawerController@index', [AuthMiddleware::class]);
$router->post('/store/cash-drawer/open', 'CashDrawer\\CashDrawerController@open', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/store/cash-drawer/close', 'CashDrawer\\CashDrawerController@close', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/cash-drawer/history', 'CashDrawer\\CashDrawerController@history', [AuthMiddleware::class]);

// =====================================================
// CERTIFICATIONS (Admin & Customer)
// =====================================================
$router->get('/store/certifications', 'Certifications\\CertificationController@index', [AuthMiddleware::class]);
$router->get('/store/certifications/create', 'Certifications\\CertificationController@create', [AuthMiddleware::class]);
$router->post('/store/certifications', 'Certifications\\CertificationController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/certifications/{id}/edit', 'Certifications\\CertificationController@edit', [AuthMiddleware::class]);
$router->post('/store/certifications/{id}', 'Certifications\\CertificationController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/account/certifications', 'Certifications\\CertificationController@customerIndex', [CustomerAuthMiddleware::class]);

// =====================================================
// DIVE CLUB (Admin & Customer)
// =====================================================
$router->get('/store/club', 'Club\\ClubController@index', [AuthMiddleware::class]);
$router->get('/store/club/members', 'Club\\ClubController@members', [AuthMiddleware::class]);
$router->get('/store/club/events', 'Club\\ClubController@events', [AuthMiddleware::class]);
$router->get('/account/club', 'Club\\ClubController@customerIndex', [CustomerAuthMiddleware::class]);
$router->get('/account/club/events', 'Club\\ClubController@customerEvents', [CustomerAuthMiddleware::class]);

// =====================================================
// CONSERVATION (Public & Admin)
// =====================================================
$router->get('/conservation', 'Conservation\\ConservationController@index');
$router->get('/conservation/{id}', 'Conservation\\ConservationController@show');
$router->get('/store/conservation', 'Conservation\\ConservationController@adminIndex', [AuthMiddleware::class]);
$router->post('/store/conservation', 'Conservation\\ConservationController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// DIVE LOG (Customer Portal)
// =====================================================
$router->get('/account/dive-log', 'DiveLog\\DiveLogController@index', [CustomerAuthMiddleware::class]);
$router->get('/account/dive-log/create', 'DiveLog\\DiveLogController@create', [CustomerAuthMiddleware::class]);
$router->post('/account/dive-log', 'DiveLog\\DiveLogController@store', [CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/account/dive-log/{id}/edit', 'DiveLog\\DiveLogController@edit', [CustomerAuthMiddleware::class]);
$router->post('/account/dive-log/{id}', 'DiveLog\\DiveLogController@update', [CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/account/dive-log/{id}/delete', 'DiveLog\\DiveLogController@delete', [CustomerAuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/account/dive-log/stats', 'DiveLog\\DiveLogController@stats', [CustomerAuthMiddleware::class]);

// =====================================================
// DIVE SITES (Public & Admin)
// =====================================================
$router->get('/dive-sites', 'DiveSitesController@index');
$router->get('/dive-sites/{id}', 'DiveSitesController@show');
$router->get('/store/dive-sites', 'DiveSitesController@adminIndex', [AuthMiddleware::class]);
$router->post('/store/dive-sites', 'DiveSitesController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// DOCUMENTS (Admin & Customer)
// =====================================================
$router->get('/store/documents', 'DocumentsController@index', [AuthMiddleware::class]);
$router->post('/store/documents/upload', 'DocumentsController@upload', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/account/documents', 'DocumentsController@customerIndex', [CustomerAuthMiddleware::class]);

// =====================================================
// FEEDBACK (Admin & Customer)
// =====================================================
$router->get('/store/feedback', 'FeedbackController@index', [AuthMiddleware::class]);
$router->get('/feedback', 'FeedbackController@create');
$router->post('/feedback', 'FeedbackController@store', [CsrfMiddleware::class]);

// =====================================================
// GIFT CARDS (Admin & Storefront)
// =====================================================
$router->get('/store/gift-cards', 'GiftCard\\GiftCardController@index', [AuthMiddleware::class]);
$router->get('/store/gift-cards/create', 'GiftCard\\GiftCardController@create', [AuthMiddleware::class]);
$router->post('/store/gift-cards', 'GiftCard\\GiftCardController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/gift-cards', 'GiftCard\\GiftCardController@storefront');
$router->post('/gift-cards/purchase', 'GiftCard\\GiftCardController@purchase', [CsrfMiddleware::class]);
$router->get('/gift-cards/check-balance', 'GiftCard\\GiftCardController@checkBalance');

// =====================================================
// HELP & SUPPORT
// =====================================================
$router->get('/help', 'HelpController@index');
$router->get('/help/{category}', 'HelpController@category');
$router->get('/help/article/{id}', 'HelpController@article');
$router->get('/store/help', 'HelpController@adminIndex', [AuthMiddleware::class]);

// =====================================================
// INCIDENT REPORTS (Admin)
// =====================================================
$router->get('/store/incidents', 'IncidentReportController@index', [AuthMiddleware::class]);
$router->get('/store/incidents/create', 'IncidentReportController@create', [AuthMiddleware::class]);
$router->post('/store/incidents', 'IncidentReportController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/incidents/{id}', 'IncidentReportController@show', [AuthMiddleware::class]);

// =====================================================
// INSTRUCTOR PORTAL
// =====================================================
$router->get('/instructor', 'Instructor\\InstructorController@index', [AuthMiddleware::class]);
$router->get('/instructor/courses', 'Instructor\\InstructorController@courses', [AuthMiddleware::class]);
$router->get('/instructor/students', 'Instructor\\InstructorController@students', [AuthMiddleware::class]);

// =====================================================
// INSURANCE (Admin & Customer)
// =====================================================
$router->get('/insurance', 'Insurance\\InsuranceController@index');
$router->get('/insurance/quote', 'Insurance\\InsuranceController@quote');
$router->post('/insurance/apply', 'Insurance\\InsuranceController@apply', [CsrfMiddleware::class]);
$router->get('/store/insurance', 'Insurance\\InsuranceController@adminIndex', [AuthMiddleware::class]);

// =====================================================
// INVENTORY MANAGEMENT (Admin)
// =====================================================
$router->get('/store/inventory', 'Inventory\\InventoryController@index', [AuthMiddleware::class]);
$router->get('/store/inventory/create', 'Inventory\\InventoryController@create', [AuthMiddleware::class]);
$router->post('/store/inventory', 'Inventory\\InventoryController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/inventory/{id}/edit', 'Inventory\\InventoryController@edit', [AuthMiddleware::class]);
$router->post('/store/inventory/{id}', 'Inventory\\InventoryController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// Equipment
$router->get('/store/equipment', 'Inventory\\EquipmentController@index', [AuthMiddleware::class]);
$router->get('/store/equipment/create', 'Inventory\\EquipmentController@create', [AuthMiddleware::class]);
$router->post('/store/equipment', 'Inventory\\EquipmentController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// Stock Management
$router->get('/store/stock', 'Inventory\\StockController@index', [AuthMiddleware::class]);
$router->post('/store/stock/adjust', 'Inventory\\StockController@adjust', [AuthMiddleware::class, CsrfMiddleware::class]);

// Transfers
$router->get('/store/transfers', 'Inventory\\TransferController@index', [AuthMiddleware::class]);
$router->post('/store/transfers', 'Inventory\\TransferController@create', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// LOYALTY PROGRAM (Admin & Customer)
// =====================================================
$router->get('/store/loyalty', 'LoyaltyController@index', [AuthMiddleware::class]);
$router->get('/account/loyalty', 'LoyaltyController@customerIndex', [CustomerAuthMiddleware::class]);
$router->get('/account/loyalty/rewards', 'LoyaltyController@rewards', [CustomerAuthMiddleware::class]);

// =====================================================
// MAINTENANCE (Admin)
// =====================================================
$router->get('/store/maintenance', 'MaintenanceController@index', [AuthMiddleware::class]);
$router->get('/store/maintenance/schedule', 'MaintenanceController@schedule', [AuthMiddleware::class]);
$router->post('/store/maintenance', 'MaintenanceController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// Compressor Maintenance
$router->get('/store/compressor', 'Admin\\CompressorController@index', [AuthMiddleware::class]);
$router->get('/store/compressor/logs', 'Admin\\CompressorController@logs', [AuthMiddleware::class]);
$router->post('/store/compressor/log', 'Admin\\CompressorController@logMaintenance', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// MEDICAL FORMS (Admin & Customer)
// =====================================================
$router->get('/medical-form', 'MedicalFormController@index');
$router->post('/medical-form/submit', 'MedicalFormController@submit', [CsrfMiddleware::class]);
$router->get('/store/medical-forms', 'MedicalFormController@adminIndex', [AuthMiddleware::class]);
$router->get('/store/medical-forms/{id}', 'MedicalFormController@show', [AuthMiddleware::class]);

// =====================================================
// NEWSLETTER (Admin & Public)
// =====================================================
$router->post('/newsletter/subscribe', 'NewsletterController@subscribe', [CsrfMiddleware::class]);
$router->get('/newsletter/unsubscribe', 'NewsletterController@unsubscribe');
$router->get('/store/newsletter', 'NewsletterController@adminIndex', [AuthMiddleware::class]);
$router->post('/store/newsletter/send', 'NewsletterController@send', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// NOTIFICATIONS (Admin & Customer)
// =====================================================
$router->get('/store/notifications', 'NotificationsController@index', [AuthMiddleware::class]);
$router->get('/account/notifications', 'NotificationsController@customerIndex', [CustomerAuthMiddleware::class]);
$router->post('/account/notifications/mark-read', 'NotificationsController@markRead', [CustomerAuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// RENTALS (Admin - already has storefront route)
// =====================================================
$router->get('/store/rentals', 'Rentals\\RentalController@index', [AuthMiddleware::class]);
$router->get('/store/rentals/create', 'Rentals\\RentalController@create', [AuthMiddleware::class]);
$router->post('/store/rentals', 'Rentals\\RentalController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/rentals/{id}', 'Rentals\\RentalController@show', [AuthMiddleware::class]);
$router->post('/store/rentals/{id}/return', 'Rentals\\RentalController@returnEquipment', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// REPORTS (Admin)
// =====================================================
$router->get('/store/reports', 'Reports\\ReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/sales', 'Reports\\SalesReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/inventory', 'Reports\\InventoryReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/customers', 'Reports\\CustomerReportController@index', [AuthMiddleware::class]);
$router->get('/store/reports/financial', 'Reports\\FinancialReportController@index', [AuthMiddleware::class]);

// =====================================================
// SAFETY (Admin & Public)
// =====================================================
$router->get('/safety', 'Safety\\SafetyController@index');
$router->get('/safety/tips', 'Safety\\SafetyController@tips');
$router->get('/store/safety', 'Safety\\SafetyController@adminIndex', [AuthMiddleware::class]);

// =====================================================
// SEARCH
// =====================================================
$router->get('/search', 'SearchController@index');
$router->get('/api/search', 'SearchController@api');

// =====================================================
// SERIAL NUMBERS (Admin)
// =====================================================
$router->get('/store/serial-numbers', 'SerialNumberController@index', [AuthMiddleware::class]);
$router->post('/store/serial-numbers/register', 'SerialNumberController@register', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// STAFF MANAGEMENT (Admin)
// =====================================================
$router->get('/store/staff', 'Staff\\StaffController@index', [AuthMiddleware::class]);
$router->get('/store/staff/create', 'Staff\\StaffController@create', [AuthMiddleware::class]);
$router->post('/store/staff', 'Staff\\StaffController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// Staff Schedule
$router->get('/store/staff/schedule', 'Staff\\ScheduleController@index', [AuthMiddleware::class]);
$router->post('/store/staff/schedule', 'Staff\\ScheduleController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// Time Clock
$router->get('/store/staff/timeclock', 'Staff\\TimeclockController@index', [AuthMiddleware::class]);
$router->post('/store/staff/timeclock/punch', 'Staff\\TimeclockController@punch', [AuthMiddleware::class, CsrfMiddleware::class]);

// Commissions
$router->get('/store/staff/commissions', 'Staff\\CommissionController@index', [AuthMiddleware::class]);
$router->get('/store/staff/commissions/{id}', 'Staff\\CommissionController@show', [AuthMiddleware::class]);

// =====================================================
// TRAINING COMPLETION (Admin)
// =====================================================
$router->get('/store/training', 'TrainingCompletionController@index', [AuthMiddleware::class]);
$router->post('/store/training/complete', 'TrainingCompletionController@complete', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// TRIPS (Admin - already has storefront routes)
// =====================================================
$router->get('/store/trips', 'Trips\\TripController@index', [AuthMiddleware::class]);
$router->get('/store/trips/create', 'Trips\\TripController@create', [AuthMiddleware::class]);
$router->post('/store/trips', 'Trips\\TripController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/trips/{id}/edit', 'Trips\\TripController@edit', [AuthMiddleware::class]);
$router->post('/store/trips/{id}', 'Trips\\TripController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// COURSES (Admin - already has storefront routes)
// =====================================================
$router->get('/store/courses', 'Courses\\CourseController@index', [AuthMiddleware::class]);
$router->get('/store/courses/create', 'Courses\\CourseController@create', [AuthMiddleware::class]);
$router->post('/store/courses', 'Courses\\CourseController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/courses/{id}/edit', 'Courses\\CourseController@edit', [AuthMiddleware::class]);
$router->post('/store/courses/{id}', 'Courses\\CourseController@update', [AuthMiddleware::class, CsrfMiddleware::class]);

// Course Schedule
$router->get('/store/courses/schedule', 'Courses\\ScheduleController@index', [AuthMiddleware::class]);
$router->post('/store/courses/schedule', 'Courses\\ScheduleController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// VENDOR CATALOG (Admin)
// =====================================================
$router->get('/store/vendors', 'VendorCatalogController@index', [AuthMiddleware::class]);
$router->get('/store/vendors/{id}', 'VendorCatalogController@show', [AuthMiddleware::class]);
$router->post('/store/vendors/sync', 'VendorCatalogController@sync', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// WAIVERS (Admin & Customer)
// =====================================================
$router->get('/waiver', 'WaiverController@index');
$router->get('/waiver/{id}', 'WaiverController@show');
$router->get('/waiver/{id}/sign', 'WaiverSigningController@show');
$router->post('/waiver/{id}/sign', 'WaiverSigningController@sign', [CsrfMiddleware::class]);
$router->get('/store/waivers', 'WaiverController@adminIndex', [AuthMiddleware::class]);
$router->get('/store/waivers/create', 'WaiverController@create', [AuthMiddleware::class]);
$router->post('/store/waivers', 'WaiverController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/waivers/{id}/signatures', 'WaiverController@signatures', [AuthMiddleware::class]);

// =====================================================
// WORK ORDERS (Admin)
// =====================================================
$router->get('/store/work-orders', 'WorkOrders\\WorkOrderController@index', [AuthMiddleware::class]);
$router->get('/store/work-orders/create', 'WorkOrders\\WorkOrderController@create', [AuthMiddleware::class]);
$router->post('/store/work-orders', 'WorkOrders\\WorkOrderController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/work-orders/{id}', 'WorkOrders\\WorkOrderController@show', [AuthMiddleware::class]);
$router->post('/store/work-orders/{id}/complete', 'WorkOrders\\WorkOrderController@complete', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// CRM (Admin)
// =====================================================
$router->get('/store/crm', 'CRM\\CustomerController@index', [AuthMiddleware::class]);
$router->get('/store/crm/customers/{id}', 'CRM\\CustomerController@show', [AuthMiddleware::class]);
$router->get('/store/crm/tags', 'CRM\\CustomerTagController@index', [AuthMiddleware::class]);
$router->post('/store/crm/tags', 'CRM\\CustomerTagController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// MARKETING (Admin)
// =====================================================
$router->get('/store/marketing', 'Marketing\\CampaignController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/campaigns/create', 'Marketing\\CampaignController@create', [AuthMiddleware::class]);
$router->post('/store/marketing/campaigns', 'Marketing\\CampaignController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/store/marketing/email', 'Marketing\\EmailController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/sms', 'Marketing\\SMSController@index', [AuthMiddleware::class]);
$router->get('/store/marketing/promotions', 'Marketing\\PromotionController@index', [AuthMiddleware::class]);

// =====================================================
// FINANCIAL (Admin)
// =====================================================
$router->get('/store/financial', 'Financial\\FinancialController@index', [AuthMiddleware::class]);
$router->get('/store/financial/dashboard', 'Financial\\FinancialController@dashboard', [AuthMiddleware::class]);
$router->get('/store/financial/transactions', 'Financial\\FinancialController@transactions', [AuthMiddleware::class]);

// =====================================================
// INTEGRATIONS (Admin)
// =====================================================
$router->get('/store/integrations', 'Integrations\\IntegrationController@index', [AuthMiddleware::class]);

// PADI Integration
$router->get('/store/integrations/padi', 'Integrations\\PADIController@index', [AuthMiddleware::class]);
$router->post('/store/integrations/padi/sync', 'Integrations\\PADIController@sync', [AuthMiddleware::class, CsrfMiddleware::class]);

// QuickBooks Integration
$router->get('/store/integrations/quickbooks', 'Integrations\\QuickBooksController@index', [AuthMiddleware::class]);
$router->get('/store/integrations/quickbooks/connect', 'Integrations\\QuickBooksController@connect', [AuthMiddleware::class]);
$router->post('/store/integrations/quickbooks/sync', 'Integrations\\QuickBooksController@sync', [AuthMiddleware::class, CsrfMiddleware::class]);

// Stripe Integration
$router->get('/store/integrations/stripe', 'Integrations\\StripeController@index', [AuthMiddleware::class]);
$router->post('/store/integrations/stripe/connect', 'Integrations\\StripeController@connect', [AuthMiddleware::class, CsrfMiddleware::class]);

// Mailchimp Integration
$router->get('/store/integrations/mailchimp', 'Integrations\\MailchimpController@index', [AuthMiddleware::class]);
$router->post('/store/integrations/mailchimp/sync', 'Integrations\\MailchimpController@sync', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// CMS (Admin & Public)
// =====================================================
$router->get('/blog', 'CMS\\BlogController@index');
$router->get('/blog/{slug}', 'CMS\\BlogController@show');
$router->get('/store/blog', 'CMS\\BlogController@adminIndex', [AuthMiddleware::class]);
$router->get('/store/blog/create', 'CMS\\BlogController@create', [AuthMiddleware::class]);
$router->post('/store/blog', 'CMS\\BlogController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/pages/{slug}', 'CMS\\PageController@show');
$router->get('/store/pages', 'CMS\\PageController@adminIndex', [AuthMiddleware::class]);
$router->get('/store/pages/create', 'CMS\\PageController@create', [AuthMiddleware::class]);
$router->post('/store/pages', 'CMS\\PageController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// ECOMMERCE (Admin)
// =====================================================
$router->get('/store/ecommerce', 'Ecommerce\\EcommerceController@index', [AuthMiddleware::class]);
$router->get('/store/ecommerce/settings', 'Ecommerce\\EcommerceController@settings', [AuthMiddleware::class]);

// =====================================================
// SHOP (Admin - storefront already exists)
// =====================================================
$router->get('/store/shop', 'Shop\\ShopController@index', [AuthMiddleware::class]);
$router->get('/store/shop/products', 'Shop\\ShopController@products', [AuthMiddleware::class]);
$router->get('/store/shop/orders', 'Shop\\ShopController@orders', [AuthMiddleware::class]);

// =====================================================
// APPOINTMENTS (Admin & Customer)
// =====================================================
$router->get('/appointments', 'AppointmentsController@index');
$router->post('/appointments/book', 'AppointmentsController@book', [CsrfMiddleware::class]);
$router->get('/store/appointments', 'AppointmentsController@adminIndex', [AuthMiddleware::class]);
$router->get('/account/appointments', 'AppointmentsController@customerIndex', [CustomerAuthMiddleware::class]);

// =====================================================
// ANALYTICS (Admin)
// =====================================================
$router->get('/store/analytics', 'AnalyticsController@index', [AuthMiddleware::class]);
$router->get('/store/analytics/dashboard', 'AnalyticsController@dashboard', [AuthMiddleware::class]);

// =====================================================
// AUDIT (Admin)
// =====================================================
$router->get('/store/audit', 'AuditController@index', [AuthMiddleware::class]);
$router->get('/store/audit/logs', 'Admin\\AuditLogController@index', [AuthMiddleware::class]);

// =====================================================
// COMMUNICATION (Admin)
// =====================================================
$router->get('/store/communication', 'CommunicationController@index', [AuthMiddleware::class]);
$router->post('/store/communication/send', 'CommunicationController@send', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// TENANT MANAGEMENT (SaaS Admin)
// =====================================================
$router->get('/saas/tenants', 'TenantController@index', [AuthMiddleware::class]);
$router->get('/saas/tenants/create', 'TenantController@create', [AuthMiddleware::class]);
$router->post('/saas/tenants', 'TenantController@store', [AuthMiddleware::class, CsrfMiddleware::class]);

// =====================================================
// SSO (Single Sign-On)
// =====================================================
$router->get('/sso/login', 'SSOController@login');
$router->get('/sso/callback', 'SSOController@callback');
$router->post('/sso/logout', 'SSOController@logout');
