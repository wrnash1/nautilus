#!/usr/bin/env php
<?php
/**
 * Nautilus System Diagnostic Script
 *
 * Tests what actually works vs what's broken
 * Run: php scripts/diagnostic-test.php
 *
 * Usage:
 *   php scripts/diagnostic-test.php              # Test development
 *   php scripts/diagnostic-test.php --prod       # Test production
 */

// Color output functions
function color($text, $color) {
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'cyan' => "\033[36m",
        'reset' => "\033[0m"
    ];
    return $colors[$color] . $text . $colors['reset'];
}

function success($text) { return color("✓ $text", 'green'); }
function error($text) { return color("✗ $text", 'red'); }
function warning($text) { return color("⚠ $text", 'yellow'); }
function info($text) { return color("ℹ $text", 'cyan'); }
function section($text) { return color("\n═══ $text ═══", 'blue'); }

// Determine which environment to test
$isProd = in_array('--prod', $argv);
$baseDir = $isProd ? '/var/www/html/nautilus' : dirname(__DIR__);

echo section("NAUTILUS DIAGNOSTIC TEST");
echo "\n";
echo info("Testing: " . ($isProd ? "PRODUCTION" : "DEVELOPMENT"));
echo "\n";
echo info("Path: $baseDir");
echo "\n\n";

$results = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'errors' => []
];

// ============================================
// TEST 1: File System Structure
// ============================================
echo section("File System Structure");
echo "\n";

$requiredDirs = [
    'app',
    'app/Controllers',
    'app/Controllers/Admin',
    'app/Core',
    'app/Models',
    'app/Services',
    'database',
    'database/migrations',
    'public',
    'routes',
    'storage',
    'storage/cache',
    'storage/logs',
    'storage/exports',
    'public/uploads',
    'vendor'
];

foreach ($requiredDirs as $dir) {
    $path = "$baseDir/$dir";
    if (is_dir($path)) {
        echo success("Directory exists: $dir");
        echo "\n";
        $results['passed']++;
    } else {
        echo error("Directory missing: $dir");
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Missing directory: $dir";
    }
}

$requiredFiles = [
    'public/index.php',
    'routes/web.php',
    'app/Core/Router.php',
    'app/Core/Database.php',
    'composer.json',
    'install.php',
    '.env.example'
];

foreach ($requiredFiles as $file) {
    $path = "$baseDir/$file";
    if (file_exists($path)) {
        echo success("File exists: $file");
        echo "\n";
        $results['passed']++;
    } else {
        echo error("File missing: $file");
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Missing file: $file";
    }
}

// ============================================
// TEST 2: Environment Configuration
// ============================================
echo section("Environment Configuration");
echo "\n";

if (file_exists("$baseDir/.env")) {
    echo success(".env file exists");
    echo "\n";
    $results['passed']++;

    // Load .env
    $envContent = file_get_contents("$baseDir/.env");
    $requiredEnvVars = [
        'APP_NAME',
        'APP_ENV',
        'APP_TIMEZONE',
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS'
    ];

    foreach ($requiredEnvVars as $var) {
        if (preg_match("/^$var=/m", $envContent)) {
            echo success("ENV variable set: $var");
            echo "\n";
            $results['passed']++;
        } else {
            echo error("ENV variable missing: $var");
            echo "\n";
            $results['failed']++;
            $results['errors'][] = "Missing ENV variable: $var";
        }
    }
} else {
    echo error(".env file does not exist");
    echo "\n";
    echo warning("You need to copy .env.example to .env");
    echo "\n";
    $results['failed']++;
    $results['errors'][] = "Missing .env file";
}

// Check if installed
if (file_exists("$baseDir/.installed")) {
    echo success("Installation marker exists (.installed)");
    echo "\n";
    $results['passed']++;
} else {
    echo warning("Installation marker missing (.installed)");
    echo "\n";
    echo info("Application may not be installed yet");
    echo "\n";
    $results['warnings']++;
}

// ============================================
// TEST 3: File Permissions
// ============================================
echo section("File Permissions");
echo "\n";

$writableDirs = [
    'storage',
    'storage/cache',
    'storage/logs',
    'storage/exports',
    'storage/backups',
    'public/uploads'
];

foreach ($writableDirs as $dir) {
    $path = "$baseDir/$dir";
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo success("Writable: $dir");
            echo "\n";
            $results['passed']++;
        } else {
            echo error("Not writable: $dir");
            echo "\n";
            $results['failed']++;
            $results['errors'][] = "Directory not writable: $dir";
        }
    }
}

// Check .env permissions
if (file_exists("$baseDir/.env")) {
    $perms = fileperms("$baseDir/.env");
    $octal = substr(sprintf('%o', $perms), -3);
    if ($octal <= 644) {
        echo success(".env permissions secure: $octal");
        echo "\n";
        $results['passed']++;
    } else {
        echo warning(".env permissions too open: $octal");
        echo "\n";
        $results['warnings']++;
    }
}

