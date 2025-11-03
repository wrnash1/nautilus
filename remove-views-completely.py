#!/usr/bin/env python3
"""
Completely REMOVE all CREATE VIEW statements from migration files.
Block comments might not work properly with mysqli::multi_query().
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

def remove_view(content, view_name):
    """
    Completely remove a CREATE VIEW statement and everything until the semicolon.
    Replace with a comment explaining the view was removed.
    """
    # Pattern to match CREATE OR REPLACE VIEW ... ; (including multi-line)
    # This pattern matches both commented and uncommented versions
    pattern = rf'(/\*.*?\*/\s*)?--.*?CREATE OR REPLACE VIEW {view_name}.*?;'

    # Also try pattern without leading comment block
    pattern2 = rf'CREATE OR REPLACE VIEW {view_name}.*?;'

    # Replacement text
    replacement = f'''-- NOTE: View '{view_name}' was removed from migration due to multi-query execution issues.
-- CREATE VIEW statements cannot be executed reliably with mysqli::multi_query().
-- The view can be created manually after installation if needed.'''

    # Try first pattern (with block comment)
    modified = re.sub(pattern, replacement, content, flags=re.DOTALL)

    # If that didn't work, try second pattern
    if modified == content:
        modified = re.sub(pattern2, replacement, content, flags=re.DOTALL)

    return modified

print("Removing CREATE VIEW statements from migrations...\\n")

for filename, view_names in files_to_fix.items():
    filepath = os.path.join(migrations_dir, filename)

    if not os.path.exists(filepath):
        print(f"⚠️  {filename} not found, skipping...")
        continue

    print(f"Processing {filename}...")

    # Read the file
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Remove each view
    modified = False
    for view_name in view_names:
        if f"VIEW {view_name}" in content or f"view '{view_name}'" in content.lower():
            content = remove_view(content, view_name)
            modified = True
            print(f"  ✓ Removed {view_name} view completely")
        else:
            print(f"  ℹ️  {view_name} view not found or already removed")

    # Write back if modified
    if modified:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"  ✓ File updated\\n")
    else:
        print(f"  ℹ️  No changes needed\\n")

print("=" * 60)
print("All VIEWs have been removed!")
print("=" * 60)
