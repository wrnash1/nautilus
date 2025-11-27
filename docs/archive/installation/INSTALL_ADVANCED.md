# Nautilus Dive Shop - Installation Guide

---

## 🎯 Choose Your Installation Method

### For Dive Shop Owners (No Technical Knowledge Required)
**→ Use the [Simple Installation Guide](INSTALL_SIMPLE.md)** ← **RECOMMENDED FOR MOST USERS**

This guide walks you through a **browser-based installation** that requires no command-line experience. Perfect for shared hosting environments.

### For System Administrators & VPS Users
**→ Continue reading this guide**

This guide covers command-line installation, VPS setup, and advanced configuration options.

---

## Quick Start (Web-Based Installer - Recommended)

### 1. Upload Files

Upload all Nautilus files to your web server's public directory:
- **Shared Hosting**: Usually `/public_html/nautilus`
- **VPS/Dedicated**: Usually `/var/www/html/nautilus`

**Upload Methods:**
- cPanel File Manager (most common)
- FTP client (FileZilla, Cyberduck, etc.)
- Git clone (advanced users)

### 2. Open the Web Installer

**In your browser, navigate to:**
```
http://yourwebsite.com/nautilus/install.php
```

**The installer will guide you through 4 steps:**
1. ✅ System Requirements Check (auto-fixes common issues)
2. ✅ Database Setup (creates tables automatically)
3. ✅ Admin Account Creation (your login credentials)
4. ✅ Installation Complete!

**That's it!** The web installer handles everything automatically:
- Creates required directories
- Sets correct permissions (with SELinux support for Fedora/RHEL)
- Checks PHP version and extensions
- Tests database connection
- Creates all database tables
- Generates secure encryption keys
- Creates your admin account

**See [INSTALL_SIMPLE.md](INSTALL_SIMPLE.md) for detailed screenshots and troubleshooting.**

---

## Advanced Installation (Command-Line - VPS/Dedicated Servers Only)

**⚠️ Only use this method if:**
- You have SSH/root access to your server
- You want to automate PHP extension installation
- You need to configure Apache virtual hosts programmatically
- You're comfortable with the command line

**For shared hosting users: Use the web installer above instead!**

### Advanced: Automated Setup Script

If you have SSH access and want to automate server configuration:

```bash
cd /var/www/html/nautilus  # or your installation path
sudo bash scripts/setup.sh
```

**The setup script will:**
- Create required directories
- Set correct file permissions
- Check PHP version and extensions
- **Offer to auto-install missing PHP extensions** (requires sudo)
- **Configure Apache virtual host automatically**
- **Add nautilus.local to /etc/hosts**
- Test database connection
- Install Composer dependencies

**Then run the web installer** at `http://nautilus.local/install.php` to complete setup.

### Advanced: Demo Data Installation

After completing the web installer, optionally install sample data:

```bash
bash scripts/setup-database.sh
```

This will prompt you to install:
- 15 sample products (dive equipment, courses, services)
- 5 sample customers with certifications
- 5 sample sales orders
- Demo admin account (email: admin@demo.com, password: demo123)

---

## Manual Installation (Expert Users Only)

**⚠️ Most users should use the web installer instead!**

### Prerequisites

- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.1 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Composer**: Latest version

### Required PHP Extensions

- pdo
- pdo_mysql
- mbstring
- json
- openssl
- curl
- fileinfo
- gd
- zip

### Step-by-Step Installation

#### 1. Clone/Upload Repository

```bash
# Via Git
git clone https://github.com/yourusername/nautilus.git
cd nautilus

# Or upload via FTP/SFTP
```

#### 2. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

#### 3. Configure Environment

```bash
cp .env.example .env
nano .env
```

Update database credentials and other settings in `.env`.

#### 4. Set Permissions

**Ubuntu/Debian/Pop!_OS:**
```bash
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage
```

**Fedora/RHEL/CentOS:**
```bash
sudo chown -R apache:apache .
sudo chmod -R 755 .
sudo chmod -R 775 storage
```

