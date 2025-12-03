#!/bin/bash
###############################################################################
# Fix Apache VirtualHost Conflict
###############################################################################

echo "=========================================="
echo "  Fixing VirtualHost Conflict"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

echo "1. Checking conflicting config..."
cat /etc/httpd/conf.d/00-default.conf
echo ""

echo "2. Backing up 00-default.conf..."
cp /etc/httpd/conf.d/00-default.conf /etc/httpd/conf.d/00-default.conf.backup
echo "✓ Backed up to 00-default.conf.backup"
echo ""

echo "3. Renaming nautilus.conf to 00-nautilus.conf (so it loads first)..."
mv /etc/httpd/conf.d/nautilus.conf /etc/httpd/conf.d/00-nautilus.conf
echo "✓ Renamed"
echo ""

echo "4. Disabling the conflicting default config..."
mv /etc/httpd/conf.d/00-default.conf /etc/httpd/conf.d/00-default.conf.disabled
echo "✓ Disabled 00-default.conf"
echo ""

echo "5. Testing Apache configuration..."
httpd -t
if [ $? -eq 0 ]; then
    echo "✓ Configuration is valid"
else
    echo "✗ Configuration error - restoring backup"
    mv /etc/httpd/conf.d/00-default.conf.disabled /etc/httpd/conf.d/00-default.conf
    mv /etc/httpd/conf.d/00-nautilus.conf /etc/httpd/conf.d/nautilus.conf
    exit 1
fi
echo ""

echo "6. Restarting Apache..."
systemctl restart httpd
echo "✓ Apache restarted"
echo ""

echo "7. Verifying configuration..."
httpd -S 2>&1 | grep -A 2 "VirtualHost"
echo ""

echo "=========================================="
echo "  Fix Complete!"
echo "=========================================="
echo ""
echo "Now try accessing:"
echo "  http://localhost/install.php"
echo ""
