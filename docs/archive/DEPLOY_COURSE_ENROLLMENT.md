# Course Enrollment Deployment Quick Guide

## 🚀 Quick Deploy

Run this single command to deploy everything:

```bash
sudo bash /tmp/sync-course-enrollment-quick.sh
```

That's it! The script will sync all files and set permissions.

---

## 📋 What Gets Deployed

### Services (Business Logic)
- `app/Services/Courses/EnrollmentService.php` ← **NEW**
- `app/Services/POS/TransactionService.php` ← Updated

### Controllers
- `app/Controllers/Courses/CourseController.php` ← Updated
- `app/Controllers/API/CourseScheduleController.php` ← **NEW**

### Views
- `app/Views/pos/index.php` ← Updated (schedule modal)
- `app/Views/courses/schedules/show.php` ← **NEW** (roster view)

### JavaScript
- `public/assets/js/pos-course-enrollment.js` ← **NEW**
- `public/assets/js/professional-pos.js` ← Updated

### Routes
- `routes/web.php` ← Updated (new API endpoint & transfer route)

### Documentation
- `COURSE_ENROLLMENT_WORKFLOW.md`
- `COURSE_ENROLLMENT_IMPLEMENTATION.md`

---

## ✅ Post-Deploy Verification

### 1. Check File Permissions
```bash
ls -l /var/www/html/nautilus/app/Services/Courses/EnrollmentService.php
# Should show: -rwxr-xr-x apache apache
```

### 2. Test POS Access
Navigate to: https://nautilus.local/store/pos
- Should load without errors
- Check browser console (F12) for JavaScript errors

### 3. Test API Endpoint
```bash
curl -X GET https://nautilus.local/store/api/courses/1/schedules \
  -H "Cookie: your-session-cookie"
```
Expected: JSON array of schedules (or empty array if no schedules exist)

### 4. Test Roster View
Navigate to: https://nautilus.local/store/courses/schedules
- Click "View" on any schedule
- Should show roster page without errors

---

## 🧪 Testing Workflow

### Create Test Course
1. Go to: https://nautilus.local/store/courses
2. Click "Create Course"
3. Fill in:
   - Name: "Test Open Water"
   - Course Code: "TEST-OW"
   - Price: $399.00
   - Duration: 3 days
   - Max Students: 8
4. Save

### Create Test Schedule
1. Go to: https://nautilus.local/store/courses/schedules
2. Click "Add Schedule"
3. Fill in:
   - Course: "Test Open Water"
   - Instructor: (select one)
   - Start Date: Tomorrow
   - End Date: 3 days from now
   - Times: 9:00 AM - 5:00 PM
   - Location: "Training Pool"
   - Max Students: 8
4. Save

### Test POS Enrollment
1. Go to: https://nautilus.local/store/pos
2. Search for a customer (or create one)
3. Click the "Open Water Diver" course tile
4. **Expected:** Modal appears with schedule selection
5. Select the test schedule you created
6. Complete the sale (any payment method)
7. **Expected:** Sale completes successfully

### Verify Enrollment
```bash
mysql -u nautilus -p nautilus -e "SELECT * FROM course_enrollments ORDER BY id DESC LIMIT 1;"
```
Should show the new enrollment with:
- Correct customer_id
- Correct schedule_id
- Correct amount_paid
- Status: 'enrolled'
- Payment_status: 'paid'

### Test Roster View
1. Go to: https://nautilus.local/store/courses/schedules
2. Click "View" on your test schedule
3. **Expected:** Student appears in roster
4. Verify displays:
   - Student name
   - Contact information
   - Payment status (should be green "PAID")
   - Enrollment status (should be blue "Enrolled")

### Test Transfer
1. On the roster page, click "Transfer" button
2. **Expected:** Modal appears
3. Create a second test schedule first (same course)
4. Select new schedule from dropdown
5. Enter reason: "Testing transfer"
6. Submit
7. **Expected:** Page reloads, student moved to new schedule

---

## ❌ Troubleshooting

### Error: "showCourseScheduleModal is not a function"
**Cause:** JavaScript not loading
**Fix:**
```bash
ls -l /var/www/html/nautilus/public/assets/js/pos-course-enrollment.js
# If missing, re-run sync script
```

### Error: 403 Forbidden on API endpoint
**Cause:** Session not authenticated
**Fix:** Make sure you're logged in. API requires `pos.view` permission.

