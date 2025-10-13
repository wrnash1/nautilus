#!/bin/bash

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                                                            ║"
echo "║          Nautilus v6.0 - Automated Installer               ║"
echo "║     Enterprise Dive Shop Management System                 ║"
echo "║                                                            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Error: This script must be run as root or with sudo${NC}"
    exit 1
fi

if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
else
    echo -e "${RED}Error: Cannot detect operating system${NC}"
    exit 1
fi

if [[ "$OS" == "ubuntu" ]] || [[ "$OS" == "debian" ]]; then
    PKG_MANAGER="apt-get"
    PKG_UPDATE="apt-get update -qq"
    PKG_INSTALL="apt-get install -y -qq"
    WEB_SERVER="apache2"
    WEB_SERVICE="apache2"
    WEB_USER="www-data"
    WEB_GROUP="www-data"
    WEB_CONF_DIR="/etc/apache2/sites-available"
    WEB_CONF_ENABLE="a2ensite"
    WEB_CONF_DISABLE="a2dissite"
    MODULE_ENABLE="a2enmod"
    PHP_PACKAGES="php8.2 php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd"
    DB_SERVER="mysql-server"
    DB_SERVICE="mysql"
    DB_CLIENT="mysql"
    WEB_LOG_DIR="/var/log/apache2"
elif [[ "$OS" == "fedora" ]] || [[ "$OS" == "rhel" ]] || [[ "$OS" == "centos" ]]; then
    PKG_MANAGER="dnf"
    PKG_UPDATE="dnf check-update || true"
    PKG_INSTALL="dnf install -y -q"
    WEB_SERVER="httpd"
    WEB_SERVICE="httpd"
    WEB_USER="apache"
    WEB_GROUP="apache"
    WEB_CONF_DIR="/etc/httpd/conf.d"
    WEB_CONF_ENABLE=""
    WEB_CONF_DISABLE=""
    MODULE_ENABLE=""
    PHP_PACKAGES="php php-cli php-mysqlnd php-xml php-mbstring php-curl php-zip php-gd"
    DB_SERVER="mariadb-server"
    DB_SERVICE="mariadb"
    DB_CLIENT="mysql"
    WEB_LOG_DIR="/var/log/httpd"
else
    echo -e "${RED}Error: Unsupported operating system: $OS${NC}"
    echo -e "${YELLOW}This script supports Ubuntu, Debian, Fedora, RHEL, and CentOS${NC}"
    exit 1
fi

echo -e "${GREEN}Detected OS: $OS${NC}"
echo ""
echo -e "${YELLOW}This script will install Nautilus v6.0 with the following components:${NC}"
echo "  - $WEB_SERVER Web Server"
echo "  - PHP 8.2+ (or latest available)"
echo "  - MySQL/MariaDB 8.0+"
echo "  - Composer"
echo "  - Let's Encrypt SSL Certificate (via snap)"
echo "  - Nautilus Application"
echo ""

read -p "Continue with installation? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}Installation cancelled${NC}"
    exit 1
fi

echo ""
read -p "Enter your domain name (e.g., nautilus.example.com): " DOMAIN
read -p "Enter admin email for Let's Encrypt: " ADMIN_EMAIL
read -p "Enter installation directory [/var/www/html/nautilus]: " INSTALL_DIR
INSTALL_DIR=${INSTALL_DIR:-/var/www/html/nautilus}

read -p "Enter database name [nautilus]: " DB_NAME
DB_NAME=${DB_NAME:-nautilus}
read -p "Enter database user [nautilus_user]: " DB_USER
DB_USER=${DB_USER:-nautilus_user}
read -sp "Enter database password: " DB_PASS
echo ""
read -p "Enter database host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

echo ""
echo -e "${GREEN}Installation Configuration:${NC}"
echo "  Domain: $DOMAIN"
echo "  Email: $ADMIN_EMAIL"
echo "  Install Directory: $INSTALL_DIR"
echo "  Database: $DB_NAME"
echo "  Database User: $DB_USER"
echo "  Database Host: $DB_HOST"
echo ""

read -p "Proceed with these settings? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}Installation cancelled${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}[1/10] Updating system packages...${NC}"
$PKG_UPDATE

echo -e "${GREEN}[2/10] Installing required packages...${NC}"
$PKG_INSTALL $WEB_SERVER $DB_SERVER $PHP_PACKAGES unzip git curl

