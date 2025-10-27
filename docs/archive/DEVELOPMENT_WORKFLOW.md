# Nautilus Development Workflow

**Single Repository for Team Development**

This guide explains how to work with the Nautilus codebase as a team, test changes, and deploy to production.

---

## 📁 Repository Structure

```
nautilus/                          ← SINGLE Git repository (tracked)
├── .git/
├── README.md
├── START_HERE.md
├── DEVELOPMENT_WORKFLOW.md        ← This file
├── app/                           ← Source code (TRACKED)
│   ├── Controllers/
│   ├── Core/
│   ├── Models/
│   ├── Services/
│   ├── Middleware/
│   └── Views/
├── database/                      ← Migrations (TRACKED)
├── routes/                        ← Route definitions (TRACKED)
├── public/                        ← Public assets (TRACKED)
├── scripts/                       ← Automation scripts (TRACKED)
│   ├── split-enterprise-apps.sh   ← Generates deployable apps
│   ├── deploy-to-production.sh
│   ├── seed-demo-data.php
│   └── backup.sh
├── docs/                          ← Documentation (TRACKED)
└── .gitignore                     ← Excludes generated apps

Generated (NOT tracked in git):
├── nautilus-customer/             ← Build artifact (NOT TRACKED)
└── nautilus-staff/                ← Build artifact (NOT TRACKED)
```

**Key Point**: Only the source code in `nautilus/` is tracked in Git. The split applications are **build artifacts** generated from source.

---

## 👥 Team Development Workflow

### **1. Initial Setup (One Time Per Developer)**

```bash
# Clone the repository
git clone https://github.com/yourusername/nautilus.git
cd nautilus

# Install dependencies
composer install

# Generate the applications for local testing
./scripts/split-enterprise-apps.sh

# Configure customer app
cd ../nautilus-customer
cp .env.example .env
nano .env  # Set database credentials, APP_BASE_PATH=

# Configure staff app
cd ../nautilus-staff
cp .env.example .env
nano .env  # Set database credentials, APP_BASE_PATH=/store

# Create local database
mysql -u root -p -e "CREATE DATABASE nautilus_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
cd ../nautilus-customer
php scripts/migrate.php

# Seed demo data (optional)
php scripts/seed-demo-data.php
```

---

### **2. Daily Development Workflow**

#### **Developer Workflow:**

```bash
# 1. Start your day - Pull latest changes
cd /home/wrnash1/development/nautilus
git pull origin main

# 2. Create a feature branch
git checkout -b feature/add-equipment-tracking

# 3. Work on source code in nautilus/
# Edit files in: app/, database/, routes/, etc.

# 4. Rebuild applications to test your changes
./scripts/split-enterprise-apps.sh

# 5. Test locally
cd ../nautilus-customer/public
php -S localhost:8000

# (In another terminal)
cd ../nautilus-staff/public
php -S localhost:8001

# 6. If everything works, commit your changes (SOURCE CODE ONLY)
cd /home/wrnash1/development/nautilus
git add app/ database/ routes/ docs/
git commit -m "Add equipment tracking feature"

# 7. Push to GitHub
git push origin feature/add-equipment-tracking

# 8. Create Pull Request on GitHub
# Other team members review and approve
```

#### **Important Notes:**
- ✅ **DO commit**: Source code in `nautilus/` directory
- ❌ **DON'T commit**: Generated apps (`nautilus-customer/`, `nautilus-staff/`)
- ✅ **DO rebuild**: Run split script after pulling changes
- ✅ **DO test**: Test both customer and staff apps before pushing

---

### **3. Testing Workflow**

#### **Local Testing (Developer Machine)**

```bash
# After making changes
cd /home/wrnash1/development/nautilus

# Rebuild apps
./scripts/split-enterprise-apps.sh

# Test customer app
cd ../nautilus-customer/public
php -S localhost:8000

# Test staff app (separate terminal)
cd ../nautilus-staff/public
php -S localhost:8001

# Run manual tests
# - Check your new feature
# - Verify existing features still work
# - Test on different browsers
```

