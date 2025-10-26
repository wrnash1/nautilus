# Nautilus - Team Onboarding Guide

## 🎯 Quick Start for New Team Members

Welcome to the Nautilus development team! This guide will help you get set up quickly.

---

## 📋 Prerequisites

Before you begin, ensure you have:

- [ ] Ubuntu/Pop!_OS Linux system
- [ ] PHP 8.2 or higher installed
- [ ] MySQL/MariaDB 8.0+ installed
- [ ] Apache 2.4+ with mod_rewrite
- [ ] Composer installed
- [ ] Git installed
- [ ] GitHub account with repository access

---

## 🚀 Initial Setup (First Time)

### 1. Clone the Repository

```bash
cd ~/Developer
git clone https://github.com/yourusername/nautilus.git
cd nautilus
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit with your database credentials
nano .env
```

Update these values:
```ini
DB_HOST=localhost
DB_DATABASE=nautilus
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Set Up Database

```bash
# Create database
mysql -u root -p
```

In MySQL:
```sql
CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Run migrations:
```bash
# Run all migrations in order
for file in database/migrations/*.sql; do
    mysql -u root -p nautilus < "$file"
done
```

### 5. Set Up Deployment Script

The deployment script is already in `~/Developer/deploy-to-test.sh` and ready to use!

Test it:
```bash
~/Developer/deploy-to-test.sh
```

### 6. Verify Installation

Open your browser:
```
http://localhost/nautilus/public
```

You should see the Nautilus login screen.

---

## 📚 Essential Documentation

Read these in order:

1. **[README.md](README.md)** - Project overview (5 min)
2. **[QUICK_DEV_REFERENCE.md](QUICK_DEV_REFERENCE.md)** - Daily commands (10 min)
3. **[DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)** - Complete guide (30 min)
4. **[ARCHITECTURE.md](ARCHITECTURE.md)** - System architecture (15 min)

---

## 🔄 Daily Workflow

### Standard Development Process

1. **Pull Latest Changes**
   ```bash
   cd ~/Developer/nautilus
   git pull origin main
   ```

2. **Create Feature Branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make Your Changes**
   - Edit files in `~/Developer/nautilus/`
   - Never edit directly in `/var/www/html/nautilus/`

4. **Deploy to Test Server**
   ```bash
   ~/Developer/deploy-to-test.sh
   ```

5. **Test in Browser**
   ```
   http://localhost/nautilus/public
   ```

6. **Watch Logs** (in separate terminals)
   ```bash
   # Terminal 1: Apache errors
   sudo tail -f /var/log/apache2/error.log

   # Terminal 2: Application logs
   sudo tail -f /var/www/html/nautilus/storage/logs/app.log
   ```

7. **Commit Your Changes**
   ```bash
   git add .
   git commit -m "Your descriptive commit message"
   ```

8. **Push to GitHub**
   ```bash
   git push origin feature/your-feature-name
   ```

9. **Create Pull Request**
   - Go to GitHub
   - Create pull request from your feature branch to main
   - Request code review

---

## 🛠️ Common Tasks

### Add New Controller

```bash
cd ~/Developer/nautilus

# Create controller
nano app/Controllers/YourModule/YourController.php

# Add route
nano routes/web.php

# Deploy and test
~/Developer/deploy-to-test.sh
```

### Add Database Table

```bash
# Create migration file
nano database/migrations/020_your_table_name.sql

# Run migration
mysql -u root -p nautilus < database/migrations/020_your_table_name.sql

# Create model
nano app/Models/YourModel.php

# Deploy and test
~/Developer/deploy-to-test.sh
```

### Fix a Bug

```bash
# Pull latest
git pull origin main

# Create bug fix branch
git checkout -b fix/bug-description

# Make fix
nano app/Controllers/BuggyController.php

# Deploy and test
~/Developer/deploy-to-test.sh

# Commit and push
git add .
git commit -m "Fix: Bug description"
git push origin fix/bug-description

# Create pull request on GitHub
```

---

## 📂 Project Structure

```
~/Developer/nautilus/
├── app/                    # Application code
│   ├── Controllers/        # HTTP controllers
│   ├── Models/            # Database models
│   ├── Services/          # Business logic
│   ├── Middleware/        # Request middleware
│   └── Views/             # HTML templates
├── database/              # Database files
│   └── migrations/        # SQL migrations
├── public/                # Web root
│   ├── index.php          # Entry point
│   └── assets/            # CSS, JS, images
├── routes/                # Route definitions
│   └── web.php
├── storage/               # Runtime files
│   ├── logs/
│   └── cache/
└── tests/                 # Test files
```

