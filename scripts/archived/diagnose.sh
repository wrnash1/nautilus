#!/bin/bash

echo "=========================================="
echo "  Nautilus Installation Diagnostics"
echo "=========================================="
echo ""

echo "1. Checking public directory..."
ls -la /var/www/html/nautilus/public/ | head -20
echo ""

echo "2. Checking install.php..."
if [ -f /var/www/html/nautilus/public/install.php ]; then
    echo "✓ install.php exists"
    ls -lZ /var/www/html/nautilus/public/install.php
else
    echo "✗ install.php NOT FOUND"
fi
echo ""

echo "3. Checking Apache configuration..."
cat /etc/httpd/conf.d/nautilus.conf
echo ""

echo "4. Checking Apache status..."
systemctl status httpd --no-pager | head -20
echo ""

echo "5. Checking Apache error log..."
echo "Last 10 lines of error log:"
sudo tail -10 /var/log/httpd/error_log
echo ""

echo "6. Testing PHP..."
php -v
echo ""

echo "7. Checking if install.php is accessible..."
curl -I http://localhost/install.php 2>&1 | head -10
echo ""
