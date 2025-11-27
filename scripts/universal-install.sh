#!/bin/bash
################################################################################
# Nautilus Universal Installer
# Works on: Debian, Ubuntu, RHEL, CentOS, Fedora, Rocky, Alma, Arch, openSUSE, Alpine
# Features: Auto-detects OS, installs dependencies, configures web server,
#           sets up SSL certificates, enables and configures SELinux
################################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[✓]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[!]${NC} $1"; }
log_error() { echo -e "${RED}[✗]${NC} $1"; }

# Banner
echo "╔══════════════════════════════════════════════════════════╗"
echo "║         NAUTILUS UNIVERSAL INSTALLER                     ║"
echo "║         Multi-Distribution Linux Support                 ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    log_error "This script must be run as root"
    echo "Usage: sudo bash universal-install.sh [--skip-ssl]"
    exit 1
fi

# Parse arguments
SKIP_SSL=false
DB_TYPE="mariadb"  # Default: mariadb (options: mysql, mariadb, postgresql)
WEB_SERVER="apache"  # Default: apache (options: apache, nginx, caddy)

while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-ssl)
            SKIP_SSL=true
            shift
            ;;
        --database=*)
            DB_TYPE="${1#*=}"
            shift
            ;;
        --webserver=*)
            WEB_SERVER="${1#*=}"
            shift
            ;;
        --help)
            echo "Usage: sudo bash universal-install.sh [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --skip-ssl           Skip SSL certificate generation"
            echo "  --database=TYPE      Database type: mysql, mariadb, postgresql (default: mariadb)"
            echo "  --webserver=TYPE     Web server: apache, nginx, caddy (default: apache)"
            echo "  --help               Show this help message"
            echo ""
            echo "Examples:"
            echo "  sudo bash universal-install.sh"
            echo "  sudo bash universal-install.sh --database=postgresql --webserver=nginx"
            echo "  sudo bash universal-install.sh --database=mysql --webserver=apache --skip-ssl"
            exit 0
            ;;
        *)
            log_warning "Unknown option: $1"
            shift
            ;;
    esac
done

log_info "Configuration: Database=$DB_TYPE, Web Server=$WEB_SERVER, SSL=$([ "$SKIP_SSL" = true ] && echo "disabled" || echo "enabled")"

################################################################################
# STEP 1: Detect Operating System and Package Manager
################################################################################
log_info "Step 1/10: Detecting operating system..."

detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_NAME=$ID
        OS_VERSION=$VERSION_ID
        OS_PRETTY=$PRETTY_NAME
    else
        log_error "Cannot detect OS. /etc/os-release not found."
        exit 1
    fi
    
    log_info "Detected: $OS_PRETTY"
    
    # Detect package manager
    if command -v dnf &> /dev/null; then
        PKG_MANAGER="dnf"
        PKG_INSTALL="dnf install -y"
        PKG_UPDATE="dnf update -y"
        OS_FAMILY="rhel"
    elif command -v yum &> /dev/null; then
        PKG_MANAGER="yum"
        PKG_INSTALL="yum install -y"
        PKG_UPDATE="yum update -y"
        OS_FAMILY="rhel"
    elif command -v apt-get &> /dev/null; then
        PKG_MANAGER="apt"
        PKG_INSTALL="apt-get install -y"
        PKG_UPDATE="apt-get update"
        OS_FAMILY="debian"
        export DEBIAN_FRONTEND=noninteractive
    elif command -v pacman &> /dev/null; then
        PKG_MANAGER="pacman"
        PKG_INSTALL="pacman -S --noconfirm"
        PKG_UPDATE="pacman -Sy"
        OS_FAMILY="arch"
    elif command -v zypper &> /dev/null; then
        PKG_MANAGER="zypper"
        PKG_INSTALL="zypper install -y"
        PKG_UPDATE="zypper refresh"
        OS_FAMILY="suse"
    elif command -v apk &> /dev/null; then
        PKG_MANAGER="apk"
        PKG_INSTALL="apk add"
        PKG_UPDATE="apk update"
        OS_FAMILY="alpine"
    else
        log_error "No supported package manager found"
        exit 1
    fi
    
    log_success "Package manager: $PKG_MANAGER ($OS_FAMILY family)"
}

