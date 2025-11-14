#!/bin/bash
# Test each migration individually to find which one fails

DB_USER="root"
DB_PASS="Frogman09!"
DB_NAME="nautilus"

# Drop and recreate database
echo "Resetting database..."
mysql -u $DB_USER -p$DB_PASS -e "DROP DATABASE IF EXISTS $DB_NAME; CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create migrations table
mysql -u $DB_USER -p$DB_PASS $DB_NAME <<EOF
CREATE TABLE IF NOT EXISTS migrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
EOF

cd /home/wrnash1/Developer/nautilus/database/migrations

# Run each migration
for migration in $(ls *.sql | sort); do
    echo ""
    echo "========================================="
    echo "Testing: $migration"
    echo "========================================="

    if mysql -u $DB_USER -p$DB_PASS $DB_NAME < "$migration" 2>&1; then
        echo "✓ SUCCESS"
        # Record migration
        mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "INSERT IGNORE INTO migrations (migration, batch) VALUES ('$migration', 1);"
    else
        echo "✗ FAILED: $migration"
        echo ""
        echo "This migration caused the error!"
        echo ""
        echo "Last 20 lines of the migration file:"
        tail -20 "$migration"
        exit 1
    fi
done

echo ""
echo "========================================="
echo "All migrations completed successfully!"
echo "========================================="
