# Nautilus - Quick Start Guide

## Test Environment Setup (Current)

### 1. Reset and Deploy
```bash
cd /home/wrnash1/Developer/nautilus

# Drop existing database
mysql -u root -pFrogman09! -e "DROP DATABASE IF EXISTS nautilus;"

# Run deployment script
./deploy-to-test.sh
```

### 2. Run Migrations (New Product Fields + Course Workflows)
```bash
php8.2 scripts/migrate.php
# or
php scripts/migrate.php
```

**Important Migrations:**
- **Migration 035**: Adds product fields (QR code, weight, dimensions, color, etc.)
- **Migration 036**: Adds course workflow system (requirement tracking, instructor notifications)

### 3. Access Application
- URL: `https://pangolin.local`
- Or visit: `https://pangolin.local/install` to run installation wizard

### 4. Login
- Default admin credentials (if created via install wizard)
- Or create admin via: `php8.2 scripts/create-admin.php`

## Production Deployment (Fedora Server 43)

### Quick Deploy
```bash
# 1. Update system
sudo dnf update -y

# 2. Install dependencies
sudo dnf install -y php php-fpm php-mysqlnd php-mbstring php-xml \
    php-curl php-gd php-zip mariadb-server httpd

# 3. Clone repo
sudo mkdir -p /var/www/nautilus
cd /var/www/nautilus
sudo git clone <your-repo> .

# 4. Run install script
chmod +x install.sh
sudo ./install.sh

# 5. Configure SELinux
sudo setsebool -P httpd_can_network_connect_db 1
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/nautilus/storage(/.*)?"
sudo restorecon -Rv /var/www/nautilus

# 6. Open firewall
sudo firewall-cmd --permanent --add-service={http,https}
sudo firewall-cmd --reload

# 7. Get SSL cert
sudo dnf install -y certbot python3-certbot-apache
sudo certbot --apache
```

## New: Streamlined Course Workflow System 🚀

### Automated Enrollment Process
- When a customer signs up for a class in POS:
  - ✅ Welcome email sent automatically with course details
  - ✅ Instructor notified via email and in-app notification
  - ✅ Student requirement checklist created (waivers, e-learning, photos, etc.)
  - ✅ Roster updated in real-time
  - ✅ Requirement reminder emails sent
  - ✅ Everything flows together - zero manual work!

### Roster Management
- Instructors can view complete class roster
- Shows student photos, contact info, and requirement completion
- Visual progress bars (e.g., "7/10 requirements complete - 70%")
- Status badges: Ready (green), In Progress (yellow), Pending (red)
- Click to view detailed requirements for each student

### Requirement Tracking
- Liability waivers
- E-learning completion
- Student photos
- Medical questionnaires
- Certification cards
- Custom requirements per course

**Quick Access:**
- Full docs: `/docs/COURSE_WORKFLOW_SYSTEM.md`
- Overview: `/STREAMLINED_WORKFLOW_SUMMARY.md`
- Quick start: `/WORKFLOW_QUICK_START.md`

## Key Features Implemented

### Products ✅
- All CRUD operations working
- 10+ new fields added (QR code, weight, dimensions, color, material, etc.)
- Barcode support
- Stock tracking
- Location in store

### Customers ✅
- Photo upload/display
- Certification tracking with agency logos
- Emergency contacts
- B2B/B2C support

### Point of Sale ✅
- Real-time clock and date
- Store logo
- Customer photo display
- Certification badges with logos
- Clean, readable interface

### System ✅
- Favicon support
- Role-based settings access
- Installation automation
- Migration system

## Important Files

### Documentation
- `INSTALLATION.md` - Full installation guide
- `docs/FEDORA_DEPLOYMENT.md` - Fedora Server 43 specific guide
- `docs/DIVESHOP360_FIELD_MAPPING.md` - Data migration from DiveShop360
- `DEPLOYMENT_SUMMARY.md` - Complete deployment checklist

### Scripts
- `install.sh` - Automated installation (Fedora/Ubuntu)
- `deploy-to-test.sh` - Test environment deployment
- `scripts/migrate.php` - Run database migrations

### Migrations
- `database/migrations/035_add_additional_product_fields.sql` - **MUST RUN!**

## Testing Checklist

Quick tests before production:

```bash
# Test product creation
1. Go to Products > Add Product
2. Fill all fields including new ones (QR code, weight, color, etc.)
3. Save and verify

# Test customer photos
4. Go to Customers > Add Customer
5. Upload a photo
6. View customer detail page
7. Use customer in POS and verify photo shows

# Test POS
8. Go to Point of Sale
9. Check date/time updating
10. Search for customer
11. Verify photo and certifications display
12. Add product and complete sale
```

## Common Commands

### Development/Test
```bash
# Run migrations
php8.2 scripts/migrate.php

# Check PHP version
php --version

# Test database connection
mysql -u root -pFrogman09! nautilus -e "SHOW TABLES;"

# View logs
tail -f logs/app.log
```

### Production
```bash
# Restart services (Fedora)
sudo systemctl restart httpd php-fpm mariadb

# View logs
sudo tail -f /var/log/httpd/nautilus-error.log
sudo tail -f /var/www/nautilus/logs/app.log

# Backup database
sudo mysqldump -u nautilus -p nautilus > backup_$(date +%Y%m%d).sql

# Check service status
sudo systemctl status httpd mariadb php-fpm
```

## Troubleshooting

### "Add Product" button doesn't work
- Migration 035 may not be run yet
- Check browser console for JavaScript errors
- Verify route exists: `grep -r "products/create" routes/`

### Photos not uploading
- Check directory permissions: `ls -la public/uploads/`
- Verify `enctype="multipart/form-data"` in form
- Check PHP upload settings: `php -i | grep upload_max`

### Database connection failed
- Verify credentials in `.env`
- Check MariaDB running: `sudo systemctl status mariadb`
- On Fedora, check SELinux: `sudo getsebool httpd_can_network_connect_db`

### 403 Forbidden (Fedora)
- Check SELinux: `sudo tail /var/log/audit/audit.log`
- Fix contexts: `sudo restorecon -Rv /var/www/nautilus`
- Check permissions: `ls -la /var/www/nautilus`

## What's New (This Update)

### Product Fields Added
- Barcode, QR Code
- Weight (with unit: lb/kg/oz/g)
- Dimensions (text)
- Color, Material, Manufacturer
- Warranty Info
- Location in Store
- Supplier Info
- Expiration Date

### Enhancements
- Customer photo upload in create/edit forms
- Certification agency logos on POS
- Favicon support
- Fedora Server 43 deployment guide
- DiveShop360 migration mapping

### Fixed
- Product routes now use correct `/store/products` prefix
- All product links updated throughout application

## Need Help?

1. Check `INSTALLATION.md` for installation issues
2. See `docs/FEDORA_DEPLOYMENT.md` for Fedora-specific problems
3. Review `DEPLOYMENT_SUMMARY.md` for complete feature list
4. Check application logs in `logs/` directory
5. For data migration, see `docs/DIVESHOP360_FIELD_MAPPING.md`

## Ready to Deploy?

✅ All code updates complete
✅ Migration 035 created
✅ Documentation written
✅ Installation scripts ready
✅ Fedora guide prepared

**Next Steps:**
1. Test in current environment first
2. Verify all features working
3. Deploy to production Fedora server
4. Import data from DiveShop360
5. Train staff on new features

Good luck! 🤿
