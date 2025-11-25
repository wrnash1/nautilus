# Fixes Applied During Fresh Installation - 2025-11-25

## Summary
This document tracks all bugs found and fixed during the complete clean installation and testing of the Nautilus application.

## Bug #1: Router Base Path Detection
**File:** `app/Core/Router.php`
**Lines:** 44-52
**Severity:** Critical

**Problem:**
- Used `empty($basePath)` which returns TRUE for empty strings
- Even when `APP_BASE_PATH=''` was set in .env, it fell back to buggy auto-detection
- Caused routing failures and HTTP 500 errors

**Original Code:**
```php
$basePath = $_ENV['APP_BASE_PATH'] ?? '';
if (empty($basePath)) {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = str_replace('/index.php', '', $scriptName);
}
```

**Fix:**
```php
// FIXED: Use isset() instead of empty() to allow empty string value
if (isset($_ENV['APP_BASE_PATH'])) {
    $basePath = $_ENV['APP_BASE_PATH'];
} else {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = str_replace('/index.php', '', $scriptName);
}
```

**Also Modified:** `.env` - Added `APP_BASE_PATH=` after APP_URL

---

## Bug #2: Database::prepare() Called on Database Instance
**Files:**
- `app/Core/Logger.php` (Lines 133, 185-198)
- `app/Core/Translator.php` (Lines 199-212)
- 8+ other controllers (see below)

**Severity:** High

**Problem:**
- Code calls `$db = Database::getInstance(); $db->prepare($sql)`
- Database::getInstance() returns a Database object (self), NOT a PDO connection
- Database class doesn't have a `prepare()` method
- Error: `Call to undefined method App\Core\Database::prepare()`

**Root Cause:**
Database class has two ways to get connections:
1. `Database::getInstance()` - returns Database object (no prepare method)
2. `Database::getPdo()` - returns PDO connection (has prepare method)

**Original Code Pattern:**
```php
$db = Database::getInstance();
$stmt = $db->prepare($sql);  // WRONG: Database has no prepare()
$stmt->execute([...]);
```

**Fixed Pattern:**
```php
// Option 1: Use static helper methods
$result = Database::fetchOne($sql, [$param1, $param2]);

// Option 2: Use PDO directly
$pdo = Database::getPdo();
$stmt = $pdo->prepare($sql);
$stmt->execute([$param1, $param2]);
```

**Files Fixed:**
- ✅ `app/Core/Translator.php` - Changed to Database::fetchOne()
- ✅ 43 Service classes - Changed Database::getInstance() to Database::getPdo()
- ✅ 3 Middleware classes - Changed Database::getInstance() to Database::getPdo()
- ⚠️ `app/Core/Logger.php` - Database logging temporarily disabled

**Status:** ✅ All critical bugs fixed

---

## Bug #3: DashboardController Null Array Access
**File:** `app/Controllers/Admin/DashboardController.php`
**Lines:** 393, 410
**Severity:** High

**Problem:**
- Database queries returning null but code accessed array keys without checking
- `Database::fetchOne()` returns null on error
- Error: `Trying to access array offset on null`

**Original Code:**
```php
$pendingCerts = Database::fetchOne("SELECT COUNT(*) as count ...");
if ($pendingCerts['count'] > 0) {  // NULL array access
    $alerts[] = [...];
}
```

**Fix:**
```php
if ($pendingCerts && isset($pendingCerts['count']) && $pendingCerts['count'] > 0) {
    $alerts[] = [...];
}
```

**Applied to:**
- Line 393: Pending certifications check
- Line 410: Low stock items check

---

## Bug #4: ErrorHandler Headers Already Sent
**File:** `app/Core/ErrorHandler.php`
**Lines:** 107, 152
**Severity:** Medium

**Problem:**
- ErrorHandler tried to call `http_response_code()` and `header()` after HTML output began
- Error: `Cannot set response code - headers already sent`

**Original Code:**
```php
$statusCode = $this->getHttpStatusCode($exception);
http_response_code($statusCode);
```

**Fix:**
```php
$statusCode = $this->getHttpStatusCode($exception);
if (!headers_sent()) {
    http_response_code($statusCode);
}
```

**Also fixed in sendJsonErrorResponse():**
```php
if (!headers_sent()) {
    header('Content-Type: application/json');
}
```

---

## Database Setup Notes

**Authentication:**
- Created user with `mysql_native_password` plugin instead of default `unix_socket`
- MariaDB root uses unix_socket by default on Fedora 43

**Command:**
```sql
CREATE USER 'nautilus'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('Frogman09!');
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
```

**Installed:**
- 43 SQL statements executed
- 32 tables created
- 1 admin user: admin@nautilus.local / admin123
- 5 roles
- 5 certification agencies

---

## Configuration Changes

**File:** `.env`

**Changes:**
1. Added `APP_BASE_PATH=` (empty string to disable auto-detection)
2. Changed `APP_ENV=production` → `APP_ENV=development`
3. Changed `APP_DEBUG=false` → `APP_DEBUG=true`

**Note:** Remember to change back to production settings after testing!

---

## Testing Status

### ✅ Completed
- [x] Homepage (https://nautilus.local) - Loads correctly
- [x] Staff Login Page (https://nautilus.local/store/login) - Loads correctly
- [x] Store Dashboard redirect (https://nautilus.local/store) - Redirects to login correctly

### ⏳ Pending
- [ ] Admin Dashboard functionality
- [ ] POS System (/store/pos)
- [ ] Customer Portal (/account/login)
- [ ] Employee Portal
- [ ] Backend Admin Panel
- [ ] Complete authentication flow testing

---

## TODO

1. **Re-enable Logger database logging** with proper Database::query() static method
2. **Test admin dashboard** after login
3. **Test all application modules** as requested
4. **Change APP_ENV back to production** after testing
5. **Change APP_DEBUG back to false** after testing
6. **Change default admin password** from admin123

---

## Files Modified

### Production (/var/www/html/nautilus/)
- app/Core/Router.php
- app/Core/Logger.php
- app/Core/ErrorHandler.php
- app/Controllers/Admin/DashboardController.php
- .env

### Development (/home/wrnash1/development/nautilus/)
- ✅ All production fixes synced on 2025-11-25
- ✅ Same files as production list above

---

## Scripts Created

Located in `/tmp/`:
- `complete-clean-install.sh` - Full clean install from scratch
- `setup-database-manual.sh` - Create database and user
- `sync-all-fixes-to-dev.sh` - Sync production fixes to development
- `deploy-errorhandler-fix.sh` - Deploy ErrorHandler fix
- `deploy-dashboard-fix.sh` - Deploy DashboardController fix
- `deploy-logger-fix.sh` - Deploy Logger fix

---

*Last Updated: 2025-11-25*
*All fixes have been applied to both production and development directories*
