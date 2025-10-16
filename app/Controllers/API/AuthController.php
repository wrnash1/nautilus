<?php

namespace App\Controllers\API;

use App\Models\User;
use App\Core\Database;

class AuthController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'Email and password are required']);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid credentials']);
            return;
        }
        
        $token = $this->generateToken($user);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role_id' => $user['role_id']
            ]
        ]);
    }
    
    public function register()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $firstName = $input['first_name'] ?? '';
        $lastName = $input['last_name'] ?? '';
        
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request', 'message' => 'All fields are required']);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Conflict', 'message' => 'Email already exists']);
            return;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (email, password, first_name, last_name, role_id, is_active, created_at)
            VALUES (?, ?, ?, ?, (SELECT id FROM roles WHERE name = 'customer'), 1, NOW())
        ");
        
        if ($stmt->execute([$email, $hashedPassword, $firstName, $lastName])) {
            $userId = $this->db->lastInsertId();
            
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $token = $this->generateToken($user);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'message' => 'Failed to create user']);
        }
    }
    
    private function generateToken($user)
    {
        return base64_encode(json_encode([
            'user_id' => $user['id'],
            'exp' => time() + 86400
        ]));
    }
}
