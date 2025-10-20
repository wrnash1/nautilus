<?php

// Define base path constant
define('BASE_PATH', dirname(__DIR__));

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

session_start();

error_reporting($_ENV['APP_DEBUG'] === 'true' ? E_ALL : 0);
ini_set('display_errors', $_ENV['APP_DEBUG'] === 'true' ? '1' : '0');

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

$router = require __DIR__ . '/../routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    $router->dispatch($method, $uri);
} catch (Exception $e) {
    error_log($e->getMessage());
    
    http_response_code(500);
    
    if ($_ENV['APP_DEBUG'] === 'true') {
        echo "<h1>Error</h1>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo "<h1>500 Internal Server Error</h1>";
    }
}
