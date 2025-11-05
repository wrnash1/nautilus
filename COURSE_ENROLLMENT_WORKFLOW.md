# Course Enrollment Workflow - Implementation Guide

## Overview

This document describes the complete course enrollment workflow that links together the POS system, course management, and instructor roster views.

## Workflow

### 1. **Student Purchases Course at POS**

When a salesperson rings up a course at the point of sale:

1. **Select Course** - Courses are loaded into the POS system automatically
2. **Select Schedule** - A modal appears showing available class schedules with:
   - Date range (start - end)
   - Time (start - end)
   - Instructor name
   - Location
   - Available spots (e.g., "4 spots left")
   - Current enrollment count

3. **Complete Purchase** - Once payment is processed:
   - Student is automatically enrolled in the selected schedule
   - Enrollment record is created in `course_enrollments` table
   - Schedule's `current_enrollment` count is incremented
   - Transaction is linked to the enrollment

### 2. **Instructor Views Class Roster**

Instructors can view their course rosters:

**Navigation**: Courses > Schedules > [Select Schedule]

**Roster Shows**:
- Student name and contact information
- Emergency contact details
- Current certifications
- Enrollment date
- Payment status (paid, partial, pending)
- Enrollment status (enrolled, in_progress, completed, dropped)
- Final grade (when completed)
- Certification number (when completed)

### 3. **Staff Transfers Student to Different Class**

If a student needs to move to a different schedule:

1. **View Current Schedule** - Go to the student's current class roster
2. **Click Transfer Button** - Next to student's name
3. **Select New Schedule** - Choose from available schedules for the same course
4. **Enter Reason** - Required field (e.g., "Schedule conflict", "Student request")
5. **Confirm Transfer** - System:
   - Removes student from old schedule (decrements enrollment count)
   - Adds student to new schedule (increments enrollment count)
   - Logs transfer in enrollment notes with timestamp and staff ID
   - Preserves payment information

**Business Rules**:
- Can only transfer to schedules of the SAME course
- New schedule must have available spots
- Transfer history is tracked in enrollment notes

## Database Schema

### Tables Involved

```sql
courses
├── id
├── course_code (e.g., "OW-001")
├── name (e.g., "Open Water Diver")
├── price
└── max_students

course_schedules
├── id
├── course_id (FK to courses)
├── instructor_id (FK to users)
├── start_date
├── end_date
├── start_time
├── end_time
├── location
├── max_students
├── current_enrollment
└── status (scheduled, in_progress, completed, cancelled)

course_enrollments
├── id
├── schedule_id (FK to course_schedules)
├── customer_id (FK to customers)
├── enrollment_date
├── status (enrolled, in_progress, completed, dropped, failed)
├── payment_status (pending, partial, paid, refunded)
├── amount_paid
├── completion_date
├── certification_number
├── final_grade
└── notes (includes transfer history)

transactions
└── Links to enrollment via transaction_items

transaction_items
├── transaction_id
├── schedule_id (NEW - links course purchase to schedule)
└── ... product info
```

## API Endpoints

### Get Available Schedules
```http
GET /store/api/courses/{courseId}/schedules
```

**Response**:
```json
[
  {
    "id": 15,
    "course_id": 3,
    "course_name": "Open Water Diver",
    "start_date": "2025-11-15",
    "end_date": "2025-11-17",
    "start_time": "09:00:00",
    "end_time": "17:00:00",
    "instructor_name": "John Smith",
    "location": "Main Pool",
    "max_students": 6,
    "current_enrollment": 2,
    "available_spots": 4
  }
]
```

### Transfer Student
```http
POST /store/courses/transfer-student
Content-Type: application/x-www-form-urlencoded

enrollment_id=45&new_schedule_id=16&reason=Schedule+conflict
```

**Response**:
```json
{
  "success": true,
  "message": "Student transferred successfully"
}
```

## Services Implemented

### EnrollmentService

**File**: `/app/Services/Courses/EnrollmentService.php`

**Key Methods**:

```php
// Enroll customer from POS transaction
enrollFromTransaction(int $customerId, int $scheduleId, float $amountPaid, ?int $transactionId): int

// Transfer student to different schedule
transferToSchedule(int $enrollmentId, int $newScheduleId, string $reason, int $staffId): bool

// Get roster for instructor
getScheduleRoster(int $scheduleId): array

// Get available schedules for course
getAvailableSchedules(int $courseId): array

// Get customer's course history
getCustomerCourseHistory(int $customerId): array

// Update enrollment status
updateEnrollmentStatus(int $enrollmentId, string $status, ?string $finalGrade, ?string $certificationNumber): bool
```

### Updated TransactionService

**File**: `/app/Services/POS/TransactionService.php`

**New Feature**: Automatically processes course enrollments when transaction includes courses with schedule_id

```php
private function processCourseEnrollments(int $transactionId, int $customerId, array $items): void
```

## Frontend Components

### POS Course Selection

**File**: `/public/assets/js/pos-course-enrollment.js`