detect_os

################################################################################
# STEP 2: Update Package Lists
################################################################################
log_info "Step 2/10: Updating package lists..."
$PKG_UPDATE
log_success "Package lists updated"

################################################################################
# STEP 3: Install Dependencies
################################################################################
log_info "Step 3/10: Installing required dependencies..."

install_dependencies() {
    case $OS_FAMILY in
        rhel)
            # RHEL/CentOS/Fedora/Rocky/Alma
            log_info "Installing Apache and PHP..."
            $PKG_INSTALL httpd mod_ssl php php-json php-mbstring php-xml \
                         php-gd php-zip php-curl openssl curl wget \
                         policycoreutils policycoreutils-python-utils selinux-policy-devel
            
            # Install database based on user choice
            case $DB_TYPE in
                mysql)
                    log_info "Installing MySQL..."
                    $PKG_INSTALL mysql-server mysql php-mysqlnd
                    DB_SERVICE="mysqld"
                    ;;
                mariadb)
                    log_info "Installing MariaDB..."
                    $PKG_INSTALL mariadb-server mariadb php-mysqlnd
                    DB_SERVICE="mariadb"
                    ;;
                postgresql)
                    log_info "Installing PostgreSQL..."
                    $PKG_INSTALL postgresql-server postgresql postgresql-contrib php-pgsql
                    DB_SERVICE="postgresql"
                    ;;
                *)
                    log_error "Unknown database type: $DB_TYPE"
                    exit 1
                    ;;
            esac
            
            WEB_SERVER="httpd"
            WEB_USER="apache"
            WEB_GROUP="apache"
            WEB_CONF_DIR="/etc/httpd/conf.d"
            WEB_LOG_DIR="/var/log/httpd"
            SSL_CERT_DIR="/etc/pki/tls/certs"
            SSL_KEY_DIR="/etc/pki/tls/private"
            PHP_SESSION_DIR="/var/lib/php/session"
            ;;
            
        debian)
            # Debian/Ubuntu
            log_info "Installing Apache and PHP..."
            $PKG_INSTALL apache2 libapache2-mod-php php php-json php-mbstring \
                         php-xml php-gd php-zip php-curl \
                         openssl curl wget ssl-cert ca-certificates
            
            # Install database based on user choice
            case $DB_TYPE in
                mysql)
                    log_info "Installing MySQL..."
                    $PKG_INSTALL mysql-server mysql-client php-mysql
                    DB_SERVICE="mysql"
                    ;;
                mariadb)
                    log_info "Installing MariaDB..."
                    $PKG_INSTALL mariadb-server mariadb-client php-mysql
                    DB_SERVICE="mariadb"
                    ;;
                postgresql)
                    log_info "Installing PostgreSQL..."
                    $PKG_INSTALL postgresql postgresql-contrib php-pgsql
                    DB_SERVICE="postgresql"
                    ;;
                *)
                    log_error "Unknown database type: $DB_TYPE"
                    exit 1
                    ;;
            esac
            
            # Enable Apache modules
            a2enmod rewrite ssl headers
            
            WEB_SERVER="apache2"
            WEB_USER="www-data"
            WEB_GROUP="www-data"
            WEB_CONF_DIR="/etc/apache2/sites-available"
            WEB_LOG_DIR="/var/log/apache2"
            SSL_CERT_DIR="/etc/ssl/certs"
            SSL_KEY_DIR="/etc/ssl/private"
            PHP_SESSION_DIR="/var/lib/php/sessions"
            
            # Try to install SELinux (optional on Debian)
            $PKG_INSTALL selinux-basics selinux-policy-default auditd 2>/dev/null || {
                log_warning "SELinux not available on this Debian/Ubuntu system"
                log_warning "For maximum security, consider using a RHEL-based distribution"
            }
            ;;
            
        arch)
            # Arch Linux
            $PKG_INSTALL apache php php-apache mariadb openssl mod_ssl
            
            WEB_SERVER="httpd"
            WEB_USER="http"
            WEB_GROUP="http"
            WEB_CONF_DIR="/etc/httpd/conf"
            WEB_LOG_DIR="/var/log/httpd"
            SSL_CERT_DIR="/etc/ssl/certs"
            SSL_KEY_DIR="/etc/ssl/private"
            PHP_SESSION_DIR="/var/lib/php/sessions"
            ;;
            
        suse)
            # openSUSE
            $PKG_INSTALL apache2 apache2-mod_php7 php7 php7-mysql php7-json php7-mbstring \
                         php7-gd php7-zip php7-curl mariadb openssl
            
            a2enmod php7 rewrite ssl
            
            WEB_SERVER="apache2"
            WEB_USER="wwwrun"
            WEB_GROUP="www"
            WEB_CONF_DIR="/etc/apache2/conf.d"
            WEB_LOG_DIR="/var/log/apache2"
            SSL_CERT_DIR="/etc/ssl/certs"
            SSL_KEY_DIR="/etc/ssl/private"
            PHP_SESSION_DIR="/var/lib/php7/sessions"
            ;;
            
        alpine)
            # Alpine Linux
            $PKG_INSTALL apache2 php-apache2 php php-mysqli php-json php-mbstring \
                         php-xml php-gd php-zip php-curl mariadb mariadb-client openssl
            
            WEB_SERVER="apache2"
            WEB_USER="apache"
            WEB_GROUP="apache"
            WEB_CONF_DIR="/etc/apache2/conf.d"
            WEB_LOG_DIR="/var/log/apache2"
            SSL_CERT_DIR="/etc/ssl/certs"
            SSL_KEY_DIR="/etc/ssl/private"
            PHP_SESSION_DIR="/var/lib/php/sessions"
            ;;
            
        *)
            log_error "Unsupported OS family: $OS_FAMILY"
            exit 1
            ;;
    esac
}

