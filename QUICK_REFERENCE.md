# Nautilus Quick Reference Card

**Single Repository - Team Development**

---

## 📁 What Gets Tracked in Git?

```
✅ TRACKED (commit these):
   nautilus/app/              ← Source code
   nautilus/database/         ← Migrations
   nautilus/routes/           ← Routes
   nautilus/public/           ← Public assets
   nautilus/scripts/          ← Build scripts
   nautilus/docs/             ← Documentation
   nautilus/.env.example      ← Config template

❌ NOT TRACKED (git ignores these):
   nautilus-customer/         ← Generated app (build artifact)
   nautilus-staff/            ← Generated app (build artifact)
   nautilus/vendor/           ← Composer dependencies
   nautilus/.env              ← Local config
```

---

## 🚀 Daily Developer Commands

```bash
# Morning - Get latest changes
cd /home/wrnash1/development/nautilus
git pull origin main
./scripts/split-enterprise-apps.sh          # ← IMPORTANT: Rebuild apps!

# Work - Make changes to SOURCE CODE in nautilus/ directory
nano app/Controllers/SomeController.php     # Edit source
./scripts/split-enterprise-apps.sh          # Rebuild to test

# Test locally
cd ../nautilus-customer/public && php -S localhost:8000
cd ../nautilus-staff/public && php -S localhost:8001

# Evening - Commit and push SOURCE CODE ONLY
cd /home/wrnash1/development/nautilus
git add app/ database/ routes/ docs/        # Only source files!
git commit -m "Add new feature"
git push origin main
```

---

## 🔄 The Golden Rule

```
┌─────────────────────────────────────────────────────────┐
│  ALWAYS WORK IN: nautilus/                             │
│  NEVER EDIT:     nautilus-customer/ or nautilus-staff/ │
│  ALWAYS REBUILD: ./scripts/split-enterprise-apps.sh    │
└─────────────────────────────────────────────────────────┘
```

**Why?**
- `nautilus/` = Source code (tracked in git)
- `nautilus-customer/` & `nautilus-staff/` = Build artifacts (NOT tracked)
- Changes in generated apps will be LOST when you rebuild!

---

## 🎯 Common Tasks

### Clone Repository (First Time)
```bash
git clone https://github.com/yourusername/nautilus.git
cd nautilus
composer install
./scripts/split-enterprise-apps.sh
```

### Pull Latest Changes
```bash
cd /home/wrnash1/development/nautilus
git pull origin main
./scripts/split-enterprise-apps.sh    # ← Don't forget!
```

### Create Feature Branch
```bash
git checkout -b feature/my-new-feature
# ... make changes ...
git add app/ database/
git commit -m "Add my feature"
git push origin feature/my-new-feature
```

### Test Your Changes
```bash
# Rebuild first!
./scripts/split-enterprise-apps.sh

# Test customer app
cd ../nautilus-customer/public
php -S localhost:8000

# Test staff app (new terminal)
cd ../nautilus-staff/public
php -S localhost:8001
```

### Deploy to Testing Server
```bash
ssh testserver
cd /var/www/nautilus
git pull origin main
./scripts/split-enterprise-apps.sh
sudo ./scripts/deploy-to-production.sh
```

---

## 📂 File Locations

| What | Where |
|------|-------|
| **Source Code** | `/home/wrnash1/development/nautilus/` |
| **Controllers** | `nautilus/app/Controllers/` |
| **Models** | `nautilus/app/Models/` |
| **Services** | `nautilus/app/Services/` |
| **Views** | `nautilus/app/Views/` |
| **Routes** | `nautilus/routes/web.php` |
| **Migrations** | `nautilus/database/migrations/` |
| **Generated Customer App** | `/home/wrnash1/development/nautilus-customer/` |
| **Generated Staff App** | `/home/wrnash1/development/nautilus-staff/` |

---

## 🔧 Build & Deploy Commands

