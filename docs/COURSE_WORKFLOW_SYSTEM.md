# Course Enrollment Workflow System

## Overview

The Nautilus Course Enrollment Workflow System provides a fully integrated, automated workflow for managing student enrollments in diving courses. When a student enrolls in a class (whether through POS or online), the system automatically handles all necessary communications, requirement tracking, and notifications.

## Key Features

### 🎯 Automated Workflows
- **Instant Enrollment Processing**: Automatically triggered when a student signs up
- **Welcome Emails**: Students receive immediate confirmation with course details
- **Instructor Notifications**: Instructors are notified of new enrollments
- **Requirement Reminders**: Automatic reminders for incomplete requirements
- **Roster Updates**: Real-time roster count updates

### ✅ Requirement Tracking
- **Student Requirements Checklist**: Track waivers, e-learning, photos, medical forms, etc.
- **Progress Monitoring**: View completion status for each student
- **Mandatory vs Optional**: Distinguish between required and optional items
- **Auto-verification**: Integrate with existing waiver system

### 👨‍🏫 Instructor Tools
- **Roster Management**: View complete class roster with student photos
- **Requirement Dashboard**: See which students are ready vs pending
- **Progress Tracking**: Monitor student completion percentages
- **In-app Notifications**: Get notified of important events

### 📧 Email Communications
- **Welcome Emails**: Professional welcome with course details and requirements list
- **Requirement Reminders**: Automated reminders for pending items
- **Instructor Updates**: Keep instructors informed of enrollments and completions
- **Customizable Templates**: Easy to modify email content

## System Components

### Database Tables

#### 1. `course_requirement_types`
Defines the types of requirements students must complete:
- Waivers (liability, equipment, minor release)
- E-learning modules
- Photos (student ID, passport)
- Medical (questionnaire, physician clearance)
- Certifications (previous cert cards)
- Documents (government ID, etc.)

#### 2. `course_requirements`
Links requirements to specific courses:
- Which requirements are needed for each course
- Mandatory vs optional designation
- Due dates relative to course start
- Auto-reminder settings
- Custom instructions

#### 3. `enrollment_requirements`
Tracks individual student progress:
- Status (pending, in_progress, completed, waived)
- Completion dates
- File uploads (waivers, photos, documents)
- E-learning completion certificates
- Verification by staff
- Reminder history

#### 4. `elearning_modules`
Catalog of online learning modules:
- PADI eLearning courses
- SSI Online courses
- Other online training
- Estimated completion time
- External URLs

#### 5. `instructor_notifications`
Dedicated instructor notification log:
- New enrollments
- Requirement completions
- Course start reminders
- Roster updates

## Workflow Process

### When a Student Enrolls

```
1. Enrollment Created
   ↓
2. Create Requirement Checklist
   - Based on course requirements
   - Initialize all to "pending"
   ↓
3. Update Roster Count
   - Increment current_enrollment
   ↓
4. Send Welcome Email
   - Course details
   - Instructor info
   - Requirements list
   ↓
5. Notify Instructor
   - In-app notification
   - Email notification
   - Add to instructor_notifications table
   ↓
6. Send Requirement Reminders
   - List of pending requirements
   - Instructions for completion
   ↓
7. Queue Waiver Email
   - If training waiver required
   - Generate unique signing link
   ↓
8. System Notifications
   - Notify admin users
   - Create audit trail
```

### When Requirements Are Completed

```
1. Requirement Marked Complete
   ↓
2. Update enrollment_requirements
   - Set status to "completed"
   - Record completion date
   - Store any uploaded files
   ↓
3. Check All Requirements
   - If all mandatory items complete:
     ↓
4. Notify Instructor
   - "Student Ready" notification
   - Email alert
   - Update instructor_notifications
```

## Integration Points

### Point of Sale (POS)
```php
// When selling a course in POS
$courseService = new CourseService();
$enrollmentId = $courseService->enrollStudent(
    $scheduleId,
    $customerId,
    $amountPaid  // Payment amount
);

// Workflow automatically triggers:
// - Welcome email sent
// - Instructor notified
// - Requirements created
// - Reminders queued
```