install_dependencies
log_success "Dependencies installed"

################################################################################
# STEP 4: Configure SELinux (Security)
################################################################################
log_info "Step 4/10: Configuring SELinux for security..."

configure_selinux() {
    if command -v getenforce &> /dev/null; then
        SELINUX_STATUS=$(getenforce)
        log_info "Current SELinux status: $SELINUX_STATUS"
        
        # Enable SELinux if disabled or permissive
        if [ "$SELINUX_STATUS" = "Disabled" ]; then
            log_warning "SELinux is DISABLED. Enabling for production security..."
            
            # Update SELinux config for persistent enforcement
            if [ -f /etc/selinux/config ]; then
                sed -i 's/^SELINUX=disabled/SELINUX=enforcing/' /etc/selinux/config
                sed -i 's/^SELINUX=permissive/SELINUX=enforcing/' /etc/selinux/config
                log_warning "SELinux config updated to enforcing mode"
                log_warning "System reboot required for full SELinux enforcement"
                log_warning "Continuing with permissive mode for this installation..."
                # Cannot set to enforcing from disabled without reboot
            fi
        elif [ "$SELINUX_STATUS" = "Permissive" ]; then
            log_info "SELinux is in permissive mode. Enabling ENFORCING mode for production security..."
            
            # Set to enforcing mode immediately
            if setenforce 1 2>/dev/null; then
                log_success "SELinux set to ENFORCING mode"
                SELINUX_STATUS="Enforcing"
            else
                log_warning "Could not set to enforcing. Will remain permissive."
            fi
            
            # Also update config for persistence
            if [ -f /etc/selinux/config ]; then
                sed -i 's/^SELINUX=permissive/SELINUX=enforcing/' /etc/selinux/config
                sed -i 's/^SELINUX=disabled/SELINUX=enforcing/' /etc/selinux/config
            fi
        else
            log_success "SELinux is already in ENFORCING mode ✓"
        fi
        
        SELINUX_ENABLED=true
    else
        log_warning "SELinux not available on this system"
        log_warning "For maximum security, consider using RHEL, Fedora, or CentOS"
        SELINUX_ENABLED=false
    fi
}

configure_selinux

################################################################################
# STEP 5: Copy Application Files
################################################################################
log_info "Step 5/10: Copying Nautilus files..."

