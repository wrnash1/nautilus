#!/bin/bash

###############################################################################
# Nautilus Dive Shop - Automated Setup Script
# Works on: Ubuntu, Debian, Fedora, RHEL, Pop!_OS, and other Linux distros
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════╗
║   Nautilus Dive Shop - Setup Script      ║
║   Automated Installation Preparation      ║
╚═══════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    OS_VERSION=$VERSION_ID
else
    print_error "Cannot detect operating system"
    exit 1
fi

print_info "Detected OS: $PRETTY_NAME"

# Detect web server user
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
    WEB_GROUP="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
    WEB_GROUP="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
    WEB_GROUP="nginx"
else
    print_warning "Could not detect web server user, using current user"
    WEB_USER=$(whoami)
    WEB_GROUP=$(id -gn)
fi

print_info "Web server user: $WEB_USER:$WEB_GROUP"

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

print_info "Working directory: $SCRIPT_DIR"

# Step 1: Check if running with appropriate permissions
echo ""
print_info "Step 1: Checking permissions..."

if [ "$EUID" -ne 0 ] && [ "$WEB_USER" != "$(whoami)" ]; then
    print_warning "Not running as root or web server user"
    print_info "You may need to run: sudo $0"
    print_info "Or run: sudo chown -R $WEB_USER:$WEB_GROUP ."
fi

# Step 2: Create .env file if it doesn't exist
echo ""
print_info "Step 2: Checking .env configuration..."

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        print_success "Created .env from .env.example"
        print_warning "Please edit .env and configure your database settings"
        print_info "Database configuration needed:"
        print_info "  - DB_HOST (default: localhost)"
        print_info "  - DB_PORT (default: 3306)"
        print_info "  - DB_DATABASE (database name)"
        print_info "  - DB_USERNAME (database user)"
        print_info "  - DB_PASSWORD (database password)"
    else
        print_error ".env.example not found!"
        exit 1
    fi
else
    print_success ".env file already exists"
fi

# Step 3: Create required directories
echo ""
print_info "Step 3: Creating required directories..."

REQUIRED_DIRS=(
    "storage"
    "storage/logs"
    "storage/uploads"
    "storage/uploads/products"
    "storage/uploads/customers"
    "storage/uploads/certifications"
    "storage/cache"
    "storage/sessions"
    "storage/temp"
)

for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        print_success "Created directory: $dir"
    else
        print_success "Directory exists: $dir"
    fi
done

# Step 4: Set proper permissions
echo ""
print_info "Step 4: Setting file permissions..."

# Set directory permissions
chmod -R 755 .
print_success "Set base permissions to 755"

# Set storage directory permissions
chmod -R 775 storage
print_success "Set storage permissions to 775"

# Make sure .env is not publicly readable
if [ -f ".env" ]; then
    chmod 640 .env
    print_success "Protected .env file (640)"
fi

# Step 5: Set ownership
echo ""
print_info "Step 5: Setting file ownership..."

if [ "$EUID" -eq 0 ]; then
    chown -R $WEB_USER:$WEB_GROUP .
    print_success "Set ownership to $WEB_USER:$WEB_GROUP"
else
    print_warning "Not running as root - skipping ownership change"
    print_info "Run: sudo chown -R $WEB_USER:$WEB_GROUP $SCRIPT_DIR"
fi

# Step 6: Check for Composer
echo ""
print_info "Step 6: Checking Composer dependencies..."

if [ ! -d "vendor" ]; then
    if command -v composer &> /dev/null; then
        print_info "Running composer install..."
        composer install --no-dev --optimize-autoloader
        print_success "Composer dependencies installed"
    else
        print_error "Composer is not installed and vendor/ directory is missing"
        print_info "Please install Composer from https://getcomposer.org/"
        print_info "Then run: composer install --no-dev --optimize-autoloader"
        exit 1
    fi
else
    print_success "Composer dependencies already installed"
fi

# Step 7: Check PHP version and extensions
echo ""
print_info "Step 7: Checking PHP requirements..."

PHP_VERSION=$(php -r "echo PHP_VERSION;" 2>/dev/null || echo "0")
REQUIRED_VERSION="8.1.0"

if php -r "exit(version_compare(PHP_VERSION, '$REQUIRED_VERSION', '<') ? 1 : 0);"; then
    print_success "PHP version: $PHP_VERSION (>= $REQUIRED_VERSION required)"
else
    print_error "PHP version $PHP_VERSION is too old (>= $REQUIRED_VERSION required)"
    exit 1
fi

