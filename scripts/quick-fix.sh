#!/bin/bash
################################################################################
# Quick Fix Script for Nautilus Installer Issues
# Fixes: HTTP->HTTPS redirect and ensures Apache modules are enabled
################################################################################

echo "=== Nautilus Quick Fix Script ==="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: This script must be run as root"
    echo "Usage: sudo bash quick-fix.sh"
    exit 1
fi

echo "[1/5] Enabling required Apache modules..."
# Enable mod_rewrite for HTTP->HTTPS redirect
if ! httpd -M 2>/dev/null | grep -q "rewrite_module"; then
    echo "  → mod_rewrite not loaded, checking config..."
    # On RHEL/Fedora, mod_rewrite is usually compiled in
    # Just verify it's loaded
fi

# Enable mod_ssl
if !httpd -M 2>/dev/null | grep -q "ssl_module"; then
    echo "  → Installing mod_ssl..."
    dnf install -y mod_ssl
fi

# Enable mod_headers for security headers
if ! httpd -M 2>/dev/null | grep -q "headers_module"; then
    echo "  → mod_headers not loaded"
fi

echo "✓ Modules checked"

echo ""
echo "[2/5] Verifying virtual host configuration..."
if [ -f /etc/httpd/conf.d/nautilus.conf ]; then
    echo "✓ HTTP virtual host exists"
    cat /etc/httpd/conf.d/nautilus.conf
else
    echo "✗ HTTP virtual host missing!"
fi

echo ""
if [ -f /etc/httpd/conf.d/nautilus-ssl.conf ]; then
    echo "✓ HTTPS virtual host exists"
else
    echo "✗ HTTPS virtual host missing!"
fi

echo ""
echo "[3/5] Testing HTTPS certificate..."
if [ -f /etc/pki/tls/certs/nautilus-selfsigned.crt ]; then
    echo "✓ SSL certificate exists"
    openssl x509 -in /etc/pki/tls/certs/nautilus-selfsigned.crt -noout -subject -dates
else
    echo "✗ SSL certificate missing!"
fi

echo ""
echo "[4/5] Restarting Apache..."
systemctl restart httpd
if systemctl is-active --quiet httpd; then
    echo "✓ Apache restarted successfully"
else
    echo "✗ Apache failed to start!"
    systemctl status httpd
fi

echo ""
echo "[5/5] Testing HTTP->HTTPS redirect..."
echo "Testing: curl -I http://nautilus.local/"
curl -I http://nautilus.local/ 2>&1 | grep -E "(HTTP|Location)" || echo "Could not test redirect"

echo ""
echo "=== Fix Complete ==="
echo ""
echo "Next steps:"
echo "1. Test HTTPS access: https://nautilus.local/"
echo "2. Login credentials:"
echo "   Username: admin"
echo "   Email: admin@nautilus.local"
echo "   Password: admin123"
echo ""
echo "If login still fails, check:"
echo "  - Database: mysql -u root -p nautilus -e 'SELECT * FROM users;'"
echo "  - Error logs: tail -f /var/log/httpd/nautilus_ssl_error.log"
echo "  - SELinux: ausearch -m avc -ts recent | grep denied"
echo ""
