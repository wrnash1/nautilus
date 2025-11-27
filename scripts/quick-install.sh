#!/bin/bash
# Quick Installation Script for Nautilus
# Run: sudo bash quick-install.sh

set -e

echo "=== Nautilus Quick Installation ==="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root: sudo bash quick-install.sh"
    exit 1
fi

# 1. Copy files
echo "[1/6] Copying files to /var/www/html/nqtilus..."
if [ ! -d "/home/wrnash1/Developer/nautilus" ]; then
    echo "ERROR: Source directory not found"
    exit 1
fi

cp -r /home/wrnash1/Developer/nautilus /var/www/html/nqtilus/
echo "✓ Files copied"

# 2. Set ownership
echo "[2/6] Setting file ownership..."
if command -v apachectl &> /dev/null; then
    chown -R apache:apache /var/www/html/nqtilus
elif command -v apache2ctl &> /dev/null; then
    chown -R www-data:www-data /var/www/html/nqtilus
fi
echo "✓ Ownership set"

# 3. Set permissions
echo "[3/6] Setting file permissions..."
chmod -R 755 /var/www/html/nqtilus
chmod -R 775 /var/www/html/nqtilus/storage
chmod -R 775 /var/www/html/nqtilus/public/uploads
chmod -R 775 /var/www/html/nqtilus/logs
echo "✓ Permissions set"

# 4. Configure SELinux
echo "[4/6] Configuring SELinux..."
if command -v getenforce &> /dev/null && [ "$(getenforce)" != "Disabled" ]; then
    bash /var/www/html/nqtilus/scripts/selinux-setup.sh
    echo "✓ SELinux configured"
else
    echo "⚠ SELinux is disabled or not installed"
fi

# 5. Restart Apache
echo "[5/6] Restarting Apache..."
if command -v systemctl &> /dev/null; then
    systemctl restart httpd 2>/dev/null || systemctl restart apache2 2>/dev/null
    echo "✓ Apache restarted"
else
    service httpd restart 2>/dev/null || service apache2 restart 2>/dev/null
    echo "✓ Apache restarted"
fi

# 6. Display next steps
echo "[6/6] Installation complete!"
echo ""
echo "=== Next Steps ==="
echo ""
echo "1. Open your browser and visit:"
echo "   http://nautilus.local/install/"
echo ""
echo "2. Or if using IP address:"
echo "   http://$(hostname -I | awk '{print $1}')/nqtilus/public/install/"
echo ""
echo "3. Follow the web installer to complete setup"
echo ""
echo "✓ Nautilus is ready to install!"
