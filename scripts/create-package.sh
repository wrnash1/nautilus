#!/bin/bash

# Nautilus Complete Package Creator
# This script creates a ready-to-deploy package WITH vendor folder
# Perfect for deploying to servers without command line access

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Nautilus Package Creator${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found!${NC}"
    echo "Please run this script from the nautilus directory."
    exit 1
fi

# Step 1: Install/update composer dependencies
echo -e "${YELLOW}Step 1: Installing Composer dependencies...${NC}"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ Composer dependencies installed${NC}\n"
else
    echo -e "${RED}✗ Composer not found!${NC}"
    echo "Please install composer first:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  sudo mv composer.phar /usr/local/bin/composer"
    exit 1
fi

# Step 2: Check if vendor folder exists
echo -e "${YELLOW}Step 2: Checking vendor folder...${NC}"
if [ -d "vendor" ]; then
    VENDOR_SIZE=$(du -sh vendor | cut -f1)
    echo -e "${GREEN}✓ vendor/ folder exists (${VENDOR_SIZE})${NC}\n"
else
    echo -e "${RED}✗ vendor/ folder missing!${NC}"
    echo "Run: composer install"
    exit 1
fi

# Step 3: Clean up temporary files
echo -e "${YELLOW}Step 3: Cleaning temporary files...${NC}"
rm -f storage/logs/*.log 2>/dev/null || true
echo -e "${GREEN}✓ Temporary files cleaned${NC}\n"

# Step 4: Create package directory
PACKAGE_NAME="nautilus-deploy-$(date +%Y%m%d-%H%M%S)"
PACKAGE_DIR="/tmp/${PACKAGE_NAME}"

echo -e "${YELLOW}Step 4: Creating package: ${PACKAGE_NAME}${NC}"

# Create output directory
OUTPUT_DIR="$(pwd)/packages"
mkdir -p "$OUTPUT_DIR"

# Step 5: Create ZIP package
echo -e "${YELLOW}Step 5: Creating ZIP archive...${NC}"

ZIP_FILE="${OUTPUT_DIR}/${PACKAGE_NAME}.zip"

zip -r "$ZIP_FILE" . \
    -x "*.git*" \
    -x "*node_modules*" \
    -x "storage/logs/*" \
    -x ".env" \
    -x "packages/*" \
    -x "*.tar.gz" \
    -x "*.zip" \
    -x "create-package.sh" \
    -q

if [ -f "$ZIP_FILE" ]; then
    ZIP_SIZE=$(du -h "$ZIP_FILE" | cut -f1)
    echo -e "${GREEN}✓ ZIP created: ${ZIP_FILE} (${ZIP_SIZE})${NC}\n"
else
    echo -e "${RED}✗ Failed to create ZIP${NC}"
    exit 1
fi

# Step 6: Create TAR.GZ package (alternative format)
echo -e "${YELLOW}Step 6: Creating TAR.GZ archive...${NC}"

TAR_FILE="${OUTPUT_DIR}/${PACKAGE_NAME}.tar.gz"

tar -czf "$TAR_FILE" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='.env' \
    --exclude='packages' \
    --exclude='*.tar.gz' \
    --exclude='*.zip' \
    --exclude='create-package.sh' \
    .

if [ -f "$TAR_FILE" ]; then
    TAR_SIZE=$(du -h "$TAR_FILE" | cut -f1)
    echo -e "${GREEN}✓ TAR.GZ created: ${TAR_FILE} (${TAR_SIZE})${NC}\n"
else
    echo -e "${RED}✗ Failed to create TAR.GZ${NC}"
fi

# Step 7: Create installation instructions
INSTRUCTIONS_FILE="${OUTPUT_DIR}/${PACKAGE_NAME}-INSTRUCTIONS.txt"

cat > "$INSTRUCTIONS_FILE" << 'EOF'
================================================================================
  NAUTILUS DEPLOYMENT INSTRUCTIONS
  For Servers WITHOUT Command Line Access
================================================================================

PACKAGE CONTENTS:
✓ Complete application code
✓ Vendor folder (Composer dependencies) - INCLUDED!
✓ All database migrations (001-041)
✓ Seeders (certification agencies, cash drawers, tags)
✓ Web installer

WHAT YOU NEED:
- Web hosting with PHP 8.0+, MySQL 8.0+
- FTP access or cPanel File Manager
- Ability to create MySQL database

================================================================================
INSTALLATION STEPS
================================================================================

1. UPLOAD FILES
   -------------
   Via FTP or cPanel File Manager:
   - Upload the ZIP or TAR.GZ file to your web server
   - Extract to: /var/www/html/nautilus
     (or wherever your web root is)

   cPanel: Use "Extract" button after uploading
   FTP: Extract locally, then upload the nautilus folder

2. CREATE DATABASE
   ---------------
   Via cPanel or hosting control panel:
   - Create new MySQL database
     Example name: nautilus_db

   - Create database user
     Example: nautilus_user

   - Set strong password

   - Grant ALL PRIVILEGES to user on database

   WRITE DOWN:
   - Database name: _________________
   - Username: _____________________
   - Password: _____________________
   - Host: localhost (usually)

3. CREATE .ENV FILE
   ----------------
   In the nautilus folder:
   - Copy .env.example to .env
   - Edit .env file
   - Set database credentials:

     DB_HOST=localhost
     DB_DATABASE=nautilus_db
     DB_USERNAME=nautilus_user
     DB_PASSWORD=your_password_here

4. SET PERMISSIONS (if possible)
   -----------------------------
   If your hosting panel allows:
   - storage/ folder → 775
   - storage/logs/ → 775
   - storage/cache/ → 775
   - storage/sessions/ → 775

5. POINT DOMAIN TO PUBLIC FOLDER
   ------------------------------
   Set your domain's document root to:
   /var/www/html/nautilus/public

   NOT just /var/www/html/nautilus

   This is important for security!

6. RUN WEB INSTALLER
   ------------------
   Visit in your browser:
   https://yourdomain.com/install

   Follow the 5-screen wizard:

   Screen 1: System Requirements Check
   - Should see all green checkmarks

   Screen 2: Database Configuration
   - Enter credentials from step 2
   - Click "Test Connection"

   Screen 3: Admin Account
   - Enter your name
   - Enter admin email
   - Create password (min 8 chars)

   Screen 4: Company Information
   - Enter your business name
   - Select timezone
   - Demo data: unchecked

   Screen 5: Installation Progress
   - Watch as it runs all 41 migrations
   - Seeds certification agencies
   - Seeds cash drawers and tags
   - Creates your admin account
   - Takes 30-60 seconds

   Screen 6: Complete!
   - Click "Go to Dashboard"
   - Login with your credentials

7. VERIFY INSTALLATION
   -------------------
   Check these URLs work:
   ✓ https://yourdomain.com/store/login
   ✓ https://yourdomain.com/store/dashboard
   ✓ https://yourdomain.com/store/pos
   ✓ https://yourdomain.com/store/customers
   ✓ https://yourdomain.com/store/cash-drawer

8. DELETE TEMPORARY FILES
   ----------------------
   For security, delete:
   - /public/test.php (if exists)
   - /public/phpinfo.php (if exists)
   - /deploy-direct.php

================================================================================
TROUBLESHOOTING
================================================================================

Problem: "This page isn't working" or 500 error
Solution:
  - Check .env file exists and has correct database credentials
  - Verify vendor/ folder exists (should be ~50MB)
  - Check storage/ folder is writable (775 permissions)
  - Ensure document root points to /public folder

Problem: "Database connection failed"
Solution:
  - Verify database exists
  - Check username has privileges
  - Try host as '127.0.0.1' instead of 'localhost'

Problem: "Blank white page"
Solution:
  - Check PHP version is 8.0+
  - Verify all files uploaded correctly
  - Check Apache/Nginx error logs

Problem: Can't access /install
Solution:
  - Verify .htaccess file exists in public/ folder
  - Check mod_rewrite is enabled (Apache)
  - Try: https://yourdomain.com/index.php (if routes work)

Problem: "Composer dependencies missing"
Solution:
  - You're using the wrong package!
  - Use the one created by create-package.sh
  - It includes vendor/ folder already

================================================================================
WHAT GETS INSTALLED
================================================================================

When installation completes, you'll have:

DATABASE:
✓ 80+ tables (complete schema)
✓ 15 certification agencies (PADI, SSI, NAUI, SDI, TDI, etc.)
✓ 20+ certification types (Open Water → Instructor)
✓ 3 cash drawers (Main Register, Pool, Boat)
✓ 10 customer tags (VIP, Wholesale, Instructor, etc.)
✓ Roles and permissions
✓ Your admin user account

FEATURES READY TO USE:
✓ Point of Sale (POS)
✓ Customer Management (CRM)
✓ Inventory Management
✓ Cash Drawer System
✓ Customer Tags
✓ Certification Tracking
✓ Reports & Analytics
✓ User Management

================================================================================
SUPPORT
================================================================================

Documentation: See DOCUMENTATION.md included in package
Email: support@yourdomain.com
Issues: Report bugs on GitHub

Need help? Contact us for professional installation service.

================================================================================

Package created: $(date)
Ready for deployment!

================================================================================
EOF

echo -e "${GREEN}✓ Instructions created: ${INSTRUCTIONS_FILE}${NC}\n"

# Step 8: Create a simple README
README_FILE="${OUTPUT_DIR}/${PACKAGE_NAME}-README.md"

cat > "$README_FILE" << 'EOF'
# Nautilus Complete Deployment Package

## Quick Start

1. Extract this package to your web server
2. Create MySQL database and user
3. Copy `.env.example` to `.env` and configure
4. Point domain to `/public` folder
5. Visit `https://yourdomain.com/install`
6. Follow the wizard!

## Contents

- ✅ Complete application code
- ✅ Vendor folder (dependencies included!)
- ✅ All 41 database migrations
- ✅ Data seeders (agencies, certs, drawers, tags)
- ✅ Web installer

## Requirements

- PHP 8.0+
- MySQL 8.0+
- 500MB disk space
- SSL certificate (recommended)

## Full Instructions

See: [PACKAGE_NAME]-INSTRUCTIONS.txt

## Support

For detailed documentation, see DOCUMENTATION.md in the package.
EOF

echo -e "${GREEN}✓ README created: ${README_FILE}${NC}\n"

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Package Created Successfully!${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "${GREEN}Package Location:${NC}"
echo -e "  ${OUTPUT_DIR}/"
echo ""
echo -e "${GREEN}Files Created:${NC}"
echo -e "  1. ${PACKAGE_NAME}.zip (${ZIP_SIZE})"
echo -e "  2. ${PACKAGE_NAME}.tar.gz (${TAR_SIZE})"
echo -e "  3. ${PACKAGE_NAME}-INSTRUCTIONS.txt"
echo -e "  4. ${PACKAGE_NAME}-README.md"
echo ""

echo -e "${GREEN}What's Included:${NC}"
echo -e "  ✓ Complete application code"
echo -e "  ✓ Vendor folder (Composer dependencies)"
echo -e "  ✓ Database migrations (all 41)"
echo -e "  ✓ Seeders (agencies, certs, drawers, tags)"
echo -e "  ✓ Web installer"
echo ""

echo -e "${YELLOW}Next Steps:${NC}"
echo -e "  1. Transfer ZIP or TAR.GZ to new server"
echo -e "  2. Include the INSTRUCTIONS.txt file"
echo -e "  3. Follow the instructions to deploy"
echo ""

echo -e "${BLUE}Ready to deploy! 🚀${NC}\n"
