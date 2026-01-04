<?php
/**
 * Seed roles and permissions with correct schema
 * Fixes admin menu not showing items
 */

// Define base path constant for the application
define('BASE_PATH', dirname(__DIR__));

// Load environment variables
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} catch (\Exception $e) {
    die("Failed to load .env: " . $e->getMessage());
}

// Initialize Database
try {
    \App\Core\Database::init();
} catch (\Exception $e) {
    die("Database Init Failed: " . $e->getMessage());
}

// Use Illuminate DB Capsule
use Illuminate\Database\Capsule\Manager as DB;

echo "<pre>\n";
echo "=== Seeding Roles and Permissions ===\n\n";

try {
    // First, let's see what's currently in the database
    echo "1. Checking current database state...\n";

    $rolesCount = DB::table('roles')->count();
    $permsCount = DB::table('permissions')->count();
    $rolePermsCount = DB::table('role_permissions')->count();
    $userRolesCount = DB::table('user_roles')->count();

    echo "   - Roles: $rolesCount\n";
    echo "   - Permissions: $permsCount\n";
    echo "   - Role-Permissions: $rolePermsCount\n";
    echo "   - User-Roles: $userRolesCount\n\n";

    // Check users
    $users = DB::table('users')->select('id', 'username', 'email')->limit(5)->get();
    echo "2. Existing users:\n";
    foreach ($users as $u) {
        echo "   - ID: {$u->id}, Username: {$u->username}, Email: {$u->email}\n";
    }
    echo "\n";

    // 3. Ensure admin role exists
    echo "3. Ensuring admin role exists...\n";
    $adminRole = DB::table('roles')
        ->where('role_code', 'admin')
        ->orWhere('name', 'Admin')
        ->first();

    if (!$adminRole) {
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'Admin',
            'role_code' => 'admin',
            'description' => 'Full system access',
            'is_system_role' => 1,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        echo "   Created admin role with ID: $adminRoleId\n";
    } else {
        $adminRoleId = $adminRole->id;
        echo "   Admin role already exists with ID: $adminRoleId\n";
    }

    // 4. Insert all required permissions
    echo "\n4. Inserting permissions...\n";
    $permissions = [
        // Dashboard
        ['dashboard.view', 'View Dashboard', 'Dashboard'],
        // POS
        ['pos.view', 'View POS', 'POS'],
        ['pos.access', 'Use POS', 'POS'],
        ['pos.refund', 'Process Refunds', 'POS'],
        // Customers
        ['customers.view', 'View Customers', 'Customers'],
        ['customers.create', 'Create Customers', 'Customers'],
        ['customers.edit', 'Edit Customers', 'Customers'],
        ['customers.delete', 'Delete Customers', 'Customers'],
        // Products
        ['products.view', 'View Products', 'Products'],
        ['products.create', 'Create Products', 'Products'],
        ['products.edit', 'Edit Products', 'Products'],
        ['products.delete', 'Delete Products', 'Products'],
        // Rentals
        ['rentals.view', 'View Rentals', 'Rentals'],
        ['rentals.manage', 'Manage Rentals', 'Rentals'],
        // Services
        ['services.view', 'View Services', 'Services'],
        ['services.manage', 'Manage Services', 'Services'],
        // Courses
        ['courses.view', 'View Courses', 'Courses'],
        ['courses.manage', 'Manage Courses', 'Courses'],
        ['courses.enroll', 'Enroll Students', 'Courses'],
        ['courses.certify', 'Issue Certifications', 'Courses'],
        // Trips
        ['trips.view', 'View Trips', 'Trips'],
        ['trips.manage', 'Manage Trips', 'Trips'],
        ['trips.book', 'Book Trips', 'Trips'],
        // Reports
        ['reports.view', 'View Reports', 'Reports'],
        ['reports.advanced', 'Advanced Reports', 'Reports'],
        // Settings
        ['settings.view', 'View Settings', 'Settings'],
        ['settings.edit', 'Edit Settings', 'Settings'],
        // Staff
        ['staff.view', 'View Staff', 'Staff'],
        ['staff.manage', 'Manage Staff', 'Staff'],
    ];

    $insertedCount = 0;
    foreach ($permissions as $perm) {
        $exists = DB::table('permissions')->where('permission_code', $perm[0])->exists();
        if (!$exists) {
            DB::table('permissions')->insert([
                'permission_code' => $perm[0],
                'permission_name' => $perm[1],
                'category' => $perm[2],
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $insertedCount++;
        }
    }
    echo "   Inserted $insertedCount new permissions\n";

    // 5. Assign all permissions to admin role
    echo "\n5. Assigning all permissions to admin role...\n";
    $permIds = DB::table('permissions')->pluck('id');

    $assignedCount = 0;
    foreach ($permIds as $permId) {
        $exists = DB::table('role_permissions')
            ->where('role_id', $adminRoleId)
            ->where('permission_id', $permId)
            ->exists();
        if (!$exists) {
            DB::table('role_permissions')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permId,
                'granted_at' => date('Y-m-d H:i:s'),
            ]);
            $assignedCount++;
        }
    }
    echo "   Assigned $assignedCount permissions to admin role\n";

    // 6. Assign admin user (id=1) to admin role
    echo "\n6. Assigning admin user to admin role...\n";
    $adminUser = DB::table('users')
        ->where('id', 1)
        ->orWhere('username', 'admin')
        ->orWhere('email', 'admin@admin.com')
        ->first();

    if ($adminUser) {
        $exists = DB::table('user_roles')
            ->where('user_id', $adminUser->id)
            ->where('role_id', $adminRoleId)
            ->exists();
        if (!$exists) {
            DB::table('user_roles')->insert([
                'user_id' => $adminUser->id,
                'role_id' => $adminRoleId,
                'assigned_at' => date('Y-m-d H:i:s'),
            ]);
        }
        echo "   Assigned user ID {$adminUser->id} to admin role\n";
    } else {
        echo "   WARNING: No admin user found!\n";
    }

    // 7. Verify final state
    echo "\n7. Final database state:\n";
    $rolesCount = DB::table('roles')->count();
    $permsCount = DB::table('permissions')->count();
    $rolePermsCount = DB::table('role_permissions')->count();
    $userRolesCount = DB::table('user_roles')->count();

    echo "   - Roles: $rolesCount\n";
    echo "   - Permissions: $permsCount\n";
    echo "   - Role-Permissions: $rolePermsCount\n";
    echo "   - User-Roles: $userRolesCount\n\n";

    // 8. Verify admin user has permissions
    echo "8. Verifying admin permissions...\n";
    $perms = DB::table('users as u')
        ->join('user_roles as ur', 'u.id', '=', 'ur.user_id')
        ->join('roles as r', 'ur.role_id', '=', 'r.id')
        ->join('role_permissions as rp', 'r.id', '=', 'rp.role_id')
        ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
        ->where('u.id', 1)
        ->limit(10)
        ->pluck('p.permission_code');

    echo "   Admin user has " . count($perms) . " permissions:\n";
    foreach ($perms as $p) {
        echo "   - $p\n";
    }

    echo "\n=== SUCCESS: Permissions seeded ===\n";
    echo "Please refresh the browser to see the updated menu.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
