#!/bin/bash
################################################################################
# Nautilus SSL and SELinux Configuration Script
# This script ensures SSL certificates are installed and SELinux is enabled
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() {echo -e "${GREEN}[✓]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[!]${NC} $1"; }
log_error() { echo -e "${RED}[✗]${NC} $1"; }

echo "═══════════════════════════════════════════════════════"
echo "  Nautilus SSL & SELinux Configuration"
echo "═══════════════════════════════════════════════════════"
echo ""

if [ "$EUID" -ne 0 ]; then
    log_error "This script must be run as root: sudo bash ssl-selinux-fix.sh"
    exit 1
fi

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS_ID=$ID
else
    log_error "Cannot detect OS"
    exit 1
fi

log_info "Detected OS: $OS_ID"

################################################################################
# Configure SSL Certificate
################################################################################
echo ""
log_info "[1/3] Configuring SSL Certificate..."

# Determine SSL paths based on OS
case $OS_ID in
    fedora|rhel|centos|rocky|alma)
        SSL_CERT_DIR="/etc/pki/tls/certs"
        SSL_KEY_DIR="/etc/pki/tls/private"
        WEB_CONF_DIR="/etc/httpd/conf.d"
        WEB_SERVICE="httpd"
        ;;
    debian|ubuntu)
        SSL_CERT_DIR="/etc/ssl/certs"
        SSL_KEY_DIR="/etc/ssl/private"
        WEB_CONF_DIR="/etc/apache2/sites-available"
        WEB_SERVICE="apache2"
        ;;
    *)
        SSL_CERT_DIR="/etc/ssl/certs"
        SSL_KEY_DIR="/etc/ssl/private"
        WEB_CONF_DIR="/etc/httpd/conf.d"
        WEB_SERVICE="httpd"
        ;;
esac

SSL_CERT="$SSL_CERT_DIR/nautilus-selfsigned.crt"
SSL_KEY="$SSL_KEY_DIR/nautilus-selfsigned.key"

# Create SSL certificate if it doesn't exist
if [ ! -f "$SSL_CERT" ]; then
    log_info "Generating self-signed SSL certificate..."
    
    mkdir -p "$SSL_CERT_DIR" "$SSL_KEY_DIR"
    
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "$SSL_KEY" \
        -out "$SSL_CERT" \
        -subj "/C=US/ST=State/L=City/O=Nautilus Dive Shop/CN=nautilus.local" \
        2>/dev/null
    
    chmod 600 "$SSL_KEY"
    chmod 644 "$SSL_CERT"
    
    log_success "SSL certificate created: $SSL_CERT"
else
    log_success "SSL certificate already exists: $SSL_CERT"
fi

# Create HTTPS virtual host
log_info "Creating HTTPS virtual host..."

if [ "$OS_ID" = "debian" ] || [ "$OS_ID" = "ubuntu" ]; then
    # Debian/Ubuntu: use sites-available
    cat > "$WEB_CONF_DIR/nautilus-ssl.conf" <<VHOST_EOF
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile $SSL_CERT
    SSLCertificateKeyFile $SSL_KEY

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        <IfModule mod_rewrite.c>
            RewriteEngine On
        </IfModule>
    </Directory>

    ErrorLog /var/log/apache2/nautilus_ssl_error.log
    CustomLog /var/log/apache2/nautilus_ssl_access.log combined
</VirtualHost>
VHOST_EOF
    
    a2ensite nautilus-ssl.conf 2>/dev/null || true
    a2enmod ssl 2>/dev/null || true
else
    # RHEL/Fedora: use conf.d
    cat > "$WEB_CONF_DIR/nautilus-ssl.conf" <<VHOST_EOF
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile $SSL_CERT
    SSLCertificateKeyFile $SSL_KEY

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
VHOST_EOF
fi

log_success "HTTPS virtual host configured"

################################################################################
# Configure Firewall for HTTPS
################################################################################
log_info "Opening firewall port 443 for HTTPS..."

if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-service=https 2>/dev/null || true
    firewall-cmd --reload 2>/dev/null || true
    log_success "Firewall configured for HTTPS"
elif command -v ufw &> /dev/null; then
    ufw allow 443/tcp 2>/dev/null || true
    log_success "Firewall configured for HTTPS"
else
    log_warning "No firewall detected, skipping"
fi

################################################################################
# Configure SELinux
################################################################################
echo ""
log_info "[2/3] Configuring SELinux for Security..."

