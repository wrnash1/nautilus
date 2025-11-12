#!/bin/bash
# Test migrations script - checks for errors

DB_USER="root"
DB_PASS="Frogman09!"
DB_NAME="nautilus_test"
DB_HOST="localhost"

echo "=== Nautilus Migration Test ==="
echo ""

# Drop and recreate test database
echo "Creating fresh test database..."
mysql -u$DB_USER -p$DB_PASS -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>/dev/null
mysql -u$DB_USER -p$DB_PASS -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "ERROR: Could not create database. Check credentials."
    exit 1
fi

echo "✓ Database created"
echo ""

# Test each migration
cd "$(dirname "$0")/../database/migrations"

SUCCESS=0
ERRORS=0
ERROR_DETAILS=""

for file in $(ls *.sql | sort); do
    echo -n "Testing $file... "

    # Run migration and capture errors
    ERROR_OUTPUT=$(mysql -u$DB_USER -p$DB_PASS $DB_NAME < "$file" 2>&1)

    if [ $? -eq 0 ]; then
        echo "✓ OK"
        ((SUCCESS++))
    else
        echo "✗ ERROR"
        ((ERRORS++))
        ERROR_DETAILS+="
======================================
FILE: $file
======================================
$ERROR_OUTPUT
"
    fi
done

echo ""
echo "=== SUMMARY ==="
echo "Success: $SUCCESS"
echo "Errors: $ERRORS"

if [ $ERRORS -gt 0 ]; then
    echo ""
    echo "=== ERROR DETAILS ==="
    echo "$ERROR_DETAILS"
fi

echo ""
echo "=== Tables Created ==="
mysql -u$DB_USER -p$DB_PASS $DB_NAME -e "SHOW TABLES;" 2>/dev/null | wc -l
echo " tables"

echo ""
echo "Test complete. Database '$DB_NAME' left for inspection."
echo "To drop: mysql -uroot -pFrogman09! -e 'DROP DATABASE $DB_NAME;'"
