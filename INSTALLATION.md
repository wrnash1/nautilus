# Nautilus Installation Guide

Welcome to Nautilus - a comprehensive dive shop management system. This guide will walk you through the installation process.

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Installation Methods](#installation-methods)
3. [Web-Based Installation](#web-based-installation)
4. [Manual Installation](#manual-installation)
5. [Post-Installation](#post-installation)
6. [Troubleshooting](#troubleshooting)

## System Requirements

Before installing Nautilus, ensure your server meets the following requirements:

### Required

- **PHP:** 8.2 or higher
- **MySQL:** 8.0+ or MariaDB 10.6+
- **Web Server:** Apache 2.4+ with mod_rewrite enabled
- **PHP Extensions:**
  - PDO (with MySQL driver)
  - mbstring
  - json
  - openssl
  - curl
  - fileinfo
  - tokenizer

### Recommended

- **Memory:** 512MB minimum, 1GB+ recommended
- **Disk Space:** 500MB minimum for application and initial data
- **SSL Certificate:** For production environments

## Installation Methods

Nautilus offers two installation methods:

1. **Web-Based Installation** (Recommended) - User-friendly wizard
2. **Manual Installation** - Command-line based for advanced users

## Web-Based Installation

The easiest way to install Nautilus is through the web-based installation wizard.

### Step 1: Download and Extract

1. Download Nautilus from the repository
2. Extract the files to your web server directory
3. Ensure the web server has write permissions to the `storage` directory

```bash
# Set permissions (adjust path as needed)
chmod -R 755 storage
chmod 644 .env.example
```

### Step 2: Access the Installer

1. Navigate to your application URL in a web browser:
   ```
   http://yourdomain.com/public/install
   ```
   Or if using a subdirectory:
   ```
   http://yourdomain.com/nautilus/public/install
   ```

### Step 3: System Check

The installer will check your system requirements:
- PHP version and extensions
- File permissions
- Database connectivity

### Step 4: Configure Application

Provide the following information:

#### Application Settings
- **Application Name:** Your dive shop name (default: Nautilus)
- **Application URL:** Full URL where Nautilus will be accessible
- **Timezone:** Your local timezone

#### Database Configuration
- **Host:** Database server address (usually `localhost`)
- **Port:** Database port (default: `3306`)
- **Database Name:** Name for the Nautilus database
- **Username:** Database user with CREATE privileges
- **Password:** Database user password

**Note:** The database will be created automatically if it doesn't exist.

#### Administrator Account
- **First Name:** Admin's first name
- **Last Name:** Admin's last name
- **Email:** Admin email address (used for login)
- **Password:** Secure password (minimum 8 characters)

#### Demo Data (Optional)
- Check the box to install sample data including:
  - Demo products (masks, fins, BCDs, regulators, etc.)
  - Sample customers (B2C and B2B)
  - Example transactions and orders
  - Course and trip listings
  - Rental equipment

### Step 5: Run Installation

1. Click "Install Nautilus"
2. The installer will:
   - Update the `.env` configuration file
   - Generate security keys (APP_KEY and JWT_SECRET)
   - Create the database (if needed)
   - Run all database migrations
   - Seed roles and permissions
   - Create your administrator account
   - Install demo data (if selected)

3. Wait for the installation to complete (usually 1-2 minutes)

### Step 6: Complete

Once installation is complete:
1. You'll be redirected to the completion page
2. Click "Login to Nautilus"
3. Use your administrator email and password to log in

## Manual Installation

For advanced users who prefer command-line installation:

### Step 1: Clone Repository

```bash
git clone https://github.com/yourusername/nautilus-v6.git
cd nautilus-v6
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit .env file with your settings
nano .env
```

Update the following values in `.env`:
```env
APP_NAME="Your Dive Shop"
APP_URL=http://yourdomain.com
DB_DATABASE=nautilus
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Generate security keys:
```bash
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;"
php -r "echo 'JWT_SECRET=' . bin2hex(random_bytes(64)) . PHP_EOL;"
```

Add the generated keys to your `.env` file.

### Step 4: Create Database

```bash
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 5: Run Migrations

```bash
php scripts/migrate.php
```

### Step 6: Seed Initial Data

```bash
# Seed roles and permissions
mysql -u your_user -p nautilus < database/seeds/001_seed_initial_data.sql

# Optional: Seed demo data
mysql -u your_user -p nautilus < database/seeds/002_seed_demo_data.sql
```

### Step 7: Create Admin User

```bash
# Connect to MySQL
mysql -u your_user -p nautilus

# Create admin user (replace values with your information)
INSERT INTO users (role_id, email, password_hash, first_name, last_name, is_active, created_at)
VALUES (
    1,
    'admin@yourdomain.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    1,
    NOW()
);
```

**Note:** The password hash above is for the password "password". Change it after first login!

### Step 8: Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 public/uploads
```

## Post-Installation

After installation, complete these steps:

### 1. Update Application Settings

Navigate to **Admin > Settings** to configure:
- Store information
- Tax rates
- Payment gateways (Stripe, Square, BTCPay)
- Email settings (SMTP)
- Shipping providers (UPS, FedEx)
- Third-party integrations (Google Workspace, PADI, Twilio)

### 2. Configure Payment Gateways

Add your API keys for enabled payment gateways in the `.env` file:

```env
# Stripe
STRIPE_PUBLIC_KEY=pk_...
STRIPE_SECRET_KEY=sk_...

# Square
SQUARE_APPLICATION_ID=...
SQUARE_ACCESS_TOKEN=...
```

### 3. Create Staff Accounts

Navigate to **Admin > User Management** to create accounts for your staff:
- Assign appropriate roles (Manager, Cashier, Instructor)
- Set permissions based on responsibilities

### 4. Set Up Your Product Catalog

1. Go to **Products > Categories** to create product categories
2. Go to **Products > Products** to add your inventory
3. Add product images and detailed descriptions
4. Set pricing, costs, and stock levels

### 5. Configure Rental Equipment

Navigate to **Rentals > Equipment** to:
- Add rental items (BCDs, regulators, wetsuits, etc.)
- Set rental rates (daily/weekly)
- Track equipment condition

### 6. Set Up Courses and Trips

- **Courses > Course Catalog:** Add your dive courses
- **Trips > Trip Catalog:** Add dive trip offerings

### 7. Production Environment Setup

For production deployments:

1. Update `.env` settings:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Enable HTTPS/SSL
3. Set up automated backups:
```env
BACKUP_ENABLED=true
BACKUP_PATH=/var/backups/nautilus
```

4. Configure cron jobs for automated tasks:
```bash
# Add to crontab
0 2 * * * php /path/to/nautilus/scripts/backup.php
0 3 * * * php /path/to/nautilus/scripts/cleanup-sessions.php
```

## Troubleshooting

### Installation Page Not Accessible

**Problem:** Cannot access `/install` page

**Solutions:**
1. Ensure mod_rewrite is enabled:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. Check `.htaccess` file exists in `public/` directory

3. Verify Apache virtual host allows `.htaccess` overrides:
   ```apache
   <Directory /path/to/nautilus/public>
       AllowOverride All
   </Directory>
   ```

### Database Connection Failed

**Problem:** Cannot connect to database

**Solutions:**
1. Verify database credentials in `.env`
2. Ensure MySQL service is running:
   ```bash
   sudo systemctl status mysql
   ```
3. Check MySQL user has proper privileges:
   ```sql
   GRANT ALL PRIVILEGES ON nautilus.* TO 'user'@'localhost';
   FLUSH PRIVILEGES;
   ```

### Permission Denied Errors

**Problem:** Cannot write to storage directory

**Solutions:**
```bash
# Set proper ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data storage
sudo chmod -R 755 storage
```

### Migrations Fail

**Problem:** Database migrations fail during installation

**Solutions:**
1. Check MySQL version is 8.0+ or MariaDB 10.6+
2. Ensure user has CREATE, ALTER, DROP privileges
3. Review error messages in the installer
4. Try running migrations manually:
   ```bash
   php scripts/migrate.php
   ```

### White Screen / 500 Error

**Problem:** Blank page or internal server error

**Solutions:**
1. Enable error display temporarily:
   ```env
   APP_DEBUG=true
   ```
2. Check PHP error logs:
   ```bash
   tail -f /var/log/apache2/error.log
   ```
3. Verify all PHP extensions are installed:
   ```bash
   php -m | grep -E 'pdo|mbstring|json|openssl'
   ```

### Composer Dependencies

**Problem:** Missing vendor dependencies

**Solution:**
```bash
composer install --no-dev --optimize-autoloader
```

## Getting Help

If you encounter issues not covered in this guide:

1. Check the [Documentation](docs/)
2. Review [Common Issues](docs/TROUBLESHOOTING.md)
3. Search or create an issue on GitHub
4. Contact support

## Security Recommendations

- Change default admin password immediately after installation
- Use strong, unique passwords for all accounts
- Enable HTTPS/SSL for production
- Keep PHP, MySQL, and dependencies up to date
- Regularly backup your database
- Restrict database user privileges to only what's needed
- Never commit `.env` file to version control
- Set `APP_DEBUG=false` in production

## Next Steps

After successful installation:

1. ✅ Login to your administrator account
2. ✅ Complete system settings configuration
3. ✅ Add your product catalog
4. ✅ Create staff user accounts
5. ✅ Set up payment gateways
6. ✅ Test POS system functionality
7. ✅ Configure online store
8. ✅ Train your staff

Thank you for choosing Nautilus!
