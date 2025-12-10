<?php

namespace App\Middleware;

class ApiAuthMiddleware
{
    public function handle()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'message' => 'Missing or invalid authorization header']);
            exit;
        }
        
        $token = $matches[1];
        
        $user = $this->validateToken($token);
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'message' => 'Invalid or expired token']);
            exit;
        }
        
        $_SESSION['api_user_id'] = $user['id'];
        $_SESSION['api_user_role'] = $user['role_id'];
    }
    
    private function validateToken($token)
    {
        return null;
    }
}
