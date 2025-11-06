#!/bin/bash
#===============================================================================
# Course Enrollment Workflow Deployment
# Quick deployment script specifically for course enrollment features
#===============================================================================

echo "================================================================"
echo "  Course Enrollment Workflow - Deployment"
echo "================================================================"
echo ""

# Services
echo "→ Syncing Services..."
sudo rsync -av /home/wrnash1/development/nautilus/app/Services/Courses/EnrollmentService.php /var/www/html/nautilus/app/Services/Courses/
sudo rsync -av /home/wrnash1/development/nautilus/app/Services/POS/TransactionService.php /var/www/html/nautilus/app/Services/POS/

# Controllers
echo "→ Syncing Controllers..."
sudo rsync -av /home/wrnash1/development/nautilus/app/Controllers/Courses/CourseController.php /var/www/html/nautilus/app/Controllers/Courses/
sudo mkdir -p /var/www/html/nautilus/app/Controllers/API
sudo rsync -av /home/wrnash1/development/nautilus/app/Controllers/API/CourseScheduleController.php /var/www/html/nautilus/app/Controllers/API/

# Views
echo "→ Syncing Views..."
sudo rsync -av /home/wrnash1/development/nautilus/app/Views/pos/index.php /var/www/html/nautilus/app/Views/pos/
sudo rsync -av /home/wrnash1/development/nautilus/app/Views/courses/schedules/roster_show.php /var/www/html/nautilus/app/Views/courses/schedules/show.php

# JavaScript
echo "→ Syncing JavaScript..."
sudo rsync -av /home/wrnash1/development/nautilus/public/assets/js/pos-course-enrollment.js /var/www/html/nautilus/public/assets/js/
sudo rsync -av /home/wrnash1/development/nautilus/public/assets/js/professional-pos.js /var/www/html/nautilus/public/assets/js/

# Routes
echo "→ Syncing Routes..."
sudo rsync -av /home/wrnash1/development/nautilus/routes/web.php /var/www/html/nautilus/routes/

# Permissions
echo "→ Setting Permissions..."
sudo chown -R apache:apache /var/www/html/nautilus/app
sudo chown -R apache:apache /var/www/html/nautilus/public/assets/js
sudo chown -R apache:apache /var/www/html/nautilus/routes
sudo chmod -R 755 /var/www/html/nautilus/app
sudo chmod 644 /var/www/html/nautilus/public/assets/js/*.js

echo ""
echo "✅ Course Enrollment Workflow Deployed!"
echo ""
echo "Test at: https://nautilus.local/store/pos"
echo ""
