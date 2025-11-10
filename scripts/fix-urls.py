#!/usr/bin/env python3
"""
Fix URL References in View Files
Adds /store/ prefix to all staff/admin URLs that are missing it
"""

import os
import re
from pathlib import Path

VIEWS_DIR = Path("/home/wrnash1/development/nautilus/app/Views")

# URL patterns to fix - these should have /store/ prefix
PATTERNS_TO_FIX = [
    # href patterns
    (r'href="/air-fills', 'href="/store/air-fills'),
    (r'href="/waivers(?![/]|")', 'href="/store/waivers'),  # Don't match /waivers/sign (public)
    (r'href="/dive-sites', 'href="/store/dive-sites'),
    (r'href="/serial-numbers', 'href="/store/serial-numbers'),
    (r'href="/inventory/serial-numbers', 'href="/store/serial-numbers'),

    # action patterns
    (r'action="/air-fills', 'action="/store/air-fills'),
    (r'action="/waivers(?![/])', 'action="/store/waivers'),
    (r'action="/dive-sites', 'action="/store/dive-sites'),
    (r'action="/serial-numbers', 'action="/store/serial-numbers'),
    (r'action="/inventory/serial-numbers', 'action="/store/serial-numbers'),
]

# Patterns for courses - only in staff views, not storefront
COURSE_PATTERNS = [
    (r'href="/courses', 'href="/store/courses'),
    (r'action="/courses', 'action="/store/courses'),
]

def fix_file(filepath):
    """Fix URLs in a single file"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        original_content = content

        # Apply general patterns
        for pattern, replacement in PATTERNS_TO_FIX:
            content = re.sub(pattern, replacement, content)

        # Apply course patterns only in non-storefront files
        if '/storefront/' not in str(filepath):
            for pattern, replacement in COURSE_PATTERNS:
                content = re.sub(pattern, replacement, content)

        # Fix any double /store/store/ that might have been created
        content = re.sub(r'/store/store/', '/store/', content)

        # Only write if changes were made
        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    print("=" * 64)
    print("  Fixing URL References in View Files")
    print("=" * 64)
    print()

    # Find all PHP files
    php_files = list(VIEWS_DIR.rglob('*.php'))

    print(f"→ Found {len(php_files)} PHP files")
    print()

    fixed_count = 0
    for filepath in php_files:
        if fix_file(filepath):
            fixed_count += 1
            rel_path = filepath.relative_to(VIEWS_DIR)
            print(f"  ✓ Fixed: {rel_path}")

    print()
    print("=" * 64)
    print(f"✅ Complete! Fixed {fixed_count} files")
    print("=" * 64)
    print()

if __name__ == '__main__':
    main()
