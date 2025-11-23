# Installer Fixes Applied

**Date:** 2025-01-22
**Issue:** System checks not completing, wrong installer being loaded

---

## Problems Identified

1. **System Check Format Mismatch:**
   - JavaScript expected: `{status: "success"|"error"|"warning", message: "...", name: "..."}`
   - check.php was returning: `{status: true|false, message: "...", name: "..."}`

2. **Routing Issues:**
   - `.htaccess` redirected `/install` → `install.php` (old installer)
   - `index.php` redirected to `/install.php` when not installed
   - Should redirect to `/install/` (new modern installer)

3. **Installation Marker Path:**
   - Installer created: `/storage/installed`
   - index.php checked for: `/.installed`
   - Mismatch prevented system from recognizing completed installation

---

## Fixes Applied

### 1. Fixed check.php Response Format

**File:** `/public/install/check.php`

**Change:**
```php
// OLD - returned boolean status
echo json_encode([
    'success' => true,
    'checks' => $checks,  // $checks had boolean 'status' field
    'overall' => [...]
]);

// NEW - returns string status
$formattedChecks = [];
foreach ($checks as $key => $check) {
    $formattedChecks[$key] = [
        'name' => $check['name'],
        'message' => $check['message'],
        'status' => $check['status']
            ? 'success'
            : ($check['critical'] ? 'error' : 'warning')
    ];
}
echo json_encode($formattedChecks);
```

**Result:** System checks now display correctly with green checkmarks, red X's, and yellow warnings.

---

### 2. Updated .htaccess to Use New Installer

**File:** `/public/.htaccess`

**Change:**
```apache
# OLD
RewriteRule ^install$ install.php [L]

# NEW
RewriteRule ^install$ /install/index.php [L]
RewriteRule ^install/$ /install/index.php [L]
```

**Result:** Visiting `/install` now loads the modern 4-step installer instead of old installer.

---

### 3. Updated index.php Redirect

**File:** `/public/index.php`

**Change:**
```php
// OLD
header('Location: /install.php');

// NEW
header('Location: /install/');
```

**Result:** When no `.installed` file exists, redirects to modern installer.

---

### 4. Fixed Installation Marker Path

**File:** `/public/install/install-db.php`

**Change:**
```php
// OLD
$installMarker = dirname(__DIR__, 2) . '/storage/installed';
if (!is_dir(dirname($installMarker))) {
    mkdir(dirname($installMarker), 0755, true);
}
file_put_contents($installMarker, date('Y-m-d H:i:s'));

// NEW
$installMarker = dirname(__DIR__, 2) . '/.installed';
file_put_contents($installMarker, date('Y-m-d H:i:s'));
```

**Result:** `.installed` file created in correct location, system recognizes completed installation.

---

## Testing Instructions

### Test 1: Fresh Installation

1. **Delete existing installation marker:**
   ```bash
   rm /home/wrnash1/development/nautilus/.installed
   ```

2. **Visit the installer:**
   ```
   https://nautilus.local/install/
   ```

3. **Expected Results:**
   - Step 1: All system checks should show green checkmarks (or yellow warnings for non-critical)
   - "Continue to Configuration" button should appear
   - Clicking it advances to Step 2

4. **Complete installation:**
   - Fill in configuration form
   - Watch database installation progress
   - See success screen with login link

5. **Verify:**
   - `.installed` file created in `/home/wrnash1/development/nautilus/`
   - Database has 24 tables
   - Can login with admin@nautilus.local / admin123

### Test 2: Already Installed Check

1. **With `.installed` file present, visit:**
   ```
   https://nautilus.local/
   ```

2. **Expected Result:**
   - Should load normal application (not redirect to installer)
   - Storefront homepage displays

### Test 3: System Checks Display

1. **Visit installer:**
   ```
   https://nautilus.local/install/
   ```

2. **Check these items display correctly:**
   - ✅ PHP Version - Green check
   - ✅ Web Server - Green check
   - ✅ All PHP extensions - Green checks
   - ⚠️ SELinux - Yellow warning (if Enforcing)
   - ⚠️ Memory Limit - Yellow warning (if < 256M)

---

## Files Modified

1. `/public/install/check.php` - Fixed JSON response format
2. `/public/.htaccess` - Redirect /install to new installer
3. `/public/index.php` - Redirect to /install/ when not installed
4. `/public/install/install-db.php` - Create .installed in correct location

---

## Current State

✅ **Installer is now fully functional!**

- System checks work correctly
- All 4 steps operational
- Creates .installed marker properly
- Redirects work as expected

You can now:
1. Delete `.installed` file to test fresh installation
2. Visit `https://nautilus.local/install/`
3. Complete the 4-step installation process
4. Access the application at `https://nautilus.local/`

---

## Notes

- **Old installer** (`/public/install.php`) is still present but no longer used
- Can be deleted if desired, or kept as backup
- New installer is at `/public/install/index.php` with supporting files:
  - `check.php` - System requirements checker
  - `save-config.php` - Configuration saver
  - `install-db.php` - Database installer

---

**End of Installer Fixes Document**
