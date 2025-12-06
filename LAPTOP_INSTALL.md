# 💻 Nautilus Laptop Installation Guide
**For Testing on Your Local Computer (Windows/Mac/Linux)**

---

## 🎯 Perfect For Testing Before Going Live

This guide helps you install Nautilus on your laptop or desktop computer for testing, without needing a web hosting account.

**Who is this for?**
- Dive shop owners who want to test before deploying to the web
- Non-technical users who need a simple local setup
- Anyone who wants to try Nautilus without hosting costs

---

## 📦 What You'll Install

You need a "local web server" on your computer. We'll use **XAMPP** - it's free, easy, and works on Windows, Mac, and Linux.

**XAMPP includes everything you need:**
- ✅ Apache web server
- ✅ MySQL database (actually MariaDB - fully compatible!)
- ✅ PHP 8.2
- ✅ phpMyAdmin (database management tool)

---

## 🚀 Step-by-Step Installation

### Step 1: Download XAMPP (5 minutes)

1. **Go to:** https://www.apachefriends.org
2. **Download** XAMPP for your operating system:
   - Windows: Download `.exe` file
   - Mac: Download `.dmg` file
   - Linux: Download `.run` file

3. **Choose version:** PHP 8.2 or newer (recommended)

### Step 2: Install XAMPP (10 minutes)

#### Windows:
1. Run the downloaded `.exe` file
2. Click "Next" through the installer
3. **Important:** When asked what to install, make sure these are checked:
   - ✅ Apache
   - ✅ MySQL (actually MariaDB)
   - ✅ PHP
   - ✅ phpMyAdmin
4. Install to default location: `C:\xampp`
5. Finish installation
6. Start XAMPP Control Panel (should launch automatically)

#### Mac:
1. Open the downloaded `.dmg` file
2. Drag XAMPP to Applications folder
3. Open XAMPP from Applications
4. Click "Open" if you get security warning
5. Start "Manage Servers" tab

#### Linux (Ubuntu/Fedora):
1. Open Terminal
2. Navigate to Downloads:
   ```bash
   cd ~/Downloads
   ```
3. Make installer executable:
   ```bash
   chmod +x xampp-linux-x64-8.2.*.run
   ```
4. Run installer:
   ```bash
   sudo ./xampp-linux-x64-8.2.*.run
   ```
5. Follow prompts (default options are fine)

### Step 3: Start XAMPP Services (2 minutes)

#### Windows:
1. Open "XAMPP Control Panel" (search in Start menu)
2. Click **"Start"** next to **Apache**
3. Click **"Start"** next to **MySQL**
4. Both should show green "Running" status

#### Mac:
1. Open XAMPP Manager
2. Go to "Manage Servers" tab
3. Start "Apache Web Server"
4. Start "MySQL Database"

#### Linux:
1. Open Terminal
2. Start XAMPP:
   ```bash
   sudo /opt/lampp/lampp start
   ```
3. You should see:
   ```
   Starting XAMPP for Linux...
   XAMPP: Starting Apache...ok.
   XAMPP: Starting MySQL...ok.
   ```

**Test if it's working:**
- Open browser and go to: http://localhost
- You should see XAMPP welcome page!

### Step 4: Download Nautilus (2 minutes)

1. Download `nautilus.zip` (you should have received this)
2. Extract the ZIP file
3. You'll get a folder called `nautilus` with lots of files inside

### Step 5: Copy Nautilus to XAMPP (3 minutes)

