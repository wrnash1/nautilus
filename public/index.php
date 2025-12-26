<?php

/**
 * Nautilus Store - Internal Staff Management Application
 * Entry Point
 */

// Define base path constant for the application
define('BASE_PATH', dirname(__DIR__));

// Check if application is installed - BOTH files must exist and .installed must have content
$envExists = file_exists(BASE_PATH . '/.env');
$installedExists = file_exists(BASE_PATH . '/.installed');
$installedValid = $installedExists && filesize(BASE_PATH . '/.installed') > 0;


if (!$envExists || !$installedValid) {
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

// Set error reporting based on environment
// FORCE DEBUGGING ON
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/New_York');

// Start session
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
$installedFile = __DIR__ . '/../.installed';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$scriptName = basename($_SERVER['SCRIPT_FILENAME'] ?? '');

// Additional check: if not installed and not accessing installer
// This is redundant now but kept for safety
/*
if (!file_exists($installedFile) && $scriptName !== 'install_streamlined.php' && $scriptName !== 'install.php' && $scriptName !== 'run_migrations.php' && $scriptName !== 'run_migrations_backend.php') {
    header('Location: /install_streamlined.php');
    exit;
}
*/

// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Dispatch request
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestMethod, $requestUri);