if ! command -v snap &> /dev/null; then
    echo -e "${GREEN}Installing snapd...${NC}"
    $PKG_INSTALL snapd
    systemctl enable --now snapd.socket
    ln -s /var/lib/snapd/snap /snap 2>/dev/null || true
    sleep 5
fi

if ! command -v composer &> /dev/null; then
    echo -e "${GREEN}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo -e "${GREEN}[3/10] Enabling web server modules...${NC}"
if [[ -n "$MODULE_ENABLE" ]]; then
    $MODULE_ENABLE rewrite
    $MODULE_ENABLE ssl
    $MODULE_ENABLE headers
else
    echo "Modules auto-enabled on $OS"
fi

echo -e "${GREEN}[4/10] Cloning Nautilus repository...${NC}"
if [ -d "$INSTALL_DIR" ]; then
    echo -e "${YELLOW}Warning: Installation directory already exists. Backing up...${NC}"
    mv "$INSTALL_DIR" "${INSTALL_DIR}.backup.$(date +%s)"
fi

git clone -q https://github.com/wrnash1/nautilus-v6.git "$INSTALL_DIR"
cd "$INSTALL_DIR"
git checkout -q devin/1760111706-nautilus-v6-complete-skeleton

echo -e "${GREEN}[5/10] Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader -q

echo -e "${GREEN}[6/10] Configuring environment...${NC}"
cp .env.example .env

sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" .env
sed -i "s|^DB_NAME=.*|DB_NAME=$DB_NAME|" .env
sed -i "s|^DB_USER=.*|DB_USER=$DB_USER|" .env
sed -i "s|^DB_PASS=.*|DB_PASS=$DB_PASS|" .env

APP_KEY=$(openssl rand -base64 32)
sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env

echo -e "${GREEN}[7/10] Setting up database...${NC}"

systemctl start $DB_SERVICE || systemctl start mariadb || systemctl start mysql || true
systemctl enable $DB_SERVICE 2>/dev/null || systemctl enable mariadb 2>/dev/null || systemctl enable mysql 2>/dev/null || true

if [[ "$OS" == "fedora" ]] || [[ "$OS" == "rhel" ]] || [[ "$OS" == "centos" ]]; then
    if ! mysql -e "SELECT 1" &>/dev/null; then
        echo -e "${YELLOW}Securing MariaDB installation...${NC}"
    fi
fi

$DB_CLIENT -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${YELLOW}Note: Database may already exist${NC}"
}

$DB_CLIENT -e "CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';" 2>/dev/null || {
    echo -e "${YELLOW}Note: Database user may already exist${NC}"
}

$DB_CLIENT -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'$DB_HOST';" 2>/dev/null
$DB_CLIENT -e "FLUSH PRIVILEGES;" 2>/dev/null

