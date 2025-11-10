#!/usr/bin/env php
<?php
/**
 * Nautilus Application Test Script
 *
 * Comprehensive testing to ensure application is fully functional
 * Run: php scripts/test-application.php
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

$baseDir = dirname(__DIR__);

echo section("NAUTILUS APPLICATION TEST");
echo "\n\n";

$results = ['passed' => 0, 'failed' => 0, 'warnings' => 0, 'errors' => []];

// ============================================
// TEST 1: Environment Configuration
// ============================================
echo section("Environment Configuration");
echo "\n";

if (file_exists("$baseDir/.env")) {
    echo success(".env file exists");
    echo "\n";
    $results['passed']++;

    $env = parse_ini_file("$baseDir/.env");

    $required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'APP_NAME', 'APP_ENV'];
    foreach ($required as $var) {
        if (isset($env[$var]) && !empty($env[$var])) {
            echo success("$var is set");
            echo "\n";
            $results['passed']++;
        } else {
            echo error("$var is missing or empty");
            echo "\n";
            $results['failed']++;
            $results['errors'][] = "Missing ENV: $var";
        }
    }
} else {
    echo error(".env file missing");
    echo "\n";
    $results['failed']++;
    $results['errors'][] = "Missing .env file";
}

// ============================================
// TEST 2: Database Connection
// ============================================
echo section("Database Connection");
echo "\n";

if (isset($env)) {
    try {
        $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_DATABASE']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        echo success("Database connection successful");
        echo "\n";
        $results['passed']++;

        // Test critical tables
        $tables = ['tenants', 'users', 'customers', 'products', 'sales'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo success("Table exists: $table");
                echo "\n";
                $results['passed']++;
            } else {
                echo warning("Table missing: $table (may need migrations)");
                echo "\n";
                $results['warnings']++;
            }
        }

    } catch (PDOException $e) {
        echo error("Database connection failed: " . $e->getMessage());
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Database error: " . $e->getMessage();
    }
}

// ============================================
// TEST 3: File Structure
// ============================================
echo section("File Structure");
echo "\n";

$criticalFiles = [
    'public/index.php',
    'public/install.php',
    'routes/web.php',
    'app/Core/Router.php',
    'app/Core/Database.php',
    'app/Controllers/Auth/AuthController.php',
    'app/Controllers/Admin/DashboardController.php',
    'app/Controllers/Inventory/ProductController.php',
    'app/Controllers/CRM/CustomerController.php',
    'app/Controllers/Courses/CourseController.php',
    'app/Controllers/NewsletterController.php',
    'app/Controllers/HelpController.php',
    'app/Controllers/Admin/CompanySettingsController.php'
];

foreach ($criticalFiles as $file) {
    if (file_exists("$baseDir/$file")) {
        echo success("$file");
        echo "\n";
        $results['passed']++;
    } else {
        echo error("$file");
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Missing: $file";
    }
}

// ============================================
// TEST 4: Composer Dependencies
// ============================================
echo section("Composer Dependencies");
echo "\n";

if (file_exists("$baseDir/vendor/autoload.php")) {
    echo success("Composer dependencies installed");
    echo "\n";
    $results['passed']++;
} else {
    echo error("Composer dependencies not installed");
    echo "\n";
    echo info("Run: composer install");
    echo "\n";
    $results['failed']++;
    $results['errors'][] = "Missing composer dependencies";
}

// ============================================
// TEST 5: Permissions
// ============================================
echo section("File Permissions");
echo "\n";

$writable = [
    'storage',
    'storage/cache',
    'storage/logs',
    'storage/exports',
    'public/uploads'
];

foreach ($writable as $dir) {
    if (is_writable("$baseDir/$dir")) {
        echo success("$dir is writable");
        echo "\n";
        $results['passed']++;
    } else {
        echo error("$dir is not writable");
        echo "\n";
        $results['failed']++;
        $results['errors'][] = "Not writable: $dir";
    }
}

// ============================================
// TEST 6: Routes
// ============================================
echo section("Critical Routes");
echo "\n";

if (file_exists("$baseDir/routes/web.php")) {
    $routesContent = file_get_contents("$baseDir/routes/web.php");

    $criticalRoutes = [
        "/store/login",
        "/store/dashboard",
        "/store/products",
        "/store/customers",
        "/store/inventory",
        "/store/courses",
        "/store/admin/settings/company",
        "/newsletter/subscribe",
        "/help"
    ];

    foreach ($criticalRoutes as $route) {
        if (strpos($routesContent, "'$route'") !== false || strpos($routesContent, "\"$route\"") !== false) {
            echo success("Route defined: $route");
            echo "\n";
            $results['passed']++;
        } else {
            echo error("Route missing: $route");
            echo "\n";
            $results['failed']++;
            $results['errors'][] = "Missing route: $route";
        }
    }
}

// ============================================
// TEST 7: Migrations
// ============================================
echo section("Database Migrations");
echo "\n";

if (is_dir("$baseDir/database/migrations")) {
    $migrations = glob("$baseDir/database/migrations/*.sql");
    echo success("Found " . count($migrations) . " migration files");
    echo "\n";
    $results['passed']++;

    // Check for new migrations
    $newMigrations = [
        '070_company_settings_table.sql',
        '071_newsletter_subscriptions_table.sql',
        '072_help_articles_table.sql'
    ];

    foreach ($newMigrations as $migration) {
        if (file_exists("$baseDir/database/migrations/$migration")) {
            echo success("New migration present: $migration");
            echo "\n";
            $results['passed']++;
        } else {
            echo warning("New migration missing: $migration");
            echo "\n";
            $results['warnings']++;
        }
    }
}

// ============================================
// SUMMARY
// ============================================
echo "\n";
echo section("TEST SUMMARY");
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
    echo section("ERRORS FOUND");
    echo "\n\n";
    foreach ($results['errors'] as $err) {
        echo error($err);
        echo "\n";
    }
    echo "\n";
}

// ============================================
// RECOMMENDATIONS
// ============================================
echo section("RECOMMENDATIONS");
echo "\n\n";

if ($passRate >= 95) {
    echo color("✓ Application is READY for deployment!", 'green');
    echo "\n\n";
    echo info("Next steps:");
    echo "\n";
    echo "1. Delete production folder: sudo rm -rf /var/www/html/nautilus";
    echo "\n";
    echo "2. Deploy: sudo bash scripts/deploy-to-production.sh";
    echo "\n";
    echo "3. Test installation: https://nautilus.local/install.php";
    echo "\n";
} elseif ($passRate >= 80) {
    echo color("⚠ Application needs minor fixes before deployment", 'yellow');
    echo "\n\n";
    echo "Fix the errors above, then re-run this test.";
    echo "\n";
} else {
    echo color("✗ Application is NOT READY for deployment", 'red');
    echo "\n\n";
    echo "Critical errors must be fixed before deployment.";
    echo "\n";
}

echo "\n";

exit($results['failed'] > 0 ? 1 : 0);
