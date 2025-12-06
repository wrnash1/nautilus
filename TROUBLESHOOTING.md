# 🔧 Nautilus Troubleshooting Guide
**Common problems and how to fix them**

---

## 🚨 Installation Issues

### Problem: "Permission Denied" or "Storage Not Writable"

**Symptoms:**
- Installer shows red X for "Storage Writable"
- Error messages about file permissions
- Can't create .env file

**Solutions:**

1. **Try Auto-Fix (Easiest):**
   - In Step 1 of installer, click **"Try Auto-Fix Permissions"** button
   - Refresh the page
   - Check if issue is resolved

2. **Manual Fix via cPanel:**
   - Go to File Manager
   - Find `storage` folder
   - Right-click → Change Permissions
   - Set to `775` (rwxrwxr-x)
   - Check "Recurse into subdirectories"
   - Apply changes

3. **Manual Fix via SSH/Terminal:**
   ```bash
   cd /path/to/nautilus
   chmod -R 775 storage
   chmod 775 .
   chown -R www-data:www-data .
   ```
   (Replace `www-data` with your web server user: `apache` on Fedora/RHEL, `IUSR` on Windows)

4. **Contact Hosting Support:**
   Send them:
   > "Please set the storage folder to writable (chmod 775) for my Nautilus installation at /path/to/nautilus"

---

### Problem: "Vendor Directory Not Found"

**Symptoms:**
- Installer shows "Vendor Directory Exists" as failed
- Error about missing Composer dependencies

**Cause:**
- Composer dependencies haven't been installed

**Solutions:**

1. **If you have SSH access:**
   ```bash
   cd /path/to/nautilus
   composer install --no-dev
   ```

2. **Shared hosting without SSH:**
   - Contact your hosting support
   - Ask them to run: `composer install --no-dev` in your Nautilus directory
   - Or ask if they have a Composer tool in cPanel

3. **Alternative - Manual upload:**
   - Download dependencies on your local computer
   - Run `composer install --no-dev` locally
   - Upload the entire `vendor` folder via FTP
   - This can take a while due to many files!

---

### Problem: "Database Connection Failed"

**Symptoms:**
- Can't proceed past Step 3
- Error: "SQLSTATE[HY000] [1045] Access denied"
- Error: "Unknown database"

**Solutions:**

1. **Verify database credentials:**
   - Database name is **case-sensitive** on Linux!
   - Check for typos in username/password
   - No extra spaces before or after values

2. **Verify database exists:**
   - Log into cPanel → MySQL Databases
   - Check if database is listed
   - If not, create it

3. **Verify user has access:**
   - In cPanel MySQL Databases
   - Scroll to "Add User To Database"
   - Make sure your user is added with ALL PRIVILEGES

4. **Check database host:**
   - Try `localhost` instead of `127.0.0.1` (or vice versa)
   - Some hosts use a different hostname (ask hosting support)

5. **Test connection manually:**
   - In cPanel, go to phpMyAdmin
   - Try logging in with your database credentials
   - If this fails, your credentials are wrong

---

### Problem: Missing PHP Extensions

**Symptoms:**
- Red X marks for PHP extensions
- Error: "PDO_MySQL extension not installed"

**Solutions:**

1. **Shared Hosting:**
   - Contact hosting support
   - Ask them to enable missing extensions
   - Most shared hosts can do this through their control panel

2. **cPanel - PHP Selector:**
   - Some cPanel installations have "Select PHP Version"
   - Go to that tool
   - Check the boxes for missing extensions
   - Save

3. **Your Own Server (Ubuntu/Debian):**
   ```bash
   sudo apt update
   sudo apt install php-mysql php-mbstring php-gd php-curl php-zip php-xml
   sudo systemctl restart apache2
   ```

4. **Your Own Server (Fedora/RHEL/CentOS):**
   ```bash
   sudo dnf install php-mysqlnd php-mbstring php-gd php-curl php-pecl-zip php-xml
   sudo systemctl restart httpd
   ```

---

### Problem: Migration Failures

**Symptoms:**
- Installer stalls on Step 4
- Error messages about SQL syntax
- Some tables created but not all

**Solutions:**

1. **Check MySQL version:**
   - MySQL 5.7+ or MySQL 8.0+ required
   - In cPanel → MySQL Databases → version shown at top
   - If too old, contact hosting to upgrade

2. **Clear partial installation:**
   ```bash
   # Drop the database and start over
   # In cPanel phpMyAdmin, select your database
   # Drop all tables, or drop the entire database and recreate
   ```

3. **Run migrations manually:**
   - If specific migration fails, note which one
   - You can manually import SQL via phpMyAdmin
   - Go to: database/migrations/
   - Import failed migration file manually

4. **Check database size limits:**
   - Some shared hosts have database size limits
   - Nautilus needs ~50MB for initial install
   - Check your hosting plan limits

