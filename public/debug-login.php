<?php
/**
 * Debug Login Issues
 * Access at: https://nautilus.local/debug-login.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../.env')) {
    die('ERROR: .env file not found at: ' . __DIR__ . '/../.env');
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "<h2>Nautilus Login Debug</h2>";
echo "<pre>";

echo "=== Environment Check ===\n";
echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'NOT SET') . "\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NOT SET') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NOT SET') . "\n";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '[SET]' : 'NOT SET') . "\n\n";

echo "=== Database Connection Test ===\n";
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};port=" . ($_ENV['DB_PORT'] ?? 3306) . ";dbname={$_ENV['DB_DATABASE']}",
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Database connection successful\n\n";
} catch (PDOException $e) {
    echo "✗ Database connection FAILED: " . $e->getMessage() . "\n";
    die();
}

echo "=== Check for admin user ===\n";
$stmt = $pdo->query("SELECT id, email, first_name, last_name, is_active, role_id FROM users WHERE email = 'admin@nautilus.local'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "✓ User found:\n";
    echo "  ID: " . $user['id'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Name: " . $user['first_name'] . " " . $user['last_name'] . "\n";
    echo "  Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
    echo "  Role ID: " . $user['role_id'] . "\n\n";

    // Check password hash
    $stmt = $pdo->query("SELECT password_hash FROM users WHERE email = 'admin@nautilus.local'");
    $hash = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Password hash length: " . strlen($hash['password_hash']) . "\n";

    // Test password
    $testPassword = 'password';
    $match = password_verify($testPassword, $hash['password_hash']);
    echo "  Password '$testPassword' matches: " . ($match ? '✓ YES' : '✗ NO') . "\n\n";

} else {
    echo "✗ User NOT found!\n\n";
}

echo "=== Check User Model ===\n";
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/User.php';

use App\Models\User;
use App\Core\Database;

try {
    $user = User::findByEmail('admin@nautilus.local');
    if ($user) {
        echo "✓ User::findByEmail() works\n";
        echo "  Found: " . $user['email'] . "\n";
        echo "  Has password_hash: " . (isset($user['password_hash']) ? 'Yes' : 'No') . "\n\n";
    } else {
        echo "✗ User::findByEmail() returned NULL\n\n";
    }
} catch (Exception $e) {
    echo "✗ Error calling User::findByEmail(): " . $e->getMessage() . "\n\n";
}

echo "=== Check Auth System ===\n";
require_once __DIR__ . '/../app/Core/Auth.php';
use App\Core\Auth;

session_start();
$testEmail = 'admin@nautilus.local';
$testPassword = 'password';

echo "Testing Auth::attempt('$testEmail', '$testPassword')...\n";
try {
    $result = Auth::attempt($testEmail, $testPassword);
    if ($result) {
        echo "✓ Auth::attempt() returned TRUE - Login would succeed!\n\n";
        echo "Session data:\n";
        echo "  user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
        echo "  user_role: " . ($_SESSION['user_role'] ?? 'NOT SET') . "\n";
    } else {
        echo "✗ Auth::attempt() returned FALSE - Login would fail\n";
        echo "This is the problem!\n\n";
    }
} catch (Exception $e) {
    echo "✗ Error in Auth::attempt(): " . $e->getMessage() . "\n\n";
}

echo "</pre>";

echo "<hr>";
echo "<p><strong>Next step:</strong> Try logging in at <a href='/store/login'>/store/login</a></p>";
echo "<p><em>Delete this file after debugging: /public/debug-login.php</em></p>";
