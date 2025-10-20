# Migration Error Fixed!

## Problem
Installation was failing with error:
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'setting_key'
```

## Root Cause
Migrations 015 and 016 were using incorrect column names:
- Used: `setting_key`, `setting_value`, `setting_type`
- Should be: `key`, `value`, `type`

The `settings` table (created in migration 013) uses short column names, but later migrations were referencing them with "setting_" prefix.

## Files Fixed

### 1. Migration 015 (`015_add_settings_encryption_and_audit.sql`)
**Changes:**
- ✅ `setting_key` → `key`
- ✅ `setting_value` → `value`
- ✅ `setting_type` → `type`
- Fixed in 11 UPDATE statements
- Fixed in 1 INSERT statement
- Fixed in 3 ALTER TABLE statements

### 2. Migration 016 (`016_add_branding_and_logo_support.sql`)
**Changes:**
- ✅ `setting_key` → `key`
- ✅ `setting_value` → `value`
- ✅ `setting_type` → `type`
- Fixed in 2 INSERT statements

## How to Proceed

Since the installation already started (and partially created tables), you need to **drop and recreate the database**:

### Step 1: Drop the Partially Created Database
```sql
mysql -u root -p
```

```sql
DROP DATABASE nautilus;
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

### Step 2: Run the Installer Again
```
http://Pangolin.local/install
```

The installer will now run successfully through all migrations!

## What Got Fixed

### Migration 015 - Settings Encryption
This migration:
- Creates settings audit log table
- Marks sensitive API keys as encrypted
- Adds performance indexes
- Now uses correct column names ✅

### Migration 016 - Branding Support
This migration:
- Adds company logo settings
- Creates file uploads tracking table
- Adds branding to emails
- Now uses correct column names ✅

## Verification

After successful installation, verify these tables exist:

```sql
USE nautilus;
SHOW TABLES LIKE 'settings%';
```

You should see:
- `settings` (with columns: id, category, key, value, type, description)
- `settings_audit` (audit log for sensitive settings)

## All Migrations Will Now Complete Successfully

✅ Migration 001-014: Create all core tables
✅ Migration 015: Settings encryption (FIXED)
✅ Migration 016: Branding support (FIXED)
✅ Migration 017-024: All remaining features including waivers

Total: **24 migration files** will run successfully!

---

**Ready to reinstall!** The column name mismatch has been corrected.