---

## 🚨 Post-Installation Issues

### Problem: "Page Not Found" or 404 Errors

**Symptoms:**
- Homepage works but other pages show 404
- URLs like `/admin/dashboard` don't work

**Cause:**
- Apache mod_rewrite not enabled
- .htaccess file missing or not working

**Solutions:**

1. **Check if .htaccess exists:**
   - In cPanel File Manager, go to `public` folder
   - Make sure `.htaccess` file is there
   - If missing, create it with this content:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

2. **Enable mod_rewrite (your own server):**
   ```bash
   # Ubuntu/Debian
   sudo a2enmod rewrite
   sudo systemctl restart apache2

   # Fedora/RHEL
   # Usually enabled by default, but check:
   sudo httpd -M | grep rewrite
   ```

3. **Check .htaccess is allowed:**
   - Your Apache config needs `AllowOverride All` for your directory
   - Contact hosting support if on shared hosting

4. **For Nginx:**
   - Add this to your server block:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

---

### Problem: Can't Login / "Invalid Credentials"

**Symptoms:**
- Login page loads but credentials rejected
- "Email or password incorrect" message

**Solutions:**

1. **Double-check credentials:**
   - Email is case-sensitive
   - Check for typos
   - No extra spaces

2. **Reset password via database:**
   ```sql
   -- In phpMyAdmin, run this query:
   UPDATE users
   SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
   WHERE email = 'your-email@example.com';
   -- This sets password to: "password"
   -- Login and change it immediately!
   ```

3. **Check users table exists:**
   - In phpMyAdmin, verify `users` table has data
   - Check if your admin user is in the table

4. **Session issues:**
   - Clear browser cache and cookies
   - Try different browser
   - Check `storage/sessions` folder is writable

---

### Problem: "500 Internal Server Error"

**Symptoms:**
- White screen or generic server error
- "The server encountered an error"

**Solutions:**

1. **Check error logs:**
   - In cPanel: Error Log viewer
   - Or check: `storage/logs/app.log`
   - Or server logs: `/var/log/apache2/error.log` (Ubuntu) or `/var/log/httpd/error_log` (Fedora)

2. **Enable debug mode (temporarily):**
   - Edit `.env` file
   - Change `APP_DEBUG=false` to `APP_DEBUG=true`
   - Reload page to see actual error
   - **IMPORTANT:** Disable debug mode after fixing! (security risk)

3. **Check file permissions:**
   - `storage` folder must be writable (775)
   - `.env` file must be readable (600 or 644)

4. **PHP memory limit:**
   - Nautilus needs at least 256MB
   - In cPanel: PHP Settings → memory_limit
   - Or edit `.htaccess`:
   ```apache
   php_value memory_limit 256M
   ```

5. **Syntax error in .env:**
   - Check `.env` file for syntax errors
   - Make sure values with spaces are quoted
   - Example: `APP_NAME="My Dive Shop"`

---

### Problem: Email Not Sending

**Symptoms:**
- Password resets don't arrive
- Booking confirmations not sent
- Test emails fail

**Solutions:**

1. **Check email configuration:**
   - Go to Settings → Email Settings
   - Send test email
   - Check spam/junk folder

2. **Gmail specific:**
   - Use "App Password" not your regular password
   - Enable "Less secure app access" (or use App Password)
   - Google Account → Security → App Passwords

3. **Check .env settings:**
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   ```

4. **Try different mail service:**
   - Use hosting's SMTP server (ask support for details)
   - Or try SendGrid, Mailgun (free tiers available)

5. **Check server firewall:**
   - Port 587 (TLS) must be open
   - Port 25 might be blocked by hosting
   - Port 465 (SSL) is alternative

---

### Problem: Payment Gateway Errors

**Symptoms:**
- "Payment processing failed"
- Transactions don't complete
- Stripe/Square errors

**Solutions:**

1. **Test mode vs Live mode:**
   - Make sure you're using correct keys (test vs live)
   - Stripe test cards: 4242 4242 4242 4242
   - Don't use real cards in test mode!

2. **Check API keys:**
   - Go to Settings → Payment Gateways
   - Verify keys have no extra spaces
   - Regenerate keys if needed (in Stripe/Square dashboard)

3. **Webhook configuration (Stripe):**
   - In Stripe Dashboard → Webhooks
   - Add endpoint: `https://yoursite.com/api/stripe/webhook`
   - Copy webhook secret to Nautilus settings

4. **SSL required:**
   - Payment gateways require HTTPS
   - Make sure SSL certificate is installed and valid
   - Check browser shows padlock icon

5. **Currency mismatch:**
   - Stripe/Square must be configured for your currency
   - Check Settings → Company Settings → Currency
   - Must match your payment gateway account

