<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Start of script\n";

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Models\User;
use App\Models\Role;
use App\Models\Product;
use App\Models\Category;
use App\Models\Course;
use App\Models\Customer;
use Illuminate\Database\Capsule\Manager as Capsule;

echo "Autoload loaded\n";

// Init DB
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}
Database::init();
echo "DB Init done\n";

try {
    Capsule::transaction(function () {
        echo "Transaction started\n";

        // 1. Users
        $password = password_hash('password', PASSWORD_DEFAULT);

        // Sales User
        echo "Creating Sales User...\n";
        $salesUser = User::firstOrCreate(['username' => 'sales_user'], [
            'email' => 'sales@nautilus.com',
            'password_hash' => $password,
            'first_name' => 'Sales',
            'last_name' => 'Rep',
            'is_active' => true
        ]);
        echo "Sales User ID: " . $salesUser->id . "\n";

        $cashierRole = Role::where('name', 'cashier')->first();
        if ($cashierRole) {
            echo "Cashier Role found: " . $cashierRole->id . "\n";
            if (!$salesUser->roles->contains($cashierRole->id)) {
                $salesUser->roles()->attach($cashierRole);
                echo "Attached cashier role\n";
            }
        } else {
            echo "Cashier role NOT found (run seed.php?)\n";
        }

        // Instructor User
        echo "Creating Instructor User...\n";
        $instructorRole = Role::firstOrCreate(['name' => 'instructor'], ['description' => 'Instructor']);
        $instructorUser = User::firstOrCreate(['username' => 'instructor_user'], [
            'email' => 'instructor@nautilus.com',
            'password_hash' => $password,
            'first_name' => 'Scuba',
            'last_name' => 'Steve',
            'is_active' => true
        ]);
        if (!$instructorUser->roles->contains($instructorRole->id)) {
            $instructorUser->roles()->attach($instructorRole);
        }

        // 2. Inventory
        echo "Creating Inventory...\n";
        $category = Category::firstOrCreate(['name' => 'Gear'], ['slug' => 'gear']);

        $mask = Product::firstOrCreate(['sku' => 'MASK-001'], [
            'name' => 'Pro Dive Mask',
            'category_id' => $category->id,
            'retail_price' => 50.00,
            'cost_price' => 20.00,
            'stock_quantity' => 10,
            'track_inventory' => true,
            'is_active' => true
        ]);
        $mask->update(['stock_quantity' => 10]);

        $snorkel = Product::firstOrCreate(['sku' => 'SNORKEL-001'], [
            'name' => 'Dry Snorkel',
            'category_id' => $category->id,
            'retail_price' => 30.00,
            'cost_price' => 10.00,
            'stock_quantity' => 10,
            'track_inventory' => true,
            'is_active' => true
        ]);
        $snorkel->update(['stock_quantity' => 10]);

        // 3. Education
        echo "Creating Education...\n";
        $course = Course::firstOrCreate(['course_code' => 'OWD'], [
            'name' => 'Open Water Diver',
            'price' => 300.00,
            'duration_days' => 3
        ]);

        $schedule = Capsule::table('course_schedules')->where('course_id', $course->id)->first();
        $scheduleId = 0;
        if (!$schedule) {
            $scheduleId = Capsule::table('course_schedules')->insertGetId([
                'course_id' => $course->id,
                'instructor_id' => $instructorUser->id,
                'start_date' => date('Y-m-d', strtotime('+3 days')),
                'end_date' => date('Y-m-d', strtotime('+6 days')),
                'location' => 'Pool 1',
                'max_students' => 4,
                'current_enrollment' => 1,
                'status' => 'scheduled',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $scheduleId = $schedule->id;
        }

        // 4. Customer
        echo "Creating Customer...\n";
        $student = Customer::firstOrCreate(['email' => 'student@test.com'], [
            'first_name' => 'Dave',
            'last_name' => 'Diver',
            'phone' => '555-0199',
            'is_active' => true
        ]);

        $existingEnrollment = Capsule::table('course_enrollments')
            ->where('course_schedule_id', $scheduleId)
            ->where('student_id', $student->id)
            ->first();

        if (!$existingEnrollment) {
            Capsule::table('course_enrollments')->insert([
                'course_schedule_id' => $scheduleId,
                'student_id' => $student->id,
                'status' => 'enrolled',
                'enrollment_date' => date('Y-m-d'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        echo "QA Data Setup Complete.\n";
    });
} catch (\Exception $e) {
    echo "QA Setup Failed (Exception): " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
} catch (\Throwable $e) {
    echo "QA Setup Failed (Throwable): " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