```bash
# Generate applications from source
./scripts/split-enterprise-apps.sh

# Deploy to production
sudo ./scripts/deploy-to-production.sh

# Run database migrations
php scripts/migrate.php

# Seed demo data
php scripts/seed-demo-data.php

# Backup database
./scripts/backup.sh
```

---

## 🌳 Branch Strategy

```
main              ← Production-ready code
  └── develop     ← Active development
       ├── feature/new-feature
       ├── bugfix/fix-something
       └── hotfix/urgent-fix
```

---

## ✅ Before Committing Checklist

- [ ] Changes made in `nautilus/` (source), NOT in generated apps
- [ ] Rebuilt apps: `./scripts/split-enterprise-apps.sh`
- [ ] Tested customer app: `http://localhost:8000`
- [ ] Tested staff app: `http://localhost:8001/store/login`
- [ ] Only committing source files (not `nautilus-customer/` or `nautilus-staff/`)
- [ ] No `.env` files committed
- [ ] Commit message is descriptive

---

## 🆘 Emergency Commands

```bash
# Lost changes? Reset to last commit
git reset --hard HEAD

# Messed up pull? Abort rebase
git rebase --abort

# Need clean state? Discard all local changes
git reset --hard origin/main

# Generated apps broken? Rebuild
./scripts/split-enterprise-apps.sh
```

---

## 📊 Workflow Diagram

```
Developer Machine:
┌──────────────────────────────────────────────────────┐
│ nautilus/ (git repo)                                 │
│   ↓ ./scripts/split-enterprise-apps.sh              │
│ nautilus-customer/ (generated, not tracked)          │
│ nautilus-staff/ (generated, not tracked)             │
└──────────────────────────────────────────────────────┘
            ↓ git push
            ↓
┌──────────────────────────────────────────────────────┐
│ GitHub: github.com/yourusername/nautilus             │
└──────────────────────────────────────────────────────┘
            ↓ git pull
            ↓
┌──────────────────────────────────────────────────────┐
│ Testing Server:                                      │
│ /var/www/nautilus/ (git repo)                       │
│   ↓ ./scripts/split-enterprise-apps.sh              │
│ /var/www/html/nautilus-customer-test/               │
│ /var/www/html/nautilus-staff-test/                  │
└──────────────────────────────────────────────────────┘
            ↓ After testing passes
            ↓
┌──────────────────────────────────────────────────────┐
│ Production Server:                                   │
│ /var/www/nautilus/ (git repo)                       │
│   ↓ ./scripts/split-enterprise-apps.sh              │
│ /var/www/html/nautilus-customer/                    │
│ /var/www/html/nautilus-staff/                       │
└──────────────────────────────────────────────────────┘
```

---

## 🎓 Key Concepts

**Monorepo**
- Single git repository
- All source code in one place
- Easy to sync and collaborate

**Build Artifacts**
- Generated apps are build artifacts
- Like compiled code - not committed to git
- Rebuilt from source whenever needed

**Split Script**
- Takes source code from `nautilus/`
- Generates `nautilus-customer/` and `nautilus-staff/`
- Run after every `git pull`!

---

## 📱 Quick Help

| Problem | Solution |
|---------|----------|
| Changes not appearing | Rebuild: `./scripts/split-enterprise-apps.sh` |
| Can't pull changes | Stash: `git stash` then `git pull` |
| Accidentally edited generated app | Discard, rebuild from source |
| Testing server outdated | SSH, `git pull`, rebuild, deploy |
| Merge conflict | Resolve in source, then rebuild |

---

## 📚 Full Documentation

- **[START_HERE.md](START_HERE.md)** - Entry point
- **[DEVELOPMENT_WORKFLOW.md](DEVELOPMENT_WORKFLOW.md)** - Detailed workflow
- **[docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)** - Code guidelines
- **[docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)** - Production deployment

---

**Print this and keep it at your desk!** 📋

---

**Version**: 2.0
**Last Updated**: 2025-10-26
