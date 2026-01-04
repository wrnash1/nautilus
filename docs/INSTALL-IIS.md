# Nautilus Dive Shop - IIS Installation Guide (Windows)

This guide will help you install and configure the Nautilus Dive Shop application using Microsoft IIS (Internet Information Services) on Windows. No technical experience required!

---

## What You'll Need

Before starting, make sure you have:
- A computer running **Windows 10, Windows 11, or Windows Server**
- Administrator access to your computer
- Internet connection to download files
- About 45-60 minutes of time

---

## Step 1: Enable IIS on Windows

### For Windows 10/11

1. Press **Windows Key + R** to open the Run dialog
2. Type `optionalfeatures` and press **Enter**
3. In the Windows Features window, find and check:
   - ✅ **Internet Information Services**
   - Expand it and check:
     - ✅ **Web Management Tools** → **IIS Management Console**
     - ✅ **World Wide Web Services** → **Application Development Features** → **CGI**
     - ✅ **World Wide Web Services** → **Common HTTP Features** (all items)
4. Click **OK** and wait for installation to complete
5. Restart your computer when prompted

### For Windows Server

1. Open **Server Manager**
2. Click **Add roles and features**
3. Click **Next** until you reach **Server Roles**
4. Check ✅ **Web Server (IIS)**
5. Click **Add Features** when prompted
6. Continue clicking **Next** and select these features:
   - CGI
   - URL Rewrite (if available)
7. Click **Install**

---

## Step 2: Install PHP for Windows

1. Go to: https://windows.php.net/download/
2. Download the **VS16 x64 Non Thread Safe** ZIP file (latest PHP 8.x version)
3. Create a folder: `C:\php`
4. Extract the ZIP contents into `C:\php`
5. Rename `php.ini-development` to `php.ini`
6. Open `php.ini` in Notepad and find/change these lines (remove the `;` at the start):
   ```ini
   extension_dir = "C:\php\ext"
   extension=curl
   extension=gd
   extension=mbstring
   extension=mysqli
   extension=pdo_mysql
   extension=openssl
   ```
7. Add PHP to system PATH:
   - Press **Windows Key + X** → **System**
   - Click **Advanced system settings**
   - Click **Environment Variables**
   - Under "System variables", find **Path** and click **Edit**
   - Click **New** and add: `C:\php`
   - Click **OK** to close all windows

---

## Step 3: Install URL Rewrite Module

1. Download from: https://www.iis.net/downloads/microsoft/url-rewrite
2. Click **Install this extension**
3. Run the downloaded installer
4. Follow the prompts to complete installation
5. Restart IIS when prompted

---

## Step 4: Install MySQL/MariaDB

1. Go to: https://dev.mysql.com/downloads/installer/
2. Download **MySQL Installer for Windows**
3. Run the installer and choose **Custom** installation
4. Select these products:
   - MySQL Server
   - MySQL Workbench (for easy database management)
5. Click **Next** and follow the installation
6. When prompted, set a **root password** (remember this!)
7. Complete the installation

---

## Step 5: Download Nautilus Application

1. Go to the Nautilus GitHub page or download location
2. Click the green **Code** button, then **Download ZIP**
3. Create a folder: `C:\inetpub\wwwroot\nautilus`
4. Extract the ZIP contents into this folder

---

## Step 6: Create the Database

1. Open **MySQL Workbench** (installed in Step 4)
2. Click the **Local instance** connection
3. Enter your root password
4. In the query window, type:
   ```sql
   CREATE DATABASE nautilus;
   CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'your_password_here';
   GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
   FLUSH PRIVILEGES;
   ```
5. Click the ⚡ **Execute** button (or press Ctrl+Shift+Enter)

---

## Step 7: Configure IIS

### Open IIS Manager

1. Press **Windows Key + R**
2. Type `inetmgr` and press **Enter**

### Configure PHP Handler

1. In IIS Manager, click your **server name** in the left panel
2. Double-click **Handler Mappings**
3. Click **Add Module Mapping** (right side)
4. Enter:
   - **Request path:** `*.php`
   - **Module:** FastCgiModule
   - **Executable:** `C:\php\php-cgi.exe`
   - **Name:** PHP_via_FastCGI
5. Click **OK**
6. When asked "Do you want to create a FastCGI application?", click **Yes**

### Create the Website

