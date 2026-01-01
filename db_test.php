<?php
require 'vendor/autoload.php';

use App\Core\Database;
use Illuminate\Database\Capsule\Manager as Capsule;

try {
    echo "Initializing Database...\n";
    Database::init();

    echo "Connection configured: " . getenv('DB_CONNECTION') . "\n";

    // Create a test table if not exists (SQLite)
    Capsule::schema()->create('test_table', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    echo "Table created.\n";

    // Insert
    $id = Capsule::table('test_table')->insertGetId(['name' => 'Nautilus Test', 'created_at' => new DateTime(), 'updated_at' => new DateTime()]);
    echo "Inserted row ID: $id\n";

    // Fetch
    $row = Database::fetchOne("SELECT * FROM test_table WHERE id = ?", [$id]);
    print_r($row);

    // Cleanup
    Capsule::schema()->drop('test_table');
    echo "Cleanup done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
