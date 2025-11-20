# Nautilus Web Installer - Feature Documentation

## Overview

The Nautilus web installer ([public/install.php](public/install.php)) is a **fully automated, browser-based installation wizard** designed for non-technical users. It handles all server configuration, database setup, and initial system configuration through a simple 4-step process.

**Target Audience**: Dive shop owners with no technical knowledge or command-line experience.

---

## Key Design Principles

1. **Zero Command Line Required**: Everything done through web browser
2. **Auto-Detection**: Automatically detects server configuration
3. **Auto-Fix**: Attempts to fix common issues without user intervention
4. **Clear Visual Feedback**: Color-coded status, progress bars, detailed logging
5. **Fail-Safe**: Prevents installation if critical requirements aren't met
6. **Mobile Responsive**: Works on tablets and mobile devices

---

## Installation Workflow

### Step 1: System Requirements Check

**What it checks:**
- ✅ PHP version (7.4+ required, checks actual version)
- ✅ Required PHP extensions (pdo, pdo_mysql, mbstring, json, curl, openssl, zip)
- ✅ Directory permissions (storage/, cache/, logs/, sessions/, uploads/)
- ✅ SELinux configuration (Fedora/RHEL/CentOS)
- ✅ Root directory writability (for .env file creation)
- ✅ Web server user detection (apache, www-data, nginx)

**Auto-Fix Features:**
```php
// Lines 378-426 in install.php
- Creates missing directories automatically (mkdir with 0775 permissions)
- Attempts to set correct permissions (chmod 0775)
- Detects SELinux and applies httpd_sys_rw_content_t context
- Tries to set ownership to web server user (apache, www-data, nginx)
- Re-checks permissions after auto-fix attempts
```

**Visual Feedback:**
- Green boxes for passing requirements
- Red boxes for failures
- Info boxes showing automatic fixes applied
- Detailed error messages with manual fix instructions

**User Actions:**
- If all green: Click "Continue to Database Setup"
- If any red: Displays manual commands to run
- Can click "Recheck After Fixing" to verify manual changes

---

### Step 2: Database Setup

**What it does:**

#### 2A: Connection Testing
```php
// Lines 516-546 in install.php
- Tests MySQL/MariaDB connection
- Validates credentials
- Reports specific connection errors with suggestions
```

#### 2B: Database Creation
```php
// Lines 548-557
- Creates database if it doesn't exist
- Sets UTF-8 character encoding (utf8mb4)
- Reports if database already exists
```

#### 2C: Migration Execution
```php
// Lines 559-640
- Finds all SQL migration files in database/migrations/
- Executes each migration in sequence
- Shows real-time progress bar
- Displays console-style log output
- Handles errors gracefully
- Verifies table creation
```

**Auto-Run Features:**
- Creates `migrations` tracking table
- Executes all .sql files in order
- Skips already-completed migrations
- Logs success/failure for each migration
- Uses transactions where possible

#### 2D: Schema Verification
```php
// Lines 707-740
- Verifies essential tables exist:
  - tenants
  - users
  - roles
  - customers
  - products
  - sales_orders
- Counts tables created
- Provides detailed error messages if verification fails
```

**Visual Feedback:**
- Connection status (green = success, red = failed)
- Animated progress bar during migrations
- Console-style output showing each SQL statement
- Final table count and verification status

**User Actions:**
- Enter database credentials
- Click "Test Connection & Setup Database"
- If successful: Proceeds automatically to Admin Setup
- If failed: Shows error and "Try Again" button

---

### Step 3: Admin Account Creation

**What it does:**

#### 3A: Company Setup
```php
// Lines 72-82 in install.php
- Creates first tenant record
- Generates UUID for tenant
- Stores company name, subdomain, email
- Sets status to 'active'
```

#### 3B: User Account Creation
```php
// Lines 84-105
- Finds or uses admin role (ID: 1)
- Hashes password using PHP password_hash()
- Creates user record with tenant_id
- Assigns admin role via user_roles table
- Sets user as active
```

#### 3C: Environment File Generation
```php
// Lines 107-136
- Creates .env file with:
  - Database credentials
  - Application settings
  - Cryptographically secure APP_KEY
  - JWT secret for authentication
  - Session configuration
  - Upload limits and file types
```

#### 3D: Installation Lock
```php
// Lines 138-143
- Creates .installed file
- Prevents re-running installer
- Stores installation timestamp and details
```

**Security Features:**
- Password confirmation required
- Minimum password length enforced
- Password hashed with bcrypt (PASSWORD_DEFAULT)
- Random APP_KEY generated (32 bytes)
- Random JWT_SECRET generated (32 bytes)
- .env file permissions set to 640

**User Actions:**
- Enter company information (name, subdomain)
- Enter admin details (name, email, password)
- Confirm password
- Click "Create Admin Account & Finish Installation"

---

### Step 4: Completion

**What it shows:**
```php
// Lines 899-957 in install.php
- Success message with confetti icon 🎉
- Admin email (login username)
- Login URL
- Security reminders
- Next steps checklist
- "Go to Dashboard" button
```

**Security Warnings Displayed:**
- Reminds user to delete install.php
- Suggests enabling HTTPS/SSL
- Recommends regular backups
- Warns about .env file security

**Next Steps Provided:**
- Login instructions
- First-time setup tips
- Links to documentation
- Support contact information

---

## Platform-Specific Features

### Fedora/RHEL/CentOS Support

**SELinux Handling** (Lines 389-402, 436-448):
```php
- Detects if SELinux is enforcing
- Automatically runs chcon commands
- Sets httpd_sys_rw_content_t context
- Applies to storage/ and root directories
- Reports success/failure
```

