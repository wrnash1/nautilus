#!/bin/bash
###############################################################################
# Nautilus Web Installer Setup Script
# Run this script to set up the web-based installer (like WordPress)
###############################################################################

set -e

echo "=========================================="
echo "  Nautilus Web Installer Setup"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Error: This script must be run with sudo${NC}"
    echo "Usage: sudo bash setup-web-installer.sh"
    exit 1
fi

echo -e "${YELLOW}Step 1: Cleaning old installation...${NC}"
rm -rf /var/www/html/nautilus
echo -e "${GREEN}✓ Done${NC}"
echo ""

echo -e "${YELLOW}Step 2: Copying Nautilus to /var/www/html...${NC}"
cp -R /home/wrnash1/Developer/nautilus /var/www/html/
echo -e "${GREEN}✓ Done${NC}"
echo ""

echo -e "${YELLOW}Step 3: Setting file permissions...${NC}"
chown -R apache:apache /var/www/html/nautilus
chmod -R 755 /var/www/html/nautilus
chmod -R 775 /var/www/html/nautilus/storage
echo -e "${GREEN}✓ Done${NC}"
echo ""

echo -e "${YELLOW}Step 4: Configuring Apache...${NC}"
cat > /etc/httpd/conf.d/nautilus.conf <<'EOF'
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/nautilus/public

    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/nautilus_error.log
    CustomLog /var/log/httpd/nautilus_access.log combined
</VirtualHost>
EOF
echo -e "${GREEN}✓ Apache configured${NC}"
echo ""

echo -e "${YELLOW}Step 5: Checking if Apache is installed...${NC}"
if ! systemctl is-active --quiet httpd; then
    echo -e "${YELLOW}Apache is not running. Starting Apache...${NC}"
    systemctl enable httpd
    systemctl start httpd
    echo -e "${GREEN}✓ Apache started${NC}"
else
    echo -e "${YELLOW}Restarting Apache...${NC}"
    systemctl restart httpd
    echo -e "${GREEN}✓ Apache restarted${NC}"
fi
echo ""

echo -e "${YELLOW}Step 6: Configuring firewall...${NC}"
if command -v firewall-cmd &> /dev/null; then
    firewall-cmd --permanent --add-service=http
    firewall-cmd --permanent --add-service=https
    firewall-cmd --reload
    echo -e "${GREEN}✓ Firewall configured${NC}"
else
    echo -e "${YELLOW}⚠ firewall-cmd not found, skipping${NC}"
fi
echo ""

echo -e "${YELLOW}Step 7: Checking SELinux...${NC}"
if command -v getenforce &> /dev/null && [ "$(getenforce)" != "Disabled" ]; then
    echo -e "${YELLOW}Configuring SELinux contexts...${NC}"
    semanage fcontext -a -t httpd_sys_content_t "/var/www/html/nautilus(/.*)?" 2>/dev/null || true
    semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?" 2>/dev/null || true
    restorecon -Rv /var/www/html/nautilus 2>/dev/null || true
    setsebool -P httpd_can_network_connect_db on
    echo -e "${GREEN}✓ SELinux configured${NC}"
else
    echo -e "${YELLOW}⚠ SELinux not active, skipping${NC}"
fi
echo ""

echo -e "${GREEN}=========================================="
echo -e "  Installation Complete!"
echo -e "==========================================${NC}"
echo ""
echo -e "${GREEN}✓ Nautilus is ready for web installation${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Open your web browser"
echo "2. Navigate to: http://localhost/install.php"
echo "3. Follow the web installer (just like WordPress!)"
echo ""
echo -e "${YELLOW}The web installer will guide you through:${NC}"
echo "  • System requirements check"
echo "  • Database configuration"
echo "  • Automatic table creation"
echo "  • Admin account setup"
echo ""
echo -e "${GREEN}No more command-line needed! 🎉${NC}"
echo ""
