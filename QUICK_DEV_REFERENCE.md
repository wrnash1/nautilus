# Nautilus - Quick Developer Reference Card

## 🚀 Daily Workflow

### 1. Make Code Changes
```bash
cd /home/wrnash1/Developer/nautilus
# Edit your files
```

### 2. Deploy to Test Server
```bash
# Quick method (use the script)
~/Developer/deploy-to-test.sh

# Manual method
cd /var/www/html
sudo rsync -av --delete --exclude='vendor/' \
  /home/wrnash1/Developer/nautilus/ \
  /var/www/html/nautilus/
sudo chown -R www-data:www-data nautilus/
```

### 3. Test in Browser
```
http://localhost/nautilus/public
```

### 4. Watch Logs
```bash
# Terminal 1: Apache errors
sudo tail -f /var/log/apache2/error.log

# Terminal 2: Application logs
sudo tail -f /var/www/html/nautilus/storage/logs/app.log
```

---

## 📁 Important Directories

| Path | Purpose |
|------|---------|
| `/home/wrnash1/Developer/nautilus/` | Development code (EDIT HERE) |
| `/var/www/html/nautilus/` | Web server (DEPLOY HERE) |
| `/var/log/apache2/` | Apache logs |
| `/var/www/html/nautilus/storage/logs/` | App logs |

---

## 🔧 Common Commands

### PHP
```bash
# Check syntax
php -l app/Controllers/YourController.php

# Check version
php -v

# Check installed extensions
php -m
```

### Apache
```bash
# Restart Apache
sudo systemctl restart apache2

# Check status
sudo systemctl status apache2

# Test config
sudo apache2ctl -t

# Enable mod_rewrite
sudo a2enmod rewrite && sudo systemctl restart apache2
```

### MySQL
```bash
# Connect to database
mysql -u root -p nautilus

# Run migration
mysql -u root -p nautilus < database/migrations/XXX_migration.sql

# Check tables
mysql -u root -p nautilus -e "SHOW TABLES;"

# Backup database
mysqldump -u root -p nautilus > backup_$(date +%Y%m%d).sql
```

### Composer
```bash
# Install dependencies
cd /home/wrnash1/Developer/nautilus
composer install

# Update dependencies
composer update

# Install in web server (after deploy)
cd /var/www/html/nautilus
sudo composer install
```

---

## 🐛 Debugging

### Enable Debug Mode
Edit `.env`:
```ini
APP_ENV=local
APP_DEBUG=true
```

### Add Debug Logging
```php
// In your code
error_log("Debug: " . print_r($variable, true));

// View logs
tail -f /var/www/html/nautilus/storage/logs/app.log
```

### Check for Errors
```bash
# PHP errors
sudo tail -f /var/log/apache2/error.log | grep PHP

# Application errors
sudo tail -f /var/www/html/nautilus/storage/logs/app.log

# MySQL errors
sudo tail -f /var/log/mysql/error.log
```

---

## 📝 Creating New Features

### Add Controller
```bash
cd /home/wrnash1/Developer/nautilus

# 1. Create controller
nano app/Controllers/YourModule/YourController.php

# 2. Add route
nano routes/web.php

# 3. Deploy
~/Developer/deploy-to-test.sh

# 4. Test
firefox http://localhost/nautilus/public/your-route
```

### Add Database Table
```bash
# 1. Create migration
nano database/migrations/020_your_table.sql

# 2. Run migration
mysql -u root -p nautilus < database/migrations/020_your_table.sql

# 3. Create model
nano app/Models/YourModel.php

# 4. Deploy and test
~/Developer/deploy-to-test.sh
```

---

## 🔒 Permissions

### Development
```bash
cd /home/wrnash1/Developer/nautilus
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

### Web Server
```bash
cd /var/www/html/nautilus
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage/
sudo chmod -R 755 public/uploads/
```

---

## 🧪 Testing Checklist

Before committing:
- [ ] Deploy to test server
- [ ] Test functionality in browser
- [ ] Check for PHP errors
- [ ] Verify database queries work
- [ ] Check security (CSRF, escaping, etc.)
- [ ] Review logs for warnings
- [ ] Test edge cases

---

## 💾 Git Workflow

```bash
cd /home/wrnash1/Developer/nautilus

# Create feature branch
git checkout -b feature/your-feature

# Make changes, deploy, test

# Commit changes
git add .
git commit -m "Add your feature description"

# Merge to main
git checkout main
git merge feature/your-feature

# Push
git push origin main
```

---

## 📊 Useful Queries

### Check Application Status
```sql
-- Total customers
SELECT COUNT(*) FROM customers;

-- Total products
SELECT COUNT(*) FROM products;

-- Recent transactions
SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10;

-- Active users
SELECT * FROM users WHERE active = 1;
```

### Performance
```sql
-- Show slow queries
SHOW FULL PROCESSLIST;

-- Table sizes
SELECT
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'nautilus'
ORDER BY (data_length + index_length) DESC;
```

---

## 🌐 URLs

| URL | Purpose |
|-----|---------|
| `http://localhost/nautilus/public` | Main application |
| `http://localhost/nautilus/public/install` | Installation wizard |
| `http://localhost/nautilus/public/store` | Staff dashboard |
| `http://localhost/nautilus/public/store/login` | Staff login |
| `http://localhost/nautilus/public/shop` | Online store |

---

## 🆘 Quick Fixes

### "Page Not Found" Error
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Database Connection Failed
```bash
# Check credentials in .env
nano /var/www/html/nautilus/.env

# Test connection
mysql -u nautilus_user -p nautilus
```

### Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus/storage
sudo chmod -R 755 /var/www/html/nautilus/public/uploads
```

### Composer Not Found
```bash
cd /var/www/html/nautilus
sudo composer install
```

---

## 📚 Documentation

- **Full Developer Guide:** [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)
- **Architecture:** [ARCHITECTURE.md](ARCHITECTURE.md)
- **Course Management:** [COURSE_MANAGEMENT_GUIDE.md](COURSE_MANAGEMENT_GUIDE.md)
- **API Documentation:** [docs/API.md](docs/API.md)
- **Security:** [docs/SECURITY.md](docs/SECURITY.md)

---

## 💡 Pro Tips

1. **Use the deploy script** - Faster than typing rsync every time
2. **Keep two terminals open** - One for Apache logs, one for app logs
3. **Test in incognito** - Avoids caching issues
4. **Commit often** - Small, focused commits are easier to debug
5. **Check logs first** - Most errors show up in logs before browser
6. **Use git branches** - Keeps main branch stable

---

**Quick Help:**
- Stuck? Check [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)
- Need to add a feature? See "Adding New Features" section
- Deployment issues? See "Development Workflow" section
- Database problems? See "Database Operations" section
