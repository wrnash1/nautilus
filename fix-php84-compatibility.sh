#!/bin/bash
# Fix PHP 8.4 nullable parameter deprecation warnings

echo "Fixing PHP 8.4 compatibility issues..."
echo ""

APP_ROOT="/home/wrnash1/development/nautilus"

# Fix ReportService.php
echo "1. Fixing ReportService.php..."
sed -i 's/getTopCustomers($startDate = null, $endDate = null)/getTopCustomers(?string $startDate = null, ?string $endDate = null)/g' "$APP_ROOT/app/Services/Reports/ReportService.php"
sed -i 's/getBestSellingProducts($startDate = null, $endDate = null, $limit = 10)/getBestSellingProducts(?string $startDate = null, ?string $endDate = null, int $limit = 10)/g' "$APP_ROOT/app/Services/Reports/ReportService.php"
sed -i 's/getRevenueByCategory($startDate = null, $endDate = null)/getRevenueByCategory(?string $startDate = null, ?string $endDate = null)/g' "$APP_ROOT/app/Services/Reports/ReportService.php"
sed -i 's/getPaymentMethodBreakdown($startDate = null, $endDate = null)/getPaymentMethodBreakdown(?string $startDate = null, ?string $endDate = null)/g' "$APP_ROOT/app/Services/Reports/ReportService.php"

# Fix EmailService.php
echo "2. Fixing EmailService.php..."
sed -i 's/sendWelcomeEmail($user, $password = null)/sendWelcomeEmail($user, ?string $password = null)/g' "$APP_ROOT/app/Services/Email/EmailService.php"

# Fix CourseService.php
echo "3. Fixing CourseService.php..."
find "$APP_ROOT/app/Services/Courses" -name "*.php" -exec sed -i 's/= null)/ ?string = null)/g' {} \;

echo ""
echo "✓ PHP 8.4 compatibility fixes applied"
echo ""
echo "Next: Sync to web server with sudo /tmp/sync-quick.sh"
