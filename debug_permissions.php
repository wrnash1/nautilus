<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

App\Core\Database::init();

echo "<h1>Permission Debug</h1>";

// Check all permissions
echo "<h2>All Permissions</h2>";
$permissions = App\Core\Database::fetchAll("SELECT * FROM permissions ORDER BY permission_code");
echo "<pre>";
foreach ($permissions as $p) {
    echo $p['permission_code'] . " - " . $p['name'] . "\n";
}
echo "</pre>";

// Check admin user roles
echo "<h2>Admin User Roles (user_id = 1)</h2>";
$roles = App\Core\Database::fetchAll("SELECT ur.*, r.name as role_name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = 1");
echo "<pre>";
print_r($roles);
echo "</pre>";

// Check role permissions for admin role
echo "<h2>Permissions for Role ID 1 (Super Admin)</h2>";
$rolePerms = App\Core\Database::fetchAll("
    SELECT p.permission_code, p.name 
    FROM role_permissions rp 
    JOIN permissions p ON rp.permission_id = p.id 
    WHERE rp.role_id = 1
");
echo "<pre>";
foreach ($rolePerms as $rp) {
    echo $rp['permission_code'] . "\n";
}
echo "</pre>";

// Check trips.view specifically
echo "<h2>Check trips.view Permission</h2>";
$tripsPerm = App\Core\Database::fetchOne("SELECT * FROM permissions WHERE permission_code = 'trips.view'");
echo "<pre>";
print_r($tripsPerm);
echo "</pre>";

// Check if trips.view is assigned to role 1
echo "<h2>Is trips.view assigned to role 1?</h2>";
$result = App\Core\Database::fetchOne("
    SELECT COUNT(*) as has_perm
    FROM role_permissions rp
    JOIN permissions p ON rp.permission_id = p.id
    WHERE rp.role_id = 1 AND p.permission_code = 'trips.view'
");
echo "Result: " . ($result['has_perm'] > 0 ? "YES" : "NO") . "\n";

// Check rentals.view
echo "<h2>Check rentals.view Permission</h2>";
$rentalsPerm = App\Core\Database::fetchOne("SELECT * FROM permissions WHERE permission_code = 'rentals.view'");
echo "<pre>";
print_r($rentalsPerm);
echo "</pre>";

// Test the hasPermission function
echo "<h2>Test hasPermission (user 1, trips.view)</h2>";
$hasPerm = App\Models\User::hasPermission(1, 'trips.view');
echo "hasPermission(1, 'trips.view'): " . ($hasPerm ? "TRUE" : "FALSE") . "\n";

echo "<h2>Test hasPermission (user 1, rentals.view)</h2>";
$hasPerm2 = App\Models\User::hasPermission(1, 'rentals.view');
echo "hasPermission(1, 'rentals.view'): " . ($hasPerm2 ? "TRUE" : "FALSE") . "\n";
