# Deploy Foreign Key Fix to Test Servers

## Status
All code changes are committed and pushed to GitHub (commit 299b1f7).
The FK fix uses MySQL standard pattern to disable foreign key checks during migrations.

## What This Fix Does
The fix wraps each migration file's SQL with:
```sql
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
[migration SQL]
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
```

This allows tables with foreign keys to be created even if the referenced tables don't exist yet.

---

## Deployment Steps

### On Pop!_OS Laptop

1. **Pull latest code from GitHub**
   ```bash
   cd ~/Developer/nautilus
   git pull origin devin/1760111706-nautilus-v6-complete-skeleton
   ```

2. **Sync to web directory**
   ```bash
   sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/
   sudo chown -R www-data:www-data /var/www/html/nautilus
   sudo chmod -R 755 /var/www/html/nautilus
   sudo chmod -R 775 /var/www/html/nautilus/storage
   ```

3. **Drop and recreate database**
   ```bash
   mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

4. **Run installation**
   - Visit: https://pangolin.local/simple-install.php
   - Fill in admin details
   - Expected result: **0 warnings** on all 72 migrations

5. **Verify the fix is deployed**
   ```bash
   bash ~/Developer/nautilus/verify-fk-fix.sh
   ```
   Should output: `✓ FK disable code FOUND in InstallService.php`

---

### On RedHat Production Server

1. **Pull latest code from GitHub**
   ```bash
   cd ~/Developer/nautilus  # or wherever you clone the repo on RedHat
   git pull origin devin/1760111706-nautilus-v6-complete-skeleton
   ```

2. **Sync to web directory**
   ```bash
   # Adjust paths based on your RedHat Apache config
   sudo rsync -av --delete ~/Developer/nautilus/ /var/www/html/nautilus/

   # RedHat uses 'apache' user instead of 'www-data'
   sudo chown -R apache:apache /var/www/html/nautilus
   sudo chmod -R 755 /var/www/html/nautilus
   sudo chmod -R 775 /var/www/html/nautilus/storage
   ```

3. **Drop and recreate database**
   ```bash
   mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

4. **Run installation**
   - Visit: https://your-production-server.com/simple-install.php
   - Fill in admin details
   - Expected result: **0 warnings** on all 72 migrations

---

## Verification

After deployment, check the database:

```bash
php ~/Developer/nautilus/public/check-what-exists.php
```

You should see:
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

All critical tables should exist with NO missing tables.

---

## Expected vs Previous Results

### BEFORE FIX (36 warnings):
```
⚠ Warning: SQLSTATE[HY000]: General error: 1005 Can't create table `nautilus`.`role_permissions` (errno: 150)
⚠ Warning: SQLSTATE[HY000]: General error: 1005 Can't create table `nautilus`.`users` (errno: 150)
... (34 more warnings)
```

### AFTER FIX (0 warnings):
```
✓ Migration 000_multi_tenant_base.sql
✓ Migration 001_users_auth_audit.sql
✓ Migration 002_customers.sql
... (all 72 migrations succeed)
```

---

## Troubleshooting

### If you still see FK errors after deployment:

1. **Verify the fix is actually deployed**
   ```bash
   grep -c "MySQL standard pattern" /var/www/html/nautilus/app/Services/Install/InstallService.php
   ```
   Should return: `1` (meaning the comment is found)

2. **Check file ownership**
   ```bash
   ls -la /var/www/html/nautilus/app/Services/Install/InstallService.php
   ```
   - Pop!_OS should show: `www-data:www-data`
   - RedHat should show: `apache:apache`

3. **Verify you're running the right installation file**
   Make sure you're visiting `/simple-install.php`, not `/install.php`

4. **Check PHP error log**
   ```bash
   # Pop!_OS
   sudo tail -f /var/log/apache2/error.log

   # RedHat
   sudo tail -f /var/log/httpd/error_log
   ```

---

## What Changed

The key file that was updated:
- [app/Services/Install/InstallService.php](app/Services/Install/InstallService.php#L310-L326)

The fix is in the `runMigrations()` method, where it wraps each migration's SQL with FK disable commands.
