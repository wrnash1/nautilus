-- Migration: 104_consolidate_fix_migration_issues.sql
-- Status: Consolidated into 115_ensure_feature_tables.sql and 116_seed_default_data.sql
-- This migration was causing installation failures due to redundant schema checks.
-- It has been replaced by safer, targeted migrations.

SELECT 1;