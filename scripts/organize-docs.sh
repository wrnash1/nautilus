#!/bin/bash

###############################################################################
# Nautilus - Documentation Organization Script
# Moves markdown files from root to docs/ directory
###############################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }

echo -e "${BLUE}"
cat << "EOF"
╔═══════════════════════════════════════════╗
║   Nautilus Documentation Organizer        ║
╚═══════════════════════════════════════════╝
EOF
echo -e "${NC}"

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

cd "$PROJECT_ROOT"

# Files to keep in root
KEEP_IN_ROOT=(
    "README.md"
    "LICENSE"
    "INSTALL.md"
)

# Count markdown files in root
MD_FILES=(*.md)
MOVED=0
KEPT=0

print_info "Scanning for markdown files in root directory..."
echo ""

for file in "${MD_FILES[@]}"; do
    if [ -f "$file" ]; then
        # Check if file should stay in root
        KEEP=false
        for keep_file in "${KEEP_IN_ROOT[@]}"; do
            if [ "$file" = "$keep_file" ]; then
                KEEP=true
                break
            fi
        done

        if [ "$KEEP" = true ]; then
            print_info "Keeping in root: $file"
            ((KEPT++))
        else
            # Move to docs/
            if [ ! -d "docs" ]; then
                mkdir -p docs
                print_success "Created docs/ directory"
            fi

            if [ -f "docs/$file" ]; then
                print_warning "File already exists in docs/: $file (skipping)"
            else
                mv "$file" "docs/"
                print_success "Moved to docs/: $file"
                ((MOVED++))
            fi
        fi
    fi
done

echo ""
echo -e "${BLUE}═══════════════════════════════════════════${NC}"
print_success "Documentation organization complete!"
echo ""
print_info "Summary:"
echo "  - Files moved to docs/: $MOVED"
echo "  - Files kept in root: $KEPT"
echo ""
print_info "Root directory now contains only:"
for keep_file in "${KEEP_IN_ROOT[@]}"; do
    if [ -f "$keep_file" ]; then
        echo "  - $keep_file"
    fi
done
echo ""