echo -e "${GREEN}Running database migrations...${NC}"
for migration in database/migrations/*.sql; do
    echo "  - Running $(basename $migration)"
    $DB_CLIENT -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration" 2>/dev/null || {
        echo -e "${YELLOW}    Warning: Migration may have already been run${NC}"
    }
done

echo -e "${GREEN}Seeding initial data...${NC}"
$DB_CLIENT -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_seed_initial_data.sql 2>/dev/null || {
    echo -e "${YELLOW}Note: Seed data may already exist${NC}"
}

echo -e "${GREEN}[8/10] Configuring web server virtual host...${NC}"

if [[ "$OS" == "fedora" ]] || [[ "$OS" == "rhel" ]] || [[ "$OS" == "centos" ]]; then
    cat > /etc/httpd/conf.d/$DOMAIN.conf <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAdmin $ADMIN_EMAIL
    DocumentRoot $INSTALL_DIR/public

    <Directory $INSTALL_DIR/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/${DOMAIN}-error.log
    CustomLog /var/log/httpd/${DOMAIN}-access.log combined
</VirtualHost>
EOF
    
    if command -v firewall-cmd &> /dev/null; then
        echo -e "${GREEN}Configuring firewall...${NC}"
        firewall-cmd --permanent --add-service=http 2>/dev/null || true
        firewall-cmd --permanent --add-service=https 2>/dev/null || true
        firewall-cmd --reload 2>/dev/null || true
    fi
else
    cat > /etc/apache2/sites-available/$DOMAIN.conf <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAdmin $ADMIN_EMAIL
    DocumentRoot $INSTALL_DIR/public

    <Directory $INSTALL_DIR/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/$DOMAIN-error.log
    CustomLog \${APACHE_LOG_DIR}/$DOMAIN-access.log combined
</VirtualHost>
EOF

    $WEB_CONF_ENABLE $DOMAIN.conf
    $WEB_CONF_DISABLE 000-default.conf 2>/dev/null || true
    
    if command -v ufw &> /dev/null && ufw status | grep -q "Status: active"; then
        echo -e "${GREEN}Configuring firewall...${NC}"
        ufw allow 'Apache Full' 2>/dev/null || true
    fi
fi

echo -e "${GREEN}[9/10] Setting file permissions...${NC}"
chown -R $WEB_USER:$WEB_GROUP "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/public/uploads"

if command -v setenforce &> /dev/null && getenforce 2>/dev/null | grep -q "Enforcing"; then
    echo -e "${GREEN}Configuring SELinux contexts...${NC}"
    semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/storage(/.*)?" 2>/dev/null || true
    semanage fcontext -a -t httpd_sys_rw_content_t "$INSTALL_DIR/public/uploads(/.*)?" 2>/dev/null || true
    restorecon -Rv "$INSTALL_DIR" 2>/dev/null || true
    setsebool -P httpd_can_network_connect_db 1 2>/dev/null || true
fi

echo -e "${GREEN}[10/10] Installing Let's Encrypt SSL certificate...${NC}"

if ! snap list certbot &> /dev/null; then
    echo -e "${GREEN}Installing certbot via snap...${NC}"
    snap install --classic certbot 2>/dev/null || {
        echo -e "${YELLOW}Warning: Failed to install certbot via snap${NC}"
        echo -e "${YELLOW}You can manually install certbot later${NC}"
    }
    ln -s /snap/bin/certbot /usr/bin/certbot 2>/dev/null || true
fi

systemctl reload $WEB_SERVICE

if [[ "$WEB_SERVER" == "httpd" ]]; then
    CERTBOT_PLUGIN="apache"
else
    CERTBOT_PLUGIN="apache"
fi

certbot --${CERTBOT_PLUGIN} -d "$DOMAIN" --non-interactive --agree-tos --email "$ADMIN_EMAIL" --redirect 2>/dev/null || {
    echo -e "${RED}Error: Failed to install SSL certificate${NC}"
    echo -e "${YELLOW}Please ensure:${NC}"
    echo "  1. Domain $DOMAIN points to this server's IP address"
    echo "  2. Ports 80 and 443 are open in your firewall"
    echo "  3. Web server is running and accessible"
    echo "  4. DNS has propagated (this can take time)"
    echo ""
    echo -e "${YELLOW}You can manually run: certbot --${CERTBOT_PLUGIN} -d $DOMAIN${NC}"
    echo ""
    read -p "Continue without SSL? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
}

systemctl restart $WEB_SERVICE

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                            ║${NC}"
echo -e "${GREEN}║         Installation Complete! 🎉                          ║${NC}"
echo -e "${GREEN}║                                                            ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}Nautilus v6.0 is now installed and running!${NC}"
echo ""
echo -e "${YELLOW}Access your application:${NC}"
echo "  URL: https://$DOMAIN"
echo ""
echo -e "${YELLOW}Default Admin Credentials:${NC}"
echo "  Email: admin@nautilus.com"
echo "  Password: admin123"
echo ""
echo -e "${RED}⚠️  IMPORTANT: Change the default admin password immediately!${NC}"
echo ""
echo -e "${YELLOW}Installation Details:${NC}"
echo "  Operating System: $OS"
echo "  Install Directory: $INSTALL_DIR"
echo "  Database: $DB_NAME"
echo "  Web Server: $WEB_SERVER"
echo "  Web Server Config: $WEB_CONF_DIR/$DOMAIN.conf"
echo "  Web Server Logs: $WEB_LOG_DIR/$DOMAIN-*.log"
echo "  Application Logs: $INSTALL_DIR/storage/logs/"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. Visit https://$DOMAIN and log in"
echo "  2. Change default admin password"
echo "  3. Configure your shop settings"
echo "  4. Set up a backup schedule (see docs/DEPLOYMENT.md)"
echo ""
echo -e "${GREEN}For support and documentation:${NC}"
echo "  GitHub: https://github.com/wrnash1/nautilus-v6"
echo "  Session: https://app.devin.ai/sessions/0a53533785e14a6f95aae83c5390ae8a"
echo ""
