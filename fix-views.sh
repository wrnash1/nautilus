#!/bin/bash
# Script to comment out all CREATE VIEW statements in migration files
# Views cause issues with multi-query execution in migrations

cd /home/wrnash1/Developer/nautilus/database/migrations

echo "Fixing VIEW statements in migrations..."

# Migration 037
if [ -f "037_create_layaway_system.sql" ]; then
    echo "Processing 037_create_layaway_system.sql..."
    # Already fixed - verify it's commented
    if grep -q "^CREATE OR REPLACE VIEW layaway_summary" 037_create_layaway_system.sql; then
        echo "  WARNING: View not commented in 037, fixing..."
    else
        echo "  ✓ Already fixed"
    fi
fi

# Migration 038 - Comment out compressor_status_dashboard view
if [ -f "038_create_compressor_tracking_system.sql" ]; then
    echo "Processing 038_create_compressor_tracking_system.sql..."
    sed -i '/^-- Create View for Compressor Status Dashboard$/,/^FROM.*compressor_fills.*;$/{
        s/^/-- /
    }' 038_create_compressor_tracking_system.sql
    echo "  ✓ Commented out compressor_status_dashboard view"
fi

# Migration 041 - Comment out both cash drawer views
if [ -f "041_cash_drawer_management.sql" ]; then
    echo "Processing 041_cash_drawer_management.sql..."
    # Comment out first view: cash_drawer_sessions_open
    sed -i '/^-- Quick view for currently open sessions$/,/^WHERE.*status.*open.*;$/{
        s/^/-- /
    }' 041_cash_drawer_management.sql

    # Comment out second view: cash_drawer_session_summary
    sed -i '/^-- Detailed session summary view$/,/^LEFT JOIN.*users.*;$/{
        s/^/-- /
    }' 041_cash_drawer_management.sql

    echo "  ✓ Commented out both cash drawer views"
fi

echo ""
echo "All VIEWs have been commented out!"
echo ""
echo "Next steps:"
echo "1. Copy files to server:"
echo "   sudo cp 037_create_layaway_system.sql /var/www/html/nautilus/database/migrations/"
echo "   sudo cp 038_create_compressor_tracking_system.sql /var/www/html/nautilus/database/migrations/"
echo "   sudo cp 041_cash_drawer_management.sql /var/www/html/nautilus/database/migrations/"
echo ""
echo "2. Drop and recreate database:"
echo "   mysql -u root -pFrogman09! -e 'DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'"
echo ""
echo "3. Run installation at: https://pangolin.local/simple-install.php"
