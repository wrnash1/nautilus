-- =====================================================
-- Migration 099: SSO/OAuth Authentication Support
-- =====================================================
-- Description: Adds support for Single Sign-On (SSO) authentication
--              using OAuth 2.0 and OpenID Connect providers
-- Providers: Google, Microsoft, GitHub, Generic OIDC, SAML
-- Created: 2025-11-19
-- =====================================================

-- Add SSO fields to users table
ALTER TABLE users
ADD COLUMN sso_provider VARCHAR(50) NULL COMMENT 'OAuth provider: google, microsoft, github, oidc, saml',
ADD COLUMN sso_provider_id VARCHAR(255) NULL COMMENT 'Unique ID from SSO provider',
ADD COLUMN sso_email VARCHAR(255) NULL COMMENT 'Email from SSO provider',
ADD COLUMN sso_avatar_url VARCHAR(500) NULL COMMENT 'Profile picture URL from provider',
ADD COLUMN sso_access_token TEXT NULL COMMENT 'Encrypted OAuth access token',
ADD COLUMN sso_refresh_token TEXT NULL COMMENT 'Encrypted OAuth refresh token',
ADD COLUMN sso_token_expires_at TIMESTAMP NULL COMMENT 'When the access token expires',
ADD COLUMN sso_last_login TIMESTAMP NULL COMMENT 'Last SSO login timestamp',
ADD COLUMN allow_password_login BOOLEAN DEFAULT TRUE COMMENT 'Allow traditional password login',
ADD INDEX idx_sso_provider (sso_provider),
ADD INDEX idx_sso_provider_id (sso_provider_id),
ADD UNIQUE KEY unique_sso_provider_id (sso_provider, sso_provider_id);

