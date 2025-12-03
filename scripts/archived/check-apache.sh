#!/bin/bash

echo "=========================================="
echo "  Apache Configuration Diagnostics"
echo "=========================================="
echo ""

echo "1. Active Apache virtual hosts:"
httpd -S 2>&1 | grep -A 2 "VirtualHost"
echo ""

echo "2. All .conf files in conf.d:"
ls -la /etc/httpd/conf.d/*.conf
echo ""

echo "3. Main httpd.conf DocumentRoot:"
grep -n "^DocumentRoot" /etc/httpd/conf/httpd.conf
echo ""

echo "4. Testing direct file access:"
echo "   Checking if file is readable by apache user..."
sudo -u apache cat /var/www/html/nautilus/public/install.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "   ✓ Apache user CAN read install.php"
else
    echo "   ✗ Apache user CANNOT read install.php"
fi
echo ""

echo "5. Testing URL with curl (detailed):"
curl -v http://localhost/install.php 2>&1 | head -30
echo ""

echo "6. Apache error log (last 20 lines):"
sudo tail -20 /var/log/httpd/error_log
echo ""

echo "7. Nautilus error log:"
if [ -f /var/log/httpd/nautilus_error.log ]; then
    sudo tail -20 /var/log/httpd/nautilus_error.log
else
    echo "   (No nautilus_error.log found yet)"
fi
echo ""

echo "8. Checking what Apache sees in DocumentRoot:"
echo "   Files in /var/www/html/nautilus/public/:"
ls -la /var/www/html/nautilus/public/*.php | head -10
echo ""