SOURCE_DIR="/home/wrnash1/Developer/nautilus"
INSTALL_DIR="/var/www/html/nautilus"

if [ ! -d "$SOURCE_DIR" ]; then
    log_error "Source directory not found: $SOURCE_DIR"
    exit 1
fi

# Backup existing installation
if [ -d "$INSTALL_DIR" ]; then
    BACKUP_DIR="$INSTALL_DIR.backup.$(date +%Y%m%d_%H%M%S)"
    log_warning "Existing installation found. Backing up to $BACKUP_DIR"
    mv "$INSTALL_DIR" "$BACKUP_DIR"
fi

# Copy files
cp -r "$SOURCE_DIR" "$INSTALL_DIR"
log_success "Files copied to $INSTALL_DIR"

################################################################################
# STEP 6: Set Permissions and Ownership
################################################################################
log_info "Step 6/10: Setting file permissions and ownership..."

chown -R $WEB_USER:$WEB_GROUP "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"

# Writable directories
for dir in "storage" "public/uploads" "logs"; do
    if [ -d "$INSTALL_DIR/$dir" ]; then
        chmod -R 775 "$INSTALL_DIR/$dir"
        log_success "$dir → writable"
    fi
done

# PHP session directory
if [ -d "$PHP_SESSION_DIR" ]; then
    chown -R $WEB_USER:$WEB_GROUP "$PHP_SESSION_DIR"
    chmod 770 "$PHP_SESSION_DIR"
fi

log_success "Permissions set"

################################################################################
# STEP 7: Configure SELinux Contexts (if enabled)
################################################################################
if [ "$SELINUX_ENABLED" = true ]; then
    log_info "Step 7/10: Applying SELinux contexts..."
    
    # Web content
    semanage fcontext -a -t httpd_sys_content_t "$INSTALL_DIR(/.*)?)" 2>/dev/null || true
    restorecon -Rv "$INSTALL_DIR" > /dev/null 2>&1
    
    # Writable directories
    for dir in "storage" "public/uploads" "logs"; do
        if [ -d "$INSTALL_DIR/$dir" ]; then
            semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/$dir(/.*)?)" 2>/dev/null || true
            restorecon -Rv "$INSTALL_DIR/$dir" > /dev/null 2>&1
        fi
    done
    
    # PHP sessions
    if [ -d "$PHP_SESSION_DIR" ]; then
        semanage fcontext -a -t httpd_sys_rw_content_t "$PHP_SESSION_DIR(/.*)?)" 2>/dev/null || true
        restorecon -Rv "$PHP_SESSION_DIR" > /dev/null 2>&1
    fi
    
    # Enable required SELinux booleans
    setsebool -P httpd_can_network_connect_db 1 2>/dev/null || true
    setsebool -P httpd_can_sendmail 1 2>/dev/null || true
    setsebool -P httpd_can_network_connect 1 2>/dev/null || true
    setsebool -P httpd_execmem 1 2>/dev/null || true
    setsebool -P httpd_unified 1 2>/dev/null || true
    
    log_success "SELinux contexts applied"
else
    log_info "Step 7/10: Skipping SELinux configuration (not available)"
fi

################################################################################
# STEP 8: Configure SSL Certificate
################################################################################
log_info "Step 8/10: Configuring SSL certificate..."

SSL_CERT="$SSL_CERT_DIR/nautilus-selfsigned.crt"
SSL_KEY="$SSL_KEY_DIR/nautilus-selfsigned.key"
SSL_CONFIGURED=false

