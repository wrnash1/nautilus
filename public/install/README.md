# Nautilus Web-Based Installer

Enterprise-grade installation wizard for deploying Nautilus Dive Shop Management System.

## Features

- **6-Step Installation Process**: Guided wizard with visual progress tracking
- **System Requirements Check**: Validates PHP version, extensions, and directory permissions
- **Database Setup**: Automated database creation and migration
- **Configuration Management**: Application and company settings
- **Admin Account Creation**: Secure administrator account setup
- **Real-time Installation**: Live progress tracking with AJAX
- **Professional UI**: Modern, responsive design

## Installation Steps

### Step 1: Requirements Check
- Verifies PHP 8.2+ installation
- Checks required PHP extensions (PDO, MySQL, mbstring, JSON, cURL)
- Validates directory permissions
- Automatically creates necessary directories

### Step 2: Database Configuration
- Database connection testing
- Automatic database creation
- Validates MySQL credentials
- Supports custom host and port

### Step 3: Application Configuration
- Application name and URL
- Company information (name, email, phone)
- Regional settings (timezone, currency, locale)

### Step 4: Admin Account Setup
- Administrator account creation
- Password strength validation
- Email verification
- Role-based permissions

### Step 5: Installation Execution
- Environment file (.env) creation
- Database table creation
- Migration execution (60+ tables)
- Admin account creation
- Directory structure setup
- Sample data loading

### Step 6: Completion
- Installation summary
- Cron job setup instructions
- Security recommendations
- Quick links to application

## Usage

1. Navigate to `https://yoursite.com/install/` in your web browser
2. Follow the 6-step wizard
3. After completion, **delete the `/public/install` directory** for security

## System Requirements

### Required
- PHP >= 8.2
- MySQL >= 8.0 or MariaDB >= 10.3
- PDO Extension
- PDO MySQL Driver
- Mbstring Extension
- JSON Extension
- cURL Extension

### Recommended
- GD Extension (for image processing)
- ZIP Extension (for backups)
- OpenSSL Extension (for secure connections)

### Server Configuration
- Apache 2.4+ or Nginx 1.18+
- mod_rewrite enabled (Apache)
- 256MB+ PHP memory limit
- 60+ seconds max execution time

## Directory Structure

```
public/install/
├── index.php                 # Main installer wizard
├── install_handler.php       # AJAX request handler
├── README.md                 # This file
└── steps/
    ├── step1.php            # Requirements check
    ├── step2.php            # Database configuration
    ├── step3.php            # Application configuration
    ├── step4.php            # Admin account setup
    ├── step5.php            # Installation execution
    └── step6.php            # Completion page
```

## Security Features

- Session-based installation tracking
- Installation lock file prevents re-installation
- Password strength validation
- SQL injection protection (prepared statements)
- XSS protection (htmlspecialchars)
- Directory traversal protection

## Post-Installation Tasks

### 1. Delete Installer (Critical)
```bash
rm -rf /path/to/nautilus/public/install
```

### 2. Set Up Cron Jobs

Add these to your crontab (`crontab -e`):

```bash
# Hourly: Automated Notifications
0 * * * * php /path/to/nautilus/app/Jobs/SendAutomatedNotificationsJob.php

# Daily 1 AM: Calculate Analytics
0 1 * * * php /path/to/nautilus/app/Jobs/CalculateDailyAnalyticsJob.php

# Daily 2 AM: Database Backup
0 2 * * * php /path/to/nautilus/app/Jobs/DatabaseBackupJob.php

# Every 6 hours: Cache Warmup
0 */6 * * * php /path/to/nautilus/app/Jobs/CacheWarmupJob.php

# Sunday 3 AM: Cleanup Old Data
0 3 * * 0 php /path/to/nautilus/app/Jobs/CleanupOldDataJob.php

# Monday 9 AM: Send Scheduled Reports
0 9 * * 1 php /path/to/nautilus/app/Jobs/SendScheduledReportsJob.php
```

### 3. Configure Email (SMTP)

