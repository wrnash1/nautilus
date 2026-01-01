<?php

/**
 * Nautilus Store - Internal Staff Management Application
 * Entry Point
 */

// Define base path constant for the application
define('BASE_PATH', dirname(__DIR__));

// Check if application is installed - .env must exist and have content
$envExists = file_exists(BASE_PATH . '/.env') && filesize(BASE_PATH . '/.env') > 0;

if (!$envExists) {
    // If either file is missing, redirect to installer
    // This prevents redirect loops from partial installations
    $scriptName = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    if ($scriptName !== 'install.php' && $scriptName !== 'run_migrations.php' && $scriptName !== 'run_migrations_backend.php') {
        header('Location: /install.php');
        exit;
    }
}


// Load environment variables
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env - already checked above
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    error_log("Failed to load .env: " . $e->getMessage());
}

// Enable Error Reporting for Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Core\Router;

// Initialize Database
try {
    \App\Core\Database::init();
} catch (\Exception $e) {
    error_log("Database Init Failed: " . $e->getMessage());
    die("Database Error: " . $e->getMessage());
}

// Set error reporting based on environment
// FORCE DEBUGGING ON
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/New_York');

// Start session
session_save_path(sys_get_temp_dir());
session_start();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// ============================================
// CHECK IF APPLICATION IS INSTALLED
// ============================================


// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Dispatch request
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    $router->dispatch($requestMethod, $requestUri);
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<h1>CRITICAL ERROR (Caught in index.php)</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " on line " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
