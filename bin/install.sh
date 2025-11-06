#!/bin/bash

# Nautilus Dive Shop - Production Installation Script
# This script sets up the Nautilus application for production use

set -e  # Exit on any error

echo "================================================"
echo "  Nautilus Dive Shop - Installation Script"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_error "Please do not run this script as root. Run as a regular user with sudo privileges."
    exit 1
fi

# Get the installation directory
INSTALL_DIR=$(pwd)
print_info "Installation directory: $INSTALL_DIR"

# Step 1: Check prerequisites
print_info "Checking prerequisites..."

# Check for PHP
if ! command -v php &> /dev/null; then
    print_error "PHP is not installed. Please install PHP 8.2 or higher."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d '.' -f 1,2)
if [ "$(printf '%s\n' "8.2" "$PHP_VERSION" | sort -V | head -n1)" != "8.2" ]; then
    print_error "PHP version must be 8.2 or higher. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP $PHP_VERSION found"

# Check for MySQL
if ! command -v mysql &> /dev/null; then
    print_error "MySQL is not installed. Please install MySQL 8.0 or higher."
    exit 1
fi
print_success "MySQL found"

# Check for Composer
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install Composer."
    exit 1
fi
print_success "Composer found"

# Check for Apache or Nginx
if command -v apache2 &> /dev/null; then
    WEB_SERVER="apache2"
    print_success "Apache web server found"
elif command -v nginx &> /dev/null; then
    WEB_SERVER="nginx"
    print_success "Nginx web server found"
else
    print_error "No web server (Apache or Nginx) found. Please install a web server."
    exit 1
fi

# Step 2: Install PHP dependencies
print_info "Installing PHP dependencies via Composer..."
composer install --no-dev --optimize-autoloader
print_success "Composer dependencies installed"

# Step 3: Set up environment file
print_info "Setting up environment configuration..."

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success "Created .env file from example"
    else
        print_error ".env.example file not found"
        exit 1
    fi
else
    print_info ".env file already exists"
fi

# Step 4: Database configuration
print_info "Database Configuration"
echo ""

read -p "Enter MySQL database name [nautilus]: " DB_NAME
DB_NAME=${DB_NAME:-nautilus}

read -p "Enter MySQL username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -s -p "Enter MySQL password: " DB_PASS
echo ""

read -p "Enter MySQL host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

# Update .env file
sed -i "s/DB_NAME=.*/DB_NAME=$DB_NAME/" .env
sed -i "s/DB_USER=.*/DB_USER=$DB_USER/" .env
sed -i "s/DB_PASS=.*/DB_PASS=$DB_PASS/" .env
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env

print_success "Database configuration updated"

# Step 5: Create database
print_info "Creating database..."
mysql -u"$DB_USER" -p"$DB_PASS" -h"$DB_HOST" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    print_success "Database '$DB_NAME' created successfully"
else
    print_error "Failed to create database. Please check your MySQL credentials."
    exit 1
fi

# Step 6: Run migrations
print_info "Running database migrations..."
php scripts/migrate.php
print_success "Database migrations completed"

# Step 7: Set up file permissions
print_info "Setting file permissions..."

# Make sure web server can write to certain directories
sudo chown -R www-data:www-data storage/ public/uploads/ logs/ 2>/dev/null || \
sudo chown -R $USER:$USER storage/ public/uploads/ logs/

chmod -R 775 storage/ public/uploads/ logs/
chmod -R 755 public/

print_success "File permissions set"

# Step 8: Web server configuration
print_info "Configuring web server..."

if [ "$WEB_SERVER" == "apache2" ]; then
    # Apache configuration
    print_info "Setting up Apache virtual host..."

    read -p "Enter domain name (e.g., nautilus.local): " DOMAIN
    DOMAIN=${DOMAIN:-nautilus.local}

    sudo tee /etc/apache2/sites-available/nautilus.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    DocumentRoot $INSTALL_DIR/public

    <Directory $INSTALL_DIR/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog \${APACHE_LOG_DIR}/nautilus-access.log combined
</VirtualHost>
EOF

    sudo a2ensite nautilus.conf
    sudo a2enmod rewrite
    sudo systemctl reload apache2

    print_success "Apache configured for $DOMAIN"

elif [ "$WEB_SERVER" == "nginx" ]; then
    # Nginx configuration
    print_info "Setting up Nginx server block..."

    read -p "Enter domain name (e.g., nautilus.local): " DOMAIN
    DOMAIN=${DOMAIN:-nautilus.local}

    sudo tee /etc/nginx/sites-available/nautilus > /dev/null <<EOF
server {
    listen 80;
    server_name $DOMAIN;
    root $INSTALL_DIR/public;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

    sudo ln -sf /etc/nginx/sites-available/nautilus /etc/nginx/sites-enabled/
    sudo nginx -t && sudo systemctl reload nginx

    print_success "Nginx configured for $DOMAIN"
fi

# Step 9: Add domain to hosts file (for local testing)
if [[ "$DOMAIN" == *.local ]]; then
    if ! grep -q "$DOMAIN" /etc/hosts; then
        print_info "Adding $DOMAIN to /etc/hosts file..."
        echo "127.0.0.1    $DOMAIN" | sudo tee -a /etc/hosts > /dev/null
        print_success "Added $DOMAIN to hosts file"
    fi
fi

# Step 10: Create default admin user
print_info "Creating default admin user..."

read -p "Enter admin email: " ADMIN_EMAIL
read -p "Enter admin first name: " ADMIN_FIRST
read -p "Enter admin last name: " ADMIN_LAST
read -s -p "Enter admin password: " ADMIN_PASS
echo ""

# Create admin user via PHP script
php -r "
require 'vendor/autoload.php';
use App\Core\Database;
use App\Models\User;

Database::connect();

\$hashedPassword = password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);

Database::query(\"
    INSERT INTO users (first_name, last_name, email, password, role, is_active, created_at)
    VALUES (?, ?, ?, ?, 'admin', 1, NOW())
    ON DUPLICATE KEY UPDATE email = email
\", ['$ADMIN_FIRST', '$ADMIN_LAST', '$ADMIN_EMAIL', \$hashedPassword]);

echo 'Admin user created successfully';
" 2>/dev/null

print_success "Admin user created"

# Step 11: Installation complete
echo ""
echo "================================================"
print_success "Installation Complete!"
echo "================================================"
echo ""
echo "Access your Nautilus installation at:"
echo "  http://$DOMAIN"
echo ""
echo "Login with:"
echo "  Email: $ADMIN_EMAIL"
echo "  Password: [your password]"
echo ""
print_info "Important: Please change the default admin password after first login!"
echo ""
print_info "To enable HTTPS, you can use Let's Encrypt:"
echo "  sudo certbot --${WEB_SERVER}"
echo ""
print_success "Happy diving! 🤿"
echo ""