Edit `.env` file:

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your Company Name"
```

### 4. Set Proper File Permissions

```bash
# Set owner (replace www-data with your web server user)
sudo chown -R www-data:www-data /path/to/nautilus

# Set directory permissions
sudo find /path/to/nautilus -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /path/to/nautilus -type f -exec chmod 644 {} \;

# Make writable directories
sudo chmod -R 775 /path/to/nautilus/storage
sudo chmod -R 775 /path/to/nautilus/public/uploads
```

### 5. Enable SSL/HTTPS

For Apache:
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/nautilus/public

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem

    <Directory /path/to/nautilus/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

For Nginx:
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /path/to/nautilus/public;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Troubleshooting

### Installation Fails at Database Step

**Problem**: Cannot connect to database

**Solutions**:
1. Verify MySQL is running: `sudo systemctl status mysql`
2. Check credentials are correct
3. Ensure MySQL user has CREATE DATABASE privileges
4. Verify firewall allows MySQL connections (port 3306)

### Permission Denied Errors

**Problem**: Cannot create directories or files

**Solutions**:
1. Check web server user: `ps aux | grep apache` or `ps aux | grep nginx`
2. Set ownership: `sudo chown -R www-data:www-data /path/to/nautilus`
3. Set permissions: `sudo chmod -R 775 /path/to/nautilus/storage`

### White Screen After Installation

**Problem**: Blank page after installation completes

**Solutions**:
1. Check PHP error logs: `tail -f /var/log/apache2/error.log`
2. Enable debug mode temporarily in `.env`: `APP_DEBUG=true`
3. Verify `.htaccess` file exists in `/public`
4. Check mod_rewrite is enabled (Apache)

### Migration Errors

**Problem**: Database migrations fail

**Solutions**:
1. Check MySQL version is 8.0+
2. Verify character set support: `SHOW VARIABLES LIKE 'character_set%'`
3. Ensure InnoDB engine is available
4. Check available disk space

### Session Errors

**Problem**: Session-related errors during installation

**Solutions**:
1. Verify `storage/sessions` directory exists and is writable
2. Check PHP session configuration in `php.ini`
3. Clear browser cookies and retry
4. Ensure sufficient disk space for session files

## File Descriptions

### index.php
Main installer wizard that coordinates the installation process. Handles session management, step navigation, and displays the UI.

### install_handler.php
Backend handler for AJAX requests. Processes each installation step:
- `create_env`: Creates `.env` configuration file
- `create_database`: Establishes database connection
- `run_migrations`: Executes all SQL migrations
- `create_admin`: Creates administrator account
- `setup_directories`: Creates required directories
- `load_sample_data`: Loads initial data
- `finalize`: Completes installation

### Step Files
Individual UI components for each installation step:
- **step1.php**: System requirements validation
- **step2.php**: Database configuration form
- **step3.php**: Application settings form
- **step4.php**: Admin account creation form
- **step5.php**: Installation progress display
- **step6.php**: Completion summary

## Technical Details

### Session Variables Used
- `install_step`: Current installation step (1-6)
- `db_config`: Database configuration array
- `app_config`: Application settings array
- `admin_config`: Administrator account details

### Database Tables Created
The installer creates 60+ tables including:
- User management (users, roles, permissions)
- Product management (products, categories, inventory)
- Customer management (customers, addresses)
- POS system (transactions, payments)
- Course management (courses, enrollments)
- Equipment rental (rentals, equipment)
- Analytics (metrics cache, KPIs, trends)
- Notifications (settings, logs, schedules)
- Reports (schedules, analytics)

### Security Measures
1. Installation lock file prevents re-installation
2. Session-based step validation
3. Password hashing using bcrypt
4. Prepared statements for SQL queries
5. Input sanitization and validation
6. CSRF protection via session tokens

## Support

For issues or questions:
1. Check the main documentation in `/docs`
2. Review `INSTALLATION_GUIDE.md`
3. Check application logs in `/storage/logs`
4. Verify system requirements are met

## License

Part of the Nautilus Dive Shop Management System
Copyright (c) 2025

## Version

Installer Version: 1.0.0
Nautilus Version: 1.0.0
Last Updated: 2025-01-08