### Error: "Call to undefined method enrollFromTransaction"
**Cause:** EnrollmentService not loaded
**Fix:**
```bash
ls -l /var/www/html/nautilus/app/Services/Courses/EnrollmentService.php
# If missing, re-run sync script
```

### Schedule modal shows "No schedules available"
**Cause:** No schedules created for that course
**Fix:** Create a schedule first (see "Create Test Schedule" above)

### Student not enrolled after purchase
**Check:**
1. Browser console for JavaScript errors
2. Application logs: `tail -f /var/www/html/nautilus/storage/logs/app.log`
3. Database: `SELECT * FROM course_enrollments ORDER BY id DESC;`

**Common causes:**
- Course tile missing `data-course-id` attribute
- Customer not selected at POS (walk-in sales)
- Enrollment service error (check logs)

### Transfer button not appearing
**Cause:** Missing `courses.edit` permission
**Fix:** Add permission to your role:
```sql
-- Check your role
SELECT * FROM users WHERE id = YOUR_USER_ID;

-- Check permissions
SELECT * FROM role_permissions WHERE role_id = YOUR_ROLE_ID;

-- Add if missing (example for role_id = 1):
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE name = 'courses.edit';
```

---

## 🔧 Manual Sync (If Script Fails)

If the quick script doesn't work, sync files manually:

```bash
# Services
sudo rsync -av /home/wrnash1/development/nautilus/app/Services/Courses/EnrollmentService.php /var/www/html/nautilus/app/Services/Courses/
sudo rsync -av /home/wrnash1/development/nautilus/app/Services/POS/TransactionService.php /var/www/html/nautilus/app/Services/POS/

# Controllers
sudo rsync -av /home/wrnash1/development/nautilus/app/Controllers/Courses/CourseController.php /var/www/html/nautilus/app/Controllers/Courses/
sudo mkdir -p /var/www/html/nautilus/app/Controllers/API
sudo rsync -av /home/wrnash1/development/nautilus/app/Controllers/API/CourseScheduleController.php /var/www/html/nautilus/app/Controllers/API/

# Views
sudo rsync -av /home/wrnash1/development/nautilus/app/Views/pos/index.php /var/www/html/nautilus/app/Views/pos/
sudo rsync -av /home/wrnash1/development/nautilus/app/Views/courses/schedules/roster_show.php /var/www/html/nautilus/app/Views/courses/schedules/show.php

# JavaScript
sudo rsync -av /home/wrnash1/development/nautilus/public/assets/js/pos-course-enrollment.js /var/www/html/nautilus/public/assets/js/
sudo rsync -av /home/wrnash1/development/nautilus/public/assets/js/professional-pos.js /var/www/html/nautilus/public/assets/js/

# Routes
sudo rsync -av /home/wrnash1/development/nautilus/routes/web.php /var/www/html/nautilus/routes/

# Permissions
sudo chown -R apache:apache /var/www/html/nautilus/app
sudo chown -R apache:apache /var/www/html/nautilus/public/assets/js
sudo chown -R apache:apache /var/www/html/nautilus/routes
sudo chmod -R 755 /var/www/html/nautilus/app
sudo chmod 644 /var/www/html/nautilus/public/assets/js/*.js
```

---

## 📊 Database Schema Check

Verify required tables exist:

```bash
mysql -u nautilus -p nautilus -e "SHOW TABLES LIKE 'course%';"
```

Should show:
- course_attendance
- course_enrollments
- course_schedules
- courses

If any are missing, run migrations:
```bash
cd /var/www/html/nautilus
php database/migrate.php
```

---

## 🎯 Success Indicators

After deployment, you should see:

✅ **POS:** Course tiles trigger schedule selection modal
✅ **POS:** Modal shows available schedules with spot counts
✅ **POS:** Checkout completes and creates enrollment
✅ **Roster:** Instructors can view enrolled students
✅ **Roster:** Export to CSV works
✅ **Transfer:** Staff can move students between schedules
✅ **API:** GET `/store/api/courses/{id}/schedules` returns JSON
✅ **Logs:** No PHP errors in application log

---

## 📞 Support

If you encounter issues:

1. Check application logs: `/var/www/html/nautilus/storage/logs/app.log`
2. Check Apache error log: `/var/log/httpd/error_log`
3. Check browser console (F12 > Console tab)
4. Review implementation docs: `COURSE_ENROLLMENT_IMPLEMENTATION.md`

---

**Last Updated:** November 5, 2025
**Status:** Ready for deployment
