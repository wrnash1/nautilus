# ACTION REQUIRED: Deploy FK Constraint Fix

## Summary

I've fixed the foreign key constraint errors, but the fix needs to be deployed to your test servers.

**Problem Found**: The previous fix (commit 299b1f7) had a `preg_replace()` that was **stripping out** the FK disable commands, which is why you still saw 36 errors.

**Fix Applied**: Commit de5145c removes the buggy regex and properly wraps all migrations with FK disable.

---

## Commits Ready to Push

You have **2 new commits** that need to be pushed to GitHub:

```
de5145c - Fix FK disable - remove regex that was stripping FK checks
d67ac38 - Add verification tools and deployment guide for FK fix
```

---

## What You Need to Do

### 1. Push to GitHub (from this machine or use GitHub Desktop)

**Option A: Command line** (if you have git credentials set up)
```bash
cd /home/wrnash1/Developer/nautilus
git push origin devin/1760111706-nautilus-v6-complete-skeleton
```

**Option B: GitHub Desktop** (easier if you have it installed)
- Open GitHub Desktop
- Select the nautilus-v6 repository
- Click "Push origin"

---

### 2. On Pop!_OS Laptop

```bash
# Pull latest code
cd ~/Developer/nautilus
git pull origin devin/1760111706-nautilus-v6-complete-skeleton

# Verify you got the fix
php ~/Developer/nautilus/check-code-version.php
# Should say: "✓ FIXED VERSION DETECTED"

# Sync to web directory
sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage

# Verify web directory has the fix
php /var/www/html/nautilus/check-code-version.php
# Should say: "✓ FIXED VERSION DETECTED"

# Drop and recreate database
mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run installation
# Visit: https://pangolin.local/simple-install.php
```

**Expected Result**: **0 warnings** (not 36!) All 72 migrations succeed.

---

### 3. On RedHat Production Server

Same steps as Pop!_OS, but use `apache:apache` instead of `www-data:www-data`:

```bash
cd ~/Developer/nautilus
git pull origin devin/1760111706-nautilus-v6-complete-skeleton
php ~/Developer/nautilus/check-code-version.php

sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage

php /var/www/html/nautilus/check-code-version.php

mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Visit installation URL
```

---

## What Was Wrong (Technical Details)

### The Bug
```php
// Line 317 in OLD version - this was stripping FK disable!
$sql = preg_replace('/^SET\s+FOREIGN_KEY_CHECKS\s*=\s*[01]\s*;/mi', '', $sql);
```

This removed the `SET FOREIGN_KEY_CHECKS=0` from migration 000b_fix_base_tables.sql,
which meant none of the migrations had FK protection.

### The Fix
```php
// NEW version - no regex stripping, just wrap everything
$sql = "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\n" .
       $sql .
       "\nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;";
```

Wrapping is safe because the MySQL pattern saves and restores the FK check state.

---

## Files to Review

1. [CRITICAL-FIX-README.md](CRITICAL-FIX-README.md) - Full technical explanation
2. [check-code-version.php](check-code-version.php) - Version verification script
3. [app/Services/Install/InstallService.php](app/Services/Install/InstallService.php#L313-L321) - The actual fix

---

## Status Check

Run this on any server to see if the fix is deployed:

```bash
php /path/to/nautilus/check-code-version.php
```

- ✓ "FIXED VERSION DETECTED" = Ready to test
- ✗ "OLD BUGGY VERSION" = Need to sync code
- ✗ "FILE NOT FOUND" = Need to copy code from Developer directory

---

## Questions?

If you still see FK errors after following all steps:
1. Run `check-code-version.php` on the web server
2. Check `/var/log/apache2/error.log` (or `/var/log/httpd/error_log` on RedHat)
3. Verify database is completely empty before installation
4. Check that PHP mysqli extension is enabled
