# Nautilus Project Cleanup - Old Files

This document lists files that can be safely removed as they are outdated, have been replaced, or are no longer needed.

## Old Shell Scripts (Root Directory)

These scripts were used during development/debugging and can be archived or removed:

### ❌ **Can Be Removed:**
- `apply-all-fixes.sh` - One-time fix script, no longer needed
- `cleanup-debug-files.sh` - Temporary cleanup script
- `fix-all-migrations.sh` - One-time migration fix
- `fix-database-config.sh` - One-time fix
- `fix-mysql-syntax.sh` - One-time fix
- `fix-php84-compatibility.sh` - One-time fix, already applied
- `fix-ssl-config.sh` - One-time SSL setup
- `fix-ssl-simple.sh` - One-time SSL setup
- `fix-views.sh` - One-time view fix
- `setup-apache-ssl.sh` - One-time setup
- `setup-database.sh` - Replaced by install wizard
- `sync-to-webserver.sh` - Replaced by `/tmp/sync-all-nautilus-files.sh`

### ✅ **Keep These:**
- `fresh-install.sh` - Useful for development resets
- `install.sh` - Main installer (if still used)
- `run-migrations.sh` - Useful for running migrations manually
- `create-package.sh` - For creating deployment packages
- `scripts/backup.sh` - Important for backups
- `scripts/deploy-to-production.sh` - Deployment script
- `scripts/setup-database.sh` - Database setup utility

## Recommended File Structure

```
nautilus/
├── scripts/              # Active utility scripts
│   ├── backup.sh
│   ├── deploy.sh        # Rename from deploy-to-production.sh
│   ├── fresh-install.sh # Move from root
│   ├── migrate.sh       # Rename from run-migrations.sh
│   └── setup-db.sh      # Rename from setup-database.sh
│
├── docs/                # Documentation
│   ├── COURSE_ENROLLMENT_WORKFLOW.md
│   ├── COURSE_ENROLLMENT_IMPLEMENTATION.md
│   └── DEPLOY_COURSE_ENROLLMENT.md
│
└── archive/             # Old scripts (if needed for reference)
    └── old-fixes/       # Move all fix-*.sh here
```

## Cleanup Commands

Run these commands to clean up:

```bash
cd /home/wrnash1/development/nautilus

# Create archive directory
mkdir -p archive/old-fixes

# Move old fix scripts
mv apply-all-fixes.sh archive/old-fixes/
mv cleanup-debug-files.sh archive/old-fixes/
mv fix-*.sh archive/old-fixes/
mv setup-apache-ssl.sh archive/old-fixes/
mv sync-to-webserver.sh archive/old-fixes/

# Create docs directory if it doesn't exist
mkdir -p docs

# Move documentation
mv COURSE_ENROLLMENT_*.md docs/ 2>/dev/null || true
mv DEPLOY_COURSE_ENROLLMENT.md docs/ 2>/dev/null || true

# Reorganize active scripts
mv fresh-install.sh scripts/
mv run-migrations.sh scripts/migrate.sh 2>/dev/null || true

echo "Cleanup complete!"
```

## Deployment Scripts

### Current (Temporary in /tmp)
- `/tmp/sync-all-nautilus-files.sh` - Complete deployment script

### Recommended
Move this to the project:
```bash
cp /tmp/sync-all-nautilus-files.sh /home/wrnash1/development/nautilus/scripts/deploy-complete.sh
```

## GitHub Repository Cleanup

Before pushing to GitHub, consider adding to `.gitignore`:

```
# Development/debugging scripts
archive/
*.bak
*.old
*.tmp
*~

# Temporary sync scripts
/tmp/sync-*.sh

# Local environment
.env
.env.local
storage/logs/*
storage/sessions/*
storage/cache/*
```

## Files Already Handled by Installation

These are no longer needed because the installer handles them:

- ❌ `setup-database.sh` (root) - Installer creates database
- ❌ Multiple `fix-*.sh` - These were one-time fixes already applied
- ❌ `sync-to-webserver.sh` - Replaced by deploy-complete.sh

## Summary

**Remove:** 12 old fix/setup scripts
**Archive:** 3 scripts for reference
**Reorganize:** 5 active scripts into scripts/ directory
**Document:** Move 3 docs to docs/ directory

**Result:** Cleaner, more professional repository structure