---

## 🔑 Useful Commands

### Development

```bash
# Navigate to project
cd ~/Developer/nautilus

# Deploy to test server
~/Developer/deploy-to-test.sh

# Check PHP syntax
php -l app/Controllers/YourController.php

# Watch logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/www/html/nautilus/storage/logs/app.log
```

### Git

```bash
# Check status
git status

# Pull latest
git pull origin main

# Create branch
git checkout -b feature/branch-name

# Stage changes
git add .

# Commit
git commit -m "Your message"

# Push
git push origin branch-name

# Switch branches
git checkout main
```

### Database

```bash
# Connect to database
mysql -u root -p nautilus

# Run migration
mysql -u root -p nautilus < database/migrations/XXX_file.sql

# Backup database
mysqldump -u root -p nautilus > backup_$(date +%Y%m%d).sql
```

### Services

```bash
# Check Apache status
sudo systemctl status apache2

# Restart Apache
sudo systemctl restart apache2

# Check MySQL status
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

---

## 🐛 Troubleshooting

### "Permission Denied" Errors

```bash
cd /var/www/html/nautilus
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage/
sudo chmod -R 755 public/uploads/
```

### "Database Connection Failed"

1. Check credentials in `.env`
2. Test connection:
   ```bash
   mysql -u nautilus_user -p nautilus
   ```

### "Page Not Found" (404)

```bash
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Changes Not Showing

1. Clear browser cache (Ctrl+Shift+R)
2. Test in incognito mode
3. Verify files deployed:
   ```bash
   ls -la /var/www/html/nautilus/app/Controllers/YourController.php
   ```

---

## 💡 Pro Tips

1. **Use Bash Aliases** - Add to `~/.bashrc`:
   ```bash
   alias deploy='~/Developer/deploy-to-test.sh'
   alias cdnautilus='cd ~/Developer/nautilus'
   alias logapache='sudo tail -f /var/log/apache2/error.log'
   alias logapp='sudo tail -f /var/www/html/nautilus/storage/logs/app.log'
   ```

2. **Keep Multiple Terminals Open**
   - Terminal 1: Development work
   - Terminal 2: Apache logs
   - Terminal 3: Application logs

3. **Test Before Committing**
   - Always deploy and test before pushing
   - Check logs for errors
   - Test edge cases

4. **Write Good Commit Messages**
   ```
   Good: "Add student photo upload validation"
   Bad:  "fixed stuff"
   ```

5. **Use Git Branches**
   - Never commit directly to main
   - Always use feature/fix branches
   - Create pull requests for review

---

## 🎓 Learning Resources

### Internal Documentation

- [QUICK_DEV_REFERENCE.md](QUICK_DEV_REFERENCE.md) - Quick commands
- [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) - Complete guide
- [COURSE_MANAGEMENT_GUIDE.md](COURSE_MANAGEMENT_GUIDE.md) - Course system
- [ARCHITECTURE.md](ARCHITECTURE.md) - System design

### External Resources

- PHP Documentation: https://www.php.net/docs.php
- MySQL Reference: https://dev.mysql.com/doc/
- Git Tutorial: https://git-scm.com/book/en/v2

---

## 👥 Team Communication

### Code Reviews

- All code must be reviewed before merging to main
- Create pull requests for all changes
- Respond to review comments promptly
- Test reviewer feedback

### Asking for Help

1. Check documentation first
2. Search existing issues/pull requests
3. Ask in team chat
4. Provide context and error messages

---

## ✅ Onboarding Checklist

- [ ] Repository cloned
- [ ] Dependencies installed
- [ ] Database created and configured
- [ ] Migrations run successfully
- [ ] `.env` file configured
- [ ] Deployment script tested
- [ ] Application loads in browser
- [ ] Read essential documentation
- [ ] Bash aliases added
- [ ] Git configured with your email
- [ ] Made first test commit
- [ ] Understand workflow
- [ ] Know where to find help

---

## 🚀 You're Ready!

Welcome to the team! Start with small tasks and ask questions.

**First Task Suggestion:**
1. Make a small documentation update
2. Deploy and test
3. Create pull request
4. Get it reviewed and merged

This will help you learn the full workflow!

---

**Questions?** Check [QUICK_DEV_REFERENCE.md](QUICK_DEV_REFERENCE.md) or ask the team!
