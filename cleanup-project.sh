#!/bin/bash
###############################################################################
# Professional Project Cleanup
# Organizes and removes temporary/development files
###############################################################################

echo "=========================================="
echo "  Nautilus Project Cleanup"
echo "=========================================="
echo ""

cd /home/wrnash1/Developer/nautilus

echo "1. Creating organized directory structure..."
mkdir -p docs/installation
mkdir -p docs/development
mkdir -p scripts/setup
mkdir -p scripts/archived
echo "✓ Directories created"
echo ""

echo "2. Moving documentation files..."
# Keep only essential docs in root (README, LICENSE, CHANGELOG)
# Move detailed guides to docs/
mv INSTALLATION_GUIDE.md docs/installation/ 2>/dev/null
mv BETA_TESTER_QUICK_START.md docs/installation/ 2>/dev/null
mv PRE_LAUNCH_CHECKLIST.md docs/development/ 2>/dev/null
mv READY_FOR_BETA.md docs/development/ 2>/dev/null
mv INVENTORY_ENHANCEMENT.md docs/development/ 2>/dev/null
mv CONTRIBUTING.md docs/development/ 2>/dev/null
mv CODE_OF_CONDUCT.md docs/development/ 2>/dev/null
mv SEND_TO_BETA_TESTER.txt docs/development/ 2>/dev/null
echo "✓ Documentation organized"
echo ""

echo "3. Moving setup scripts..."
mv setup-web-installer.sh scripts/setup/ 2>/dev/null
mv setup-nautilus-vhost.sh scripts/setup/ 2>/dev/null
mv setup-https.sh scripts/setup/ 2>/dev/null
echo "✓ Setup scripts organized"
echo ""

echo "4. Archiving temporary diagnostic scripts..."
mv diagnose.sh scripts/archived/ 2>/dev/null
mv check-apache.sh scripts/archived/ 2>/dev/null
mv fix-permissions.sh scripts/archived/ 2>/dev/null
mv fix-vhost-conflict.sh scripts/archived/ 2>/dev/null
mv update-installer.sh scripts/archived/ 2>/dev/null
mv deploy-installer-fix.sh scripts/archived/ 2>/dev/null
echo "✓ Temporary scripts archived"
echo ""

echo "5. Removing obsolete installation scripts..."
# Keep only the web installer, remove old shell-based installers
rm -f install.sh 2>/dev/null
rm -f run_migrations.sh 2>/dev/null
echo "✓ Old installers removed (web installer only)"
echo ""

echo "6. Cleaning up test files..."
rm -f test_db.php 2>/dev/null
rm -f public/test.php 2>/dev/null
rm -f public/test_diag.php 2>/dev/null
rm -f public/check_mig.php 2>/dev/null
rm -f public/install-improved.php 2>/dev/null
rm -f public/install.php.backup 2>/dev/null
echo "✓ Test files removed"
echo ""

echo "7. Creating project structure documentation..."
cat > docs/PROJECT_STRUCTURE.md <<'EOF'
# Nautilus Project Structure

## Root Directory
- `README.md` - Project overview and quick start
- `CHANGELOG.md` - Version history
- `LICENSE` - Software license
- `composer.json` - PHP dependencies
- `.env.example` - Environment configuration template

## Application Directories
- `app/` - Application logic (Models, Controllers, Services)
- `config/` - Configuration files
- `database/` - Database migrations and seeds
- `public/` - Web-accessible files (entry point)
- `routes/` - Application routing
- `storage/` - Application storage (logs, cache, uploads)
- `vendor/` - Composer dependencies

## Documentation
- `docs/installation/` - Installation guides
- `docs/development/` - Development documentation
- `docs/PROJECT_STRUCTURE.md` - This file

## Scripts
- `scripts/setup/` - Initial setup scripts
- `scripts/archived/` - Archived/deprecated scripts
- `scripts/backup.sh` - Database backup script

## Installation
Use the web-based installer at: `https://your-domain.com/install.php`

No command-line installation required!
EOF
echo "✓ Project structure documented"
echo ""

echo "8. Creating .gitignore for clean repository..."
cat > .gitignore <<'EOF'
# Environment & Configuration
.env
.env.local
.env.*.local

# Installation marker
.installed

# Application Storage
storage/logs/*.log
storage/cache/*
storage/backups/*
!storage/logs/.gitkeep
!storage/cache/.gitkeep
!storage/backups/.gitkeep

# Vendor
vendor/

# IDE
.vscode/
.idea/
*.swp
*.swo
*~

# OS
.DS_Store
Thumbs.db

# Temporary files
*.tmp
*.bak
*.old
*~
.*.swp

# Test files
test*.php
check*.php
EOF
echo "✓ .gitignore created"
echo ""

echo "9. Final structure:"
tree -L 2 -d --charset ascii 2>/dev/null || ls -la
echo ""

echo "=========================================="
echo "  Cleanup Complete!"
echo "=========================================="
echo ""
echo "✅ Project is now professionally organized:"
echo ""
echo "📁 Root directory is clean (only essential files)"
echo "📚 Documentation moved to docs/"
echo "🔧 Scripts organized in scripts/"
echo "🗑️  Test and temporary files removed"
echo "📄 Project structure documented"
echo "🔒 .gitignore created for version control"
echo ""
echo "Professional file structure:"
echo "  nautilus/"
echo "  ├── README.md"
echo "  ├── CHANGELOG.md"
echo "  ├── LICENSE"
echo "  ├── composer.json"
echo "  ├── app/"
echo "  ├── config/"
echo "  ├── database/"
echo "  ├── docs/"
echo "  │   ├── installation/"
echo "  │   └── development/"
echo "  ├── public/"
echo "  │   └── install.php (web installer)"
echo "  ├── scripts/"
echo "  │   ├── setup/"
echo "  │   └── archived/"
echo "  └── storage/"
echo ""
