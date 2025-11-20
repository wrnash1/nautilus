#!/bin/bash

# Nautilus Production Readiness Cleanup Script
# This script performs comprehensive cleanup and validation

echo "======================================"
echo "Nautilus Production Readiness Cleanup"
echo "======================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Create backup directory
echo "Creating backup directory..."
mkdir -p backup/cleanup-$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="backup/cleanup-$(date +%Y%m%d-%H%M%S)"

# Function to check for duplicate migration numbers
check_duplicate_migrations() {
    echo ""
    echo "Checking for duplicate migration numbers..."
    
    cd database/migrations
    
    # Extract migration numbers and check for duplicates
    ls *.sql | sed 's/_.*//' | sort | uniq -d > /tmp/duplicates.txt
    
    if [ -s /tmp/duplicates.txt ]; then
        echo -e "${RED}WARNING: Found duplicate migration numbers:${NC}"
        cat /tmp/duplicates.txt
        echo ""
        echo "Moving duplicates to backup..."
        
        while read -r num; do
            # Keep the first one, move others
            count=0
            for file in ${num}_*.sql; do
                if [ $count -gt 0 ]; then
                    echo "  Moving $file to backup..."
                    mv "$file" "../../$BACKUP_DIR/"
                fi
                ((count++))
            done
        done < /tmp/duplicates.txt
    else
        echo -e "${GREEN}✓ No duplicate migration numbers found${NC}"
    fi
    
    cd ../..
}

# Function to check for unused files
check_unused_files() {
    echo ""
    echo "Checking for potentially unused files..."
    
    # Check for .bak, .old, .tmp files
    find . -type f \( -name "*.bak" -o -name "*.old" -o -name "*.tmp" -o -name "*~" \) > /tmp/unused_files.txt
    
    if [ -s /tmp/unused_files.txt ]; then
        echo -e "${YELLOW}Found backup/temporary files:${NC}"
        cat /tmp/unused_files.txt
        echo ""
        echo "Moving to backup..."
        while read -r file; do
            mkdir -p "$BACKUP_DIR/$(dirname "$file")"
            mv "$file" "$BACKUP_DIR/$file"
        done < /tmp/unused_files.txt
    else
        echo -e "${GREEN}✓ No backup/temporary files found${NC}"
    fi
}

# Function to validate PHP syntax
validate_php_syntax() {
    echo ""
    echo "Validating PHP syntax..."
    
    errors=0
    find app public -name "*.php" -type f | while read -r file; do
        if ! php -l "$file" > /dev/null 2>&1; then
            echo -e "${RED}✗ Syntax error in: $file${NC}"
            ((errors++))
        fi
    done
    
    if [ $errors -eq 0 ]; then
        echo -e "${GREEN}✓ All PHP files have valid syntax${NC}"
    else
        echo -e "${RED}Found $errors PHP syntax errors${NC}"
    fi
}

