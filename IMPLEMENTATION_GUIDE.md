# Security Enhancement Implementation Guide

## What Was Implemented

This guide documents the security enhancements added to Nautilus v6.0 to protect sensitive API keys and credentials.

---

## Summary of Changes

### 1. **Encryption System** ✅

**File:** [`app/Core/Encryption.php`](app/Core/Encryption.php)

A new encryption utility class using AES-256-CBC encryption:
- Encrypts sensitive data before storing in database
- Decrypts data when retrieved
- Masks values for display (shows last 4 characters)
- Uses `APP_KEY` from `.env` as master encryption key

### 2. **Enhanced Settings Service** ✅

**File:** [`app/Services/Admin/SettingsService.php`](app/Services/Admin/SettingsService.php)

Updated to automatically handle encryption:
- 12 sensitive settings automatically encrypted (Stripe, Square, Twilio, etc.)
- Transparent encryption/decryption when saving/loading
- Audit logging for sensitive setting changes
- New helper methods: `getMaskedSetting()`, `isEncryptedSetting()`

### 3. **Database Migration** ✅

**File:** [`database/migrations/015_add_settings_encryption_and_audit.sql`](database/migrations/015_add_settings_encryption_and_audit.sql)

Database changes:
- New `settings_audit` table for tracking sensitive data access
- Updates existing sensitive settings to `encrypted` type
- Performance indexes for settings queries
- System metadata tracking

### 4. **Secure Input Component** ✅

**File:** [`app/Views/components/secure-input.php`](app/Views/components/secure-input.php)

Reusable UI component for sensitive inputs:
- Password-masked input fields
- Shows masked current value (e.g., `••••••••1234`)
- Toggle visibility button
- Help text and validation

### 5. **Enhanced Admin Views** ✅

**File:** [`app/Views/admin/settings/payment.php`](app/Views/admin/settings/payment.php)

Comprehensive payment settings interface:
- Stripe, Square, and BTCPay configuration
- Secure masked inputs for API keys
- Security warnings and best practices
- PCI compliance information
- Test mode indicators

### 6. **Security Documentation** ✅

**File:** [`docs/SECURITY.md`](docs/SECURITY.md)

Complete security reference:
- Credential management best practices
- Encryption implementation details
- API key rotation procedures
- Audit logging usage
- Production deployment checklist
- Incident response procedures

---

## Installation Instructions

### Step 1: Run Database Migration

```bash
# Navigate to project directory
cd /home/wrnash1/development/nautilus

# Run the migration (method depends on your migration system)
# Option A: If using migration runner
php artisan migrate

# Option B: Run SQL directly
mysql -u root -p nautilus < database/migrations/015_add_settings_encryption_and_audit.sql
```

### Step 2: Verify APP_KEY

Ensure your `.env` file has a strong `APP_KEY` (at least 32 characters):

```bash
# Check current APP_KEY
grep APP_KEY .env

# If empty or too short, generate a new one
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;"

# Update .env with the generated key
```

### Step 3: Test Encryption

Create a test script to verify encryption is working:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Core\Encryption;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test encryption
if (Encryption::test()) {
    echo "✅ Encryption is working correctly!\n";
} else {
    echo "❌ Encryption failed! Check APP_KEY in .env\n";
}
```

### Step 4: Re-enter Sensitive API Keys

Since existing settings are stored as plaintext, you need to re-enter them via the admin panel to encrypt them:

1. Log in to admin panel
2. Go to **Settings → Payment** or **Settings → Integrations**
3. Re-enter each API key/secret
4. Click "Save"
5. Values are now encrypted in the database

**Note:** You can leave fields blank to keep existing values, or enter new values to update.

---

## Usage Examples

### Encrypting Custom Settings

To add a new encrypted setting:

1. **Add to encrypted settings list:**

```php
// In app/Services/Admin/SettingsService.php
private const ENCRYPTED_SETTINGS = [
    'stripe_secret_key',
    'square_access_token',
    'your_new_secret_key',  // Add here
];
```

2. **Use normally - encryption is automatic:**

```php
use App\Services\Admin\SettingsService;

$settingsService = new SettingsService();

// Save - automatically encrypted
$settingsService->updateSetting('integrations', 'your_new_secret_key', 'secret_value_12345');

// Retrieve - automatically decrypted
$value = $settingsService->getSetting('integrations', 'your_new_secret_key');
// Returns: "secret_value_12345"

// Get masked version for display
$masked = $settingsService->getMaskedSetting('integrations', 'your_new_secret_key');
// Returns: "••••••••••2345"
```

### Using Secure Input Component in Views

```php
<?php
// In your settings view file
require __DIR__ . '/../../components/secure-input.php';
?>

<form method="POST" action="/admin/settings/update">
    <?= renderSecureInput(
        'api_secret',                          // Field name
        'API Secret Key',                      // Label
        $settings['api_secret'] ?? '',         // Current value
        'Enter your secret key',               // Placeholder
        'Get this from your provider dashboard' // Help text
    ) ?>

    <button type="submit">Save Settings</button>
</form>
```

### Viewing Audit Logs

```php
<?php
use App\Core\Database;

// Get recent audit events
$auditLog = Database::fetchAll(
    "SELECT sa.*, u.email AS user_email
     FROM settings_audit sa
     LEFT JOIN users u ON sa.user_id = u.id
     WHERE sa.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
     ORDER BY sa.created_at DESC
     LIMIT 50"
);