### Online Enrollment
```php
// When customer enrolls online
$courseService = new CourseService();
$enrollmentId = $courseService->enrollStudent(
    $scheduleId,
    $customerId
);

// Same automatic workflow
```

### Manual Enrollment
Staff can enroll students through the courses interface, and all automation still applies.

## Viewing the Roster

### Instructor View
**URL**: `/courses/schedules/{schedule_id}/roster`

**Features**:
- Student photos
- Contact information
- Requirement completion percentage
- Progress bars (visual)
- Status badges (Ready, In Progress, Pending)
- Quick access to requirement details
- Print-friendly format

**Requirement Details Modal**:
- Click "View Requirements" button
- See complete checklist
- Mark items as complete
- View completion dates
- Add notes

### Student Information Shown
- Name and photo
- Email and phone
- Enrollment date
- Overall progress (X/Y complete)
- Status (Ready vs Pending)
- Quick actions

## Requirement Management

### Adding Requirements to a Course

```sql
-- Example: Add waiver requirement to Open Water course
INSERT INTO course_requirements
(course_id, requirement_type_id, is_mandatory, due_before_start_days, instructions)
VALUES
(1, -- course_id for Open Water Diver
 1, -- requirement_type_id for Liability Waiver
 TRUE, -- mandatory
 3, -- must be completed 3 days before start
 'Please review and sign the liability waiver at the dive shop or online');
```

### Marking Requirements Complete

```php
$workflow = new CourseEnrollmentWorkflow();
$workflow->markRequirementComplete(
    $enrollmentRequirementId,
    [
        'waiver_id' => 123, // if it's a waiver
        'photo_path' => '/uploads/photos/student.jpg', // if it's a photo
        'elearning_completion_date' => '2025-11-01', // if e-learning
        'notes' => 'Verified in person'
    ]
);
```

### Checking Roster with Requirements

```php
$workflow = new CourseEnrollmentWorkflow();
$roster = $workflow->getRosterWithRequirements($scheduleId);

foreach ($roster as $student) {
    echo $student['student_name'];
    echo "Progress: {$student['completion_percentage']}%";
    echo "Status: {$student['enrollment_status']}";

    foreach ($student['requirements'] as $req) {
        echo "{$req['requirement_name']}: ";
        echo $req['is_completed'] ? 'Complete' : 'Pending';
    }
}
```

## Email Templates

### Location
All email templates are in: `/app/Views/emails/`

### Available Templates
1. **course_enrollment_welcome.php**
   - Sent immediately upon enrollment
   - Includes course details, requirements list
   - Professional design with branding

2. **instructor_new_enrollment.php**
   - Sent to instructor when student enrolls
   - Includes student contact info
   - Link to view roster

3. **course_requirements_reminder.php**
   - Sent to students with pending requirements
   - Shows what's incomplete
   - Urgent deadline notice

### Customizing Templates
Templates use standard PHP with HTML email formatting. Modify as needed:
- Change colors in inline styles
- Update wording
- Add/remove sections
- Include shop logo

## Configuration

### Email Settings
Configure in `.env`:
```ini
MAIL_FROM_ADDRESS=info@nautilus.local
MAIL_FROM_NAME=Nautilus Dive Shop
APP_URL=https://nautilus.local
```

### Requirement Auto-Send Settings
Configured per requirement type in `course_requirements` table:
- `auto_send_reminder`: Enable/disable automatic reminders
- `reminder_days_before`: Days before course to send reminder
- `due_before_start_days`: How many days before course it must be done

## API Endpoints

### Get Enrollment Requirements
```
GET /api/courses/enrollments/{enrollmentId}/requirements
```

Returns:
```json
{
    "student_name": "John Doe",
    "course_name": "Open Water Diver",
    "requirements": [
        {
            "id": 1,
            "requirement_name": "Liability Waiver",
            "is_mandatory": true,
            "is_completed": false,
            "status": "pending"
        }
    ]
}
```

### Mark Requirement Complete
```
POST /api/courses/requirements/{requirementId}/complete
```

## Instructor Notifications

