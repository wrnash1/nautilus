# 🌊 Nautilus - Simple Installation Guide
## Alpha Version 1 - For Dive Shop Owners (No Technical Knowledge Required)

---

## 📦 What You Need Before Starting

1. **A Web Server** - You need hosting that supports:
   - PHP 7.4 or newer
   - MySQL database
   - At least 500MB of space

2. **FTP/File Access** - A way to upload files to your server (like FileZilla, or your hosting control panel)

3. **5-10 Minutes** - That's all it takes!

---

## 🚀 Installation Steps

### Step 1: Get the Files on Your Server

1. **Download or copy** all the Nautilus files to your computer
2. **Upload everything** to your web server
   - If you're using cPanel, upload to `public_html/` or `www/`
   - If you have a subdomain, upload to that folder
   - Make sure ALL folders are uploaded (app, database, public, storage, vendor, etc.)

### Step 2: Visit the Installer

1. Open your web browser
2. Go to your website URL + `/install.php`
   - Example: `https://yourdomain.com/install.php`
   - Or: `https://nautilus.yourdomain.com/install.php`

3. You'll see a beautiful blue installation screen with a wave emoji 🌊

### Step 3: System Check (Automatic!)

The installer will automatically:
- ✅ Check if your server meets all requirements
- ✅ Create necessary folders
- ✅ Fix file permissions

**What you do:**
- Just click **"Continue to Database Setup"**

If you see red X marks, contact your hosting provider and show them this screen.

### Step 4: Database Setup

You need your database information. Find this in:
- **cPanel**: Look for "MySQL Databases"
- **Plesk**: Look for "Databases"
- **Other hosting**: Check your hosting control panel or email from your provider

**Fill in the form:**

| Field | What to Enter | Example |
|-------|--------------|---------|
| Database Host | Usually `localhost` | localhost |
| Port | Usually `3306` | 3306 |
| Database Name | Pick any name | nautilus |
| Database Username | Your MySQL username | nautilus_user |
| Database Password | Your MySQL password | (your password) |

**Click:** "Test Connection & Setup Database"

⏱️ **Please wait 30-60 seconds** - The installer is creating 250+ database tables. You'll see a console with green checkmarks as it works.

### Step 5: Create Your Admin Account

Fill in your information:

**Company Information:**
- Company Name: Your dive shop name
- Subdomain: Short version (example: `blueocean`)

**Administrator Account:**
- First Name: Your first name
- Last Name: Your last name
- Email: Your email (you'll use this to log in)
- Password: Create a strong password (min 8 characters)
- Confirm Password: Type the same password again

**Click:** "Create Admin Account & Complete Installation"

### Step 6: Done! 🎉

You'll see a success screen. Click **"Go to Login Page"** and log in with your email and password!

---

## 🎯 Quick Troubleshooting

### "Requirements Not Met" Error

**Problem:** Red X marks on the system check

**Solution:** Contact your hosting provider and say:
> "I need PHP 7.4 or higher with these extensions: pdo, pdo_mysql, mbstring, json, curl, openssl, and zip. Also, I need write permissions on my application folders."

### "Database Connection Failed" Error

**Problem:** Can't connect to database

**Solution:**
1. Double-check your database username and password
2. Make sure you created the database in your hosting control panel
3. Contact your hosting provider if still stuck

### "Permission Denied" Error

**Problem:** Can't create files

**Solution:**
1. Make sure you uploaded ALL files
2. Try setting folder permissions to `755` or `775`
3. Contact your hosting provider for help with permissions

### Can't Find install.php

**Problem:** "404 Not Found" when visiting /install.php

**Solution:**
1. Make sure you uploaded the `public` folder
2. Your website should point to the `public` folder
3. Try: `yoursite.com/public/install.php`

---

## 📞 Need Help?

If you get stuck:

1. **Screenshot the error** - Take a picture of whatever error you see
2. **Note what step you're on** - System Check? Database? Admin Account?
3. **Contact your hosting provider** - They can help with:
   - Database credentials
   - File permissions
   - PHP version
   - Server requirements

---

## ✅ After Installation

Once you're logged in:

1. **Update Company Settings** - Add your logo, address, phone
2. **Add Your First Product** - Go to Inventory → Products
3. **Add a Customer** - Go to CRM → Customers
4. **Explore!** - Click around and see all the features

---

## 🔒 Security Tips

After installation:

1. **Change APP_DEBUG to false**
   - Find the `.env` file in your root folder
   - Change `APP_DEBUG=true` to `APP_DEBUG=false`

2. **Use a strong password**
   - At least 12 characters
   - Mix of letters, numbers, symbols

3. **Regular backups**
   - Back up your database weekly
   - Back up your files monthly

---

## 📱 Mobile Friendly

Nautilus works great on:
- 💻 Desktop computers
- 📱 iPhones & Android phones
- 📱 iPads & Tablets

Just use your web browser!

---

## 🎓 Learning Nautilus

After installation, check out:
- **Dashboard** - See your daily stats
- **Help Center** - Built-in guides (top right menu)
- **Sample Data** - Try creating test customers/products

---

## ⚡ Quick Start Checklist

After logging in for the first time:

- [ ] Update company settings (name, logo, contact info)
- [ ] Add your first staff member
- [ ] Create product categories
- [ ] Add 5-10 products to get started
- [ ] Add a test customer
- [ ] Process a test sale to learn the POS system
- [ ] Set up certifications/courses you offer
- [ ] Configure trip schedules
- [ ] Test the rental system

---

**That's it! You're ready to manage your dive shop with Nautilus!** 🌊

---

*Nautilus Alpha Version 1 - Professional Dive Shop Management*
*Easy enough for anyone, powerful enough for anything*
