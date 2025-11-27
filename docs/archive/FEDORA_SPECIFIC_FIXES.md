# Fedora/RHEL Specific Fixes

**Date:** 2025-01-22
**Platform:** Fedora 43 / RHEL-based systems

---

## Issues Fixed

### ❌ Problem 1: a2enmod doesn't exist on Fedora

**Wrong Command (Debian/Ubuntu only):**
```bash
sudo a2enmod rewrite  # DOESN'T WORK ON FEDORA!
```

**Correct for Fedora/RHEL:**
```bash
# mod_rewrite is built into httpd on Fedora
# Just verify it's loaded:
grep -i "rewrite_module" /etc/httpd/conf.modules.d/*.conf

# Should see:
# LoadModule rewrite_module modules/mod_rewrite.so

# If not present, reinstall httpd:
sudo dnf reinstall httpd
sudo systemctl restart httpd
```

### ❌ Problem 2: setenforce 0 is invalid

**Wrong Command:**
```bash
sudo setenforce 0  # ERROR: 0 is not valid!
```

**Correct for Fedora:**
```bash
# Temporary (until reboot):
sudo setenforce Permissive

# Permanent:
sudo sed -i 's/SELINUX=enforcing/SELINUX=permissive/' /etc/selinux/config
# Then reboot or run: sudo setenforce Permissive
```

**Note:** For production, keep SELinux Enforcing and configure proper contexts:
```bash
# Set httpd contexts for Nautilus
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/public/uploads(/.*)?"
sudo restorecon -R /var/www/html/nautilus/
```

### ✅ Problem 3: Firewall Check Inaccurate

**Issue:** Installer reported "HTTP/HTTPS blocked" even though ports were open

**Solution:** Updated check logic:
- If user can access installer, firewall is OK for their connection
- Shows informational status only
- Provides command for external access if needed

**Check firewall manually:**
```bash
# Check firewalld status
sudo firewall-cmd --state

# List allowed services
sudo firewall-cmd --list-services

# List open ports
sudo firewall-cmd --list-ports

# If http/https not in list but you want external access:
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

---

## Complete Fedora Setup Commands

### Initial Setup
```bash
# 1. Install required packages
sudo dnf install httpd php php-mysqlnd php-pdo php-json php-mbstring php-xml mariadb-server composer

# 2. Start services
sudo systemctl start httpd
sudo systemctl start mariadb
sudo systemctl enable httpd
sudo systemctl enable mariadb

# 3. Configure MariaDB
sudo mysql_secure_installation
```

### Deploy Nautilus
```bash
# 1. Copy files
sudo rm -rf /var/www/html/nautilus
sudo cp -R ~/development/nautilus/ /var/www/html/

# 2. Set ownership
sudo chown -R apache:apache /var/www/html/nautilus/

# 3. Set permissions
sudo chmod -R 755 /var/www/html/nautilus/
sudo chmod -R 775 /var/www/html/nautilus/storage/
sudo chmod -R 775 /var/www/html/nautilus/public/uploads/
```

### SELinux Configuration (Choose one)

#### Option A: Permissive (Development)
```bash
sudo setenforce Permissive
sudo sed -i 's/SELINUX=enforcing/SELINUX=permissive/' /etc/selinux/config
```

#### Option B: Enforcing with Contexts (Production)
```bash
# Set proper SELinux contexts
sudo semanage fcontext -a -t httpd_sys_content_t "/var/www/html/nautilus(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/public/uploads(/.*)?"
sudo restorecon -R /var/www/html/nautilus/

# Allow httpd to connect to network (for external APIs)
sudo setsebool -P httpd_can_network_connect on

# Allow httpd to connect to database
sudo setsebool -P httpd_can_network_connect_db on
```

### Firewall Configuration
```bash
# Open web ports (if needed for external access)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload

