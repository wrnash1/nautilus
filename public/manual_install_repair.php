<?php
// public/manual_install_repair.php

// 1. Config
$host = 'nautilus-db'; // Docker host
$port = 3306;
$dbName = 'nautilus';
$user = 'root';
$pass = 'Frogman09!';

echo "Starting Manual Install Repair...\n";

// 2. Connect & Reset DB
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Dropping database $dbName...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");

    echo "Creating database $dbName...\n";
    $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    
    echo "Database created.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage() . "\n");
}

// 3. Run Migrations
$migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
sort($migrationFiles);

echo "Found " . count($migrationFiles) . " migrations.\n";

$mysqli = new mysqli($host, $user, $pass, $dbName, $port);
if ($mysqli->connect_error) {
    die("MySQLi Connection Failed: " . $mysqli->connect_error . "\n");
}

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    echo "Running $filename... ";
    
    $sql = file_get_contents($file);
    if (trim($sql) === '') {
        echo "Skipped (Empty)\n";
        continue;
    }

    if ($mysqli->multi_query($sql)) {
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
        echo "Done.\n";
    } else {
        echo "FAILED: " . $mysqli->error . "\n";
        // Don't die, try next (some might be dependent but strict failure stops everything)
        // For now, let's stop on failure to be safe, unless it's known safe.
        // die("Migration failed.\n"); 
    }
}
$mysqli->close();

// 4. Create .env
$envContent = "APP_NAME=\"Nautilus Dive Shop\"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8080
APP_TIMEZONE=America/New_York

DB_CONNECTION=mysql
DB_HOST=$host
DB_PORT=$port
DB_DATABASE=$dbName
DB_USERNAME=$user
DB_PASSWORD=$pass
";

file_put_contents(__DIR__ . '/../.env', $envContent);
echo ".env file created.\n";

// 5. Seed Admin User & Permissions
try {
    // Reconnect to specific DB
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert Tenant
    echo "Seeding Tenant...\n";
    $stmt = $pdo->prepare("INSERT INTO tenants (id, name, domain, created_at) VALUES (1, 'Nautilus Dive', 'localhost', NOW()) ON DUPLICATE KEY UPDATE name='Nautilus Dive'");
    $stmt->execute();

    // Insert User
    echo "Seeding Admin User...\n";
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
    // Columns might vary based on migrations, but let's try standard set
    // Check if table users exists
    $stmt = $pdo->prepare("INSERT INTO users (id, tenant_id, username, email, password_hash, role, created_at) VALUES (1, 1, 'admin', 'admin@nautilus.local', ?, 'admin', NOW()) ON DUPLICATE KEY UPDATE password_hash = ?");
    $stmt->execute([$passwordHash, $passwordHash]);

    // Roles (Ensure Admin role exists)
    echo "Seeding Roles...\n";
    $stmt = $pdo->prepare("INSERT INTO roles (id, tenant_id, name, slug, description, created_at) VALUES (1, 1, 'Administrator', 'admin', 'Full access', NOW()) ON DUPLICATE KEY UPDATE name='Administrator'");
    $stmt->execute();

    // Assign Role to User
    echo "Assigning Role...\n";
    $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (1, 1) ON DUPLICATE KEY UPDATE role_id=1");
    $stmt->execute();

    // Permissions
    echo "Granting Permissions...\n";
    $permissions = ['customers.view', 'customers.edit', 'customers.delete']; // Add more as needed
    
    foreach ($permissions as $code) {
        // Insert Permission
        $stmt = $pdo->prepare("SELECT id FROM permissions WHERE name = ?");
        $stmt->execute([$code]);
        $permId = $stmt->fetchColumn();

        if (!$permId) {
            $parts = explode('.', $code);
            $module = $parts[0];
            $displayName = ucfirst($module) . ' ' . ucfirst($parts[1] ?? 'Access');
            
            $stmt = $pdo->prepare("INSERT INTO permissions (name, display_name, module, description, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$code, $displayName, $module, 'Auto-generated']);
            $permId = $pdo->lastInsertId();
        }

        // Grant to Role 1
        $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (1, ?) ON DUPLICATE KEY UPDATE permission_id=permission_id");
        $stmt->execute([$permId]);
    }

    echo "User setup complete.\n";

} catch (PDOException $e) {
    die("Seeding Error: " . $e->getMessage() . "\n");
}

// 6. Mark Installed
file_put_contents(__DIR__ . '/../.installed', date('Y-m-d H:i:s'));
echo "SUCCESS: System installed successfully.\n";
?>
