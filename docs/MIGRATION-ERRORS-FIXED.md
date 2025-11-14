# Migration Errors Found and Fixed

## Executive Summary

Comprehensive analysis of all 70 SQL migration files revealed **critical data type mismatches** causing foreign key constraint failures (errno 150). The primary culprit was migration file `060_user_permissions_roles.sql` which used `INT` instead of `INT UNSIGNED` for all id columns.

---

## Critical Error Fixed (Commit afb72fc)

### File: 060_user_permissions_roles.sql

**Problem:** All integer columns used `INT` instead of `INT UNSIGNED`, causing type mismatches with earlier migrations (000, 001, 002, etc.) that consistently use `INT UNSIGNED` for id columns.

**Impact:** This caused errno 150 foreign key constraint errors for:
- `role_permissions` table
- `user_roles` table
- `user_permissions` table
- `permission_audit_log` table

**Columns Fixed (19 total):**
1. `roles.id`: INT → INT UNSIGNED
2. `roles.tenant_id`: INT → INT UNSIGNED
3. `permissions.id`: INT → INT UNSIGNED
4. `role_permissions.id`: INT → INT UNSIGNED
5. `role_permissions.role_id`: INT → INT UNSIGNED
6. `role_permissions.permission_id`: INT → INT UNSIGNED
7. `user_roles.id`: INT → INT UNSIGNED
8. `user_roles.user_id`: INT → INT UNSIGNED
9. `user_roles.role_id`: INT → INT UNSIGNED
10. `user_roles.assigned_by`: INT → INT UNSIGNED
11. `user_permissions.id`: INT → INT UNSIGNED
12. `user_permissions.user_id`: INT → INT UNSIGNED
13. `user_permissions.permission_id`: INT → INT UNSIGNED
14. `user_permissions.granted_by`: INT → INT UNSIGNED
15. `permission_audit_log.id`: BIGINT → BIGINT UNSIGNED
16. `permission_audit_log.tenant_id`: INT → INT UNSIGNED
17. `permission_audit_log.user_id`: INT → INT UNSIGNED
18. `permission_audit_log.role_id`: INT → INT UNSIGNED
19. `permission_audit_log.granted_to_user_id`: INT → INT UNSIGNED

**Status:** ✅ FIXED in commit afb72fc

---

## Other Issues Found (Not Yet Fixed)

### 1. Duplicate Table Definitions

**Issue:** Same tables defined in multiple migrations with conflicting schemas.

#### Affected Tables:
- **roles** - Defined in: 000_multi_tenant_base.sql, 060_user_permissions_roles.sql (different columns)
- **permissions** - Defined in: 001, 000b, 060 (different columns in 060)
- **role_permissions** - Defined in: 001, 000b, 060 (000b has DROP TABLE)
- **user_roles** - Defined in: 001, 060 (different columns)
- **users** - Defined in: 001, 000b (identical)
- **audit_logs** - Defined in: 001, 000b (identical)
- **password_resets** - Defined in: 001, 000b (identical)
- **sessions** - Defined in: 001, 000b (identical)

**Impact:** While `IF NOT EXISTS` prevents immediate failures, inconsistent schemas can cause issues if migrations run in different orders.

**Recommended Fix:**
- Remove duplicates from `000b_fix_base_tables.sql` (it should only fix, not recreate)
- Convert 060 to use `ALTER TABLE` instead of `CREATE TABLE IF NOT EXISTS` for tables that already exist
- Or rename migration 060 columns to match earlier definitions

**Status:** ⚠️ NOT YET FIXED (low priority - doesn't cause immediate failures with current FK fix)

---

### 2. Migration File 000b_fix_base_tables.sql

**Issue:** This file was created to "fix" FK errors, but it duplicates tables from 001.

**Problem:**
- Creates same tables as 001 (permissions, role_permissions, users, password_resets, sessions, audit_logs)
- Uses `DROP TABLE IF EXISTS` for role_permissions, which could delete data if run after initial setup

**Recommended Fix:**
- Consider removing this file entirely now that FK checks are properly disabled at connection level
- Or convert it to a true "repair" script that only runs on demand, not as part of normal migrations

**Status:** ⚠️ NOT YET FIXED (low priority)

---

### 3. Conflicting Column Names in 060

**Issue:** Migration 060 tries to create tables with different column names than original definitions:

**roles table:**
- Original (000): `name`, `display_name`
- Migration 060: `role_name`, `role_code` (no `display_name`)

**permissions table:**
- Original (001): `name`, `display_name`, `module`
- Migration 060: `permission_name`, `permission_code`, `category` (different names)

**Impact:** If 060 runs and creates these tables, the column names won't match what the application code expects.

**Recommended Fix:**
```sql
-- Instead of CREATE TABLE IF NOT EXISTS, use:
ALTER TABLE roles
ADD COLUMN IF NOT EXISTS role_code VARCHAR(50) AFTER name,
ADD COLUMN IF NOT EXISTS is_system_role BOOLEAN DEFAULT FALSE AFTER description,
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER is_system_role;
```

**Status:** ⚠️ NOT YET FIXED (medium priority - could break application code)

---

## Testing Recommendations

### 1. After Deploying Current Fixes

```bash
# Drop and recreate database
mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run installation
# Visit: https://your-server/simple-install.php

# Expected results:
# - 0 FK errors (not 36!)
# - All 72 migrations succeed
# - All critical tables exist
```

### 2. Verify Table Structures

```sql
-- Check that all id columns are INT UNSIGNED
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = 'nautilus'
    AND COLUMN_NAME = 'id'
    AND COLUMN_TYPE NOT LIKE '%unsigned%';
-- Should return 0 rows

-- Check FK constraints exist
SELECT
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'nautilus'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;
-- Should show all FK relationships
```

---

## Summary

**Critical Issues Fixed:**
- ✅ INT vs INT UNSIGNED mismatches in 060_user_permissions_roles.sql (19 columns)
- ✅ FK checks now disabled at connection level (commit bcf2c49)

**Remaining Issues (Non-blocking):**
- ⚠️ Duplicate table definitions across multiple migrations
- ⚠️ Column name conflicts in migration 060
- ⚠️ 000b_fix_base_tables.sql needs review

**Expected Outcome:**
With the current fixes (commits bcf2c49 and afb72fc), installation should complete with **0 FK errors** instead of 36.

---

## Commits

1. **bcf2c49** - Disable FK checks at connection level (fixes multi_query issue)
2. **afb72fc** - Fix INT vs INT UNSIGNED in 060_user_permissions_roles.sql (fixes data type mismatches)

Deploy both commits together for full FK error resolution.