**Functions**:
- `showCourseScheduleModal(courseId, courseName, coursePrice)` - Shows schedule selection
- `addCourseToCart(courseId, courseName, coursePrice, scheduleId)` - Adds course with schedule to cart

### Course Roster View

**File**: `/app/Views/courses/schedules/show.php` (to be updated)

**Features**:
- Full student roster with contact info
- Emergency contacts for safety
- Current certifications
- Transfer button for each student
- Payment status indicators
- Enrollment status badges

## Usage Examples

### Example 1: Customer Buys Open Water Course

**At POS**:
1. Cashier selects "Open Water Diver" course
2. Modal shows 3 available schedules:
   - Nov 15-17 with Instructor John (4 spots left)
   - Nov 22-24 with Instructor Sarah (2 spots left)
   - Dec 6-8 with Instructor Mike (6 spots left)
3. Customer prefers weekend with John
4. Cashier selects Nov 15-17 schedule
5. Course added to cart for $399
6. Customer pays $399
7. **System automatically**:
   - Creates transaction record
   - Creates enrollment record linking customer to Nov 15-17 schedule
   - Sets payment_status to 'paid'
   - Increments Nov 15-17 schedule enrollment count

**Instructor John can now see**:
- New student in his Nov 15-17 roster
- Student's contact information
- Student's emergency contact
- Payment confirmed

### Example 2: Student Needs to Transfer

**Scenario**: Student Sarah Jones enrolled in Nov 15-17 but has a conflict

**Staff Action**:
1. Go to Courses > Schedules > Nov 15-17 Schedule
2. Find Sarah Jones in roster
3. Click "Transfer" button
4. Select new schedule: Dec 6-8
5. Enter reason: "Work conflict - student requested transfer"
6. Confirm transfer

**System Actions**:
- Removes Sarah from Nov 15-17 (enrollment drops from 3 to 2)
- Adds Sarah to Dec 6-8 (enrollment increases from 0 to 1)
- Adds note to enrollment: "Transferred from schedule #15 on 2025-11-05 by staff #7. Reason: Work conflict - student requested transfer"
- Preserves $399 payment information

**Result**:
- Instructor John sees Sarah removed from his roster
- Instructor Mike now sees Sarah in Dec 6-8 roster
- Sarah's payment status remains 'paid'
- Full transfer history is logged

### Example 3: Instructor Reviews Roster Before Class

**Day Before Class**:
1. Instructor John logs in
2. Goes to Courses > Schedules
3. Clicks on Nov 15-17 "Open Water Diver" schedule
4. Reviews roster showing:
   - 4 students enrolled
   - All payment status: PAID
   - Emergency contacts for each student
   - Current certification levels
5. Notes one student has "Scuba Diver" cert already
6. Prepares class materials accordingly

## Implementation Status

### ✅ Completed

- [x] EnrollmentService with all core methods
- [x] Updated TransactionService to handle course enrollments
- [x] API endpoint for fetching course schedules
- [x] POS JavaScript for schedule selection modal
- [x] Transfer student functionality
- [x] Route for student transfers
- [x] Database schema supports complete workflow

### 📝 Next Steps (To Complete)

1. **Update Course Schedule View** (`/app/Views/courses/schedules/show.php`)
   - Add roster table with all student details
   - Add transfer button for each student
   - Add transfer modal
   - Show enrollment statistics

2. **Update POS View** (`/app/Views/pos/index.php`)
   - Include pos-course-enrollment.js script
   - Wire up course tiles to trigger schedule modal

3. **Test Complete Workflow**
   - Test course purchase at POS
   - Verify enrollment creation
   - Test roster display
   - Test student transfer
   - Verify enrollment counts update correctly

4. **Add Notifications** (Optional)
   - Email confirmation to student when enrolled
   - Email to instructor when student joins roster
   - Email to student when transferred

## Benefits

### For Sales Staff
- Seamless course sales at POS
- Clear schedule availability
- No separate enrollment process needed

### For Instructors
- Real-time roster visibility
- Student contact information readily available
- Emergency contacts for safety
- Certification history for planning

### For Management
- Accurate enrollment tracking
- Transfer history for reporting
- Payment status monitoring
- Class capacity management

### For Students
- Immediate enrollment upon purchase
- Flexible schedule changes
- All information in one system

## Configuration

### Tax Settings

Courses are typically taxable. Ensure tax rates are configured in:
**Settings > Tax Configuration**

### Permissions Required

- `pos.view` - View POS and available courses
- `pos.create` - Process course sales
- `courses.view` - View course rosters
- `courses.edit` - Transfer students between schedules

### Course Setup Checklist

Before courses can be sold:
1. Create course in system (Courses > Create)
2. Set price and max students
3. Create schedule with dates and instructor
4. Ensure instructor has necessary permissions
5. Course will automatically appear in POS

---

**Document Version**: 1.0
**Last Updated**: November 5, 2025
**Status**: Implementation In Progress (90% complete)
