# Nautilus Dive Shop Management System
## Installation Guide for Dive Shop Owners

**No computer expertise required!** This guide walks you through every step in plain English.

---

## 🎯 What You Need Before Starting

1. **A Web Server** - One of these options:
   - Shared hosting account (like Bluehost, SiteGround, HostGator)
   - Your own Linux server (VPS or dedicated)
   - Local computer running Apache/LAMP

2. **MySQL Database Access**
   - Most hosting providers give you this automatically
   - You'll create the database through cPanel or similar

3. **5-10 Minutes** - That's it!

---

## 📦 Installation Methods

Choose the method that fits your situation:

### Method 1: Shared Hosting (Easiest - Recommended for Most)
### Method 2: Your Own Linux Server
### Method 3: Local Development Computer

---

## Method 1: Shared Hosting Installation

**Perfect for:** Most dive shops using hosting services like Bluehost, SiteGround, etc.

### Step 1: Upload Files

1. Download the Nautilus files (you should have received a ZIP file)
2. Log into your hosting control panel (cPanel, Plesk, etc.)
3. Open **File Manager**
4. Navigate to your website folder (usually `public_html` or `www`)
5. Upload the ZIP file
6. Extract/unzip it
7. You should now see folders like: `app`, `config`, `database`, `public`, etc.

### Step 2: Point Your Domain to Public Folder

**Important:** Your website should point to the `public` folder, not the root.

**In cPanel:**
1. Go to **Domains** or **Addon Domains**
2. Find your domain settings
3. Change the **Document Root** to: `/public_html/nautilus/public` (adjust path as needed)
4. Save changes

**Ask your hosting provider** if you're not sure how to do this!

### Step 3: Create a Database

1. In cPanel, find **MySQL Databases**
2. Create a new database named: `nautilus`
3. Create a database user (pick a strong password!)
4. Add the user to the database with **ALL PRIVILEGES**
5. Write down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### Step 4: Run the Web Installer

1. Open your web browser
2. Visit: `https://yourdomain.com/install.php`
3. Follow the on-screen wizard - it guides you through everything!

**The installer will:**
- Check if your server is ready
- Try to fix common permission issues automatically
- Walk you through 6 simple steps
- Create your admin account
- Set up the database
- Get you ready to go!

### Step 5: You're Done!

Delete the installer for security:
1. Go back to File Manager
2. Navigate to the `public` folder
3. Delete `install.php`

---

## Method 2: Your Own Linux Server

**Perfect for:** If you have a VPS or dedicated server

### Prerequisites

