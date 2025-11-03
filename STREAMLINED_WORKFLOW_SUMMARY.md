# Nautilus - Streamlined Workflow Summary

## Overview

Nautilus now has a **fully integrated, automated workflow system** that eliminates manual processes and ensures everything flows together seamlessly. When a customer signs up for a class in POS, the system automatically handles everything from welcome emails to instructor notifications to requirement tracking.

## The Complete Flow

### 1. Customer Signs Up for a Class (POS or Online)

**What Happens Automatically:**
```
✅ Enrollment record created in database
✅ Roster count updated immediately
✅ Student requirement checklist created
✅ Welcome email sent to customer
✅ Instructor notified (email + in-app)
✅ Admin users notified
✅ Requirement reminder email sent
✅ Waiver email queued (if applicable)
✅ All actions logged
```

**Customer Receives:**
- Professional welcome email with:
  - Course details (name, dates, instructor, location)
  - Complete requirements list
  - Instructions for each requirement
  - Important deadlines
  - Link to view their enrollment

**Instructor Receives:**
- In-app notification: "New Student Enrolled"
- Email notification with:
  - Student name and contact info
  - Course details
  - Link to view roster

### 2. Student Completes Requirements

**Requirements Tracked:**
- ✅ Liability Waiver (signed digitally or in-person)
- ✅ E-Learning Completion (PADI, SSI, etc.)
- ✅ Student Photo Upload
- ✅ Medical Questionnaire
- ✅ Previous Certification Card
- ✅ Government ID
- ✅ Any other custom requirements

**What Happens:**
```
1. Student completes requirement (signs waiver, uploads photo, etc.)
2. System marks requirement as "completed"
3. Records completion date and who verified it
4. Checks if ALL requirements are now complete
5. If yes → Notifies instructor: "Student Ready"
```

### 3. Instructor Views Roster

**Instructor Access:**
- Navigate to: Courses → Schedules → [Select Course] → View Roster
- Or click link in email notification

**Roster Shows:**
- Student photo (or initial if no photo)
- Full name
- Email and phone
- Enrollment date
- **Progress bar** showing requirement completion (e.g., "7/10 complete - 70%")
- **Status badge**:
  - 🟢 "Ready" = All requirements complete
  - 🟡 "In Progress" = 50%+ complete
  - 🔴 "Pending" = Less than 50% complete
- Quick action buttons

**Instructor Can:**
- View detailed requirements for any student
- See which specific items are pending
- Mark requirements as complete
- Print roster
- Contact students directly (email/phone links)

### 4. Everything Stays Synced

**Real-time Updates:**
- Roster count updates automatically
- Progress bars update when requirements completed
- Instructor notifications sent immediately
- Email reminders sent at configured intervals
- All data synced across POS, courses, and reports

## System Components

### New Database Tables (Migration 036)

1. **course_requirement_types** - Types of requirements (waivers, e-learning, photos, etc.)
2. **course_requirements** - Which requirements each course needs
3. **enrollment_requirements** - Student progress tracking
4. **elearning_modules** - Catalog of online learning modules
5. **course_elearning_modules** - Link courses to e-learning
6. **instructor_notifications** - Dedicated instructor notification log

### New Service Class

**CourseEnrollmentWorkflow.php** - Handles all automation:
- `processEnrollment()` - Main workflow trigger
- `createRequirementChecklist()` - Initialize student checklist
- `sendStudentWelcomeEmail()` - Send welcome email
- `notifyInstructor()` - Notify instructor
- `sendRequirementReminders()` - Send reminders
- `markRequirementComplete()` - Mark item complete
- `getRosterWithRequirements()` - Get roster data

### Updated Existing Services

**CourseService.php** - Now triggers workflow:
```php
public function enrollStudent(int $scheduleId, int $customerId, float $amountPaid = 0): int
{
    // Create enrollment
    // ...

    // Trigger automated workflow
    $this->workflow->processEnrollment($enrollmentId);

    return $enrollmentId;
}
```

### New Views

1. **roster.php** - Complete roster view with requirements
2. **course_enrollment_welcome.php** - Welcome email template
3. **instructor_new_enrollment.php** - Instructor notification email
4. **course_requirements_reminder.php** - Requirement reminder email

## Integration with Existing Systems

### ✅ Point of Sale (POS)
When staff sells a course in POS, enrollment is created and workflow automatically triggers.

### ✅ Waiver System
Existing digital waiver system (migration 024) integrated:
- Training waivers automatically queued
- When signed, marked complete in requirements
- Linked via `waiver_id` in enrollment_requirements

### ✅ Email Service
Existing EmailService.php used:
- All templates follow same format
- Same SMTP/mail configuration
- Logging and error handling

### ✅ Notification Service
Existing NotificationService.php used:
- In-app notifications for instructors
- Admin notifications
- Notification badge updates

### ✅ Customer Photos
Existing photo upload system:
- Photos uploaded in customer module
- Displayed in roster
- Can be requirement if needed

### ✅ Certification Tracking
Existing certification system:
- Previous cert cards verified
- Linked to requirements
- Tracked for prerequisites

## Example Scenarios

### Scenario 1: Open Water Diver Course

**Customer Action:**
Customer pays for Open Water Diver course at checkout

