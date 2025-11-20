<?php

namespace App\Services;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\GenericProvider;
use PDO;

/**
 * OAuth/SSO Authentication Service
 * 
 * Handles Single Sign-On authentication with multiple providers:
 * - Google OAuth 2.0
 * - Microsoft Azure AD / Office 365
 * - GitHub OAuth
 * - Generic OpenID Connect
 * - SAML 2.0 (future)
 */
class OAuthService
{
    private PDO $db;
    private int $tenantId;
    private array $config;

    public function __construct(PDO $db, int $tenantId)
    {
        $this->db = $db;
        $this->tenantId = $tenantId;
        $this->loadConfig();
    }

    /**
     * Load OAuth configuration from database
     */
    private function loadConfig(): void
    {
        $stmt = $this->db->prepare("
            SELECT * FROM oauth_providers 
            WHERE tenant_id = ? AND is_enabled = 1
        ");
        $stmt->execute([$this->tenantId]);
        
        $this->config = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->config[$row['provider']] = $row;
        }
    }

    /**
     * Get list of enabled SSO providers
     */
    public function getEnabledProviders(): array
    {
        return array_map(function($config) {
            return [
                'provider' => $config['provider'],
                'display_name' => $config['display_name'],
                'icon' => $this->getProviderIcon($config['provider'])
            ];
        }, $this->config);
    }

    /**
     * Get provider icon class
     */
    private function getProviderIcon(string $provider): string
    {
        $icons = [
            'google' => 'bi-google',
            'microsoft' => 'bi-microsoft',
            'github' => 'bi-github',
            'oidc' => 'bi-shield-lock',
            'saml' => 'bi-building'
        ];
        return $icons[$provider] ?? 'bi-box-arrow-in-right';
    }

    /**
     * Get OAuth provider instance
     */
    public function getProvider(string $providerName): object
    {
        if (!isset($this->config[$providerName])) {
            throw new \Exception("OAuth provider '{$providerName}' is not configured or enabled");
        }

        $config = $this->config[$providerName];

        switch ($providerName) {
            case 'google':
                return new Google([
                    'clientId' => $config['client_id'],
                    'clientSecret' => $this->decrypt($config['client_secret']),
                    'redirectUri' => $config['redirect_uri']
                ]);

            case 'github':
                return new Github([
                    'clientId' => $config['client_id'],
                    'clientSecret' => $this->decrypt($config['client_secret']),
                    'redirectUri' => $config['redirect_uri']
                ]);

            case 'microsoft':
                return new GenericProvider([
                    'clientId' => $config['client_id'],
                    'clientSecret' => $this->decrypt($config['client_secret']),
                    'redirectUri' => $config['redirect_uri'],
                    'urlAuthorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
                    'urlAccessToken' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
                    'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
                    'scopes' => json_decode($config['scopes'] ?? '["openid", "email", "profile"]')
                ]);

            case 'oidc':
                // Generic OpenID Connect provider
                return new GenericProvider([
                    'clientId' => $config['client_id'],
                    'clientSecret' => $this->decrypt($config['client_secret']),
                    'redirectUri' => $config['redirect_uri'],
                    'urlAuthorize' => $config['authorization_url'],
                    'urlAccessToken' => $config['token_url'],
                    'urlResourceOwnerDetails' => $config['user_info_url'],
                    'scopes' => json_decode($config['scopes'] ?? '["openid", "email", "profile"]')
                ]);

            default:
                throw new \Exception("Unsupported OAuth provider: {$providerName}");
        }
    }

    /**
     * Generate authorization URL and state token
     */
    public function getAuthorizationUrl(string $providerName): array
    {
        $provider = $this->getProvider($providerName);
        
        // Generate state token for CSRF protection
        $state = bin2hex(random_bytes(32));
        
        // Generate PKCE code verifier (for enhanced security)
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        
        // Get authorization URL
        $authUrl = $provider->getAuthorizationUrl([
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ]);

        // Store state in session
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_provider'] = $providerName;
        $_SESSION['oauth_code_verifier'] = $codeVerifier;

        // Log the initiation
        $this->logSSOEvent(null, $providerName, 'login_initiated', 'success', 'User initiated SSO login');

        return [
            'url' => $authUrl,
            'state' => $state
        ];
    }