# Verify
sudo firewall-cmd --list-services
```

### Apache Configuration

**Verify mod_rewrite is loaded:**
```bash
grep -i "LoadModule rewrite_module" /etc/httpd/conf.modules.d/*.conf
```

**Check AllowOverride for .htaccess:**
```bash
# Edit /etc/httpd/conf/httpd.conf
sudo vi /etc/httpd/conf/httpd.conf

# Find DocumentRoot section and ensure:
<Directory "/var/www/html">
    AllowOverride All    # <-- MUST be "All" not "None"
    Require all granted
</Directory>

# Restart Apache
sudo systemctl restart httpd
```

---

## Verification Commands

### Check System Status
```bash
# Apache status
sudo systemctl status httpd

# MariaDB status
sudo systemctl status mariadb

# SELinux status
getenforce

# Firewall status
sudo firewall-cmd --state
sudo firewall-cmd --list-all
```

### Check File Permissions
```bash
# Check ownership
ls -la /var/www/html/nautilus/ | head -20

# Check storage permissions
ls -la /var/www/html/nautilus/storage/

# Check uploads permissions
ls -la /var/www/html/nautilus/public/uploads/
```

### Check Apache Modules
```bash
# Check if mod_rewrite is loaded
httpd -M | grep rewrite

# Should output:
# rewrite_module (shared)
```

### Check PHP Configuration
```bash
# Check PHP version
php -v

# Check loaded extensions
php -m | grep -E "pdo|mysqli|json|mbstring"

# Check php.ini location
php --ini
```

---

## Common Errors & Solutions

### Error: "Permission denied" accessing files

**Cause:** SELinux is blocking Apache
**Solution:**
```bash
# Check SELinux denials
sudo ausearch -m avc -ts recent

# Fix with proper contexts (see SELinux section above)
```

### Error: "Cannot write to storage directory"

**Cause:** Incorrect permissions or SELinux
**Solution:**
```bash
# Fix permissions
sudo chmod -R 775 /var/www/html/nautilus/storage/
sudo chown -R apache:apache /var/www/html/nautilus/storage/

# Fix SELinux context
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo restorecon -R /var/www/html/nautilus/storage/
```

### Error: ".htaccess not working" / "404 on all pages"

**Cause:** AllowOverride not set or mod_rewrite not loaded
**Solution:**
```bash
# Check mod_rewrite
httpd -M | grep rewrite

# Check AllowOverride in httpd.conf
grep -A 5 "DocumentRoot" /etc/httpd/conf/httpd.conf

# Should see: AllowOverride All

# If not, edit and add it:
sudo vi /etc/httpd/conf/httpd.conf
sudo systemctl restart httpd
```

---

## Installer Updates Applied

The installer now correctly:

1. **Detects Fedora vs Debian/Ubuntu**
   - Shows correct commands for each platform
   - No more "a2enmod" on Fedora!

2. **SELinux Commands Corrected**
   - `setenforce Permissive` instead of `setenforce 0`
   - Shows both temporary and permanent options

3. **Firewall Check Improved**
   - Recognizes user can access the page
   - Shows informational status
   - Provides external access commands if needed

4. **Better Help Text**
   - "If you can access this installer, it's working"
   - Platform-specific guidance
   - Production vs development advice

---

## Quick Start (Fedora Development)

```bash
# Full setup in one go:
sudo dnf install -y httpd php php-mysqlnd php-pdo php-json php-mbstring mariadb-server composer
sudo systemctl start httpd mariadb
sudo systemctl enable httpd mariadb
sudo rm -rf /var/www/html/nautilus
sudo cp -R ~/development/nautilus/ /var/www/html/
sudo chown -R apache:apache /var/www/html/nautilus/
sudo chmod -R 755 /var/www/html/nautilus/
sudo chmod -R 775 /var/www/html/nautilus/storage/ /var/www/html/nautilus/public/uploads/
sudo setenforce Permissive
sudo sed -i 's/SELINUX=enforcing/SELINUX=permissive/' /etc/selinux/config
echo "AllowOverride All" | sudo tee -a /etc/httpd/conf/httpd.conf
sudo systemctl restart httpd
mysql -u root -p'Frogman09!' -e "DROP DATABASE IF EXISTS nautilus_dev; CREATE DATABASE nautilus_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo rm -f /var/www/html/nautilus/.installed

# Then visit: https://nautilus.local/install/
```

---

**Platform:** Fedora 43 / RHEL 9
**Apache:** httpd (not apache2)
**Service Manager:** systemctl
**Package Manager:** dnf
**SELinux:** Default Enforcing
**Firewall:** firewalld

---

**All fixes applied to:** `/public/install/check.php`
**Status:** ✅ Ready for Fedora/RHEL systems