foreach ($auditLog as $entry) {
    echo "{$entry['created_at']} - {$entry['user_email']} {$entry['action']} {$entry['setting_key']} from {$entry['ip_address']}\n";
}
```

---

## Security Best Practices

### ✅ DO

- **Use strong APP_KEY** (32+ characters, randomly generated)
- **Set file permissions** on `.env` to `600`
- **Use test API keys** in development environments
- **Rotate keys** if compromised or employee leaves
- **Review audit logs** regularly for suspicious activity
- **Keep APP_DEBUG=false** in production

### ❌ DON'T

- **Never commit `.env`** to version control (already in `.gitignore`)
- **Never log plaintext** API keys or secrets
- **Never share APP_KEY** publicly
- **Never use production keys** in development
- **Never display full keys** in UI (always mask)

---

## Testing Checklist

After implementation, verify:

- [ ] Run database migration successfully
- [ ] `APP_KEY` is set and at least 32 characters
- [ ] Encryption test passes: `Encryption::test()` returns `true`
- [ ] Can save a test setting via admin panel
- [ ] Setting is encrypted in database (check `settings` table)
- [ ] Setting decrypts correctly when retrieved
- [ ] Masked value shows in UI (e.g., `••••••••1234`)
- [ ] Audit log records the change in `settings_audit` table
- [ ] `.env` file has permissions `600`
- [ ] Web server blocks access to `.env` file

---

## File Permissions Checklist

```bash
# Set proper permissions
chmod 600 .env                     # Environment file (read/write for owner only)
chmod 755 public                   # Public directory (readable)
chmod 750 app database storage     # Application directories (owner + group)
chown -R www-data:www-data .       # Web server ownership

# Verify
ls -la .env                        # Should show: -rw------- 1 www-data www-data
```

---

## Troubleshooting

### "Encryption failed" Error

**Cause:** `APP_KEY` not set or too short

**Fix:**
```bash
# Generate a new APP_KEY
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

# Add to .env
echo "APP_KEY=<generated-key>" >> .env
```

### "Decryption failed" Error

**Cause:** Data was encrypted with a different `APP_KEY`

**Fix:** If you changed `APP_KEY`, you need to re-encrypt all settings:
1. Change `APP_KEY` back to the old value
2. Export all encrypted settings
3. Update `APP_KEY` to new value
4. Re-import and save settings (they'll be re-encrypted)

### Settings Not Encrypting

**Check:**
1. Is the setting key in `ENCRYPTED_SETTINGS` array?
2. Is `APP_KEY` properly set in `.env`?
3. Check PHP error logs: `tail -f /var/log/php_errors.log`

### Audit Logs Not Recording

**Check:**
1. Did migration 015 run successfully?
2. Does `settings_audit` table exist?
3. Check database user has INSERT permissions on `settings_audit`

---

## Migration Rollback (Emergency)

If you need to rollback these changes:

```sql
-- Drop audit table
DROP TABLE IF EXISTS settings_audit;

-- Drop metadata table
DROP TABLE IF EXISTS system_metadata;

-- Revert encrypted settings to string type
UPDATE settings SET setting_type = 'string' WHERE setting_type = 'encrypted';

-- Remove indexes (if needed)
ALTER TABLE settings DROP INDEX IF EXISTS idx_category_key;
ALTER TABLE settings DROP INDEX IF EXISTS idx_setting_type;
ALTER TABLE settings DROP INDEX IF EXISTS idx_updated_at;
```

**Warning:** Encrypted values will remain encrypted in the database. You'll need to manually re-enter them.

---

## Next Steps

1. **Configure Payment Gateways**
   - Go to `/admin/settings/payment`
   - Enter your Stripe/Square API keys
   - Test payment processing

2. **Configure Integrations**
   - Go to `/admin/settings/integrations`
   - Set up PADI, Twilio, or other services
   - Test connections

3. **Review Security Documentation**
   - Read [`docs/SECURITY.md`](docs/SECURITY.md)
   - Implement production hardening steps
   - Set up regular audit log reviews

4. **Monitor Audit Logs**
   - Create a weekly review process
   - Look for suspicious access patterns
   - Document any security incidents

---

## Support

For questions or issues:

- **Security Issues:** See [`docs/SECURITY.md`](docs/SECURITY.md#support)
- **General Questions:** Create a GitHub issue
- **Implementation Help:** Review this guide and security documentation

---

## Version Information

- **Implementation Date:** 2025-10-19
- **Nautilus Version:** 6.0
- **Migration:** 015_add_settings_encryption_and_audit.sql
- **PHP Version Required:** 7.4+
- **Dependencies:** OpenSSL PHP extension

---

## Files Modified/Created

### Created:
- `app/Core/Encryption.php` - Encryption utility class
- `app/Views/components/secure-input.php` - Secure input component
- `database/migrations/015_add_settings_encryption_and_audit.sql` - Database migration
- `docs/SECURITY.md` - Security documentation
- `IMPLEMENTATION_GUIDE.md` - This file

### Modified:
- `app/Services/Admin/SettingsService.php` - Added encryption support
- `app/Views/admin/settings/payment.php` - Enhanced with secure inputs
- `app/Views/admin/settings/integrations.php` - Will be enhanced next

### Database Tables:
- `settings` - Added encrypted type support
- `settings_audit` - New audit logging table
- `system_metadata` - New metadata tracking table

---

**End of Implementation Guide**
