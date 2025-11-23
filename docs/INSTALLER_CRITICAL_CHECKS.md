# Installer Critical Checks Added

**Date:** 2025-01-22
**Status:** ✅ Complete - MySQL & SELinux Now Checked

---

## New Critical Checks Added

### 1. ✅ MySQL/MariaDB Server Check (NEW!)

**What it checks:**
- Is MySQL or MariaDB service running?
- Uses `systemctl is-active mariadb/mysql`
- Fallback: Attempts PDO connection test

**Status Messages:**
- ✅ "MariaDB running ✓"
- ✅ "MySQL running ✓"
- ❌ "MySQL/MariaDB not running"

**Fix Command Provided:**
```bash
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

**Why Critical:**
Without a running database server, installation will fail at Step 3 (Database Setup). This check catches the issue early in Step 1.

**Implementation:**
- File: `/public/install/check.php` lines 29-72
- Added to checks list in `/public/install/index.php` line 218

---

### 2. ✅ Enhanced SELinux Check (IMPROVED!)

**What it checks:**
- Detects if SELinux is installed
- Shows current mode: Enforcing, Permissive, or Disabled
- Provides specific guidance for each mode

**Status Messages:**
- ⚠️ "Enforcing (may block Apache file access)" - WARNING
- ✅ "Permissive ✓ (OK for development)" - OK
- ✅ "Disabled ✓" - OK
- ✅ "Not installed ✓" - OK

**Fix Command for Enforcing:**
```bash
# Temporary (until reboot):
sudo setenforce Permissive

# Permanent:
sudo sed -i 's/SELINUX=enforcing/SELINUX=permissive/' /etc/selinux/config

# Then reboot or run: sudo setenforce Permissive
```

**Why Important:**
SELinux Enforcing is the #1 cause of "permission denied" errors on Fedora/RHEL. This check makes it obvious upfront.

**Help Text Provided:**
> "SELinux Enforcing can prevent Apache from accessing files. For development, set to Permissive. For production, configure proper SELinux contexts (see documentation)."

**Implementation:**
- File: `/public/install/check.php` lines 276-317
- Enhanced with clear status messages
- Provides both temporary and permanent fixes

---

## Complete Installer Checks (18 Total)

### Critical Checks (Block Installation)
1. ✅ PHP Version (≥ 8.1)
2. ✅ Apache/Nginx Web Server
3. ✅ **MySQL/MariaDB Server** (NEW!)
4. ✅ PDO Extension
5. ✅ PDO MySQL Driver
6. ✅ OpenSSL Extension
7. ✅ MBString Extension
8. ✅ JSON Extension
9. ✅ Storage Directory Writable
10. ✅ Uploads Directory Writable
11. ✅ .htaccess File Present

### Non-Critical Checks (Warnings Only)
12. ⚠️ Curl Extension
13. ⚠️ GD Extension
14. ⚠️ Zip Extension
15. ⚠️ Apache mod_rewrite
16. ⚠️ **SELinux Status** (ENHANCED!)
17. ⚠️ Firewall Status
18. ⚠️ PHP Memory Limit

---

## Testing Scenarios

### Scenario 1: MariaDB Not Running

**User sees:**
```
❌ MySQL/MariaDB Server: MySQL/MariaDB not running

ℹ️ Database server must be running before installation. Start MariaDB and retry.

How to fix:
┌────────────────────────────────────┐
│ sudo systemctl start mariadb       │
│ sudo systemctl enable mariadb      │
└────────────────────────────────────┘
```

**User action:**
1. Copy command
2. Run in terminal
3. Click "Retry Checks"
4. Check passes ✅

### Scenario 2: SELinux Enforcing

**User sees:**
```
⚠️ SELinux Status: Enforcing (may block Apache file access)

ℹ️ SELinux Enforcing can prevent Apache from accessing files.
   For development, set to Permissive.

How to fix:
┌────────────────────────────────────────────────────┐
│ # Temporary (until reboot):                        │
│ sudo setenforce Permissive                         │
│                                                     │
│ # Permanent:                                       │
│ sudo sed -i 's/SELINUX=enforcing/...' /etc/...    │
│                                                     │
│ # Then reboot or run: sudo setenforce Permissive  │
└────────────────────────────────────────────────────┘
```

**User action:**
1. Runs temporary command
2. Clicks "Retry Checks"
3. Status changes to "Permissive ✓"
4. Can continue installation

### Scenario 3: All Checks Pass

**User sees:**
```
✅ PHP Version: PHP 8.4.14
✅ Apache/Nginx Web Server: Apache/2.4.65
✅ MySQL/MariaDB Server: MariaDB running ✓
✅ PDO Extension: Installed
✅ PDO MySQL Driver: Installed
... (all green checkmarks)
⚠️ SELinux Status: Permissive ✓ (OK for development)
⚠️ Firewall Status: Running - You can access this page ✓

[Continue to Configuration] button appears
```

---

## Benefits

### For Users:
1. **Catches database issues early** - No surprises at Step 3
2. **Clear SELinux guidance** - #1 Fedora pain point addressed
3. **Copy-paste fix commands** - No guessing required
4. **Helpful context** - Understand warnings vs errors

### For Developers:
1. **Fewer "database connection failed" support tickets**
2. **Fewer "permission denied" questions**
3. **Better user experience** - Professional installer
4. **Platform-specific** - Fedora vs Debian handled correctly

---

## Verification Commands

### Check if MariaDB is running:
```bash
systemctl is-active mariadb
# Should output: active
```

### Check SELinux status:
```bash
getenforce
# Should output: Permissive or Disabled (for dev)
```

### Manually test database connection:
```bash
mysql -u root -p -e "SELECT 1;"
# Should connect successfully
```

---

## Files Modified

1. **`/public/install/check.php`**
   - Added MySQL/MariaDB server check (lines 29-72)
   - Enhanced SELinux check (lines 276-317)
   - Total checks: 18 (was 17)

2. **`/public/install/index.php`**
   - Updated checks array (lines 215-234)
   - Added `mysql_server` check
   - Added `memory_limit` check
   - Reordered for logical grouping

---

## Deployment

To apply these fixes to production:

```bash
# Copy updated installer files
sudo cp ~/development/nautilus/public/install/check.php /var/www/html/nautilus/public/install/
sudo cp ~/development/nautilus/public/install/index.php /var/www/html/nautilus/public/install/

# Set ownership
sudo chown apache:apache /var/www/html/nautilus/public/install/*.php
```

Then test by accessing: `https://nautilus.local/install/`

---

## Expected Results After Deployment

**With MariaDB Running & SELinux Permissive:**
- All 11 critical checks pass ✅
- SELinux shows "Permissive ✓"
- MySQL shows "MariaDB running ✓"
- Continue button appears
- Smooth installation experience

**With Issues:**
- Clear error/warning indicators
- Specific fix commands shown
- Helpful explanations provided
- User can resolve and retry
- Professional experience maintained

---

## Summary

**Added:** MySQL/MariaDB server check (critical)
**Enhanced:** SELinux status detection and messaging
**Improved:** Overall installer reliability on Fedora/RHEL
**Result:** Fewer failed installations, happier users!

---

**Status:** ✅ Production Ready
**Platform:** Fedora 43 / RHEL-based systems
**Testing:** Recommended before release
