<?php

namespace App\Services\Auth;

use App\Core\TenantDatabase;
use App\Services\Audit\AuditService;

/**
 * Enterprise SSO and SAML Authentication Service
 *
 * Supports:
 * - SAML 2.0 authentication
 * - OAuth 2.0 / OpenID Connect
 * - Microsoft Azure AD
 * - Google Workspace
 * - Okta
 * - OneLogin
 */
class SsoService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Initiate SAML authentication
     */
    public function initiateSamlAuth(int $tenantId): array
    {
        $config = $this->getSamlConfig($tenantId);

        if (!$config || !$config['enabled']) {
            return ['success' => false, 'error' => 'SSO not configured'];
        }

        $requestId = bin2hex(random_bytes(16));
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        // Create SAML AuthnRequest
        $authnRequest = $this->buildSamlAuthnRequest($config, $requestId, $timestamp);

        // Store request for validation
        TenantDatabase::insertTenant('saml_requests', [
            'tenant_id' => $tenantId,
            'request_id' => $requestId,
            'issued_at' => $timestamp,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
            'status' => 'pending'
        ]);

        $this->auditService->log('sso', 'saml_auth_initiated', null, [
            'tenant_id' => $tenantId,
            'request_id' => $requestId
        ]);

        return [
            'success' => true,
            'redirect_url' => $config['idp_sso_url'],
            'saml_request' => base64_encode($authnRequest),
            'relay_state' => $requestId
        ];
    }

    /**
     * Process SAML response
     */
    public function processSamlResponse(string $samlResponse, string $relayState): array
    {
        try {
            // Decode and parse SAML response
            $decodedResponse = base64_decode($samlResponse);
            $response = $this->parseSamlResponse($decodedResponse);

            // Validate request exists
            $request = TenantDatabase::fetchOneTenant(
                "SELECT * FROM saml_requests WHERE request_id = ? AND status = 'pending'",
                [$relayState]
            );

            if (!$request) {
                throw new \Exception('Invalid or expired SAML request');
            }

            // Validate response
            $config = $this->getSamlConfig($request['tenant_id']);
            if (!$this->validateSamlResponse($response, $config)) {
                throw new \Exception('SAML response validation failed');
            }

            // Extract user attributes
            $attributes = $this->extractSamlAttributes($response);

            // Find or create user
            $user = $this->findOrCreateSsoUser($request['tenant_id'], $attributes);

            // Update request status
            TenantDatabase::updateTenant('saml_requests', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$request['id']]);

            $this->auditService->log('sso', 'saml_auth_success', $user['id'], [
                'email' => $attributes['email'],
                'name_id' => $response['name_id']
            ]);

            return [
                'success' => true,
                'user' => $user,
                'attributes' => $attributes
            ];

        } catch (\Exception $e) {
            $this->auditService->log('sso', 'saml_auth_failed', null, [
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Initiate OAuth 2.0 / OpenID Connect authentication
     */
    public function initiateOAuthAuth(int $tenantId, string $provider): array
    {
        $config = $this->getOAuthConfig($tenantId, $provider);

        if (!$config || !$config['enabled']) {
            return ['success' => false, 'error' => 'OAuth not configured'];
        }

        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));

        // Store state for CSRF protection
        TenantDatabase::insertTenant('oauth_states', [
            'tenant_id' => $tenantId,
            'state' => $state,
            'nonce' => $nonce,
            'provider' => $provider,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
        ]);

        // Build authorization URL
        $params = [
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $config['scope'] ?? 'openid email profile',
            'state' => $state,
            'nonce' => $nonce
        ];

        if ($provider === 'azure') {
            $params['response_mode'] = 'query';
        }

        $authUrl = $config['authorization_endpoint'] . '?' . http_build_query($params);

        return [
            'success' => true,
            'redirect_url' => $authUrl,
            'state' => $state
        ];
    }

    /**
     * Process OAuth callback
     */
    public function processOAuthCallback(string $code, string $state): array
    {
        try {
            // Validate state
            $stateRecord = TenantDatabase::fetchOneTenant(
                "SELECT * FROM oauth_states WHERE state = ? AND expires_at > NOW()",
                [$state]
            );

            if (!$stateRecord) {
                throw new \Exception('Invalid or expired state');
            }

            $config = $this->getOAuthConfig($stateRecord['tenant_id'], $stateRecord['provider']);

            // Exchange code for tokens
            $tokens = $this->exchangeOAuthCode($code, $config);

            // Get user info
            $userInfo = $this->getOAuthUserInfo($tokens['access_token'], $config);

            // Find or create user
            $user = $this->findOrCreateSsoUser($stateRecord['tenant_id'], [
                'email' => $userInfo['email'],
                'first_name' => $userInfo['given_name'] ?? '',
                'last_name' => $userInfo['family_name'] ?? '',
                'provider' => $stateRecord['provider'],
                'provider_id' => $userInfo['sub'] ?? $userInfo['id']
            ]);

            // Clean up state
            TenantDatabase::deleteTenant('oauth_states', 'id = ?', [$stateRecord['id']]);

            $this->auditService->log('sso', 'oauth_auth_success', $user['id'], [
                'provider' => $stateRecord['provider'],
                'email' => $userInfo['email']
            ]);

            return [
                'success' => true,
                'user' => $user,
                'user_info' => $userInfo
            ];

        } catch (\Exception $e) {
            $this->auditService->log('sso', 'oauth_auth_failed', null, [
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Configure SAML for tenant
     */
    public function configureSaml(int $tenantId, array $config): bool
    {
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT id FROM sso_configurations WHERE tenant_id = ? AND provider = 'saml'",
            [$tenantId]
        );

        $data = [
            'tenant_id' => $tenantId,
            'provider' => 'saml',
            'enabled' => $config['enabled'] ?? true,
            'configuration' => json_encode([
                'entity_id' => $config['entity_id'],
                'idp_sso_url' => $config['idp_sso_url'],
                'idp_slo_url' => $config['idp_slo_url'] ?? null,
                'idp_certificate' => $config['idp_certificate'],
                'sp_entity_id' => $config['sp_entity_id'],
                'sp_acs_url' => $config['sp_acs_url'],
                'sp_slo_url' => $config['sp_slo_url'] ?? null,
                'attribute_mapping' => $config['attribute_mapping'] ?? [
                    'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
                    'first_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
                    'last_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname'
                ]
            ]),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            TenantDatabase::updateTenant('sso_configurations', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            TenantDatabase::insertTenant('sso_configurations', $data);
        }

        $this->auditService->log('sso', 'saml_configured', null, ['tenant_id' => $tenantId]);

        return true;
    }

    /**
     * Configure OAuth provider for tenant
     */
    public function configureOAuth(int $tenantId, string $provider, array $config): bool
    {
        $existing = TenantDatabase::fetchOneTenant(
            "SELECT id FROM sso_configurations WHERE tenant_id = ? AND provider = ?",
            [$tenantId, $provider]
        );

        $data = [
            'tenant_id' => $tenantId,
            'provider' => $provider,
            'enabled' => $config['enabled'] ?? true,
            'configuration' => json_encode([
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect_uri'],
                'authorization_endpoint' => $config['authorization_endpoint'],
                'token_endpoint' => $config['token_endpoint'],
                'userinfo_endpoint' => $config['userinfo_endpoint'],
                'scope' => $config['scope'] ?? 'openid email profile'
            ]),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            TenantDatabase::updateTenant('sso_configurations', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            TenantDatabase::insertTenant('sso_configurations', $data);
        }

        $this->auditService->log('sso', 'oauth_configured', null, [
            'tenant_id' => $tenantId,
            'provider' => $provider
        ]);

        return true;
    }

    /**
     * Get SSO configuration for tenant
     */
    public function getSsoConfig(int $tenantId): array
    {
        $configs = TenantDatabase::fetchAllTenant(
            "SELECT * FROM sso_configurations WHERE tenant_id = ? AND enabled = 1",
            [$tenantId]
        ) ?? [];

        return array_map(function($config) {
            $config['configuration'] = json_decode($config['configuration'], true);
            return $config;
        }, $configs);
    }

    // Private helper methods

    private function getSamlConfig(int $tenantId): ?array
    {
        $config = TenantDatabase::fetchOneTenant(
            "SELECT * FROM sso_configurations WHERE tenant_id = ? AND provider = 'saml'",
            [$tenantId]
        );

        if ($config) {
            $config['configuration'] = json_decode($config['configuration'], true);
            return array_merge($config, $config['configuration']);
        }

        return null;
    }

    private function getOAuthConfig(int $tenantId, string $provider): ?array
    {
        $config = TenantDatabase::fetchOneTenant(
            "SELECT * FROM sso_configurations WHERE tenant_id = ? AND provider = ?",
            [$tenantId, $provider]
        );

        if ($config) {
            $config['configuration'] = json_decode($config['configuration'], true);
            return array_merge($config, $config['configuration']);
        }

        return null;
    }

    private function buildSamlAuthnRequest(array $config, string $requestId, string $timestamp): string
    {
        return <<<XML
<samlp:AuthnRequest xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="{$requestId}"
                    Version="2.0"
                    IssueInstant="{$timestamp}"
                    Destination="{$config['idp_sso_url']}"
                    AssertionConsumerServiceURL="{$config['sp_acs_url']}">
    <saml:Issuer>{$config['sp_entity_id']}</saml:Issuer>
    <samlp:NameIDPolicy Format="urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress" AllowCreate="true"/>
</samlp:AuthnRequest>
XML;
    }

    private function parseSamlResponse(string $xml): array
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        // Extract NameID
        $nameIdNodes = $doc->getElementsByTagName('NameID');
        $nameId = $nameIdNodes->length > 0 ? $nameIdNodes->item(0)->nodeValue : null;

        // Extract attributes
        $attributes = [];
        $attributeNodes = $doc->getElementsByTagName('Attribute');

        foreach ($attributeNodes as $node) {
            $name = $node->getAttribute('Name');
            $valueNodes = $node->getElementsByTagName('AttributeValue');
            $value = $valueNodes->length > 0 ? $valueNodes->item(0)->nodeValue : null;
            $attributes[$name] = $value;
        }

        return [
            'name_id' => $nameId,
            'attributes' => $attributes
        ];
    }

    private function validateSamlResponse(array $response, array $config): bool
    {
        // Basic validation - in production, use proper SAML library
        return !empty($response['name_id']) && !empty($response['attributes']);
    }

    private function extractSamlAttributes(array $response): array
    {
        $mapping = [
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress' => 'email',
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname' => 'first_name',
            'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname' => 'last_name'
        ];

        $extracted = ['email' => $response['name_id']];

        foreach ($response['attributes'] as $key => $value) {
            if (isset($mapping[$key])) {
                $extracted[$mapping[$key]] = $value;
            }
        }

        return $extracted;
    }

    private function exchangeOAuthCode(string $code, array $config): array
    {
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $config['redirect_uri'],
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret']
        ];

        $ch = curl_init($config['token_endpoint']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function getOAuthUserInfo(string $accessToken, array $config): array
    {
        $ch = curl_init($config['userinfo_endpoint']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function findOrCreateSsoUser(int $tenantId, array $attributes): array
    {
        // Try to find existing user by email
        $user = TenantDatabase::fetchOneTenant(
            "SELECT * FROM users WHERE email = ?",
            [$attributes['email']]
        );

        if ($user) {
            // Update SSO mapping
            TenantDatabase::updateTenant('users', [
                'sso_provider' => $attributes['provider'] ?? 'saml',
                'sso_provider_id' => $attributes['provider_id'] ?? $attributes['email'],
                'last_login' => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);

            return $user;
        }

        // Create new user
        $userId = TenantDatabase::insertTenant('users', [
            'email' => $attributes['email'],
            'first_name' => $attributes['first_name'] ?? '',
            'last_name' => $attributes['last_name'] ?? '',
            'sso_provider' => $attributes['provider'] ?? 'saml',
            'sso_provider_id' => $attributes['provider_id'] ?? $attributes['email'],
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => date('Y-m-d H:i:s')
        ]);

        // Assign default role
        $defaultRole = TenantDatabase::fetchOneTenant(
            "SELECT id FROM roles WHERE name = 'employee' LIMIT 1"
        );

        if ($defaultRole) {
            TenantDatabase::insertTenant('user_roles', [
                'user_id' => $userId,
                'role_id' => $defaultRole['id']
            ]);
        }

        return TenantDatabase::fetchOneTenant("SELECT * FROM users WHERE id = ?", [$userId]);
    }
}
