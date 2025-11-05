#!/bin/bash
# Run all Nautilus migrations
# This script should be run with: sudo ./run-migrations.sh

set -e

DB_NAME="nautilus_dev"
MIGRATION_DIR="/home/wrnash1/development/nautilus/database/migrations"
SEEDER_DIR="/home/wrnash1/development/nautilus/database/seeders"

echo "=========================================="
echo "Running Nautilus Migrations"
echo "Database: $DB_NAME"
echo "=========================================="
echo ""

# Create database if needed
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo "Database already exists or couldn't create"
}

# Count migrations
total=$(ls -1 "$MIGRATION_DIR"/*.sql 2>/dev/null | wc -l)
current=0

echo "Found $total migration files"
echo ""

# Run each migration
for migration in "$MIGRATION_DIR"/*.sql; do
    if [ -f "$migration" ]; then
        current=$((current + 1))
        filename=$(basename "$migration")
        echo "[$current/$total] Running: $filename"

        mysql "$DB_NAME" < "$migration" 2>&1 || {
            echo "  ⚠ Migration may have already been applied or encountered an error"
        }
    fi
done

echo ""
echo "✓ Migrations complete"
echo ""
echo "Running seeders..."

# Run seeders
for seeder in "$SEEDER_DIR"/*.sql; do
    if [ -f "$seeder" ]; then
        filename=$(basename "$seeder")
        echo "→ $filename"
        mysql "$DB_NAME" < "$seeder" 2>&1 || {
            echo "  ⚠ Seeder may have already been applied"
        }
    fi
done

echo ""
echo "✓ Seeders complete"
echo ""
echo "Database setup complete!"
echo ""