    /**
     * Handle OAuth callback
     */
    public function handleCallback(string $providerName, string $code, string $state): array
    {
        // Verify state token (CSRF protection)
        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            $this->logSSOEvent(null, $providerName, 'login', 'failure', 'Invalid state token (CSRF)');
            throw new \Exception('Invalid state parameter. Possible CSRF attack.');
        }

        // Verify provider matches
        if (!isset($_SESSION['oauth_provider']) || $providerName !== $_SESSION['oauth_provider']) {
            throw new \Exception('Provider mismatch');
        }

        $provider = $this->getProvider($providerName);

        try {
            // Exchange authorization code for access token
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $code,
                'code_verifier' => $_SESSION['oauth_code_verifier'] ?? null
            ]);

            // Get user details from provider
            $resourceOwner = $provider->getResourceOwner($accessToken);
            $providerUser = $resourceOwner->toArray();

            // Extract user information
            $userData = $this->extractUserData($providerName, $providerUser);
            $userData['access_token'] = $accessToken->getToken();
            $userData['refresh_token'] = $accessToken->getRefreshToken();
            $userData['expires_at'] = $accessToken->getExpires() ? date('Y-m-d H:i:s', $accessToken->getExpires()) : null;

            // Find or create user
            $user = $this->findOrCreateUser($providerName, $userData);

            // Log successful login
            $this->logSSOEvent($user['id'], $providerName, 'login', 'success', 'SSO login successful');

            // Clean up session
            unset($_SESSION['oauth_state'], $_SESSION['oauth_provider'], $_SESSION['oauth_code_verifier']);

            return $user;

        } catch (\Exception $e) {
            $this->logSSOEvent(null, $providerName, 'login', 'failure', $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract user data from provider response
     */
    private function extractUserData(string $provider, array $providerUser): array
    {
        $data = [
            'provider_id' => null,
            'email' => null,
            'first_name' => null,
            'last_name' => null,
            'avatar_url' => null
        ];

        switch ($provider) {
            case 'google':
                $data['provider_id'] = $providerUser['sub'] ?? $providerUser['id'];
                $data['email'] = $providerUser['email'];
                $data['first_name'] = $providerUser['given_name'] ?? '';
                $data['last_name'] = $providerUser['family_name'] ?? '';
                $data['avatar_url'] = $providerUser['picture'] ?? null;
                break;

            case 'github':
                $data['provider_id'] = $providerUser['id'];
                $data['email'] = $providerUser['email'];
                $name = explode(' ', $providerUser['name'] ?? '', 2);
                $data['first_name'] = $name[0] ?? '';
                $data['last_name'] = $name[1] ?? '';
                $data['avatar_url'] = $providerUser['avatar_url'] ?? null;
                break;

            case 'microsoft':
                $data['provider_id'] = $providerUser['id'];
                $data['email'] = $providerUser['mail'] ?? $providerUser['userPrincipalName'];
                $data['first_name'] = $providerUser['givenName'] ?? '';
                $data['last_name'] = $providerUser['surname'] ?? '';
                $data['avatar_url'] = null; // Microsoft Graph requires separate call
                break;

            case 'oidc':
                $data['provider_id'] = $providerUser['sub'];
                $data['email'] = $providerUser['email'];
                $data['first_name'] = $providerUser['given_name'] ?? '';
                $data['last_name'] = $providerUser['family_name'] ?? '';
                $data['avatar_url'] = $providerUser['picture'] ?? null;
                break;
        }

        return $data;
    }

    /**
     * Find existing user or create new one
     */
    private function findOrCreateUser(string $provider, array $userData): array
    {
        $config = $this->config[$provider];

        // Check if user exists by SSO provider ID
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE tenant_id = ? AND sso_provider = ? AND sso_provider_id = ?
        ");
        $stmt->execute([$this->tenantId, $provider, $userData['provider_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update existing SSO user
            $this->updateSSOUser($user['id'], $provider, $userData);
            return $user;
        }

        // Check if user exists by email (for account linking)
        if ($config['auto_link_by_email'] && $userData['email']) {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE tenant_id = ? AND email = ?
            ");
            $stmt->execute([$this->tenantId, $userData['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Link SSO to existing account
                $this->linkSSOToUser($user['id'], $provider, $userData);
                return $user;
            }
        }

        // Create new user if auto-provisioning is enabled
        if ($config['auto_create_users']) {
            return $this->createSSOUser($provider, $userData, $config);
        }

        throw new \Exception('User not found and auto-provisioning is disabled');
    }

    /**
     * Create new user from SSO
     */
    private function createSSOUser(string $provider, array $userData, array $config): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO users (
                tenant_id, role_id, email, first_name, last_name,
                sso_provider, sso_provider_id, sso_email, sso_avatar_url,
                sso_access_token, sso_refresh_token, sso_token_expires_at,
                sso_last_login, allow_password_login, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0, 1, NOW())
        ");

        $stmt->execute([
            $this->tenantId,
            $config['default_role_id'] ?? 2, // Default to customer role
            $userData['email'],
            $userData['first_name'],
            $userData['last_name'],
            $provider,
            $userData['provider_id'],
            $userData['email'],
            $userData['avatar_url'],
            $this->encrypt($userData['access_token']),
            $this->encrypt($userData['refresh_token'] ?? ''),
            $userData['expires_at']
        ]);

        $userId = $this->db->lastInsertId();

        // Create account link
        $this->createAccountLink($userId, $provider, $userData, true);

        // Fetch and return the new user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update existing SSO user
     */
    private function updateSSOUser(int $userId, string $provider, array $userData): void
    {
        $stmt = $this->db->prepare("
            UPDATE users SET
                sso_access_token = ?,
                sso_refresh_token = ?,
                sso_token_expires_at = ?,
                sso_last_login = NOW(),
                sso_avatar_url = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $this->encrypt($userData['access_token']),
            $this->encrypt($userData['refresh_token'] ?? ''),
            $userData['expires_at'],
            $userData['avatar_url'],
            $userId
        ]);

        // Update account link last used
        $stmt = $this->db->prepare("
            UPDATE sso_account_links SET
                last_used_at = NOW(),
                use_count = use_count + 1
            WHERE user_id = ? AND provider = ?
        ");
        $stmt->execute([$userId, $provider]);
    }

    /**
     * Link SSO provider to existing user
     */
    private function linkSSOToUser(int $userId, string $provider, array $userData): void
    {
        // Update user with SSO info
        $stmt = $this->db->prepare("
            UPDATE users SET
                sso_provider = ?,
                sso_provider_id = ?,
                sso_email = ?,
                sso_avatar_url = ?,
                sso_access_token = ?,
                sso_refresh_token = ?,
                sso_token_expires_at = ?,
                sso_last_login = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $provider,
            $userData['provider_id'],
            $userData['email'],
            $userData['avatar_url'],
            $this->encrypt($userData['access_token']),
            $this->encrypt($userData['refresh_token'] ?? ''),
            $userData['expires_at'],
            $userId
        ]);

        // Create account link
        $this->createAccountLink($userId, $provider, $userData, true);

        $this->logSSOEvent($userId, $provider, 'link', 'success', 'SSO account linked');
    }

    /**
     * Create account link record
     */
    private function createAccountLink(int $userId, string $provider, array $userData, bool $isPrimary = false): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO sso_account_links (
                tenant_id, user_id, provider, provider_user_id, provider_email,
                provider_name, is_primary, linked_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                provider_email = VALUES(provider_email),
                provider_name = VALUES(provider_name),
                last_used_at = NOW(),
                use_count = use_count + 1
        ");

        $stmt->execute([
            $this->tenantId,
            $userId,
            $provider,
            $userData['provider_id'],
            $userData['email'],
            ($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''),
            $isPrimary ? 1 : 0
        ]);
    }

    /**
     * Log SSO event for auditing
     */
    private function logSSOEvent(?int $userId, string $provider, string $eventType, string $status, string $message): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO sso_audit_log (
                tenant_id, user_id, provider, event_type, event_status,
                event_message, ip_address, user_agent, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $this->tenantId,
            $userId,
            $provider,
            $eventType,
            $status,
            $message,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }

    /**
     * Generate PKCE code verifier
     */
    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generate PKCE code challenge
     */
    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    /**
     * Encrypt sensitive data
     */
    private function encrypt(string $data): string
    {
        if (empty($data)) return '';
        
        $key = $_ENV['APP_KEY'] ?? 'default-encryption-key-change-me';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt sensitive data
     */
    private function decrypt(string $data): string
    {
        if (empty($data)) return '';
        
        $key = $_ENV['APP_KEY'] ?? 'default-encryption-key-change-me';
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }
}