**SELinux (Fedora/RHEL):**
```bash
sudo chcon -R -t httpd_sys_rw_content_t storage
```

#### 5. Create Database

```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 6. Configure Web Server

**Apache (.htaccess already included):**

Ensure `mod_rewrite` is enabled:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Add to your Apache configuration or virtual host:
```apache
<Directory /var/www/html/nautilus/public>
    AllowOverride All
    Require all granted
</Directory>

<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
```

**Nginx:**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/nautilus/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

#### 7. Run Web Installer

Visit `http://nautilus.local/install.php` (or your configured domain) and follow the on-screen instructions.

**Note**: If you ran the automated setup script, the virtual host should already be configured. Otherwise, manually configure Apache as shown above.

---

## Platform-Specific Instructions

### Ubuntu / Pop!_OS / Debian

#### Install Required Packages

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php8.1 php8.1-cli php8.1-common \
    php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-gd \
    php8.1-zip php8.1-bcmath php8.1-json php8.1-intl composer
```

#### Enable Apache Modules

```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

### Fedora / RHEL / CentOS

#### Install Required Packages

```bash
sudo dnf install -y httpd mariadb-server php php-cli php-common \
    php-mysqlnd php-mbstring php-xml php-curl php-gd php-zip \
    php-bcmath php-json php-intl composer
```

**Note**: The setup script can now auto-install missing PHP extensions when run with sudo.

#### Start Services

```bash
sudo systemctl enable httpd mariadb
sudo systemctl start httpd mariadb
```

#### Configure Firewall

```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### macOS (Development)

#### Install via Homebrew

```bash
brew install php@8.1 mysql composer
brew services start mysql
brew services start php@8.1
```

---

## Troubleshooting

### HTTP 500 Error

1. Check Apache/Nginx error logs:
   ```bash
   # Ubuntu/Debian
   sudo tail -f /var/log/apache2/error.log

   # Fedora/RHEL
   sudo tail -f /var/log/httpd/error_log
   ```

2. Verify PHP errors are displayed:
   ```bash
   # In .env file
   APP_DEBUG=true
   ```

3. Check file permissions:
   ```bash
   ls -la storage
   # Should be writable by web server user
   ```

### Database Connection Failed

1. Verify credentials in `.env`
2. Test MySQL connection:
   ```bash
   mysql -u your_user -p -h localhost
   ```
3. Check MySQL is running:
   ```bash
   sudo systemctl status mysql  # or mariadb
   ```

### Composer Dependencies Missing

```bash
cd /path/to/nautilus
composer install --no-dev
```

### Permission Denied Errors

```bash
# Ubuntu/Debian
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage

# Fedora/RHEL
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

### Can't Access Installation Page

1. Check virtual host configuration
2. Verify DocumentRoot points to `/path/to/nautilus/public`
3. Ensure `.htaccess` exists in `public/` directory
4. Verify `mod_rewrite` is enabled (Apache)

---

## Post-Installation

### Security Checklist

- [ ] Delete or rename `install.php` after installation
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Ensure `.env` is not publicly accessible (should be 640)
- [ ] Configure SSL/HTTPS (update virtual host for production)
- [ ] Set up regular database backups
- [ ] Review file permissions
- [ ] Remove demo data and demo admin account in production

### Recommended Configuration

```bash
# Secure .env file
chmod 640 .env
chown www-data:www-data .env  # or apache:apache

# Remove installer (after successful installation)
rm public/install.php

# Organize documentation (optional)
bash scripts/organize-docs.sh
```

---

## Getting Help

- **Documentation**: See `/docs` folder
- **GitHub Issues**: https://github.com/yourusername/nautilus/issues
- **Installation Scripts**: All setup scripts are in `/scripts` directory
  - `scripts/setup.sh` - Main setup script
  - `scripts/setup-database.sh` - Database migration and seeding
  - `scripts/organize-docs.sh` - Move markdown files to docs/
  - `scripts/fix-permissions.sh` - Fix file permissions

---

## License

Copyright © 2025 Nautilus Dive Shop Software. All rights reserved.
