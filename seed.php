<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Capsule\Manager as Capsule;

echo "Seeding Database...\n";

// Init DB
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

Database::init();

try {
    Capsule::transaction(function () {
        // 1. Permissions
        $permissions = [
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',
            'products.view' => 'View Products',
            'products.create' => 'Create Products',
            'products.edit' => 'Edit Products',
            'products.delete' => 'Delete Products',
            'products.adjust_stock' => 'Adjust Stock',
            'customers.view' => 'View Customers',
            'customers.create' => 'Create Customers',
            'customers.edit' => 'Edit Customers',
            'customers.delete' => 'Delete Customers',
            'pos.view' => 'View POS',
            'pos.create' => 'Create POS Transaction',
            'pos.void' => 'Void Transaction',
            'pos.refund' => 'Refund Transaction',
            'reports.view' => 'View Reports',
            'settings.view' => 'View Settings',
            'settings.edit' => 'Edit Settings',
        ];

        foreach ($permissions as $code => $desc) {
            Permission::firstOrCreate(
                ['permission_code' => $code],
                ['description' => $desc]
            );
        }
        echo "Permissions seeded.\n";

        // 2. Roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            ['description' => 'Store Manager']
        );

        $cashierRole = Role::firstOrCreate(
            ['name' => 'cashier'],
            ['description' => 'Store Cashier']
        );

        echo "Roles seeded.\n";

        // 3. Assign Permissions to Roles (Admin gets all)
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));
        echo "Admin permissions assigned.\n";

        // 4. Create Admin User
        $adminUser = User::where('username', 'admin')->first();
        if (!$adminUser) {
            $adminUser = User::create([
                'username' => 'admin',
                'email' => 'admin@nautilus.com',
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'is_active' => true
            ]);
            echo "Admin user created (admin / password).\n";
        } else {
            echo "Admin user already exists.\n";
        }

        // Assign Admin Role
        if (!$adminUser->roles->contains($adminRole->id)) {
            $adminUser->roles()->attach($adminRole);
            echo "Admin role assigned to user.\n";
        }

        // 5. System Settings
        $settings = [
            ['setting_key' => 'tax_rate', 'setting_value' => '0.08', 'description' => 'Default Sales Tax Rate'],
            ['setting_key' => 'currency_symbol', 'setting_value' => '$', 'description' => 'Currency Symbol'],
            ['setting_key' => 'company_name', 'setting_value' => 'Nautilus Scuba', 'description' => 'Company Name'],
            ['setting_key' => 'bitcoin_enabled', 'setting_value' => '0', 'description' => 'Enable Bitcoin Payments'],
        ];

        foreach ($settings as $setting) {
            Capsule::table('system_settings')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                $setting
            );
        }
        echo "Settings seeded.\n";

        // 6. Cash Drawers
        Capsule::table('cash_drawers')->updateOrInsert(
            ['name' => 'Main Register'],
            ['location' => 'Front Counter', 'is_active' => true]
        );
        echo "Cash Drawer seeded.\n";

    });

    echo "Seeding Completed Successfully.\n";

} catch (\Exception $e) {
    echo "Seeding Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