---

### Problem: Images Not Uploading

**Symptoms:**
- Logo upload fails
- Product images won't upload
- "File too large" errors

**Solutions:**

1. **Check file size:**
   - Default limit: 10MB
   - To increase, edit `.env`:
   ```
   UPLOAD_MAX_SIZE=20971520
   ```
   (Value in bytes, 20971520 = 20MB)

2. **PHP upload limits:**
   - In cPanel: PHP Settings
   - Increase: upload_max_filesize and post_max_size
   - Both should be larger than your image sizes

3. **Check file type:**
   - Allowed by default: jpg, jpeg, png, pdf, doc, docx
   - To add more, edit `.env`:
   ```
   ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf,doc,docx,gif,webp
   ```

4. **Permissions:**
   - `public/uploads` folder must be writable
   - `storage/uploads` folder must be writable
   - Set to 775 permissions

---

### Problem: Reports Showing No Data

**Symptoms:**
- Dashboard charts are empty
- Reports show zeros
- "No data available"

**Causes & Solutions:**

1. **No transactions yet:**
   - Make some test sales/bookings
   - Reports need data to display!

2. **Date range:**
   - Check date filter is set correctly
   - Try "All Time" range
   - Make sure dates include your test data

3. **Cache issues:**
   - Clear application cache
   - In Settings → System → Clear Cache
   - Or delete: `storage/cache/*`

4. **Database query issues:**
   - Check error logs: `storage/logs/app.log`
   - Enable debug mode to see SQL errors

---

## 🚨 Performance Issues

### Problem: Site is Slow

**Solutions:**

1. **Enable caching:**
   - In `.env`, make sure:
   ```
   CACHE_DRIVER=file
   ```
   - Consider Redis for better performance (requires installation)

2. **Database optimization:**
   - In phpMyAdmin, run: `OPTIMIZE TABLE` on all tables
   - Do this monthly

3. **Image optimization:**
   - Compress images before uploading
   - Use tools like TinyPNG, ImageOptim
   - Recommended: <500KB per image

4. **Upgrade hosting:**
   - Nautilus recommends 512MB+ RAM
   - Shared hosting with many sites can be slow
   - Consider VPS for better performance

5. **PHP OpCache:**
   - Ask hosting to enable PHP OpCache
   - Significant performance improvement

---

## 🆘 When All Else Fails

### Reset Installation

If you need to start over:

1. **Backup first!**
   - Download .env file
   - Export database (phpMyAdmin → Export)

2. **Drop database:**
   - In phpMyAdmin, select database
   - Drop all tables (or drop entire database)

3. **Delete .installed file:**
   - In cPanel File Manager
   - Delete `/.installed` from root

4. **Re-run installer:**
   - Go to: `https://yoursite.com/install.php`
   - Start fresh

---

## 📞 Getting Help

### Before Asking for Help

Gather this information:
1. **Error message** (exact wording)
2. **When it happens** (what steps cause it)
3. **What you tried** (from this guide)
4. **Environment info:**
   - PHP version
   - MySQL version
   - Hosting provider
   - Browser (if frontend issue)

### Where to Get Help

1. **Check logs:**
   - `storage/logs/app.log`
   - Server error logs
   - Browser console (F12)

2. **Documentation:**
   - `INSTALLATION_GUIDE.md`
   - `FIRST_TIME_SETUP.md`
   - `README.md`

3. **Hosting Support:**
   - For server configuration issues
   - PHP/MySQL problems
   - Permission issues

4. **GitHub Issues:**
   - For bugs in Nautilus
   - Feature requests
   - Community help

---

## 🔍 Debugging Checklist

When something goes wrong:

- [ ] Check error logs (`storage/logs/app.log`)
- [ ] Check server error logs (cPanel or `/var/log/`)
- [ ] Enable debug mode temporarily (`APP_DEBUG=true`)
- [ ] Clear browser cache
- [ ] Try different browser
- [ ] Check file permissions (storage = 775)
- [ ] Check .env file syntax
- [ ] Verify database connection
- [ ] Check server meets requirements (PHP 8.0+, etc.)
- [ ] Restart web server (if you have access)
- [ ] Check SSL certificate is valid (for payment issues)

---

## 💡 Pro Tips

1. **Always backup before making changes**
   - Database export
   - .env file copy
   - Note what you changed

2. **Test in incognito/private mode**
   - Eliminates cache issues
   - Fresh session

3. **Check browser console (F12)**
   - JavaScript errors show here
   - Network tab shows failed requests

4. **One change at a time**
   - Easier to identify what broke
   - Can undo easily

5. **Keep debug mode OFF in production**
   - Security risk!
   - Only enable when actively debugging
   - Always disable after

---

**Still stuck? Don't hesitate to ask for help! Include error logs and detailed description of the issue.**
