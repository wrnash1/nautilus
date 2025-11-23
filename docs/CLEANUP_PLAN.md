# Nautilus Cleanup Plan

**Date:** 2025-01-22
**Purpose:** Clean up unused files, organize documentation, prepare for production

---

## Files to MOVE to /docs

### Root Level Documentation (move to docs/)
- [x] `INSTALLER_FIXES.md` → `/docs/INSTALLER_FIXES.md`
- [ ] `todo.txt` → `/docs/TODO.md` (convert to markdown)

---

## Files to DELETE

### 1. Backup Folders (1.8MB total - can be deleted)
- [ ] `/backup/` - 28KB of old cleanup files from Nov 19
- [ ] `/backups/` - 1.8MB of old view backups from Nov 9-13

**Rationale:** These are old backups from development. Production system uses proper backups.

### 2. Old Installer (public/)
- [ ] `/public/install.php` - 60KB old installer (replaced by /public/install/)

**Rationale:** New modern installer is at `/public/install/index.php`

### 3. Duplicate/Old Migration Scripts (database/)
- [ ] `/database/run-migrations.php` - Replaced by installer
- [ ] `/database/install-database.sh` - Replaced by installer

**Rationale:** New installer handles all database installation via `/public/install/install-db.php`

### 4. Redundant Scripts (scripts/)

#### URL Fixing Scripts (no longer needed):
- [ ] `/scripts/fix-all-urls.sh`
- [ ] `/scripts/fix-urls.sh`
- [ ] `/scripts/fix-urls.py`

#### PHP 8.4 Fix Scripts (already applied):
- [ ] `/scripts/fix-php84-nullable.sh`

#### Old Setup Scripts (replaced by installer):
- [ ] `/scripts/setup.sh` - 15KB
- [ ] `/scripts/setup-database.sh` - 8KB
- [ ] `/scripts/migrate.sh`
- [ ] `/scripts/run-migrations.php`

#### Old Deployment Scripts (will create new ones):
- [ ] `/scripts/deploy-to-production.sh`
- [ ] `/scripts/update-production.sh`
- [ ] `/scripts/sync-to-server.sh`

#### Diagnostic (development only):
- [ ] `/scripts/diagnostic-test.php` - 16KB

**Keep These Scripts:**
- ✅ `/scripts/backup.sh` - Database backups
- ✅ `/scripts/fix-permissions.sh` - Permission fixes
- ✅ `/scripts/production-cleanup.sh` - Production prep
- ✅ `/scripts/organize-docs.sh` - Documentation organization
- ✅ `/scripts/validate-migrations.php` - Validation tool
- ✅ `/scripts/seed-demo-data.php` - Demo data for testing

### 5. Old Migration-Related Scripts (docs/)
- [ ] `/docs/deploy-to-test.sh`
- [ ] `/docs/test-migrations.sh`

**Rationale:** Migrations handled by installer, test scripts outdated

---

## Files to KEEP (Required for Production)

### Root Level
- ✅ `composer.json` - Dependency management
- ✅ `composer.lock` - Locked dependencies
- ✅ `.env` - Environment configuration
- ✅ `.env.example` - Template for new installations
- ✅ `.gitignore` - Git ignore rules
- ✅ `LICENSE` - MIT License
- ✅ `README.md` - Main documentation

### Directories
- ✅ `app/` - Application code
- ✅ `public/` - Web root
- ✅ `routes/` - Route definitions
- ✅ `database/migrations/` - Database schema (keep 000_CORE_SCHEMA.sql)
- ✅ `database/seeders/` - Database seeders
- ✅ `storage/` - App storage
- ✅ `vendor/` - Composer dependencies
- ✅ `docs/` - Documentation
- ✅ `bin/` - CLI tools
- ✅ `tests/` - Test suite

---

## Consolidation Actions

### 1. Move Documentation
```bash
# Move INSTALLER_FIXES.md
mv INSTALLER_FIXES.md docs/

# Convert and move todo.txt
# (will do this programmatically)
```

### 2. Delete Backup Folders
```bash
# Remove old backups (1.8MB freed)
rm -rf backup/
rm -rf backups/
```

### 3. Clean Up Public Folder
```bash
# Remove old installer
rm public/install.php
```

### 4. Clean Database Folder
```bash
# Remove old migration runners
rm database/run-migrations.php
rm database/install-database.sh
```

### 5. Clean Scripts Folder
```bash
# Remove URL fix scripts
rm scripts/fix-all-urls.sh
rm scripts/fix-urls.sh
rm scripts/fix-urls.py

# Remove PHP fix script
rm scripts/fix-php84-nullable.sh

# Remove old setup scripts
rm scripts/setup.sh
rm scripts/setup-database.sh
rm scripts/migrate.sh
rm scripts/run-migrations.php

# Remove old deployment scripts
rm scripts/deploy-to-production.sh
rm scripts/update-production.sh
rm scripts/sync-to-server.sh

# Remove diagnostic
rm scripts/diagnostic-test.php
```

### 6. Clean Docs Folder
```bash
# Remove old test scripts
rm docs/deploy-to-test.sh
rm docs/test-migrations.sh
```

---

## Final Directory Structure (After Cleanup)

```
nautilus/
├── app/                          # Application code
├── bin/                          # CLI tools
│   ├── create-admin-cli.php
│   ├── seed-roles.php
│   └── seed-roles-simple.php
├── database/
│   ├── migrations/
│   │   └── 000_CORE_SCHEMA.sql  # Main schema (keep this!)
│   ├── seeders/                  # Database seeders
│   └── seeds/                    # Seed data
├── docs/                         # All documentation
│   ├── INSTALLER_FIXES.md        # Moved from root
│   ├── TODO.md                   # Converted from todo.txt
│   ├── SESSION_SUMMARY_INSTALLER_AND_STOREFRONT.md
│   ├── ENTERPRISE_READINESS.md
│   ├── CLEANUP_PLAN.md          # This file
│   └── [other docs]
├── public/                       # Web root
│   ├── install/                  # New modern installer
│   │   ├── index.php
│   │   ├── check.php
│   │   ├── save-config.php
│   │   └── install-db.php
│   ├── index.php
│   ├── .htaccess
│   └── offline.html
├── routes/                       # Route definitions
├── scripts/                      # Production scripts only
│   ├── backup.sh                 # KEEP - Backups
│   ├── fix-permissions.sh        # KEEP - Permission fixes
│   ├── production-cleanup.sh     # KEEP - Production prep
│   ├── organize-docs.sh          # KEEP - Doc organization
│   ├── validate-migrations.php   # KEEP - Validation
│   └── seed-demo-data.php        # KEEP - Demo data
├── storage/                      # Application storage
├── tests/                        # Test suite
├── vendor/                       # Composer dependencies
├── .env                          # Environment config
├── .env.example                  # Env template
├── .gitignore                    # Git ignore
├── composer.json                 # Dependencies
├── composer.lock                 # Locked versions
├── LICENSE                       # MIT License
└── README.md                     # Main docs
```

---

## Space Savings

- **Backup folders:** ~1.8MB
- **Old installer:** ~60KB
- **Duplicate scripts:** ~80KB
- **Old migration runners:** ~15KB

**Total:** ~2MB freed

---

## Production Readiness Checklist

After cleanup:
- [ ] All documentation in `/docs`
- [ ] No duplicate files
- [ ] No backup folders
- [ ] Only essential scripts in `/scripts`
- [ ] Clear directory structure
- [ ] README updated with current info
- [ ] .gitignore updated if needed

---

**Next Step:** Execute cleanup plan
