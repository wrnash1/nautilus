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
$router->get('/products/{id}', 'Inventory\ProductController@show', [AuthMiddleware::class]);

$router->get('/rentals', 'Rentals\RentalController@index', [AuthMiddleware::class]);

$router->get('/courses', 'Courses\CourseController@index', [AuthMiddleware::class]);

$router->get('/orders', 'Ecommerce\OrderController@index', [AuthMiddleware::class]);
$router->get('/orders/{id}', 'Ecommerce\OrderController@show', [AuthMiddleware::class]);

return $router;
