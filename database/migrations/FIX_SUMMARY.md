# Nautilus Migration Fixes - Summary

## Overview
This document summarizes all the migration file fixes applied to resolve SQL syntax errors, foreign key issues, and idempotency problems.

## Files Fixed

### 1. 002_create_customer_tables.sql
**Status**: No changes needed - already idempotent with CREATE TABLE IF NOT EXISTS

### 2. 014_enhance_certifications_and_travel.sql
**Issues Fixed**:
- Changed `ADD COLUMN IF NOT EXISTS` to conditional column additions using prepared statements
- This avoids MariaDB syntax errors with `ADD COLUMN IF NOT EXISTS ... AFTER column_name`
**Changes**:
- Converted ALTER TABLE statements to use INFORMATION_SCHEMA checks
- Made photo_path addition conditional

### 3. 016_add_branding_and_logo_support.sql
**Issues Fixed**:
- Replaced `ON DUPLICATE KEY UPDATE` with `INSERT IGNORE` for idempotency
**Changes**:
- settings table inserts now use INSERT IGNORE
- system_metadata inserts now use INSERT IGNORE

### 4. 025_create_storefront_theme_system.sql
**Issues Fixed**:
- INSERT statements not idempotent
**Changes**:
- All INSERT statements now use INSERT IGNORE
- Added explicit ID to theme_config INSERT for predictability

### 5. 030_create_communication_system.sql
**Issues Fixed**:
- Mixed CREATE INDEX and table syntax issues
- TINYINT(1) usage (should be BOOLEAN)
- Improper TIMESTAMP defaults
**Changes**:
- Moved indexes inside CREATE TABLE statements
- Changed TINYINT(1) to BOOLEAN
- Fixed TIMESTAMP NULL defaults
- Added proper ENGINE and CHARSET declarations
- Added foreign key constraints

### 6. 032_add_certification_agency_branding.sql
**Issues Fixed**:
- `ADD COLUMN IF NOT EXISTS` syntax error
**Changes**:
- Converted to conditional column addition using prepared statements

### 7. 038_create_compressor_tracking_system.sql
**Issues Fixed**:
- ALTER TABLE ... COMMENT syntax not supported in migration context
**Changes**:
- Commented out ALTER TABLE COMMENT statements (comments already in CREATE TABLE)

### 8. 040_customer_tags_and_linking.sql
**Issues Fixed**:
- Multiple `ADD COLUMN IF NOT EXISTS` syntax errors
- `ADD INDEX IF NOT EXISTS` syntax errors
**Changes**:
- Converted all column additions to conditional prepared statements
- Converted all index additions to conditional prepared statements

### 9-27. Remaining Files (055-097)

The following files need similar fixes but are deferred for batch processing:

- **055_feedback_ticket_system.sql**: Already looks idempotent (uses CREATE TABLE IF NOT EXISTS)
- **056_notification_system.sql**: Already looks idempotent, just needs ALTER TABLE fixes for existing tables
- **058_multi_tenant_architecture.sql**: Already mostly idempotent
- **059_stock_management_tables.sql**: Already mostly idempotent
- **062_customer_portal.sql**: Foreign key issues (tenants table reference)
- **064_notification_preferences.sql**: Foreign key issues (tenants table reference)
- **065_search_system.sql**: Full-text index issues
- **066_audit_trail_system.sql**: Foreign key issues (tenants table reference)
- **067_ecommerce_and_ai_features.sql**: Foreign key issues (tenants table reference)
- **068_enterprise_saas_features.sql**: Syntax errors in complex schema
- **070_company_settings_table.sql**: Foreign key issues
- **071_newsletter_subscriptions_table.sql**: Foreign key issues
- **072_help_articles_table.sql**: Foreign key issues
- **074_email_queue_system.sql**: Foreign key issues
- **080_advanced_scheduling_system.sql**: Syntax errors
- **083-091**: INSERT statements with invalid foreign key data
- **092_advanced_inventory_control.sql**: Foreign key issues
- **096_online_booking_and_mobile_apis.sql**: Syntax errors
- **097_business_intelligence_reporting.sql**: Syntax errors

## Common Patterns Fixed

### 1. ADD COLUMN IF NOT EXISTS with AFTER clause
**Problem**: MariaDB doesn't support `ADD COLUMN IF NOT EXISTS ... AFTER column_name`
**Solution**: Use prepared statements with INFORMATION_SCHEMA checks

```sql
SET @dbname = DATABASE();
SET @tablename = "table_name";
SET @columnname = "column_name";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE (table_name = @tablename) AND (table_schema = @dbname) AND (column_name = @columnname)) > 0,
  "SELECT 1",
  "ALTER TABLE table_name ADD COLUMN column_name TYPE"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
```

### 2. CREATE INDEX IF NOT EXISTS
**Problem**: MariaDB syntax differs from MySQL for conditional index creation
**Solution**: Either include indexes in CREATE TABLE or use prepared statements

### 3. INSERT with ON DUPLICATE KEY UPDATE
**Problem**: Not truly idempotent if keys change
**Solution**: Use INSERT IGNORE for simple cases

### 4. TINYINT(1) vs BOOLEAN
**Problem**: Inconsistent boolean representation
**Solution**: Use BOOLEAN for clarity (maps to TINYINT(1) anyway)

### 5. TIMESTAMP defaults
**Problem**: TIMESTAMP NOT NULL without default causes errors
**Solution**: Use TIMESTAMP NULL or provide DEFAULT

### 6. Foreign Key Dependencies
**Problem**: Tables referenced before they're created
**Solution**:
- Ensure tenants table exists (from 058_multi_tenant_architecture.sql)
- Ensure referenced tables exist before creating foreign keys
- Use ON DELETE CASCADE/SET NULL appropriately

## Testing Recommendations

After applying all fixes:

1. **Fresh Install Test**: Drop database and run all migrations from scratch
2. **Re-run Test**: Run migrations again to verify idempotency
3. **Partial Install Test**: Run first 50 migrations, then run all again
4. **Foreign Key Validation**: Verify all foreign keys resolve correctly

## Next Steps

1. Apply remaining fixes to files 055-097
2. Test full migration sequence
3. Fix any remaining INSERT statement data issues
4. Validate foreign key chains
5. Document any tables that must be created in specific order
