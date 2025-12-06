# 📋 Nautilus Pre-Installation Checklist
**Complete this checklist BEFORE starting installation**

---

## ✅ Hosting Account Ready

Before you begin, make sure you have:

- [ ] **Web hosting account** with cPanel, Plesk, or similar control panel
- [ ] **Login credentials** for your hosting account
- [ ] **Domain name** pointed to your hosting (DNS configured)
- [ ] **SSL certificate** installed (HTTPS) - recommended but not required

---

## ✅ Server Requirements Met

Check with your hosting provider that you have:

### PHP Version
- [ ] PHP 8.0 or newer (PHP 8.2+ recommended)
- [ ] To check: Log into cPanel → PHP Selector or PHP Version

### PHP Extensions (Required)
- [ ] PDO
- [ ] PDO_MySQL (or MySQLi)
- [ ] MBString
- [ ] JSON
- [ ] cURL
- [ ] OpenSSL
- [ ] GD (for image processing)
- [ ] ZIP
- [ ] XML

**Shared Hosting?** Most providers have these installed by default. If not, contact support.

### Database
- [ ] MySQL 5.7+ or MySQL 8.0+ (or MariaDB 10.3+)
- [ ] Ability to create databases in cPanel
- [ ] Database access credentials

### Composer (PHP Dependency Manager)
- [ ] Composer installed on server
- [ ] OR ask hosting to run `composer install` for you

---

## ✅ Information You'll Need During Installation

Gather this information NOW before starting:

### 1. Company Information
- [ ] **Dive shop name:** ________________________________
- [ ] **Company legal name:** ________________________________
- [ ] **Timezone:** ________________________________
  - Example: America/New_York, America/Los_Angeles, Europe/London

### 2. Database Details (Create in cPanel first!)
- [ ] **Database name:** ________________________________
- [ ] **Database username:** ________________________________
- [ ] **Database password:** ________________________________
- [ ] **Database host:** Usually `localhost` or `127.0.0.1`
- [ ] **Database port:** Usually `3306`

### 3. Admin Account (Your First User)
- [ ] **First name:** ________________________________
- [ ] **Last name:** ________________________________
- [ ] **Email address:** ________________________________
- [ ] **Strong password:** ________________________________

---

## ✅ Preparation Steps

Complete these BEFORE running the installer:

### Step 1: Create MySQL Database
1. [ ] Log into cPanel
2. [ ] Go to **MySQL Databases**
3. [ ] Create new database: `nautilus`
4. [ ] Create database user with strong password
5. [ ] Add user to database with **ALL PRIVILEGES**
6. [ ] Write down all details above

### Step 2: Upload Files
1. [ ] Download `nautilus.zip` from GitHub
2. [ ] Upload to your hosting account
3. [ ] Extract/unzip files
4. [ ] Verify folders exist: `app`, `config`, `database`, `public`, etc.

### Step 3: Install Composer Dependencies (If Needed)
- [ ] Check if `vendor` folder exists
- [ ] If NOT, ask hosting support to run: `composer install --no-dev`
- [ ] OR run it yourself if you have SSH access

### Step 4: Point Domain to Public Folder
1. [ ] In cPanel, go to **Domains**
2. [ ] Set **Document Root** to: `public_html/nautilus/public`
3. [ ] Save changes

---

## ✅ Security Preparation

- [ ] **Strong passwords ready** for database and admin account
  - At least 12 characters
  - Mix of letters, numbers, symbols
  - Not used anywhere else

- [ ] **Backup plan** in place
  - Know how to backup database (cPanel → phpMyAdmin)
  - Enable automatic backups if available

---

## ✅ Optional (Configure Later)

You DON'T need these to install, but will need them eventually:

### Payment Processing
- [ ] Stripe account (or Square)
- [ ] API keys ready

### Email Sending
- [ ] SMTP server details (Gmail, SendGrid, etc.)
- [ ] OR use hosting's mail server

### SMS Notifications (Optional)
- [ ] Twilio account and credentials

### PADI Integration (Optional)
- [ ] PADI store number
- [ ] PADI API credentials

---

## 🚀 Ready to Install?

If you checked ALL items in the "Required" sections above, you're ready!

### Next Steps:
1. Go to: `https://yourwebsite.com/install.php`
2. Follow the 6-step installation wizard
3. See `QUICK_INSTALL.md` for detailed instructions

---

## 🆘 Having Trouble?

### Can't check all the boxes?

**Contact your hosting provider** and tell them:

> "I'm installing Nautilus Dive Shop Management System. I need:
> - PHP 8.0 or newer
> - The following PHP extensions: PDO, PDO_MySQL, MBString, JSON, cURL, OpenSSL, GD, ZIP, XML
> - Ability to create MySQL databases
> - Composer to run: composer install --no-dev"

Most shared hosting providers can help you with this in minutes!

---

## 📞 Support Resources

1. **Quick Install Guide:** See `QUICK_INSTALL.md`
2. **Full Documentation:** See `INSTALLATION_GUIDE.md`
3. **Your Hosting Support:** For server configuration issues
4. **GitHub Issues:** For application bugs or feature requests

---

**Print this checklist and check off each item before starting!**