// ============================================
// TEST 4: Database Connectivity
// ============================================
echo section("Database Connectivity");
echo "\n";

if (file_exists("$baseDir/.env")) {
    $env = parse_ini_file("$baseDir/.env");

    try {
        $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        echo success("Database connection successful");
        echo "\n";
        echo info("Host: {$env['DB_HOST']}");
        echo "\n";
        echo info("Database: {$env['DB_NAME']}");
        echo "\n";
        $results['passed']++;

        // Check database tables
        echo "\n";
        echo info("Checking database tables...");
        echo "\n";

        $requiredTables = [
            'tenants',
            'users',
            'customers',
            'products',
            'inventory',
            'sales',
            'sale_items',
            'courses',
            'course_schedules',
            'dive_trips',
            'rentals',
            'air_fills',
            'waivers',
            'dive_sites',
            'serial_numbers'
        ];

        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($requiredTables as $table) {
            if (in_array($table, $existingTables)) {
                echo success("Table exists: $table");
                echo "\n";
                $results['passed']++;
            } else {
                echo error("Table missing: $table");
                echo "\n";
                $results['failed']++;
                $results['errors'][] = "Missing database table: $table";
            }
        }

        // Check for new feature tables
        $newTables = [
            'company_settings',
            'newsletter_subscriptions',
            'help_articles'
        ];

        echo "\n";
        echo info("Checking new feature tables...");
        echo "\n";

        foreach ($newTables as $table) {
            if (in_array($table, $existingTables)) {
                echo success("New table exists: $table");
                echo "\n";
                $results['passed']++;
            } else {
                echo warning("New table not created yet: $table");
                echo "\n";
                echo info("Run migrations 070, 071, 072 to add this");
                echo "\n";
                $results['warnings']++;
            }
        }

    } catch (PDOException $e) {
        echo error("Database connection failed");
        echo "\n";
        echo error("Error: " . $e->getMessage());
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Database connection failed: " . $e->getMessage();
    }
} else {
    echo error("Cannot test database - .env missing");
    echo "\n";
    $results['failed']++;
}

// ============================================
// TEST 5: Controllers Exist
// ============================================
echo section("Controllers");
echo "\n";

$requiredControllers = [
    'app/Controllers/HomeController.php',
    'app/Controllers/AuthController.php',
    'app/Controllers/DashboardController.php',
    'app/Controllers/SalesController.php',
    'app/Controllers/ProductController.php',
    'app/Controllers/CustomerController.php',
    'app/Controllers/InventoryController.php',
    'app/Controllers/CourseController.php',
    'app/Controllers/RentalController.php',
    'app/Controllers/AirFillController.php',
    'app/Controllers/WaiverController.php',
    'app/Controllers/DiveSiteController.php',
    'app/Controllers/SerialNumberController.php',
    'app/Controllers/Admin/CompanySettingsController.php',
    'app/Controllers/NewsletterController.php',
    'app/Controllers/HelpController.php'
];

foreach ($requiredControllers as $controller) {
    $path = "$baseDir/$controller";
    if (file_exists($path)) {
        echo success("Controller exists: " . basename($controller));
        echo "\n";
        $results['passed']++;
    } else {
        echo error("Controller missing: " . basename($controller));
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Missing controller: $controller";
    }
}

// ============================================
// TEST 6: Routes Configuration
// ============================================
echo section("Routes Configuration");
echo "\n";

if (file_exists("$baseDir/routes/web.php")) {
    $routesContent = file_get_contents("$baseDir/routes/web.php");

    $criticalRoutes = [
        "/store/login" => "Login route",
        "/store/dashboard" => "Dashboard route",
        "/store/sales" => "Sales route",
        "/store/products" => "Products route",
        "/store/customers" => "Customers route",
        "/store/inventory" => "Inventory route",
        "/store/courses" => "Courses route",
        "/store/rentals" => "Rentals route",
        "/store/air-fills" => "Air fills route",
        "/store/waivers" => "Waivers route",
        "/store/dive-sites" => "Dive sites route",
        "/store/serial-numbers/scan" => "Serial scanner route",
        "/store/admin/settings/company" => "Company settings route",
        "/newsletter/subscribe" => "Newsletter route",
        "/help" => "Help center route"
    ];

    foreach ($criticalRoutes as $route => $description) {
        if (strpos($routesContent, $route) !== false) {
            echo success("Route defined: $description");
            echo "\n";
            $results['passed']++;
        } else {
            echo error("Route missing: $description");
            echo "\n";
            $results['failed']++;
            $results['errors'][] = "Missing route: $description";
        }
    }
} else {
    echo error("routes/web.php not found");
    echo "\n";
    $results['failed']++;
}

// ============================================
// TEST 7: Migrations Status
// ============================================
echo section("Database Migrations");
echo "\n";

