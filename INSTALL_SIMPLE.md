# Nautilus - Installation Guide for Dive Shop Owners

**No technical knowledge required! This guide is written for people who have never used a server before.**

---

## What You Need

1. A web hosting account with:
   - PHP 7.4 or newer
   - MySQL or MariaDB database
   - At least 500MB of storage space

2. Access to upload files (via FTP, cPanel File Manager, or similar)

3. A web browser (Chrome, Firefox, Safari, or Edge)

---

## Installation in 3 Simple Steps

### Step 1: Upload Files

**Option A: Using cPanel File Manager** (Most Common)

1. Log into your cPanel
2. Click "File Manager"
3. Navigate to `public_html` folder
4. Create a new folder called `nautilus`
5. Upload ALL Nautilus files into this folder
6. Click "Extract" if files are zipped

**Option B: Using FTP Software** (FileZilla, etc.)

1. Open your FTP program
2. Connect to your server (use credentials from your hosting provider)
3. Navigate to `public_html` folder
4. Create a new folder called `nautilus`
5. Upload ALL Nautilus files into this folder

---

### Step 2: Open the Installer

1. Open your web browser
2. Go to: `http://yourwebsite.com/nautilus/install.php`
   - Replace `yourwebsite.com` with your actual domain name
   - Example: `http://blueoceandiving.com/nautilus/install.php`

You should see a blue screen that says "Nautilus Installation" with a wave icon 🌊

---

### Step 3: Follow the On-Screen Steps

The installer will guide you through 4 easy steps:

#### ✅ Step 1: System Check
- The installer automatically checks if your server is compatible
- **It will try to fix any issues automatically**
- If everything is green, click "Continue to Database Setup"

#### ✅ Step 2: Database Setup
You'll need information from your hosting provider:
- **Database Name**: Usually something like `yourusername_nautilus`
- **Database Username**: Usually provided by your host
- **Database Password**: Set when you created the database

**Where to get these details:**
- In cPanel, go to "MySQL Databases"
- Or check your hosting provider's welcome email
- Or contact your hosting support

Enter the information and click "Test Connection & Setup Database"

The installer will:
- Create all necessary database tables
- Set up the initial structure
- Populate certification agencies and training courses

#### ✅ Step 3: Create Your Admin Account
Enter your information:
- Your dive shop name
- Your email address (this will be your login)
- Choose a password
- Your name

Click "Create Admin Account & Finish Installation"

#### ✅ Step 4: Complete!
You'll see a success message with:
- Your login email
- A button to access your dive shop system

**Click "Go to Dashboard"** to start using Nautilus!

---

## After Installation

### First Login

1. You'll see a login screen
2. Enter the email and password you created in Step 3
3. Click "Login"

### What to Do First

1. **Add Your Products**
   - Go to Products → Add New Product
   - Add dive equipment, courses, rentals, etc.

2. **Add Customer Records**
   - Go to Customers → Add New Customer
   - Import existing customer data if you have it

3. **Configure Settings**
   - Go to Settings
   - Set your timezone
   - Set your currency
   - Add your tax rate
   - Add your shop's contact information

4. **Set Up User Accounts** (Optional)
   - Go to Users → Add New User
   - Create accounts for your staff
   - Assign roles (Manager, Instructor, Sales, etc.)

---

## Troubleshooting

### "Can't connect to the installer"

**Check your URL:**
- Make sure you're using the correct address
- Example: `http://yoursite.com/nautilus/install.php`
- Don't forget `/install.php` at the end

**Make sure files are uploaded:**
- Log into cPanel File Manager
- Check that files are in `public_html/nautilus/public/`
- The `install.php` file should be visible

### "System requirements not met"

The installer tries to fix issues automatically. If it can't:

**Contact your hosting provider and say:**
> "I need PHP 7.4 or newer with these extensions enabled: pdo, pdo_mysql, mbstring, json, curl, openssl, zip"

Most modern hosts already have these enabled.

### "Database connection failed"

**Check your database details:**
1. Log into cPanel
2. Go to "MySQL Databases"
3. Verify:
   - Database name exists
   - Username has "All Privileges" on the database
   - Password is correct

**Still not working?**
- Contact your hosting provider
- Say: "I need help connecting to my MySQL database"

### "Permission denied" or "Can't create .env file"

The installer tries to fix permissions automatically. If it can't:

**Contact your hosting provider and say:**
> "I need the folder `/public_html/nautilus` and `/public_html/nautilus/storage` to be writable by the web server"

They'll know what to do.

---

## Security After Installation

After you've successfully logged in, you should:

### Delete the Installer (Important!)

**Via cPanel File Manager:**
1. Go to `public_html/nautilus/public/`
2. Find `install.php`
3. Delete it (or rename it to `install.php.old`)

**Why?** So nobody else can run the installer and wipe your data.

### Use HTTPS (Recommended)

Ask your hosting provider to enable SSL/HTTPS for your website. This encrypts customer data and makes your site secure.

Most modern hosts offer free SSL certificates through Let's Encrypt.

---

## Getting Help

### Need Support?

- **Email**: support@nautilus-dive-shop.com (example - use your actual support email)
- **Documentation**: Check the `/docs` folder for detailed guides
- **Video Tutorials**: Visit our YouTube channel (if you have one)

### Your Hosting Provider Can Help With:

- Database setup issues
- File upload problems
- PHP version or extensions
- File permissions
- SSL/HTTPS setup

They WON'T be able to help with how to use Nautilus itself.

---

## Frequently Asked Questions

### Q: Do I need to know programming?
**A:** No! The installer handles all technical setup automatically.

### Q: Can I install this on a shared hosting account?
**A:** Yes! Nautilus works on shared hosting, VPS, or dedicated servers.

### Q: What if I make a mistake during installation?
**A:** Just refresh the page and start over. Nothing is saved until you complete all steps.

### Q: Can I install a demo version to test first?
**A:** Yes! During Step 2 (Database Setup), the installer offers to install sample data so you can explore features before adding real customer information.

### Q: How do I backup my data?
**A:** Most hosting providers offer automatic backups. Also:
1. Go to Settings → Backup in Nautilus
2. Click "Download Database Backup"
3. Save the file to your computer

### Q: Can I move Nautilus to a different server later?
**A:** Yes! Just:
1. Backup your database
2. Download all files
3. Upload to new server
4. Run the installer again
5. Import your database backup

### Q: What happens if I forget my password?
**A:** On the login screen, click "Forgot Password?" and follow the instructions. A reset link will be sent to your email.

---

## System Requirements (Technical)

*This section is for your hosting provider or technical support person*

- **PHP**: 7.4 or newer (8.0+ recommended)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Extensions**: pdo, pdo_mysql, mbstring, json, curl, openssl, zip, gd, fileinfo
- **Disk Space**: 500MB minimum, 2GB+ recommended
- **Memory**: 256MB PHP memory limit minimum

---

## Still Confused?

**Watch our installation video tutorial:** [Link to video if you have one]

**Or contact your web hosting provider and say:**

> "I need to install a PHP web application called Nautilus. Can you help me upload the files and set up a MySQL database?"

They'll walk you through it!

---

## License

Copyright © 2025 Nautilus Dive Shop Software. All rights reserved.

---

**Ready to get started? Go back to [Step 1](#step-1-upload-files) and let's set up your dive shop system!**