# Check required extensions
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "json" "openssl" "curl" "fileinfo" "gd" "zip")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -r "exit(extension_loaded('$ext') ? 0 : 1);"; then
        print_success "PHP extension: $ext"
    else
        print_error "Missing PHP extension: $ext"
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
    echo ""
    print_error "Missing required PHP extensions!"

    # Ask if user wants to auto-install
    if [ "$EUID" -eq 0 ]; then
        read -p "Would you like to install missing extensions automatically? (y/n) " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            print_info "Installing missing extensions..."

            case "$OS" in
                ubuntu|debian|pop)
                    apt update
                    for ext in "${MISSING_EXTENSIONS[@]}"; do
                        if [ "$ext" = "pdo_mysql" ]; then
                            apt install -y php-mysql
                        else
                            apt install -y php-$ext
                        fi
                    done
                    systemctl restart apache2 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true
                    print_success "Extensions installed. Restart complete."
                    ;;
                fedora|rhel|centos)
                    dnf install -y $(for ext in "${MISSING_EXTENSIONS[@]}"; do
                        if [ "$ext" = "pdo_mysql" ]; then
                            echo "php-mysqlnd"
                        else
                            echo "php-$ext"
                        fi
                    done)
                    systemctl restart httpd 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true
                    print_success "Extensions installed. Restart complete."
                    ;;
                *)
                    print_error "Automatic installation not supported for this distribution"
                    echo "  Please install the missing PHP extensions manually"
                    exit 1
                    ;;
            esac

            # Re-check extensions
            MISSING_AFTER_INSTALL=()
            for ext in "${MISSING_EXTENSIONS[@]}"; do
                if ! php -r "exit(extension_loaded('$ext') ? 0 : 1);"; then
                    MISSING_AFTER_INSTALL+=("$ext")
                fi
            done

            if [ ${#MISSING_AFTER_INSTALL[@]} -gt 0 ]; then
                print_error "Some extensions still missing after installation: ${MISSING_AFTER_INSTALL[*]}"
                exit 1
            fi
        else
            print_info "Manual installation required. Run these commands:"
            case "$OS" in
                ubuntu|debian|pop)
                    for ext in "${MISSING_EXTENSIONS[@]}"; do
                        if [ "$ext" = "pdo_mysql" ]; then
                            echo "  sudo apt install php-mysql"
                        else
                            echo "  sudo apt install php-$ext"
                        fi
                    done
                    ;;
                fedora|rhel|centos)
                    for ext in "${MISSING_EXTENSIONS[@]}"; do
                        if [ "$ext" = "pdo_mysql" ]; then
                            echo "  sudo dnf install php-mysqlnd"
                        else
                            echo "  sudo dnf install php-$ext"
                        fi
                    done
                    ;;
            esac
            exit 1
        fi
    else
        print_info "Install missing extensions with these commands:"
        case "$OS" in
            ubuntu|debian|pop)
                for ext in "${MISSING_EXTENSIONS[@]}"; do
                    if [ "$ext" = "pdo_mysql" ]; then
                        echo "  sudo apt install php-mysql"
                    else
                        echo "  sudo apt install php-$ext"
                    fi
                done
                ;;
            fedora|rhel|centos)
                for ext in "${MISSING_EXTENSIONS[@]}"; do
                    if [ "$ext" = "pdo_mysql" ]; then
                        echo "  sudo dnf install php-mysqlnd"
                    else
                        echo "  sudo dnf install php-$ext"
                    fi
                done
                ;;
            *)
                echo "  Please install the missing PHP extensions for your distribution"
                ;;
        esac
        print_info "Then re-run this script with: sudo $0"
        exit 1
    fi
fi

# Step 8: Test database connection (optional)
echo ""
print_info "Step 8: Testing database connection..."

if [ -f ".env" ] && [ -d "vendor" ]; then
    if php -r "
        require 'vendor/autoload.php';
        \$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        \$dotenv->load();
        try {
            \$pdo = new PDO(
                'mysql:host=' . \$_ENV['DB_HOST'] . ';port=' . (\$_ENV['DB_PORT'] ?? 3306),
                \$_ENV['DB_USERNAME'],
                \$_ENV['DB_PASSWORD']
            );
            echo 'SUCCESS';
        } catch (Exception \$e) {
            echo 'FAILED: ' . \$e->getMessage();
            exit(1);
        }
    " 2>&1 | grep -q "SUCCESS"; then
        print_success "Database connection successful"
    else
        print_warning "Database connection failed - please check .env settings"
        print_info "You can continue, but you'll need to fix database settings before installation"
    fi
else
    print_warning "Skipping database test (.env or vendor missing)"
