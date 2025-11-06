#!/bin/bash
#===============================================================================
# Nautilus Project Cleanup Script
# Organizes files and removes outdated scripts
#===============================================================================

echo "================================================================"
echo "  Nautilus Project Cleanup"
echo "================================================================"
echo ""
echo "This will:"
echo "  • Move old fix scripts to archive/"
echo "  • Organize documentation into docs/"
echo "  • Reorganize active scripts in scripts/"
echo "  • Create proper .gitignore"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit 1
fi

cd /home/wrnash1/development/nautilus

echo ""
echo "Step 1: Creating directory structure..."
mkdir -p archive/old-fixes
mkdir -p docs
mkdir -p scripts

echo "Step 2: Archiving old fix scripts..."
mv apply-all-fixes.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ apply-all-fixes.sh" || true
mv cleanup-debug-files.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ cleanup-debug-files.sh" || true
mv fix-all-migrations.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-all-migrations.sh" || true
mv fix-database-config.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-database-config.sh" || true
mv fix-mysql-syntax.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-mysql-syntax.sh" || true
mv fix-php84-compatibility.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-php84-compatibility.sh" || true
mv fix-ssl-config.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-ssl-config.sh" || true
mv fix-ssl-simple.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-ssl-simple.sh" || true
mv fix-views.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ fix-views.sh" || true
mv setup-apache-ssl.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ setup-apache-ssl.sh" || true
mv setup-database.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ setup-database.sh" || true
mv sync-to-webserver.sh archive/old-fixes/ 2>/dev/null && echo "  ✓ sync-to-webserver.sh" || true

echo ""
echo "Step 3: Organizing documentation..."
mv COURSE_ENROLLMENT_WORKFLOW.md docs/ 2>/dev/null && echo "  ✓ COURSE_ENROLLMENT_WORKFLOW.md" || true
mv COURSE_ENROLLMENT_IMPLEMENTATION.md docs/ 2>/dev/null && echo "  ✓ COURSE_ENROLLMENT_IMPLEMENTATION.md" || true
mv DEPLOY_COURSE_ENROLLMENT.md docs/ 2>/dev/null && echo "  ✓ DEPLOY_COURSE_ENROLLMENT.md" || true
mv CLEANUP_OLD_FILES.md docs/ 2>/dev/null && echo "  ✓ CLEANUP_OLD_FILES.md" || true

echo ""
echo "Step 4: Reorganizing active scripts..."
# Only move if they exist and aren't already in scripts/
[ -f "fresh-install.sh" ] && mv fresh-install.sh scripts/ && echo "  ✓ fresh-install.sh" || true
[ -f "run-migrations.sh" ] && mv run-migrations.sh scripts/migrate.sh && echo "  ✓ run-migrations.sh → migrate.sh" || true
[ -f "create-package.sh" ] && mv create-package.sh scripts/ && echo "  ✓ create-package.sh" || true

# Copy deployment script from /tmp if it exists
if [ -f "/tmp/sync-all-nautilus-files.sh" ]; then
    cp /tmp/sync-all-nautilus-files.sh scripts/deploy-complete.sh
    chmod +x scripts/deploy-complete.sh
    echo "  ✓ deploy-complete.sh (from /tmp)"
fi

echo ""
echo "Step 5: Creating/updating .gitignore..."
cat > .gitignore << 'EOF'
# Development/debugging files
archive/
*.bak
*.old
*.tmp
*~

# Environment files
.env
.env.local
.env.production

