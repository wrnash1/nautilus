# Migration Issue - Step 3 Tenants Table Missing

**Date:** November 11, 2025
**Status:** 🔍 DIAGNOSING
**Issue:** Step 3 of installer fails with "Table 'nautilus.tenants' doesn't exist"

---

## Problem Summary

During installation testing on Fedora laptop:
- ✅ Step 1 (System Requirements) - Completed successfully
- ⚠️  Step 2 (Database Setup) - Appeared to complete, but migrations incomplete
- ❌ Step 3 (Admin Account) - Fails with missing tenants table

**Error Message:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'nautilus.tenants' doesn't exist
```

---

## Investigation Results

### Expected Behavior
- **Total Migration Files:** 70 SQL files
- **Expected Tables:** 271+ tables
- **Critical Base Tables:** tenants, roles, users, customers, products, courses

### Actual Behavior
- **Migrations Executed:** Only 34 out of 70
- **Tables Created:** Unknown (need to verify on Fedora laptop)
- **Missing Migrations:** Critical base migrations 001, 002, 003, 004, etc.

### Migrations Table Shows
The following migrations were recorded as executed:
1. 000_multi_tenant_base.sql
2. 002c_add_customer_authentication.sql
3. 005_create_certification_tables.sql
4. 009_create_ecommerce_tables.sql
... (34 total)

### Missing Critical Migrations
- ❌ 001_create_users_and_auth_tables.sql - Creates users, roles, permissions tables
- ❌ 002_create_customer_tables.sql - Creates customers, addresses, contacts tables
- ❌ 003_create_product_inventory_tables.sql - Creates products, categories, inventory tables
- ❌ 004_create_pos_transaction_tables.sql - Creates transactions, line items tables
- ❌ 006_create_rental_tables.sql - Creates rental equipment tables
- ❌ 007_create_course_trip_tables.sql - Creates courses, trips tables
- ❌ 008_create_work_order_tables.sql - Creates work orders tables

---

## Why This Happened

### Hypothesis 1: Migrations Failed Silently
The installer may have encountered errors during Step 2 but continued anyway, marking some migrations as executed while skipping others.

### Hypothesis 2: Database Was Not Empty
The database may have had leftover data from a previous installation attempt, causing the installer to skip migrations that were already marked as executed.

### Hypothesis 3: Installer Logic Issue
The installer may have a bug in the migration execution loop that causes it to skip certain files or stop early.

---

## Diagnostic Steps

### On Fedora Laptop, Run:

```bash
# 1. Check database status
chmod +x /tmp/check-database-status.sh
/tmp/check-database-status.sh
```

This will show:
- How many tables exist
- Which core tables are missing
- How many migrations were recorded
- Whether .env and .installed files exist

### Expected Output Issues:
- `tenants` table: May or may not exist (000_multi_tenant_base.sql was supposedly executed)
- `users` table: **Should be MISSING** (001 was not executed)
- `customers` table: **Should be MISSING** (002 was not executed)
- `products` table: **Should be MISSING** (003 was not executed)
- `pos_transactions` table: **Should be MISSING** (004 was not executed)

---

## Immediate Fix - Run Missing Migrations

### Option 1: Manual Migration Script (RECOMMENDED)

Run the missing migrations manually:

```bash
# Copy script to Fedora laptop
chmod +x /tmp/run-missing-migrations.sh
/tmp/run-missing-migrations.sh
```

This script will:
1. Check which migrations are already executed
2. Run only the missing migrations
3. Mark them as executed in the migrations table
4. Verify core tables exist
5. Report total table count

### Option 2: Complete Database Reinstall

If the database is in a bad state, do a complete reinstall:

```bash
# 1. Drop and recreate database
mysql -uroot -pFrogman09! << SQL
DROP DATABASE IF EXISTS nautilus;
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL

# 2. Visit installer again
# http://localhost/nautilus/install.php

# 3. Complete all steps carefully
```

---

## Root Cause Analysis Needed

### Questions to Answer:

1. **Did Step 2 show any errors or warnings?**
   - User reported seeing "36 warnings" during migration step
   - Were these warnings critical errors that stopped execution?

2. **How many tables actually exist in the database?**
   - Need to run: `mysql -uroot -pFrogman09! nautilus -e "SHOW TABLES" | wc -l`
   - Expected: 271+ tables
   - If 0 tables: Migrations didn't run at all
   - If ~100 tables: Migrations ran partially

3. **Does the tenants table actually exist?**
   - If YES: Then the error is misleading, might be a permissions issue
   - If NO: Then 000_multi_tenant_base.sql didn't actually create it despite being marked as executed

4. **What's in the migrations table?**
   - Need exact list of what's recorded vs what files exist
   - This will show the gap

### Installer Code to Review:

Location: `public/install.php` lines 439-514

**Key Logic:**
```php
// Get all migration files
$migrationFiles = glob(ROOT_DIR . '/database/migrations/*.sql');
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    $filename = basename($file);

    // Check if already executed
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
    $stmt->execute([$filename]);
    $alreadyExecuted = $stmt->fetchColumn() > 0;

    if ($alreadyExecuted) {
        echo "⊘ Skipped: $filename (already executed)";
        continue;
    }

    // Execute migration...
}
```

**Potential Issues:**
1. If glob() fails to find files, no migrations run
2. If database connection drops mid-migration, some files get marked but tables not created
3. If a migration has SQL syntax errors, it may get marked as executed but fail to create tables

---

## Fix for Production (Installer Enhancement)

### Enhancement 1: Better Error Reporting
The installer should:
- Show each migration as it runs (it already does this)
- **Stop immediately if a critical migration fails**
- **Verify core tables exist before allowing Step 3**

### Enhancement 2: Pre-Step 3 Validation

Add this check before Step 3:

```php
// Before showing admin account form
$requiredTables = ['tenants', 'roles', 'users'];
foreach ($requiredTables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() === 0) {
        die("ERROR: Required table '$table' does not exist. Please re-run Step 2.");
    }
}
```

### Enhancement 3: Migration Verification

After running migrations, verify table count:

```php
$stmt = $pdo->query("SHOW TABLES");
$tableCount = $stmt->rowCount();

if ($tableCount < 250) {
    echo "ERROR: Only $tableCount tables created. Expected at least 250.";
    echo "Please check the migration errors above.";
    exit;
}
```

---

## Next Steps

1. **Run diagnostic script** on Fedora laptop to understand current state
2. **Run missing migrations script** to fix the immediate issue
3. **Complete Step 3** of installation to create admin account
4. **Test login** to verify everything works
5. **Enhance installer** with better validation before production release

---

## For GitHub Release

Before releasing to GitHub, the installer MUST:
- ✅ Verify all migrations execute successfully
- ✅ Verify core tables exist before Step 3
- ✅ Show clear error messages if migrations fail
- ✅ Not allow proceeding to Step 3 if database setup incomplete

This ensures non-technical dive shop owners cannot encounter this issue.

---

**Status:** 🔧 Ready to fix with run-missing-migrations.sh script
**Impact:** HIGH - Blocks installation completion
**Priority:** CRITICAL - Must fix before GitHub release

---

