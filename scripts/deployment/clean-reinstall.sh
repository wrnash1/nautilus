#!/bin/bash
###############################################################################
# Clean Reinstall Script
# Removes old installation and prepares for fresh install
###############################################################################

echo "=========================================="
echo "  Nautilus Clean Reinstall"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

echo "⚠️  WARNING: This will delete your existing installation!"
echo ""
read -p "Are you sure you want to continue? (type 'yes' to confirm): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Cancelled."
    exit 0
fi

echo ""
echo "1. Stopping Apache..."
systemctl stop httpd 2>/dev/null || systemctl stop apache2 2>/dev/null
echo "✓ Apache stopped"
echo ""

echo "2. Backing up database (if exists)..."
if mysql -e "use nautilus" 2>/dev/null; then
    mkdir -p /home/wrnash1/Developer/nautilus/backups
    mysqldump nautilus > /home/wrnash1/Developer/nautilus/backups/nautilus_backup_$(date +%Y%m%d_%H%M%S).sql 2>/dev/null
    echo "✓ Database backed up to backups/ folder"
else
    echo "ℹ No existing database found"
fi
echo ""

echo "3. Removing old web installation..."
rm -rf /var/www/html/nautilus
echo "✓ Old web files removed"
echo ""

echo "4. Dropping existing database (if exists)..."
mysql -e "DROP DATABASE IF EXISTS nautilus;" 2>/dev/null
echo "✓ Database dropped"
echo ""

echo "5. Creating fresh directory structure..."
mkdir -p /var/www/html/nautilus
echo "✓ Directory created"
echo ""

echo "6. Copying fresh files from development..."
cp -r /home/wrnash1/Developer/nautilus/* /var/www/html/nautilus/
echo "✓ Files copied"
echo ""

echo "7. Setting proper ownership..."
chown -R apache:apache /var/www/html/nautilus
echo "✓ Ownership set"
echo ""

echo "8. Setting proper permissions..."
chmod -R 755 /var/www/html/nautilus
chmod -R 775 /var/www/html/nautilus/storage
chmod 775 /var/www/html/nautilus
echo "✓ Permissions set"
echo ""

echo "9. Configuring SELinux (if active)..."
if command -v setenforce &> /dev/null && [ "$(getenforce 2>/dev/null)" != "Disabled" ]; then
    semanage fcontext -a -t httpd_sys_content_t "/var/www/html/nautilus(/.*)?" 2>/dev/null || true
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?" 2>/dev/null || true
    restorecon -Rv /var/www/html/nautilus 2>/dev/null || true
    setsebool -P httpd_can_network_connect_db on 2>/dev/null || true
    echo "✓ SELinux configured"
else
    echo "ℹ SELinux not active"
fi
echo ""

echo "10. Recreating database..."
mysql -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "✓ Fresh database created"
echo ""

echo "11. Starting Apache..."
systemctl start httpd 2>/dev/null || systemctl start apache2 2>/dev/null
echo "✓ Apache started"
echo ""

echo "=========================================="
echo "  Clean Reinstall Complete!"
echo "=========================================="
echo ""
echo "✅ Ready for fresh installation!"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Open your browser"
echo "2. Visit: https://nautilus.local/install.php"
echo "3. Follow the 6-step wizard:"
echo "   • Step 1: Requirements check (auto-fixes issues)"
echo "   • Step 2: Application settings (name, company, timezone)"
echo "   • Step 3: Database config (host, user, password)"
echo "   • Step 4: Automatic table creation"
echo "   • Step 5: Create admin account"
echo "   • Step 6: Done!"
echo ""
echo "🎯 The web installer will guide you through everything!"
echo ""
echo "💾 Backup location (if existed):"
echo "   /home/wrnash1/Developer/nautilus/backups/"
echo ""
echo "📖 Need help? Check INSTALLATION_GUIDE.md"
echo ""
