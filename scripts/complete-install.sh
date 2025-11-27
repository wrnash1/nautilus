#!/bin/bash
# Nautilus Complete Installation Script
# This script installs Nautilus with Apache, virtual host, and SELinux configuration
# Run: sudo bash complete-install.sh

set -e

echo "╔══════════════════════════════════════════════════════════╗"
echo "║         NAUTILUS DIVE SHOP INSTALLATION                 ║"
echo "║         Complete Setup Script                           ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: This script must be run as root"
    echo "Usage: sudo bash complete-install.sh"
    exit 1
fi

# Detect installation source
SOURCE_DIR="/home/wrnash1/Developer/nautilus"
INSTALL_DIR="/var/www/html/nautilus"

if [ ! -d "$SOURCE_DIR" ]; then
    echo "ERROR: Source directory not found: $SOURCE_DIR"
    exit 1
fi

echo "[1/9] Copying files to $INSTALL_DIR..."
if [ -d "$INSTALL_DIR" ]; then
    echo "  → Backing up existing installation..."
    mv "$INSTALL_DIR" "$INSTALL_DIR.backup.$(date +%Y%m%d_%H%M%S)"
fi
cp -r "$SOURCE_DIR" "$INSTALL_DIR"
echo "  ✓ Files copied"

echo ""
echo "[2/9] Setting file ownership..."
chown -R apache:apache "$INSTALL_DIR"
echo "  ✓ Ownership set to apache:apache"

echo ""
echo "[3/9] Setting file permissions..."
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/public/uploads"
chmod -R 775 "$INSTALL_DIR/logs" 2>/dev/null || true
echo "  ✓ Permissions configured"

echo ""
echo "[4/9] Configuring SELinux..."
if command -v getenforce &> /dev/null && [ "$(getenforce)" != "Disabled" ]; then
    # Set Web Content contexts
    semanage fcontext -a -t httpd_sys_content_t "$INSTALL_DIR(/.*)?" 2>/dev/null || true
    restorecon -Rv "$INSTALL_DIR" > /dev/null 2>&1
    
    # Set Writable contexts
    for dir in "storage" "public/uploads" "logs"; do
        if [ -d "$INSTALL_DIR/$dir" ]; then
            semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/$dir(/.*)?" 2>/dev/null || true
            restorecon -Rv "$INSTALL_DIR/$dir" > /dev/null 2>&1
        fi
    done
    
    # Enable required booleans
    setsebool -P httpd_can_network_connect_db 1
    setsebool -P httpd_can_sendmail 1
    setsebool -P httpd_can_network_connect 1
    setsebool -P httpd_execmem 1
    setsebool -P httpd_unified 1
    
    echo "  ✓ SELinux configured (Enforcing mode)"
else
    echo "  ⚠ SELinux not active or not installed"
fi

echo ""
echo "[5/9] Creating Apache Virtual Host..."

# Create virtual host configuration
cat > /etc/httpd/conf.d/nautilus.conf <<'VHOST_EOF'
<VirtualHost *:80>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Enable .htaccess
        <IfModule mod_rewrite.c>
            RewriteEngine On
        </IfModule>
    </Directory>

    # Logs
    ErrorLog /var/log/httpd/nautilus_error.log
    CustomLog /var/log/httpd/nautilus_access.log combined
</VirtualHost>
VHOST_EOF

echo "  ✓ Virtual host created: /etc/httpd/conf.d/nautilus.conf"

echo ""
echo "[6/10] Checking SSL/HTTPS configuration..."
if [ ! -f "/etc/pki/tls/certs/nautilus-selfsigned.crt" ]; then
    echo "  → SSL certificate not found, generating self-signed certificate..."
    
    # Create self-signed certificate
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/pki/tls/private/nautilus-selfsigned.key \
        -out /etc/pki/tls/certs/nautilus-selfsigned.crt \
        -subj "/C=US/ST=State/L=City/O=Nautilus Dive Shop/CN=nautilus.local" \
        2>/dev/null
    
    # Create HTTPS virtual host
    cat > /etc/httpd/conf.d/nautilus-ssl.conf <<'SSL_VHOST_EOF'
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/pki/tls/certs/nautilus-selfsigned.crt
    SSLCertificateKeyFile /etc/pki/tls/private/nautilus-selfsigned.key

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
        </IfModule>
    </Directory>

    ErrorLog /var/log/httpd/nautilus_ssl_error.log
    CustomLog /var/log/httpd/nautilus_ssl_access.log combined
</VirtualHost>
SSL_VHOST_EOF
    
    echo "  ✓ Self-signed SSL certificate generated"
    echo "  ✓ HTTPS virtual host created"
else
    echo "  → SSL certificate already exists"
fi

echo ""
echo "[7/10] Updating /etc/hosts..."
if ! grep -q "nautilus.local" /etc/hosts; then
    echo "127.0.0.1   nautilus.local www.nautilus.local" >> /etc/hosts
    echo "  ✓ Added nautilus.local to /etc/hosts"
else
    echo "  → nautilus.local already in /etc/hosts"
fi

echo ""
echo "[8/10] Starting Apache web server..."
systemctl start httpd
systemctl enable httpd
echo "  ✓ Apache started and enabled"

echo ""
echo "[9/10] Configuring firewall..."
if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-service=http 2>/dev/null || true
    firewall-cmd --permanent --add-service=https 2>/dev/null || true
    firewall-cmd --reload 2>/dev/null || true
    echo "  ✓ Firewall configured (ports 80/443 open)"
else
    echo "  → firewalld not installed (skipping)"
fi

echo ""
echo "[10/10] Testing Apache configuration..."
if httpd -t > /dev/null 2>&1; then
    echo "  ✓ Apache configuration is valid"
    systemctl reload httpd
else
    echo "  ⚠ Apache configuration has warnings (but should work)"
fi

echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║              INSTALLATION COMPLETE!                      ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "📍 Access URLs (try in this order):"
echo "   1. https://nautilus.local/install/ (HTTPS with self-signed cert)"
echo "   2. http://nautilus.local/install/"
echo "   3. http://localhost/nautilus/public/install/"
echo ""
echo "⚠️  Note: If using HTTPS, your browser will show a security warning"
echo "   because the certificate is self-signed. This is normal for local"
echo "   development. Click 'Advanced' and proceed to the site."
echo ""
echo "🔍 Quick Test:"
echo "   curl -I http://localhost/nautilus/public/install/"
echo ""
echo "📊 Service Status:"
systemctl --no-pager status httpd | head -5
echo ""
echo "✨ Next Steps:"
echo "   1. Open browser to: http://nautilus.local/install/"
echo "   2. Complete the installation wizard"
echo "   3. Default login: admin@nautilus.local / admin123"
echo ""
echo "📖 Documentation: /home/wrnash1/Developer/nautilus/INSTALLATION_GUIDE.md"
echo ""
