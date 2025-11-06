#!/bin/bash
#===============================================================================
# Nautilus Complete Deployment Script
# Syncs all application files, database migrations, and course enrollment workflow
#===============================================================================

echo "================================================================"
echo "  Nautilus Dive Shop - Complete Deployment"
echo "================================================================"
echo ""
echo "This script will sync:"
echo "  ✓ Core application files (controllers, services, views)"
echo "  ✓ Database migrations and seeders"
echo "  ✓ Course enrollment workflow"
echo "  ✓ POS system updates"
echo "  ✓ Install/setup fixes"
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    exit 1
fi

echo ""
echo "Step 1: Syncing Core Application Files"
echo "================================================================"

# Services
echo "→ Services..."
mkdir -p /var/www/html/nautilus/app/Services/Courses
mkdir -p /var/www/html/nautilus/app/Services/POS
mkdir -p /var/www/html/nautilus/app/Services/Install
rsync -av /home/wrnash1/development/nautilus/app/Services/Courses/EnrollmentService.php /var/www/html/nautilus/app/Services/Courses/
rsync -av /home/wrnash1/development/nautilus/app/Services/POS/TransactionService.php /var/www/html/nautilus/app/Services/POS/
rsync -av /home/wrnash1/development/nautilus/app/Services/Install/InstallService.php /var/www/html/nautilus/app/Services/Install/

# Controllers
echo "→ Controllers..."
mkdir -p /var/www/html/nautilus/app/Controllers/API
mkdir -p /var/www/html/nautilus/app/Controllers/Courses
rsync -av /home/wrnash1/development/nautilus/app/Controllers/Courses/CourseController.php /var/www/html/nautilus/app/Controllers/Courses/
rsync -av /home/wrnash1/development/nautilus/app/Controllers/API/CourseScheduleController.php /var/www/html/nautilus/app/Controllers/API/

# Views
echo "→ Views..."
mkdir -p /var/www/html/nautilus/app/Views/courses/schedules
mkdir -p /var/www/html/nautilus/app/Views/pos
rsync -av /home/wrnash1/development/nautilus/app/Views/pos/index.php /var/www/html/nautilus/app/Views/pos/
rsync -av /home/wrnash1/development/nautilus/app/Views/courses/schedules/roster_show.php /var/www/html/nautilus/app/Views/courses/schedules/show.php

# JavaScript
echo "→ JavaScript..."
rsync -av /home/wrnash1/development/nautilus/public/assets/js/pos-course-enrollment.js /var/www/html/nautilus/public/assets/js/
rsync -av /home/wrnash1/development/nautilus/public/assets/js/professional-pos.js /var/www/html/nautilus/public/assets/js/

# Routes
echo "→ Routes..."
rsync -av /home/wrnash1/development/nautilus/routes/web.php /var/www/html/nautilus/routes/

echo "✓ Core application files synced"
echo ""

echo "Step 2: Syncing Database Migrations"
echo "================================================================"

mkdir -p /var/www/html/nautilus/database/migrations
rsync -av /home/wrnash1/development/nautilus/database/migrations/ /var/www/html/nautilus/database/migrations/

# Count migrations
MIGRATION_COUNT=$(ls -1 /var/www/html/nautilus/database/migrations/*.sql 2>/dev/null | wc -l)
echo "✓ Synced $MIGRATION_COUNT migration files"
echo ""

echo "Step 3: Syncing Database Seeders"
echo "================================================================"

mkdir -p /var/www/html/nautilus/database/seeders
rsync -av /home/wrnash1/development/nautilus/database/seeders/ /var/www/html/nautilus/database/seeders/

SEEDER_COUNT=$(ls -1 /var/www/html/nautilus/database/seeders/*.sql 2>/dev/null | wc -l)
echo "✓ Synced $SEEDER_COUNT seeder files"
echo ""

echo "Step 4: Syncing Documentation"
echo "================================================================"

rsync -av /home/wrnash1/development/nautilus/COURSE_ENROLLMENT_WORKFLOW.md /var/www/html/nautilus/ 2>/dev/null || true
rsync -av /home/wrnash1/development/nautilus/COURSE_ENROLLMENT_IMPLEMENTATION.md /var/www/html/nautilus/ 2>/dev/null || true
rsync -av /home/wrnash1/development/nautilus/DEPLOY_COURSE_ENROLLMENT.md /var/www/html/nautilus/ 2>/dev/null || true

echo "✓ Documentation synced"
echo ""

echo "Step 5: Setting Permissions"
echo "================================================================"

chown -R apache:apache /var/www/html/nautilus/app
chown -R apache:apache /var/www/html/nautilus/public/assets/js
chown -R apache:apache /var/www/html/nautilus/routes
chown -R apache:apache /var/www/html/nautilus/database
chmod -R 755 /var/www/html/nautilus/app
chmod -R 755 /var/www/html/nautilus/routes
chmod -R 755 /var/www/html/nautilus/database
chmod 644 /var/www/html/nautilus/public/assets/js/*.js

echo "✓ Permissions set"
echo ""

echo "================================================================"
echo "  ✅  DEPLOYMENT COMPLETE!"
echo "================================================================"
echo ""
echo "📋 What Was Deployed:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🔧 Core Updates:"
echo "  ✓ InstallService (fixed cash_drawers table check)"
echo "  ✓ TransactionService (course enrollment integration)"
echo "  ✓ EnrollmentService (new - handles enrollments & transfers)"
echo ""
echo "🎓 Course Enrollment Workflow:"
echo "  ✓ POS course schedule selection modal"
echo "  ✓ Automatic enrollment on purchase"
echo "  ✓ Instructor roster view with full student details"
echo "  ✓ Student transfer between schedules"
echo "  ✓ API endpoint: GET /store/api/courses/{id}/schedules"
echo "  ✓ Transfer endpoint: POST /store/courses/transfer-student"
echo ""
echo "💾 Database:"
echo "  ✓ $MIGRATION_COUNT migrations ready"
echo "  ✓ $SEEDER_COUNT seeders ready"
echo "  ✓ Installation fix for cash_drawers and certification_agencies tables"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎯 Next Steps:"
echo ""
echo "For Fresh Installation:"
echo "  1. Navigate to: https://nautilus.local/install"
echo "  2. Follow installation wizard"
echo "  3. Database will be created and migrated automatically"
echo ""
echo "For Existing Installation:"
echo "  1. Application files are updated"
echo "  2. Run migrations if needed: php database/migrate.php"
echo "  3. Clear cache if applicable"
echo ""
echo "Testing Course Enrollment:"
echo "  1. POS: https://nautilus.local/store/pos"
echo "  2. Courses: https://nautilus.local/store/courses"
echo "  3. Create a course → Create a schedule → Test enrollment at POS"
echo ""
echo "================================================================"
echo ""
