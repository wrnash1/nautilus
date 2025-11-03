#!/bin/bash

# Nautilus Database Setup Script
# This script runs all pending migrations and seeders

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Nautilus Database Setup Script${NC}"
echo -e "${BLUE}========================================${NC}\n"

# Check if .env file exists
if [ ! -f "/home/wrnash1/Developer/nautilus/.env" ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    echo "Please create .env file with database credentials first."
    exit 1
fi

# Load database credentials from .env
export $(cat /home/wrnash1/Developer/nautilus/.env | grep -v '^#' | xargs)

# Check if mysql command exists
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}Error: mysql command not found!${NC}"
    echo "Please install MySQL client."
    exit 1
fi

# Test database connection
echo -e "${YELLOW}Testing database connection...${NC}"
if mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" -e "USE ${DB_NAME};" 2>/dev/null; then
    echo -e "${GREEN}✓ Database connection successful${NC}\n"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo "Please check your database credentials in .env file."
    exit 1
fi

# Function to run a migration
run_migration() {
    local migration_file=$1
    local migration_name=$(basename "$migration_file")

    echo -e "${YELLOW}Running migration: ${migration_name}${NC}"

    if mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$migration_file" 2>/dev/null; then
        echo -e "${GREEN}✓ ${migration_name} completed${NC}"

        # Record migration in migrations table
        mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "
            INSERT INTO migrations (filename, status, executed_at)
            VALUES ('${migration_name}', 'completed', NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', executed_at = NOW();
        " 2>/dev/null || true

        return 0
    else
        echo -e "${RED}✗ ${migration_name} failed${NC}"

        # Record migration failure
        mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "
            INSERT INTO migrations (filename, status, error_message, executed_at)
            VALUES ('${migration_name}', 'failed', 'Migration failed during execution', NOW())
            ON DUPLICATE KEY UPDATE status = 'failed', error_message = 'Migration failed during execution', executed_at = NOW();
        " 2>/dev/null || true

        return 1
    fi
}

# Check for new migrations (039, 040, 041)
echo -e "${BLUE}Checking for pending migrations...${NC}\n"

MIGRATION_DIR="/home/wrnash1/Developer/nautilus/database/migrations"
PENDING_MIGRATIONS=()

# Check specific migrations
for migration_num in 039 040 041; do
    migration_file="${MIGRATION_DIR}/${migration_num}_"*.sql
    if [ -f $migration_file ]; then
        # Check if already run
        migration_name=$(basename "$migration_file")
        already_run=$(mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -se "
            SELECT COUNT(*) FROM migrations WHERE filename = '${migration_name}' AND status = 'completed';
        " 2>/dev/null || echo "0")

        if [ "$already_run" -eq "0" ]; then
            PENDING_MIGRATIONS+=("$migration_file")
        else
            echo -e "${GREEN}✓ ${migration_name} already completed${NC}"
        fi
    fi
done

if [ ${#PENDING_MIGRATIONS[@]} -eq 0 ]; then
    echo -e "\n${GREEN}No pending migrations found. Database is up to date!${NC}\n"
else
    echo -e "\n${YELLOW}Found ${#PENDING_MIGRATIONS[@]} pending migration(s)${NC}\n"

    # Run pending migrations
    for migration_file in "${PENDING_MIGRATIONS[@]}"; do
        run_migration "$migration_file"
        echo ""
    done

    echo -e "${GREEN}All migrations completed!${NC}\n"
fi

# Run seeders
echo -e "${BLUE}Running database seeders...${NC}\n"

SEEDER_DIR="/home/wrnash1/Developer/nautilus/database/seeders"

# Check if certification agencies need to be seeded
agencies_count=$(mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -se "
    SELECT COUNT(*) FROM certification_agencies;
" 2>/dev/null || echo "0")

if [ "$agencies_count" -eq "0" ]; then
    echo -e "${YELLOW}Seeding certification agencies...${NC}"
    if mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "${SEEDER_DIR}/certification_agencies.sql" 2>/dev/null; then
        echo -e "${GREEN}✓ Certification agencies seeded${NC}\n"
    else
        echo -e "${RED}✗ Failed to seed certification agencies${NC}\n"
    fi
else
    echo -e "${GREEN}✓ Certification agencies already seeded (${agencies_count} agencies)${NC}\n"
fi

# Check if cash drawers need to be seeded
drawer_count=$(mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -se "
    SELECT COUNT(*) FROM cash_drawers;
" 2>/dev/null || echo "0")

if [ "$drawer_count" -eq "0" ]; then
    echo -e "${YELLOW}Seeding cash drawers and customer tags...${NC}"
    if mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "${SEEDER_DIR}/cash_drawers.sql" 2>/dev/null; then
        echo -e "${GREEN}✓ Cash drawers and tags seeded${NC}\n"
    else
        echo -e "${RED}✗ Failed to seed cash drawers${NC}\n"
    fi
else
    echo -e "${GREEN}✓ Cash drawers already seeded (${drawer_count} drawers)${NC}\n"
fi

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Setup Complete!${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "${GREEN}Database setup summary:${NC}"
mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "
    SELECT
        (SELECT COUNT(*) FROM migrations WHERE status = 'completed') as migrations_completed,
        (SELECT COUNT(*) FROM certification_agencies) as agencies,
        (SELECT COUNT(*) FROM certifications) as certifications,
        (SELECT COUNT(*) FROM customer_tags) as customer_tags,
        (SELECT COUNT(*) FROM cash_drawers) as cash_drawers;
" 2>/dev/null

echo -e "\n${GREEN}✓ Database is ready for use!${NC}\n"

echo -e "${YELLOW}Next steps:${NC}"
echo "1. Access your application at https://pangolin.local"
echo "2. Check Cash Drawer Management at /store/cash-drawer"
echo "3. Check Customer Tags at /store/customers/tags"
echo "4. View Settings at /store/admin/settings"
echo ""
