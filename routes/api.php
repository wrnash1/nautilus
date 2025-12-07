<?php

use App\Middleware\ApiAuthMiddleware;

$router->group('/api/v1', function($router) {
    
    $router->post('/auth/login', 'API\AuthController@login');
    $router->post('/auth/register', 'API\AuthController@register');
    
    $router->group('', function($router) {
        
        $router->get('/customers', 'API\CustomerController@index');
        $router->get('/customers/:id', 'API\CustomerController@show');
        $router->post('/customers', 'API\CustomerController@store');
        $router->put('/customers/:id', 'API\CustomerController@update');
        $router->delete('/customers/:id', 'API\CustomerController@destroy');
        
        $router->get('/products', 'API\ProductController@index');
        $router->get('/products/:id', 'API\ProductController@show');
        $router->post('/products', 'API\ProductController@store');
        $router->put('/products/:id', 'API\ProductController@update');
        $router->delete('/products/:id', 'API\ProductController@destroy');
        
        $router->get('/transactions', 'API\TransactionController@index');
        $router->get('/transactions/:id', 'API\TransactionController@show');
        $router->post('/transactions', 'API\TransactionController@store');
        
        $router->get('/orders', 'API\OrderController@index');
        $router->get('/orders/:id', 'API\OrderController@show');
        $router->post('/orders', 'API\OrderController@store');
        $router->put('/orders/:id', 'API\OrderController@update');
        
        $router->get('/rentals', 'API\RentalController@index');
        $router->get('/rentals/:id', 'API\RentalController@show');
        $router->post('/rentals', 'API\RentalController@store');
        $router->put('/rentals/:id/checkin', 'API\RentalController@checkin');
        
        $router->get('/courses', 'API\CourseController@index');
        $router->get('/courses/:id', 'API\CourseController@show');
        $router->post('/courses/:id/enroll', 'API\CourseController@enroll');

        // Dashboard Widgets
        $router->get('/dashboard/widgets', 'DashboardController@getData');
        $router->post('/dashboard/widgets', 'DashboardController@addWidget');
        $router->put('/dashboard/widgets/:id', 'DashboardController@updateWidget');
        $router->delete('/dashboard/widgets/:id', 'DashboardController@removeWidget');
        $router->post('/dashboard/widgets/reorder', 'DashboardController@reorderWidgets');

        // Search
        $router->get('/search', 'SearchController@search');
        $router->get('/search/products', 'SearchController@searchProducts');
        $router->get('/search/customers', 'SearchController@searchCustomers');
        $router->get('/search/transactions', 'SearchController@searchTransactions');
        $router->get('/search/suggestions', 'SearchController@suggestions');
        $router->get('/search/recent', 'SearchController@recentSearches');
        $router->get('/search/popular', 'SearchController@popularSearches');

        // Audit Trail
        $router->get('/audit', 'AuditController@index');
        $router->get('/audit/entity/:type/:id', 'AuditController@entityHistory');
        $router->get('/audit/statistics', 'AuditController@statistics');
        $router->get('/audit/security-events', 'AuditController@securityEvents');
        $router->get('/audit/failed-logins', 'AuditController@failedLogins');
        $router->get('/audit/user-activity/:id', 'AuditController@userActivity');
        $router->get('/audit/export', 'AuditController@export');

        // Notification Preferences
        $router->get('/notifications/preferences', 'NotificationPreferenceController@index');
        $router->put('/notifications/preferences/:id', 'NotificationPreferenceController@update');
        $router->post('/notifications/preferences/bulk', 'NotificationPreferenceController@bulkUpdate');
        $router->get('/notifications/history', 'NotificationPreferenceController@history');
        $router->post('/notifications/disable-all', 'NotificationPreferenceController@disableAll');
        $router->post('/notifications/enable-all', 'NotificationPreferenceController@enableAll');

        // Backup Management
        $router->post('/backups/database', 'BackupController@createDatabaseBackup');
        $router->post('/backups/files', 'BackupController@createFileBackup');
        $router->get('/backups', 'BackupController@listBackups');
        $router->delete('/backups/:id', 'BackupController@deleteBackup');
        $router->post('/backups/:id/restore', 'BackupController@restoreBackup');

        // Customer Portal (for staff managing portal access)
        $router->get('/customer-portal/access/:customerId', 'CustomerPortalAdminController@getAccess');
        $router->post('/customer-portal/access', 'CustomerPortalAdminController@createAccess');
        $router->put('/customer-portal/access/:id', 'CustomerPortalAdminController@updateAccess');
        $router->delete('/customer-portal/access/:id', 'CustomerPortalAdminController@revokeAccess');

    }, [new ApiAuthMiddleware()]);
});

// Customer Portal Public API (separate auth)
$router->group('/api/portal', function($router) {

    $router->post('/auth/login', 'CustomerPortal\AuthController@login');
    $router->post('/auth/forgot-password', 'CustomerPortal\AuthController@forgotPassword');
    $router->post('/auth/reset-password', 'CustomerPortal\AuthController@resetPassword');

    $router->group('', function($router) {

        $router->get('/dashboard', 'CustomerPortal\DashboardController@index');
        $router->get('/purchases', 'CustomerPortal\PurchaseController@history');
        $router->get('/purchases/:id', 'CustomerPortal\PurchaseController@show');
        $router->get('/courses', 'CustomerPortal\CourseController@enrollments');
        $router->post('/courses/:id/enroll', 'CustomerPortal\CourseController@requestEnrollment');
        $router->get('/rentals', 'CustomerPortal\RentalController@history');
        $router->get('/certifications', 'CustomerPortal\CertificationController@index');
        $router->get('/profile', 'CustomerPortal\ProfileController@show');
        $router->put('/profile', 'CustomerPortal\ProfileController@update');
        $router->get('/notifications', 'CustomerPortal\NotificationController@index');
        $router->put('/notifications/:id/read', 'CustomerPortal\NotificationController@markRead');
        $router->get('/support-tickets', 'CustomerPortal\SupportController@index');
        $router->post('/support-tickets', 'CustomerPortal\SupportController@create');
        $router->get('/support-tickets/:id', 'CustomerPortal\SupportController@show');
        $router->post('/support-tickets/:id/messages', 'CustomerPortal\SupportController@addMessage');

    }, [new App\Middleware\CustomerPortalAuthMiddleware()]);
});