-- OAuth provider configurations (tenant-specific)
CREATE TABLE oauth_providers (
    id INTEGER  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    provider VARCHAR(50) NOT NULL COMMENT 'google, microsoft, github, oidc, saml',
    display_name VARCHAR(100) NOT NULL COMMENT 'Display name on login button',
    is_enabled BOOLEAN DEFAULT TRUE,
    
    -- OAuth 2.0 Configuration
    client_id VARCHAR(255) NOT NULL,
    client_secret TEXT NOT NULL COMMENT 'Encrypted',
    redirect_uri VARCHAR(500) NOT NULL,
    authorization_url VARCHAR(500) NULL,
    token_url VARCHAR(500) NULL,
    user_info_url VARCHAR(500) NULL,
    scopes TEXT NULL COMMENT 'JSON array of scopes',
    
    -- OIDC Configuration
    oidc_discovery_url VARCHAR(500) NULL COMMENT 'OpenID Connect discovery endpoint',
    oidc_issuer VARCHAR(500) NULL,
    oidc_jwks_uri VARCHAR(500) NULL,
    
    -- SAML Configuration
    saml_entity_id VARCHAR(500) NULL,
    saml_sso_url VARCHAR(500) NULL,
    saml_slo_url VARCHAR(500) NULL,
    saml_certificate TEXT NULL,
    
    -- Field Mapping
    field_mapping JSON NULL COMMENT 'Map provider fields to user fields',
    
    -- Settings
    auto_create_users BOOLEAN DEFAULT TRUE COMMENT 'Auto-create users on first SSO login',
    auto_link_by_email BOOLEAN DEFAULT TRUE COMMENT 'Link to existing users by email',
    require_email_verification BOOLEAN DEFAULT FALSE,
    default_role_id INTEGER NULL COMMENT 'Default role for new SSO users',
    allowed_domains TEXT NULL COMMENT 'Comma-separated list of allowed email domains',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (default_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_tenant_provider (tenant_id, provider),
    INDEX idx_tenant_enabled (tenant_id, is_enabled)
);

-- SSO login sessions (for security auditing)
CREATE TABLE sso_login_sessions (
    id INTEGER  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    
    -- Session Info
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NULL,
    
    -- OAuth State
    state_token VARCHAR(255) NULL COMMENT 'OAuth state parameter for CSRF protection',
    nonce VARCHAR(255) NULL COMMENT 'OIDC nonce for replay protection',
    code_verifier VARCHAR(255) NULL COMMENT 'PKCE code verifier',
    
    -- Timestamps
    initiated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    
    -- Status
    status ENUM('pending', 'completed', 'failed', 'expired') DEFAULT 'pending',
    error_message TEXT NULL,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_state_token (state_token),
    INDEX idx_user_sessions (user_id, completed_at),
    INDEX idx_status (status, expires_at)
);

-- SSO account linking (for users with multiple auth methods)
CREATE TABLE sso_account_links (
    id INTEGER  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    provider_email VARCHAR(255) NULL,
    provider_name VARCHAR(255) NULL,
    
    -- Link Info
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    linked_by_user_id INTEGER NULL COMMENT 'User who created the link',
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Primary SSO method',
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Last Use
    last_used_at TIMESTAMP NULL,
    use_count INTEGER DEFAULT 0,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_provider_link (provider, provider_user_id),
    INDEX idx_user_links (user_id, is_active),
    INDEX idx_provider_email (provider, provider_email)
);

-- SSO audit log
CREATE TABLE sso_audit_log (
    id BIGINT  PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    user_id INTEGER NULL,
    provider VARCHAR(50) NOT NULL,
    
    -- Event Info
    event_type VARCHAR(50) NOT NULL COMMENT 'login, logout, link, unlink, token_refresh, error',
    event_status VARCHAR(20) NOT NULL COMMENT 'success, failure, warning',
    event_message TEXT NULL,
    
    -- Context
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    session_id VARCHAR(255) NULL,
    
    -- Additional Data
    metadata JSON NULL COMMENT 'Additional event data',
    
    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_user (tenant_id, user_id, created_at),
    INDEX idx_event_type (event_type, created_at),
    INDEX idx_provider (provider, created_at)
);

-- Insert default OAuth provider configurations
INSERT INTO oauth_providers (tenant_id, provider, display_name, client_id, client_secret, redirect_uri, scopes, is_enabled)
SELECT 
    id,
    'google',
    'Google',
    'YOUR_GOOGLE_CLIENT_ID',
    'YOUR_GOOGLE_CLIENT_SECRET',
    CONCAT('http://localhost/store/auth/sso/callback/google'),
    '["openid", "email", "profile"]',
    FALSE
FROM tenants
WHERE id = 1;

INSERT INTO oauth_providers (tenant_id, provider, display_name, client_id, client_secret, redirect_uri, scopes, is_enabled)
SELECT 
    id,
    'microsoft',
    'Microsoft',
    'YOUR_MICROSOFT_CLIENT_ID',
    'YOUR_MICROSOFT_CLIENT_SECRET',
    CONCAT('http://localhost/store/auth/sso/callback/microsoft'),
    '["openid", "email", "profile"]',
    FALSE
FROM tenants
WHERE id = 1;

INSERT INTO oauth_providers (tenant_id, provider, display_name, client_id, client_secret, redirect_uri, scopes, is_enabled)
SELECT 
    id,
    'github',
    'GitHub',
    'YOUR_GITHUB_CLIENT_ID',
    'YOUR_GITHUB_CLIENT_SECRET',
    CONCAT('http://localhost/store/auth/sso/callback/github'),
    '["user:email"]',
    FALSE
FROM tenants
WHERE id = 1;

-- Add system setting for SSO
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public, created_at)
VALUES 
('sso_enabled', 'true', 'boolean', 'Enable SSO authentication globally', 0, NOW()),
('sso_force_for_new_users', 'false', 'boolean', 'Force new users to use SSO only', 0, NOW()),
('sso_allow_account_linking', 'true', 'boolean', 'Allow users to link multiple SSO providers', 0, NOW()),
('sso_session_lifetime', '86400', 'integer', 'SSO session lifetime in seconds (24 hours)', 0, NOW())
ON DUPLICATE KEY UPDATE updated_at = NOW();

-- =====================================================
-- Migration Complete
-- =====================================================
-- Tables Created:
--   - oauth_providers (SSO provider configurations)
--   - sso_login_sessions (login session tracking)
--   - sso_account_links (multiple auth methods per user)
--   - sso_audit_log (security audit trail)
-- 
-- Users Table Updated:
--   - Added SSO fields (provider, tokens, etc.)
--
-- Features:
--   ✓ Multiple OAuth providers (Google, Microsoft, GitHub)
--   ✓ OpenID Connect support
--   ✓ SAML 2.0 support
--   ✓ Account linking (multiple SSO methods)
--   ✓ Security auditing
--   ✓ PKCE support for mobile apps
--   ✓ Token refresh handling
--   ✓ Domain whitelisting
--   ✓ Auto-provisioning
-- =====================================================
