# Nautilus - Development Setup Summary

## ✅ What's Been Updated

Your Nautilus project now has comprehensive documentation for your Linux development environment and workflow.

---

## 📁 Files Created/Updated

### 1. **DEVELOPER_GUIDE.md** (Updated)
- Added **Development Environment** section
- Added **Development Workflow** section
- Updated **Testing** section with your specific rsync workflow
- Comprehensive Linux/Pop!_OS setup instructions

### 2. **deploy-to-test.sh** (New)
- Location: `/home/wrnash1/Developer/deploy-to-test.sh`
- Automated deployment script
- Syncs code from development to web server
- Sets proper permissions automatically
- **Usage:** `~/Developer/deploy-to-test.sh`

### 3. **QUICK_DEV_REFERENCE.md** (New)
- Quick reference card for daily development
- Common commands and workflows
- Troubleshooting shortcuts
- One-page cheat sheet

### 4. **DEVELOPMENT_SETUP_SUMMARY.md** (This File)
- Overview of development environment
- Setup verification checklist

---

## 🔧 Your Development Environment

### Directories
```
/home/wrnash1/Developer/nautilus/    ← Development (edit here)
         ↓
    [rsync deploy]
         ↓
/var/www/html/nautilus/                  ← Web server (test here)
```

### Workflow
1. **Edit** code in `/home/wrnash1/Developer/nautilus/`
2. **Deploy** using `~/Developer/deploy-to-test.sh`
3. **Test** at `http://localhost/nautilus/public`
4. **Monitor** logs: `sudo tail -f /var/log/apache2/error.log`
5. **Commit** changes: `git add . && git commit -m "message"`

---

## 🚀 Quick Start Commands

### Deploy to Test Server
```bash
~/Developer/deploy-to-test.sh
```

### Manual Deploy (if needed)
```bash
cd /var/www/html
sudo rsync -av --delete --exclude='vendor/' \
  /home/wrnash1/Developer/nautilus/ \
  /var/www/html/nautilus/
sudo chown -R www-data:www-data nautilus/
```

### Watch Logs
```bash
# Terminal 1: Apache errors
sudo tail -f /var/log/apache2/error.log

# Terminal 2: Application logs
sudo tail -f /var/www/html/nautilus/storage/logs/app.log
```

---

## ✅ Setup Verification Checklist

Run these commands to verify your environment is set up correctly:

### 1. Check Directories
```bash
# Development directory exists
ls -la /home/wrnash1/Developer/nautilus/

# Web server directory exists
ls -la /var/www/html/nautilus/
```

### 2. Check PHP
```bash
# PHP version (should be 8.2+)
php -v

# Check extensions
php -m | grep -E "mysqli|pdo|curl|mbstring|openssl|gd"
```

### 3. Check Apache
```bash
# Apache is running
sudo systemctl status apache2

# mod_rewrite is enabled
apache2ctl -M | grep rewrite
```

### 4. Check MySQL
```bash
# MySQL is running
sudo systemctl status mysql

# Can connect to database
mysql -u root -p -e "SHOW DATABASES;" | grep nautilus
```

### 5. Check Permissions
```bash
# Storage directory is writable
ls -la /var/www/html/nautilus/ | grep storage

# Should show: drwxr-xr-x www-data www-data
```

### 6. Check Deploy Script
```bash
# Script exists and is executable
ls -la ~/Developer/deploy-to-test.sh

# Should show: -rwxr-xr-x
```

### 7. Test Deployment
```bash
# Run deploy script
~/Developer/deploy-to-test.sh

# Should complete without errors
```

### 8. Test in Browser
```bash
# Open in browser (or use curl)
curl -I http://localhost/nautilus/public

# Should return HTTP 200 or 302 (redirect to login)
```

---

## 📚 Documentation Index

| Document | Purpose | When to Use |
|----------|---------|-------------|
| [QUICK_DEV_REFERENCE.md](QUICK_DEV_REFERENCE.md) | Quick commands | Daily development |
| [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) | Complete guide | Learning/reference |
| [COURSE_MANAGEMENT_GUIDE.md](COURSE_MANAGEMENT_GUIDE.md) | Course system | PADI features |
| [ARCHITECTURE.md](ARCHITECTURE.md) | System design | Understanding flow |
| [README.md](README.md) | Project overview | First-time setup |

---