#### **Testing Computer (Pre-Production)**

```bash
# On testing computer
cd /var/www/testing

# Pull latest code
git pull origin main

# Rebuild applications
./scripts/split-enterprise-apps.sh

# Deploy to testing web server
sudo ./scripts/deploy-to-production.sh

# Or manually:
sudo rsync -av ../nautilus-customer/ /var/www/html/nautilus-customer-test/
sudo rsync -av ../nautilus-staff/ /var/www/html/nautilus-staff-test/

# Configure Apache for test domains
# test-customer.yourdomain.com
# test-staff.yourdomain.com

# Run integration tests
# Have QA team test everything
```

---

## 🧪 Three-Tier Testing Strategy

```
┌─────────────────────────────────────────────────────────────┐
│                    DEVELOPMENT                               │
│  Developer Machine - Local testing with PHP built-in server │
│  - Rapid iteration                                          │
│  - Unit testing                                             │
│  - Feature development                                      │
└──────────────────┬──────────────────────────────────────────┘
                   │ git push
                   ▼
┌─────────────────────────────────────────────────────────────┐
│                    TESTING/STAGING                           │
│  Testing Computer - Full Apache setup, test database        │
│  - Integration testing                                      │
│  - QA testing                                               │
│  - Performance testing                                      │
│  - Security testing                                         │
└──────────────────┬──────────────────────────────────────────┘
                   │ After approval
                   ▼
┌─────────────────────────────────────────────────────────────┐
│                    PRODUCTION                                │
│  Production Server - Live customer traffic                  │
│  - Real data                                                │
│  - Backups enabled                                          │
│  - Monitoring enabled                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 Deployment to Testing Computer

### **Option 1: Manual Deployment**

```bash
# On your development computer
cd /home/wrnash1/development/nautilus

# Commit and push changes
git add .
git commit -m "Ready for testing"
git push origin main

# On testing computer
ssh user@testing-server
cd /var/www/nautilus
git pull origin main

# Rebuild applications
./scripts/split-enterprise-apps.sh

# Deploy
sudo ./scripts/deploy-to-production.sh
```

### **Option 2: Automated Deployment (Recommended)**

Create a deployment script on testing computer:

`/home/testuser/deploy-to-test.sh`:
```bash
#!/bin/bash

echo "Deploying to testing environment..."

# Pull latest code
cd /var/www/nautilus
git pull origin main

# Rebuild applications
./scripts/split-enterprise-apps.sh

# Install dependencies
cd ../nautilus-customer && composer install --no-dev
cd ../nautilus-staff && composer install --no-dev

# Run migrations
cd ../nautilus-customer
php scripts/migrate.php

# Deploy to web directories
sudo rsync -av --delete \
    /var/www/nautilus-customer/ \
    /var/www/html/nautilus-customer-test/

sudo rsync -av --delete \
    /var/www/nautilus-staff/ \
    /var/www/html/nautilus-staff-test/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/nautilus-customer-test
sudo chown -R www-data:www-data /var/www/html/nautilus-staff-test

# Restart Apache
sudo systemctl restart apache2

echo "Deployment complete!"
echo "Customer: https://test-customer.yourdomain.com"
echo "Staff:    https://test-staff.yourdomain.com"
```

Then just run:
```bash
./deploy-to-test.sh
```

---

## 🔄 Branch Strategy

### **Main Branches**

```
main (or master)
  ├── develop              ← Active development
  ├── feature/new-feature  ← Individual features
  ├── bugfix/fix-bug       ← Bug fixes
  └── release/v2.1         ← Release preparation
```

### **Workflow:**

```bash
# 1. Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/add-loyalty-program

# 2. Work on feature
# ... make changes ...