**Automatic Actions:**
1. Enrollment created ✅
2. Welcome email sent ✅
3. Requirements created:
   - Liability waiver
   - Medical questionnaire
   - Student photo
   - E-learning completion (PADI OWD)
4. Instructor John Smith notified ✅
5. Reminder email sent with requirements list ✅
6. Waiver email queued with signing link ✅

**Student Experience:**
- Receives welcome email in minutes
- Sees clear list of what to do
- Can click link to sign waiver online
- Uploads photo through customer portal
- Completes PADI eLearning at home

**Instructor Experience:**
- Gets notification: "New student: Sarah Johnson"
- Clicks to view roster
- Sees Sarah's progress: 3/4 complete (75%)
- Notes she still needs to complete eLearning
- Gets another notification when she finishes: "Student Ready"

**Staff Experience:**
- Zero manual work required
- Can check roster anytime
- Can help students if they have questions
- Everything tracked automatically

### Scenario 2: Advanced Open Water Course

**Customer Action:**
Books Advanced OW course online

**System Checks:**
- Prerequisites required? Yes (Open Water certification)
- Auto-creates requirement: "Previous certification card"

**Automatic Actions:**
1. Enrollment created ✅
2. All notifications sent ✅
3. Requirements include:
   - Liability waiver
   - OW certification proof
   - Photo (if not on file)
   - AOW eLearning
4. Student notified of requirements ✅

**When Student Uploads OW Card:**
- Staff verifies it's valid
- Marks requirement complete
- System checks all requirements
- All complete? → Instructor notified: "Student Ready"

### Scenario 3: Last Minute Enrollment

**3 Days Before Course:**
- Customer enrolls
- Welcome email sent immediately
- **Reminder email** notes urgent deadline
- Instructor sees enrollment immediately
- Can contact student if needed

**Student Completes Requirements Same Day:**
- Signs waiver online
- Photo already on file ✅
- Medical form completed ✅
- Shows eLearning certificate ✅
- All requirements met
- Instructor notified: "Student Ready"
- Good to go! 🎉

## Benefits

### For Customers
- ✅ Immediate confirmation
- ✅ Clear instructions
- ✅ Professional communication
- ✅ Know exactly what's needed
- ✅ Can complete requirements online

### For Instructors
- ✅ Always know who's enrolled
- ✅ See student readiness at a glance
- ✅ No surprises on course day
- ✅ Contact info readily available
- ✅ Professional roster to review

### For Staff
- ✅ Zero manual emails to send
- ✅ Automated reminders
- ✅ Easy to track progress
- ✅ Visual dashboard
- ✅ Everything logged automatically
- ✅ Can help students when asked

### For the Business
- ✅ Professional image
- ✅ Fewer no-shows
- ✅ Better prepared students
- ✅ Improved instructor satisfaction
- ✅ Reduced admin overhead
- ✅ Full audit trail
- ✅ Scalable process

## Configuration

### Setup Required (One-time)

1. **Run Migration 036:**
   ```bash
   php scripts/migrate.php
   ```

2. **Configure Email Settings** (`.env`):
   ```ini
   MAIL_FROM_ADDRESS=info@yourdiveshop.com
   MAIL_FROM_NAME=Your Dive Shop
   APP_URL=https://yourdiveshop.com
   ```

3. **Define Course Requirements:**
   - For each course, specify what students need
   - Set mandatory vs optional
   - Set due dates
   - Add instructions

4. **Customize Email Templates** (optional):
   - Edit files in `/app/Views/emails/`
   - Update branding, colors, wording

### Ongoing Management

**Staff Actions:**
- Enroll students (POS or courses interface)
- Verify and mark requirements complete
- Monitor roster before courses
- Help students with questions

**System Handles:**
- All emails automatically
- All notifications automatically
- All tracking automatically
- All updates automatically

## Next Steps

### Immediate
1. ✅ Run migration 036
2. ✅ Test enrollment workflow with test customer
3. ✅ Configure requirements for your courses
4. ✅ Train staff on roster view
5. ✅ Train instructors on roster access

### Short-term
- Review and customize email templates
- Set up requirements for all course types
- Configure reminder timing
- Test with real enrollments

### Future Enhancements
- Student portal for self-service
- SMS reminders
- Mobile app integration
- Direct e-learning provider integration
- Calendar sync
- Automated gear sizing collection

## Documentation

**Complete Documentation:**
- `/docs/COURSE_WORKFLOW_SYSTEM.md` - Detailed technical documentation
- This file - High-level overview

**Quick Reference:**
- Roster URL: `/courses/schedules/{id}/roster`
- API: `/api/courses/enrollments/{id}/requirements`
- Email templates: `/app/Views/emails/`
- Service: `/app/Services/Courses/CourseEnrollmentWorkflow.php`

## Support

If you have questions:
1. Review `/docs/COURSE_WORKFLOW_SYSTEM.md`
2. Check application logs: `/logs/app.log`
3. Review email delivery logs
4. Contact development team

---

## Summary

**Before:** Manual process - staff had to remember to email students, email instructors, track requirements on paper, chase down waivers, etc.

**After:** Fully automated - customer enrolls → everything happens automatically → instructor sees complete roster with all requirements tracked → everyone knows what's happening → streamlined operation.

**Result:** Professional, scalable, efficient course management that keeps everyone informed and prepared. 🚀🤿

