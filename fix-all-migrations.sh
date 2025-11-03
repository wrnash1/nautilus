#!/bin/bash
# Fix all migration syntax errors

cd /home/wrnash1/Developer/nautilus/database/migrations

echo "Fixing CREATE INDEX syntax errors..."
# Remove ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 from CREATE INDEX statements
for file in *.sql; do
    sed -i 's/) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;/);/g' "$file"
    echo "  Fixed INDEX syntax in $file"
done

echo ""
echo "Fixing INTEGER to INT UNSIGNED for user_id fields..."
# Fix INTEGER fields that reference users table (need to match INT UNSIGNED)
for file in 028*.sql 029*.sql 030*.sql 031*.sql; do
    if [ -f "$file" ]; then
        # Fix user_id fields
        sed -i 's/counted_by INTEGER,/counted_by INT UNSIGNED,/g' "$file"
        sed -i 's/resolved_by INTEGER,/resolved_by INT UNSIGNED,/g' "$file"
        sed -i 's/created_by INTEGER,/created_by INT UNSIGNED,/g' "$file"
        sed -i 's/received_by INTEGER,/received_by INT UNSIGNED,/g' "$file"
        sed -i 's/requested_by INTEGER,/requested_by INT UNSIGNED,/g' "$file"
        sed -i 's/approved_by INTEGER,/approved_by INT UNSIGNED,/g' "$file"
        sed -i 's/shipped_by INTEGER,/shipped_by INT UNSIGNED,/g' "$file"
        sed -i 's/adjusted_by INTEGER,/adjusted_by INT UNSIGNED,/g' "$file"
        sed -i 's/manager_user_id INTEGER,/manager_user_id INT UNSIGNED,/g' "$file"
        echo "  Fixed INTEGER types in $file"
    fi
done

echo ""
echo "All migrations fixed!"
