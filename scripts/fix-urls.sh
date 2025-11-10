#!/bin/bash
#
# Fix URL References in Views
# Adds /store/ prefix to all staff/admin URLs
#

echo "════════════════════════════════════════════════════════════"
echo "  Fixing URL References in View Files"
echo "════════════════════════════════════════════════════════════"
echo ""

VIEWS_DIR="/home/wrnash1/development/nautilus/app/Views"
BACKUP_DIR="/home/wrnash1/development/nautilus/backups/views-$(date +%Y%m%d-%H%M%S)"

# Create backup
echo "→ Creating backup..."
mkdir -p "$BACKUP_DIR"
cp -r "$VIEWS_DIR"/* "$BACKUP_DIR/"
echo "  ✓ Backup created at: $BACKUP_DIR"
echo ""

# Fix air-fills URLs
echo "→ Fixing air-fills URLs..."
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|href="/air-fills|href="/store/air-fills|g' {} \;
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|action="/air-fills|action="/store/air-fills|g' {} \;
echo "  ✓ air-fills URLs fixed"

# Fix waivers URLs
echo "→ Fixing waivers URLs..."
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|href="/waivers\([^/]|\)|href="/store/waivers\1|g' {} \;
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|action="/waivers\([^/]|\)|action="/store/waivers\1|g' {} \;
echo "  ✓ waivers URLs fixed"

# Fix dive-sites URLs
echo "→ Fixing dive-sites URLs..."
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|href="/dive-sites|href="/store/dive-sites|g' {} \;
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|action="/dive-sites|action="/store/dive-sites|g' {} \;
echo "  ✓ dive-sites URLs fixed"

# Fix courses URLs (but NOT /courses in storefront)
echo "→ Fixing courses URLs..."
find "$VIEWS_DIR/courses" -type f -name "*.php" -exec sed -i 's|href="/courses|href="/store/courses|g' {} \;
find "$VIEWS_DIR/courses" -type f -name "*.php" -exec sed -i 's|action="/courses|action="/store/courses|g' {} \;
echo "  ✓ courses URLs fixed"

# Fix serial-numbers/inventory URLs
echo "→ Fixing inventory/serial-numbers URLs..."
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|href="/inventory/serial-numbers|href="/store/serial-numbers|g' {} \;
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|action="/inventory/serial-numbers|action="/store/serial-numbers|g' {} \;
echo "  ✓ serial-numbers URLs fixed"

# Fix any double /store/store/ that might have been created
echo "→ Cleaning up double prefixes..."
find "$VIEWS_DIR" -type f -name "*.php" -exec sed -i 's|/store/store/|/store/|g' {} \;
echo "  ✓ Double prefixes cleaned"

echo ""
echo "════════════════════════════════════════════════════════════"
echo "✅ URL fixes complete!"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "Files modified in: $VIEWS_DIR"
echo "Backup stored at: $BACKUP_DIR"
echo ""
echo "Next steps:"
echo "  1. Review changes: diff -r $BACKUP_DIR $VIEWS_DIR"
echo "  2. Test application"
echo "  3. If issues, restore: cp -r $BACKUP_DIR/* $VIEWS_DIR/"
echo ""
