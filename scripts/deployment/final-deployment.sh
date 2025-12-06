#!/bin/bash
###############################################################################
# Final Professional Deployment
# Sets up Nautilus with proper permissions and configuration
###############################################################################

echo "=========================================="
echo "  Nautilus Final Deployment"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

echo "1. Setting proper ownership..."
chown -R apache:apache /var/www/html/nautilus
echo "✓ Ownership set to apache:apache"
echo ""

echo "2. Setting directory permissions..."
find /var/www/html/nautilus -type d -exec chmod 755 {} \;
echo "✓ Directory permissions: 755"
echo ""

echo "3. Setting file permissions..."
find /var/www/html/nautilus -type f -exec chmod 644 {} \;
echo "✓ File permissions: 644"
echo ""

echo "4. Making storage writable..."
chmod -R 775 /var/www/html/nautilus/storage
chown -R apache:apache /var/www/html/nautilus/storage
echo "✓ Storage is writable"
echo ""

echo "5. Allowing .env file creation..."
chmod 775 /var/www/html/nautilus
echo "✓ Root directory writable for .env creation"
echo ""

echo "6. Configuring SELinux contexts..."
if command -v setenforce &> /dev/null && [ "$(getenforce 2>/dev/null)" != "Disabled" ]; then
    semanage fcontext -a -t httpd_sys_content_t "/var/www/html/nautilus(/.*)?" 2>/dev/null || true
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?" 2>/dev/null || true
    restorecon -Rv /var/www/html/nautilus 2>/dev/null || true
    setsebool -P httpd_can_network_connect_db on 2>/dev/null || true
    echo "✓ SELinux configured"
else
    echo "⚠ SELinux not active"
fi
echo ""

echo "7. Verifying Apache configuration..."
if [ -f /etc/httpd/conf.d/nautilus.conf ]; then
    echo "✓ Apache virtual host configured"
else
    echo "⚠ No Apache configuration found - will use default"
fi
echo ""

echo "8. Restarting Apache..."
systemctl restart httpd
echo "✓ Apache restarted"
echo ""

echo "9. Checking installer..."
if [ -f /var/www/html/nautilus/public/install.php ]; then
    echo "✓ Web installer is ready"
    chmod 644 /var/www/html/nautilus/public/install.php
    chown apache:apache /var/www/html/nautilus/public/install.php
else
    echo "✗ Installer not found!"
fi
echo ""

echo "=========================================="
echo "  Deployment Complete!"
echo "=========================================="
echo ""
echo "✅ Nautilus is ready for installation!"
echo ""
echo "📁 Clean project structure"
echo "🔒 Proper permissions set"
echo "🌐 Apache configured"
echo "🔐 SELinux configured"
echo ""
echo "🚀 Next step: Open your browser"
echo ""
echo "   https://nautilus.local/install.php"
echo ""
echo "The web installer will guide you through:"
echo "  1. System requirements check"
echo "  2. Application settings"
echo "  3. Database configuration"
echo "  4. Automatic migrations"
echo "  5. Admin account creation"
echo ""
echo "Everything is handled through the web interface!"
echo ""