Instructors receive notifications for:
- **New Enrollment**: When a student signs up
- **Requirement Complete**: When a student completes all requirements
- **Course Start Reminder**: X days before course starts
- **Roster Updates**: When important changes occur

### Viewing Notifications
- In-app: Bell icon in navigation
- Email: Sent to instructor's email address
- Dashboard: Instructor dashboard widget (if implemented)

## Reports

### Available Reports
1. **Enrollment Status Report**
   - Shows all students by requirement completion
   - Filter by pending/ready/complete

2. **Requirement Completion Report**
   - Which requirements are most commonly delayed
   - Completion rates by requirement type

3. **Instructor Workload Report**
   - Upcoming classes
   - Total students
   - Students ready vs pending

## Best Practices

### For Staff
1. **Set Up Requirements Early**: Define course requirements before scheduling
2. **Monitor Roster**: Check roster 1 week before course start
3. **Follow Up**: Contact students with pending requirements
4. **Verify Documents**: Mark requirements complete only after verification
5. **Keep Templates Updated**: Review email templates quarterly

### For Instructors
1. **Check Roster Early**: Review roster at least 3 days before course
2. **Contact Unprepared Students**: Reach out to students with pending items
3. **Verify Completion**: Double-check that "ready" students truly have everything
4. **Provide Feedback**: Let staff know if requirements need adjustment

### For Students (via communication)
1. **Complete Requirements Early**: Don't wait until last minute
2. **Upload Clear Photos**: Ensure photos meet certification standards
3. **Check Email**: Watch for reminder emails
4. **Ask Questions**: Contact shop if unclear about any requirement

## Troubleshooting

### Student Didn't Receive Welcome Email
1. Check email in `course_enrollments` table
2. Verify email service is configured (check logs)
3. Check spam folder
4. Resend manually if needed

### Requirement Stuck as Pending
1. Verify it was actually marked complete in database
2. Check `enrollment_requirements` table
3. Ensure staff user had permission to mark complete
4. Try marking complete again

### Instructor Not Getting Notifications
1. Verify instructor_id is set in course_schedules
2. Check instructor's email address
3. Review instructor_notifications table for record
4. Check NotificationService logs

### Roster Shows Wrong Count
1. Roster count based on `course_schedules.current_enrollment`
2. Should auto-update when enrollStudent() is called
3. Can manually fix:
   ```sql
   UPDATE course_schedules
   SET current_enrollment = (
       SELECT COUNT(*) FROM course_enrollments
       WHERE schedule_id = {schedule_id}
       AND status IN ('enrolled', 'in_progress')
   )
   WHERE id = {schedule_id};
   ```

## Migration

### Running the Migration
```bash
php scripts/migrate.php
```

This will create:
- 6 new tables
- Default requirement types (10 types)
- Default e-learning modules (5 modules)
- Proper indexes and foreign keys

### Existing Data
Migration is non-destructive and won't affect existing enrollments. However, you'll need to:
1. Define requirements for existing courses
2. Manually create enrollment_requirements for active enrollments (if desired)

## Future Enhancements

Potential additions:
- **SMS Reminders**: Text message reminders for pending requirements
- **Student Portal**: Self-service requirement upload
- **Mobile App Integration**: Push notifications
- **Automated E-learning Integration**: Direct API integration with PADI/SSI
- **Calendar Sync**: Sync course dates to student calendars
- **Pre-course Quizzes**: Online knowledge checks before course
- **Equipment Sizing**: Collect gear sizes during enrollment

## Support

For questions or issues:
1. Review this documentation
2. Check application logs: `/logs/app.log`
3. Review email logs for delivery issues
4. Contact development team

## Summary

The Course Enrollment Workflow System transforms the enrollment process from manual to fully automated. Students receive immediate confirmation and clear instructions, instructors stay informed, and staff can easily monitor progress. Everything flows together seamlessly, improving the experience for everyone involved.

**Key Benefits**:
- ✅ Zero manual email sending required
- ✅ Students know exactly what to do
- ✅ Instructors see complete roster with readiness status
- ✅ Staff can track requirements visually
- ✅ Automated reminders prevent last-minute issues
- ✅ Professional, branded communications
- ✅ Full audit trail of all activities
