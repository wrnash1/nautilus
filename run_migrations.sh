#!/bin/bash

# Nautilus Database Migration Runner
# Runs all migrations in order

DB_USER="root"
DB_PASS="Frogman09!"
DB_NAME="nautilus"
MIGRATION_DIR="/home/wrnash1/Developer/nautilus/database/migrations"

echo "Starting database migrations..."
echo "================================"

# Get list of all .sql files, sorted
cd "$MIGRATION_DIR"
for migration in $(ls -1 *.sql | sort); do
    echo "Running migration: $migration"
    
    # Check if migration already exists in migrations table
    ALREADY_RUN=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -sN -e "SELECT COUNT(*) FROM migrations WHERE migration = '$migration'" 2>/dev/null)
    
    if [ "$ALREADY_RUN" = "1" ]; then
        echo "  ✓ Already applied, skipping..."
    else
        # Run the migration
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration" 2>&1
        
        if [ $? -eq 0 ]; then
            # Record in migrations table
            mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "INSERT INTO migrations (migration, batch) VALUES ('$migration', 1)" 2>/dev/null
            echo "  ✓ Applied successfully"
        else
            echo "  ✗ Failed (may be expected if tables already exist)"
        fi
    fi
    echo ""
done

echo "================================"
echo "Migration process complete!"
echo ""
echo "Total migrations in database:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) as total FROM migrations"
