# 🌊 Nautilus Quick Installation Guide
**Get your dive shop online in 10 minutes!**

---

## 🎯 What You Need

1. **Web hosting** with:
   - PHP 8.0 or newer
   - MySQL 5.7+ or MySQL 8.0+
   - Apache or Nginx web server

2. **Your hosting login** (cPanel, Plesk, or FTP access)

3. **10 minutes of your time**

---

## 📦 Installation in 3 Steps

### Step 1: Upload Files (3 minutes)

#### Option A: Using cPanel (Easiest)
1. Download `nautilus.zip` from GitHub
2. Log into your cPanel
3. Open **File Manager**
4. Go to `public_html` folder
5. Click **Upload** and select `nautilus.zip`
6. After upload, right-click the file and choose **Extract**
7. Done! You should see folders: `app`, `config`, `database`, `public`, etc.

#### Option B: Using FTP
1. Download and unzip `nautilus.zip` on your computer
2. Open FileZilla (or your FTP program)
3. Connect to your hosting
4. Upload all folders to `public_html/nautilus/`

### Step 2: Point Your Domain (2 minutes)

**IMPORTANT:** Your website must point to the `/public` folder inside Nautilus.

**In cPanel:**
1. Go to **Domains** (or **Addon Domains**)
2. Click the domain you want to use
3. Change **Document Root** to: `public_html/nautilus/public`
4. Save

**Not sure?** Contact your hosting support and say: *"I need my domain to point to the /public folder inside my application"*

### Step 3: Run the Installer (5 minutes)

1. Open your web browser
2. Go to: `https://yourwebsite.com/install.php`
3. Follow the 6-step wizard:
   - ✅ **Step 1:** Requirements check (installer may auto-fix)
   - ⚙️ **Step 2:** Enter your dive shop name and timezone
   - 💾 **Step 3:** Enter database details (from cPanel)
   - 🔄 **Step 4:** Wait while database is created (automatic)
   - 👤 **Step 5:** Create your admin account
   - ✨ **Step 6:** Done!

4. Login and start managing your dive shop!

---

## 🔑 Database Information

You'll need this for Step 3 of the installer. Get it from cPanel:

1. In cPanel, go to **MySQL Databases**
2. Create new database: `nautilus` (or any name)
3. Create database user with a strong password
4. Add user to database with **ALL PRIVILEGES**

Write down:
- **Database Host:** Usually `localhost` or `127.0.0.1`
- **Database Port:** Usually `3306`
- **Database Name:** `nautilus` (or what you named it)
- **Username:** The database user you created
- **Password:** The password you set

---

## 🆘 Common Issues & Quick Fixes

### "Permission Denied" or "Storage Not Writable"

**Fix:** Click the **"Try Auto-Fix Permissions"** button in Step 1 of the installer.

If that doesn't work, contact your hosting support and send them this:
```
Please run this command:
cd /path/to/nautilus && chmod -R 775 storage && chmod 775 .
```

### "Composer Vendor Directory Not Found"

**Fix:** You need to run `composer install` on the server.

**Shared Hosting?** Ask your hosting support to run:
```
cd /path/to/nautilus && composer install --no-dev
```

**Have SSH access?** Run the command yourself via terminal.

### "PHP Extension Missing"

The installer will tell you exactly which extensions are missing.

**Shared Hosting:** Contact support and say *"Please enable the following PHP extensions: [list from installer]"*

**Your Own Server:** The installer provides the exact command to run.

### Database Connection Failed

Double-check:
- Database name is correct (case-sensitive!)
- Username and password are correct
- User has been added to the database in cPanel
- Host is `localhost` or `127.0.0.1`

---

## 🎉 After Installation

### Immediate Steps:
1. **Delete the installer** for security:
   - Go to cPanel File Manager
   - Navigate to `public` folder
   - Delete `install.php`

2. **Login** at: `https://yourwebsite.com`

3. **Configure your settings:**
   - Go to Settings → Company Settings
   - Add your logo, address, phone, etc.
   - Set your currency and tax rates

### Next Steps:
1. Add your staff/instructors
2. Setup your courses and pricing
3. Configure payment gateway (Stripe or Square)
4. Add your products and rental equipment
5. Start taking bookings!

---

## 📞 Need Help?

1. **Check the full documentation:** See `INSTALLATION_GUIDE.md`
2. **Contact your hosting support** for server-related issues
3. **GitHub Issues:** Report bugs at [your-github-repo]

---

## 🔒 Security Recommendations

After installation:
- [ ] Delete `public/install.php`
- [ ] Change `APP_DEBUG=false` in `.env` file
- [ ] Set strong passwords for all admin accounts
- [ ] Keep your server software updated
- [ ] Setup regular database backups

---

## ⚙️ System Requirements

**Minimum:**
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- 256MB RAM
- 500MB disk space

**Recommended:**
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- 512MB+ RAM
- 1GB+ disk space
- SSL certificate (HTTPS)

**Required PHP Extensions:**
- PDO, PDO_MySQL, MySQLi
- MBString, JSON, cURL
- OpenSSL, GD, ZIP, XML

---

## 📊 What Gets Installed

The installer automatically creates:
- ✅ 210+ database tables
- ✅ Default admin account
- ✅ Initial tenant/company
- ✅ Essential configuration files
- ✅ Logging and cache directories

---

**Ready to dive in? Start with Step 1!** 🏊‍♂️
