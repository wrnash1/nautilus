#!/bin/bash
################################################################################
# Complete Nautilus Diagnostic and Repair Script
# Diagnoses and fixes all installation issues
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║    NAUTILUS COMPLETE DIAGNOSTIC & REPAIR                ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}ERROR: Must run as root${NC}"
    echo "Usage: sudo bash complete-repair.sh"
    exit 1
fi

################################################################################
# DIAGNOSTICS
################################################################################
echo -e "${BLUE}=== RUNNING DIAGNOSTICS ===${NC}"
echo ""

echo "[1/8] Checking Apache status..."
if systemctl is-active --quiet httpd; then
    echo -e "${GREEN}✓ Apache is running${NC}"
else
    echo -e "${RED}✗ Apache is NOT running${NC}"
    APACHE_DEAD=true
fi

echo ""
echo "[2/8] Checking Apache configuration..."
if httpd -t 2>&1 | grep -q "Syntax OK"; then
    echo -e "${GREEN}✓ Apache config syntax OK${NC}"
else
    echo -e "${RED}✗ Apache config has errors:${NC}"
    httpd -t
    CONFIG_ERROR=true
fi

echo ""
echo "[3/8] Checking virtual host files..."
if [ -f /etc/httpd/conf.d/nautilus.conf ]; then
    echo -e "${GREEN}✓ HTTP vhost exists${NC}"
else
    echo -e "${RED}✗ HTTP vhost missing${NC}"
    HTTP_VHOST_MISSING=true
fi

if [ -f /etc/httpd/conf.d/nautilus-ssl.conf ]; then
    echo -e "${GREEN}✓ HTTPS vhost exists${NC}"
else
    echo -e "${YELLOW}✗ HTTPS vhost MISSING - this is the main problem!${NC}"
    HTTPS_VHOST_MISSING=true
fi

echo""
echo "[4/8] Checking SSL certificates..."
if [ -f /etc/pki/tls/certs/nautilus-selfsigned.crt ]; then
    echo -e "${GREEN}✓ SSL certificate exists${NC}"
else
    echo -e "${RED}✗ SSL certificate missing${NC}"
    SSL_MISSING=true
fi

echo ""
echo "[5/8] Checking application files..."
if [ -d /var/www/html/nautilus ]; then
    echo -e "${GREEN}✓ Nautilus directory exists${NC}"
else
    echo -e "${RED}✗ Nautilus directory missing${NC}"
    APP_MISSING=true
fi

