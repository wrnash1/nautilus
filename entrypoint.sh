#!/bin/bash
# Nautilus Docker entrypoint script
set -e

echo "Nautilus: Setting up permissions..."

# Fix ownership - the mounted volume might be owned by host user
chown -R www-data:www-data /var/www/html

# Ensure directories are writable
chmod 775 /var/www/html
chmod -R 775 /var/www/html/storage 2>/dev/null || true
chmod -R 775 /var/www/html/public/uploads 2>/dev/null || true

# Fix .env and .installed if they exist
[ -f /var/www/html/.env ] && chown www-data:www-data /var/www/html/.env && chmod 664 /var/www/html/.env
[ -f /var/www/html/.installed ] && chown www-data:www-data /var/www/html/.installed

echo "Nautilus: Starting Apache..."
exec "$@"