# Storage directories
storage/logs/*
!storage/logs/.gitkeep
storage/sessions/*
!storage/sessions/.gitkeep
storage/cache/*
!storage/cache/.gitkeep
storage/uploads/*
!storage/uploads/.gitkeep

# Temporary files
/tmp/
*.log

# IDE files
.vscode/
.idea/
*.sublime-*

# OS files
.DS_Store
Thumbs.db

# Dependencies
/vendor/
/node_modules/

# Build artifacts
/public/hot
/public/storage

# Composer
composer.phar
composer.lock

# NPM
package-lock.json
npm-debug.log
yarn-error.log
EOF

echo "  ✓ .gitignore created/updated"

echo ""
echo "Step 6: Creating README for archived files..."
cat > archive/old-fixes/README.md << 'EOF'
# Archived Development Scripts

These scripts were used during development and debugging phases.
They have been archived as they are no longer needed for regular operations.

## Contents

- `apply-all-fixes.sh` - Applied multiple fixes during development
- `cleanup-debug-files.sh` - Cleaned up temporary debug files
- `fix-*.sh` - Various one-time fixes for database, PHP 8.4, SSL, etc.
- `setup-*.sh` - One-time setup scripts
- `sync-to-webserver.sh` - Replaced by scripts/deploy-complete.sh

## Note

These are kept for historical reference only.
For current deployment, use `/scripts/deploy-complete.sh`
EOF

echo "  ✓ Archive README created"

echo ""
echo "Step 7: Creating project structure documentation..."
cat > PROJECT_STRUCTURE.md << 'EOF'
# Nautilus Project Structure

## Directory Organization

```
nautilus/
│
├── app/                    # Application code
│   ├── Controllers/        # Request handlers
│   ├── Services/           # Business logic
│   ├── Models/             # Data models
│   ├── Middleware/         # Request middleware
│   └── Views/              # HTML templates
│
├── public/                 # Web root
│   ├── index.php           # Application entry point
│   └── assets/             # CSS, JS, images
│
├── database/               # Database files
│   ├── migrations/         # Schema migrations (*.sql)
│   └── seeders/            # Data seeders (*.sql)
│
├── routes/                 # Route definitions
│   └── web.php
│
├── scripts/                # Utility scripts
│   ├── deploy-complete.sh  # Full deployment
│   ├── migrate.sh          # Run migrations
│   ├── backup.sh           # Database backup
│   └── fresh-install.sh    # Development reset
│
├── docs/                   # Documentation
│   ├── COURSE_ENROLLMENT_WORKFLOW.md
│   ├── COURSE_ENROLLMENT_IMPLEMENTATION.md
│   └── DEPLOY_COURSE_ENROLLMENT.md
│
├── storage/                # Application storage
│   ├── logs/               # Log files
│   ├── sessions/           # Session data
│   ├── cache/              # Cache files
│   └── uploads/            # User uploads
│
├── archive/                # Archived files (not in git)
│   └── old-fixes/          # Old development scripts
│
├── vendor/                 # Composer dependencies
├── .env                    # Environment config (not in git)
├── .gitignore             # Git ignore rules
└── README.md              # Project overview
```

## Key Files

- `.env` - Environment configuration (database, API keys)
- `composer.json` - PHP dependencies
- `routes/web.php` - Application routes
- `public/index.php` - Application bootstrap

## Installation

Fresh installation:
```bash
# Navigate to https://yourdomain.com/install
# Follow the installation wizard
```

Existing installation (updates):
```bash
sudo bash scripts/deploy-complete.sh
```

## Deployment

For production deployment:
```bash
sudo bash scripts/deploy-complete.sh
```

This syncs:
- Application code
- Database migrations
- Assets (JS/CSS)
- Configuration

## Development

Run migrations:
```bash
php scripts/migrate.sh
```

Fresh install (development):
```bash
bash scripts/fresh-install.sh
```

Backup database:
```bash
bash scripts/backup.sh
```

## Documentation

All documentation is in the `docs/` directory:
- Course enrollment workflow
- Deployment guides
- API documentation
EOF

echo "  ✓ PROJECT_STRUCTURE.md created"

echo ""
echo "================================================================"
echo "  ✅  CLEANUP COMPLETE!"
echo "================================================================"
echo ""
echo "Summary:"
echo "  ✓ 12 old scripts archived to archive/old-fixes/"
echo "  ✓ Documentation organized in docs/"
echo "  ✓ Active scripts in scripts/"
echo "  ✓ .gitignore created/updated"
echo "  ✓ PROJECT_STRUCTURE.md created"
echo ""
echo "Next steps:"
echo "  1. Review changes: git status"
echo "  2. Commit changes: git add . && git commit -m 'Organize project structure'"
echo "  3. Push to GitHub: git push origin main"
echo ""
echo "Deployment:"
echo "  Use: sudo bash scripts/deploy-complete.sh"
echo ""
