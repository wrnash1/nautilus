#!/usr/bin/env python3
"""
Fix all CREATE VIEW statements in migration files.
Views cause issues with multi-query execution during migrations.
"""

import re
import os

migrations_dir = "/home/wrnash1/Developer/nautilus/database/migrations"

# Files that contain CREATE VIEW statements
files_to_fix = {
    "037_create_layaway_system.sql": ["layaway_summary"],
    "038_create_compressor_tracking_system.sql": ["compressor_status_dashboard"],
    "041_cash_drawer_management.sql": ["cash_drawer_sessions_open", "cash_drawer_session_summary"]
}

def comment_out_view(content, view_name):
    """
    Find and comment out a CREATE VIEW statement and everything until the semicolon.
    """
    # Pattern to match CREATE OR REPLACE VIEW ... ; (including multi-line)
    pattern = rf'(CREATE OR REPLACE VIEW {view_name}.*?;)'

    def replace_func(match):
        view_sql = match.group(1)
        # Split into lines and comment each one
        lines = view_sql.split('\n')
        commented_lines = ['-- ' + line if line.strip() else line for line in lines]
        return '\n'.join(commented_lines)

    # Use DOTALL flag to match across newlines
    modified = re.sub(pattern, replace_func, content, flags=re.DOTALL)
    return modified

print("Fixing CREATE VIEW statements in migrations...\n")

for filename, view_names in files_to_fix.items():
    filepath = os.path.join(migrations_dir, filename)

    if not os.path.exists(filepath):
        print(f"⚠️  {filename} not found, skipping...")
        continue

    print(f"Processing {filename}...")

    # Read the file
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Comment out each view
    modified = False
    for view_name in view_names:
        if f"CREATE OR REPLACE VIEW {view_name}" in content:
            content = comment_out_view(content, view_name)
            modified = True
            print(f"  ✓ Commented out {view_name} view")
        else:
            print(f"  ℹ️  {view_name} view already commented or not found")

    # Write back if modified
    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"  ✓ File updated\n")
    else:
        print(f"  ℹ️  No changes needed\n")

print("=" * 60)
print("All VIEWs have been commented out!")
print("=" * 60)
print("\nNext steps:")
print("1. Copy fixed files to server:")
print("   sudo cp 037_create_layaway_system.sql /var/www/html/nautilus/database/migrations/")
print("   sudo cp 038_create_compressor_tracking_system.sql /var/www/html/nautilus/database/migrations/")
print("   sudo cp 041_cash_drawer_management.sql /var/www/html/nautilus/database/migrations/")
print("\n2. Drop and recreate database:")
print("   mysql -u root -pFrogman09! -e 'DROP DATABASE IF EXISTS nautilus; CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'")
print("\n3. Run installation at: https://pangolin.local/simple-install.php")
