#!/bin/bash

###############################################################################
# Nautilus Universal Installer
# Works on any Linux distribution with any database (MySQL/MariaDB/PostgreSQL)
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

###############################################################################
# 1. DETECT OPERATING SYSTEM
###############################################################################
detect_os() {
    log_info "Detecting operating system..."
    
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    else
        log_error "Cannot detect OS. /etc/os-release not found."
        exit 1
    fi
    
    log_success "Detected: $PRETTY_NAME"
}

###############################################################################
# 2. DETECT PACKAGE MANAGER
###############################################################################
detect_package_manager() {
    log_info "Detecting package manager..."
    
    if command -v dnf &> /dev/null; then
        PKG_MANAGER="dnf"
        PKG_INSTALL="dnf install -y"
        PKG_UPDATE="dnf update -y"
    elif command -v yum &> /dev/null; then
        PKG_MANAGER="yum"
        PKG_INSTALL="yum install -y"
        PKG_UPDATE="yum update -y"
    elif command -v apt-get &> /dev/null; then
        PKG_MANAGER="apt"
        PKG_INSTALL="apt-get install -y"
        PKG_UPDATE="apt-get update"
    elif command -v zypper &> /dev/null; then
        PKG_MANAGER="zypper"
        PKG_INSTALL="zypper install -y"
        PKG_UPDATE="zypper refresh"
    elif command -v pacman &> /dev/null; then
        PKG_MANAGER="pacman"
        PKG_INSTALL="pacman -S --noconfirm"
        PKG_UPDATE="pacman -Sy"
    else
        log_error "No supported package manager found!"
        exit 1
    fi
    
    log_success "Package manager: $PKG_MANAGER"
}

###############################################################################
# 3. DETECT WEB SERVER
###############################################################################
detect_webserver() {
    log_info "Detecting web server..."
    
    if systemctl is-active --quiet httpd 2>/dev/null; then
        WEBSERVER="httpd"
        WEBSERVER_USER="apache"
        WEBSERVER_CONF_DIR="/etc/httpd/conf.d"
        WEBSERVER_LOG_DIR="/var/log/httpd"
    elif systemctl is-active --quiet apache2 2>/dev/null; then
        WEBSERVER="apache2"
        WEBSERVER_USER="www-data"
        WEBSERVER_CONF_DIR="/etc/apache2/sites-available"
        WEBSERVER_LOG_DIR="/var/log/apache2"
    elif systemctl is-active --quiet nginx 2>/dev/null; then
        WEBSERVER="nginx"
        WEBSERVER_USER="nginx"
        WEBSERVER_CONF_DIR="/etc/nginx/conf.d"
        WEBSERVER_LOG_DIR="/var/log/nginx"
    else
        log_warning "No active web server detected. Will install Apache/httpd."
        install_webserver
        return
    fi
    
    log_success "Web server: $WEBSERVER"
}

###############################################################################
# 4. INSTALL WEB SERVER
###############################################################################
install_webserver() {
    log_info "Installing web server..."
    
    case $PKG_MANAGER in
        dnf|yum)
            sudo $PKG_INSTALL httpd mod_ssl
            WEBSERVER="httpd"
            WEBSERVER_USER="apache"
            WEBSERVER_CONF_DIR="/etc/httpd/conf.d"
            WEBSERVER_LOG_DIR="/var/log/httpd"
            ;;
        apt)
            sudo $PKG_INSTALL apache2 libapache2-mod-php
            sudo a2enmod rewrite
            WEBSERVER="apache2"
            WEBSERVER_USER="www-data"
            WEBSERVER_CONF_DIR="/etc/apache2/sites-available"
            WEBSERVER_LOG_DIR="/var/log/apache2"
            ;;
        *)
            sudo $PKG_INSTALL apache2 || sudo $PKG_INSTALL httpd
            WEBSERVER="httpd"
            WEBSERVER_USER="apache"
            WEBSERVER_CONF_DIR="/etc/httpd/conf.d"
            WEBSERVER_LOG_DIR="/var/log/httpd"
            ;;
    esac
    
    sudo systemctl enable $WEBSERVER
    sudo systemctl start $WEBSERVER
    log_success "Web server installed and started"
}

###############################################################################
# 5. DETECT DATABASE
###############################################################################
detect_database() {
    log_info "Detecting database..."
    
    if systemctl is-active --quiet mariadb 2>/dev/null; then
        DB_TYPE="mariadb"
        DB_SERVICE="mariadb"
    elif systemctl is-active --quiet mysql 2>/dev/null; then
        DB_TYPE="mysql"
        DB_SERVICE="mysql"
    elif systemctl is-active --quiet postgresql 2>/dev/null; then
        DB_TYPE="postgresql"
        DB_SERVICE="postgresql"
    else
        log_warning "No active database detected. Will install MariaDB."
        install_database
        return
    fi
    
    log_success "Database: $DB_TYPE"
}

