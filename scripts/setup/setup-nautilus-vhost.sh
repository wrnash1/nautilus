#!/bin/bash
###############################################################################
# Setup Nautilus on Separate Virtual Host
# This will NOT affect your other projects on localhost
###############################################################################

echo "=========================================="
echo "  Nautilus Virtual Host Setup"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

DOMAIN="nautilus.local"

echo "Setting up Nautilus on: $DOMAIN"
echo ""

echo "1. Creating new VirtualHost config..."
cat > /etc/httpd/conf.d/nautilus.conf <<EOF
# Nautilus Virtual Host
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/nautilus_error.log
    CustomLog /var/log/httpd/nautilus_access.log combined
</VirtualHost>
EOF
echo "✓ VirtualHost config created"
echo ""

echo "2. Adding $DOMAIN to /etc/hosts..."
# Check if entry already exists
if grep -q "127.0.0.1.*$DOMAIN" /etc/hosts; then
    echo "✓ Entry already exists in /etc/hosts"
else
    echo "127.0.0.1   $DOMAIN www.$DOMAIN" >> /etc/hosts
    echo "✓ Added to /etc/hosts"
fi
echo ""

echo "3. Testing Apache configuration..."
httpd -t
if [ $? -eq 0 ]; then
    echo "✓ Configuration is valid"
else
    echo "✗ Configuration error"
    exit 1
fi
echo ""

echo "4. Restarting Apache..."
systemctl restart httpd
echo "✓ Apache restarted"
echo ""

echo "5. Verifying virtual hosts..."
httpd -S 2>&1 | grep -E "VirtualHost|port 80"
echo ""

echo "=========================================="
echo "  Setup Complete!"
echo "=========================================="
echo ""
echo "✓ Nautilus is now available at:"
echo ""
echo "   http://$DOMAIN/install.php"
echo ""
echo "✓ Your localhost is unchanged"
echo "✓ All your other webapps still work on localhost"
echo ""
echo "Open your browser and visit:"
echo "   http://$DOMAIN/install.php"
echo ""
