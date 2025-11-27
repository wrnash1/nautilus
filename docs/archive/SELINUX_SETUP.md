# SELinux Configuration for Nautilus

## Quick Start (Copy-Paste Commands)

```bash
# Run this script to configure SELinux for Nautilus
sudo bash /home/wrnash1/Developer/nautilus/scripts/selinux-setup.sh
```

## SELinux Contexts Required

### 1. Web Application Files

```bash
# Set context for web root
sudo semanage fcontext -a -t httpd_sys_content_t "/var/www/html/nautilus(/.*)?"
sudo restorecon -Rv /var/www/html/nautilus

# Allow writing to storage directories
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/public/uploads(/.*)?"
sudo restorecon -Rv /var/www/html/nautilus/storage
sudo restorecon -Rv /var/www/html/nautilus/public/uploads
```

### 2. Database Connection

```bash
# Allow Apache to connect to MySQL/MariaDB
sudo setsebool -P httpd_can_network_connect_db 1
```

### 3. Email Sending (SMTP)

```bash
# Allow Apache to send emails
sudo setsebool -P httpd_can_sendmail 1
```

### 4. External API Connections

```bash
# Allow Apache to make network connections (for Google APIs, Stripe, etc.)
sudo setsebool -P httpd_can_network_connect 1
```

### 5. Session Directory

```bash
# Set context for session storage
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/lib/php/session(/.*)?"
sudo restorecon -Rv /var/lib/php/session
```

## Complete Setup Script

Create `/home/wrnash1/Developer/nautilus/scripts/selinux-setup.sh`:

```bash
#!/bin/bash
# SELinux Configuration Script for Nautilus
# Run with: sudo bash selinux-setup.sh

set -e

echo "=== Nautilus SELinux Configuration ==="
echo ""

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

echo "Configuring SELinux for: $WEB_ROOT"
echo ""

# 1. Web content
echo "[1/7] Setting web content context..."
semanage fcontext -a -t httpd_sys_content_t "$WEB_ROOT(/.*)?" 2>/dev/null || true
restorecon -Rv "$WEB_ROOT"

# 2. Writable directories
echo "[2/7] Setting writable directory contexts..."
for dir in "storage" "public/uploads" "logs"; do
    if [ -d "$WEB_ROOT/$dir" ]; then
        semanage fcontext -a -t httpd_sys_rw_content_t "$WEB_ROOT/$dir(/.*)?" 2>/dev/null || true
        restorecon -Rv "$WEB_ROOT/$dir"
    fi
done

# 3. Database connection
echo "[3/7] Allowing database connections..."
setsebool -P httpd_can_network_connect_db 1

# 4. Email
echo "[4/7] Allowing email sending..."
setsebool -P httpd_can_sendmail 1

# 5. Network connections
echo "[5/7] Allowing network connections (APIs)..."
setsebool -P httpd_can_network_connect 1

# 6. Execute memory
echo "[6/7] Allowing memory execution (for PHP)..."
setsebool -P httpd_execmem 1

# 7. Unified permissions
echo "[7/7] Setting unified permissions..."
setsebool -P httpd_unified 1

# Session directory
if [ -d "/var/lib/php/session" ]; then
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/lib/php/session(/.*)?" 2>/dev/null || true
    restorecon -Rv /var/lib/php/session
fi

echo ""
echo "=== SELinux Configuration Complete ==="
echo ""
echo "Current booleans:"
getsebool -a | grep httpd | grep " on$"
echo ""
echo "✓ Nautilus is now configured to work with SELinux"
```

## Required SELinux Booleans

| Boolean | Purpose | Command |
|---|---|---|
| `httpd_can_network_connect_db` | MySQL/MariaDB connection | `setsebool -P httpd_can_network_connect_db 1` |
| `httpd_can_sendmail` | Email sending (PHPMailer) | `setsebool -P httpd_can_sendmail 1` |
| `httpd_can_network_connect` | External APIs (Google, Stripe) | `setsebool -P httpd_can_network_connect 1` |
| `httpd_execmem` | PHP execution | `setsebool -P httpd_execmem 1` |
| `httpd_unified` | Simplified permissions | `setsebool -P httpd_unified 1` |

## Troubleshooting

### Check SELinux Denials

```bash
# View recent denials
sudo ausearch -m avc -ts recent

# Monitor denials in real-time
sudo tail -f /var/log/audit/audit.log | grep denied
```

### Generate Custom Policy from Denials

```bash
# If you see denials, generate a custom policy
sudo ausearch -m avc -ts recent | audit2allow -M nautilus_custom
sudo semodule -i nautilus_custom.pp
```

### Temporarily Disable SELinux (Testing Only)

```bash
# Permissive mode (logs violations but doesn't block)
sudo setenforce 0

# Re-enable
sudo setenforce 1

# IMPORTANT: This is temporary. Reboot resets to /etc/selinux/config setting
```

### Verify Contexts

```bash
# Check file contexts
ls -lZ /var/www/html/nautilus

# Check booleans
getsebool -a | grep httpd

# Check if SELinux is blocking
sudo sealert -a /var/log/audit/audit.log
```

## Development Environment

For development on Fedora/RHEL with home directory:

```bash
# Allow Apache to read home directories
setsebool -P httpd_enable_homedirs 1

# Set context for developer directory
sudo semanage fcontext -a -t httpd_sys_content_t "/home/wrnash1/Developer/nautilus(/.*)?"
sudo restorecon -Rv /home/wrnash1/Developer/nautilus
```

## Common Issues & Solutions

### Issue: 403 Forbidden

**Solution**: Check file contexts
```bash
ls -lZ /var/www/html/nautilus
sudo restorecon -Rv /var/www/html/nautilus
```

### Issue: Can't Connect to Database

**Solution**: Enable database boolean
```bash
sudo setsebool -P httpd_can_network_connect_db 1
```

### Issue: Can't Upload Files

**Solution**: Fix upload directory context
```bash
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/public/uploads(/.*)?"
sudo restorecon -Rv /var/www/html/nautilus/public/uploads
```

### Issue: External APIs Don't Work (Google, Stripe)

**Solution**: Enable network connections
```bash
sudo setsebool -P httpd_can_network_connect 1
```

## Security Best Practices

1. **Always keep SELinux enabled** - Use `Enforcing` mode in production
2. **Use specific contexts** - Don't use generic `httpd_sys_script_exec_t`
3. **Document custom policies** - Save custom `.te` files to version control
4. **Test after updates** - SELinux policies may need adjustment after system updates
5. **Monitor audit logs** - Regular review of `/var/log/audit/audit.log`

## Quick Reference

```bash
# Check SELinux status
getenforce

# View all httpd booleans
getsebool -a | grep httpd

# List file contexts
semanage fcontext -l | grep nautilus

# Restore default contexts
restorecon -Rv /var/www/html/nautilus

# Create custom policy from denials
ausearch -m avc -ts recent | audit2allow -M my_nautilus
semodule -i my_nautilus.pp
```

## Automated Setup

Run the complete setup with one command:

```bash
curl -s https://raw.githubusercontent.com/yourusername/nautilus/main/scripts/selinux-setup.sh | sudo bash
```

Or download and review first:

```bash
wget https://raw.githubusercontent.com/yourusername/nautilus/main/scripts/selinux-setup.sh
chmod +x selinux-setup.sh
sudo ./selinux-setup.sh
```
