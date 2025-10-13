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

echo -e "${YELLOW}This script will install Nautilus v6.0 with the following components:${NC}"
echo "  - Apache Web Server"
echo "  - PHP 8.2+"
echo "  - MySQL 8.0+"
echo "  - Composer"
echo "  - Let's Encrypt SSL Certificate"
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
apt-get update -qq

echo -e "${GREEN}[2/10] Installing required packages...${NC}"
apt-get install -y -qq apache2 mysql-server php8.2 php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd unzip git curl certbot python3-certbot-apache

if ! command -v composer &> /dev/null; then
    echo -e "${GREEN}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo -e "${GREEN}[3/10] Enabling Apache modules...${NC}"
a2enmod rewrite
a2enmod ssl
a2enmod headers

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

mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${YELLOW}Note: Database may already exist${NC}"
}

mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';" 2>/dev/null || {
    echo -e "${YELLOW}Note: Database user may already exist${NC}"
}

mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'$DB_HOST';" 2>/dev/null
mysql -e "FLUSH PRIVILEGES;" 2>/dev/null

echo -e "${GREEN}Running database migrations...${NC}"
for migration in database/migrations/*.sql; do
    echo "  - Running $(basename $migration)"
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration" 2>/dev/null || {
        echo -e "${YELLOW}    Warning: Migration may have already been run${NC}"
    }
done

echo -e "${GREEN}Seeding initial data...${NC}"
mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/seeds/001_seed_initial_data.sql 2>/dev/null || {
    echo -e "${YELLOW}Note: Seed data may already exist${NC}"
}

echo -e "${GREEN}[8/10] Configuring Apache virtual host...${NC}"
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

a2ensite $DOMAIN.conf
a2dissite 000-default.conf 2>/dev/null || true

echo -e "${GREEN}[9/10] Setting file permissions...${NC}"
chown -R www-data:www-data "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage"
chmod -R 775 "$INSTALL_DIR/public/uploads"

echo -e "${GREEN}[10/10] Installing Let's Encrypt SSL certificate...${NC}"
systemctl reload apache2

certbot --apache -d "$DOMAIN" --non-interactive --agree-tos --email "$ADMIN_EMAIL" --redirect || {
    echo -e "${RED}Error: Failed to install SSL certificate${NC}"
    echo -e "${YELLOW}Please ensure:${NC}"
    echo "  1. Domain $DOMAIN points to this server's IP address"
    echo "  2. Ports 80 and 443 are open in your firewall"
    echo "  3. Apache is running and accessible"
    echo ""
    echo -e "${YELLOW}You can manually run: certbot --apache -d $DOMAIN${NC}"
    echo ""
    read -p "Continue without SSL? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
}

systemctl restart apache2

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
echo "  Install Directory: $INSTALL_DIR"
echo "  Database: $DB_NAME"
echo "  Apache Config: /etc/apache2/sites-available/$DOMAIN.conf"
echo "  Apache Logs: /var/log/apache2/$DOMAIN-*.log"
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
