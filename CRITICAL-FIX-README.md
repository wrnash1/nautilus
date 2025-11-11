# CRITICAL FIX: Foreign Key Constraint Errors

## What Was Wrong

The previous "fix" (commit 299b1f7) had a **critical bug**:
- Line 317 had `preg_replace()` that **stripped out** all `SET FOREIGN_KEY_CHECKS` commands
- This removed the FK disable from the one migration that had it (000b_fix_base_tables.sql)
- Result: All 72 migrations ran WITHOUT foreign key protection
- Caused 36 migrations to fail with errno 150

## What's Fixed Now

**New commit: de5145c**
- Removed the buggy `preg_replace()` line
- Now ALWAYS wraps every migration with FK disable
- Uses MySQL standard pattern that saves and restores FK check state
- Wrapper is part of the `multi_query()` batch so it executes correctly

## How to Deploy and Test

### Step 1: Pull Latest Code (on Pop!_OS)

```bash
cd ~/Developer/nautilus
git pull origin devin/1760111706-nautilus-v6-complete-skeleton
```

Expected output:
```
remote: Enumerating objects: 5, done.
remote: Counting objects: 100% (5/5), done.
...
Updating 299b1f7..de5145c
Fast-forward
 app/Services/Install/InstallService.php | 12 ++++++------
 1 file changed, 6 insertions(+), 6 deletions(-)
```

### Step 2: Verify Code Version

```bash
php ~/Developer/nautilus/check-code-version.php
```

Expected output:
```
✓ FIXED VERSION DETECTED

This version has the correct FK disable wrapper.
Code is ready to test!
```

If you see "OLD BUGGY VERSION" then the pull didn't work or you're checking the wrong directory.

### Step 3: Sync to Web Directory

```bash
sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

### Step 4: Verify Web Directory Has Fix

Visit in browser or run:
```bash
php /var/www/html/nautilus/check-code-version.php
```

Should show:
```
✓ FIXED VERSION DETECTED
```

### Step 5: Drop and Recreate Database

```bash
mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 6: Run Installation

Visit: https://pangolin.local/simple-install.php

**Expected Results:**
- All 72 migrations should succeed
- **0 warnings** (not 36!)
- Progress bar should go to 100%
- Should redirect to login page

### Step 7: Verify Database

```bash
php /var/www/html/nautilus/public/check-what-exists.php
```

Should show all critical tables exist:
```
=== CRITICAL TABLES ===
✓ tenants (migration 000)
✓ roles (migration 000)
✓ permissions (migration 001)
✓ role_permissions (migration 001)
✓ users (migration 001)
✓ customers (migration 002)
✓ products (migration 003)
✓ categories (migration 003)
✓ transactions (migration 004)

Total tables: 72
```

---

## Technical Details

### The Bug (commit 299b1f7)

```php
// Read the entire SQL file
$sql = file_get_contents($file);

// BUG: This strips out FK disable commands!
$sql = preg_replace('/^SET\s+FOREIGN_KEY_CHECKS\s*=\s*[01]\s*;/mi', '', $sql);

// Then tries to add them back
$sql = "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\n" . $sql . "\nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;";
```

**Problem**: The `preg_replace()` removes the FK disable that's already in `000b_fix_base_tables.sql`, so when the wrapper is added, the migration file's original FK disable is gone!

### The Fix (commit de5145c)

```php
// Read the entire SQL file
$sql = file_get_contents($file);

// ALWAYS wrap with FK disable - this is safe even if migration already has it
// The @OLD_FOREIGN_KEY_CHECKS pattern saves and restores the state
// This must be INSIDE the SQL string so it's part of the multi_query batch
$sql = "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;\n" .
       $sql .
       "\nSET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;";
```

**Solution**: No regex stripping! Just wrap every migration. The MySQL pattern saves the current state and restores it, so it's safe to nest.

---

## Troubleshooting

### Still seeing 36 warnings?

1. **Check code version on web server**:
   ```bash
   php /var/www/html/nautilus/check-code-version.php
   ```

   If it says "OLD BUGGY VERSION", then the rsync didn't work or you synced to wrong directory.

2. **Check file modification time**:
   ```bash
   ls -la /var/www/html/nautilus/app/Services/Install/InstallService.php
   ```

   Should show a recent modification time (after you ran rsync).

3. **Manually verify the code**:
   ```bash
   grep -A 5 "ALWAYS wrap with FK disable" /var/www/html/nautilus/app/Services/Install/InstallService.php
   ```

   Should return the comment and FK disable code.

4. **Check PHP error log**:
   ```bash
   sudo tail -50 /var/log/apache2/error.log
   ```

### Still failing after confirming code is deployed?

If you've verified the code is deployed and you're still getting FK errors, then there might be a deeper issue with how `multi_query()` handles the SET commands.

In that case, we'll need to try a different approach:
1. Add FK disable directly in each migration file
2. Or use a different migration runner that executes files individually

---

## Files Changed

- [app/Services/Install/InstallService.php](app/Services/Install/InstallService.php#L313-L321) - Core fix
- check-code-version.php - Version checker (new)
- CRITICAL-FIX-README.md - This file (new)

## Git Commits

- `de5145c` - Fix FK disable (removes buggy regex)
- `299b1f7` - Previous attempt (had the bug)