1. In IIS Manager, expand your server → **Sites**
2. Right-click **Sites** → **Add Website**
3. Enter:
   - **Site name:** Nautilus
   - **Physical path:** `C:\inetpub\wwwroot\nautilus\public`
   - **Binding:** 
     - Type: http
     - IP Address: All Unassigned
     - Port: 80
     - Host name: nautilus.local
4. Click **OK**

### Configure URL Rewrite

1. Click on your **Nautilus** site in the left panel
2. Double-click **URL Rewrite**
3. Click **Import Rules** (right side)
4. Browse to: `C:\inetpub\wwwroot\nautilus\public\.htaccess`
5. Click **Import**
6. If there are conversion errors, create rules manually:
   - Click **Add Rule** → **Blank rule**
   - Name: Route to index.php
   - Match URL Pattern: `^(.*)$`
   - Conditions: Add condition
     - Input: `{REQUEST_FILENAME}`
     - Type: Is Not a File
   - Action: Rewrite
   - Rewrite URL: `index.php`
7. Click **Apply**

### Set Folder Permissions

1. Open **File Explorer**
2. Navigate to `C:\inetpub\wwwroot\nautilus`
3. Right-click the `storage` folder → **Properties**
4. Click **Security** tab → **Edit**
5. Click **Add** → type `IUSR` → **Check Names** → **OK**
6. Check ✅ **Modify** permission for IUSR
7. Click **OK**
8. Repeat for the `IIS_IUSRS` group

---

## Step 8: Configure Hosts File

1. Open **Notepad as Administrator**:
   - Right-click Notepad → **Run as administrator**
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Add this line at the bottom:
   ```
   127.0.0.1    nautilus.local
   ```
4. Save the file

---

## Step 9: Configure Nautilus Application

1. Navigate to `C:\inetpub\wwwroot\nautilus`
2. Find the file `.env.example`
3. Make a copy and rename it to `.env`
4. Open `.env` in Notepad
5. Update these lines:
   ```
   DB_HOST=127.0.0.1
   DB_DATABASE=nautilus
   DB_USERNAME=nautilus
   DB_PASSWORD=your_password_here
   ```
6. Save the file

---

## Step 10: Run the Installation

1. Open your web browser
2. Go to: `http://nautilus.local/install.php`
3. Follow the on-screen instructions
4. Click **Install Demo Data** if you want sample data
5. Complete the setup wizard

---

## Step 11: Access Your Application

🎉 **Congratulations!** Your Nautilus Dive Shop is now installed!

- **Your Website:** http://nautilus.local
- **Admin Login:** http://nautilus.local/store/login
- **Default Admin:** admin@admin.com / password

> ⚠️ **Important:** Change the default password immediately after your first login!

---

## Troubleshooting

### "500 Internal Server Error"

1. Check PHP is configured correctly:
   - Open Command Prompt
   - Type: `php -v`
   - You should see PHP version info

2. Enable detailed errors temporarily:
   - Open `C:\php\php.ini`
   - Find `display_errors` and set to `On`
   - Find `log_errors` and set to `On`
   - Restart IIS

3. Check IIS logs:
   - Located at: `C:\inetpub\logs\LogFiles\W3SVC1\`

### "Page not found" for routes

1. Make sure URL Rewrite is installed and configured
2. Verify the web.config file exists in `public` folder
3. Check the rewrite rules in IIS Manager

### "Access denied" or permission errors

1. Right-click the nautilus folder → Properties → Security
2. Add `IIS_IUSRS` and `IUSR` with Modify permissions
3. Check the `storage` folder has write permissions

### Check PHP errors

1. Look in: `C:\inetpub\wwwroot\nautilus\storage\logs\`
2. Open the most recent log file

---

## Optional: Create web.config for URL Rewriting

If URL rewrite import doesn't work, create this file manually:

Save as `C:\inetpub\wwwroot\nautilus\public\web.config`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Imported Rule 1" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>
        <defaultDocument>
            <files>
                <add value="index.php" />
            </files>
        </defaultDocument>
    </system.webServer>
</configuration>
```

---

## Security Recommendations

After installation:

1. **Enable HTTPS** using IIS SSL certificates
2. **Restrict database access** - only allow localhost connections
3. **Enable Windows Firewall** for the web server
4. **Remove the install.php file** after installation
5. **Set production mode** in `.env`: `APP_ENV=production`

---

## Getting Help

If you encounter any issues:
1. Check the Nautilus GitHub Issues page
2. Review Windows Event Viewer for IIS/PHP errors
3. Check logs in `storage/logs/`
4. Contact support or open a new issue

---

*Last Updated: January 2026*