###############################################################################
# 6. INSTALL DATABASE
###############################################################################
install_database() {
    log_info "Installing MariaDB..."
    
    case $PKG_MANAGER in
        dnf|yum)
            sudo $PKG_INSTALL mariadb-server mariadb
            ;;
        apt)
            sudo $PKG_INSTALL mariadb-server mariadb-client
            ;;
        *)
            sudo $PKG_INSTALL mariadb-server
            ;;
    esac
    
    DB_TYPE="mariadb"
    DB_SERVICE="mariadb"
    
    sudo systemctl enable $DB_SERVICE
    sudo systemctl start $DB_SERVICE
    log_success "MariaDB installed and started"
}

###############################################################################
# 7. CONFIGURE WEB SERVER
###############################################################################
configure_webserver() {
    log_info "Configuring web server for Nautilus..."
    
    # Determine installation directory
    INSTALL_DIR="$SCRIPT_DIR"
    PUBLIC_DIR="$INSTALL_DIR/public"
    
    if [ "$WEBSERVER" = "httpd" ]; then
        # Apache/httpd configuration
        sudo tee "$WEBSERVER_CONF_DIR/nautilus.conf" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot $PUBLIC_DIR

    <Directory $PUBLIC_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog $WEBSERVER_LOG_DIR/nautilus_error.log
    CustomLog $WEBSERVER_LOG_DIR/nautilus_access.log combined
</VirtualHost>
EOF
        
    elif [ "$WEBSERVER" = "apache2" ]; then
        # Debian/Ubuntu Apache configuration
        sudo tee "$WEBSERVER_CONF_DIR/nautilus.conf" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot $PUBLIC_DIR

    <Directory $PUBLIC_DIR>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog $WEBSERVER_LOG_DIR/nautilus_error.log
    CustomLog $WEBSERVER_LOG_DIR/nautilus_access.log combined
</VirtualHost>
EOF
        sudo a2ensite nautilus.conf
        
    elif [ "$WEBSERVER" = "nginx" ]; then
        # Nginx configuration
        sudo tee "$WEBSERVER_CONF_DIR/nautilus.conf" > /dev/null <<EOF
server {
    listen 80;
    server_name localhost;
    root $PUBLIC_DIR;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    access_log $WEBSERVER_LOG_DIR/nautilus_access.log;
    error_log $WEBSERVER_LOG_DIR/nautilus_error.log;
}
EOF
    fi
    
    log_success "Web server configured"
}

###############################################################################
# 8. SET PERMISSIONS
###############################################################################
set_permissions() {
    log_info "Setting file permissions..."
    
    # Set ownership
    sudo chown -R $WEBSERVER_USER:$WEBSERVER_USER "$SCRIPT_DIR"
    
    # Set directory permissions
    sudo find "$SCRIPT_DIR" -type d -exec chmod 755 {} \;
    
    # Set file permissions
    sudo find "$SCRIPT_DIR" -type f -exec chmod 644 {} \;
    
    # Make storage writable
    sudo chmod -R 775 "$SCRIPT_DIR/storage"
    
    # SELinux contexts (if SELinux is enabled)
    if command -v setenforce &> /dev/null && [ "$(getenforce 2>/dev/null)" != "Disabled" ]; then
        log_info "Configuring SELinux contexts..."
        sudo semanage fcontext -a -t httpd_sys_content_t "$SCRIPT_DIR(/.*)?" 2>/dev/null || true
        sudo semanage fcontext -a -t httpd_sys_rw_content_t "$SCRIPT_DIR/storage(/.*)?" 2>/dev/null || true
        sudo restorecon -Rv "$SCRIPT_DIR" 2>/dev/null || true
        log_success "SELinux contexts configured"
    fi
    
    log_success "Permissions set"
}

###############################################################################
# 9. RESTART WEB SERVER
###############################################################################
restart_webserver() {
    log_info "Restarting web server..."
    sudo systemctl restart $WEBSERVER
    log_success "Web server restarted"
}

###############################################################################
# MAIN INSTALLATION FLOW
###############################################################################
main() {
    echo ""
    echo "╔═══════════════════════════════════════════════════════════╗"
    echo "║                                                           ║"
    echo "║           NAUTILUS UNIVERSAL INSTALLER                    ║"
    echo "║                                                           ║"
    echo "╚═══════════════════════════════════════════════════════════╝"
    echo ""
    
    # Check if running as root or with sudo
    if [ "$EUID" -eq 0 ]; then
        log_warning "Running as root. This is not recommended."
        log_warning "Please run as a regular user. Sudo will be used when needed."
        exit 1
    fi
    
    # Detect system
    detect_os
    detect_package_manager
    detect_webserver
    detect_database
    
    # Configure
    configure_webserver
    set_permissions
    restart_webserver
    
    echo ""
    log_success "═══════════════════════════════════════════════════════════"
    log_success "  Installation Complete!"
    log_success "═══════════════════════════════════════════════════════════"
    echo ""
    log_info "Next steps:"
    log_info "1. Open your browser and navigate to: http://localhost/"
    log_info "2. Complete the web-based installation wizard"
    log_info "3. Enter your database credentials and admin account details"
    echo ""
    log_info "Database credentials needed:"
    log_info "  - Host: localhost"
    log_info "  - Port: 3306 (MySQL/MariaDB) or 5432 (PostgreSQL)"
    log_info "  - Database: nautilus (will be created if it doesn't exist)"
    log_info "  - Username: root (or your database user)"
    log_info "  - Password: (your database password)"
    echo ""
}

# Run main function
main
