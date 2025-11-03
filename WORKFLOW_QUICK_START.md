# Course Workflow System - Quick Start Guide

## Immediate Setup (5 Minutes)

### Step 1: Run the Migration
```bash
cd /home/wrnash1/Developer/nautilus
php8.2 scripts/migrate.php
```

This creates:
- 6 new tables for requirement tracking
- 10 default requirement types
- 5 default e-learning modules
- Instructor notification system

### Step 2: Verify Email Configuration
Check your `.env` file has:
```ini
MAIL_FROM_ADDRESS=info@nautilus.local
MAIL_FROM_NAME=Nautilus Dive Shop
APP_URL=https://nautilus.local
```

### Step 3: Test It!
1. Go to Point of Sale
2. Add a customer
3. Add a course to the transaction
4. Complete the sale

**Expected Results:**
- ✅ Customer gets welcome email
- ✅ Instructor gets notification
- ✅ Requirements created
- ✅ Roster updated

## Quick Feature Overview

### For Staff

**Enrolling a Student:**
- Use POS or go to Courses → Schedules → [Select Schedule] → Add Enrollment
- System handles everything automatically

**Viewing Roster:**
- Courses → Schedules → [Select Schedule] → View Roster
- See all students with completion progress

**Marking Requirements Complete:**
1. Click "View Requirements" on roster
2. Check off completed items
3. System notifies instructor when all done

### For Instructors

**Viewing Your Classes:**
- Login → Courses → Schedules
- Filter by your name
- Click "View Roster"

**Roster Shows:**
- Student photos
- Contact info
- Completion percentage (e.g., 7/10 = 70%)
- Status: Ready, In Progress, or Pending

**When Students Enroll:**
- You get email notification
- You get in-app notification (bell icon)
- Roster updates immediately

### For Students (What They Receive)

**Immediate Welcome Email:**
- Course details (dates, instructor, location)
- Complete requirements list
- Instructions for each requirement
- Important deadlines

**Reminder Email:**
- List of pending requirements
- Urgency notice if close to course date
- Contact info for help

## Common Tasks

### Adding Requirements to a Course

**Via SQL:**
```sql
-- Example: Add waiver requirement to course ID 1
INSERT INTO course_requirements
(course_id, requirement_type_id, is_mandatory, due_before_start_days, instructions, sort_order)
VALUES
(1, 1, TRUE, 3, 'Sign the liability waiver online or at the shop', 1);
```

**Available Requirement Types** (from `course_requirement_types`):
1. Liability Waiver
2. Medical Questionnaire
3. Student Photo
4. E-Learning Completion
5. Previous Certification Card
6. Government ID
7. Passport Photo
8. Medical Clearance
9. Equipment Waiver
10. Minor Release

### Checking Student Progress

**Method 1 - Roster View:**
- Navigate to roster
- Visual progress bars show completion
- Click "View Requirements" for details

**Method 2 - Database:**
```sql
SELECT
    CONCAT(c.first_name, ' ', c.last_name) as student_name,
    COUNT(*) as total_requirements,
    SUM(CASE WHEN er.is_completed = TRUE THEN 1 ELSE 0 END) as completed_requirements
FROM enrollment_requirements er
JOIN course_enrollments ce ON er.enrollment_id = ce.id
JOIN customers c ON ce.customer_id = c.id
WHERE ce.schedule_id = {schedule_id}
GROUP BY ce.id, c.first_name, c.last_name;
```

### Manually Triggering Workflow

If you enroll a student manually and want to trigger notifications:
```php
use App\Services\Courses\CourseEnrollmentWorkflow;

$workflow = new CourseEnrollmentWorkflow();
$workflow->processEnrollment($enrollmentId);
```

### Resending Welcome Email

```php
use App\Services\Email\EmailService;

$emailService = new EmailService();
$enrollment = /* fetch enrollment data */;

$emailService->sendTemplate(
    $enrollment['customer_email'],
    'course_enrollment_welcome',
    [
        'subject' => 'Welcome to ' . $enrollment['course_name'],
        'customer_name' => $enrollment['first_name'],
        'course_name' => $enrollment['course_name'],
        // ... other data
    ]
);
```

## Troubleshooting

### Email Not Sending

**Check 1 - Email Configuration:**
```bash
# Verify .env settings
cat .env | grep MAIL
```

**Check 2 - Test Email:**
```php
use App\Services\Email\EmailService;

$emailService = new EmailService();
$result = $emailService->testConnection();
var_dump($result);
```

**Check 3 - Logs:**
```bash
tail -f logs/app.log
```

### Requirements Not Showing

**Check 1 - Course Has Requirements:**
```sql
SELECT * FROM course_requirements WHERE course_id = {course_id};
```

**Check 2 - Enrollment Requirements Created:**
```sql
SELECT * FROM enrollment_requirements WHERE enrollment_id = {enrollment_id};
```

**Fix - Manually Create:**
```php
$workflow = new CourseEnrollmentWorkflow();
$workflow->createRequirementChecklist($enrollmentId, $courseId);
```

### Instructor Not Receiving Notifications

**Check 1 - Instructor Assigned:**
```sql
SELECT instructor_id FROM course_schedules WHERE id = {schedule_id};
```

**Check 2 - Instructor Email:**
```sql
SELECT email FROM users WHERE id = {instructor_id};
```

**Check 3 - Notification Created:**
```sql
SELECT * FROM instructor_notifications
WHERE instructor_id = {instructor_id}
ORDER BY created_at DESC LIMIT 5;
```

