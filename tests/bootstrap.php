<?php
/**
 * PHPUnit Bootstrap File
 * Initializes the testing environment
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables for testing
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Override environment for testing
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_DATABASE'] = $_ENV['DB_TEST_DATABASE'] ?? 'nautilus_test';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/New_York');

// Define base path
define('BASE_PATH', dirname(__DIR__));
