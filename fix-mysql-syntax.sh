#!/bin/bash

# Fix SQLite syntax to MySQL syntax in all migration files

echo "Fixing SQLite syntax to MySQL/MariaDB syntax..."

cd database/migrations

# Fix each file
for file in *.sql; do
    if grep -q "AUTOINCREMENT\|INSERT OR IGNORE" "$file"; then
        echo "Fixing: $file"

        # Replace AUTOINCREMENT with AUTO_INCREMENT
        sed -i 's/INTEGER PRIMARY KEY AUTOINCREMENT/INT UNSIGNED AUTO_INCREMENT PRIMARY KEY/g' "$file"
        sed -i 's/INTEGER AUTOINCREMENT PRIMARY KEY/INT UNSIGNED AUTO_INCREMENT PRIMARY KEY/g' "$file"

        # Replace INTEGER with INT UNSIGNED (for IDs)
        sed -i 's/ INTEGER NOT NULL/ INT UNSIGNED NOT NULL/g' "$file"
        sed -i 's/ INTEGER DEFAULT/ INT DEFAULT/g' "$file"

        # Replace BOOLEAN with TINYINT(1)
        sed -i 's/ BOOLEAN DEFAULT/ TINYINT(1) DEFAULT/g' "$file"

        # Replace INSERT OR IGNORE with INSERT IGNORE
        sed -i 's/INSERT OR IGNORE/INSERT IGNORE/g' "$file"

        # Add ENGINE and CHARSET to CREATE TABLE statements
        sed -i 's/);$/) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;/g' "$file"

        echo "  ✓ Fixed $file"
    fi
done

echo ""
echo "Done! All migrations fixed for MySQL/MariaDB compatibility."
