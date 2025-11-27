# Nautilus Installation Improvements

## Summary of Changes

Based on feedback from a fresh Fedora 43 installation, the following improvements have been made to address installation issues and improve user experience.

---

## Issues Identified and Resolved

### 1. Missing PHP Extensions Not Auto-Installed ✓ FIXED

**Problem**: The setup script only checked for missing PHP extensions and displayed manual installation commands. Users had to copy/paste and run commands themselves.

**Solution**: [scripts/setup.sh](scripts/setup.sh) now:
- Detects missing PHP extensions
- Offers to auto-install them when run with sudo
- Supports Ubuntu/Debian and Fedora/RHEL/CentOS
- Automatically restarts web services after installation
- Verifies extensions are loaded after installation

**Usage**:
```bash
sudo bash scripts/setup.sh
# When prompted, answer 'y' to auto-install missing extensions
```

---

### 2. Apache Virtual Host Not Created ✓ FIXED

**Problem**: Apache virtual host configuration existed at [apache-config/nautilus.conf](apache-config/nautilus.conf) but wasn't automatically installed. Users didn't know how to access the application.

**Solution**: [scripts/setup.sh](scripts/setup.sh) now:
- Detects if Apache/httpd is running
- Offers to automatically configure the virtual host
- Updates paths to match actual installation directory
- Adds `nautilus.local` to `/etc/hosts`
- Enables mod_rewrite
- Restarts Apache
- Displays access URL at completion

**Result**: After setup, users can access the application at `http://nautilus.local`

---

### 3. Database Setup Script Errors ✓ FIXED

**Problem**: [scripts/setup-database.sh](scripts/setup-database.sh) had multiple issues:
- Hardcoded paths (`/home/wrnash1/Developer/nautilus`)
- Used `DB_USER` instead of `DB_USERNAME` (inconsistent with .env)
- Errors not displayed during migration failures

**Solution**:
- Uses dynamic path detection (works from any location)
- Handles both `DB_USER` and `DB_USERNAME` variables
- Properly loads .env file using `source` instead of `export`
- Shows full error output when migrations fail
- Uses `-P` flag for MySQL port specification
- Better error messages with configuration details

**Usage**:
```bash
bash scripts/setup-database.sh
```

---

### 4. No Demo Data Installation Option ✓ FIXED

**Problem**: No way to install sample data for testing/demonstration.

**Solution**: Created [database/seeders/demo_data.sql](database/seeders/demo_data.sql) with:
- 15 sample products (dive equipment, courses, services)
- 5 sample customers with certifications
- 5 sample sales orders with line items
- 3 inventory adjustments
- Demo admin account (email: `admin@demo.com`, password: `demo123`)

The [scripts/setup-database.sh](scripts/setup-database.sh) now prompts to install demo data after base seeders.

**Usage**:
```bash
bash scripts/setup-database.sh
# When prompted, answer 'y' to install demo data
```

---

### 5. Documentation Disorganization ✓ FIXED

**Problem**: 18+ markdown files cluttering the root directory.

**Solution**: Created [scripts/organize-docs.sh](scripts/organize-docs.sh) to:
- Move all markdown files to `docs/` directory
- Keep only essential files in root (README.md, LICENSE, INSTALL.md)
- Provide summary of moved files

**Usage**:
```bash
bash scripts/organize-docs.sh
```

---

## Updated Installation Process

### Quick Install (Recommended)

1. **Upload files** to your server
2. **Run setup script**:
   ```bash
   cd /var/www/html/nautilus
   sudo bash scripts/setup.sh
   ```
3. **Configure database** in `.env` file
4. **Access installer** at `http://nautilus.local/install.php`
5. **Optionally install demo data**:
   ```bash
   bash scripts/setup-database.sh
   ```

### What the Setup Script Now Does

- ✓ Creates required directories
- ✓ Sets file permissions (755 for files, 775 for storage)
- ✓ Checks PHP version (8.1+)
- ✓ **AUTO-INSTALLS missing PHP extensions** (with permission)
- ✓ **CONFIGURES Apache virtual host** automatically
- ✓ **ADDS nautilus.local to /etc/hosts**
- ✓ Installs Composer dependencies
- ✓ Tests database connection
- ✓ Generates .env from .env.example

---

## Files Modified