#### Windows:
1. Open File Explorer
2. Navigate to: `C:\xampp\htdocs\`
3. Copy the entire `nautilus` folder here
4. Final path should be: `C:\xampp\htdocs\nautilus\`

#### Mac:
1. Open Finder
2. Navigate to: `/Applications/XAMPP/htdocs/`
3. Copy the `nautilus` folder here
4. You may need to authenticate with your password

#### Linux:
1. Open Terminal
2. Copy folder:
   ```bash
   sudo cp -r ~/Downloads/nautilus /opt/lampp/htdocs/
   ```
3. Fix permissions:
   ```bash
   sudo chmod -R 775 /opt/lampp/htdocs/nautilus/storage
   sudo chown -R daemon:daemon /opt/lampp/htdocs/nautilus
   ```

### Step 6: Install Composer Dependencies (5 minutes)

**What is Composer?** It's a tool that downloads code libraries Nautilus needs.

#### Windows:

**Option A: If vendor folder is included (easiest)**
- Skip this step! You're done!

**Option B: If you need to install Composer:**
1. Download Composer: https://getcomposer.org/download/
2. Run `Composer-Setup.exe`
3. Follow installer (default options)
4. Open Command Prompt
5. Navigate to Nautilus:
   ```cmd
   cd C:\xampp\htdocs\nautilus
   ```
6. Run:
   ```cmd
   composer install --no-dev
   ```
7. Wait 2-5 minutes for download

#### Mac:

1. Open Terminal
2. Navigate to Nautilus:
   ```bash
   cd /Applications/XAMPP/htdocs/nautilus
   ```
3. Run Composer (XAMPP includes it):
   ```bash
   /Applications/XAMPP/bin/php composer.phar install --no-dev
   ```

#### Linux:

1. Open Terminal
2. Install Composer:
   ```bash
   cd /opt/lampp/htdocs/nautilus
   sudo /opt/lampp/bin/php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   sudo /opt/lampp/bin/php composer-setup.php
   sudo /opt/lampp/bin/php composer.phar install --no-dev
   ```

### Step 7: Create Database (3 minutes)

1. **Open phpMyAdmin:** http://localhost/phpmyadmin
2. Click **"New"** on the left sidebar
3. **Database name:** `nautilus`
4. **Collation:** `utf8mb4_unicode_ci` (from dropdown)
5. Click **"Create"**

**You should now see `nautilus` in the left sidebar!**

### Step 8: Run Nautilus Installer (10 minutes)

1. **Open browser**
2. **Go to:** http://localhost/nautilus/public/install.php
3. **Follow the 6-step wizard:**

**Step 1: Requirements Check**
- Everything should be green ✅
- If any red ✗, see Troubleshooting below
- Click "Continue to Application Settings"

**Step 2: Application Settings**
- **Application Name:** Nautilus Dive Shop (or your name)
- **Company Name:** Your dive shop name
- **Timezone:** Choose your timezone
- Click "Continue to Database"

**Step 3: Database Configuration**
- **Database Host:** `127.0.0.1` (or `localhost`)
- **Database Port:** `3306`
- **Database Name:** `nautilus`
- **Database Username:** `root`
- **Database Password:** (leave empty - XAMPP has no password by default)
- Click "Test Connection & Continue"

**Step 4: Creating Database Tables**
- Just wait! This runs automatically (30-60 seconds)
- You'll see progress as tables are created

**Step 5: Create Admin Account**
- **First Name:** Your first name
- **Last Name:** Your last name
- **Admin Email:** your-email@example.com
- **Admin Password:** Choose a strong password (at least 8 characters)
- Click "Complete Installation"

**Step 6: Installation Complete!**
- Success! 🎉
- Click "Go to Dashboard"

### Step 9: Login & Test (5 minutes)

1. **Login page:** http://localhost/nautilus/public/
2. **Use credentials** you just created
3. **You should see the Dashboard!**

**Test the customer-facing website:**
- Open new browser tab
- Go to: http://localhost/nautilus/public/
- You should see the storefront homepage!
- Click around: Shop, Courses, Trips, etc.

---

## 🎉 You're Done!

Your Nautilus system is running on your laptop!

### What URLs to use:

**Admin Backend (Staff Login):**
- http://localhost/nautilus/public/store

**Customer-Facing Website:**
- http://localhost/nautilus/public/

**Direct Homepage:**
- http://localhost/nautilus/public/

---

## 🔧 Troubleshooting Common Issues

### XAMPP won't start Apache - "Port 80 in use"

**Problem:** Another program is using port 80 (probably Skype or IIS)

**Solution 1 - Change Apache Port:**
1. In XAMPP Control Panel, click "Config" next to Apache
2. Select "httpd.conf"
3. Find line: `Listen 80`
4. Change to: `Listen 8080`
5. Save and close
6. Restart Apache
7. Now use: http://localhost:8080/nautilus/public/

**Solution 2 - Stop Conflicting Program:**
- **Windows:** Disable IIS (Control Panel → Programs → Turn Windows Features on/off)
- **Skype:** Settings → Advanced → Uncheck "Use ports 80 and 443"

### XAMPP won't start MySQL - "Port 3306 in use"

**Problem:** Another MySQL/MariaDB is running

**Solution:**
1. Stop other MySQL service:
   - **Windows:** Services → MySQL → Stop
   - **Mac:** System Preferences → MySQL → Stop
   - **Linux:** `sudo systemctl stop mysql`
2. Restart XAMPP MySQL

### "Permission Denied" errors

**Windows:**
- Run XAMPP Control Panel as Administrator
- Right-click → "Run as administrator"

**Mac:**
- Grant disk access permissions to XAMPP
- System Preferences → Security & Privacy → Files and Folders

**Linux:**
- Fix permissions:
  ```bash
  sudo chmod -R 775 /opt/lampp/htdocs/nautilus/storage
  sudo chown -R daemon:daemon /opt/lampp/htdocs/nautilus
  ```

### "Vendor Directory Not Found" in installer

**Solution:**
- You need to run `composer install`
- See Step 6 above

### Database connection fails

**Check these:**
- Is MySQL running in XAMPP Control Panel? (should be green)
- Database host: Try both `localhost` AND `127.0.0.1`
- Username: Should be `root`
- Password: Should be **empty** (blank) for XAMPP
- Database name: `nautilus` (make sure you created it)

### Pages show errors or white screen

**Enable error display:**
1. Go to: `C:\xampp\htdocs\nautilus\` (or Mac/Linux equivalent)
2. Find `.env` file (if it exists)
3. Open in Notepad/TextEdit
4. Change `APP_DEBUG=false` to `APP_DEBUG=true`
5. Save
6. Refresh page to see actual error

### Can't find .htaccess file

**Windows hides files starting with dot:**
1. File Explorer → View tab
2. Check "Hidden items"
3. Check "File name extensions"

**Mac:**
1. Finder → Press: `Cmd + Shift + .` (period)
2. Hidden files will appear

---

## ⚠️ Important Notes for Laptop Testing

### This is for TESTING ONLY

**Not for production use because:**
- ❌ Your computer must be running 24/7
- ❌ Not secure for public internet access
- ❌ No SSL/HTTPS encryption
- ❌ Other devices can't access it easily

**When you're ready to go live:**
- Follow [QUICK_INSTALL.md](QUICK_INSTALL.md) for web hosting installation
- You can export your database from phpMyAdmin and import it on the live server

### Accessing from other devices on your network

**Want to test from a phone/tablet on same WiFi?**

1. **Find your computer's IP address:**
   - **Windows:** Open Command Prompt → type `ipconfig` → look for "IPv4 Address"
   - **Mac:** System Preferences → Network → Your IP is shown
   - **Linux:** Terminal → `hostname -I`

2. **On other device, use:**
   - http://YOUR-IP-ADDRESS/nautilus/public/
   - Example: http://192.168.1.100/nautilus/public/

3. **Update .env file:**
   - Change `APP_URL=http://localhost/nautilus/public`
   - To `APP_URL=http://YOUR-IP/nautilus/public`

