#!/bin/bash
# Fix PHP 8.4 nullable parameter deprecation warnings
# Changes: string $param = null -> ?string $param = null

cd /home/wrnash1/development/nautilus

echo "Fixing PHP 8.4 nullable parameter issues..."

# Find all PHP files and fix nullable parameters
find app -name "*.php" -type f | while read file; do
    # Create backup
    cp "$file" "$file.bak"

    # Fix string parameters
    sed -i 's/\(function[^(]*([^)]*\)string \$\([a-zA-Z_][a-zA-Z0-9_]*\) = null/\1?string $\2 = null/g' "$file"

    # Fix int parameters
    sed -i 's/\(function[^(]*([^)]*\)int \$\([a-zA-Z_][a-zA-Z0-9_]*\) = null/\1?int $\2 = null/g' "$file"

    # Fix array parameters
    sed -i 's/\(function[^(]*([^)]*\)array \$\([a-zA-Z_][a-zA-Z0-9_]*\) = null/\1?array $\2 = null/g' "$file"

    # Fix bool parameters
    sed -i 's/\(function[^(]*([^)]*\)bool \$\([a-zA-Z_][a-zA-Z0-9_]*\) = null/\1?bool $\2 = null/g' "$file"

    # Fix float parameters
    sed -i 's/\(function[^(]*([^)]*\)float \$\([a-zA-Z_][a-zA-Z0-9_]*\) = null/\1?float $\2 = null/g' "$file"

    # Check if file was changed
    if diff -q "$file" "$file.bak" > /dev/null; then
        # No changes, remove backup
        rm "$file.bak"
    else
        echo "Fixed: $file"
    fi
done

# Remove any remaining backup files
find app -name "*.php.bak" -delete

echo "Done! Fixed all nullable parameter issues."
