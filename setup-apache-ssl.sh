#!/bin/bash
# Setup Apache with SSL for Nautilus
# Run with: sudo ./setup-apache-ssl.sh

set -e

echo "=========================================="
echo "Nautilus Apache + SSL Setup"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}ERROR: This script must be run as root (use sudo)${NC}"
    exit 1
fi

# Variables
DOMAIN="nautilus.local"
APP_ROOT="/var/www/html/nautilus"
PUBLIC_DIR="$APP_ROOT/public"
CERT_DIR="/etc/pki/tls/certs"
KEY_DIR="/etc/pki/tls/private"
CERT_FILE="$CERT_DIR/nautilus-selfsigned.crt"
KEY_FILE="$KEY_DIR/nautilus-selfsigned.key"

echo "Step 1: Install mod_ssl if not present..."
dnf install -y mod_ssl 2>/dev/null || yum install -y mod_ssl 2>/dev/null || echo "mod_ssl already installed"

echo ""
echo "Step 2: Generate self-signed SSL certificate..."
if [ ! -f "$CERT_FILE" ]; then
    openssl req -new -newkey rsa:2048 -days 365 -nodes -x509 \
        -subj "/C=US/ST=State/L=City/O=DiveShop/CN=$DOMAIN" \
        -keyout "$KEY_FILE" \
        -out "$CERT_FILE"
    chmod 600 "$KEY_FILE"
    echo -e "${GREEN}✓ SSL certificate created${NC}"
else
    echo -e "${YELLOW}SSL certificate already exists${NC}"
fi

echo ""
echo "Step 3: Create Apache virtual host configuration..."

cat > /etc/httpd/conf.d/nautilus.conf <<'APACHE_CONF'
# Nautilus Dive Shop Management System
# Redirect all HTTP to HTTPS
<VirtualHost *:80>
    ServerName nautilus.local
    ServerAlias localhost

    # Redirect to HTTPS
    Redirect permanent / https://nautilus.local/

    ErrorLog /var/log/httpd/nautilus-error.log
    CustomLog /var/log/httpd/nautilus-access.log combined
</VirtualHost>

# HTTPS Virtual Host
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias localhost

    # Point to public directory
    DocumentRoot /var/www/html/nautilus/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/pki/tls/certs/nautilus-selfsigned.crt
    SSLCertificateKeyFile /etc/pki/tls/private/nautilus-selfsigned.key

    # Security headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Enable URL rewriting
        <IfModule mod_rewrite.c>
            RewriteEngine On
        </IfModule>
    </Directory>

    # Deny access to parent directory
    <DirectoryMatch "^/var/www/html/nautilus/(?!public)">
        Require all denied
    </DirectoryMatch>

    # PHP Configuration
    <IfModule mod_php.c>
        php_value upload_max_filesize 10M
        php_value post_max_size 10M
        php_value memory_limit 256M
        php_value max_execution_time 300
    </IfModule>

    # Logging
    ErrorLog /var/log/httpd/nautilus-ssl-error.log
    CustomLog /var/log/httpd/nautilus-ssl-access.log combined

    # Modern SSL configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on
</VirtualHost>
APACHE_CONF

echo -e "${GREEN}✓ Apache configuration created${NC}"

echo ""
echo "Step 4: Add nautilus.local to /etc/hosts..."
if ! grep -q "nautilus.local" /etc/hosts; then
    echo "127.0.0.1   nautilus.local" >> /etc/hosts
    echo -e "${GREEN}✓ Added nautilus.local to /etc/hosts${NC}"
else
    echo -e "${YELLOW}nautilus.local already in /etc/hosts${NC}"
fi

echo ""
echo "Step 5: Set correct permissions..."
chown -R apache:apache "$APP_ROOT"
chmod -R 755 "$APP_ROOT"
chmod -R 775 "$APP_ROOT/storage" 2>/dev/null || mkdir -p "$APP_ROOT/storage" && chmod 775 "$APP_ROOT/storage"
chmod -R 775 "$APP_ROOT/public/uploads" 2>/dev/null || mkdir -p "$APP_ROOT/public/uploads" && chmod 775 "$APP_ROOT/public/uploads"
echo -e "${GREEN}✓ Permissions set${NC}"

echo ""
echo "Step 6: Enable firewall for HTTPS..."
if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-service=https 2>/dev/null || echo "Firewall rule may already exist"
    firewall-cmd --reload 2>/dev/null || echo "Could not reload firewall"
    echo -e "${GREEN}✓ Firewall configured${NC}"
else
    echo -e "${YELLOW}⚠ firewalld not found, skipping firewall config${NC}"
fi

echo ""
echo "Step 7: Test Apache configuration..."
if apachectl configtest 2>&1 | grep -q "Syntax OK"; then
    echo -e "${GREEN}✓ Apache configuration is valid${NC}"
else
    echo -e "${RED}✗ Apache configuration has errors!${NC}"
    apachectl configtest
    exit 1
fi

echo ""
echo "Step 8: Restart Apache..."
systemctl restart httpd
if systemctl is-active --quiet httpd; then
    echo -e "${GREEN}✓ Apache restarted successfully${NC}"
else
    echo -e "${RED}✗ Apache failed to start!${NC}"
    systemctl status httpd
    exit 1
fi

echo ""
echo "=========================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "Access your application:"
echo -e "  ${GREEN}https://nautilus.local/store/login${NC}"
echo -e "  ${GREEN}https://localhost/store/login${NC}"
echo ""
echo "Login credentials:"
echo "  Email:    admin@nautilus.local"
echo "  Password: password"
echo ""
echo -e "${YELLOW}⚠ Note: You'll see a browser warning about the self-signed certificate.${NC}"
echo -e "   Click 'Advanced' and 'Proceed' to continue."
echo ""
echo "To view logs:"
echo "  sudo tail -f /var/log/httpd/nautilus-ssl-error.log"
echo ""
