# Nautilus Credentials - Quick Start Guide

## ✅ What's Been Implemented

You now have a complete multi-environment credential management system with:

- ✅ Separate .env files for development, staging, and production
- ✅ Database tables for storing encrypted credentials
- ✅ Per-tenant database isolation support
- ✅ API key and secret management per tenant
- ✅ Environment-specific settings
- ✅ Credential encryption/decryption
- ✅ Audit logging for credential changes

---

## 🚀 Quick Setup

### 1. Choose Your Environment

**Development (Current Setup):**
```bash
# Already configured - uses .env with nautilus/nautilus123
# Shared database for all tenants
```

**Staging:**
```bash
cp .env.staging.example .env
# Edit .env and update:
# - DB_HOST, DB_USERNAME, DB_PASSWORD
# - APP_KEY (generate new one)
```

**Production:**
```bash
cp .env.production.example .env
# Edit .env and update:
# - All database credentials (use strong passwords!)
# - APP_KEY (generate: php -r "echo bin2hex(random_bytes(32));")
# - All API keys for production services
```

---

## 📝 Common Tasks

### Set Up Dedicated Database for Enterprise Tenant

```php
use App\Core\CredentialManager;

CredentialManager::setTenantDatabaseCredentials(
    tenantId: 5,
    config: [
        'use_dedicated_db' => true,
        'host' => 'tenant5-db.example.com',
        'port' => 3306,
        'database' => 'nautilus_tenant_5',
        'username' => 'tenant5_user',
        'password' => 'VeryStrongPassword123!@#',
    ],
    environment: 'production'
);

// Test it works
$test = CredentialManager::testDatabaseConnection(5, 'production');
```

### Store Stripe Keys for a Tenant

```php
// Production Stripe keys
CredentialManager::setTenantSecret(
    tenantId: 5,
    serviceName: 'stripe',
    keyName: 'api_key',
    keyValue: 'pk_live_XXXXXXXXX',
    keyType: 'api_key'
);

CredentialManager::setTenantSecret(
    tenantId: 5,
    serviceName: 'stripe',
    keyName: 'secret_key',
    keyValue: 'sk_live_XXXXXXXXX',
    keyType: 'secret_key'
);

// Retrieve when needed
$stripeKey = CredentialManager::getTenantSecret(5, 'stripe', 'api_key');
```

### Get/Set Environment Settings

```php
// Get setting
$poolSize = CredentialManager::getEnvironmentSetting(
    'db_connection_pool_size',
    default: 10
);

// Set setting
CredentialManager::setEnvironmentSetting(
    key: 'max_upload_size',
    value: '50MB',
    isSensitive: false,
    description: 'Maximum file upload size'
);
```

---

## 🔐 Security Checklist

### Development
- [x] Using simple passwords (nautilus123) - OK for local
- [x] APP_DEBUG=true - OK for local
- [x] Shared database - OK for local

### Staging
- [ ] Update DB credentials (different from dev)
- [ ] Generate unique APP_KEY
- [ ] Use test API keys (Stripe test mode, etc.)
- [ ] Set APP_DEBUG=true (for debugging)

### Production
- [ ] **CRITICAL:** Strong database passwords (32+ chars)
- [ ] **CRITICAL:** Unique APP_KEY (never reuse from other envs)
- [ ] **CRITICAL:** Production API keys
- [ ] **CRITICAL:** APP_DEBUG=false
- [ ] **CRITICAL:** Never commit .env to git
- [ ] Dedicated databases for enterprise tenants
- [ ] HTTPS/SSL enabled
- [ ] Regular credential rotation (90 days)

---

## 📊 Database Tables Created

| Table | Purpose |
|-------|---------|
| `environment_settings` | Store app settings per environment |
| `tenant_database_credentials` | Per-tenant DB credentials (encrypted) |
| `tenant_secrets` | API keys, tokens, secrets per tenant |
| `credential_rotation_log` | Audit log of credential changes |

---

## 🔍 Verify Everything Works

```bash
./start-dev.sh shell

# Inside container:
cd /var/www/html && php -r "
require_once 'vendor/autoload.php';
\$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
\$dotenv->load();
require_once 'app/Core/Database.php';
require_once 'app/Core/CredentialManager.php';

use App\Core\CredentialManager;

// Test environment settings
\$setting = CredentialManager::getEnvironmentSetting('db_connection_pool_size');
echo \"DB Pool Size: \$setting\n\";

// Test tenant credentials
\$creds = CredentialManager::getTenantDatabaseCredentials(1);
echo \"DB Host: \" . \$creds['host'] . \"\n\";
echo \"✅ System working!\n\";
"
```

---

## 📚 Full Documentation

See [CREDENTIALS_MANAGEMENT.md](CREDENTIALS_MANAGEMENT.md) for:
- Detailed API documentation
- Security best practices
- Troubleshooting guide
- Credential rotation procedures
- Multi-tenant architecture details

---

## 🆘 Troubleshooting

**Problem:** Can't connect to tenant database
**Solution:** Run `CredentialManager::testDatabaseConnection($tenantId)` to see error

**Problem:** Encryption errors
**Solution:** Make sure APP_KEY is set in .env

**Problem:** Tables don't exist
**Solution:** Run migration: `mysql -h database -u nautilus -pnautilus123 nautilus < database/migrations/998_environment_and_tenant_credentials.sql`

---

## 🎯 Next Steps

1. **For Development:** Continue using current setup
2. **For Staging:** Copy .env.staging.example and configure
3. **For Production:**
   - Generate strong APP_KEY
   - Create production database users with strong passwords
   - Set up enterprise tenants with dedicated databases
   - Configure production API keys (Stripe, AWS, etc.)
   - Enable credential rotation schedule

---

## ⚡ Quick Commands

```bash
# View environment settings
mysql -h database -u nautilus -pnautilus123 nautilus -e "SELECT * FROM environment_settings WHERE environment='development';"

# View tenant DB configs
mysql -h database -u nautilus -pnautilus123 nautilus -e "SELECT * FROM v_tenant_db_config;"

# View tenant secrets (passwords hidden)
mysql -h database -u nautilus -pnautilus123 nautilus -e "SELECT tenant_id, service_name, key_name, key_type FROM tenant_secrets;"

# Check credential rotation log
mysql -h database -u nautilus -pnautilus123 nautilus -e "SELECT * FROM credential_rotation_log ORDER BY rotated_at DESC LIMIT 10;"
```
