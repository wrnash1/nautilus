# 📘 Nautilus Complete Documentation

**Enterprise Dive Shop Management System**

Version 2.0 | Last Updated: 2024

---

## Table of Contents

1. [Introduction](#introduction)
2. [Features](#features)
3. [System Requirements](#system-requirements)
4. [Installation](#installation)
   - [Web-Based Installation (Recommended)](#web-based-installation)
   - [Command Line Installation](#command-line-installation)
5. [Updating Existing Installation](#updating-existing-installation)
6. [Configuration](#configuration)
7. [User Guide](#user-guide)
8. [Technical Documentation](#technical-documentation)
9. [Troubleshooting](#troubleshooting)
10. [Support](#support)

---

## Introduction

Nautilus is a comprehensive, open-source dive shop management system designed to handle every aspect of running a successful dive business. From point-of-sale transactions to customer management, inventory tracking, and certification management, Nautilus provides a complete solution.

### Why Nautilus?

- **✅ No Licensing Fees** - Open source, use anywhere
- **✅ Complete Ownership** - Your data, your server
- **✅ Modern Interface** - Built with Bootstrap 5
- **✅ Easy Installation** - 5-minute web-based setup
- **✅ Fully Customizable** - Modify to fit your needs
- **✅ No Command Line Required** - Everything through web browser

### What Makes It Special

- **Automatic Data Seeding** - Pre-loaded with 15 dive certification agencies, 20+ cert types, default cash drawers, and customer tags
- **Integrated Systems** - POS automatically records to cash drawer, customer data all in one place
- **Professional Grade** - Complete audit trails, role-based access, security features
- **Enterprise Features** - Multi-phone/email per customer, emergency contacts, passport tracking, equipment rental history

---

## Features

### Core Systems

#### Point of Sale (POS)
- Fast, intuitive checkout interface
- Multiple payment methods (cash, credit, check)
- Barcode scanning support
- Receipt printing
- Transaction history
- Automatic cash drawer integration

#### Customer Management (CRM)
- Complete customer profiles
- Multiple phones and emails per customer
- Emergency contact management
- Dive certification tracking (15+ agencies)
- Customer tagging system (VIP, Wholesale, etc.)
- Transaction history
- Passport and medical information
- Physical measurements for equipment fitting

#### Inventory Management
- Product catalog with categories
- Real-time stock tracking
- Barcode support
- Supplier management
- Low stock alerts
- Purchase order management

#### Cash Drawer System
- Open/close sessions with user tracking
- Bill and coin denomination counting
- Real-time JavaScript validation
- Automatic POS transaction recording
- Variance detection with required explanations
- Complete audit trail
- Session history and reports

#### Customer Tags
- Color-coded visual tags
- Custom icons (Bootstrap Icons)
- Quick templates (VIP, Wholesale, Instructor, etc.)
- Usage tracking
- Multiple tags per customer

#### Certification Tracking
- 15 pre-loaded dive agencies (PADI, SSI, NAUI, SDI, TDI, ERDI, PFI, BSAC, CMAS, GUE, IANTD, ACUC, IDA, PDIC, RAID)
- 20+ certification types
- Complete certification ladder (Open Water → Instructor)
- Specialty certifications
- Technical diving certifications
- Expiration tracking

#### Reports & Analytics
- Sales reports (daily, weekly, monthly)
- Customer analytics
- Inventory reports
- Cash variance reports
- Custom date ranges
- Export to CSV/PDF

#### User Management
- Role-based access control (Admin, Manager, Staff, Viewer)
- Granular permissions
- User activity logging
- Multiple staff accounts

---

## System Requirements

### Server Requirements
- **PHP:** 8.0 or higher
- **MySQL:** 8.0+ or MariaDB 10.5+
- **Web Server:** Apache 2.4+ or Nginx
- **SSL Certificate:** Recommended for production
- **Disk Space:** 500MB minimum
- **RAM:** 512MB minimum (2GB recommended)

### PHP Extensions Required
- PDO
- mysqli
- mbstring
- openssl
- json
- curl

### Browser Requirements (Client)
- Modern browser (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- Minimum 1024x768 resolution

---

## Installation

### Web-Based Installation (Recommended)

**Perfect for users WITHOUT command line access!**

This method works entirely through your web browser and is ideal for shared hosting, cPanel, Plesk, or any environment where you only have FTP access.

#### Step 1: Get the Files (30 seconds)

**Option A: Download from GitHub**
1. Go to: https://github.com/your-username/nautilus
2. Click green "Code" button
3. Click "Download ZIP"
4. Extract the ZIP file on your computer

**Option B: Receive from Provider**
Your provider will give you a `nautilus.zip` file. Extract it on your computer.

#### Step 2: Upload to Your Server (2 minutes)

**Using cPanel File Manager:**
1. Login to cPanel
2. Go to File Manager
3. Navigate to `public_html` (or your web directory)
4. Click "Upload"
5. Upload the entire nautilus folder
6. Verify files are in: `public_html/nautilus/`

**Using FTP (FileZilla, etc.):**
1. Connect to your server via FTP
2. Navigate to `public_html` or `www` folder
3. Upload the entire nautilus folder
4. Verify upload completed successfully

#### Step 3: Create Database (2 minutes)

**In cPanel:**
1. Find "MySQL Databases" icon
2. **Create New Database:**
   - Name: `nautilus` (or your choice)
   - Click "Create Database"

3. **Create New User:**
   - Username: `nautilus_user`
   - Password: [Generate strong password]
   - Click "Create User"

4. **Add User to Database:**
   - Select user: `nautilus_user`
   - Select database: `nautilus`
   - Check "ALL PRIVILEGES"
   - Click "Make Changes"

5. **Write down your credentials:**
   - Database Name: \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_
   - Username: \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_
   - Password: \_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_\_
   - Host: `localhost` (usually)

**In Plesk:**
1. Go to "Databases"
2. Click "Add Database"
3. Enter database name: `nautilus`
4. Create user and set password
5. Note the credentials

#### Step 4: Point Your Domain (1 minute)

Your domain needs to point to the `public` folder inside nautilus.

**Option A: Subdomain (Recommended)**
```
1. Create subdomain: store.yourdomain.com
2. Set document root to: /public_html/nautilus/public
```

**Option B: Main Domain**
```
Set document root to: /public_html/nautilus/public
```

#### Step 5: Run Web Installer (2 minutes)

**Visit in your browser:**
```
https://store.yourdomain.com/install
or
https://yourdomain.com/install
```

**The Wizard Process:**

**Page 1: Welcome**
- Check system requirements (all should be green ✅)
- Click "Next"

**Page 2: Database Setup**
- Database Host: `localhost`
- Database Name: (what you created)
- Username: (what you created)
- Password: (what you created)
- Click "Test Connection" → Should show ✅
- Click "Next"

**Page 3: Create Admin Account**
- First Name: Your name
- Last Name: Your last name
- Email: your@email.com
- Password: [min 8 characters]
- Confirm Password: [same password]
- Click "Next"

**Page 4: Company Information**
- Business Name: Your Dive Shop Name
- Timezone: Select your timezone
- Install Demo Data: ☐ (uncheck for production)
- Click "Install"

**Page 5: Installation Progress**

Watch the automated process:
```
✓ Creating database structure...
✓ Running migration 1/41...
✓ Running migration 2/41...
✓ Running migration 3/41...
... [continues automatically]
✓ Running migration 39/41... (Customer enhanced tables)
✓ Running migration 40/41... (Cash drawer system)
✓ Running migration 41/41... (Certification tracking)
✓ Seeding roles and permissions...
✓ Seeding certification agencies... (15 agencies loaded)
✓ Seeding certifications... (20+ cert types loaded)
✓ Seeding cash drawers... (3 drawers created)
✓ Seeding customer tags... (10 tags created)
✓ Creating admin user...
✓ Installation complete!
```

**Time:** 30-60 seconds

**Page 6: Success!**
```
Installation Complete! 🎉

Your Nautilus system is ready to use.

Login at: https://yourdomain.com/store/login
Email: your@email.com
```

Click "Go to Dashboard"

#### Step 6: First Login

1. Enter your email and password
2. Click "Login"
3. Welcome to your dashboard! 🎊

**That's it! Your system is fully installed and ready to use.**

---

### Command Line Installation

Alternative method for users with SSH access.

#### Quick Install
```bash
# Get the code
git clone https://github.com/your-username/nautilus.git
cd nautilus

# Install dependencies
composer install --no-dev --optimize-autoloader

# Configure
cp .env.example .env
nano .env  # Edit database credentials

# Create database
mysql -u root -p -e "CREATE DATABASE nautilus"

# Run web installer
# Visit: https://yourdomain.com/install
```

#### Manual SQL Installation

If web installer fails:

```bash
# Run migrations manually
mysql -u root -p nautilus < database/migrations/001_*.sql
mysql -u root -p nautilus < database/migrations/002_*.sql
# ... continue through all 41 migrations

# Run seeders
mysql -u root -p nautilus < database/seeders/certification_agencies.sql
mysql -u root -p nautilus < database/seeders/cash_drawers.sql

# Record migrations
mysql -u root -p nautilus -e "
INSERT INTO migrations (filename, status, executed_at) VALUES
('001_create_users_table.sql', 'completed', NOW()),
('002_create_roles_table.sql', 'completed', NOW())
... [all 41 migrations]
ON DUPLICATE KEY UPDATE status = 'completed';
"
```

---

## Updating Existing Installation

If you already have Nautilus installed and need to add the latest features (migrations 039-041):

### Method 1: Web Update (Coming Soon)

Visit: `https://yourdomain.com/update`

### Method 2: Command Line

```bash
cd /path/to/nautilus
php deploy-direct.php
```

This will:
- ✅ Run migrations 039, 040, 041 (if not already run)
- ✅ Seed certification agencies (if empty)
- ✅ Seed cash drawers and tags (if empty)
- ✅ Display summary

### Method 3: Manual SQL

```bash
mysql -u root -p nautilus < database/migrations/039_create_customer_enhanced_tables.sql
mysql -u root -p nautilus < database/migrations/040_create_cash_drawer_system.sql
mysql -u root -p nautilus < database/migrations/041_add_customer_certifications.sql

mysql -u root -p nautilus < database/seeders/certification_agencies.sql
mysql -u root -p nautilus < database/seeders/cash_drawers.sql
```

---

## Configuration

### Company Settings

After installation, configure via web interface:

**Dashboard → Settings → Company Settings**
- Upload logo
- Set business name and address
- Configure tax rates
- Set currency and number format
- Choose timezone
- Customize receipt format

### User Accounts

**Dashboard → Users → Add New User**
- Create staff accounts
- Assign roles (Admin, Manager, Staff, Viewer)
- Set permissions

### Cash Drawers

**Dashboard → Cash Drawer → Settings**
- Configure drawer locations
- Set starting float amounts
- Assign drawers to specific users/locations

### POS Settings

**Dashboard → Settings → POS**
- Configure receipt header/footer
- Set default payment methods
- Enable/disable barcode scanning
- Configure tax calculations

---

## User Guide

### Daily Operations

#### Opening a Cash Drawer

1. Go to: **Cash Drawer → Open Session**
2. Select drawer
3. Count starting cash:
   - Enter bill quantities ($100, $50, $20, $10, $5, $2, $1)
   - Enter coin quantities (Dollar, Quarter, Dime, Nickel, Penny)
4. System calculates total automatically
5. Verify total matches expected float
6. Click "Open Session"

#### Processing a Sale (POS)

1. Go to: **POS**
2. Scan products or search by name
3. Add items to cart
4. Enter customer (optional)
5. Select payment method:
   - **Cash** → Automatically records to open cash drawer
   - Credit/Debit → Manual entry
   - Check → Enter check number
6. Click "Complete Sale"
7. Print receipt

#### Closing a Cash Drawer

1. Go to: **Cash Drawer → My Open Session**
2. Click "Close Session"
3. Count ending cash (bills and coins)
4. System calculates:
   - Expected balance (starting + sales)
   - Actual balance (your count)
   - Variance (difference)
5. If variance > $1.00, explain reason
6. Click "Close Session"
7. Review variance report

### Customer Management

#### Adding a Customer

1. Go to: **Customers → Add New**
2. **Basic Info Tab:**
   - Name, gender, birth date
   - Primary address

3. **Contact Info Tab:**
   - Add multiple phones (mobile, home, work)
   - Add multiple emails (personal, work)
   - Add emergency contacts

4. **Travel Info Tab:**
   - Passport number and expiration
   - Medical conditions
   - Allergies
   - Physical measurements (for equipment)

5. **Tags Tab:**
   - Assign tags (VIP, Wholesale, etc.)
   - Add notes

6. **Certifications Tab:**
   - Add certifications
   - Select agency (PADI, SSI, etc.)
   - Enter cert number and date
   - Set expiration if applicable

7. Click "Save Customer"

#### Using Customer Tags

**Create Tag:**
1. **Customers → Tags → Create New**
2. Enter tag name
3. Choose color
4. Select icon (optional)
5. Add description
6. Click "Save"

**Quick Templates Available:**
- VIP (Gold)
- Wholesale (Blue)
- Instructor (Purple)
- New Customer (Green)
- Inactive (Gray)

**Assign to Customer:**
1. Open customer profile
2. Click "Tags" tab
3. Click "Assign Tag"
4. Select tag
5. Add notes (optional)
6. Click "Assign"

### Inventory Management

#### Adding Products

1. Go to: **Inventory → Products → Add New**
2. Enter product details:
   - Name, SKU, barcode
   - Category
   - Description
   - Cost and retail price
   - Quantity in stock
   - Reorder level
3. Upload product photos
4. Click "Save Product"

#### Tracking Stock

- **Low Stock Alerts** automatically notify when inventory below reorder level
- **Stock Adjustments** track changes (damaged, lost, found)
- **Reorder Reports** show what needs ordering

---

## Technical Documentation

### Database Schema

#### Major Tables

**Users & Authentication:**
- `users` - User accounts
- `roles` - User roles (admin, manager, staff)
- `permissions` - Permission definitions
- `role_permissions` - Role-permission mapping

**Customers:**
- `customers` - Core customer data
- `addresses` - Customer addresses
- `customer_phones` - Multiple phones per customer
- `customer_emails` - Multiple emails per customer
- `customer_contacts` - Emergency contacts
- `customer_tags` - Tag definitions
- `customer_tag_assignments` - Tags assigned to customers

**Certifications:**
- `certification_agencies` - Dive certification agencies
- `certifications` - Certification types
- `customer_certifications` - Customer cert assignments

**Cash Management:**
- `cash_drawers` - Physical drawer definitions
- `cash_drawer_sessions` - Open/close sessions
- `cash_drawer_transactions` - Individual transactions
- `cash_drawer_sessions_open` (view) - Currently open sessions
- `cash_drawer_session_summary` (view) - Session statistics

**POS & Sales:**
- `transactions` - Sale transactions
- `transaction_items` - Line items
- `payments` - Payment records

**Inventory:**
- `products` - Product catalog
- `categories` - Product categories
- `stock_movements` - Inventory changes

**Total:** 80+ tables

### API Endpoints

#### Authentication
```
POST   /store/login           - User login
POST   /store/logout          - User logout
```

#### Customers
```
GET    /store/customers       - List customers
GET    /store/customers/{id}  - View customer
POST   /store/customers       - Create customer
PUT    /store/customers/{id}  - Update customer
DELETE /store/customers/{id}  - Delete customer
```

#### POS
```
GET    /store/pos             - POS interface
POST   /store/pos/transaction - Create transaction
```

#### Cash Drawer
```
GET    /store/cash-drawer                  - Dashboard
POST   /store/cash-drawer/open             - Open session
POST   /store/cash-drawer/session/{id}/close - Close session
GET    /store/cash-drawer/history          - Session history
```

### File Structure

```
nautilus/
├── app/
│   ├── Controllers/       # Request handlers
│   │   ├── Admin/         # Admin controllers
│   │   ├── Auth/          # Authentication
│   │   ├── POS/           # Point of sale
│   │   └── CRM/           # Customer management
│   ├── Models/            # Database models
│   ├── Services/          # Business logic
│   │   ├── POS/
│   │   ├── CRM/
│   │   └── Install/
│   ├── Middleware/        # Request filters
│   └── Views/             # HTML templates
│       ├── layouts/       # Page layouts
│       ├── customers/     # Customer views
│       ├── cash_drawer/   # Cash drawer views
│       └── pos/           # POS views
├── config/                # Configuration
│   └── database.php
├── database/
│   ├── migrations/        # 41 SQL migration files
│   └── seeders/           # Default data
│       ├── certification_agencies.sql
│       └── cash_drawers.sql
├── public/                # Web root
│   ├── assets/            # CSS, JS, images
│   ├── index.php          # Entry point
│   └── .htaccess          # Apache config
├── routes/
│   └── web.php            # URL routes
├── storage/               # Writable storage
│   ├── logs/              # Error logs
│   ├── cache/             # Cache files
│   └── sessions/          # Session data
├── vendor/                # Composer dependencies
├── .env.example           # Environment template
├── .env                   # Environment config (gitignored)
├── composer.json          # PHP dependencies
└── DOCUMENTATION.md       # This file
```

### Security Features

- **Password Hashing:** bcrypt with 10 rounds
- **CSRF Protection:** Tokens on all forms
- **SQL Injection Prevention:** PDO prepared statements
- **XSS Protection:** Output escaping
- **Session Security:** HttpOnly, Secure flags
- **Role-Based Access:** Granular permissions
- **Audit Logging:** All user actions logged
- **File Upload Validation:** Type and size checks

---

## Troubleshooting

### Installation Issues

#### "Can't Connect to Database"

**Symptoms:** Installation fails at database connection test

**Solutions:**
1. Verify database credentials are correct
2. Check database user has privileges:
   ```sql
   GRANT ALL PRIVILEGES ON nautilus.* TO 'user'@'localhost';
   FLUSH PRIVILEGES;
   ```
3. Try `127.0.0.1` instead of `localhost` for host
4. Ensure MySQL is running
5. Check firewall isn't blocking port 3306

#### "White Screen" or "500 Error"

**Symptoms:** Blank page or internal server error

**Solutions:**
1. Check PHP version: `php -v` (needs 8.0+)
2. Enable error display temporarily:
   ```php
   // In public/index.php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Check Apache/Nginx error logs
4. Verify file permissions:
   ```bash
   chmod 755 nautilus/
   chmod 755 storage/
   chmod 755 storage/logs/
   ```
5. Check `.htaccess` file exists in `public/`

#### "Migration Failed"

**Symptoms:** Installation stops during migration process

**Solutions:**
1. Check which migration failed (shown in error message)
2. Check error logs: `storage/logs/`
3. Manually run failed migration:
   ```bash
   mysql -u root -p nautilus < database/migrations/XXX_*.sql
   ```
4. Check MySQL user has CREATE, ALTER, DROP privileges
5. Verify MySQL version (needs 8.0+)

#### "Composer Dependencies Missing"

**Symptoms:** Errors about missing vendor folder

**Solutions:**
1. Run: `composer install`
2. If composer not installed:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install
   ```
3. Or download pre-packaged version with vendor/ included

### Runtime Issues

#### "Session Expired" Frequently

**Solutions:**
1. Check session storage directory is writable:
   ```bash
   chmod 755 storage/sessions/
   ```
2. Increase session lifetime in `.env`:
   ```
   SESSION_LIFETIME=240
   ```
3. Check server time is correct

#### "Cash Drawer Not Recording POS Sales"

**Symptoms:** POS sales don't appear in cash drawer

**Solutions:**
1. Verify migration 040 completed:
   ```sql
   SELECT * FROM cash_drawers LIMIT 1;
   ```
2. Check user has open cash drawer session
3. Verify payment method is set to "cash"
4. Check error logs for issues

#### "Tags Not Showing on Customer Profile"

**Solutions:**
1. Verify migration 039 completed:
   ```sql
   SELECT * FROM customer_tags LIMIT 1;
   ```
2. Clear browser cache
3. Check CustomerService.php includes tag fetching

---

## Support

### Documentation
- **This Guide:** Complete reference
- **GitHub:** https://github.com/your-username/nautilus
- **Issues:** Report bugs on GitHub Issues

### Community Support
- **Email:** support@yourdomain.com
- **Discord:** [Link to Discord]
- **Forum:** [Link to Forum]

### Professional Support
- **Installation Service:** $299 (we do it for you)
- **Training:** Available for staff
- **Custom Development:** Contact for quote
- **Priority Support:** Available via support plans

### Getting Help

**When Reporting Issues:**
1. Describe what you're trying to do
2. Describe what actually happens
3. Include error messages (exact text)
4. Include screenshots if relevant
5. List your PHP and MySQL versions
6. Mention your hosting provider

**Before Asking for Help:**
1. Check this documentation
2. Search GitHub Issues
3. Check error logs: `storage/logs/`
4. Try with browser console open (F12)

---

## Appendix

### Default Data Loaded

#### Certification Agencies (15)
1. PADI - Professional Association of Diving Instructors
2. SSI - Scuba Schools International
3. NAUI - National Association of Underwater Instructors
4. SDI - Scuba Diving International
5. TDI - Technical Diving International
6. ERDI - Emergency Response Diving International
7. PFI - Performance Freediving International
8. BSAC - British Sub-Aqua Club
9. CMAS - Confédération Mondiale des Activités Subaquatiques
10. GUE - Global Underwater Explorers
11. IANTD - International Association of Nitrox and Technical Divers
12. ACUC - American Canadian Underwater Certification
13. IDA - International Diving Association
14. PDIC - Professional Diving Instructors Corporation
15. RAID - Rebreather Association of International Divers

#### Certification Types (20+)
- Open Water Diver
- Advanced Open Water Diver
- Rescue Diver
- Divemaster
- Assistant Instructor
- Open Water Scuba Instructor
- Enriched Air (Nitrox)
- Deep Diver
- Wreck Diver
- Night Diver
- Underwater Navigator
- Peak Performance Buoyancy
- Dry Suit Diver
- Search and Recovery Diver
- Underwater Photographer
- Decompression Procedures
- Extended Range
- Trimix
- Cave Diver
- Ice Diver

#### Cash Drawers (3)
1. Main Register - $200 starting float
2. Pool Register - $150 starting float
3. Boat Register - $100 starting float

#### Customer Tags (10)
1. VIP - Gold color
2. Wholesale - Blue color
3. Instructor - Purple color
4. New Customer - Green color
5. Inactive - Gray color
6. Corporate - Navy color
7. Newsletter Subscriber - Teal color
8. Referral - Orange color
9. Certification Due - Red color
10. Equipment Rental - Brown color

### Keyboard Shortcuts

**POS Screen:**
- `F2` - Open search
- `F5` - Refresh
- `F9` - Hold transaction
- `F12` - Complete sale
- `Esc` - Clear cart

**General:**
- `Ctrl+S` - Save (on forms)
- `Ctrl+/` - Search customers
- `Alt+H` - Go home (dashboard)

### Default User Roles

**Admin:**
- Full system access
- User management
- Settings configuration
- Financial reports
- Database backup/restore

**Manager:**
- Customer management
- Inventory management
- POS operations
- Cash drawer management
- Reports (non-financial)

**Staff:**
- POS operations
- Customer lookup (view only)
- Open/close cash drawer
- Process sales

**Viewer:**
- Read-only access
- View customers
- View inventory
- View reports
- No modifications

---

## Changelog

### Version 2.0 (Current)
- ✅ Added cash drawer management system
- ✅ Added customer tagging system
- ✅ Enhanced customer profiles (phones, emails, contacts)
- ✅ Added certification agency database
- ✅ Added automatic data seeding
- ✅ Added web-based installation
- ✅ Improved POS integration
- ✅ Enhanced dashboard metrics

### Version 1.0
- Initial release
- Basic POS functionality
- Customer management
- Inventory system
- User roles and permissions

---

## License

Proprietary - All Rights Reserved

---

## Credits

**Built With:**
- PHP 8.2
- MySQL 8.0
- Bootstrap 5.3
- Bootstrap Icons
- Font Awesome
- Composer

**Developed By:**
[Your Company Name]

**Contact:**
- Website: [Your Website]
- Email: [Your Email]
- Support: [Support Email]

---

**Document Version:** 2.0
**Last Updated:** 2024
**Status:** ✅ Production Ready

**For the latest version of this documentation, visit:**
https://github.com/your-username/nautilus/blob/main/DOCUMENTATION.md