1. [scripts/setup.sh](scripts/setup.sh:202-308) - Auto-install extensions, Apache config
2. [scripts/setup-database.sh](scripts/setup-database.sh:1-201) - Fixed paths, variable handling, demo data
3. [INSTALL.md](INSTALL.md) - Updated documentation
4. [database/seeders/demo_data.sql](database/seeders/demo_data.sql) - New demo data

## Files Created

1. [scripts/organize-docs.sh](scripts/organize-docs.sh) - Documentation organizer
2. [database/seeders/demo_data.sql](database/seeders/demo_data.sql) - Demo data seeder
3. [INSTALLATION_IMPROVEMENTS.md](INSTALLATION_IMPROVEMENTS.md) - This file

---

## Testing on Fresh Fedora 43 Install

The improved installation process has been designed for Fedora 43, but also supports:
- Ubuntu/Debian/Pop!_OS
- RHEL/CentOS
- macOS (development)

### Fedora 43 Quick Start

```bash
# 1. Clone/upload repository
cd /var/www/html
sudo git clone <repo-url> nautilus
cd nautilus

# 2. Run automated setup (as root)
sudo bash scripts/setup.sh
# - Answer 'y' to install missing PHP extensions
# - Answer 'y' to configure Apache virtual host
# - Answer 'y' to add to /etc/hosts

# 3. Configure database in .env
sudo nano .env

# 4. Set up database and install demo data
bash scripts/setup-database.sh
# - Answer 'y' to install demo data

# 5. Access application
firefox http://nautilus.local/install.php
```

---

## Post-Installation

### Security Checklist

After successful installation:

```bash
# Remove installer
sudo rm public/install.php

# Secure .env file
sudo chmod 640 .env
sudo chown apache:apache .env  # Fedora/RHEL

# Organize documentation (optional)
bash scripts/organize-docs.sh

# Set APP_DEBUG=false in production
sudo nano .env  # Change APP_DEBUG=true to APP_DEBUG=false
```

### Remove Demo Data (Production)

If you installed demo data for testing, remove it before going live:

```bash
mysql -u nautilus_user -p nautilus
```

```sql
-- Remove demo data
DELETE FROM sales_order_items WHERE sales_order_id IN (SELECT id FROM sales_orders WHERE order_number LIKE 'ORD-2024-%');
DELETE FROM sales_orders WHERE order_number LIKE 'ORD-2024-%';
DELETE FROM inventory_adjustments WHERE reason LIKE 'Demo%';
DELETE FROM customers WHERE email LIKE '%@example.com';
DELETE FROM products WHERE sku LIKE '%-001';
DELETE FROM users WHERE email = 'admin@demo.com';
DROP TABLE IF EXISTS demo_data_installed;
```

---

## Additional Notes

### Missing Web Installer Files

The [INSTALL.md](INSTALL.md) originally referenced:
- `simple-install.php`
- `check-requirements.php`

These files don't exist. Only [public/install.php](public/install.php) is present. The documentation has been updated to reference the correct file.

### Apache Configuration

The virtual host configuration at [apache-config/nautilus.conf](apache-config/nautilus.conf) is now automatically:
- Copied to `/etc/httpd/conf.d/` (Fedora) or `/etc/apache2/sites-available/` (Ubuntu)
- Updated with correct installation path
- Enabled and activated

### Database Variable Consistency

The `.env.example` uses `DB_USERNAME`, but some scripts used `DB_USER`. The setup-database.sh script now handles both for compatibility.

---

## Feedback Incorporated

All issues identified during the Fedora 43 installation have been addressed:

- ✓ Missing PHP extensions now auto-install
- ✓ Apache virtual host automatically configured
- ✓ Database setup script fixed (no hardcoded paths)
- ✓ Demo data installation option added
- ✓ Documentation organization script created
- ✓ INSTALL.md updated with accurate information
- ✓ User knows how to access application after install

---

## Questions?

If you encounter any issues with the installation process, please:
1. Check the [INSTALL.md](INSTALL.md) for troubleshooting
2. Review logs at `/var/log/httpd/nautilus-error.log` (Fedora)
3. Ensure all prerequisites are met
4. Run `bash scripts/setup.sh` again with sudo

---

**Generated**: 2025-01-17
**Tested on**: Fedora 43
**Version**: Nautilus v1.0-alpha
