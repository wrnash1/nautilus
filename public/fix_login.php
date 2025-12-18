<?php
// Fix Login Script
// This script bypasses the app framework to directly reset the user password in the DB.
// It is intended to be run via HTTP request.

header('Content-Type: text/plain');

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die("No .env file found at $envFile");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$env = [];
foreach ($lines as $line) {
    if (strpos($line, '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $env[trim($name)] = trim($value);
}

$host = $env['DB_HOST'] ?? 'database';
$db   = $env['DB_DATABASE'] ?? 'nautilus';
$user = $env['DB_USERNAME'] ?? 'nautilus';
$pass = $env['DB_PASSWORD'] ?? 'password';

echo "Attempting connection to $host -> $db with user $user...\n";

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "Connected.\n";
    
    $targetEmail = 'bill@ascubadiving.com';
    $newPassword = 'Frogman09!';
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Check for user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$targetEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User $targetEmail found (ID: {$user['id']}). Resetting password...\n";
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $user['id']]);
        echo "Password updated successfully.\n";
    } else {
        echo "User $targetEmail not found.\n";
        echo "Checking for admin@nautilus.local...\n";
        
        $backupEmail = 'admin@nautilus.local';
        $stmt->execute([$backupEmail]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "Admin user found. Resetting password to $newPassword...\n";
            $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$newHash, $admin['id']]);
            echo "Admin password updated.\n";
        } else {
            echo "No suitable user found. Creating $targetEmail...\n";
            // Try to insert (guessing cols)
            try {
                $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, 'admin', NOW())";
                $pdo->prepare($sql)->execute(['Bill', 'Test', $targetEmail, $newHash]);
                echo "User created successfully.\n";
            } catch (Exception $e) {
                echo "Failed to create user: " . $e->getMessage() . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
