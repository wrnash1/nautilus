#!/bin/bash
###############################################################################
# Setup HTTPS for Nautilus with Self-Signed Certificate
###############################################################################

echo "=========================================="
echo "  Nautilus HTTPS Setup"
echo "=========================================="
echo ""

if [ "$EUID" -ne 0 ]; then
    echo "Error: This script must be run with sudo"
    exit 1
fi

DOMAIN="nautilus.local"
CERT_DIR="/etc/pki/tls/certs"
KEY_DIR="/etc/pki/tls/private"
CERT_FILE="$CERT_DIR/nautilus.crt"
KEY_FILE="$KEY_DIR/nautilus.key"

echo "1. Checking if mod_ssl is installed..."
if ! rpm -q mod_ssl &> /dev/null; then
    echo "Installing mod_ssl..."
    dnf install -y mod_ssl
    echo "✓ mod_ssl installed"
else
    echo "✓ mod_ssl already installed"
fi
echo ""

echo "2. Generating self-signed SSL certificate..."
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout "$KEY_FILE" \
    -out "$CERT_FILE" \
    -subj "/C=US/ST=State/L=City/O=Nautilus/CN=$DOMAIN" \
    -addext "subjectAltName=DNS:$DOMAIN,DNS:www.$DOMAIN"

chmod 600 "$KEY_FILE"
chmod 644 "$CERT_FILE"
echo "✓ Certificate created"
echo "  Certificate: $CERT_FILE"
echo "  Private Key: $KEY_FILE"
echo ""

echo "3. Backing up current Apache config..."
cp /etc/httpd/conf.d/nautilus.conf /etc/httpd/conf.d/nautilus.conf.backup
echo "✓ Backed up to nautilus.conf.backup"
echo ""

echo "4. Installing HTTPS configuration..."
cat > /etc/httpd/conf.d/nautilus.conf <<'EOF'
# Nautilus - HTTP to HTTPS Redirect
<VirtualHost *:80>
    ServerName nautilus.local
    ServerAlias www.nautilus.local

    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

# Nautilus - HTTPS Virtual Host
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/pki/tls/certs/nautilus.crt
    SSLCertificateKeyFile /etc/pki/tls/private/nautilus.key

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/nautilus_error.log
    CustomLog /var/log/httpd/nautilus_access.log combined
</VirtualHost>
EOF
echo "✓ HTTPS configuration installed"
echo ""

echo "5. Enabling Apache SSL module..."
# Ensure mod_ssl is loaded (should be automatic with mod_ssl package)
echo "✓ SSL module ready"
echo ""

echo "6. Testing Apache configuration..."
httpd -t
if [ $? -eq 0 ]; then
    echo "✓ Configuration is valid"
else
    echo "✗ Configuration error - restoring backup"
    cp /etc/httpd/conf.d/nautilus.conf.backup /etc/httpd/conf.d/nautilus.conf
    exit 1
fi
echo ""

echo "7. Configuring firewall for HTTPS..."
if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-service=https
    firewall-cmd --reload
    echo "✓ Firewall configured"
else
    echo "⚠ firewall-cmd not found, skipping"
fi
echo ""

echo "8. Restarting Apache..."
systemctl restart httpd
if [ $? -eq 0 ]; then
    echo "✓ Apache restarted successfully"
else
    echo "✗ Apache failed to restart"
    systemctl status httpd
    exit 1
fi
echo ""

echo "9. Verifying HTTPS is working..."
sleep 2
if curl -k -s -o /dev/null -w "%{http_code}" https://nautilus.local/ | grep -q "200\|302"; then
    echo "✓ HTTPS is responding"
else
    echo "⚠ HTTPS may not be responding correctly"
fi
echo ""

echo "=========================================="
echo "  HTTPS Setup Complete!"
echo "=========================================="
echo ""
echo "✅ Nautilus is now available via HTTPS:"
echo ""
echo "   https://nautilus.local/install.php"
echo ""
echo "⚠️  IMPORTANT: Browser Security Warning"
echo ""
echo "You will see a security warning in your browser because"
echo "this is a self-signed certificate. This is normal for"
echo "local development. To proceed:"
echo ""
echo "  • Firefox: Click 'Advanced' → 'Accept Risk and Continue'"
echo "  • Chrome: Click 'Advanced' → 'Proceed to nautilus.local'"
echo "  • Edge: Click 'Advanced' → 'Continue to nautilus.local'"
echo ""
echo "This is safe for local development on your own computer."
echo ""