# 3. Commit regularly
git add .
git commit -m "Add loyalty points calculation"

# 4. Push to GitHub
git push origin feature/add-loyalty-program

# 5. Create Pull Request: feature/add-loyalty-program → develop

# 6. After review and approval, merge to develop

# 7. When ready for testing, merge develop → main
git checkout main
git merge develop
git push origin main

# 8. Deploy main branch to testing server

# 9. After testing passes, deploy main to production
```

---

## 📋 Pull Request Checklist

Before creating a PR, ensure:

- [ ] Code follows project standards (see [DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md))
- [ ] All new features have been tested locally
- [ ] Database migrations created (if needed)
- [ ] Documentation updated (if needed)
- [ ] No sensitive data (passwords, keys) committed
- [ ] `.env.example` updated (if new config added)
- [ ] Rebuild script runs successfully
- [ ] Both customer and staff apps tested
- [ ] No errors in logs

---

## 🎯 Common Scenarios

### **Scenario 1: Adding a New Feature**

```bash
# 1. Pull latest
cd /home/wrnash1/development/nautilus
git pull origin main

# 2. Create feature branch
git checkout -b feature/wetsuit-rentals

# 3. Add code to source
nano app/Controllers/Rentals/WetsuitController.php
nano database/migrations/018_create_wetsuit_tables.sql

# 4. Rebuild apps
./scripts/split-enterprise-apps.sh

# 5. Test
cd ../nautilus-staff/public
php -S localhost:8001

# 6. Commit source code
cd /home/wrnash1/development/nautilus
git add app/ database/
git commit -m "Add wetsuit rental management"
git push origin feature/wetsuit-rentals

# 7. Create PR on GitHub
```

### **Scenario 2: Fixing a Bug**

```bash
# 1. Create bugfix branch
git checkout -b bugfix/fix-checkout-crash

# 2. Fix the bug in source
nano app/Controllers/Ecommerce/CheckoutController.php

# 3. Rebuild and test
./scripts/split-enterprise-apps.sh
cd ../nautilus-customer/public
php -S localhost:8000

# 4. Commit and push
cd /home/wrnash1/development/nautilus
git add app/
git commit -m "Fix checkout crash when cart is empty"
git push origin bugfix/fix-checkout-crash

# 5. Create PR for immediate review
```

### **Scenario 3: Team Member Pulls Changes**

```bash
# 1. Pull latest changes
cd /home/wrnash1/development/nautilus
git pull origin main

# 2. Rebuild applications (IMPORTANT!)
./scripts/split-enterprise-apps.sh

# 3. Run any new migrations
cd ../nautilus-customer
php scripts/migrate.php

# 4. Test that everything works
cd public
php -S localhost:8000
```

---

## 🖥️ Setting Up Testing Computer

### **One-Time Setup on Testing Server**

```bash
# 1. Install prerequisites
sudo apt update
sudo apt install apache2 php8.2 mysql-server git composer

# 2. Clone repository
cd /var/www
sudo git clone https://github.com/yourusername/nautilus.git
sudo chown -R $USER:$USER nautilus

# 3. Generate applications
cd nautilus
./scripts/split-enterprise-apps.sh

# 4. Configure .env files for testing
cd ../nautilus-customer
cp .env.example .env
nano .env  # Set test database credentials

cd ../nautilus-staff
cp .env.example .env
nano .env  # Set test database credentials

# 5. Create test database
mysql -u root -p -e "CREATE DATABASE nautilus_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Run migrations
cd ../nautilus-customer
php scripts/migrate.php
php scripts/seed-demo-data.php

# 7. Deploy to web directories
sudo ./scripts/deploy-to-production.sh

# 8. Configure Apache (see ENTERPRISE_DEPLOYMENT_GUIDE.md)

# 9. Setup automated updates (optional)
crontab -e
# Add: 0 */6 * * * /home/testuser/deploy-to-test.sh
```

---

## 🔐 Production Deployment

### **When Testing Passes:**

```bash
# On production server
ssh user@production-server

