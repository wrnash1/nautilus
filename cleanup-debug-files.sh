#!/bin/bash
# Remove debug and test files from public directory

echo "Cleaning up debug files..."

PUBLIC_DIR="/var/www/html/nautilus/public"

# List of debug files to remove
DEBUG_FILES=(
    "phpinfo.php"
    "debug-login.php"
    "debug-install.php"
    "debug-migration.php"
    "fix-env.php"
    "fix-cash-drawer-table.php"
    "check-databases.php"
    "create-admin.php"
    "fix-status-column.php"
    "show-migration.php"
    "simple-install.php"
    "test-migrations-one-by-one.php"
    "test.php"
)

echo "Files to remove:"
for file in "${DEBUG_FILES[@]}"; do
    if [ -f "$PUBLIC_DIR/$file" ]; then
        echo "  - $file"
        rm "$PUBLIC_DIR/$file"
    fi
done

echo ""
echo "✓ Debug files removed"
echo ""
echo "Remaining files in public/:"
ls -la "$PUBLIC_DIR" | grep -E "\.php$"
