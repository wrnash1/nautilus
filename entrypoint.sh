#!/bin/bash
# Nautilus Docker entrypoint script
set -e

echo "Nautilus: Setting up permissions and directories..."

# Create storage directories if they don't exist
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/sessions
mkdir -p /var/www/html/storage/cache
mkdir -p /var/www/html/storage/backups
mkdir -p /var/www/html/public/uploads

# Fix ownership - CRITICAL: www-data must be able to write .env and .installed
# We DO NOT chown the entire /var/www/html to avoid breaking git permissions for the host user
# chown www-data:www-data /var/www/html
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/public/uploads

# Ensure directories are writable
chmod 775 /var/www/html
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/public/uploads

# Fix PHP files to be readable by web server
find /var/www/html/public -type f -name "*.php" -exec chmod 644 {} \; 2>/dev/null || true
find /var/www/html/app -type f -name "*.php" -exec chmod 644 {} \; 2>/dev/null || true

# Fix .env and .installed - ensure they exist and are writable by www-data
# This allows the installer to overwrite them since it cannot write to the root directory
touch /var/www/html/.env
touch /var/www/html/.installed
chown www-data:www-data /var/www/html/.env /var/www/html/.installed
chmod 664 /var/www/html/.env /var/www/html/.installed

echo "Nautilus: Permissions set. Storage is writable by www-data."
echo "Nautilus: Starting Apache..."
exec "$@"