# Pull approved code
cd /var/www/nautilus
git pull origin main

# Rebuild applications
./scripts/split-enterprise-apps.sh

# Backup current production (IMPORTANT!)
sudo /var/www/nautilus/scripts/backup.sh

# Deploy
sudo ./scripts/deploy-to-production.sh

# Run any new migrations
cd /var/www/html/nautilus-customer
php scripts/migrate.php

# Monitor logs
sudo tail -f /var/log/apache2/error.log
```

---

## 📊 Directory Comparison

| Location | Development PC | Testing Server | Production Server |
|----------|---------------|----------------|-------------------|
| **Source Repo** | `/home/wrnash1/development/nautilus/` | `/var/www/nautilus/` | `/var/www/nautilus/` |
| **Customer App** | `/home/wrnash1/development/nautilus-customer/` | `/var/www/html/nautilus-customer-test/` | `/var/www/html/nautilus-customer/` |
| **Staff App** | `/home/wrnash1/development/nautilus-staff/` | `/var/www/html/nautilus-staff-test/` | `/var/www/html/nautilus-staff/` |
| **Database** | `nautilus_dev` | `nautilus_test` | `nautilus` |
| **Web Server** | PHP built-in (port 8000/8001) | Apache (test subdomains) | Apache (live domains) |

---

## 🛠️ Troubleshooting

### **Generated apps don't have my changes**

```bash
# Always rebuild after pulling changes!
cd /home/wrnash1/development/nautilus
./scripts/split-enterprise-apps.sh
```

### **Merge conflicts**

```bash
# Pull with rebase to avoid merge commits
git pull --rebase origin main

# If conflicts occur, resolve them
git status  # See conflicted files
nano conflicted-file.php  # Fix conflicts
git add conflicted-file.php
git rebase --continue
```

### **Testing server out of sync**

```bash
# On testing server
cd /var/www/nautilus
git fetch origin
git reset --hard origin/main
./scripts/split-enterprise-apps.sh
sudo ./scripts/deploy-to-production.sh
```

---

## 📝 Best Practices

1. **Always work on source code** in `nautilus/` directory
2. **Never edit generated apps** directly - changes will be lost
3. **Rebuild after every pull** to get latest changes
4. **Test both apps** before pushing
5. **Use feature branches** for all changes
6. **Create PRs** for code review
7. **Test on testing server** before production
8. **Backup production** before deploying
9. **Monitor logs** after deployment
10. **Document changes** in commit messages

---

## 🎯 Quick Reference

```bash
# Daily workflow
git pull origin main                      # Get latest
./scripts/split-enterprise-apps.sh        # Rebuild apps
# ... make changes to source code ...
git add app/ database/ routes/            # Stage changes
git commit -m "Description"               # Commit
git push origin feature/branch-name       # Push

# Testing
cd ../nautilus-customer/public && php -S localhost:8000
cd ../nautilus-staff/public && php -S localhost:8001

# Deploy to testing
ssh testserver
cd /var/www/nautilus && git pull && ./scripts/split-enterprise-apps.sh
sudo ./scripts/deploy-to-production.sh

# Deploy to production
ssh prodserver
cd /var/www/nautilus && git pull && ./scripts/split-enterprise-apps.sh
sudo /var/www/nautilus/scripts/backup.sh  # BACKUP FIRST!
sudo ./scripts/deploy-to-production.sh
```

---

## 📚 Related Documentation

- [START_HERE.md](START_HERE.md) - Getting started guide
- [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) - Quick setup
- [docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md) - Production deployment
- [docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md) - Development guidelines
- [ARCHITECTURE.md](ARCHITECTURE.md) - System architecture

---

**Questions?** See the troubleshooting section in [docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)

---

**Version**: 2.0
**Last Updated**: 2025-10-26
