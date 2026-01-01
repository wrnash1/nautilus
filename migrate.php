<?php

// Force error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

echo "Migration Utility Started...\n";

// Load environment and init DB
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Ensure database file exists for SQLite
$dbConnection = $_ENV['DB_CONNECTION'] ?? 'sqlite';
if ($dbConnection === 'sqlite') {
    $dbPath = $_ENV['DB_DATABASE'] ?? __DIR__ . '/database/database.sqlite';
    $dir = dirname($dbPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    if (!file_exists($dbPath)) {
        touch($dbPath);
        echo "Created database file: $dbPath\n";
    }
}

try {
    echo "Initializing Database...\n";
    Database::init();

    $capsule = Database::getCapsule();
    $schema = $capsule->schema();

    // Create migrations table
    if (!$schema->hasTable('migrations')) {
        echo "Creating migrations table...\n";
        $schema->create('migrations', function (Blueprint $table) {
            $table->id();
            $table->string('migration');
            $table->integer('batch');
            $table->timestamps();
        });
    }

    // Get ran migrations
    $ran = $capsule->table('migrations')->pluck('migration')->toArray();

    // Get migration files
    $files = glob(__DIR__ . '/database/migrations/*.php');
    sort($files); // Ensure order

    $batch = ($capsule->table('migrations')->max('batch') ?? 0) + 1;

    foreach ($files as $file) {
        $migrationName = basename($file, '.php');

        if (in_array($migrationName, $ran)) {
            continue;
        }

        echo "Migrating: $migrationName\n";

        $migration = require $file;

        // Support returning anonymous class
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        } else {
            echo "Skipped: $migrationName (Invalid format - must return anonymous class)\n";
            continue;
        }

        $capsule->table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batch,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        echo "Migrated: $migrationName\n";
    }

    echo "Migration completed successfully.\n";
} catch (\Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} catch (\Throwable $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
