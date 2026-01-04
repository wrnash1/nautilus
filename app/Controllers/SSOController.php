<?php

namespace App\Controllers;

use App\Services\OAuthService;
use App\Core\Database;

/**
 * SSO Authentication Controller
 * 
 * Handles OAuth/OpenID Connect authentication flows
 */
class SSOController
{
    private $db;
    private $oauthService;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        
        // Get tenant ID from session or default to 1
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        $this->oauthService = new OAuthService($this->db, $tenantId);
    }

    /**
     * Initiate SSO login
     * GET /store/auth/sso/{provider}
     */
    public function login(string $provider): void
    {
        try {
            // Get authorization URL
            $authData = $this->oauthService->getAuthorizationUrl($provider);
            
            // Redirect to provider's login page
            header('Location: ' . $authData['url']);
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'SSO login failed: ' . $e->getMessage();
            header('Location: /store/login');
            exit;
        }
    }

    /**
     * Handle OAuth callback
     * GET /store/auth/sso/callback/{provider}
     */
    public function callback(string $provider): void
    {
        try {
            // Check for errors from provider
            if (isset($_GET['error'])) {
                throw new \Exception($_GET['error_description'] ?? $_GET['error']);
            }

            // Get authorization code
            $code = $_GET['code'] ?? null;
            $state = $_GET['state'] ?? null;

            if (!$code) {
                throw new \Exception('No authorization code received');
            }

            // Handle the callback and get user
            $user = $this->oauthService->handleCallback($provider, $code, $state);

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['sso_provider'] = $provider;

            // Redirect to dashboard
            $_SESSION['flash_success'] = 'Welcome back, ' . $user['first_name'] . '!';
            header('Location: /store/dashboard');
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'SSO authentication failed: ' . $e->getMessage();
            header('Location: /store/login');
            exit;
        }
    }

    /**
     * Link SSO account to existing user
     * POST /store/auth/sso/link/{provider}
     */
    public function link(string $provider): void
    {
        // User must be logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'You must be logged in to link an SSO account';
            header('Location: /store/login');
            exit;
        }

        try {
            // Get authorization URL
            $authData = $this->oauthService->getAuthorizationUrl($provider);
            
            // Store that this is a link operation
            $_SESSION['sso_link_mode'] = true;
            
            // Redirect to provider's login page
            header('Location: ' . $authData['url']);
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to link SSO account: ' . $e->getMessage();
            header('Location: /store/profile');
            exit;
        }
    }

    /**
     * Unlink SSO account
     * POST /store/auth/sso/unlink/{provider}
     */
    public function unlink(string $provider): void
    {
        // User must be logged in
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $userId = $_SESSION['user_id'];
            $tenantId = $_SESSION['tenant_id'];

            // Check if user has password login enabled
            $stmt = $this->db->prepare("
                SELECT allow_password_login, password_hash 
                FROM users 
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([$userId, $tenantId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user['allow_password_login'] || empty($user['password_hash'])) {
                throw new \Exception('Cannot unlink SSO. Please set a password first.');
            }

            // Unlink the SSO account
            $stmt = $this->db->prepare("
                UPDATE sso_account_links 
                SET is_active = 0 
                WHERE user_id = ? AND provider = ?
            ");
            $stmt->execute([$userId, $provider]);

            // If this was the primary SSO, clear it from user
            $stmt = $this->db->prepare("
                UPDATE users 
                SET sso_provider = NULL, 
                    sso_provider_id = NULL,
                    sso_access_token = NULL,
                    sso_refresh_token = NULL
                WHERE id = ? AND sso_provider = ?
            ");
            $stmt->execute([$userId, $provider]);

            echo json_encode([
                'success' => true,
                'message' => ucfirst($provider) . ' account unlinked successfully'
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get user's linked SSO accounts
     * GET /store/auth/sso/accounts
     */
    public function getLinkedAccounts(): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $userId = $_SESSION['user_id'];

            $stmt = $this->db->prepare("
                SELECT 
                    provider,
                    provider_email,
                    provider_name,
                    is_primary,
                    last_used_at,
                    use_count
                FROM sso_account_links
                WHERE user_id = ? AND is_active = 1
                ORDER BY is_primary DESC, last_used_at DESC
            ");
            $stmt->execute([$userId]);
            $accounts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'accounts' => $accounts
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