if command -v getenforce &> /dev/null; then
    CURRENT_STATUS=$(getenforce)
    log_info "Current SELinux status: $CURRENT_STATUS"
    
    if [ "$CURRENT_STATUS" = "Disabled" ]; then
        log_warning "SELinux is DISABLED - this is a security risk!"
        log_info "Enabling SELinux..."
        
        # Install SELinux if needed
        if [ "$OS_ID" = "fedora" ] || [ "$OS_ID" = "rhel" ] || [ "$OS_ID" = "centos" ] || [ "$OS_ID" = "rocky" ] || [ "$OS_ID" = "alma" ]; then
            dnf install -y policycoreutils policycoreutils-python-utils selinux-policy-targeted 2>/dev/null || \
            yum install -y policycoreutils policycoreutils-python-utils selinux-policy-targeted 2>/dev/null || true
        fi
        
        # Update SELinux config
        if [ -f /etc/selinux/config ]; then
            sed -i.bak 's/^SELINUX=disabled/SELINUX=enforcing/' /etc/selinux/config
            sed -i 's/^SELINUX=permissive/SELINUX=enforcing/' /etc/selinux/config
            log_success "SELinux config updated to enforcing mode"
            log_warning "System needs to reboot for SELinux to be fully enabled"
            log_info "Setting to permissive mode for now..."
            setenforce 0 2>/dev/null || true
        fi
    elif [ "$CURRENT_STATUS" = "Permissive" ]; then
        log_warning "SELinux is in permissive mode"        log_info "Switching to enforcing mode for better security..."
        setenforce 1 2>/dev/null && log_success "SELinux set to enforcing" || log_warning "Could not set to enforcing"
    else
        log_success "SELinux is already in enforcing mode ✓"
    fi
    
    # Apply SELinux contexts
    log_info "Applying SELinux contexts to Nautilus files..."
    
    INSTALL_DIR="/var/www/html/nautilus"
    
    if [ -d "$INSTALL_DIR" ]; then
        # Web content
        semanage fcontext -a -t httpd_sys_content_t "$INSTALL_DIR(/.*)?)" 2>/dev/null || true
        restorecon -Rv "$INSTALL_DIR" > /dev/null 2>&1 || true
        
        # Writable directories
        for dir in "storage" "public/uploads" "logs"; do
            if [ -d "$INSTALL_DIR/$dir" ]; then
                semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/$dir(/.*)?)" 2>/dev/null || true
                restorecon -Rv "$INSTALL_DIR/$dir" > /dev/null 2>&1 || true
            fi
        done
        
        # Enable SELinux booleans
        setsebool -P httpd_can_network_connect_db 1 2>/dev/null || true
        setsebool -P httpd_can_sendmail 1 2>/dev/null || true
        setsebool -P httpd_can_network_connect 1 2>/dev/null || true
        setsebool -P httpd_execmem 1 2>/dev/null || true
        setsebool -P httpd_unified 1 2>/dev/null || true
        
        log_success "SELinux contexts applied"
    fi
    
else
    log_warning "SELinux not available on this system"
    log_warning "Consider using RHEL, Fedora, or CentOS for enhanced security"
    
    # Try to install SELinux on Debian/Ubuntu
    if [ "$OS_ID" = "debian" ] || [ "$OS_ID" = "ubuntu" ]; then
        log_info "Attempting to install SELinux..."
        apt-get update -qq
        apt-get install -y selinux-basics selinux-policy-default auditd 2>/dev/null && {
            log_success "SELinux installed"
            log_info "Activating SELinux..."
            selinux-activate
            log_warning "System needs to reboot for SELinux to be enabled"
        } || {
            log_warning "Could not install SELinux on this Debian/Ubuntu system"
        }
    fi
fi

################################################################################
# Restart Web Server
################################################################################
echo ""
log_info "[3/3] Restarting web server..."

if command -v systemctl &> /dev/null; then
    systemctl restart $WEB_SERVICE 2>/dev/null && log_success "Web server restarted" || log_error "Failed to restart web server"
    systemctl status $WEB_SERVICE --no-pager -l | head -5
elif command -v service &> /dev/null; then
    service $WEB_SERVICE restart && log_success "Web server restarted" || log_error "Failed to restart web server"
fi

################################################################################
# Summary
################################################################################
echo ""
echo "═══════════════════════════════════════════════════════"
echo "  Configuration Complete!"
echo "═══════════════════════════════════════════════════════"
echo ""
echo "🔒 SSL Status:"
if [ -f "$SSL_CERT" ]; then
    echo "   ✓ Certificate: $SSL_CERT"
    echo "   ✓ HTTPS Available: https://nautilus.local/install/"
else
    echo "   ✗ No certificate found"
fi
echo ""
echo "🛡️  SELinux Status:"
if command -v getenforce &> /dev/null; then
    FINAL_STATUS=$(getenforce)
    echo "   Current Mode: $FINAL_STATUS"
    if [ "$FINAL_STATUS" = "Enforcing" ]; then
        echo "   ✓ Security: MAXIMUM"
    elif [ "$FINAL_STATUS" = "Permissive" ]; then
        echo "   ⚠ Security: MODERATE (consider enforcing mode)"
    else
        echo "   ⚠ Security: MINIMAL (reboot required to enable)"
    fi
else
    echo "   ✗ Not Available"
fi
echo ""
echo "🌐 Access URLs:"
echo "   HTTPS: https://nautilus.local/install/"
echo "   HTTP:  http://nautilus.local/install/"
echo ""
log_success "Nautilus is now secure!"