## 🔄 Typical Development Session

```bash
# 1. Navigate to project
cd /home/wrnash1/Developer/nautilus

# 2. Create feature branch
git checkout -b feature/new-feature

# 3. Edit files (use your IDE)
nano app/Controllers/YourController.php

# 4. Check syntax (optional)
php -l app/Controllers/YourController.php

# 5. Deploy to test server
~/Developer/deploy-to-test.sh

# 6. Open browser to test
firefox http://localhost/nautilus/public/your-route

# 7. Watch logs in another terminal
sudo tail -f /var/log/apache2/error.log

# 8. If OK, commit
git add .
git commit -m "Add new feature"

# 9. Merge to main
git checkout main
git merge feature/new-feature

# 10. Push to remote
git push origin main
```

---

## 🛠️ Common Development Tasks

### Add New Controller
```bash
cd /home/wrnash1/Developer/nautilus
nano app/Controllers/YourModule/YourController.php
nano routes/web.php  # Add route
~/Developer/deploy-to-test.sh
# Test in browser
```

### Add Database Table
```bash
nano database/migrations/020_your_table.sql
mysql -u root -p nautilus < database/migrations/020_your_table.sql
nano app/Models/YourModel.php
~/Developer/deploy-to-test.sh
# Test in browser
```

### Fix Bug
```bash
cd /home/wrnash1/Developer/nautilus
nano app/Controllers/BuggyController.php  # Make fix
php -l app/Controllers/BuggyController.php  # Check syntax
~/Developer/deploy-to-test.sh  # Deploy
# Test in browser
git add . && git commit -m "Fix bug in X"
```

---

## 🐛 Troubleshooting

### Deploy Script Fails
```bash
# Check permissions
ls -la ~/Developer/deploy-to-test.sh

# Make executable if needed
chmod +x ~/Developer/deploy-to-test.sh
```

### "Permission Denied" on Web Server
```bash
cd /var/www/html/nautilus
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage/
sudo chmod -R 755 public/uploads/
```

### Changes Not Showing in Browser
```bash
# Clear browser cache (Ctrl+Shift+R)
# Or test in incognito mode

# Verify files were deployed
ls -la /var/www/html/nautilus/app/Controllers/YourController.php

# Check file modification time
stat /var/www/html/nautilus/app/Controllers/YourController.php
```

### Database Connection Errors
```bash
# Check .env file
cat /var/www/html/nautilus/.env | grep DB_

# Test database connection
mysql -u nautilus_user -p nautilus
```

---

## 💡 Pro Tips

1. **Keep terminals open**
   - Terminal 1: Development work
   - Terminal 2: Apache logs
   - Terminal 3: Application logs

2. **Use aliases**
   ```bash
   # Add to ~/.bashrc
   alias deploy='~/Developer/deploy-to-test.sh'
   alias logapache='sudo tail -f /var/log/apache2/error.log'
   alias logapp='sudo tail -f /var/www/html/nautilus/storage/logs/app.log'
   ```

3. **Test in incognito**
   - Avoids caching issues
   - Ensures fresh session

4. **Commit often**
   - Small commits are easier to debug
   - Easier to revert if needed

5. **Read logs first**
   - Most errors appear in logs before showing in browser
   - Saves debugging time

---

## 🎯 Next Steps

1. **Familiarize yourself with the deploy script**
   ```bash
   ~/Developer/deploy-to-test.sh
   ```

2. **Set up your IDE** (VSCode recommended)
   - Install PHP extensions
   - Configure debugging

3. **Create test database** (optional)
   ```sql
   CREATE DATABASE nautilus_test;
   ```

4. **Set up git aliases** (optional)
   ```bash
   git config --global alias.st status
   git config --global alias.co checkout
   git config --global alias.br branch
   ```

5. **Start developing!**
   - Check [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) for detailed instructions
   - Use [QUICK_DEV_REFERENCE.md](QUICK_DEV_REFERENCE.md) for daily tasks

---

## 📞 Questions?

- **Development workflow:** See [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) → "Development Workflow"
- **Testing:** See [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) → "Testing"
- **Adding features:** See [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) → "Adding New Features"
- **Quick commands:** See [QUICK_DEV_REFERENCE.md](QUICK_DEV_REFERENCE.md)

---

**You're all set!** Your development environment is documented and ready to use. Happy coding! 🚀