### Stopping XAMPP when not testing

**You don't need to run XAMPP 24/7!**

**To stop:**
- **Windows:** XAMPP Control Panel → Stop Apache & MySQL
- **Mac:** XAMPP Manager → Stop all servers
- **Linux:** `sudo /opt/lampp/lampp stop`

**To start again:**
- Just start the services again (see Step 3)

---

## 📊 Testing Checklist

Before moving to live hosting, test these:

### Customer-Facing Website:
- [ ] Homepage loads
- [ ] Can view courses
- [ ] Can view dive trips
- [ ] Can add items to cart
- [ ] Can view product details
- [ ] Contact form works (if email configured)

### Admin Backend:
- [ ] Can login at /store
- [ ] Dashboard shows data
- [ ] Can add a customer
- [ ] Can create a product
- [ ] Can process a test sale
- [ ] Can schedule a course

### Data Entry:
- [ ] Add your real courses and pricing
- [ ] Add your products
- [ ] Add staff members
- [ ] Test workflows you'll use daily

---

## 🚀 Moving to Live Hosting

**When you're ready to deploy for real:**

1. **Export your database:**
   - phpMyAdmin → Select `nautilus` database
   - Click "Export"
   - Choose "Quick" method
   - Click "Go"
   - Save the SQL file

2. **Follow web hosting installation:**
   - Use [QUICK_INSTALL.md](QUICK_INSTALL.md)
   - Upload files to your web host
   - Import the SQL file you saved