**Web Server Detection**:
```php
- Checks for 'apache' user (Fedora/RHEL)
- Checks for 'www-data' user (Ubuntu/Debian)
- Checks for 'nginx' user
- Attempts ownership change via chown
```

### Ubuntu/Debian Support

**Automatic Fixes**:
- Uses 'www-data' user for ownership
- Standard permissions (0775)
- No SELinux handling needed

---

## Technical Features

### Migration System

**How it works**:
```php
// Lines 579-640
1. Scans database/migrations/ for *.sql files
2. Sorts files alphanumerically
3. For each file:
   - Reads SQL content
   - Splits into individual statements
   - Executes each statement
   - Records success in migrations table
   - Logs output to console
4. Reports total migrations run
```

**Error Handling**:
- Catches PDOException for each statement
- Displays error message in console
- Allows continuation on non-critical errors
- Final verification step ensures core tables exist

### Security Measures

**Password Handling**:
```php
// Line 91
password_hash($_POST['password'], PASSWORD_DEFAULT)
// Uses bcrypt, automatically salted, resistant to rainbow tables
```

**Credential Storage**:
```php
// Lines 107-131
- .env file created with secure permissions
- Database credentials never exposed in code
- APP_KEY uses random_bytes(32)
- JWT_SECRET uses random_bytes(32)
```

**Installation Lock**:
```php
// Lines 23-52
- Checks for .installed file before running
- Shows warning if trying to re-run
- Prevents accidental data loss
- Requires manual intervention to reinstall
```

### Progress Feedback

**Real-Time Updates**:
```php
// Lines 570-573
- JavaScript-updated progress bar
- Percentage completion shown
- Console-style output streams
- Color-coded success/error messages
```

**User Experience**:
- Step indicator at top (1→2→3→4)
- Active step highlighted in blue
- Completed steps marked green
- Current step expanded

---

## What's Still Manual (Bash Scripts)

The current bash scripts ([scripts/setup.sh](scripts/setup.sh), [scripts/setup-database.sh](scripts/setup-database.sh)) do these additional tasks that **could be added to the web installer**:

### Missing from Web Installer:

1. **PHP Extension Installation**
   - Bash: Auto-installs missing extensions via dnf/apt
   - Web: Only checks and reports missing extensions

2. **Apache Virtual Host Setup**
   - Bash: Copies config to /etc/httpd/conf.d/
   - Web: Not handled at all

3. **Demo Data Installation**
   - Bash: Offers to install sample data
   - Web: Not offered in installer

4. **Composer Dependencies**
   - Bash: Runs `composer install`
   - Web: Assumes already installed

---

## Recommendations for Non-Technical Users

### What Works Great Now:

✅ **For Shared Hosting Users** (most dive shops):
- Upload files via FTP or cPanel
- Navigate to install.php in browser
- Follow 4-step wizard
- **No command line needed**
- **No technical knowledge required**

### What Could Be Better:

❌ **For VPS/Dedicated Server Users**:
- Must manually install PHP extensions
- Must manually configure Apache
- Must manually run composer install
- **Requires SSH access and command line knowledge**

### Proposed Solution:

**Option 1: Keep Both** (Recommended)
- Web installer for shared hosting users (95% of dive shops)
- Bash scripts for VPS/self-hosted users (5% of dive shops)

**Option 2: Enhance Web Installer** (Future)
- Add "Download Server Config" button that generates:
  - Apache vhost config file
  - Nginx config file
  - PHP extension install commands
- User downloads and sends to hosting provider
- Hosting provider applies configuration

**Option 3: Hosting Provider Partnership** (Best)
- Partner with dive shop-friendly hosting providers
- Provide pre-configured Nautilus hosting packages
- One-click installation like WordPress
- Support included

---

## Documentation Updates Needed

### Current State:
- [INSTALL.md](INSTALL.md) - Technical, command-line focused
- [public/install.php](public/install.php) - Fully functional web wizard

### Recommendation:
- ✅ **INSTALL_SIMPLE.md** - Non-technical, browser-based (CREATED)
- ⚠️ **INSTALL.md** - Keep for VPS users, mark as "Advanced"
- ✅ **WEB_INSTALLER_FEATURES.md** - Technical documentation (THIS FILE)

---

## Summary

The existing web installer at [public/install.php](public/install.php) is **already well-suited for non-technical users**. It:

✅ Runs entirely in browser
✅ Auto-detects and fixes common issues
✅ Provides clear visual feedback
✅ Handles all database setup automatically
✅ Creates secure admin accounts
✅ Generates environment configuration
✅ Includes SELinux support (Fedora)
✅ Works on shared hosting

**The bash scripts are redundant for most users.** They're only needed for VPS/dedicated servers where the user has root access and wants to configure Apache/PHP extensions programmatically.

**For dive shop owners**: Follow [INSTALL_SIMPLE.md](INSTALL_SIMPLE.md) and ignore the bash scripts entirely.

**For system administrators**: Use [INSTALL.md](INSTALL.md) and the bash scripts if you prefer automation.

---

## Files Reference

- [public/install.php](public/install.php) - Main web installer (1118 lines)
- [INSTALL_SIMPLE.md](INSTALL_SIMPLE.md) - User-friendly guide
- [INSTALL.md](INSTALL.md) - Technical guide
- [scripts/setup.sh](scripts/setup.sh) - Automated setup script (VPS/advanced)
- [scripts/setup-database.sh](scripts/setup-database.sh) - Database setup script (VPS/advanced)
- [database/seeders/demo_data.sql](database/seeders/demo_data.sql) - Demo data (not in web installer)

---

**Last Updated**: 2025-01-17
**Web Installer Version**: Alpha v1
