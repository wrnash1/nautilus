# Foreign Key Constraint Fix - Final Guide

## The Problem
Installation was failing with 36 FK constraint errors (errno 150) during database migration.

## The Solution (Commit bcf2c49)
Disable FK checks at **connection level** before executing multi_query():

```php
// app/Services/Install/InstallService.php lines 319-335
$mysqli->query("SET FOREIGN_KEY_CHECKS=0");
$mysqli->multi_query($sql);
$mysqli->query("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
```

## Why Previous Attempts Failed
- Wrapping SQL with SET commands doesn't work with multi_query()
- MySQL checks FK constraints during SQL parsing, before SET executes
- Need to disable at connection level FIRST, then run multi_query()

---

## Deployment Instructions

### 1. Push Latest Code
```bash
cd /home/wrnash1/Developer/nautilus
git push origin devin/1760111706-nautilus-v6-complete-skeleton
```

### 2. On Pop!_OS Server
```bash
# Pull latest code
cd ~/Developer/nautilus
git pull origin devin/1760111706-nautilus-v6-complete-skeleton

# Verify the fix is present
php check-code-version.php
# Should show: "✓ LATEST FIXED VERSION DETECTED (bcf2c49)"

# Optional: Test FK disable works
php test-fk-actual-error.php

# Sync to web directory
sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage

# Drop and recreate database
mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run installation
# Visit: https://pangolin.local/simple-install.php
```

### 3. On RedHat Server
Same as Pop!_OS, but use `apache:apache` instead of `www-data:www-data`

---

## Expected Results
- **Before fix:** 36 warnings, many tables missing
- **After fix:** 0 warnings, all 72 migrations succeed

---

## Verification Tools

### check-code-version.php
Checks which version of InstallService.php is deployed:
```bash
php /var/www/html/nautilus/check-code-version.php
```

### test-fk-actual-error.php
Tests if FK disable actually works on your database:
```bash
php ~/Developer/nautilus/test-fk-actual-error.php
```

---

## Commits Ready to Push
- `bcf2c49` - Connection-level FK disable (THE FIX)
- `72a6329` - Updated version checker
- `0709791` - Diagnostic test
- `8069b58` - Cleanup redundant files

## File Changed
- [app/Services/Install/InstallService.php](app/Services/Install/InstallService.php#L316-L335)
