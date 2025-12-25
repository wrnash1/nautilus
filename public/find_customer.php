<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $db = \App\Core\Database::getInstance()->getConnection();
    // Find a user with Customer role
    $sql = "SELECT u.id, u.email FROM users u 
            JOIN user_roles ur ON u.id = ur.user_id 
            JOIN roles r ON ur.role_id = r.id 
            WHERE r.role_name = 'Customer' LIMIT 1";
            
    $user = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        file_put_contents(__DIR__ . '/customer_id.log', $user['id']);
        echo "Found Customer: " . $user['email'] . " (ID: " . $user['id'] . ")";
    } else {
        // Create one if missing
        $email = 'test_cust_' . uniqid() . '@example.com';
        $db->prepare("INSERT INTO users (email, password, first_name, last_name, created_at) VALUES (?, 'hash', 'Test', 'Customer', NOW())")->execute([$email]);
        $id = $db->lastInsertId();
        
        // Assign Role
        $roleId = $db->query("SELECT id FROM roles WHERE role_name = 'Customer'")->fetchColumn();
        if ($roleId) {
             $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$id, $roleId]);
        }
        
        file_put_contents(__DIR__ . '/customer_id.log', $id);
        echo "Created Customer: $email (ID: $id)";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
