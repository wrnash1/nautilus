# 🚀 Nautilus Quick Start Guide

**Version:** Beta 1
**Status:** Ready for Production Testing

---

## 📖 What is Nautilus?

Nautilus is a complete dive shop management system with **91% PADI standards compliance**.

### Key Features
✅ Point of Sale & Inventory
✅ Customer & Course Management
✅ **PADI-Compliant Student Tracking**
✅ **Tablet-Optimized Skills Checkoff (Offline Mode)**
✅ **Universal Camera Capture**
✅ Equipment Rentals & Trip Management
✅ Cash Drawer & Reporting

---

## 🎯 Three Ways to Use This Guide

### 1. **First Time Installing?**
→ See [Installation](#installation) below

### 2. **Pulling Updates from GitHub?**
→ See [Updating](#updating-existing-installation) below

### 3. **Want to Contribute or Report Issues?**
→ Go to `/feedback/create` in the application (built-in feedback system!)

---

## 📦 Installation

### Prerequisites
- Linux server (Fedora, CentOS, Ubuntu)
- Apache/Nginx + PHP 8.0+
- MySQL 8.0+
- Git

### Quick Install (3 Steps)

```bash
# 1. Clone repository
git clone <your-repo-url>
cd nautilus

# 2. Create database
mysql -u root -p -e "CREATE DATABASE nautilus"

# 3. Run web installer
# Navigate to: https://your-domain.com/install
```

The installer will:
- Run all 55+ database migrations
- Seed default data (certifications, skills, etc.)
- Create admin account
- Configure settings

**Detailed Instructions:** See [docs/INSTALLATION_COMPLETE_FIX.md](docs/INSTALLATION_COMPLETE_FIX.md)

---

## 🔄 Updating Existing Installation

```bash
cd /path/to/nautilus
git pull origin main

# Run any new migrations
mysql -u user -p nautilus < database/migrations/055_feedback_ticket_system.sql
```

---

## 📚 Documentation

### Root Directory
- **[README.md](README.md)** - System overview and features
- **[VERSION.md](VERSION.md)** - Version history & release notes
- **[QUICK_START.md](QUICK_START.md)** - This guide

### Complete Documentation (`docs/` directory)
- **[Production Roadmap](docs/PRODUCTION_ROADMAP.md)** - Path to v1.0 (12-18 weeks)
- **[PADI Compliance Checklist](docs/PADI_COMPLIANCE_CHECKLIST.md)** - What's implemented (91%)
- **[Implementation Roadmap](docs/IMPLEMENTATION_ROADMAP.md)** - PADI features roadmap
- **[Installation Guide](docs/INSTALLATION_COMPLETE_FIX.md)** - Detailed installation
- **[Deployment Guide](docs/DEPLOYMENT_GUIDE.md)** - Production deployment
- **[Deployment Summary PADI](docs/DEPLOYMENT_SUMMARY_PADI.md)** - PADI-specific deployment
- **[Documentation Index](docs/DOCUMENTATION_INDEX.md)** - Complete file index

---

## 🎓 Using the PADI Features

### For Instructors: Skills Checkoff

1. **Navigate:** `/instructor/skills`
2. **Select Student:** Choose from your roster
3. **Select Session:** Confined Water 1-5 or Open Water 1-4
4. **Mark Skills:** Large touch buttons, works offline
5. **Complete Session:** Automatic progress tracking

**Works offline!** Skills sync when internet returns.

### For Shop Owners: Student Progress

- View complete student records
- Track course completion
- Monitor instructor performance
- Generate PADI-compliant reports

---

## 💬 Feedback & Support

### Built-In Feedback System (NEW!)

**Submit feedback directly in the application:**

```
Navigate to: /feedback/create
```

Use this to:
- 🐛 Report bugs
- 💡 Request features
- ❓ Ask questions
- 📚 Suggest documentation improvements

**Or use GitHub Issues** for open-source collaboration.

---

## 📊 System Status

### ✅ Fully Working
- POS, Inventory, Customers
- Course Enrollment
- **Student Skills Tracking (Offline)**
- **Camera Capture (All Devices)**
- Cash Drawer, Rentals, Trips
- Reporting & Analytics

### ⚠️ Database Ready (UI Pending)
- Medical Form Submission
- Digital Waiver Signing
- Training Completion Workflow
- Incident Reporting Mobile UI
- Pre-Dive Safety Check Mobile
- Quality Control Dashboard

**Translation:** Backend is 100% complete, some UIs need building for v1.0.

---

## 🎯 What's Next?

### Path to v1.0 (Production)

**6 Major UIs to Build:**
1. Medical form submission interface
2. Digital waiver signing (touch)
3. Training completion workflow
4. Incident reporting mobile
5. Pre-dive safety check mobile
6. Quality control dashboard

**Plus:**
- Automated feedback emails
- PDF generation for PADI forms
- Comprehensive testing

**Estimated Time:** 12-18 weeks
**Target Release:** February 2026

See [docs/PRODUCTION_ROADMAP.md](docs/PRODUCTION_ROADMAP.md) for detailed plan.

---

## 🚨 Common Issues

### Installation fails at migration X
```bash
# Check which migrations ran
mysql -u user -p nautilus -e "SELECT * FROM migrations ORDER BY id DESC"

# Re-run specific migration
mysql -u user -p nautilus < database/migrations/XXX_migration_name.sql
```

### Skills checkoff not loading
```bash
# Verify PADI skills were seeded
mysql -u user -p nautilus -e "SELECT COUNT(*) FROM padi_standard_skills"
# Should return: 45
```

### Routes not found
Add routes to `routes/web.php` - see [docs/DEPLOYMENT_SUMMARY_PADI.md](docs/DEPLOYMENT_SUMMARY_PADI.md)

---

## 📞 Need Help?

1. **Check Documentation:** [docs/DOCUMENTATION_INDEX.md](docs/DOCUMENTATION_INDEX.md)
2. **Submit Feedback:** `/feedback/create` in application
3. **GitHub Issues:** For bugs and feature requests

---

## 🎉 Quick Tips

### For Dive Shop Staff
- Use tablets at dive sites for skills checkoff
- Turn off Wi-Fi to test offline mode
- Take customer photos with built-in camera capture
- Use feedback system to suggest improvements

### For Developers
- All migrations are idempotent (can re-run safely)
- Services contain business logic, controllers are thin
- Views use Bootstrap 5 and vanilla JavaScript
- See [docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)

### For Beta Testers
- Test on multiple devices (iPad, Android, desktop)
- Try offline mode at dive sites
- Report issues via `/feedback/create`
- Vote on feature requests you want

---

## 📈 Version Info

**Current:** Beta 1 (November 6, 2025)
**PADI Compliance:** 91%
**Production Ready:** Backend complete, UI pending
**Next Release:** v1.0 (Target: Q1 2026)

See [VERSION.md](VERSION.md) for complete history.

---

## ✅ Success Checklist

- [ ] Installed successfully
- [ ] Can login as admin
- [ ] POS works
- [ ] Created test course enrollment
- [ ] Tried skills checkoff on tablet
- [ ] Tested offline mode
- [ ] Took photo with camera capture
- [ ] Submitted feedback ticket
- [ ] Read PADI compliance checklist

---

**Ready to dive in? Start with installation and explore the system!** 🌊🤿

---

*For detailed guides, see [docs/DOCUMENTATION_INDEX.md](docs/DOCUMENTATION_INDEX.md)*
*To contribute or report issues, use `/feedback/create` in the application*
