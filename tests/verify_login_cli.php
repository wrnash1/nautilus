<?php
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Auth.php';
require_once __DIR__ . '/../app/Models/User.php';

use App\Core\Auth;
use App\Models\User;

// Mock environment
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

echo "Attempting login check for admin@nautilus.local...\n";

$email = 'admin@nautilus.local';
$password = 'password';

$user = User::findByEmail($email);

if (!$user) {
    echo "FAIL: User not found via User::findByEmail\n";
    // Debug raw query
    $db = \App\Core\Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $rawUser = $stmt->fetch();
    echo "Raw DB check matches: " . ($rawUser ? 'Yes' : 'No') . "\n";
} else {
    echo "User found. ID: " . $user['id'] . "\n";
    echo "Hash in DB: " . $user['password_hash'] . "\n";
    
    $verify = password_verify($password, $user['password_hash']);
    echo "password_verify result: " . ($verify ? 'TRUE' : 'FALSE') . "\n";
    
    // Test Auth::attempt
    // Mock session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $result = Auth::attempt($email, $password);
    echo "Auth::attempt result: " . ($result ? 'TRUE' : 'FALSE') . "\n";
}
