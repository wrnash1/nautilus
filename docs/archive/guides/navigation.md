# 🧭 Nautilus Navigation Guide

**Quick reference to find exactly what you need**

---

## 🚀 Getting Started (New Users Start Here!)

1. **[README.md](README.md)** - System overview and features
2. **[QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)** - Installation and setup
3. **[SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md)** - Copy-paste code examples
4. **Run `php verify-system.php`** - Verify installation

---

## 📚 Documentation by Purpose

### I want to... Learn About the System
- **What it does**: [README.md](README.md)
- **All features**: [FINAL_FEATURE_SUMMARY.md](FINAL_FEATURE_SUMMARY.md)
- **Complete reference**: [COMPLETE_SYSTEM_DOCUMENTATION.md](COMPLETE_SYSTEM_DOCUMENTATION.md)

### I want to... Get Started
- **Quick setup**: [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) (15 minutes)
- **Deployment**: [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- **Verification**: Run `php verify-system.php`

### I want to... Use the System
- **Code examples**: [SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md)
- **Common tasks**: Section by section in Simple Usage Guide
- **API reference**: [COMPLETE_SYSTEM_DOCUMENTATION.md](COMPLETE_SYSTEM_DOCUMENTATION.md)

### I want to... Understand Features
- **Enterprise features**: [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md)
- **Business intelligence**: [BUSINESS_INTELLIGENCE_GUIDE.md](BUSINESS_INTELLIGENCE_GUIDE.md)
- **Specific module**: See migration files (001-098)

### I want to... Deploy to Production
- **Pre-flight checklist**: [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- **System verification**: Run `php verify-system.php`
- **Integration tests**: Run `php tests/SystemIntegrationTest.php`

---

## 📖 Documentation Files

| File | Best For | Length |
|------|----------|--------|
| [README.md](README.md) | Overview, quick reference | 5 min read |
| [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) | Installation, feature tour | 15 min read |
| [SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md) | Code examples, how-to | 20 min read |
| [COMPLETE_SYSTEM_DOCUMENTATION.md](COMPLETE_SYSTEM_DOCUMENTATION.md) | Complete reference | 60+ min read |
| [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md) | Advanced features | 30 min read |
| [BUSINESS_INTELLIGENCE_GUIDE.md](BUSINESS_INTELLIGENCE_GUIDE.md) | Analytics & reports | 30 min read |
| [FINAL_FEATURE_SUMMARY.md](FINAL_FEATURE_SUMMARY.md) | Feature checklist | 10 min read |
| [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) | Go-live preparation | Interactive |

---

## 🎯 Quick Links by Topic

### Customer Management
- **Guide**: [SIMPLE_USAGE_GUIDE.md#customer-management](SIMPLE_USAGE_GUIDE.md#customer-management)
- **Tables**: customers, customer_notes, customer_documents, certifications
- **Migration**: 001-020

### Courses & Training
- **Guide**: [SIMPLE_USAGE_GUIDE.md#course-bookings](SIMPLE_USAGE_GUIDE.md#course-bookings)
- **Tables**: courses, bookings, enrollments, certifications
- **Migration**: 011-030

### Equipment & Rentals
- **Guide**: [SIMPLE_USAGE_GUIDE.md#equipment-rentals](SIMPLE_USAGE_GUIDE.md#equipment-rentals)
- **Tables**: equipment_inventory, equipment_rentals, equipment_maintenance
- **Migration**: 021-040

### Inventory Control
- **Guide**: [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md)
- **Service**: `app/Services/Inventory/` (to be created)
- **Tables**: product_master, inventory_stock_levels, inventory_movements
- **Migration**: 092

### Point of Sale
- **Guide**: [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md)
- **Tables**: pos_terminals, pos_transactions, cash_drawer_operations
- **Migration**: 095

### Layaway System
- **Guide**: [SIMPLE_USAGE_GUIDE.md#layaway-plans](SIMPLE_USAGE_GUIDE.md#layaway-plans)
- **Service**: `app/Services/Financial/LayawayService.php`
- **Tables**: layaway_plans, layaway_agreements, layaway_payment_schedules
- **Migration**: 098

### Diving Clubs
- **Guide**: [SIMPLE_USAGE_GUIDE.md#diving-clubs](SIMPLE_USAGE_GUIDE.md#diving-clubs)
- **Service**: `app/Services/Club/DivingClubService.php`
- **Tables**: diving_clubs, club_memberships, club_events
- **Migration**: 098

### Travel Booking
- **Guide**: [SIMPLE_USAGE_GUIDE.md#travel-bookings](SIMPLE_USAGE_GUIDE.md#travel-bookings)
- **Service**: `app/Services/Travel/TravelBookingService.php`
- **Tables**: travel_packages, travel_destinations, liveaboard_boats
- **Migration**: 089-091

### Business Intelligence
- **Guide**: [BUSINESS_INTELLIGENCE_GUIDE.md](BUSINESS_INTELLIGENCE_GUIDE.md)
- **Service**: `app/Services/Analytics/BusinessIntelligenceService.php`
- **Tables**: dashboards, kpi_definitions, report_templates
- **Migration**: 097

### Security System
- **Guide**: [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md)
- **Tables**: security_cameras, access_control_points, alarm_systems
- **Migration**: 093

### Communications
- **Guide**: [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md)
- **Tables**: communication_channels, conversations, messages
- **Migration**: 094

---

## 🗂️ File Structure

```
nautilus/
├── README.md                              ⭐ START HERE
├── QUICK_START_GUIDE.md                   ⭐ THEN THIS
├── SIMPLE_USAGE_GUIDE.md                  ⭐ CODE EXAMPLES
├── NAVIGATION.md                          📍 YOU ARE HERE
│
├── COMPLETE_SYSTEM_DOCUMENTATION.md       📚 Full reference
├── ENTERPRISE_FEATURES_COMPLETE.md        🏢 Advanced features
├── BUSINESS_INTELLIGENCE_GUIDE.md         📊 Analytics
├── FINAL_FEATURE_SUMMARY.md              ✅ Feature list
├── DEPLOYMENT_CHECKLIST.md               🚀 Go-live prep
│
├── verify-system.php                      🧪 Run after install
├── database/
│   └── migrations/
│       ├── 001_*.sql through 098_*.sql   📊 Database schema
│
├── app/
│   └── Services/
│       ├── Travel/
│       │   └── TravelBookingService.php
│       ├── Analytics/
│       │   ├── BusinessIntelligenceService.php
│       │   └── CustomerAnalyticsService.php
│       ├── Club/
│       │   └── DivingClubService.php
│       └── Financial/
│           └── LayawayService.php
│
└── tests/
    └── SystemIntegrationTest.php          🧪 Integration tests
```

---

## 🎓 Learning Path

### For Dive Shop Owners
1. Read [README.md](README.md) (understand what Nautilus can do)
2. Read [FINAL_FEATURE_SUMMARY.md](FINAL_FEATURE_SUMMARY.md) (see all features)
3. Review [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) (plan deployment)
4. Hire developer or IT professional for installation

### For Developers
1. Read [README.md](README.md) (system overview)
2. Read [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) (installation)
3. Run `php verify-system.php` (verify setup)
4. Review [SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md) (learn APIs)
5. Explore migration files (understand schema)
6. Review service classes (understand business logic)

### For System Administrators
1. Read [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) (setup requirements)
2. Complete [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) (go-live prep)
3. Run `php verify-system.php` (verify installation)
4. Run `php tests/SystemIntegrationTest.php` (run tests)
5. Set up monitoring and backups

### For End Users (Staff)
1. Attend training session
2. Reference [SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md) for common tasks
3. Use in-app help (? icon)
4. Contact administrator for support

---

## 🔍 Find Specific Information

### "How do I create a booking?"
→ [SIMPLE_USAGE_GUIDE.md#course-bookings](SIMPLE_USAGE_GUIDE.md#course-bookings)

### "How do I set up inventory?"
→ [ENTERPRISE_FEATURES_COMPLETE.md](ENTERPRISE_FEATURES_COMPLETE.md) + Migration 092

### "How do I create a layaway agreement?"
→ [SIMPLE_USAGE_GUIDE.md#layaway-plans](SIMPLE_USAGE_GUIDE.md#layaway-plans)

### "How do I manage a diving club?"
→ [SIMPLE_USAGE_GUIDE.md#diving-clubs](SIMPLE_USAGE_GUIDE.md#diving-clubs)

### "How do I generate reports?"
→ [BUSINESS_INTELLIGENCE_GUIDE.md](BUSINESS_INTELLIGENCE_GUIDE.md)

### "What features are included?"
→ [FINAL_FEATURE_SUMMARY.md](FINAL_FEATURE_SUMMARY.md)

### "How do I deploy to production?"
→ [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)

### "Is everything working?"
→ Run `php verify-system.php`

---

## 📞 Support

### Documentation Issues
- Check this navigation guide
- Search in appropriate documentation file
- Review migration comments in SQL files

### Technical Issues
- Run `php verify-system.php`
- Check error logs
- Review [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)

### Feature Questions
- See [FINAL_FEATURE_SUMMARY.md](FINAL_FEATURE_SUMMARY.md)
- Review service class documentation
- Check migration files for table structure

---

## 🎯 Common Tasks Quick Reference

| Task | File | Section |
|------|------|---------|
| Create customer | SIMPLE_USAGE_GUIDE.md | Example 1 |
| Book a course | SIMPLE_USAGE_GUIDE.md | Example 3 |
| Rent equipment | SIMPLE_USAGE_GUIDE.md | Example 5 |
| Start layaway | SIMPLE_USAGE_GUIDE.md | Example 7 |
| Create club | SIMPLE_USAGE_GUIDE.md | Example 9 |
| Book travel | SIMPLE_USAGE_GUIDE.md | Example 15 |
| Generate report | SIMPLE_USAGE_GUIDE.md | Example 17 |
| View dashboard | SIMPLE_USAGE_GUIDE.md | Example 18 |
| POS transaction | SIMPLE_USAGE_GUIDE.md | Example 20 |

---

## 🌟 Best Practices

1. **Always start with** [README.md](README.md)
2. **For installation**, use [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)
3. **For coding**, reference [SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md)
4. **For deployment**, follow [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
5. **Verify everything** with `php verify-system.php`

---

**Lost? Start here**: [README.md](README.md) → [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) → [SIMPLE_USAGE_GUIDE.md](SIMPLE_USAGE_GUIDE.md)

**Need help?** All documentation is cross-referenced and searchable.

---

*Last Updated: January 2025*
*Version: 1.0*
