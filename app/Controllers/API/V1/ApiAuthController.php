<?php

namespace App\Controllers\API\V1;

use App\Core\Database;
use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Services\Tenant\TenantService;

/**
 * API Authentication Controller
 *
 * Handles API key authentication and token management
 */
class ApiAuthController
{
    private TenantService $tenantService;

    public function __construct()
    {
        $this->tenantService = new TenantService();
    }

    /**
     * Authenticate API request
     */
    public function authenticate(): ?array
    {
        // Get API key from header
        $apiKey = $this->getApiKeyFromRequest();

        if (!$apiKey) {
            $this->jsonResponse(['error' => 'API key required'], 401);
            return null;
        }

        // Validate API key
        $keyData = $this->validateApiKey($apiKey);

        if (!$keyData) {
            $this->jsonResponse(['error' => 'Invalid API key'], 401);
            return null;
        }

        // Check if key is active
        if (!$keyData['is_active']) {
            $this->jsonResponse(['error' => 'API key is inactive'], 403);
            return null;
        }

        // Check expiration
        if ($keyData['expires_at'] && strtotime($keyData['expires_at']) < time()) {
            $this->jsonResponse(['error' => 'API key has expired'], 403);
            return null;
        }

        // Update last used timestamp
        $this->updateLastUsed($keyData['id']);

        // Set tenant context
        $_SESSION['tenant_id'] = $keyData['tenant_id'];
        $_SESSION['api_key_id'] = $keyData['id'];
        $_SESSION['api_permissions'] = json_decode($keyData['permissions'], true);

        return $keyData;
    }

    /**
     * Create new API key
     */
    public function createApiKey(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['error' => 'Unauthorized'], 401);
                return;
            }

            // Validate input
            $keyName = $_POST['key_name'] ?? '';
            $permissions = $_POST['permissions'] ?? '{}';

            if (empty($keyName)) {
                $this->jsonResponse(['error' => 'Key name is required'], 400);
                return;
            }

            // Generate API key and secret
            $apiKey = $this->generateApiKey();
            $apiSecret = $this->generateApiSecret();
            $apiSecretHash = password_hash($apiSecret, PASSWORD_BCRYPT);

            // Parse permissions
            $permissionsArray = is_string($permissions) ? json_decode($permissions, true) : $permissions;

            // Insert API key
            Database::query(
                "INSERT INTO tenant_api_keys (
                    tenant_id, key_name, api_key, api_secret,
                    permissions, created_by, is_active, expires_at
                ) VALUES (?, ?, ?, ?, ?, ?, 1, ?)",
                [
                    $tenantId,
                    $keyName,
                    $apiKey,
                    $apiSecretHash,
                    json_encode($permissionsArray),
                    $_SESSION['user_id'] ?? null,
                    isset($_POST['expires_at']) ? $_POST['expires_at'] : null
                ]
            );

            $keyId = Database::lastInsertId();

            // Log activity
            $this->tenantService->logActivity(
                $tenantId,
                $_SESSION['user_id'] ?? null,
                'api_key_created',
                'api_key',
                $keyId,
                "API key created: {$keyName}"
            );

            // Return API key and secret (only shown once!)
            $this->jsonResponse([
                'success' => true,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
                'message' => 'API key created successfully. Save the secret - it will not be shown again!'
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List API keys for tenant
     */
    public function listApiKeys(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['error' => 'Unauthorized'], 401);
                return;
            }

            $keys = Database::fetchAll(
                "SELECT id, key_name, api_key, permissions, is_active,
                        last_used_at, expires_at, created_at
                 FROM tenant_api_keys
                 WHERE tenant_id = ?
                 ORDER BY created_at DESC",
                [$tenantId]
            );

            // Mask API keys for security
            foreach ($keys as &$key) {
                $key['api_key'] = substr($key['api_key'], 0, 8) . '...' . substr($key['api_key'], -4);
                $key['permissions'] = json_decode($key['permissions'], true);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $keys
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Revoke API key
     */
    public function revokeApiKey(): void
    {
        try {
            $tenantId = TenantMiddleware::getCurrentTenantId();

            if (!$tenantId) {
                $this->jsonResponse(['error' => 'Unauthorized'], 401);
                return;
            }

            $keyId = $_POST['key_id'] ?? 0;

            // Verify key belongs to tenant
            $key = Database::fetchOne(
                "SELECT id, key_name FROM tenant_api_keys WHERE id = ? AND tenant_id = ?",
                [$keyId, $tenantId]
            );

            if (!$key) {
                $this->jsonResponse(['error' => 'API key not found'], 404);
                return;
            }

            // Deactivate key
            Database::query(
                "UPDATE tenant_api_keys SET is_active = 0 WHERE id = ?",
                [$keyId]
            );

            // Log activity
            $this->tenantService->logActivity(
                $tenantId,
                $_SESSION['user_id'] ?? null,
                'api_key_revoked',
                'api_key',
                $keyId,
                "API key revoked: {$key['key_name']}"
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'API key revoked successfully'
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check API permission
     */
    public static function checkPermission(string $permission): bool
    {
        $permissions = $_SESSION['api_permissions'] ?? [];

        // Check specific permission
        if (isset($permissions[$permission])) {
            return (bool)$permissions[$permission];
        }

        // Check wildcard permissions
        if (isset($permissions['*'])) {
            return (bool)$permissions['*'];
        }

        return false;
    }

    /**
     * Require API permission (throw exception if not granted)
     */
    public static function requirePermission(string $permission): void
    {
        if (!self::checkPermission($permission)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        }
    }

    /**
     * Get API key from request
     */
    private function getApiKeyFromRequest(): ?string
    {
        // Check Authorization header
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        // Check X-API-Key header
        if (isset($headers['X-API-Key'])) {
            return $headers['X-API-Key'];
        }

        // Check query parameter (not recommended, but supported)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        return null;
    }

    /**
     * Validate API key
     */
    private function validateApiKey(string $apiKey): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM tenant_api_keys WHERE api_key = ?",
            [$apiKey]
        );
    }

    /**
     * Update last used timestamp
     */
    private function updateLastUsed(int $keyId): void
    {
        Database::query(
            "UPDATE tenant_api_keys SET last_used_at = NOW() WHERE id = ?",
            [$keyId]
        );
    }

    /**
     * Generate API key
     */
    private function generateApiKey(): string
    {
        return 'nautilus_' . bin2hex(random_bytes(32));
    }

    /**
     * Generate API secret
     */
    private function generateApiSecret(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
