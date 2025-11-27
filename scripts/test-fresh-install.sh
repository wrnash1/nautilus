#!/bin/bash
################################################################################
# Nautilus Complete Fresh Installation Test
# This script performs a complete cleanup and fresh installation
################################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║    NAUTILUS FRESH INSTALLATION TEST                     ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}ERROR: This script must be run as root${NC}"
    echo "Usage: sudo bash test-fresh-install.sh"
    exit 1
fi

echo -e "${YELLOW}WARNING: This will COMPLETELY REMOVE the existing Nautilus installation!${NC}"
echo "Press Ctrl+C to cancel, or Enter to continue..."
read

################################################################################
# STEP 1: Complete Cleanup
################################################################################ 
echo -e "${BLUE}[1/6] Stopping services...${NC}"
systemctl stop httpd 2>/dev/null || true

echo -e "${BLUE}[2/6] Removing existing installation...${NC}"
rm -rf /var/www/html/nautilus
rm -f /etc/httpd/conf.d/nautilus*.conf
echo -e "${GREEN}✓ Files removed${NC}"

echo -e "${BLUE}[3/6] Dropping database...${NC}"
mysql -u root -e "DROP DATABASE IF EXISTS nautilus;" 2>/dev/null || echo -e "${YELLOW}Note: Database may not exist${NC}"
echo -e "${GREEN}✓ Database dropped${NC}"

echo -e "${BLUE}[4/6] Enabling SELinux enforcing mode...${NC}"
if command -v getenforce &> /dev/null; then
    CURRENT=$(getenforce)
    echo "Current SELinux status: $CURRENT"
    
    if [ "$CURRENT" != "Enforcing" ]; then
        setenforce 1 2>/dev/null && echo -e "${GREEN}✓ SELinux set to enforcing${NC}" || echo -e "${YELLOW}! Could not set to enforcing${NC}"
    else
        echo -e "${GREEN}✓ SELinux already enforcing${NC}"
    fi
else
    echo -e "${YELLOW}! SELinux not available${NC}"
fi

echo -e "${BLUE}[5/6] Removing SSL certificates...${NC}"
rm -f /etc/pki/tls/certs/nautilus* /etc/pki/tls/private/nautilus* 2>/dev/null
echo -e "${GREEN}✓ SSL certificates removed${NC}"

################################################################################
# STEP 2: Fresh Installation
################################################################################
echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║              STARTING FRESH INSTALLATION                ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${ NC}"
echo ""

echo -e "${BLUE}[6/6] Running universal installer...${NC}"
cd /home/wrnash1/Developer/nautilus
bash scripts/universal-install.sh

################################################################################
# STEP 3: Verification
################################################################################
echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║              POST-INSTALLATION VERIFICATION              ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${GREEN}Installation Complete!${NC}"
echo ""
echo "=== Next Steps ==="
echo "1. Open browser: https://nautilus.local/install/"
echo "2. Complete web installer"
echo "3. Verify all database tables installed"
echo ""
echo "=== Verification Commands ==="
echo "Check SELinux:    sudo getenforce"
echo "Check HTTPS:      curl -k https://nautilus.local/"
echo "Check tables:     mysql -u root -p nautilus -e 'SHOW TABLES;' | wc -l"
echo "Check Apache:     systemctl status httpd"
echo ""
