# 🌊 Nautilus Dive Shop Management System

**Complete dive shop management with PADI standards compliance**

![Version](https://img.shields.io/badge/version-Beta_1-blue)
![PHP](https://img.shields.io/badge/PHP-8.4-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange)
![PADI Compliance](https://img.shields.io/badge/PADI_Compliance-91%25-green)

---

## Overview

Nautilus is a complete dive shop management system with **91% PADI standards compliance**. It handles everything from point-of-sale to customer management, inventory, course enrollment, student skills tracking, equipment rentals, and comprehensive reporting.

### Key Features

- ✅ **PADI-Compliant Student Tracking** - 45 Open Water skills with tablet interface
- ✅ **Offline Mode** - Skills checkoff works without internet at dive sites
- ✅ **Universal Camera Capture** - Works on desktop, tablet, and mobile devices
- ✅ **Complete POS & Inventory** - Point-of-sale, stock tracking, barcode support
- ✅ **Course Management** - Enrollment, scheduling, student progress tracking
- ✅ **Built-in Feedback System** - Submit bugs and feature requests at `/feedback/create`

---

## Features

### 🎓 PADI Compliance (91% Complete)
- **Student Skills Tracking** - 45 Open Water skills (Confined Water 1-5, Open Water 1-4)
- **Tablet-Optimized Interface** - Large touch buttons for use at dive sites
- **Offline Capability** - Skills sync when internet returns
- **Medical Form Management** - 34 PADI medical questions
- **Liability Waivers** - 11 waiver types
- **Incident Reporting** - PADI Form 10120
- **Training Completion** - PADI Form 10234
- **Quality Control** - Automated student feedback

### 💰 Point of Sale & Inventory
- Fast checkout with barcode scanning
- Cash drawer tracking with denomination counting
- Real-time stock levels and low stock alerts
- Product categories and supplier management

### 👥 Customer & Course Management
- Complete customer profiles with certifications
- Course enrollment and scheduling
- Student progress tracking
- Emergency contacts and medical information

### 🚤 Equipment & Trips
- Equipment rental management
- Trip planning and manifests
- Maintenance tracking

### 📊 Reports & Analytics
- Sales reports (daily/weekly/monthly)
- Customer analytics
- Instructor performance
- Cash variance reports

---

## Quick Start

### Installation (3 Steps)

```bash
# 1. Clone repository
git clone https://github.com/yourusername/nautilus.git
cd nautilus

# 2. Create database
mysql -u root -p -e "CREATE DATABASE nautilus"

# 3. Run web installer
# Navigate to: https://your-domain.com/install
```

The installer will run all 55+ migrations, seed PADI skills data, and create your admin account.

📖 **Full Instructions:** See [QUICK_START.md](QUICK_START.md)

---

## System Requirements

- **PHP:** 8.0 or higher
- **MySQL:** 8.0+ or MariaDB 10.5+
- **Web Server:** Apache or Nginx
- **Disk Space:** 500MB
- **RAM:** 512MB minimum (2GB recommended)

---

## Documentation

📖 **[Quick Start Guide](QUICK_START.md)** - Installation, updates, and getting started

📘 **[Complete Documentation](docs/)** - Detailed guides:
- [Installation Guide](docs/INSTALLATION_COMPLETE_FIX.md) - Step-by-step installation
- [Production Roadmap](docs/PRODUCTION_ROADMAP.md) - Path to v1.0 (12-18 weeks)
- [PADI Compliance Checklist](docs/PADI_COMPLIANCE_CHECKLIST.md) - What's implemented (91%)
- [Deployment Guide](docs/DEPLOYMENT_GUIDE.md) - Production deployment
- [Version History](VERSION.md) - Release notes and roadmap

---

## Feedback & Support

### Built-in Feedback System
Your testers can submit feedback directly in the application:

**Navigate to:** `/feedback/create`

Features:
- 🐛 Bug reports with auto-captured system info
- 💡 Feature requests with voting system
- ❓ Questions and support
- 📸 Screenshot uploads
- 🎫 Ticket tracking system

### Beta Testing
**Current Status:** Beta 1 - Ready for production testing
**PADI Compliance:** 91% complete
**Target:** v1.0 in Q1 2026 (12-18 weeks)

---

## What's Next - Production Roadmap

### Path to v1.0 (12-18 weeks)

**Backend:** 100% complete ✅
**Frontend:** 6 major UIs needed

**Week 1-2:** Medical & Waiver UIs
**Week 3-4:** Training & Incident UIs
**Week 5-6:** Safety Check & Quality Dashboard
**Week 7-9:** Email automation & PDF generation
**Week 10-15:** Testing, bug fixes, documentation

See [PRODUCTION_ROADMAP.md](docs/PRODUCTION_ROADMAP.md) for detailed breakdown.

---

## Technical Details

### Built With
- PHP 8.4
- MySQL 8.0+
- Bootstrap 5.3
- Vanilla JavaScript (no jQuery)
- Offline-first design

### Architecture
- MVC with service layer
- PDO with prepared statements
- Role-based access control
- 55+ database migrations
- Complete audit logging

---

## Contributing

This is a beta release. We welcome testers and feedback!

### How to Help
1. Install on your test server
2. Test with real dive shop workflows
3. Submit feedback via `/feedback/create` in the app
4. Report bugs on GitHub Issues
5. Vote on feature requests

---

## Version History

See [VERSION.md](VERSION.md) for complete release history.

**Current:** Beta 1 (November 6, 2025)
**Next:** v1.0 (Target: Q1 2026)

---

## License

All Rights Reserved

---

**Ready to test Nautilus?**

📖 [Get Started](QUICK_START.md) | 📘 [Documentation](docs/) | 💬 [Submit Feedback](/feedback/create)

---

<p align="center">
  <strong>Built for the dive community 🌊🤿</strong>
</p>

<p align="center">
  <sub>Beta 1 • November 6, 2025 • 91% PADI Compliant</sub>
</p>
