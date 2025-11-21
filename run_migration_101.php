#!/usr/bin/env php
<?php
/**
 * Run Migration 101 - Comprehensive Database Fixes
 * 
 * This script runs migration 101 which fixes all database issues from earlier migrations.
 * 
 * Usage: php run_migration_101.php
 */

// Define base path
define('BASE_PATH', __DIR__);

// Load environment
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  NAUTILUS - Migration 101: Comprehensive Database Fixes     ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

try {
    // Connect to database
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'nautilus';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    
    echo "📊 Connecting to database: {$dbname}@{$host}...\n";
    
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connection established!\n\n";
    
    // Check if migration 101 already ran
    echo "🔍 Checking if migration 101 has already been run...\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM migrations WHERE filename LIKE '%101%' AND status = 'completed'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "⚠️  Migration 101 has already been completed!\n";
        echo "    If you want to re-run it, delete the migration record first.\n\n";
        
        $response = readline("Do you want to re-run migration 101? (yes/no): ");
        if (strtolower(trim($response)) !== 'yes') {
            echo "\n❌ Migration cancelled.\n\n";
            exit(0);
        }
        
        echo "\n🔄 Re-running migration 101...\n\n";
    }
    
    // Load migration file
    $migrationFile = __DIR__ . '/database/migrations/101_comprehensive_database_fixes.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: {$migrationFile}");
    }
    
    echo "📄 Loading migration file...\n";
    $sql = file_get_contents($migrationFile);
    
    echo "⚙️  Executing migration 101...\n";
    echo "    This may take a few moments...\n\n";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    $warnings = [];
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Show progress every 10 statements
            if ($successCount % 10 === 0) {
                echo "   ✓ {$successCount} statements executed...\n";
            }
        } catch (PDOException $e) {
            // Some errors are expected (e.g., "column already exists")
            $errorMsg = $e->getMessage();
            
            // These are acceptable warnings, not errors
            if (
                strpos($errorMsg, 'Duplicate column') !== false ||
                strpos($errorMsg, 'already exists') !== false ||
                strpos($errorMsg, 'Duplicate key') !== false
            ) {
                $warnings[] = $errorMsg;
            } else {
                // Real error
                $errorCount++;
                echo "   ❌ Error in statement " . ($index + 1) . ": " . $errorMsg . "\n";
            }
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "📊 MIGRATION RESULTS:\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ Successful statements: {$successCount}\n";
    echo "⚠️  Warnings (expected):  " . count($warnings) . "\n";
    echo "❌ Errors:                {$errorCount}\n";
    echo "\n";
    
    if ($errorCount === 0) {
        echo "🎉 Migration 101 completed successfully!\n\n";
        
        // Record migration as completed
        $stmt = $pdo->prepare("
            INSERT INTO migrations (filename, status, executed_at)
            VALUES ('101_comprehensive_database_fixes.sql', 'completed', NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', executed_at = NOW()
        ");
        $stmt->execute();
        
        // Get final statistics
        echo "📊 DATABASE STATISTICS:\n";
        echo "───────────────────────────────────────────────────────────────\n";
        
        $stats = [
            'Total Tables' => "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()",
            'System Settings' => "SELECT COUNT(*) FROM system_settings",
            'Tenants' => "SELECT COUNT(*) FROM tenants",
            'Customer Tags' => "SELECT COUNT(*) FROM customer_tags",
            'Users' => "SELECT COUNT(*) FROM users",
            'Customers' => "SELECT COUNT(*) FROM customers",
            'Products' => "SELECT COUNT(*) FROM products"
        ];
        
        foreach ($stats as $label => $query) {
            try {
                $count = $pdo->query($query)->fetchColumn();
                echo sprintf("%-20s: %d\n", $label, $count);
            } catch (Exception $e) {
                echo sprintf("%-20s: N/A\n", $label);
            }
        }
        
        echo "\n";
        echo "✅ All database fixes have been applied!\n";
        echo "✅ Multi-tenant support is now properly configured!\n";
        echo "✅ System settings table is populated!\n";
        echo "\n";
        
    } else {
        echo "⚠️  Migration completed with {$errorCount} errors.\n";
        echo "    Please review the errors above and fix them manually.\n\n";
        
        // Record migration as failed
        $stmt = $pdo->prepare("
            INSERT INTO migrations (filename, status, error_message, executed_at)
            VALUES ('101_comprehensive_database_fixes.sql', 'failed', ?, NOW())
            ON DUPLICATE KEY UPDATE status = 'failed', error_message = ?, executed_at = NOW()
        ");
        $stmt->execute(["{$errorCount} errors", "{$errorCount} errors"]);
    }
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n";
    echo "❌ FATAL ERROR:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "\n";
    exit(1);
}
