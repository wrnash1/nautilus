#!/usr/bin/env php
<?php
/**
 * Seed Roles and Permissions - Simple Approach
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/.env')) {
    die('ERROR: .env file not found.');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Seeding roles and permissions...\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Insert Roles
    echo "Inserting roles...\n";
    $pdo->exec("INSERT IGNORE INTO roles (id, name, display_name, description) VALUES
        (1, 'admin', 'Administrator', 'Full system access with all permissions'),
        (2, 'manager', 'Manager', 'Store manager with most permissions except system settings'),
        (3, 'employee', 'Employee', 'Regular employee with limited permissions'),
        (4, 'instructor', 'Instructor', 'Dive instructor with course/certification permissions'),
        (5, 'cashier', 'Cashier', 'POS and cash drawer access only')");

    // Insert Permissions
    echo "Inserting permissions...\n";
    $permissions = [
        ['dashboard.view', 'View Dashboard', 'Dashboard'],
        ['pos.view', 'View POS', 'POS'],
        ['pos.access', 'Use POS', 'POS'],
        ['pos.refund', 'Process Refunds', 'POS'],
        ['customers.view', 'View Customers', 'Customers'],
        ['customers.create', 'Create Customers', 'Customers'],
        ['customers.edit', 'Edit Customers', 'Customers'],
        ['customers.delete', 'Delete Customers', 'Customers'],
        ['customers.export', 'Export Customer Data', 'Customers'],
        ['products.view', 'View Products', 'Products'],
        ['products.create', 'Create Products', 'Products'],
        ['products.edit', 'Edit Products', 'Products'],
        ['products.delete', 'Delete Products', 'Products'],
        ['products.adjust_inventory', 'Adjust Inventory', 'Products'],
        ['categories.view', 'View Categories', 'Categories'],
        ['categories.manage', 'Manage Categories', 'Categories'],
        ['rentals.view', 'View Rentals', 'Rentals'],
        ['rentals.manage', 'Manage Rentals', 'Rentals'],
        ['courses.view', 'View Courses', 'Courses'],
        ['courses.manage', 'Manage Courses', 'Courses'],
        ['courses.enroll', 'Enroll Students', 'Courses'],
        ['courses.certify', 'Issue Certifications', 'Courses'],
        ['trips.view', 'View Trips', 'Trips'],
        ['trips.manage', 'Manage Trips', 'Trips'],
        ['trips.book', 'Book Trips', 'Trips'],
        ['workorders.view', 'View Work Orders', 'Work Orders'],
        ['workorders.manage', 'Manage Work Orders', 'Work Orders'],
        ['orders.view', 'View Orders', 'Orders'],
        ['orders.manage', 'Manage Orders', 'Orders'],
        ['reports.view', 'View Reports', 'Reports'],
        ['reports.advanced', 'Advanced Reports', 'Reports'],
        ['staff.view', 'View Staff', 'Staff'],
        ['staff.manage', 'Manage Staff', 'Staff'],
        ['air_fills.view', 'View Air Fills', 'Air Fills'],
        ['air_fills.create', 'Record Air Fills', 'Air Fills'],
        ['admin.settings', 'System Settings', 'Admin'],
        ['admin.users', 'User Management', 'Admin'],
        ['admin.roles', 'Role Management', 'Admin'],
        ['admin.integrations', 'Integrations', 'Admin'],
        ['admin.api', 'API Management', 'Admin'],
        ['admin.backups', 'Backups', 'Admin'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO permissions (name, display_name, module) VALUES (?, ?, ?)");
    foreach ($permissions as $perm) {
        $stmt->execute($perm);
    }

    // Assign ALL permissions to Admin
    echo "Assigning permissions to roles...\n";
    $pdo->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT 1, id FROM permissions");

    // Assign Manager permissions (all except admin functions)
    $pdo->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT 2, id FROM permissions
        WHERE module != 'Admin' OR name IN ('admin.settings', 'admin.integrations')");

    // Assign Employee permissions
    $employeePerms = [
        'dashboard.view', 'pos.view', 'pos.access',
        'customers.view', 'customers.create', 'customers.edit',
        'products.view', 'categories.view',
        'rentals.view', 'rentals.manage',
        'air_fills.view', 'air_fills.create',
        'workorders.view', 'workorders.manage',
        'reports.view'
    ];
    $placeholders = str_repeat('?,', count($employeePerms) - 1) . '?';
    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT 3, id FROM permissions WHERE name IN ($placeholders)");
    $stmt->execute($employeePerms);

    // Assign Cashier permissions
    $cashierPerms = ['dashboard.view', 'pos.view', 'pos.access', 'customers.view', 'products.view'];
    $placeholders = str_repeat('?,', count($cashierPerms) - 1) . '?';
    $stmt = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id)
        SELECT 5, id FROM permissions WHERE name IN ($placeholders)");
    $stmt->execute($cashierPerms);

    // Display results
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $roleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
    $permCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM role_permissions");
    $rpCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "\n✓ Roles: $roleCount\n";
    echo "✓ Permissions: $permCount\n";
    echo "✓ Role-Permission mappings: $rpCount\n";
    echo "\nSeeding complete!\n\n";

    echo "Roles available:\n";
    $stmt = $pdo->query("SELECT id, name, display_name FROM roles ORDER BY id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  [{$row['id']}] {$row['display_name']} ({$row['name']})\n";
    }

} catch (PDOException $e) {
    die('ERROR: ' . $e->getMessage() . "\n");
}
