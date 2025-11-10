#!/bin/bash
#
# Fix All Broken URLs in View Files
# This script updates all view files to use correct URL prefixes
#

set -e

BASE_DIR="/home/wrnash1/development/nautilus"
VIEW_DIR="$BASE_DIR/app/Views"
BACKUP_DIR="$BASE_DIR/backups/views-$(date +%Y%m%d-%H%M%S)"

echo "========================================="
echo "Nautilus URL Fix Script"
echo "========================================="
echo ""

# Create backup
echo "→ Creating backup..."
mkdir -p "$BACKUP_DIR"
cp -r "$VIEW_DIR" "$BACKUP_DIR/"
echo "  ✓ Backup created: $BACKUP_DIR"
echo ""

# Counter for changes
TOTAL_FILES=0
TOTAL_CHANGES=0

# Function to fix URLs in a file
fix_file() {
    local file="$1"
    local changes=0

    # Temporary file
    local temp_file="${file}.tmp"

    # Fix URLs (but preserve storefront public URLs and those already correct)
    # Only fix admin/backend URLs that should have /store/ prefix

    # Fix air-fills URLs
    if grep -q 'href="/air-fills' "$file" 2>/dev/null; then
        # Don't change if it's already /store/air-fills
        if ! grep -q 'href="/store/air-fills' "$file" 2>/dev/null; then
            sed -i 's|href="/air-fills|href="/store/air-fills|g' "$file"
            ((changes++))
        fi
    fi

    # Fix action="/air-fills
    if grep -q 'action="/air-fills' "$file" 2>/dev/null; then
        if ! grep -q 'action="/store/air-fills' "$file" 2>/dev/null; then
            sed -i 's|action="/air-fills|action="/store/air-fills|g' "$file"
            ((changes++))
        fi
    fi

    # Fix waivers URLs
    if grep -q 'href="/waivers' "$file" 2>/dev/null; then
        if ! grep -q 'href="/store/waivers' "$file" 2>/dev/null; then
            sed -i 's|href="/waivers|href="/store/waivers|g' "$file"
            ((changes++))
        fi
    fi

    # Fix dive-sites URLs
    if grep -q 'href="/dive-sites' "$file" 2>/dev/null; then
        if ! grep -q 'href="/store/dive-sites' "$file" 2>/dev/null; then
            sed -i 's|href="/dive-sites|href="/store/dive-sites|g' "$file"
            ((changes++))
        fi
    fi

    # Fix inventory URLs (except public storefront)
    if grep -q 'href="/inventory' "$file" 2>/dev/null; then
        # Only if in admin or staff views, not storefront
        if [[ ! "$file" =~ storefront ]]; then
            if ! grep -q 'href="/store/inventory' "$file" 2>/dev/null; then
                sed -i 's|href="/inventory|href="/store/inventory|g' "$file"
                ((changes++))
            fi
        fi
    fi

    # Fix serial-numbers URLs
    if grep -q 'href="/serial-numbers' "$file" 2>/dev/null; then
        if ! grep -q 'href="/store/serial-numbers' "$file" 2>/dev/null; then
            sed -i 's|href="/serial-numbers|href="/store/serial-numbers|g' "$file"
            ((changes++))
        fi
    fi

    # Fix rentals URLs (backend only)
    if grep -q 'href="/rentals' "$file" 2>/dev/null; then
        if [[ ! "$file" =~ storefront ]]; then
            if ! grep -q 'href="/store/rentals' "$file" 2>/dev/null; then
                sed -i 's|href="/rentals|href="/store/rentals|g' "$file"
                ((changes++))
            fi
        fi
    fi

    return $changes
}

echo "→ Scanning view files for broken URLs..."
echo ""

# Find all PHP view files
while IFS= read -r file; do
    if fix_file "$file"; then
        file_changes=$?
        if [ $file_changes -gt 0 ]; then
            ((TOTAL_CHANGES += file_changes))
            ((TOTAL_FILES++))
            echo "  ✓ Fixed: $(basename $(dirname "$file"))/$(basename "$file") ($file_changes changes)"
        fi
    fi
done < <(find "$VIEW_DIR" -name "*.php" -type f)

echo ""
echo "========================================="
echo "Summary"
echo "========================================="
echo "Files modified: $TOTAL_FILES"
echo "Total changes: $TOTAL_CHANGES"
echo ""

if [ $TOTAL_CHANGES -gt 0 ]; then
    echo "✓ URLs have been fixed!"
    echo ""
    echo "Backup location: $BACKUP_DIR"
    echo ""
    echo "To revert changes:"
    echo "  cp -r $BACKUP_DIR/Views/* $VIEW_DIR/"
else
    echo "ℹ No broken URLs found - all URLs are already correct!"
fi

echo ""
