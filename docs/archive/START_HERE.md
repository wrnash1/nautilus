# 🚀 START HERE - Nautilus v2.0

**Your application has been cleaned up and is ready to deploy!**

---

## What Just Happened?

Your Nautilus dive shop application has been professionally cleaned up, documented, and prepared for enterprise deployment. Here's what's new:

✅ **Dual-Application Architecture** - Split into Customer + Staff apps
✅ **Professional Documentation** - 5 comprehensive guides (3,500+ lines)
✅ **Deployment Automation** - 4 production-ready scripts (1,450+ lines)
✅ **Demo Data** - One-command realistic test data
✅ **Automated Backups** - Daily backup script with retention
✅ **Production Ready** - Everything needed for deployment

---

## Choose Your Path

### 🏃 Quick Start (15 minutes)
**Perfect for**: Testing locally, seeing what you have

```bash
# 1. Split the application
cd /home/wrnash1/development/nautilus
./scripts/split-enterprise-apps.sh

# 2. Install dependencies
cd ../nautilus-customer && composer install
cd ../nautilus-staff && composer install

# 3. Setup database
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Configure apps (edit the .env files)
cd ../nautilus-customer && cp .env.example .env && nano .env
cd ../nautilus-staff && cp .env.example .env && nano .env

# 5. Run migrations
cd ../nautilus-customer && php scripts/migrate.php

# 6. Add demo data
php scripts/seed-demo-data.php

# 7. Test it
cd public && php -S localhost:8000
```

**Access:**
- Customer: http://localhost:8000
- Staff: http://localhost:8001/store/login (run in separate terminal)

**Login:** admin@diveshop.com / password

---

### 🏢 Production Deployment (30 minutes)
**Perfect for**: Going live with your dive shop

```bash
# 1. Split the application
cd /home/wrnash1/development/nautilus
./scripts/split-enterprise-apps.sh

# 2. Deploy to production
sudo ./scripts/deploy-to-production.sh

# 3. Configure Apache
sudo nano /etc/apache2/sites-available/nautilus.conf
# (Copy config from docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)

# 4. Enable site
sudo a2ensite nautilus.conf
sudo systemctl restart apache2

# 5. Setup automated backups
sudo crontab -e
# Add: 0 2 * * * /path/to/scripts/backup.sh
```

---

### 📚 Learn More (Read first)
**Perfect for**: Understanding the system before deploying

**Read these in order:**
1. [README.md](README.md) - Project overview
2. [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) - Step-by-step setup
3. [docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md) - Production deployment
4. [docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md) - Customization

---

## What's in the Box?

### 📄 Documentation (NEW)
- **README.md** - Main project overview (500+ lines)
- **QUICK_START_GUIDE.md** - 15-minute setup (200+ lines)
- **docs/ENTERPRISE_DEPLOYMENT_GUIDE.md** - Complete deployment (700+ lines)
- **docs/DEVELOPER_GUIDE.md** - Development guide (1,800+ lines)
- **CLEANUP_SUMMARY.md** - What was done (300+ lines)

### 🛠️ Scripts (NEW)
- **split-enterprise-apps.sh** - Split app into customer + staff (600+ lines)
- **deploy-to-production.sh** - Automated deployment (250+ lines)
- **seed-demo-data.php** - Populate with test data (400+ lines)
- **backup.sh** - Daily automated backups (200+ lines)

### 🏗️ Architecture
```
Customer App (Public)          Staff App (Internal)
├── Storefront                 ├── Point of Sale
├── Shopping Cart              ├── CRM
├── Customer Portal            ├── Inventory
└── E-commerce                 ├── Reports
                               └── Admin
         │                            │
         └──── Shared Database ───────┘
```

---

## Quick Commands

### Development
```bash
# Split app
./scripts/split-enterprise-apps.sh

# Run migrations
php scripts/migrate.php

# Seed demo data
php scripts/seed-demo-data.php

# Start local server
php -S localhost:8000 -t public
```

### Production
```bash
# Deploy
sudo ./scripts/deploy-to-production.sh

# Backup
./scripts/backup.sh

# View logs
tail -f storage/logs/app.log
sudo tail -f /var/log/apache2/error.log
```

---

## Default Login Credentials

### Staff Portal (`/store/login`)
- **Administrator**: admin@diveshop.com / password
- **Manager**: manager@diveshop.com / password
- **Sales**: mike@diveshop.com / password

### Customer Portal (`/account/login`)
- john.doe@example.com / password
- jane.smith@example.com / password

**⚠️ IMPORTANT**: Change these passwords immediately in production!

---

## File Structure

```
nautilus/                          ← Original monolithic app
├── scripts/
│   ├── split-enterprise-apps.sh   ← Run this first!
│   ├── deploy-to-production.sh
│   ├── seed-demo-data.php
│   └── backup.sh
│
├── docs/
│   ├── ENTERPRISE_DEPLOYMENT_GUIDE.md
│   ├── DEVELOPER_GUIDE.md
│   └── archive/                   ← Old docs moved here
│
├── README.md                      ← Start here
├── QUICK_START_GUIDE.md
├── START_HERE.md                  ← You are here
└── CLEANUP_SUMMARY.md

After splitting:
├── nautilus-customer/             ← Public storefront
└── nautilus-staff/                ← Internal management
```

---

## Support

### Need Help?

**Documentation:**
- Quick questions: See [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)
- Deployment: See [docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)
- Development: See [docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)
- Troubleshooting: All guides have troubleshooting sections

**Common Issues:**
- Route not found → Check Apache mod_rewrite is enabled
- Database error → Verify .env credentials
- Can't login → Check storage/ permissions
- 500 error → Check Apache error log

---

## Next Steps

1. **Choose your path** above (Quick Start or Production)
2. **Run the split script** to create the two apps
3. **Test locally** before deploying to production
4. **Read the docs** to understand the system
5. **Customize** for your dive shop

---

## What's Different?

### Before Cleanup
- 😕 Monolithic application
- 😕 17 scattered documentation files
- 😕 No deployment automation
- 😕 Manual everything
- 😕 Confusing structure

### After Cleanup (Now!)
- ✅ Two clean applications
- ✅ 5 professional guides
- ✅ One-command automation
- ✅ Production-ready
- ✅ Crystal-clear structure

---

## Your Next Move

**Just want to see it working?**
→ Follow the **Quick Start** path above (15 minutes)

**Ready to go live?**
→ Follow the **Production Deployment** path above (30 minutes)

**Want to understand it first?**
→ Read **[README.md](README.md)** then **[QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)**

---

**Questions? Check [CLEANUP_SUMMARY.md](CLEANUP_SUMMARY.md) for a complete breakdown of everything that was done.**

---

**🤿 Nautilus v2.0 - Ready to Dive In!**

*Professional dive shop management made simple*