3. **Update settings:**
   - Change APP_URL to your real domain
   - Setup email properly
   - Configure payment gateway (Stripe/Square)
   - Enable SSL (HTTPS)

---

## 💡 Pro Tips

### Make development easier:

1. **Bookmark these URLs:**
   - Admin: http://localhost/nautilus/public/store
   - Customer Site: http://localhost/nautilus/public/
   - phpMyAdmin: http://localhost/phpmyadmin

2. **Create desktop shortcuts:**
   - Windows: Right-click → Send to → Desktop
   - Mac: Drag to Dock

3. **Test with real data:**
   - Add your actual courses
   - Use real pricing
   - Create realistic customer profiles
   - This way, you'll be ready for go-live!

### Backup your test database:

**Do this weekly:**
1. phpMyAdmin → `nautilus` database
2. Export → Quick → Go
3. Save file with date: `nautilus-2025-12-05.sql`
4. Keep in safe folder

### Reset and start over:

**If you want a fresh install:**
1. phpMyAdmin → Drop `nautilus` database
2. Create new `nautilus` database
3. Delete `.installed` file from nautilus folder
4. Run installer again: http://localhost/nautilus/public/install.php

---

## 📞 Need Help?

### During Laptop Setup:
1. **XAMPP issues:** Check XAMPP documentation at https://www.apachefriends.org/faq.html
2. **Nautilus installer:** See [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

### Testing the Application:
1. **How to use features:** See [FIRST_TIME_SETUP.md](FIRST_TIME_SETUP.md)
2. **Errors in the application:** Check `storage/logs/app.log`

---

## ✅ Quick Reference

**Start XAMPP:**
- Windows: XAMPP Control Panel → Start Apache & MySQL
- Mac: XAMPP Manager → Start servers
- Linux: `sudo /opt/lampp/lampp start`

**URLs:**
- Customer Website: http://localhost/nautilus/public/
- Admin Panel: http://localhost/nautilus/public/store
- Database Admin: http://localhost/phpmyadmin

**Database Credentials:**
- Host: `127.0.0.1` or `localhost`
- Username: `root`
- Password: (empty)
- Database: `nautilus`

**File Locations:**
- Windows: `C:\xampp\htdocs\nautilus\`
- Mac: `/Applications/XAMPP/htdocs/nautilus/`
- Linux: `/opt/lampp/htdocs/nautilus/`

---

**You're ready to test Nautilus on your laptop!** 💻🌊

**Questions?** Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md) or [QUICK_INSTALL.md](QUICK_INSTALL.md) for web hosting.