fi

# Step 9: Generate security keys
echo ""
print_info "Step 9: Checking security keys..."

if grep -q "your-secret-key-here" .env 2>/dev/null; then
    print_warning "Default security keys detected in .env"
    print_info "The installer will generate new keys automatically"
else
    print_success "Security keys are configured"
fi

# Step 10: Configure Apache Virtual Host
echo ""
print_info "Step 10: Configuring Apache Virtual Host..."

# Detect web server
WEB_SERVER=""
if systemctl is-active --quiet httpd 2>/dev/null; then
    WEB_SERVER="httpd"
    VHOST_DIR="/etc/httpd/conf.d"
elif systemctl is-active --quiet apache2 2>/dev/null; then
    WEB_SERVER="apache2"
    VHOST_DIR="/etc/apache2/sites-available"
fi

if [ -n "$WEB_SERVER" ] && [ "$EUID" -eq 0 ]; then
    if [ -f "apache-config/nautilus.conf" ]; then
        # Update paths in config file
        INSTALL_PATH="$(cd "$(dirname "$SCRIPT_DIR")" && pwd)"

        read -p "Configure Apache virtual host? (y/n) " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            # Create temporary config with correct path
            sed "s|/var/www/html/nautilus|$INSTALL_PATH|g" apache-config/nautilus.conf > /tmp/nautilus.conf

            if [ "$WEB_SERVER" = "httpd" ]; then
                cp /tmp/nautilus.conf $VHOST_DIR/nautilus.conf
                print_success "Copied virtual host configuration to $VHOST_DIR/nautilus.conf"

                # Enable mod_rewrite if not already enabled
                if ! httpd -M 2>/dev/null | grep -q rewrite_module; then
                    print_warning "mod_rewrite may not be enabled"
                fi

                systemctl restart httpd
                print_success "Apache (httpd) restarted"

            elif [ "$WEB_SERVER" = "apache2" ]; then
                cp /tmp/nautilus.conf $VHOST_DIR/nautilus.conf
                a2ensite nautilus.conf 2>/dev/null || true
                a2enmod rewrite 2>/dev/null || true
                systemctl restart apache2
                print_success "Apache virtual host configured and enabled"
            fi

            rm /tmp/nautilus.conf

            # Add to /etc/hosts if not present
            if ! grep -q "nautilus.local" /etc/hosts 2>/dev/null; then
                read -p "Add 'nautilus.local' to /etc/hosts for local development? (y/n) " -n 1 -r
                echo ""
                if [[ $REPLY =~ ^[Yy]$ ]]; then
                    echo "127.0.0.1    nautilus.local" >> /etc/hosts
                    print_success "Added nautilus.local to /etc/hosts"
                fi
            fi

            print_success "Apache configuration complete!"
            print_info "You can now access Nautilus at: http://nautilus.local"
        else
            print_warning "Skipped Apache configuration"
            print_info "To configure manually, copy apache-config/nautilus.conf to $VHOST_DIR/"
        fi
    else
        print_warning "apache-config/nautilus.conf not found"
    fi
elif [ "$EUID" -ne 0 ]; then
    print_warning "Not running as root - skipping Apache configuration"
    print_info "To configure Apache, run: sudo cp apache-config/nautilus.conf /etc/httpd/conf.d/"
    print_info "Then restart Apache: sudo systemctl restart httpd"
else
    print_warning "No active Apache/httpd service detected"
fi

# Step 11: Final checks
echo ""
print_info "Step 11: Final verification..."

ISSUES=0

# Check if .env exists
if [ ! -f ".env" ]; then
    print_error ".env file missing"
    ((ISSUES++))
fi

# Check if vendor exists
if [ ! -d "vendor" ]; then
    print_error "vendor/ directory missing (run composer install)"
    ((ISSUES++))
fi

# Check if storage is writable
if [ ! -w "storage" ]; then
    print_error "storage/ directory is not writable"
    ((ISSUES++))
fi

echo ""
echo -e "${BLUE}═══════════════════════════════════════════${NC}"

if [ $ISSUES -eq 0 ]; then
    print_success "Setup complete! Your server is ready for installation."
    echo ""
    print_info "Next steps:"
    echo "  1. Edit .env and configure your database settings"
    echo "  2. Open your browser and go to: http://nautilus.local/install.php"
    echo "     (or http://localhost/install.php if you didn't configure the virtual host)"
    echo ""
    print_info "Default installation will be at:"
    echo "  http://nautilus.local"
    echo ""
else
    print_error "Setup incomplete. Please fix the $ISSUES issue(s) above."
    exit 1
fi
