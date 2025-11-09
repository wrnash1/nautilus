#!/bin/bash

################################################################################
# Nautilus Dive Shop Management System - Installation Script
# Version: 1.0
# Description: Automated installation and setup script
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════════════════════════════╗"
    echo "║                                                                ║"
    echo "║          NAUTILUS DIVE SHOP MANAGEMENT SYSTEM                 ║"
    echo "║                  Installation Script                          ║"
    echo "║                                                                ║"
    echo "╚════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_step() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

check_command() {
    if command -v $1 &> /dev/null; then
        print_success "$1 is installed"
        return 0
    else
        print_error "$1 is not installed"
        return 1
    fi
}

################################################################################
# Pre-Installation Checks
################################################################################

print_header

print_step "Step 1: Pre-Installation Checks"

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. It's recommended to run as a regular user."
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check required commands
REQUIRED_COMMANDS=("php" "mysql" "composer")
ALL_PRESENT=true

for cmd in "${REQUIRED_COMMANDS[@]}"; do
    if ! check_command $cmd; then
        ALL_PRESENT=false
    fi
done

if [ "$ALL_PRESENT" = false ]; then
    print_error "Missing required dependencies. Please install them first."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r 'echo PHP_VERSION;')
PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)

if [ $PHP_MAJOR -lt 8 ] || ([ $PHP_MAJOR -eq 8 ] && [ $PHP_MINOR -lt 2 ]); then
    print_error "PHP 8.2 or higher is required. Current version: $PHP_VERSION"
    exit 1
else
    print_success "PHP version $PHP_VERSION is compatible"
fi

################################################################################
# Environment Configuration
################################################################################

print_step "Step 2: Environment Configuration"

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        print_success "Created .env file from .env.example"
    else
        print_error ".env.example not found"
        exit 1
    fi
fi

# Prompt for database credentials
print_info "Configure database connection:"
read -p "Database host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Database port [3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

read -p "Database name [nautilus]: " DB_NAME
DB_NAME=${DB_NAME:-nautilus}

read -p "Database username: " DB_USER

read -sp "Database password: " DB_PASSWORD
echo

# Update .env file
sed -i "s/DB_HOST=.*/DB_HOST=$DB_HOST/" .env
sed -i "s/DB_PORT=.*/DB_PORT=$DB_PORT/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env

print_success "Environment configuration updated"

################################################################################
# Composer Dependencies
################################################################################

print_step "Step 3: Installing Composer Dependencies"

if [ -f composer.json ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "Composer dependencies installed"
else
    print_error "composer.json not found"
    exit 1
fi

################################################################################
# Database Setup
################################################################################

print_step "Step 4: Database Setup"

# Test database connection
print_info "Testing database connection..."
if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1;" &>/dev/null; then
    print_success "Database connection successful"
else
    print_error "Database connection failed. Please check your credentials."
    exit 1
fi

# Create database if it doesn't exist
print_info "Creating database if not exists..."
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
print_success "Database '$DB_NAME' ready"

# Run migrations
print_info "Running database migrations..."
MIGRATION_COUNT=0

for migration in database/migrations/*.sql; do
    if [ -f "$migration" ]; then
        print_info "Running $(basename $migration)..."
        if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$migration" 2>/dev/null; then
            ((MIGRATION_COUNT++))
        else
            print_warning "Migration $(basename $migration) may have already been applied or failed"
        fi
    fi
done

print_success "Processed $MIGRATION_COUNT migration files"

################################################################################
# Directory Permissions
################################################################################

print_step "Step 5: Setting Directory Permissions"

# Create necessary directories
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/sessions
mkdir -p storage/uploads
mkdir -p public/uploads

# Set permissions
chmod -R 755 storage
chmod -R 755 public/uploads

print_success "Directory permissions set"

################################################################################
# Cron Jobs Setup
################################################################################

print_step "Step 6: Cron Jobs Configuration"

CRON_FILE="/tmp/nautilus_cron_$(date +%s).txt"

# Get current user's crontab
crontab -l > "$CRON_FILE" 2>/dev/null || true

print_info "Setting up automated jobs..."

# Define cron jobs
CRON_JOBS=(
    # Automated Notifications - Every hour
    "0 * * * * cd $SCRIPT_DIR && php app/Jobs/SendAutomatedNotificationsJob.php >> storage/logs/notifications.log 2>&1"

    # Calculate Daily Analytics - Every day at 1 AM
    "0 1 * * * cd $SCRIPT_DIR && php app/Jobs/CalculateDailyAnalyticsJob.php >> storage/logs/analytics.log 2>&1"

    # Cache Warmup - Every 6 hours
    "0 */6 * * * cd $SCRIPT_DIR && php app/Jobs/CacheWarmupJob.php >> storage/logs/cache.log 2>&1"

    # Database Backup - Every day at 2 AM
    "0 2 * * * cd $SCRIPT_DIR && php app/Jobs/DatabaseBackupJob.php >> storage/logs/backup.log 2>&1"

    # Cleanup Old Logs - Every Sunday at 3 AM
    "0 3 * * 0 cd $SCRIPT_DIR && php app/Jobs/CleanupOldDataJob.php >> storage/logs/cleanup.log 2>&1"

    # Send Scheduled Reports - Every Monday at 9 AM
    "0 9 * * 1 cd $SCRIPT_DIR && php app/Jobs/SendScheduledReportsJob.php >> storage/logs/reports.log 2>&1"
)

