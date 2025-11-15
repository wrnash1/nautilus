#!/bin/bash

################################################################################
# Nautilus - Deploy to Test Server Script
#
# This script syncs your development code to the Apache web server
# for testing in the browser.
#
# Usage: ~/Developer/deploy-to-test.sh
################################################################################

echo "=========================================="
echo " Nautilus - Deploy to Test Server"
echo "=========================================="
echo ""

# Configuration
SOURCE="/home/wrnash1/Developer/nautilus/"
DEST="/var/www/html/nautilus/"

# Check if source directory exists
if [ ! -d "$SOURCE" ]; then
    echo "❌ Error: Source directory not found: $SOURCE"
    exit 1
fi

# Check if destination directory exists, create if not
if [ ! -d "$DEST" ]; then
    echo "Creating destination directory: $DEST"
    sudo mkdir -p "$DEST"
fi

# Deploy files
echo "📦 Syncing files from development to web server..."
echo "   Source: $SOURCE"
echo "   Destination: $DEST"
echo ""

sudo rsync -av --delete \
    --exclude='vendor/' \
    --exclude='.git/' \
    --exclude='storage/logs/*' \
    --exclude='storage/cache/*' \
    --exclude='storage/sessions/*' \
    --exclude='node_modules/' \
    --exclude='.env' \
    $SOURCE $DEST

# Copy .env if it doesn't exist in destination
if [ ! -f "$DEST/.env" ]; then
    if [ -f "$SOURCE/.env" ]; then
        echo "📝 Copying .env file..."
        sudo cp "$SOURCE/.env" "$DEST/.env"
    fi
fi

# Ensure APP_BASE_PATH is set in .env
if [ -f "$DEST/.env" ]; then
    if ! grep -q "APP_BASE_PATH" "$DEST/.env"; then
        echo "📝 Adding APP_BASE_PATH to .env..."
        sudo sed -i '/APP_URL/a APP_BASE_PATH=' "$DEST/.env"
    fi
fi

# Set proper ownership
echo ""
echo "🔐 Setting permissions..."
sudo chown -R www-data:www-data "$DEST"
sudo chmod -R 755 "$DEST/storage"
sudo chmod -R 755 "$DEST/public/uploads"

# Create storage directories if they don't exist
echo "📁 Ensuring storage directories exist..."
sudo mkdir -p "$DEST/storage/logs"
sudo mkdir -p "$DEST/storage/cache"
sudo mkdir -p "$DEST/storage/sessions"
sudo mkdir -p "$DEST/storage/backups"
sudo mkdir -p "$DEST/storage/exports"
sudo mkdir -p "$DEST/storage/waivers"
sudo mkdir -p "$DEST/public/uploads/forms"
sudo mkdir -p "$DEST/public/uploads/student_photos"

# Set permissions on storage directories
sudo chmod -R 755 "$DEST/storage"
sudo chmod -R 755 "$DEST/public/uploads"
sudo chown -R www-data:www-data "$DEST/storage"
sudo chown -R www-data:www-data "$DEST/public/uploads"

# Check if vendor directory needs to be installed
if [ ! -d "$DEST/vendor" ]; then
    echo ""
    echo "⚠️  Vendor directory not found. Running composer install..."
    cd "$DEST"
    sudo composer install --no-dev --optimize-autoloader
else
    echo ""
    echo "✓ Vendor directory exists"
fi

echo ""
echo "=========================================="
echo "🗄️  Database Setup"
echo "=========================================="

# Check if database exists
DB_NAME="nautilus"
DB_USER="root"
DB_EXISTS=$(sudo mysql -u $DB_USER -pFrogman09! -e "SHOW DATABASES LIKE '$DB_NAME';" | grep "$DB_NAME" > /dev/null; echo "$?")

if [ $DB_EXISTS -eq 0 ]; then
    echo "✓ Database '$DB_NAME' exists"
else
    echo "📦 Creating database '$DB_NAME'..."
    sudo mysql -u $DB_USER -pFrogman09! -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    if [ $? -eq 0 ]; then
        echo "✅ Database created successfully"
    else
        echo "❌ Failed to create database"
    fi
