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
        
    }, [new ApiAuthMiddleware()]);
});
