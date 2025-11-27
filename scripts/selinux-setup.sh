#!/bin/bash
# SELinux Configuration Script for Nautilus
# Run with: sudo bash selinux-setup.sh

set -e

echo "=== Nautilus SELinux Configuration ==="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: This script must be run as root (use sudo)"
    exit 1
fi

# Check if SELinux is enabled
if ! command -v getenforce &> /dev/null; then
    echo "SELinux tools not found. Installing..."
    dnf install -y policycoreutils policycoreutils-python-utils selinux-policy-devel
fi

SELINUX_STATUS=$(getenforce)
echo "Current SELinux status: $SELINUX_STATUS"

if [ "$SELINUX_STATUS" = "Disabled" ]; then
    echo "SELinux is disabled. No configuration needed."
    exit 0
fi

# Detect web root
if [ -d "/var/www/html/nautilus" ]; then
    WEB_ROOT="/var/www/html/nautilus"
elif [ -d "/home/wrnash1/Developer/nautilus" ]; then
    WEB_ROOT="/home/wrnash1/Developer/nautilus"
else
    read -p "Enter Nautilus installation path: " WEB_ROOT
fi

if [ ! -d "$WEB_ROOT" ]; then
    echo "ERROR: Directory $WEB_ROOT does not exist"
    exit 1
fi

echo "Configuring SELinux for: $WEB_ROOT"
echo ""

# 1. Web content
echo "[1/7] Setting web content context..."
semanage fcontext -a -t httpd_sys_content_t "$WEB_ROOT(/.*)?" 2>/dev/null || echo "  (context may already exist)"
restorecon -Rv "$WEB_ROOT" 2>&1 | head -5
echo "  ✓ Web content context set"

# 2. Writable directories
echo "[2/7] Setting writable directory contexts..."
for dir in "storage" "public/uploads" "logs"; do
    if [ -d "$WEB_ROOT/$dir" ]; then
        semanage fcontext -a -t httpd_sys_rw_content_t "$WEB_ROOT/$dir(/.*)?" 2>/dev/null || echo "  (context may already exist for $dir)"
        restorecon -Rv "$WEB_ROOT/$dir" > /dev/null 2>&1
        echo "  ✓ $dir is writable"
    else
        echo "  ⚠ $dir not found, skipping"
    fi
done

# 3. Database connection
echo "[3/7] Allowing database connections..."
setsebool -P httpd_can_network_connect_db 1
echo "  ✓ Database connections enabled"

# 4. Email
echo "[4/7] Allowing email sending..."
setsebool -P httpd_can_sendmail 1
echo "  ✓ Email sending enabled"

# 5. Network connections
echo "[5/7] Allowing network connections (APIs)..."
setsebool -P httpd_can_network_connect 1
echo "  ✓ Network connections enabled"

# 6. Execute memory
echo "[6/7] Allowing memory execution (for PHP)..."
setsebool -P httpd_execmem 1
echo "  ✓ Memory execution enabled"

# 7. Unified permissions
echo "[7/7] Setting unified permissions..."
setsebool -P httpd_unified 1
echo "  ✓ Unified permissions set"

# Session directory
if [ -d "/var/lib/php/session" ]; then
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/lib/php/session(/.*)?" 2>/dev/null || true
    restorecon -Rv /var/lib/php/session > /dev/null 2>&1
    echo "  ✓ PHP session directory configured"
fi

# Home directories (if in development)
if [[ "$WEB_ROOT" == /home/* ]]; then
    echo ""
    echo "[DEV] Enabling home directory access..."
    setsebool -P httpd_enable_homedirs 1
    echo "  ✓ Home directory access enabled"
fi

echo ""
echo "=== SELinux Configuration Complete ==="
echo ""
echo "Active httpd booleans:"
getsebool -a | grep httpd | grep " on$" | awk '{print "  ✓", $1}'
echo ""
echo "✓ Nautilus is now configured to work with SELinux"
echo ""
echo "To verify, visit your site and check /var/log/audit/audit.log for denials"
