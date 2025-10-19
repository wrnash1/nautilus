# Security Documentation - Nautilus v6.0

## Table of Contents

1. [Credential Management](#credential-management)
2. [Encryption](#encryption)
3. [Environment Variables](#environment-variables)
4. [Database Security](#database-security)
5. [API Key Best Practices](#api-key-best-practices)
6. [Audit Logging](#audit-logging)
7. [Production Deployment](#production-deployment)
8. [Security Checklist](#security-checklist)

---

## Credential Management

### Overview

Nautilus uses a **hybrid security model** for managing sensitive credentials:

- **Infrastructure secrets** → `.env` file (database credentials, encryption keys)
- **Integration secrets** → Encrypted database settings (API keys, tokens)
- **Business settings** → Plaintext database (non-sensitive configuration)

### Sensitive Credentials (Encrypted in Database)

The following settings are automatically encrypted using AES-256-CBC:

**Payment Gateways:**
- `stripe_secret_key` - Stripe Secret API Key
- `stripe_webhook_secret` - Stripe Webhook Signing Secret
- `square_access_token` - Square Access Token
- `btcpay_api_key` - BTCPay Server API Key

**Communications:**
- `twilio_auth_token` - Twilio Authentication Token
- `smtp_password` - Email server password

**Integrations:**
- `padi_api_key` - PADI API Key
- `padi_api_secret` - PADI API Secret
- `ssi_api_key` - SSI API Key
- `wave_access_token` - Wave Apps Access Token

**Shipping:**
- `ups_password` - UPS API Password
- `fedex_secret_key` - FedEx Secret Key

---

## Encryption

### How It Works

Nautilus uses the **`App\Core\Encryption`** class for encrypting sensitive data at rest.

**Algorithm:** AES-256-CBC (Advanced Encryption Standard, 256-bit key, Cipher Block Chaining)

**Key Derivation:**
- Master key: `APP_KEY` from `.env` file
- Derived encryption key: SHA-256 hash of `APP_KEY` (produces 32-byte key)

**Process:**
1. Generate random 16-byte IV (Initialization Vector) for each encryption
2. Encrypt plaintext using AES-256-CBC with derived key and IV
3. Prepend IV to ciphertext: `[16-byte IV][encrypted data]`
4. Base64-encode the result for storage

### Usage Example

```php
use App\Core\Encryption;

// Encrypt a value
$encrypted = Encryption::encrypt('sk_live_secret_key_12345');

// Decrypt a value
$plaintext = Encryption::decrypt($encrypted);

// Mask a value for display (shows last 4 chars)
$masked = Encryption::mask('sk_live_secret_key_12345', 4);
// Returns: "••••••••••••••••2345"

// Check if encryption is configured
if (Encryption::isConfigured()) {
    // Safe to encrypt
}

// Test encryption/decryption
if (Encryption::test()) {
    // Encryption is working properly
}
```

### Security Properties

✅ **Authenticated Encryption:** Each encryption uses a unique random IV
✅ **Cryptographically Secure:** Uses PHP's `random_bytes()` for IV generation
✅ **Industry Standard:** AES-256-CBC is NIST-approved and widely vetted
✅ **Key Derivation:** SHA-256 hash prevents weak key issues

⚠️ **Important:** Never commit `APP_KEY` to version control!

---

## Environment Variables

### `.env` File Structure

```env
# === CRITICAL SECURITY KEYS ===
APP_KEY=<32+ character random string>    # Master encryption key
JWT_SECRET=<64+ character random string> # JWT signing key

# === DATABASE CREDENTIALS ===
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=<strong-database-password>

# === APPLICATION SETTINGS ===
APP_ENV=production                       # production|development|local
APP_DEBUG=false                          # MUST be false in production
APP_URL=https://yourdomain.com
```

### Generating Secure Keys

During installation, keys are auto-generated. To manually generate:

```php
// Generate APP_KEY (32 bytes = 64 hex chars)
$appKey = bin2hex(random_bytes(32));

// Generate JWT_SECRET (64 bytes = 128 hex chars)
$jwtSecret = bin2hex(random_bytes(64));
```

Or use command line:

```bash
# Generate APP_KEY
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# Generate JWT_SECRET
php -r "echo bin2hex(random_bytes(64)) . PHP_EOL;"
```

### File Permissions

```bash
# Set proper ownership
chown www-data:www-data .env

# Restrict read access to web server user only
chmod 600 .env

# Verify permissions
ls -la .env
# Should show: -rw------- 1 www-data www-data
```

### Web Server Protection

**Apache (.htaccess):**
```apache
# Deny access to .env file
<FilesMatch "^\.env">
    Require all denied
</FilesMatch>
```

**Nginx:**
```nginx
# Deny access to .env file
location ~ /\.env {
    deny all;
    return 404;
}
```

---

## Database Security

### Settings Table Schema

```sql
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(50) NOT NULL,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `setting_type` ENUM('string', 'integer', 'boolean', 'json', 'encrypted'),
  `description` TEXT,
  `updated_by` INT UNSIGNED,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_setting` (`category`, `setting_key`)
);
```

**Setting Types:**
- `string` - Plain text (non-sensitive)
- `integer` - Numeric values
- `boolean` - True/false flags
- `json` - Complex data structures
- `encrypted` - **Sensitive data, automatically encrypted**

### Automatic Encryption

Settings are **automatically encrypted/decrypted** based on their key name:

```php
// In SettingsService.php
private const ENCRYPTED_SETTINGS = [
    'stripe_secret_key',
    'square_access_token',
    'twilio_auth_token',
    // ... more sensitive keys
];
```

When you save a setting with a key in `ENCRYPTED_SETTINGS`, it's:
1. Automatically encrypted before INSERT/UPDATE
2. Automatically decrypted when retrieved
3. Logged in audit trail

### Connection Security

```php
// Database.php uses secure PDO options
$pdo = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,          // Real prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);
```

**Best Practices:**
- All queries use prepared statements (prevents SQL injection)
- Database user should have minimum required privileges
- Use strong database password (16+ characters, mixed case, numbers, symbols)
- Consider using SSL/TLS for database connections in production

---

## API Key Best Practices

### Development vs Production Keys

| Environment | Stripe | Square | BTCPay |
|-------------|--------|--------|--------|
| **Development** | `pk_test_...` / `sk_test_...` | Sandbox keys | Testnet |
| **Production** | `pk_live_...` / `sk_live_...` | Production keys | Mainnet |

**Never** use production API keys in development environments!

### Key Rotation

To rotate a compromised API key:

1. **Generate new key** in the provider's dashboard
2. **Update in Nautilus:**
   - Admin Panel → Settings → Payment/Integrations
   - Enter new key (old value will be shown masked)
   - Save settings
3. **Old key automatically encrypted** and overwritten in database
4. **Verify** new key works
5. **Revoke old key** in provider's dashboard

### Key Storage Checklist

✅ Stored encrypted in database
✅ Never logged in plain text
✅ Never exposed in error messages
✅ Masked in admin UI (shows last 4 chars only)
✅ Access logged in `settings_audit` table
✅ Only accessible by admins
✅ Never committed to git

---

## Audit Logging

### Settings Audit Table

Every access/modification to sensitive settings is logged:

```sql
CREATE TABLE `settings_audit` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(150) NOT NULL,     -- e.g., "payment.stripe_secret_key"
  `action` ENUM('read', 'update', 'delete'),
  `user_id` INT UNSIGNED,                  -- Who accessed it
  `ip_address` VARCHAR(45),                -- From where
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Viewing Audit Logs

```sql
-- View all sensitive setting changes
SELECT
    sa.setting_key,
    sa.action,
    u.email AS user_email,
    sa.ip_address,
    sa.created_at
FROM settings_audit sa
LEFT JOIN users u ON sa.user_id = u.id
ORDER BY sa.created_at DESC
LIMIT 100;

-- Find who last updated Stripe key
SELECT * FROM settings_audit
WHERE setting_key = 'payment.stripe_secret_key'
ORDER BY created_at DESC
LIMIT 1;

-- Suspicious activity (multiple failed access attempts)
SELECT ip_address, COUNT(*) as attempts
FROM settings_audit
WHERE action = 'read'
  AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_address
HAVING attempts > 10;
```

### Audit Retention

By default, audit logs are kept indefinitely. To implement retention:

```sql
-- Delete audit logs older than 1 year
DELETE FROM settings_audit
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Archive old logs before deletion
CREATE TABLE settings_audit_archive LIKE settings_audit;
INSERT INTO settings_audit_archive
SELECT * FROM settings_audit
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

---

## Production Deployment

### Pre-Deployment Security Checklist

- [ ] `APP_ENV=production` in `.env`
- [ ] `APP_DEBUG=false` in `.env`
- [ ] Strong `APP_KEY` (32+ chars) generated
- [ ] Strong `JWT_SECRET` (64+ chars) generated
- [ ] `.env` file permissions set to `600`
- [ ] `.env` owned by web server user
- [ ] `.env` blocked by web server config
- [ ] Database user has minimum required privileges
- [ ] All production API keys are **live/production** versions
- [ ] SSL/TLS certificate installed and enforced
- [ ] Run database migration 015 (encryption & audit)

### Hardening Commands

```bash
# Set proper file permissions
chmod 600 .env
chmod 755 public
chmod 750 app database storage
chown -R www-data:www-data /path/to/nautilus

# Disable dangerous PHP functions (php.ini)
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

# Enable PHP security settings (php.ini)
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

### Web Server Security Headers

**Apache (.htaccess):**
```apache
# Security Headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

# HTTPS Redirect
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

**Nginx:**
```nginx
# Security Headers
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;

# HTTPS Redirect
if ($scheme != "https") {
    return 301 https://$server_name$request_uri;
}
```

---

## Security Checklist

### Installation

- [x] Auto-generates `APP_KEY` and `JWT_SECRET`
- [x] Creates admin user with bcrypt password hash
- [x] Sets up encrypted settings infrastructure
- [x] Creates audit logging table

### Daily Operations

- [ ] Review audit logs weekly for suspicious activity
- [ ] Keep production API keys separate from development
- [ ] Never email or message API keys (use secure methods)
- [ ] Rotate API keys if employee leaves with access
- [ ] Monitor for security updates to PHP, MySQL, dependencies

### Incident Response

If you suspect a key has been compromised:

1. **Immediately rotate** the compromised key
2. **Review audit logs** to identify unauthorized access
3. **Check transaction logs** for fraudulent activity
4. **Notify payment provider** if payment keys were compromised
5. **Change database password** if database was accessed
6. **Regenerate APP_KEY** if encryption key was compromised
   - ⚠️ **Warning:** Regenerating `APP_KEY` will make all encrypted settings unreadable!
   - You'll need to re-enter all API keys after regenerating

### Encryption Key Rotation (Advanced)

If you need to rotate the `APP_KEY`:

```php
// 1. Decrypt all encrypted settings with old key
$settings = Database::fetchAll("SELECT * FROM settings WHERE setting_type = 'encrypted'");
$decrypted = [];
foreach ($settings as $setting) {
    $decrypted[] = [
        'id' => $setting['id'],
        'value' => Encryption::decrypt($setting['setting_value'])
    ];
}

// 2. Update APP_KEY in .env file
// (manually edit .env with new key)

// 3. Re-encrypt all settings with new key
foreach ($decrypted as $item) {
    $encrypted = Encryption::encrypt($item['value']);
    Database::execute(
        "UPDATE settings SET setting_value = ? WHERE id = ?",
        [$encrypted, $item['id']]
    );
}
```

---

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [PCI DSS Compliance](https://www.pcisecuritystandards.org/)
- [Stripe Security](https://stripe.com/docs/security)
- [Square Security](https://developer.squareup.com/docs/build-basics/using-rest-api)

---

## Support

For security-related questions or to report vulnerabilities:

- **Email:** security@nautilusdiveshop.com
- **GitHub Issues:** For non-critical security enhancements
- **Private Disclosure:** For critical vulnerabilities (do not create public issues)

---

**Last Updated:** 2025-10-19
**Version:** 6.0
**Migration:** 015_add_settings_encryption_and_audit.sql
