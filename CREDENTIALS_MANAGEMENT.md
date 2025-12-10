# Nautilus Credential Management Guide

## Overview

Nautilus supports environment-specific credentials and per-tenant database isolation for enhanced security. This guide explains how to manage credentials across development, staging, and production environments.

## Table of Contents

1. [Environment Configuration](#environment-configuration)
2. [Database Credentials](#database-credentials)
3. [API Keys and Secrets](#api-keys-and-secrets)
4. [Security Best Practices](#security-best-practices)
5. [Usage Examples](#usage-examples)
6. [Credential Rotation](#credential-rotation)

---

## Environment Configuration

### Available Environment Files

- `.env.docker.example` - Development (Docker/Podman)
- `.env.staging.example` - Staging environment
- `.env.production.example` - Production environment

### Setup for Each Environment

**Development:**
```bash
cp .env.docker.example .env
# Edit .env with development settings
```

**Staging:**
```bash
cp .env.staging.example .env
# Update with staging database and service credentials
```

**Production:**
```bash
cp .env.production.example .env
# Update with production credentials
# NEVER commit .env to version control
```

### Key Environment Variables

```bash
# Application
APP_ENV=production|staging|development
APP_DEBUG=false  # NEVER true in production
APP_KEY=  # Generate: php -r "echo bin2hex(random_bytes(16));"

# Database (Master/Default)
DB_HOST=your-db-server.com
DB_USERNAME=unique_username
DB_PASSWORD=strong_random_password

# Multi-Tenant Mode
MULTI_TENANT_MODE=isolated  # Options: shared, isolated, hybrid
```

---

## Database Credentials

### Multi-Tenant Database Architecture

Nautilus supports three modes:

1. **Shared Database** (Development/Small Deployments)
   - All tenants share one database
   - Row-level isolation via `tenant_id`
   - Single set of credentials

2. **Isolated Databases** (Enterprise/Production)
   - Each tenant has dedicated database
   - Unique credentials per tenant
   - Complete data isolation

3. **Hybrid** (Flexible)
   - Default tenants use shared DB
   - Enterprise tenants get dedicated DB
   - Mix of both approaches

### Setting Up Per-Tenant Credentials

#### Via Code:

```php
use App\Core\CredentialManager;

// Set dedicated database for a tenant
CredentialManager::setTenantDatabaseCredentials(
    tenantId: 5,
    config: [
        'use_dedicated_db' => true,
        'host' => 'tenant5-db.example.com',
        'port' => 3306,
        'database' => 'nautilus_tenant_5',
        'username' => 'tenant5_user',
        'password' => 'strong_unique_password',
        'options' => []
    ],
    environment: 'production'
);

// Test the connection
$result = CredentialManager::testDatabaseConnection(5, 'production');
if ($result['success']) {
    echo "✅ Connection successful!";
} else {
    echo "❌ Error: " . $result['error'];
}
```

#### Via SQL:

```sql
INSERT INTO tenant_database_credentials
    (tenant_id, environment, use_dedicated_db, db_host, db_database, db_username, db_password)
VALUES
    (5, 'production', 1, 'tenant5-db.example.com', 'nautilus_tenant_5',
     'tenant5_user', 'ENCRYPTED_PASSWORD_HERE');
```

**IMPORTANT:** Passwords in the database MUST be encrypted using `CredentialManager::encrypt()`

### Retrieving Tenant Credentials

```php
// Get credentials for tenant
$creds = CredentialManager::getTenantDatabaseCredentials(
    tenantId: 5,
    environment: 'production'
);

// Use credentials
$pdo = new PDO(
    "mysql:host={$creds['host']};dbname={$creds['database']}",
    $creds['username'],
    $creds['password']
);
```

---

## API Keys and Secrets

### Storing Service Credentials

Store API keys, secrets, and tokens per tenant and environment:

```php
use App\Core\CredentialManager;

// Store Stripe API key for a tenant
CredentialManager::setTenantSecret(
    tenantId: 5,
    serviceName: 'stripe',
    keyName: 'api_key',
    keyValue: 'pk_live_XXXXXXXX',
    keyType: 'api_key',
    rotationDays: 90,
    environment: 'production'
);

// Store Stripe secret key
CredentialManager::setTenantSecret(
    tenantId: 5,
    serviceName: 'stripe',
    keyName: 'secret_key',
    keyValue: 'sk_live_XXXXXXXX',
    keyType: 'secret_key',
    rotationDays: 90
);
```

### Retrieving Secrets

```php
// Get Stripe API key
$stripeKey = CredentialManager::getTenantSecret(
    tenantId: 5,
    serviceName: 'stripe',
    keyName: 'api_key',
    environment: 'production'
);

// Use in your code
\Stripe\Stripe::setApiKey($stripeKey);
```

### Supported Services

Common services you might store credentials for:

- `stripe` - Payment processing
- `aws` - Amazon Web Services
- `mailgun` - Email service
- `twilio` - SMS/Phone
- `google` - Google APIs
- `facebook` - Social integration
- `github` - Version control
- `slack` - Team communication

---

## Security Best Practices

### 1. Password Strength

**Development:**
```bash
DB_PASSWORD=nautilus123  # Simple is OK
```

**Production:**
```bash
DB_PASSWORD=$(openssl rand -base64 32)  # Strong random password
```

### 2. Encryption Key Management

**Generate Strong APP_KEY:**
```bash
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;" >> .env
```

**Use External Secrets Manager (Recommended for Production):**

- AWS Secrets Manager
- HashiCorp Vault
- Azure Key Vault
- Google Secret Manager

### 3. Never Commit Secrets

**Add to .gitignore:**
```
.env
.env.local
.env.production
.env.staging
*.key
*.pem
credentials.json
```

### 4. Rotate Credentials Regularly

**Production:** Every 90 days
**Staging:** Every 180 days
**Development:** When compromised

### 5. Access Control

- Limit who can view production credentials
- Use role-based access for credential management
- Audit all credential access

### 6. Environment Separation

| Environment  | Purpose | Credential Sharing |
|--------------|---------|-------------------|
| Development  | Local testing | OK to share |
| Staging      | Pre-production testing | Limited sharing |
| Production   | Live system | NO sharing |

---

## Usage Examples

### Example 1: Multi-Tenant SaaS Setup

```php
// Company A (Small business) - Uses shared database
CredentialManager::setTenantDatabaseCredentials(1, [
    'use_dedicated_db' => false,  // Use shared DB
]);

// Company B (Enterprise) - Gets dedicated database
CredentialManager::setTenantDatabaseCredentials(2, [
    'use_dedicated_db' => true,
    'host' => 'companyb-db.private.com',
    'database' => 'nautilus_companyb',
    'username' => 'companyb_user',
    'password' => 'ultra_secure_password_here',
], 'production');

// Set their Stripe credentials
CredentialManager::setTenantSecret(2, 'stripe', 'api_key', 'pk_live_xxx');
CredentialManager::setTenantSecret(2, 'stripe', 'secret_key', 'sk_live_xxx');
```

### Example 2: Environment-Specific Settings

```php
// Set rate limit per environment
CredentialManager::setEnvironmentSetting(
    key: 'rate_limit_max_attempts',
    value: '60',
    isSensitive: false,
    description: 'Maximum API requests per minute',
    environment: 'production'
);

// Get setting
$maxAttempts = CredentialManager::getEnvironmentSetting(
    'rate_limit_max_attempts',
    default: 100
);
```

### Example 3: Testing Database Connections

```php
// Test all tenant database connections
$tenants = Tenant::all();
foreach ($tenants as $tenant) {
    $result = CredentialManager::testDatabaseConnection($tenant->id);

    if (!$result['success']) {
        logger()->error("Tenant {$tenant->id} DB connection failed", [
            'error' => $result['error']
        ]);
    }
}
```

---

## Credential Rotation

### Manual Rotation

```php
// 1. Generate new password
$newPassword = bin2hex(random_bytes(16));

// 2. Update database user (on DB server)
// CREATE USER 'tenant5_user_new'@'%' IDENTIFIED BY 'new_password';
// GRANT ALL ON nautilus_tenant_5.* TO 'tenant5_user_new'@'%';

// 3. Update Nautilus credentials
CredentialManager::setTenantDatabaseCredentials(5, [
    'use_dedicated_db' => true,
    'host' => 'tenant5-db.example.com',
    'database' => 'nautilus_tenant_5',
    'username' => 'tenant5_user_new',
    'password' => $newPassword,
]);

// 4. Test connection
$test = CredentialManager::testDatabaseConnection(5);
if ($test['success']) {
    // 5. Drop old user
    // DROP USER 'tenant5_user_old'@'%';
}
```

### Automated Rotation (Future Enhancement)

Check credential age and rotate automatically:

```sql
-- Find credentials due for rotation
SELECT tenant_id, service_name, key_name, last_rotated_at,
       DATEDIFF(CURRENT_TIMESTAMP, last_rotated_at) as days_since_rotation
FROM tenant_secrets
WHERE rotation_days > 0
  AND DATEDIFF(CURRENT_TIMESTAMP, last_rotated_at) >= rotation_days
  AND is_active = 1;
```

---

## Troubleshooting

### Connection Failed

```php
$result = CredentialManager::testDatabaseConnection(5);
echo $result['error'];  // See detailed error message
```

**Common Issues:**
- Wrong host/port
- User lacks permissions
- Password not properly decrypted
- Network/firewall blocking connection

### Encryption Errors

Ensure `APP_KEY` is set in `.env`:
```bash
APP_KEY=$(php -r "echo hash('sha256', random_bytes(32));")
```

### Migration Not Applied

Run the migration manually:
```bash
./start-dev.sh shell
mysql -h database -u nautilus -pnautilus123 nautilus < /var/www/html/database/migrations/998_environment_and_tenant_credentials.sql
```

---

## Support

For questions or issues:
1. Check logs: `./start-dev.sh logs`
2. Review audit log: `SELECT * FROM credential_rotation_log ORDER BY rotated_at DESC;`
3. Test connections: `CredentialManager::testDatabaseConnection()`

---

## Security Checklist

- [ ] Different credentials for each environment
- [ ] Strong passwords (32+ characters) in production
- [ ] APP_KEY set and secured
- [ ] .env never committed to git
- [ ] Secrets encrypted in database
- [ ] Regular credential rotation schedule
- [ ] Audit log monitoring
- [ ] Access control for credential management
- [ ] Backup encryption enabled
- [ ] Multi-factor authentication for admin accounts
