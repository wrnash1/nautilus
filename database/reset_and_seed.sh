#!/bin/bash

# Nautilus Database Reset and Seed Script
# This script drops and recreates the database with fresh data

echo "======================================"
echo "Nautilus Database Reset & Seed"
echo "======================================"
echo ""

# Database connection details
DB_HOST="127.0.0.1"
DB_PORT="3307"
DB_USER="nautilus_user"
DB_PASS="Frogman09!"
DB_NAME="nautilus"

# Confirm action
read -p "This will DROP and RECREATE the database. Are you sure? (yes/no): " confirm
if [ "$confirm" != "yes" ]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "Step 1: Dropping existing database..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS -e "DROP DATABASE IF EXISTS $DB_NAME;"

echo "Step 2: Creating fresh database..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Step 3: Running schema migrations..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < database/schema.sql

echo "Step 4: Creating membership tables..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < database/migrations/create_membership_tables.sql

echo "Step 5: Seeding initial data..."
mysql -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS $DB_NAME < database/seeds/storefront_data.sql

echo ""
echo "======================================"
echo "✓ Database reset complete!"
echo "======================================"
echo ""
echo "You can now test the application with fresh data."
echo ""
