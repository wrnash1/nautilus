<?php
// QA Seed Data Script
// Creates users for each role if they don't exist.

$logFile = __DIR__ . '/qa_seed.log';
file_put_contents($logFile, "Starting Data Seeding...\n");

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, $msg, FILE_APPEND);
    echo $msg;
}

// 1. Get Configuration
$envPath = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }
    }
}

$dbHost = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'database');
$dbPort = getenv('DB_PORT') ?: ($env['DB_PORT'] ?? '3306');
$dbName = getenv('DB_DATABASE') ?: ($env['DB_DATABASE'] ?? 'nautilus');
$dbUser = getenv('DB_USERNAME') ?: ($env['DB_USERNAME'] ?? 'nautilus');
$dbPass = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? 'nautilus123');

try {
    $dsn = "mysql:host=127.0.0.1;port=3307;dbname=$dbName";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
} catch (PDOException $e) {
    try {
        $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName";
        $pdo = new PDO($dsn, $dbUser, $dbPass);
    } catch (PDOException $e2) {
        logMsg("Connection failed: " . $e2->getMessage() . "\n");
        exit;
    }
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
logMsg("Connected to DB.\n");

// 2. Define Users to Seed
$usersToSeed = [
    'admin@nautilus.local' => ['name' => 'Admin User', 'role' => 'Admin'],
    'sales@nautilus.local' => ['name' => 'Sales Person', 'role' => 'Sales'],
    'instructor@nautilus.local' => ['name' => 'Instructor User', 'role' => 'Instructor'],
    'dm@nautilus.local' => ['name' => 'Dive Master', 'role' => 'Dive Master'],
    'customer@nautilus.local' => ['name' => 'Customer User', 'role' => 'Customer'],
];

$passwordHash = password_hash('password', PASSWORD_DEFAULT);

// Ensure Roles Exist
$rolesToEnsure = [
    ['Admin', 'admin', 'Store administrator'],
    ['Super Admin', 'super_admin', 'Full system access'],
    ['Manager', 'manager', 'Store manager'],
    ['Sales', 'sales_associate', 'Sales Associate'],
    ['Instructor', 'instructor', 'Diving instructor'],
    ['Dive Master', 'dive_master', 'Dive master'],
    ['Customer', 'customer', 'Registered customer']
];

foreach ($rolesToEnsure as $roleData) {
    $roleName = $roleData[0];
    $roleCode = $roleData[1];
    $desc = $roleData[2];
    
    // Check if role exists (by name or role_name)
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
    try {
        $stmt->execute([$roleName]);
        if (!$stmt->fetch()) {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO roles (role_name, role_code, description, is_active, tenant_id) VALUES (?, ?, ?, 1, NULL)");
            $stmt->execute([$roleName, $roleCode, $desc]);
            logMsg("Created missing role: $roleName\n");
        }
    } catch (Exception $e) {
        // Fallback for old schema if running against old DB?
        // But we expect new schema
        logMsg("Warning: Failed to ensure role $roleName: " . $e->getMessage() . "\n");
    }
}

foreach ($usersToSeed as $email => $info) {
    try {
        // Fetch Tenant ID dynamically
        $stmt = $pdo->query("SELECT id FROM tenants LIMIT 1");
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);
        $tenantId = $tenant ? $tenant['id'] : 1; 

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $userId = null;
        if ($user) {
            logMsg("User $email already exists (ID: {$user['id']}).\n");
            $userId = $user['id'];
            // Ensure active and tenant set
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1, tenant_id = ? WHERE id = ?");
            try {
                $stmt->execute([$tenantId, $userId]);
            } catch (Exception $e) {
                // If tenant_id column doesn't exist or fails, ignore
            }
        } else {
            logMsg("Creating user $email...\n");
            // Try insert with tenant_id first
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, is_active, tenant_id, created_at) VALUES (?, ?, ?, 1, ?, NOW())");
                // extracting username from email
                $username = explode('@', $email)[0]; 
                $stmt->execute([$username, $email, $passwordHash, $tenantId]);
                $userId = $pdo->lastInsertId();
            } catch (Exception $e) {
                 // Fallback
                 logMsg("Insert with tenant_id failed, retrying without: " . $e->getMessage() . "\n");
                 $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
                 $username = explode('@', $email)[0]; 
                 $stmt->execute([$username, $email, $passwordHash]);
                 $userId = $pdo->lastInsertId();
            }
            logMsg("Created user $email (ID: $userId).\n");
        }

        // Assign Role
        $roleName = $info['role'];
        // Find Role ID - Try both 'name' and 'role_name'
        $roleId = null;
        try {
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = ?");
            $stmt->execute([$roleName]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($role) $roleId = $role['id'];
        } catch (Exception $e) {
             // Try 'name' column if role_name failed
             try {
                $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
                $stmt->execute([$roleName]);
                $role = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($role) $roleId = $role['id'];
             } catch (Exception $e2) {
                 logMsg("Column error finding role: " . $e2->getMessage() . "\n");
             }
        }

        if (!$roleId) {
            logMsg("ERROR: Role '$roleName' not found for user $email.\n");
            continue;
        }

        // Check if role assigned
        $stmt = $pdo->prepare("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?");
        $stmt->execute([$userId, $roleId]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->execute([$userId, $roleId]);
            logMsg("Assigned role '$roleName' to $email.\n");
        } else {
            logMsg("Role '$roleName' already assigned to $email.\n");
        }

        // Special handling for Customer role - Ensure entry in `customers` table
        if ($roleName === 'Customer') {
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->execute([$email]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                logMsg("Creating customer record for $email...\n");
                $parts = explode(' ', $info['name'], 2);
                $firstName = $parts[0];
                $lastName = $parts[1] ?? 'User';
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO customers (tenant_id, first_name, last_name, email, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
                    $stmt->execute([$tenantId, $firstName, $lastName, $email]);
                    logMsg("Created customer record.\n");
                } catch (Exception $e) {
                    logMsg("Error creating customer record: " . $e->getMessage() . "\n");
                }
            } else {
                logMsg("Customer record already exists for $email.\n");
            }
        }
    } catch (Exception $e) {
        logMsg("Error processing $email: " . $e->getMessage() . "\n");
    }
}

logMsg("Seeding Validated.\n");
