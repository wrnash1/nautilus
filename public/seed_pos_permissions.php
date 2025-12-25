<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// Force populate $_ENV from shell environment BEFORE loading Dotenv
$overrides = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
foreach ($overrides as $key) {
    $val = getenv($key);
    if ($val !== false) {
        $_ENV[$key] = $val;
    }
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../app/helpers.php';

use App\Core\Database;

try {
    $db = Database::getInstance(); // This triggers connection

    $permissions = [
        'pos.view' => 'Access Point of Sale',
        'pos.create' => 'Create POS Transactions',
        'pos.refund' => 'Process Refunds',
        'pos.void' => 'Void Transactions',
        'pos.open_register' => 'Open Cash Register',
        'pos.close_register' => 'Close Cash Register'
    ];

    $roles = ['Admin', 'Sales'];

    echo "--- Seeding POS Permissions ---\n";

    foreach ($permissions as $code => $desc) {
        $existing = Database::fetchOne("SELECT id FROM permissions WHERE permission_code = ?", [$code]);
        if (!$existing) {
            echo "Creating permission: $code\n";
            $conn = $db->getConnection();
            $stmt = $conn->prepare("INSERT INTO permissions (permission_code, permission_name, description, category, created_at) VALUES (?, ?, ?, 'POS', NOW())");
            $stmt->execute([$code, $desc, $desc]);
            $permId = $conn->lastInsertId();
        } else {
            $permId = $existing['id'];
            echo "Permission exists: $code (ID: $permId)\n";
        }

        foreach ($roles as $roleName) {
            $role = Database::fetchOne("SELECT id FROM roles WHERE role_name = ?", [$roleName]);
            if ($role) {
                $roleId = $role['id'];
                $exists = Database::fetchOne("SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$roleId, $permId]);
                if (!$exists) {
                    echo "Assigning $code to $roleName\n";
                    $conn = $db->getConnection();
                    $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                    $stmt->execute([$roleId, $permId]);
                    echo "Assigned.\n";
                }
            } else {
                echo "Warning: Role $roleName not found!\n";
            }
        }
    }
    echo "--- Done ---\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
