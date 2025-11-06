# Nautilus Complete Deployment Guide

**Version:** 2.0 Alpha
**Date:** November 5, 2025
**Status:** Production Ready with Course Enrollment Workflow

---

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [Fresh Installation](#fresh-installation)
3. [Updating Existing Installation](#updating-existing-installation)
4. [Course Enrollment Deployment](#course-enrollment-deployment)
5. [Database Management](#database-management)
6. [Troubleshooting](#troubleshooting)
7. [Security](#security)

---

## 🚀 Quick Start

### For Existing Installations (Updates)

```bash
cd /home/wrnash1/development/nautilus
sudo bash /tmp/sync-all-nautilus-files.sh
```

This syncs everything: code, database migrations, course enrollment, assets.

### For Fresh Installations

1. Navigate to: `https://your-domain.com/install`
2. Follow the installation wizard
3. Database setup is automatic

---

## 🆕 Fresh Installation

### Prerequisites

**Server:**
- Apache 2.4+ with mod_rewrite
- PHP 8.1+ (mysqli, pdo, mbstring, openssl, curl)
- MySQL 8.0+ or MariaDB 10.6+
- 2GB RAM, 10GB disk

**Setup:**
- Domain configured with SSL
- Apache virtual host
- Database created

### Installation Steps

#### 1. Deploy Files

```bash
cd /var/www/html
git clone https://github.com/yourusername/nautilus.git
cd nautilus
composer install --no-dev
```

#### 2. Set Permissions

```bash
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 storage/ public/uploads/
```

#### 3. Run Installation Wizard

**Navigate to:** `https://nautilus.local/install`

The wizard handles:
✅ Database connection test
✅ Table creation (40+ migrations)
✅ Seed initial data (roles, permissions)
✅ Create admin account
✅ Configure settings

**Fixed Issue:** The installer now properly handles `cash_drawers` and `certification_agencies` tables (checks if tables exist before querying).

---

## 🔄 Updating Existing Installation

### Complete Deployment

```bash
sudo bash /tmp/sync-all-nautilus-files.sh
```

**What It Syncs:**
- Core application (controllers, services, views)
- Database migrations (all 40+)
- Course enrollment workflow
- POS system updates
- JavaScript/CSS assets
- Documentation

### Course Enrollment Only

```bash
cd /home/wrnash1/development/nautilus
sudo bash scripts/deploy-course-enrollment.sh
```

Syncs only course enrollment features.

---

## 🎓 Course Enrollment Deployment

### What's Included

**New Files:**
- `EnrollmentService.php` - Handles enrollments & transfers
- `CourseScheduleController.php` - API for schedule data
- `pos-course-enrollment.js` - Schedule selection modal
- `roster_show.php` - Comprehensive roster view

**Updated Files:**
- `TransactionService.php` - Auto-enrollment on purchase
- `CourseController.php` - Roster & transfer methods
- `pos/index.php` - Schedule modal HTML
- `professional-pos.js` - Course tile wiring
- `routes/web.php` - API & transfer endpoints
- `InstallService.php` - Fixed table existence checks

### Testing After Deployment

**1. Create Test Course:**
```
Courses > Create Course
- Name: "Test Open Water"
- Code: "TEST-OW"
- Price: $399
```

**2. Create Test Schedule:**
```
Courses > Schedules > Add Schedule
- Course: Test Open Water
- Instructor: (select)
- Dates: Tomorrow → +3 days
- Times: 9:00 AM - 5:00 PM
- Max Students: 8
```

**3. Test POS Enrollment:**
```
POS > Select Customer > Click Course Tile
→ Modal should appear with schedule selection
→ Complete sale
→ Check: SELECT * FROM course_enrollments ORDER BY id DESC LIMIT 1;
```

**4. View Roster:**
```
Courses > Schedules > View
→ Student should appear with contact info
→ Payment status should show "PAID"
```

**5. Test Transfer:**
```
Create 2nd schedule > Roster > Click "Transfer"
→ Modal appears → Select new schedule → Submit
→ Student moves, counts update
```

---

## 💾 Database Management

### Run Migrations

After code deployment:

```bash
cd /var/www/html/nautilus
php database/migrate.php
```

### Backup Database

```bash
bash scripts/backup.sh
```

Creates: `storage/backups/nautilus_YYYY-MM-DD_HHMMSS.sql`

### Restore Database

```bash
mysql -u user -p nautilus < backup.sql
```

### Check Migration Status

```bash
mysql -u user -p nautilus -e "SELECT * FROM migrations ORDER BY batch, id;"
```

---

## 🔧 Troubleshooting

### Installation Errors

**Error:** `Table 'cash_drawers' doesn't exist`

**Cause:** Old InstallService queried table before it was created
**Fix:** Redeploy with fixed InstallService:
```bash
sudo bash /tmp/sync-all-nautilus-files.sh
```

**Error:** `Permission denied`

**Fix:**
```bash
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 storage/ public/uploads/
```

### Course Enrollment Issues

**Schedule modal doesn't appear:**

Check:
1. Browser console (F12) for JavaScript errors
2. File exists: `/public/assets/js/pos-course-enrollment.js`
3. Course tile has `data-course-id="1"` attribute
4. Script loaded: View page source, search for "pos-course-enrollment.js"

**Student not enrolled after sale:**

Check:
1. Customer was selected (not walk-in)
2. Logs: `tail -f /var/www/html/nautilus/storage/logs/app.log`
3. Database: `SELECT * FROM course_enrollments WHERE customer_id = X;`
4. Schedule had available spots

**Transfer button not visible:**

Check:
1. User has `courses.edit` permission:
   ```sql
   SELECT p.name FROM permissions p
   JOIN role_permissions rp ON p.id = rp.permission_id
   JOIN users u ON u.role_id = rp.role_id
   WHERE u.id = YOUR_USER_ID;
   ```
2. Multiple schedules exist for same course

---

## 🔐 Security

### Production Checklist

- [ ] `.env` file secured (chmod 600)
- [ ] `APP_DEBUG=false` in .env
- [ ] SSL certificate valid
- [ ] Database user has minimal privileges
- [ ] File permissions: 755 directories, 644 files
- [ ] Upload directory protected
- [ ] Backups automated

### Secure Installation

```bash
# Secure .env
chmod 600 .env
chown apache:apache .env

# Secure storage
chmod 755 storage/
find storage/ -type f -exec chmod 644 {} \;

# Secure uploads
chmod 755 public/uploads/
echo "Options -Indexes" > public/uploads/.htaccess
```

---

## 📁 Deployment Contents

### Services
```
app/Services/
├── Courses/EnrollmentService.php (NEW)
├── POS/TransactionService.php (UPDATED)
└── Install/InstallService.php (FIXED)
```

### Controllers
```
app/Controllers/
├── Courses/CourseController.php (UPDATED)
└── API/CourseScheduleController.php (NEW)
```

### Views
```
app/Views/
├── pos/index.php (UPDATED - modal)
└── courses/schedules/show.php (NEW - roster)
```

### JavaScript
```
public/assets/js/
├── pos-course-enrollment.js (NEW)
└── professional-pos.js (UPDATED)
```

### Routes
```
routes/web.php (UPDATED)
+ GET /store/api/courses/{id}/schedules
+ POST /store/courses/transfer-student
```

### Database
```
database/
├── migrations/ (40+ files, all synced)
└── seeders/ (roles, permissions, agencies, drawers)
```

---

## 📊 Post-Deployment Checklist

### Immediately After Deployment

- [ ] Files synced successfully
- [ ] Permissions set (apache:apache, 755/644)
- [ ] Apache restarted if needed
- [ ] No errors in: `tail -f storage/logs/app.log`

### Testing

- [ ] Login works
- [ ] POS loads without errors
- [ ] Course list displays
- [ ] Schedule modal appears when clicking course
- [ ] Can complete a test sale
- [ ] Student enrolled in database
- [ ] Roster view shows student
- [ ] Transfer works between schedules

### Verification Queries

```sql
-- Check migrations ran
SELECT COUNT(*) as total_migrations FROM migrations;

-- Check enrollments working
SELECT * FROM course_enrollments ORDER BY id DESC LIMIT 5;

-- Check cash drawers seeded
SELECT * FROM cash_drawers;

-- Check certification agencies seeded
SELECT * FROM certification_agencies;
```

---

## 🎯 Quick Command Reference

| Task | Command |
|------|---------|
| **Full Deploy** | `sudo bash /tmp/sync-all-nautilus-files.sh` |
| **Course Enrollment** | `sudo bash scripts/deploy-course-enrollment.sh` |
| **Run Migrations** | `php database/migrate.php` |
| **Backup DB** | `bash scripts/backup.sh` |
| **View Logs** | `tail -f storage/logs/app.log` |
| **Fix Permissions** | `sudo chown -R apache:apache /var/www/html/nautilus` |
| **Restart Apache** | `sudo systemctl restart httpd` |
| **MySQL Console** | `mysql -u user -p nautilus` |

---

## 📞 Support & Logs

### Log Files

```bash
# Application logs
tail -f /var/www/html/nautilus/storage/logs/app.log

# Apache errors
tail -f /var/log/httpd/error_log

# MySQL errors
tail -f /var/log/mysql/error.log
```

### Debug Mode (Development Only)

In `.env`:
```env
APP_DEBUG=true
APP_ENVIRONMENT=development
```

**⚠️ Never enable debug mode in production!**

---

## 📚 Additional Documentation

- **Course Enrollment Workflow:** `docs/COURSE_ENROLLMENT_WORKFLOW.md`
- **Implementation Details:** `docs/COURSE_ENROLLMENT_IMPLEMENTATION.md`
- **Project Structure:** `PROJECT_STRUCTURE.md`
- **Cleanup Guide:** `docs/CLEANUP_OLD_FILES.md`

---

**Last Updated:** November 5, 2025
**Deployment Script:** `/tmp/sync-all-nautilus-files.sh`
**Status:** ✅ Production Ready