# Function to check for missing database tables in migrations
validate_database_schema() {
    echo ""
    echo "Validating database schema..."
    
    # Extract all CREATE TABLE statements
    grep -h "CREATE TABLE" database/migrations/*.sql | \
        sed 's/CREATE TABLE IF NOT EXISTS //' | \
        sed 's/CREATE TABLE //' | \
        sed 's/ .*//' | \
        sed 's/`//g' | \
        sort -u > /tmp/db_tables.txt
    
    echo -e "${GREEN}✓ Found $(wc -l < /tmp/db_tables.txt) unique tables in migrations${NC}"
}

# Function to check for security issues
check_security() {
    echo ""
    echo "Checking for security issues..."
    
    # Check for hardcoded passwords
    if grep -r "password.*=.*['\"]" app/ --include="*.php" | grep -v "password_hash" | grep -v "// " > /tmp/passwords.txt 2>/dev/null; then
        echo -e "${YELLOW}WARNING: Potential hardcoded passwords found:${NC}"
        head -5 /tmp/passwords.txt
    else
        echo -e "${GREEN}✓ No hardcoded passwords found${NC}"
    fi
    
    # Check for debug mode
    if grep -r "APP_DEBUG.*=.*true" . --include=".env*" > /dev/null 2>&1; then
        echo -e "${YELLOW}WARNING: Debug mode is enabled in .env${NC}"
    else
        echo -e "${GREEN}✓ Debug mode is disabled${NC}"
    fi
}

# Function to organize documentation
organize_documentation() {
    echo ""
    echo "Organizing documentation..."
    
    # Move any stray .md files to docs
    find . -maxdepth 1 -name "*.md" -not -name "README.md" -not -name "LICENSE" -type f > /tmp/stray_docs.txt
    
    if [ -s /tmp/stray_docs.txt ]; then
        echo "Moving documentation files to docs/..."
        while read -r file; do
            mv "$file" "docs/"
            echo "  Moved $file"
        done < /tmp/stray_docs.txt
    else
        echo -e "${GREEN}✓ All documentation is organized${NC}"
    fi
}

# Function to check file permissions
check_permissions() {
    echo ""
    echo "Checking file permissions..."
    
    # Storage should be writable
    if [ -w "storage" ]; then
        echo -e "${GREEN}✓ storage/ is writable${NC}"
    else
        echo -e "${RED}✗ storage/ is not writable${NC}"
        chmod -R 775 storage
        echo "  Fixed permissions"
    fi
    
    # public/uploads should be writable
    if [ -w "public/uploads" ]; then
        echo -e "${GREEN}✓ public/uploads/ is writable${NC}"
    else
        echo -e "${RED}✗ public/uploads/ is not writable${NC}"
        chmod -R 775 public/uploads
        echo "  Fixed permissions"
    fi
}

# Function to generate production checklist
generate_checklist() {
    echo ""
    echo "Generating production checklist..."
    
    cat > PRODUCTION_CHECKLIST.md << 'EOF'
# Production Deployment Checklist

## Pre-Deployment

### Configuration
- [ ] Update `.env` with production values
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure production database credentials
- [ ] Set secure `APP_KEY` and `JWT_SECRET`
- [ ] Configure OAuth providers (if using SSO)
- [ ] Set up email configuration (SMTP)
- [ ] Configure payment gateways (Stripe, Square)

### Security
- [ ] Review and update `.gitignore`
- [ ] Ensure no sensitive data in repository
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Make storage/ and public/uploads/ writable (775)
- [ ] Enable HTTPS/SSL
- [ ] Configure firewall rules
- [ ] Set up fail2ban or similar
- [ ] Review security headers

### Database
- [ ] Run all migrations in order
- [ ] Verify all tables created successfully
- [ ] Set up database backups
- [ ] Configure database user with minimal permissions
- [ ] Test database connection

### Performance
- [ ] Enable OPcache for PHP
- [ ] Configure Redis/Memcached (if available)
- [ ] Set up CDN for static assets
- [ ] Enable gzip compression
- [ ] Optimize images

### Monitoring
- [ ] Set up error logging
- [ ] Configure application monitoring
- [ ] Set up uptime monitoring
- [ ] Configure backup monitoring
- [ ] Set up alerts for critical errors

## Post-Deployment

### Testing
- [ ] Test user login
- [ ] Test SSO login (if configured)
- [ ] Test POS functionality
- [ ] Test customer management
- [ ] Test product management
- [ ] Test reporting
- [ ] Test mobile responsiveness
- [ ] Test PWA installation

### Documentation
- [ ] Update README with production URL
- [ ] Document deployment process
- [ ] Create admin user guide
- [ ] Create backup/restore procedures

### Maintenance
- [ ] Schedule regular backups
- [ ] Plan for updates and patches
- [ ] Set up monitoring dashboards
- [ ] Create incident response plan

## Verification

- [ ] All migrations run successfully
- [ ] No PHP errors in logs
- [ ] All features working as expected
- [ ] Performance is acceptable
- [ ] Security scan passed
- [ ] Backup system tested
- [ ] SSL certificate valid
- [ ] DNS configured correctly

## Sign-off

- [ ] Development team approval
- [ ] QA team approval
- [ ] Security team approval
- [ ] Management approval

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Version:** 1.1.0
EOF

    echo -e "${GREEN}✓ Created PRODUCTION_CHECKLIST.md${NC}"
}

# Main execution
echo "Starting cleanup process..."
echo ""

check_duplicate_migrations
check_unused_files
organize_documentation
validate_php_syntax
validate_database_schema
check_security
check_permissions
generate_checklist

echo ""
echo "======================================"
echo "Cleanup Summary"
echo "======================================"
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
echo -e "${GREEN}Cleanup complete!${NC}"
echo ""
echo "Next steps:"
echo "1. Review PRODUCTION_CHECKLIST.md"
echo "2. Test the application"
echo "3. Run database migrations"
echo "4. Deploy to production"
echo ""
