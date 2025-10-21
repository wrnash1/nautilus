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

// Auto-redirect logic for installation and login
$path = parse_url($uri, PHP_URL_PATH);

// Skip auto-redirect for install routes, login routes, and assets
$skipPaths = ['/install', '/login', '/logout', '/assets', '/api'];
$shouldSkip = false;
foreach ($skipPaths as $skipPath) {
    if (strpos($path, $skipPath) === 0) {
        $shouldSkip = true;
        break;
    }
}

if (!$shouldSkip) {
    // Check if accessing root or any non-excluded path
    require_once __DIR__ . '/../app/Services/Install/InstallService.php';
    $installService = new App\Services\Install\InstallService();

    if (!$installService->isInstalled()) {
        // Database not set up - redirect to install
        if ($path !== '/install') {
            header('Location: /install');
            exit;
        }
    } else {
        // Database is set up - check if user is logged in
        if (!isset($_SESSION['user_id']) && $path === '/') {
            // Not logged in and accessing root - redirect to login
            header('Location: /login');
            exit;
        }
    }
}

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