## Default Course Setup Example

### Open Water Diver Course Requirements

```sql
-- Assuming course_id = 1 for Open Water Diver
INSERT INTO course_requirements
(course_id, requirement_type_id, is_mandatory, due_before_start_days, instructions, sort_order)
VALUES
(1, 1, TRUE, 3, 'Sign liability waiver online or at dive shop', 1),
(1, 2, TRUE, 7, 'Complete medical questionnaire. Physician approval required for certain conditions', 2),
(1, 3, TRUE, 3, 'Upload passport-style photo for certification card', 3),
(1, 4, TRUE, 7, 'Complete PADI eLearning Open Water Diver online course', 4);
```

### Advanced Open Water Course Requirements

```sql
-- Assuming course_id = 2 for Advanced Open Water
INSERT INTO course_requirements
(course_id, requirement_type_id, is_mandatory, due_before_start_days, instructions, sort_order)
VALUES
(2, 1, TRUE, 3, 'Sign liability waiver', 1),
(2, 5, TRUE, 7, 'Provide copy of Open Water Diver certification card', 2),
(2, 3, FALSE, 3, 'New photo if needed for updated card', 3),
(2, 4, TRUE, 7, 'Complete PADI eLearning Advanced Open Water course', 4);
```

### Rescue Diver Course Requirements

```sql
-- Assuming course_id = 3 for Rescue Diver
INSERT INTO course_requirements
(course_id, requirement_type_id, is_mandatory, due_before_start_days, instructions, sort_order)
VALUES
(3, 1, TRUE, 3, 'Sign liability waiver', 1),
(3, 5, TRUE, 7, 'Provide copy of Advanced Open Water certification', 2),
(3, 2, TRUE, 7, 'Medical questionnaire required - must be fit for rescue diving', 3),
(3, 4, TRUE, 7, 'Complete PADI eLearning Rescue Diver course', 4);
```

## API Endpoints (For Future Integration)

### Get Enrollment Requirements
```http
GET /api/courses/enrollments/{enrollmentId}/requirements

Response:
{
    "student_name": "John Doe",
    "course_name": "Open Water Diver",
    "requirements": [
        {
            "id": 1,
            "requirement_name": "Liability Waiver",
            "requirement_type": "waiver",
            "is_mandatory": true,
            "is_completed": false,
            "status": "pending",
            "instructions": "Sign liability waiver online or at dive shop"
        }
    ]
}
```

### Mark Requirement Complete
```http
POST /api/courses/requirements/{requirementId}/complete

Body:
{
    "waiver_id": 123,
    "notes": "Verified in person"
}

Response:
{
    "success": true,
    "message": "Requirement marked complete"
}
```

## Testing Checklist

- [ ] Run migration 036 successfully
- [ ] Verify email configuration
- [ ] Test enrollment workflow:
  - [ ] Create test enrollment
  - [ ] Verify welcome email received
  - [ ] Verify instructor notified
  - [ ] Check requirement checklist created
- [ ] Test roster view:
  - [ ] Navigate to roster
  - [ ] See test student listed
  - [ ] Progress bar shows 0%
  - [ ] View requirements modal works
- [ ] Test marking requirements complete:
  - [ ] Mark one requirement complete
  - [ ] Progress bar updates
  - [ ] Verify in database
- [ ] Test completion notification:
  - [ ] Mark all requirements complete
  - [ ] Instructor gets "Student Ready" notification

## Production Deployment

### Pre-Deployment
1. Backup database
2. Test in staging environment
3. Review and customize email templates
4. Train staff on new features
5. Train instructors on roster view

### Deployment
1. Run migration 036
2. Verify email settings
3. Set up requirements for all courses
4. Test with one real enrollment
5. Monitor for issues

### Post-Deployment
1. Monitor email delivery
2. Check notification system
3. Gather feedback from instructors
4. Adjust requirements as needed
5. Review completion rates after first courses

## Support

**Documentation:**
- Full docs: `/docs/COURSE_WORKFLOW_SYSTEM.md`
- Overview: `/STREAMLINED_WORKFLOW_SUMMARY.md`
- This guide: `/WORKFLOW_QUICK_START.md`

**Logs:**
- Application: `/logs/app.log`
- Email: Check EmailService log entries
- Database: Check `instructor_notifications` and `enrollment_requirements` tables

**Need Help?**
- Review documentation
- Check logs for errors
- Test individual components
- Contact development team

---

## Quick Command Reference

```bash
# Run migration
php8.2 scripts/migrate.php

# Check logs
tail -f logs/app.log

# Check course requirements
mysql -u root -pFrogman09! nautilus -e "SELECT * FROM course_requirements WHERE course_id = 1;"

# Check enrollment requirements for student
mysql -u root -pFrogman09! nautilus -e "SELECT * FROM enrollment_requirements WHERE enrollment_id = 1;"

# View instructor notifications
mysql -u root -pFrogman09! nautilus -e "SELECT * FROM instructor_notifications ORDER BY created_at DESC LIMIT 10;"

# Test email configuration
php8.2 -r "require 'vendor/autoload.php'; use App\Services\Email\EmailService; \$e = new EmailService(); print_r(\$e->testConnection());"
```

---

## Summary

Your system is now fully streamlined! When a customer signs up for a class:
1. ✅ Everything happens automatically
2. ✅ Customer gets welcome email
3. ✅ Instructor gets notified
4. ✅ Requirements tracked
5. ✅ Roster updates
6. ✅ Everyone stays informed

No manual work required! 🎉🤿