$migrationsDir = "$baseDir/database/migrations";
if (is_dir($migrationsDir)) {
    $migrations = glob("$migrationsDir/*.sql");
    echo success("Found " . count($migrations) . " migration files");
    echo "\n";
    $results['passed']++;

    // Check for latest migrations
    $latestMigrations = [
        '070_company_settings_table.sql',
        '071_newsletter_subscriptions_table.sql',
        '072_help_articles_table.sql'
    ];

    foreach ($latestMigrations as $migration) {
        if (file_exists("$migrationsDir/$migration")) {
            echo success("New migration ready: $migration");
            echo "\n";
            $results['passed']++;
        } else {
            echo error("Migration missing: $migration");
            echo "\n";
            $results['failed']++;
            $results['errors'][] = "Missing migration: $migration";
        }
    }
} else {
    echo error("Migrations directory not found");
    echo "\n";
    $results['failed']++;
}

// ============================================
// TEST 8: Composer Dependencies
// ============================================
echo section("Composer Dependencies");
echo "\n";

if (file_exists("$baseDir/vendor/autoload.php")) {
    echo success("Composer dependencies installed");
    echo "\n";
    $results['passed']++;

    // Check for key dependencies
    $keyPackages = [
        'vlucas/phpdotenv',
        'phpmailer/phpmailer'
    ];

    $composerLock = "$baseDir/composer.lock";
    if (file_exists($composerLock)) {
        $lockContent = file_get_contents($composerLock);
        foreach ($keyPackages as $package) {
            if (strpos($lockContent, $package) !== false) {
                echo success("Package installed: $package");
                echo "\n";
                $results['passed']++;
            } else {
                echo warning("Package missing: $package");
                echo "\n";
                $results['warnings']++;
            }
        }
    }
} else {
    echo error("Composer dependencies not installed");
    echo "\n";
    echo info("Run: composer install");
    echo "\n";
    $results['failed']++;
    $results['errors'][] = "Composer dependencies not installed";
}

// ============================================
// TEST 9: Apache/Web Server Configuration
// ============================================
echo section("Web Server Configuration");
echo "\n";

if (file_exists("$baseDir/public/.htaccess")) {
    echo success(".htaccess file exists");
    echo "\n";
    $results['passed']++;

    $htaccess = file_get_contents("$baseDir/public/.htaccess");
    if (strpos($htaccess, 'RewriteEngine On') !== false) {
        echo success("URL rewriting enabled");
        echo "\n";
        $results['passed']++;
    } else {
        echo error("URL rewriting not configured");
        echo "\n";
        $results['failed']++;
    }
} else {
    echo error(".htaccess file missing");
    echo "\n";
    $results['failed']++;
    $results['errors'][] = "Missing .htaccess file";
}

// ============================================
// FINAL SUMMARY
// ============================================
echo "\n";
echo section("DIAGNOSTIC SUMMARY");
echo "\n\n";

$total = $results['passed'] + $results['failed'] + $results['warnings'];
$passRate = $total > 0 ? round(($results['passed'] / $total) * 100, 1) : 0;

echo success("Passed: {$results['passed']}");
echo "\n";
echo error("Failed: {$results['failed']}");
echo "\n";
echo warning("Warnings: {$results['warnings']}");
echo "\n";
echo info("Pass Rate: $passRate%");
echo "\n\n";

if ($results['failed'] > 0) {
    echo section("CRITICAL ERRORS FOUND");
    echo "\n\n";
    foreach ($results['errors'] as $error) {
        echo error($error);
        echo "\n";
    }
    echo "\n";
}

// ============================================
// RECOMMENDATIONS
// ============================================
echo section("RECOMMENDATIONS");
echo "\n\n";

if (!file_exists("$baseDir/.env")) {
    echo "1. " . warning("Create .env file from .env.example");
    echo "\n";
}

if (!file_exists("$baseDir/vendor/autoload.php")) {
    echo "2. " . warning("Run: composer install");
    echo "\n";
}

if ($results['failed'] > 0) {
    echo "3. " . warning("Fix all critical errors before deployment");
    echo "\n";
}

if (isset($pdo) && !in_array('company_settings', $existingTables ?? [])) {
    echo "4. " . warning("Run new migrations (070, 071, 072)");
    echo "\n";
}

if (!$isProd) {
    echo "5. " . info("Deploy to production: sudo bash scripts/deploy-to-production.sh");
    echo "\n";
}

echo "\n";

if ($passRate >= 90) {
    echo color("✓ System Status: READY FOR PRODUCTION", 'green');
} elseif ($passRate >= 70) {
    echo color("⚠ System Status: NEEDS ATTENTION", 'yellow');
} else {
    echo color("✗ System Status: NOT READY", 'red');
}

echo "\n\n";

// Exit code
exit($results['failed'] > 0 ? 1 : 0);