if [ "$SKIP_SSL" = false ]; then
    if [ ! -f "$SSL_CERT" ]; then
        log_info "Generating self-signed SSL certificate with SAN..."
        
        # Ensure directories exist
        mkdir -p "$SSL_CERT_DIR" "$SSL_KEY_DIR"
        
       # Create OpenSSL config for SAN (Subject Alternative Names)
        SSL_CONF="/tmp/nautilus-ssl.conf"
        cat > "$SSL_CONF" <<SSLCONF
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
SSLCONF
        
        # Generate certificate with SAN
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout "$SSL_KEY" \
            -out "$SSL_CERT" \
            -config "$SSL_CONF" \
            -extensions v3_req \
            2>/dev/null
        
        # Clean up temp config
        rm -f "$SSL_CONF"
        
        # Set proper permissions
        chmod 600 "$SSL_KEY"
        chmod 644 "$SSL_CERT"
        
        # Set SELinux contexts if SELinux is enabled
        if [ "$SELINUX_ENABLED" = true ]; then
            chcon -t cert_t "$SSL_CERT" 2>/dev/null || true
            chcon -t cert_t "$SSL_KEY" 2>/dev/null || true
            log_info "SELinux contexts set for SSL certificates"
        fi
        
        log_success "SSL certificate generated at $SSL_CERT"
        log_info "Certificate includes SAN for nautilus.local, www.nautilus.local, localhost"
        SSL_CONFIGURED=true
    else
        log_success "SSL certificate already exists at $SSL_CERT"
        SSL_CONFIGURED=true
    fi
else
    log_warning "Skipping SSL configuration (--skip-ssl flag used)"
fi

################################################################################
# STEP 9: Configure Web Server Virtual Host
################################################################################
log_info "Step 9/10: Configuring web server virtual host..."

create_vhost() {
    case $OS_FAMILY in
        debian)
            # Apache on Debian/Ubuntu uses sites-available/sites-enabled
            cat > "$WEB_CONF_DIR/nautilus.conf" <<'VHOST_EOF'
<VirtualHost *:80>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    
    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>
VHOST_EOF
            
            # Enable the site
            a2ensite nautilus.conf 2>/dev/null || true
            
            # Create HTTPS vhost if SSL is configured
            if [ "$SSL_CONFIGURED" = true ]; then
                cat > "$WEB_CONF_DIR/nautilus-ssl.conf" <<VHOST_SSL_EOF
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile $SSL_CERT
    SSLCertificateKeyFile $SSL_KEY
    
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

    ErrorLog /var/log/apache2/nautilus_ssl_error.log
    CustomLog /var/log/apache2/nautilus_ssl_access.log combined
</VirtualHost>
VHOST_SSL_EOF
                a2ensite nautilus-ssl.conf 2>/dev/null || true
            fi
            ;;
            
        rhel|arch|suse|alpine)
            # Other systems use conf.d directly
            cat > "$WEB_CONF_DIR/nautilus.conf" <<VHOST_EOF
<VirtualHost *:80>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    
    # Redirect all HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>
VHOST_EOF
            
            # Create HTTPS vhost if SSL is configured
            if [ "$SSL_CONFIGURED" = true ]; then
                cat > "$WEB_CONF_DIR/nautilus-ssl.conf" <<VHOST_SSL_EOF
<VirtualHost *:443>
    ServerName nautilus.local
    ServerAlias www.nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile $SSL_CERT
    SSLCertificateKeyFile $SSL_KEY
    
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

    ErrorLog $WEB_LOG_DIR/nautilus_ssl_error.log
    CustomLog $WEB_LOG_DIR/nautilus_ssl_access.log combined
</VirtualHost>
VHOST_SSL_EOF
            fi
            ;;
    esac
}

create_vhost
log_success "Virtual host configured"

# Update /etc/hosts
if ! grep -q "nautilus.local" /etc/hosts; then
    echo "127.0.0.1   nautilus.local www.nautilus.local" >> /etc/hosts
    log_success "Added nautilus.local to /etc/hosts"
fi

################################################################################
# STEP 10: Start and Enable Services
################################################################################
log_info "Step 10/10: Starting services..."

