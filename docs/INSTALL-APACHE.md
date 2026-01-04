# Nautilus Dive Shop - Apache Installation Guide

This guide will help you install and configure the Nautilus Dive Shop application using Apache web server. No technical experience required!

---

## What You'll Need

Before starting, make sure you have:
- A computer running **Windows 10/11**, **Ubuntu/Debian Linux**, or **macOS**
- Internet connection to download files
- About 30-60 minutes of time

---

## Step 1: Download Required Software

### For Windows Users

1. **Download XAMPP** (includes Apache, PHP, and MySQL all-in-one)
   - Go to: https://www.apachefriends.org/download.html
   - Click the **Windows** button to download
   - Run the downloaded file and click **Next** through the installer
   - Install to the default location: `C:\xampp`

### For Ubuntu/Debian Linux Users

1. Open **Terminal** (press `Ctrl + Alt + T`)
2. Copy and paste these commands one at a time, pressing **Enter** after each:

```bash
sudo apt update
sudo apt install apache2 php php-mysql php-mbstring php-xml php-curl php-gd mariadb-server unzip -y
sudo systemctl start apache2
sudo systemctl start mariadb
sudo systemctl enable apache2
sudo systemctl enable mariadb
```

### For macOS Users

1. **Download MAMP** (free version)
   - Go to: https://www.mamp.info/en/downloads/
   - Download and install MAMP
   - Open MAMP and click **Start Servers**

---

## Step 2: Download Nautilus Application

1. Go to the Nautilus GitHub page or download location
2. Click the green **Code** button, then **Download ZIP**
3. Extract the ZIP file to:
   - **Windows (XAMPP):** `C:\xampp\htdocs\nautilus`
   - **Linux:** `/var/www/html/nautilus`
   - **macOS (MAMP):** `/Applications/MAMP/htdocs/nautilus`

---

## Step 3: Create the Database

### For Windows (XAMPP) and macOS (MAMP)

1. Open your web browser
2. Go to: `http://localhost/phpmyadmin`
3. Click **Databases** at the top
4. In the "Create database" box, type: `nautilus`
5. Click **Create**

### For Linux

1. Open Terminal and type:
```bash
sudo mysql -u root
```

2. At the `MariaDB>` prompt, type these commands:
```sql
CREATE DATABASE nautilus;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Step 4: Configure Apache

### For Windows (XAMPP)

1. Open XAMPP Control Panel
2. Click **Config** next to Apache
3. Click **Apache (httpd-vhosts.conf)**
4. Add this at the bottom of the file:

```apache
<VirtualHost *:80>
    ServerName nautilus.local
    DocumentRoot "C:/xampp/htdocs/nautilus/public"
    
    <Directory "C:/xampp/htdocs/nautilus/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

5. Save and close the file
6. Open Notepad **as Administrator**
7. Open the file: `C:\Windows\System32\drivers\etc\hosts`
8. Add this line at the bottom: `127.0.0.1 nautilus.local`
9. Save and restart Apache in XAMPP Control Panel

### For Linux

1. Create a new configuration file:
```bash
sudo nano /etc/apache2/sites-available/nautilus.conf
```

2. Paste this content:
```apache
<VirtualHost *:80>
    ServerName nautilus.local
    DocumentRoot /var/www/html/nautilus/public
    
    <Directory /var/www/html/nautilus/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
```

3. Press `Ctrl + X`, then `Y`, then `Enter` to save
4. Enable the site and restart Apache:
```bash
sudo a2ensite nautilus.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

5. Edit your hosts file:
```bash
sudo nano /etc/hosts
```

6. Add this line: `127.0.0.1 nautilus.local`
7. Save and exit (`Ctrl + X`, `Y`, `Enter`)

### For macOS (MAMP)

1. Open MAMP
2. Click **Preferences** → **Web Server**
3. Set the Document Root to: `/Applications/MAMP/htdocs/nautilus/public`
4. Click **OK** and restart MAMP servers

---

## Step 5: Configure Nautilus Application

1. Navigate to your Nautilus folder
2. Find the file named `.env.example`
3. Make a copy and rename it to `.env`
4. Open `.env` in a text editor (Notepad, TextEdit, or nano)
5. Update these lines with your database information:

```
DB_HOST=127.0.0.1
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

> **Note:** For XAMPP, the default password is empty (just leave it blank).

---

## Step 6: Run the Installation

1. Open your web browser
2. Go to: `http://nautilus.local/install.php` or `http://localhost/nautilus/public/install.php`
3. Follow the on-screen instructions
4. Click **Install Demo Data** if you want sample data to explore
5. Complete the setup wizard

---

## Step 7: Access Your Application

🎉 **Congratulations!** Your Nautilus Dive Shop is now installed!

- **Your Website:** http://nautilus.local (or http://localhost/nautilus/public)
- **Admin Login:** http://nautilus.local/store/login
- **Default Admin:** admin@admin.com / password

> ⚠️ **Important:** Change the default password immediately after your first login!

---

## Troubleshooting

### "Page not found" or blank page
- Make sure Apache is running
- Check that `mod_rewrite` is enabled
- Verify the `.htaccess` file exists in the `public` folder

### "Database connection error"
- Verify your database credentials in the `.env` file
- Make sure MySQL/MariaDB is running
- Check that the `nautilus` database was created

### Permission errors (Linux/macOS)
```bash
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

---

## Getting Help

If you encounter any issues:
1. Check the Nautilus GitHub Issues page
2. Review the error logs in `storage/logs/`
3. Contact support or open a new issue

---

*Last Updated: January 2026*
