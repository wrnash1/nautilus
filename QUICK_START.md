# 🚀 Nautilus Quick Start Guide

**For Dive Shop Owners - No Tech Skills Required!**

---

## Fresh Install (From Scratch)

### If You Have Server Access:

```bash
sudo /home/wrnash1/Developer/nautilus/clean-reinstall.sh
```

Then open browser: **https://nautilus.local/install.php**

---

## Web Installer Steps (All Users)

### 1️⃣ Requirements Check
- Page auto-fixes most issues
- See red X? Click **"Try Auto-Fix"** button
- Still stuck? Copy command, send to hosting provider

### 2️⃣ Application Settings
- **Application Name**: Nautilus Dive Shop
- **Company Name**: [Your Dive Shop Name]
- **Timezone**: [Your Location]

### 3️⃣ Database Configuration
- **Host**: `localhost` (or what hosting provider says)
- **Port**: `3306`
- **Database**: `nautilus`
- **Username**: [Your DB username]
- **Password**: [Your DB password]

**Don't have database info?** Create it in cPanel → MySQL Databases

### 4️⃣ Database Setup
- Automatic - just wait 30 seconds
- Watch the spinner, nothing to do!

### 5️⃣ Admin Account
- **First Name**: [Your first name]
- **Last Name**: [Your last name]
- **Email**: [Your login email]
- **Password**: [Strong password, 8+ characters]

### 6️⃣ Complete!
- Click **"Go to Dashboard"**
- You're done! 🎉

---

## After Installation

✅ Delete `public/install.php` for security
✅ Add your logo
✅ Configure shop settings
✅ Add staff accounts
✅ Set up courses

---

## Need Help?

**Shared Hosting Users:**
Contact your hosting support - they handle this daily!

**Server Owners:**
Check logs:
```bash
sudo tail -f /var/log/httpd/error_log
```

**Everyone:**
Read the full [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md)

---

## Database Credentials Cheat Sheet

Write these down before starting:

| Field | Your Value |
|-------|------------|
| Host | `____________` |
| Port | `____________` |
| Database | `____________` |
| Username | `____________` |
| Password | `____________` |

---

## Troubleshooting One-Liners

**Permission errors?**
```bash
sudo chmod -R 775 /var/www/html/nautilus/storage
```

**Can't create .env?**
```bash
sudo chmod 775 /var/www/html/nautilus
```

**Extensions missing?**
```bash
# Fedora
sudo dnf install php-mysqlnd php-gd php-pecl-zip

# Ubuntu
sudo apt install php-mysql php-gd php-zip
```

Then restart Apache:
```bash
sudo systemctl restart httpd    # Fedora
sudo systemctl restart apache2  # Ubuntu
```

---

**That's it!** The web installer handles everything else automatically.

**Time to complete:** 5-10 minutes
**Difficulty:** ⭐ Easy (if you can use WordPress, you can do this!)

---

*Last updated: December 2024*
