# Nautilus Fresh Installation Guide

**For:** Fresh installations from GitHub on a new computer
**Date:** November 5, 2025
**Status:** Ready to Install

---

## 🚀 Quick Start

### 1. Fix Permissions First

```bash
sudo bash /tmp/fix-installation-permissions.sh
```

### 2. Run Installation Wizard

Navigate to: **https://nautilus.local/install**

---

## 📋 What Was Fixed

### Issue 1: Database Error (cash_drawers table)

**Error:**
```
Table 'cash_drawers' doesn't exist
```

**Fixed:** [InstallService.php](file:///var/www/html/nautilus/app/Services/Install/InstallService.php) now checks if tables exist before querying them.

### Issue 2: Permission Denied (.env file)

**Error:**
```
file_put_contents(/var/www/html/nautilus/.env): Failed to open stream: Permission denied
```

**Fix:** Run the permission fix script (see above).

---

## 📁 Complete Installation Process

### Step 1: Prepare the System

Make sure you have:
- ✅ Apache with mod_rewrite enabled
- ✅ PHP 8.1+ with required extensions
- ✅ MySQL/MariaDB running
- ✅ Database created: `nautilus`
- ✅ Database user with full privileges on `nautilus` database

### Step 2: Clone from GitHub

```bash
cd /var/www/html
git clone https://github.com/yourusername/nautilus.git
cd nautilus
```

### Step 3: Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Step 4: Fix Permissions

```bash
sudo bash /tmp/fix-installation-permissions.sh
```

This script:
- Sets ownership to `apache:apache`
- Makes storage directories writable (775)
- Fixes .env permissions (664)
- Configures SELinux contexts (if enabled)

### Step 5: Run Installation Wizard

**Navigate to:** https://nautilus.local/install

**Fill in:**
- Application Name: Nautilus Dive Shop
- Application URL: https://nautilus.local
- Database Host: localhost
- Database Name: nautilus
- Database User: your_db_user
- Database Password: your_db_password
- Admin Email: your@email.com
- Admin Password: (strong password)
- Admin Name: Your Name

**The wizard will:**
1. ✅ Update .env file
2. ✅ Create database (if doesn't exist)
3. ✅ Run 40+ migrations
4. ✅ Seed roles and permissions
5. ✅ Seed certification agencies
6. ✅ Seed cash drawers (if table exists)
7. ✅ Save company settings
8. ✅ Create admin user
9. ✅ Optionally install demo data

**Expected time:** 2-5 minutes

---

## ✅ Post-Installation

### 1. Login

Navigate to: https://nautilus.local/login

Use the admin credentials you created during installation.

### 2. Deploy Course Enrollment (Optional)

If you want the course enrollment workflow:

```bash
cd /home/wrnash1/development/nautilus
sudo bash scripts/deploy-course-enrollment.sh
```

This adds:
- Course schedule selection at POS
- Automatic student enrollment
- Instructor roster views
- Student transfer functionality

### 3. Secure .env File

After installation:

```bash
sudo chmod 640 /var/www/html/nautilus/.env
```

### 4. Verify Installation

Check:
- [ ] Can login as admin
- [ ] Dashboard loads
- [ ] POS system accessible
- [ ] No errors in: `tail -f /var/www/html/nautilus/storage/logs/app.log`

---

## 🔧 Troubleshooting

### Permission Errors

If you still get permission errors after running the fix script:

**Check SELinux:**
```bash
sudo getenforce
```

If it shows "Enforcing", either:

1. Set SELinux contexts (recommended):
```bash
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/.env"
sudo restorecon -Rv /var/www/html/nautilus/storage
sudo restorecon -v /var/www/html/nautilus/.env
```

2. Or temporarily disable for testing:
```bash
sudo setenforce 0
# Test installation
sudo setenforce 1  # Re-enable after
```

**Check Apache User:**
```bash
ps aux | grep apache | head -3
```
Should show processes running as `apache` user.

### Database Connection Fails

**Check MySQL is running:**
```bash
sudo systemctl status mariadb
# or
sudo systemctl status mysql
```

**Test connection:**
```bash
mysql -u your_db_user -p nautilus -e "SELECT 1;"
```

**Check privileges:**
```sql
mysql -u root -p
SHOW GRANTS FOR 'your_db_user'@'localhost';
```

Should see: `GRANT ALL PRIVILEGES ON nautilus.*`

### Migration Errors

If migrations fail during installation:

**Check logs:**
```bash
tail -f /var/www/html/nautilus/storage/logs/app.log
```

**Run migrations manually:**
```bash
cd /var/www/html/nautilus
php database/migrate.php
```

**Check migration status:**
```bash
mysql -u user -p nautilus -e "SELECT * FROM migrations ORDER BY batch;"
```

---

## 📊 Installation Checklist

### Before Installation
- [ ] Apache running and configured
- [ ] PHP 8.1+ installed with extensions
- [ ] MySQL/MariaDB running
- [ ] Database `nautilus` created
- [ ] Database user has privileges
- [ ] Composer installed
- [ ] Code cloned from GitHub
- [ ] Dependencies installed (`composer install`)
- [ ] Permissions fixed (`bash /tmp/fix-installation-permissions.sh`)

### During Installation
- [ ] Navigate to /install URL
- [ ] Fill in all required fields
- [ ] Database connection test passes
- [ ] Watch progress bar complete
- [ ] No errors shown
- [ ] Redirected to completion page

### After Installation
- [ ] Can access /login
- [ ] Can login with admin credentials
- [ ] Dashboard loads correctly
- [ ] No errors in logs
- [ ] .env file secured (chmod 640)
- [ ] Consider deploying course enrollment

---

## 🎓 Optional: Deploy Course Enrollment

After successful installation, deploy the course enrollment workflow:

```bash
cd /home/wrnash1/development/nautilus
sudo bash scripts/deploy-course-enrollment.sh
```

**Test the workflow:**

1. Create a course: Courses > Create Course
2. Create a schedule: Courses > Schedules > Add
3. Go to POS and click the course tile
4. Verify schedule selection modal appears
5. Complete a test sale
6. Check student enrolled: Courses > Schedules > View

---

## 📞 Getting Help

### Check Logs

**Application:**
```bash
tail -f /var/www/html/nautilus/storage/logs/app.log
```

**Apache:**
```bash
sudo tail -f /var/log/httpd/error_log
```

**MySQL:**
```bash
sudo tail -f /var/log/mysql/error.log
# or
sudo tail -f /var/log/mariadb/mariadb.log
```

### Common Issues

| Issue | Solution |
|-------|----------|
| Permission denied | Run `/tmp/fix-installation-permissions.sh` |
| cash_drawers error | Deploy updated InstallService.php |
| Database connection fails | Check MySQL running, verify credentials |
| Blank page | Check PHP error logs, enable display_errors |
| 500 error | Check Apache error_log, file permissions |
| SELinux blocking | Set contexts or disable temporarily |

---

## 🔄 Starting Over

If you need to restart the installation:

### 1. Drop and Recreate Database

```bash
mysql -u root -p
DROP DATABASE nautilus;
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
exit
```

### 2. Remove .env

```bash
sudo rm /var/www/html/nautilus/.env
```

### 3. Fix Permissions Again

```bash
sudo bash /tmp/fix-installation-permissions.sh
```

### 4. Navigate to Installer

https://nautilus.local/install

---

## 📚 Additional Documentation

- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Deployment for updates
- **[FIX_INSTALLATION_PERMISSIONS.md](FIX_INSTALLATION_PERMISSIONS.md)** - Detailed permission fix
- **[docs/COURSE_ENROLLMENT_WORKFLOW.md](docs/COURSE_ENROLLMENT_WORKFLOW.md)** - Course enrollment docs

---

## ✨ Summary

**Two main issues fixed:**

1. ✅ **Database Error:** InstallService.php now checks table existence
2. ✅ **Permission Error:** Fix script sets proper ownership and permissions

**Installation is now:**
- ✅ Fixed and ready to use
- ✅ All migrations included
- ✅ Course enrollment ready to deploy
- ✅ Documented and organized

**To install:**
```bash
sudo bash /tmp/fix-installation-permissions.sh
# Then navigate to: https://nautilus.local/install
```

**After installation:**
```bash
sudo bash scripts/deploy-course-enrollment.sh  # Optional
```

---

**Ready to install! 🚀**

**Questions? Check the logs or troubleshooting section above.**