start_services() {
    log_info "Starting web server ($WEB_SERVER)..."
    systemctl start "$WEB_SERVER"
    systemctl enable "$WEB_SERVER"
    
    # Initialize and start database
    case $DB_TYPE in
        mysql)
            log_info "Starting MySQL..."
            systemctl enable "$DB_SERVICE" 2>/dev/null || systemctl enable mysql 2>/dev/null || true
            systemctl start "$DB_SERVICE" 2>/dev/null || systemctl start mysql 2>/dev/null || true
            ;;
        mariadb)
            log_info "Starting MariaDB..."
            systemctl enable "$DB_SERVICE" 2>/dev/null || systemctl enable mariadb 2>/dev/null || true
            systemctl start "$DB_SERVICE" 2>/dev/null || systemctl start mariadb 2>/dev/null || true
            ;;
        postgresql)
            log_info "Initializing PostgreSQL..."
            # Initialize PostgreSQL first (if not already done)
            if [ -f /usr/bin/postgresql-setup ]; then
                postgresql-setup --initdb || true  # RHEL/Fedora
            elif [ -f /usr/pgsql-*/bin/postgresql-*-setup ]; then
                /usr/pgsql-*/bin/postgresql-*-setup initdb || true
            fi
            
            log_info "Starting PostgreSQL..."
            systemctl enable "$DB_SERVICE" 2>/dev/null || systemctl enable postgresql 2>/dev/null || true
            systemctl start "$DB_SERVICE" 2>/dev/null || systemctl start postgresql 2>/dev/null || true
            ;;
    esac
    
    log_success "Services started"
}

# Detect init system
if command -v systemctl &> /dev/null; then
    # systemd
    start_services
elif command -v service &> /dev/null; then
    # sysvinit
    service "$WEB_SERVER" start
    service mysql start 2>/dev/null || service mariadb start 2>/dev/null || true
elif command -v rc-service &> /dev/null; then
    # OpenRC (Alpine)
    rc-service $WEB_SERVER start
    rc-update add $WEB_SERVER default
    rc-service mariadb start 2>/dev/null || true
    rc-update add mariadb default 2>/dev/null || true
fi

log_success "Services started"

# Configure firewall if available
if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-service=http 2>/dev/null || true
    firewall-cmd --permanent --add-service=https 2>/dev/null || true
    firewall-cmd --reload 2>/dev/null || true
    log_success "Firewall configured (ports 80/443)"
elif command -v ufw &> /dev/null; then
    ufw allow 80/tcp 2>/dev/null || true
    ufw allow 443/tcp 2>/dev/null || true
    log_success "Firewall configured (ports 80/443)"
fi

################################################################################
# Installation Complete
################################################################################
echo ""
echo "╔══════════════════════════════════════════════════════════╗"
echo "║           INSTALLATION COMPLETE!                         ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""
echo "📋 System Information:"
echo "   OS: $OS_PRETTY"
echo "   Web Server: $WEB_SERVER"
echo "   Database: $DB_TYPE ($DB_SERVICE)"
echo "   Install Location: $INSTALL_DIR"
echo "   SELinux: $(command -v getenforce &>/dev/null && getenforce || echo 'Not Available')"
if [ "$SSL_CONFIGURED" = true ]; then
    echo "   SSL: ✓ Enabled (self-signed)"
else
    echo "   SSL: ✗ Disabled"
fi
echo ""
echo "🌐 Access URLs:"
if [ "$SSL_CONFIGURED" = true ]; then
    echo "   Primary:   https://nautilus.local/install/"
    echo "   Alternate: http://nautilus.local/install/"
else
    echo "   Primary:   http://nautilus.local/install/"
fi
echo "   Direct IP: http://$(hostname -I | awk '{print $1}')/nautilus/public/install/"
echo ""
if [ "$SSL_CONFIGURED" = true ]; then
    echo "⚠️  SSL Certificate Warning:"
    echo "   The certificate is self-signed. Your browser will show a security"
    echo "   warning. This is normal for local development. Click 'Advanced'"
    echo "   and proceed to the site."
    echo ""
fi
if [ "$SELINUX_ENABLED" = true ]; then
    echo "🔒 Security Status:"
    echo "   SELinux is ENABLED and configured"
    echo "   Current mode: $(getenforce)"
    echo "   To check for denials: journalctl -xe | grep AVC"
    echo ""
fi
echo "✨ Next Steps:"
echo "   1. Open your browser"
echo "   2. Navigate to: http://nautilus.local/install/"
echo "   3. Complete the installation wizard"
echo "   4. Follow the on-screen instructions"
echo ""
echo "📖 Documentation: $INSTALL_DIR/INSTALLATION_GUIDE.md"
echo ""
log_success "Nautilus is ready to use!"