echo ""
echo "[6/8] Checking database..."
DB_EXISTS=$(mysql -u root -N -e "SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='nautilus';" 2>/dev/null || echo "0")
if [ "$DB_EXISTS" = "1" ]; then
    TABLE_COUNT=$(mysql -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='nautilus';" 2>/dev/null || echo "0")
    echo -e "${GREEN}✓ Database exists with $TABLE_COUNT tables${NC}"
    
    USER_COUNT=$(mysql -u root -N -e "SELECT COUNT(*) FROM nautilus.users;" 2>/dev/null || echo "0")
    echo "  Users in database: $USER_COUNT"
else  
    echo -e "${RED}✗ Database does not exist${NC}"
    DB_MISSING=true
fi

echo ""
echo "[7/8] Checking SELinux..."
if command -v getenforce &> /dev/null; then
    SELINUX_STATUS=$(getenforce)
    echo "  SELinux status: $SELINUX_STATUS"
else
    echo "  SELinux not available"
fi

echo ""
echo "[8/8] Checking file permissions..."
if [ -d /var/www/html/nautilus/storage ]; then
    STORAGE_OWNER=$(stat -c '%U:%G' /var/www/html/nautilus/storage)
    echo "  Storage ownership: $STORAGE_OWNER"
    if [ "$STORAGE_OWNER" = "apache:apache" ]; then
        echo -e "${GREEN}✓ Storage owned by apache${NC}"
    else
        echo -e "${YELLOW}! Storage should be owned by apache${NC}"
        PERM_ISSUE=true
    fi
fi

################################################################################
# REPAIRS
################################################################################
echo ""
echo -e "${BLUE}=== STARTING REPAIRS ===${NC}"
echo ""

# Fix 1: Generate SSL certificate if missing
if [ "$SSL_MISSING" = "true" ]; then
    echo "[FIX 1] Generating SSL certificate..."
    mkdir -p /etc/pki/tls/certs /etc/pki/tls/private
    
    cat > /tmp/ssl-config.conf <<'EOF'
[req]
distinguished_name = req_distinguished_name
req_extensions = v3_req
prompt = no

[req_distinguished_name]
C = US
ST = State
L = City
O = Nautilus Dive Shop
CN = nautilus.local

[v3_req]
keyUsage = keyEncipherment, dataEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[alt_names]
DNS.1 = nautilus.local
DNS.2 = www.nautilus.local
DNS.3 = localhost
IP.1 = 127.0.0.1
EOF
    
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout /etc/pki/tls/private/nautilus-selfsigned.key \
        -out /etc/pki/tls/certs/nautilus-selfsigned.crt \
        -config /tmp/ssl-config.conf \
        -extensions v3_req 2>/dev/null
    
    chmod 600 /etc/pki/tls/private/nautilus-selfsigned.key
    chmod 644 /etc/pki/tls/certs/nautilus-selfsigned.crt
    
    rm -f /tmp/ssl-config.conf
    echo -e "${GREEN}✓ SSL certificate generated${NC}"
fi

# Fix 2: Create HTTPS virtual host (THIS IS THE CRITICAL FIX)
if [ "$HTTPS_VHOST_MISSING" = "true" ] || [ ! -f /etc/httpd/conf.d/nautilus-ssl.conf ]; then
    echo "[FIX 2] Creating HTTPS virtual host..."
    cat > /etc/httpd/conf.d/nautilus-ssl.conf <<'EOF'
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/pki/tls/certs/nautilus-selfsigned.crt
    SSLCertificateKeyFile /etc/pki/tls/private/nautilus-selfsigned.key
    
    # SSL Security Settings
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5

    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

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
EOF
    echo -e "${GREEN}✓ HTTPS virtual host created${NC}"
fi

# Fix 3: Update HTTP vhost to redirect to HTTPS
echo "[FIX 3] Updating HTTP vhost to redirect to HTTPS..."
cat > /etc/httpd/conf.d/nautilus.conf <<'EOF'
<VirtualHost *:80>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    
    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>
EOF
echo -e "${GREEN}✓ HTTP vhost updated with HTTPS redirect${NC}"

# Fix 4: Set proper permissions
if [ "$PERM_ISSUE" = "true" ] || [ -d /var/www/html/nautilus ]; then
    echo "[FIX 4] Setting proper permissions..."
    chown -R apache:apache /var/www/html/nautilus
    mkdir -p /var/www/html/nautilus/storage/logs
    chmod -R 775 /var/www/html/nautilus/storage
    
    if command -v getenforce &> /dev/null && [ "$(getenforce)" != "Disabled" ]; then
        chcon -R -t httpd_sys_content_t /var/www/html/nautilus
        chcon -R -t httpd_sys_rw_content_t /var/www/html/nautilus/storage
        chcon -t cert_t /etc/pki/tls/certs/nautilus-selfsigned.crt 2>/dev/null || true
        chcon -t cert_t /etc/pki/tls/private/nautilus-selfsigned.key 2>/dev/null || true
        echo -e "${GREEN}✓ Permissions and SELinux contexts set${NC}"
    else
        echo -e "${GREEN}✓ Permissions set${NC}"
    fi
fi

# Fix 5: Copy fixed AuthController
echo "[FIX 5] Copying fixed AuthController..."
if [ -f /home/wrnash1/Developer/nautilus/app/Controllers/Auth/AuthController.php ]; then
    cp /home/wrnash1/Developer/nautilus/app/Controllers/Auth/AuthController.php \
       /var/www/html/nautilus/app/Controllers/Auth/AuthController.php
    echo -e "${GREEN}✓ AuthController updated${NC}"
fi

# Fix 6: Restart Apache
echo "[FIX 6] Restarting Apache..."
systemctl restart httpd

if systemctl is-active --quiet httpd; then
    echo -e "${GREEN}✓ Apache restarted successfully${NC}"
else
    echo -e "${RED}✗ Apache failed to start!${NC}"
    echo "Checking logs..."
    journalctl -u httpd --no-pager -n 20
    exit 1
fi

################################################################################
# VERIFICATION
################################################################################
echo ""
echo -e "${BLUE}=== VERIFICATION ===${NC}"
echo ""

echo "[TEST 1] Testing HTTP redirect..."
HTTP_TEST=$(curl -s -I http://nautilus.local/ 2>&1 | grep -i "location.*https" && echo "OK" || echo "FAIL")
if [ "$HTTP_TEST" = "OK" ]; then
    echo -e "${GREEN}✓ HTTP redirects to HTTPS${NC}"
else
    echo -e "${YELLOW}! HTTP redirect may not be working${NC}"
fi

echo "[TEST 2] Testing HTTPS..."
HTTPS_TEST=$(curl -k -s -I https://nautilus.local/ 2>&1 | grep -i "HTTP" && echo "OK" || echo "FAIL")
if [ "$HTTPS_TEST" = "OK" ]; then
    echo -e "${GREEN}✓ HTTPS is accessible${NC}"
else
    echo -e "${RED}✗ HTTPS is not accessible${NC}"
fi

echo "[TEST 3] Testing port listeners..."
netstat -tlnp | grep -E "(80|443)" || true

################################################################################
# SUMMARY
################################################################################
echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                  REPAIR COMPLETE                         ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}All repairs completed!${NC}"
echo ""
echo "=== NEXT STEPS ==="
echo "1. Open browser: https://nautilus.local/"
echo "2. Accept SSL certificate warning"
echo "3. Login with:"
echo "   Email: admin@nautilus.local"
echo "   Password: admin123"
echo ""
echo "=== TROUBLESHOOTING ==="
echo "If login still fails, check debug logs:"
echo "  sudo tail -f /var/www/html/nautilus/storage/logs/debug_login.log"
echo "  sudo tail -f /var/www/html/nautilus/storage/logs/debug_auth.log"
echo ""
echo "Apache logs:"
echo "  sudo tail -f /var/log/httpd/nautilus_ssl_error.log"
echo ""
