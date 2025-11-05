#!/bin/bash
# Nautilus Database Setup Script
# This script creates the database and runs all migrations

set -e  # Exit on error

echo "=========================================="
echo "Nautilus Database Setup"
echo "=========================================="
echo ""

# Database credentials from .env
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="nautilus_dev"
DB_USER="root"
DB_PASS="Frogman09!"

echo "Creating database: $DB_NAME"

# Create database if it doesn't exist
mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✓ Database created successfully"
else
    echo "✗ Failed to create database. Trying with sudo..."
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

echo ""
echo "Running migrations..."
echo ""

# Run all migration files in order
MIGRATION_DIR="/home/wrnash1/development/nautilus/database/migrations"
SEEDER_DIR="/home/wrnash1/development/nautilus/database/seeders"

count=0
for migration in "$MIGRATION_DIR"/*.sql; do
    if [ -f "$migration" ]; then
        filename=$(basename "$migration")
        echo "→ Running: $filename"

        # Try with password first
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration" 2>/dev/null

        if [ $? -eq 0 ]; then
            ((count++))
        else
            # Try with sudo
            sudo mysql "$DB_NAME" < "$migration"
            ((count++))
        fi
    fi
done

echo ""
echo "✓ Ran $count migrations"
echo ""
echo "Seeding default data..."
echo ""

# Run seeders
for seeder in "$SEEDER_DIR"/*.sql; do
    if [ -f "$seeder" ]; then
        filename=$(basename "$seeder")
        echo "→ Running: $filename"

        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$seeder" 2>/dev/null || \
        sudo mysql "$DB_NAME" < "$seeder"
    fi
done

echo ""
echo "✓ Seeders complete"
echo ""
echo "=========================================="
echo "Database setup complete!"
echo "=========================================="
echo ""
echo "Next step: Create an admin user"
echo "Run: php /home/wrnash1/development/nautilus/scripts/create-admin.php"
echo ""