fi

# Run migrations
echo ""
echo "🔄 Running database migrations..."
cd "$DEST"
php scripts/migrate.php
if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully"
else
    echo "⚠️  Migrations may have already been run or encountered errors"
fi

# Run seed files if roles table is empty
echo ""
echo "🌱 Checking if initial data needs to be seeded..."
ROLE_COUNT=$(sudo mysql -u $DB_USER -pFrogman09! $DB_NAME -sN -e "SELECT COUNT(*) FROM roles;" 2>/dev/null)
if [ "$ROLE_COUNT" = "0" ] || [ -z "$ROLE_COUNT" ]; then
    echo "📦 Seeding initial data (roles, permissions)..."
    sudo mysql -u $DB_USER -pFrogman09! $DB_NAME < "$DEST/database/seeds/001_seed_initial_data.sql" 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Initial data seeded successfully"
    else
        echo "⚠️  Seeding may have failed"
    fi
else
    echo "✓ Initial data already seeded ($ROLE_COUNT roles found)"
fi

# Create admin user if it doesn't exist
echo ""
echo "👤 Setting up admin user..."
ADMIN_EXISTS=$(sudo mysql -u $DB_USER -pFrogman09! $DB_NAME -sN -e "SELECT COUNT(*) FROM users WHERE email='admin@nautilus.local';" 2>/dev/null)
if [ "$ADMIN_EXISTS" = "0" ] || [ -z "$ADMIN_EXISTS" ]; then
    echo "📝 Creating admin user..."
    sudo mysql -u $DB_USER -pFrogman09! $DB_NAME <<'EOSQL' 2>/dev/null
INSERT INTO users (email, password_hash, first_name, last_name, role_id, is_active, created_at, updated_at)
VALUES (
    'admin@nautilus.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    1,
    1,
    NOW(),
    NOW()
);
EOSQL
    if [ $? -eq 0 ]; then
        echo "✅ Admin user created successfully"
        echo ""
        echo "📝 Admin Login Credentials:"
        echo "   Email: admin@nautilus.local"
        echo "   Password: password"
    else
        echo "⚠️  Could not create admin user"
    fi
else
    echo "✓ Admin user already exists"
fi

echo ""
echo "=========================================="
echo "🔄 Restarting Apache (clears opcache)"
echo "=========================================="
sudo systemctl restart apache2
if [ $? -eq 0 ]; then
    echo "✅ Apache restarted successfully"
else
    echo "⚠️  Apache restart may have failed"
fi

echo ""
echo "=========================================="
echo "✅ Deployment Complete!"
echo "=========================================="
echo ""
echo "🌐 Test URLs:"
echo "   - Homepage: https://pangolin.local/"
echo "   - Staff Login: https://pangolin.local/store/login"
echo "   - Customer Login: https://pangolin.local/account/login"
echo ""
echo "📊 Opening Apache error log in new terminal..."

# Try to open a new terminal window with the log
if command -v gnome-terminal &> /dev/null; then
    gnome-terminal -- bash -c "echo 'Watching Apache Error Log...'; echo 'Press Ctrl+C to stop'; echo ''; sudo tail -f /var/log/apache2/error.log" &
    echo "✅ Log viewer opened in new terminal window"
elif command -v xterm &> /dev/null; then
    xterm -e "bash -c 'echo Watching Apache Error Log...; echo Press Ctrl+C to stop; echo; sudo tail -f /var/log/apache2/error.log'" &
    echo "✅ Log viewer opened in new terminal window"
elif command -v konsole &> /dev/null; then
    konsole -e bash -c "echo 'Watching Apache Error Log...'; echo 'Press Ctrl+C to stop'; echo ''; sudo tail -f /var/log/apache2/error.log" &
    echo "✅ Log viewer opened in new terminal window"
else
    echo "⚠️  Could not open new terminal window"
    echo "📊 To view logs manually, run:"
    echo "   sudo tail -f /var/log/apache2/error.log"
fi

echo ""
echo "Happy testing! 🚀"
echo ""
