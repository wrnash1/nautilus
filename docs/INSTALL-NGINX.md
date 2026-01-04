# Nautilus Dive Shop - Nginx Installation Guide

This guide will help you install and configure the Nautilus Dive Shop application using Nginx web server. No technical experience required!

---

## What You'll Need

Before starting, make sure you have:
- A computer running **Ubuntu/Debian Linux**, **Windows 10/11**, or **macOS**
- Internet connection to download files
- About 30-60 minutes of time

---

## Step 1: Install Required Software

### For Ubuntu/Debian Linux (Recommended)

1. Open **Terminal** (press `Ctrl + Alt + T`)
2. Copy and paste these commands one at a time, pressing **Enter** after each:

```bash
sudo apt update
sudo apt install nginx php-fpm php-mysql php-mbstring php-xml php-curl php-gd mariadb-server unzip -y
sudo systemctl start nginx
sudo systemctl start mariadb
sudo systemctl start php8.1-fpm
sudo systemctl enable nginx
sudo systemctl enable mariadb
```

> **Note:** Replace `php8.1-fpm` with your PHP version (e.g., `php8.0-fpm` or `php8.2-fpm`)

### For Windows Users

1. **Download and Install Laragon** (includes Nginx, PHP, and MySQL)
   - Go to: https://laragon.org/download/
   - Download the **Full** version
   - Run the installer and follow the prompts
   - In Laragon preferences, switch from Apache to **Nginx**

### For macOS Users (Using Homebrew)

1. Open **Terminal** (press `Cmd + Space`, type "Terminal")
2. Install Homebrew (if not installed):
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

3. Install the required packages:
```bash
brew install nginx php mysql
brew services start nginx
brew services start php
brew services start mysql
```

---

## Step 2: Download Nautilus Application

1. Go to the Nautilus GitHub page or download location
2. Click the green **Code** button, then **Download ZIP**
3. Extract the ZIP file to:
   - **Linux:** `/var/www/nautilus`
   - **Windows (Laragon):** `C:\laragon\www\nautilus`
   - **macOS:** `/usr/local/var/www/nautilus`

---

## Step 3: Create the Database

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

### For Windows (Laragon)

1. Open Laragon
2. Right-click the Laragon icon → **MySQL** → **HeidiSQL**
3. Connect to the database
4. Click **Query** → **New query tab**
5. Enter: `CREATE DATABASE nautilus;`
6. Press **F9** to run

### For macOS

1. Open Terminal:
```bash
mysql -u root
```

2. Run these commands:
```sql
CREATE DATABASE nautilus;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Step 4: Configure Nginx

### For Linux

1. Create a new configuration file:
```bash
sudo nano /etc/nginx/sites-available/nautilus
```

2. Paste this content (adjust PHP version if needed):

```nginx
server {
    listen 80;
    server_name nautilus.local;
    root /var/www/nautilus/public;
    
    index index.php index.html;
    
    # Security - hide .env and other sensitive files
    location ~ /\. {
        deny all;
    }
    
    # Handle all requests through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Cache static files
    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Logs
    access_log /var/log/nginx/nautilus-access.log;
    error_log /var/log/nginx/nautilus-error.log;
}
```

3. Press `Ctrl + X`, then `Y`, then `Enter` to save

4. Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/nautilus /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

5. Edit your hosts file:
```bash
sudo nano /etc/hosts
```

6. Add this line: `127.0.0.1 nautilus.local`
7. Save and exit (`Ctrl + X`, `Y`, `Enter`)

### For Windows (Laragon)

1. Open Laragon
2. Right-click the Laragon icon → **Nginx** → **sites-enabled** → **Add new**
3. Name it `nautilus`
4. Laragon will create the configuration automatically
5. Right-click → **Nginx** → **Reload**

### For macOS

1. Create the configuration:
```bash
sudo nano /usr/local/etc/nginx/servers/nautilus.conf
```

2. Paste the same configuration as Linux (adjust PHP socket path):
```nginx
server {
    listen 80;
    server_name nautilus.local;
    root /usr/local/var/www/nautilus/public;
    
    index index.php index.html;
    
    location ~ /\. {
        deny all;
    }
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

3. Add to hosts file:
```bash
sudo nano /etc/hosts
```
Add: `127.0.0.1 nautilus.local`

4. Restart Nginx:
```bash
brew services restart nginx
```

---

## Step 5: Set File Permissions (Linux/macOS)

```bash
# For Linux
sudo chown -R www-data:www-data /var/www/nautilus
sudo chmod -R 755 /var/www/nautilus
sudo chmod -R 775 /var/www/nautilus/storage

# For macOS
sudo chown -R $(whoami):staff /usr/local/var/www/nautilus
chmod -R 755 /usr/local/var/www/nautilus
chmod -R 775 /usr/local/var/www/nautilus/storage
```

---

## Step 6: Configure Nautilus Application

1. Navigate to your Nautilus folder
2. Find the file named `.env.example`
3. Make a copy and rename it to `.env`
4. Open `.env` in a text editor
5. Update these lines with your database information:

```
DB_HOST=127.0.0.1
DB_DATABASE=nautilus
DB_USERNAME=nautilus
DB_PASSWORD=your_password_here
```

---

## Step 7: Run the Installation

1. Open your web browser
2. Go to: `http://nautilus.local/install.php`
3. Follow the on-screen instructions
4. Click **Install Demo Data** if you want sample data
5. Complete the setup wizard

---

## Step 8: Access Your Application

🎉 **Congratulations!** Your Nautilus Dive Shop is now installed!

- **Your Website:** http://nautilus.local
- **Admin Login:** http://nautilus.local/store/login
- **Default Admin:** admin@admin.com / password

> ⚠️ **Important:** Change the default password immediately after your first login!

---

## Troubleshooting

### "502 Bad Gateway" error
- Make sure PHP-FPM is running:
  ```bash
  sudo systemctl status php8.1-fpm
  sudo systemctl restart php8.1-fpm
  ```
- Check the PHP socket path matches your configuration

### "Page not found" for all routes except home
- Verify the `try_files` directive is in your Nginx config
- Check Nginx configuration syntax: `sudo nginx -t`

### "Permission denied" errors
- Run the permission commands in Step 5
- Check that `www-data` (or your web user) owns the files

### Check error logs
```bash
# Linux
sudo tail -f /var/log/nginx/nautilus-error.log

# macOS
tail -f /usr/local/var/log/nginx/error.log
```

---

## Security Recommendations

After installation, consider these security improvements:

1. **Enable HTTPS** using Let's Encrypt:
   ```bash
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d yourdomain.com
   ```

2. **Restrict database access** to localhost only

3. **Set up a firewall**:
   ```bash
   sudo ufw allow 'Nginx Full'
   sudo ufw enable
   ```

---

## Getting Help

If you encounter any issues:
1. Check the Nautilus GitHub Issues page
2. Review logs in `storage/logs/` and Nginx error logs
3. Contact support or open a new issue

---

*Last Updated: January 2026*