You need:
- Ubuntu 20.04+ or Fedora 38+
- Root/sudo access
- Basic terminal knowledge (we'll guide you!)

### Step 1: Install Required Software

**For Ubuntu/Debian:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache, PHP, MySQL
sudo apt install apache2 php php-mysql php-mbstring php-xml php-curl php-gd php-zip mysql-server composer -y

# Enable Apache modules
sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

**For Fedora/RHEL:**
```bash
# Update system
sudo dnf update -y

# Install Apache, PHP, MySQL
sudo dnf install httpd php php-mysqlnd php-mbstring php-xml php-gd php-pecl-zip mariadb-server composer -y

# Start services
sudo systemctl enable --now httpd mariadb
sudo systemctl restart httpd
```

### Step 2: Upload Nautilus Files

**Option A - Using SCP (from your local computer):**
```bash
scp -r nautilus/ username@yourserver.com:/var/www/html/
```

**Option B - Using Git (if you have repository access):**
```bash
cd /var/www/html
sudo git clone https://your-repo-url/nautilus.git
```

### Step 3: Install PHP Dependencies

```bash
cd /var/www/html/nautilus
sudo composer install --no-dev --optimize-autoloader
```

### Step 4: Set Up Database

```bash
# Log into MySQL
sudo mysql

# Create database and user
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**Write down your database credentials!**

### Step 5: Configure Web Server

**Option A - Use the automated script:**
```bash
cd /var/www/html/nautilus
sudo ./deploy-smart-installer.sh
```

**Option B - Manual configuration:**

**For Apache on Ubuntu:**
```bash
sudo nano /etc/apache2/sites-available/nautilus.conf
```

**For Apache on Fedora:**
```bash
sudo nano /etc/httpd/conf.d/nautilus.conf
```

**Paste this configuration:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus_error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus_access.log combined
</VirtualHost>
```

**Enable the site (Ubuntu only):**
```bash
sudo a2ensite nautilus.conf
sudo systemctl reload apache2
```

**For Fedora:**
```bash
sudo systemctl restart httpd
```

### Step 6: Set Permissions

```bash
# Set ownership
sudo chown -R apache:apache /var/www/html/nautilus  # Fedora
# OR
sudo chown -R www-data:www-data /var/www/html/nautilus  # Ubuntu

# Set permissions
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

### Step 7: Configure Firewall (if needed)

**Ubuntu:**
```bash
sudo ufw allow 'Apache Full'
```

**Fedora:**
```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### Step 8: Run the Web Installer

1. Open browser: `http://yourdomain.com/install.php`
2. Follow the wizard!

### Step 9: Set Up HTTPS (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y  # Ubuntu
# OR
sudo dnf install certbot python3-certbot-apache -y  # Fedora

# Get SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically!
```

### Step 10: Secure the Installation

```bash
# Delete installer
sudo rm /var/www/html/nautilus/public/install.php

# Secure .env file
sudo chmod 600 /var/www/html/nautilus/.env
```

---

## Method 3: Local Development (Your Computer)

**Perfect for:** Testing before going live

### Windows (Using XAMPP)

1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL from XAMPP Control Panel
3. Copy nautilus folder to: `C:\xampp\htdocs\`
4. Open browser: `http://localhost/nautilus/public/install.php`
5. Follow the wizard!

### macOS (Using MAMP)

1. Download and install [MAMP](https://www.mamp.info/)
2. Start servers
3. Copy nautilus folder to: `/Applications/MAMP/htdocs/`
4. Open browser: `http://localhost:8888/nautilus/public/install.php`
5. Follow the wizard!

### Linux (Manual Setup)

Same as "Method 2" above, but use `/home/yourusername/public_html/` instead of `/var/www/html/`

---

## 🚀 Web Installer Guide (All Methods)

Once you visit `install.php`, you'll see 6 simple steps:

### Step 1: Requirements Check
- Installer automatically checks your server
- **Auto-fixes permission issues** when possible
- Shows a big "Try Auto-Fix" button if needed
- Green checkmarks = you're good to go!

**If you see red X's:**
- Click the "✨ Try Auto-Fix Permissions" button first
- If that doesn't work, copy the command shown and send it to your hosting provider
- They can run it for you in seconds!

### Step 2: Application Settings
- **Application Name**: What you want to call the system (e.g., "Nautilus Dive Shop")
- **Company Name**: Your dive shop's business name (appears on invoices, certificates)
- **Timezone**: Select your location for correct scheduling

### Step 3: Database Configuration
- **Host**: Usually `localhost` (hosting provider will tell you if different)
- **Port**: Usually `3306`
- **Database Name**: The database you created (e.g., `nautilus`)
- **Username**: Your database username
- **Password**: Your database password

**Tip:** Have this info from your hosting provider handy!

### Step 4: Database Setup
- Installer automatically creates all tables
- Takes about 30 seconds
- Just watch the spinner - nothing to do here!

### Step 5: Admin Account
- **First & Last Name**: Your name
- **Email**: Your login email (you'll use this to sign in)
- **Password**: Pick a strong password (minimum 8 characters)

### Step 6: Complete!
- You're done! 🎉
- Click "Go to Dashboard" to start using Nautilus

---

## 🔧 Troubleshooting

### "Permission Denied" Errors

**Solution 1 - Try Auto-Fix Button:**
The installer has a "Try Auto-Fix Permissions" button. Click it!

**Solution 2 - Manual Fix (if you have server access):**
```bash
cd /path/to/nautilus
chmod -R 775 storage
chmod 775 .
chown -R [web-user]:[web-user] .
```
Replace `[web-user]` with `apache` (Fedora) or `www-data` (Ubuntu)

**Solution 3 - Shared Hosting:**
Contact your hosting support and say:
> "I need write permissions on my nautilus/storage directory and the root directory to create a .env file"

### "PHP Extension Missing"

The installer will tell you exactly which extensions are missing.

**Shared Hosting:**
- Log into cPanel
- Find "Select PHP Version" or "PHP Extensions"
- Enable the required extensions (pdo_mysql, gd, zip)

**Your Own Server:**
The installer shows you the exact command to run. For example:
```bash
sudo apt install php-mysql php-gd php-zip  # Ubuntu
sudo dnf install php-mysqlnd php-gd php-pecl-zip  # Fedora
sudo systemctl restart apache2  # or httpd
```

### "Cannot Connect to Database"

**Check these:**
1. Is MySQL running? `sudo systemctl status mysql` or `mariadb`
2. Did you create the database?
3. Are the username and password correct?
4. Is the host correct? (try `localhost` or `127.0.0.1`)

**For shared hosting:** Your hosting control panel shows your database details

### "Vendor Directory Missing"

You need to install PHP dependencies:
```bash
cd /path/to/nautilus
composer install --no-dev
```

If you don't have Composer, install it:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Installer Won't Load

**Check:**
1. Did you point your domain to the `public` folder?
2. Is Apache/web server running?
3. Do you have PHP installed? Check: `php -v`

---

## 📞 Getting Help

### For Shared Hosting Users

**Contact your hosting provider** and share this guide. They can:
- Set up your document root correctly
- Enable PHP extensions
- Fix permission issues
- Create database credentials

Most hosting providers are happy to help with these common tasks!

### For Server Owners

**Check the logs:**
```bash
# Apache error log
sudo tail -f /var/log/apache2/error.log  # Ubuntu
sudo tail -f /var/log/httpd/error_log    # Fedora

# PHP errors
sudo tail -f /var/log/php*.log
```

### Documentation

- **User Guide**: See `docs/` folder after installation
- **Support Email**: [Your support email here]
- **Community Forum**: [Your forum URL here]

---

## ✅ Post-Installation Checklist

After successful installation:

- [ ] Delete `/public/install.php` for security
- [ ] Log in with your admin credentials
- [ ] Configure your dive shop settings
- [ ] Add staff members and instructors
- [ ] Set up your courses and pricing
- [ ] Configure email settings (for customer notifications)
- [ ] Upload your logo
- [ ] Test a sample booking
- [ ] Create a backup schedule

---

## 🎓 First Steps After Installation

1. **Complete Your Profile**
   - Add your contact information
   - Upload your dive shop logo
   - Set business hours

2. **Add Your Team**
   - Create accounts for instructors
   - Set up roles and permissions
   - Assign certifications

3. **Configure Courses**
   - Add your course catalog
   - Set pricing
   - Configure prerequisites

4. **Set Up Equipment**
   - Add inventory items
   - Configure rental pricing
   - Set availability

5. **Customize Settings**
   - Payment methods
   - Email templates
   - Booking policies
   - Certification templates

---

## 🔐 Security Best Practices

1. **Use HTTPS** - Always! Get a free SSL certificate with Let's Encrypt
2. **Strong Passwords** - Use a password manager
3. **Regular Backups** - Set up automated database backups
4. **Keep Updated** - Install updates when available
5. **Delete Install File** - Remove `install.php` after setup
6. **Secure .env** - Never commit to version control
7. **Staff Accounts** - Use least-privilege principle

---

## 📱 Browser Compatibility

Nautilus works great on:
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

---

## 🆘 Common Questions

**Q: Do I need to know programming?**
A: No! The web installer handles everything. If you can create a WordPress site, you can install Nautilus.

**Q: What if I get stuck?**
A: The installer has a "Try Auto-Fix" button that solves most issues automatically. Otherwise, copy the command shown and send it to your hosting provider.

**Q: Can I install this on shared hosting?**
A: Yes! Nautilus works great on shared hosting plans like Bluehost, SiteGround, etc.

**Q: How much does hosting cost?**
A: Shared hosting typically costs $5-20/month. Requirements: PHP 8.0+, MySQL 5.7+, 512MB RAM minimum.

**Q: Can I try it locally first?**
A: Yes! Use XAMPP (Windows), MAMP (Mac), or LAMP (Linux) to test on your computer before going live.

**Q: What if my hosting provider doesn't have PHP 8.0?**
A: Most modern hosting providers have it. If yours doesn't, either upgrade your plan or switch providers (we recommend SiteGround or DigitalOcean).

---

## 🎉 You're Ready!

That's it! The installer makes everything simple. Most dive shop owners complete installation in under 10 minutes.

**Remember:** The web installer does the heavy lifting. Just visit `install.php` and follow the friendly step-by-step wizard!

---

**Last Updated:** December 2024
**Version:** 1.0.0

For questions or support, contact your system administrator or hosting provider.