# Check if jobs already exist and add if not
JOBS_ADDED=0
for job in "${CRON_JOBS[@]}"; do
    if ! grep -Fq "$job" "$CRON_FILE"; then
        echo "$job" >> "$CRON_FILE"
        ((JOBS_ADDED++))
        print_success "Added: $(echo $job | cut -d' ' -f6-)"
    else
        print_info "Already exists: $(echo $job | cut -d' ' -f6-)"
    fi
done

if [ $JOBS_ADDED -gt 0 ]; then
    # Install new crontab
    crontab "$CRON_FILE"
    print_success "Installed $JOBS_ADDED new cron jobs"
else
    print_info "All cron jobs were already configured"
fi

# Cleanup
rm "$CRON_FILE"

# Display current cron jobs
print_info "Current Nautilus cron jobs:"
echo ""
crontab -l | grep "$SCRIPT_DIR" || print_warning "No cron jobs found"
echo ""

################################################################################
# Initial Data Setup
################################################################################

print_step "Step 7: Initial Data Setup"

# Run seeders if they exist
if [ -d "database/seeders" ]; then
    print_info "Running database seeders..."
    php -r "require 'database/seeders/run_all_seeders.php';" 2>/dev/null || print_warning "No seeders to run"
fi

# Create default admin user
read -p "Create default admin user? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Admin username [admin]: " ADMIN_USER
    ADMIN_USER=${ADMIN_USER:-admin}

    read -p "Admin email: " ADMIN_EMAIL

    read -sp "Admin password: " ADMIN_PASSWORD
    echo

    # Hash password
    ADMIN_PASSWORD_HASH=$(php -r "echo password_hash('$ADMIN_PASSWORD', PASSWORD_BCRYPT);")

    # Insert admin user
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" << EOF
INSERT INTO users (username, email, password, first_name, last_name, role_id, is_active, created_at)
VALUES ('$ADMIN_USER', '$ADMIN_EMAIL', '$ADMIN_PASSWORD_HASH', 'System', 'Administrator', 1, 1, NOW())
ON DUPLICATE KEY UPDATE username=username;
EOF

    print_success "Admin user created: $ADMIN_USER"
fi

################################################################################
# Testing
################################################################################

print_step "Step 8: Running Tests"

read -p "Run test suite? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    composer test || print_warning "Some tests may have failed"
fi

################################################################################
# Post-Installation
################################################################################

print_step "Step 9: Post-Installation Tasks"

# Create a post-install checklist file
cat > POST_INSTALL_CHECKLIST.txt << 'EOF'
NAUTILUS POST-INSTALLATION CHECKLIST
=====================================

[ ] 1. Configure Email Settings
    - Update SMTP settings in .env file:
      MAIL_HOST=smtp.example.com
      MAIL_PORT=587
      MAIL_USERNAME=your-email@example.com
      MAIL_PASSWORD=your-password
      MAIL_ENCRYPTION=tls
      MAIL_FROM_ADDRESS=noreply@yourdiveshop.com
      MAIL_FROM_NAME="Nautilus Dive Shop"

[ ] 2. Test Email Configuration
    - Run: php test_email.php

[ ] 3. Configure Notification Settings
    - Update notification_settings table in database
    - Set manager email addresses
    - Enable/disable notification types

[ ] 4. Set Up Backup Storage
    - Configure backup destination in .env
    - Test backup job: php app/Jobs/DatabaseBackupJob.php

[ ] 5. Review Cron Jobs
    - Verify all jobs are running: crontab -l
    - Check log files in storage/logs/

[ ] 6. Configure Web Server
    - Set document root to /public
    - Enable mod_rewrite (Apache) or configure nginx
    - Set up SSL certificate

[ ] 7. Security Hardening
    - Change default admin password
    - Review file permissions
    - Configure firewall rules
    - Enable fail2ban if available

[ ] 8. Performance Tuning
    - Enable OPcache in PHP
    - Configure MySQL query cache
    - Set up Redis for session storage (optional)

[ ] 9. Monitoring Setup
    - Set up log rotation
    - Configure error notifications
    - Install monitoring tools (optional)

[ ] 10. Documentation Review
    - Read docs/ANALYTICS_DASHBOARD.md
    - Read docs/AUTOMATED_NOTIFICATIONS.md
    - Review DEVELOPMENT_SUMMARY.md

EOF

print_success "Created POST_INSTALL_CHECKLIST.txt"

################################################################################
# Installation Complete
################################################################################

print_step "Installation Complete!"

echo -e "${GREEN}"
cat << 'EOF'
╔════════════════════════════════════════════════════════════════╗
║                                                                ║
║               INSTALLATION SUCCESSFUL!                         ║
║                                                                ║
╚════════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

print_info "Next Steps:"
echo ""
echo "1. Review POST_INSTALL_CHECKLIST.txt for remaining configuration"
echo "2. Configure your web server to point to the public/ directory"
echo "3. Access the application at your configured domain"
echo "4. Login with your admin credentials"
echo "5. Configure notification settings in the admin panel"
echo ""

print_info "Cron Jobs Configured:"
echo "  - Automated Notifications: Every hour"
echo "  - Daily Analytics: 1:00 AM daily"
echo "  - Cache Warmup: Every 6 hours"
echo "  - Database Backup: 2:00 AM daily"
echo "  - Data Cleanup: 3:00 AM every Sunday"
echo "  - Scheduled Reports: 9:00 AM every Monday"
echo ""

print_info "Useful Commands:"
echo "  - Run tests: composer test"
echo "  - Check logs: tail -f storage/logs/application.log"
echo "  - View cron jobs: crontab -l | grep nautilus"
echo ""

print_success "Thank you for installing Nautilus!"
print_info "For support, check the documentation in the docs/ directory"

exit 0
