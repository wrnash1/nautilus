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

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Check if .env file exists
if [ ! -f "$PROJECT_ROOT/.env" ]; then
    echo -e "${RED}Error: .env file not found at $PROJECT_ROOT/.env${NC}"
    echo "Please create .env file with database credentials first."
    exit 1
fi

# Load database credentials from .env
set -a
source "$PROJECT_ROOT/.env"
set +a

# Map .env variables to script variables (handle both DB_USER and DB_USERNAME)
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE}"
DB_USER="${DB_USERNAME:-$DB_USER}"
DB_PASS="${DB_PASSWORD}"

# Check if mysql command exists
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}Error: mysql command not found!${NC}"
    echo "Please install MySQL client."
    exit 1
fi

# Test database connection
echo -e "${YELLOW}Testing database connection...${NC}"
if mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" -e "USE ${DB_NAME};" 2>/dev/null; then
    echo -e "${GREEN}✓ Database connection successful${NC}\n"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo "Please check your database credentials in .env file."
    echo "DB_HOST: ${DB_HOST}"
    echo "DB_PORT: ${DB_PORT}"
    echo "DB_DATABASE: ${DB_NAME}"
    echo "DB_USERNAME: ${DB_USER}"
    exit 1
fi

# Function to run a migration
run_migration() {
    local migration_file=$1
    local migration_name=$(basename "$migration_file")

    echo -e "${YELLOW}Running migration: ${migration_name}${NC}"

    if mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "$migration_file" 2>&1; then
        echo -e "${GREEN}✓ ${migration_name} completed${NC}"

        # Record migration in migrations table
        mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
            INSERT INTO migrations (filename, status, executed_at)
            VALUES ('${migration_name}', 'completed', NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', executed_at = NOW();
        " 2>/dev/null || true

        return 0
    else
        echo -e "${RED}✗ ${migration_name} failed${NC}"
        echo -e "${YELLOW}Check the error output above for details${NC}"

        # Record migration failure
        mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
            INSERT INTO migrations (filename, status, error_message, executed_at)
            VALUES ('${migration_name}', 'failed', 'Migration failed during execution', NOW())
            ON DUPLICATE KEY UPDATE status = 'failed', error_message = 'Migration failed during execution', executed_at = NOW();
        " 2>/dev/null || true

        return 1
    fi
}

# Check for all pending migrations
echo -e "${BLUE}Checking for pending migrations...${NC}\n"

MIGRATION_DIR="$PROJECT_ROOT/database/migrations"
PENDING_MIGRATIONS=()

if [ ! -d "$MIGRATION_DIR" ]; then
    echo -e "${RED}Error: Migration directory not found at $MIGRATION_DIR${NC}"
    exit 1
fi

# Find all .sql migration files
for migration_file in "$MIGRATION_DIR"/*.sql; do
    if [ -f "$migration_file" ]; then
        migration_name=$(basename "$migration_file")

        # Check if already run
        already_run=$(mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "
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

SEEDER_DIR="$PROJECT_ROOT/database/seeders"

if [ -d "$SEEDER_DIR" ]; then
    # Check if certification agencies need to be seeded
    agencies_count=$(mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "
        SELECT COUNT(*) FROM certification_agencies;
    " 2>/dev/null || echo "0")

    if [ "$agencies_count" -eq "0" ] && [ -f "${SEEDER_DIR}/certification_agencies.sql" ]; then
        echo -e "${YELLOW}Seeding certification agencies...${NC}"
        if mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SEEDER_DIR}/certification_agencies.sql" 2>&1; then
            echo -e "${GREEN}✓ Certification agencies seeded${NC}\n"
        else
            echo -e "${RED}✗ Failed to seed certification agencies${NC}\n"
        fi
    else
        echo -e "${GREEN}✓ Certification agencies already seeded (${agencies_count} agencies)${NC}\n"
    fi

    # Check if cash drawers need to be seeded
    drawer_count=$(mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "
        SELECT COUNT(*) FROM cash_drawers;
    " 2>/dev/null || echo "0")

    if [ "$drawer_count" -eq "0" ] && [ -f "${SEEDER_DIR}/cash_drawers.sql" ]; then
        echo -e "${YELLOW}Seeding cash drawers and customer tags...${NC}"
        if mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SEEDER_DIR}/cash_drawers.sql" 2>&1; then
            echo -e "${GREEN}✓ Cash drawers and tags seeded${NC}\n"
        else
            echo -e "${RED}✗ Failed to seed cash drawers${NC}\n"
        fi
    else
        echo -e "${GREEN}✓ Cash drawers already seeded (${drawer_count} drawers)${NC}\n"
    fi

    # Ask about demo data
    demo_installed=$(mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -se "
        SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '${DB_NAME}' AND table_name = 'demo_data_installed';
    " 2>/dev/null || echo "0")

    if [ "$demo_installed" -eq "0" ] && [ -f "${SEEDER_DIR}/demo_data.sql" ]; then
        echo ""
        read -p "Would you like to install demo data (sample products, customers, sales)? (y/n) " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            echo -e "${YELLOW}Installing demo data...${NC}"
            if mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${SEEDER_DIR}/demo_data.sql" 2>&1; then
                echo -e "${GREEN}✓ Demo data installed successfully${NC}"
                echo -e "${BLUE}  - 15 sample products${NC}"
                echo -e "${BLUE}  - 5 sample customers${NC}"
                echo -e "${BLUE}  - 5 sample sales orders${NC}"
                echo -e "${BLUE}  - Demo admin login: admin@demo.com / demo123${NC}\n"
            else
                echo -e "${RED}✗ Failed to install demo data${NC}\n"
            fi
        else
            echo -e "${YELLOW}Skipped demo data installation${NC}\n"
        fi
    elif [ "$demo_installed" -eq "1" ]; then
        echo -e "${GREEN}✓ Demo data already installed${NC}\n"
    fi
else
    echo -e "${YELLOW}No seeder directory found at $SEEDER_DIR${NC}\n"
fi

# Summary
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Setup Complete!${NC}"
echo -e "${BLUE}========================================${NC}\n"

echo -e "${GREEN}Database setup summary:${NC}"
mysql -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
    SELECT
        (SELECT COUNT(*) FROM migrations WHERE status = 'completed') as migrations_completed,
        (SELECT COUNT(*) FROM certification_agencies) as agencies,
        (SELECT COUNT(*) FROM certifications) as certifications,
        (SELECT COUNT(*) FROM customer_tags) as customer_tags,
        (SELECT COUNT(*) FROM cash_drawers) as cash_drawers;
" 2>/dev/null

echo -e "\n${GREEN}✓ Database is ready for use!${NC}\n"

echo -e "${YELLOW}Next steps:${NC}"
echo "1. Access your application at http://nautilus.local"
echo "2. Complete the web-based installation at http://nautilus.local/install.php"
echo ""
