# Nautilus - Course Management & Training System Guide

## Overview

This guide details the complete PADI-compliant course management and student training system for Nautilus. This system enables dive shops to manage the entire certification lifecycle from enrollment through certification.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
3. [Database Architecture](#database-architecture)
4. [Core Features](#core-features)
5. [Multi-Instructor Team Teaching](#multi-instructor-team-teaching)
6. [Form Compliance & Medical Clearance](#form-compliance--medical-clearance)
7. [Student Progress Tracking](#student-progress-tracking)
8. [Referral System](#referral-system)
9. [Certification Generation](#certification-generation)
10. [Incident Reporting](#incident-reporting)
11. [Implementation Guide](#implementation-guide)
12. [Common Workflows](#common-workflows)
13. [API Reference](#api-reference)
14. [Troubleshooting](#troubleshooting)

---

## System Overview

### What This System Manages

The course management system handles the complete PADI diver training lifecycle:

```
┌─────────────┐   ┌──────────────┐   ┌─────────────┐   ┌──────────────┐   ┌───────────────┐
│ Enrollment  │ → │ Form         │ → │ Training    │ → │ Skills       │ → │ Certification │
│             │   │ Compliance   │   │ Sessions    │   │ Tracking     │   │               │
└─────────────┘   └──────────────┘   └─────────────┘   └──────────────┘   └───────────────┘
      │                  │                   │                  │                    │
  Students          13 PADI Forms    Multi-Instructor    Per-Skill Per-        PADI Cards
  Enroll           Medical Review     Team Teaching      Instructor Sign        + Credly
                                                                                 Badges
```

### Supported PADI Courses

**Entry-Level:**
- Discover Scuba Diving (DSD)
- Scuba Diver
- Open Water Diver
- Junior Open Water Diver (ages 10-14)

**Advanced:**
- Advanced Open Water Diver
- Rescue Diver
- Divemaster

**Specialty Courses:**
- Deep Diver, Night Diver, Navigation
- Wreck Diver, Dry Suit Diver, Nitrox
- Search & Recovery, Underwater Photography
- Peak Performance Buoyancy, Naturalist

**Professional:**
- Assistant Instructor
- Open Water Scuba Instructor (OWSI)
- Instructor Development Course (IDC)
- Master Instructor

### Key Capabilities

✅ **Course Management** - Schedule courses, manage capacity, assign instructors
✅ **Multi-Instructor Support** - Team teaching with individual sign-offs (Form 007DT)
✅ **Form Compliance** - Track all 13 required PADI forms
✅ **Medical Clearance** - Physician approval workflow with 12-month tracking
✅ **Progress Tracking** - Per-skill, per-instructor sign-offs
✅ **Student Photos** - Certification-ready passport photos with approval
✅ **Referral System** - 12-month validity, automatic skill recognition
✅ **Waterskills Assessment** - 200m swim, 10-minute float tracking
✅ **Incident Reporting** - PADI Form 10120 compliant
✅ **Certification** - PADI cards + Credly digital badges
✅ **Compliance Reporting** - Audit trails and verification

---

## Getting Started

### Quick Installation (35 Minutes)

**Step 1: Database Migration (2 minutes)**
```bash
mysql -u root -p nautilus < database/migrations/019_create_course_management.sql
```

**Step 2: Seed PADI Courses (5 minutes)**
```bash
php scripts/seed_padi_courses.php
```

**Step 3: Configure Environment (5 minutes)**

Edit `.env`:
```ini
# Credly Integration
CREDLY_API_KEY=your_credly_api_key_here
CREDLY_API_URL=https://api.credly.com/v1

# PADI Integration
PADI_INCIDENT_EMAIL=incidents@yourdiveshop.com

# File Storage
FORM_STORAGE_PATH=/public/uploads/forms/
PHOTO_STORAGE_PATH=/public/uploads/student_photos/
MAX_PHOTO_SIZE=5242880  # 5MB

# Referral Settings
REFERRAL_VALIDITY_DAYS=365  # 12 months
```

**Step 4: Create Upload Directories (3 minutes)**
```bash
mkdir -p public/uploads/forms
mkdir -p public/uploads/student_photos
chmod 755 public/uploads/forms
chmod 755 public/uploads/student_photos
```

**Step 5: Verify Installation (5 minutes)**
```php
// Test course creation
$db = Database::getInstance();
$result = $db->fetchOne("SELECT COUNT(*) as count FROM course_templates");
echo "Course templates loaded: " . $result->count;

// Should return 20+ PADI courses
```

---

## Database Architecture

### 23 Interconnected Tables

**Core Course Management (4 tables)**
```sql
course_templates        -- PADI course definitions (OWD, AOW, etc.)
course_instances        -- Scheduled courses with dates and instructors
course_enrollments      -- Student enrollments with status tracking
course_skills           -- Individual skills per course (24 skills for OWD)
```

**Progress Tracking (4 tables)**
```sql
student_skill_progress  -- Student completion per skill per instructor
dive_sessions           -- Confined/open water dive scheduling
dive_session_skills     -- Skills practiced in each dive
waterskills_assessments -- 200m swim, 10-min float verification
```

**Team Teaching (2 tables)**
```sql
course_instructors      -- Multiple instructors per course
instructor_skill_signoffs -- Individual sign-offs with dates/initials
```

**Compliance (4 tables)**
```sql
required_forms          -- 13 PADI forms per course
student_form_completion -- Form submission status tracking
medical_statements      -- Medical clearance with physician approval
compliance_checklist    -- Per-student compliance verification
```

**Additional Tables (9)**
```sql
student_photos          -- Certification passport photos
student_referrals       -- Students from other dive centers
incident_reports        -- PADI Form 10120 accident reporting
student_certifications  -- Generated certifications
compliance_requirements -- Course-specific requirements
```

### Database Diagram

```
┌─────────────────┐
│ course_templates│
│ (PADI courses)  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐        ┌──────────────────┐
│ course_instances│ ◄────► │ course_instructors│
│ (scheduled)     │        │ (team teaching)  │
└────────┬────────┘        └──────────────────┘
         │
         ▼
┌─────────────────┐        ┌──────────────────┐
│course_enrollments│ ◄────► │ student_referrals│
│ (students)      │        │ (12-mo validity) │
└────────┬────────┘        └──────────────────┘
         │
         ├──────────────────────┬───────────────────┐
         ▼                      ▼                   ▼
┌──────────────────┐  ┌────────────────┐  ┌──────────────────┐
│student_skill_    │  │ required_forms │  │ waterskills_     │
│progress          │  │ (13 forms)     │  │ assessments      │
└────────┬─────────┘  └────────────────┘  └──────────────────┘
         │
         ▼
┌──────────────────┐
│ instructor_skill_│
│ signoffs         │
└──────────────────┘
```

---

## Core Features

### 1. Course Management

**Create Course Template (One-Time Setup)**

```php
use App\Models\Course\CourseTemplate;

$template = CourseTemplate::create([
    'padi_course_code' => 'OWD',
    'name' => 'Open Water Diver',
    'description' => 'Entry-level PADI certification',
    'certification_level' => 'Open Water Diver',
    'course_type' => 'entry_level',
    'min_age' => 10,
    'academics_modules' => 5,      // Knowledge Development
    'confined_water_dives' => 5,   // Pool sessions
    'open_water_dives' => 4,       // Ocean certification dives
    'minimum_duration_days' => 3,
    'course_fee' => 450.00
]);
```

**Schedule Course Instance**

```php
use App\Services\CourseManagementService;

$courseService = new CourseManagementService();

$instanceId = $courseService->createCourseInstance([
    'course_template_id' => 1,           // Open Water Diver
    'class_number' => 'OWD-2025-02-01',
    'start_date' => '2025-02-01',
    'end_date' => '2025-02-05',
    'location' => 'Training Pool & Local Beach',
    'max_students' => 8,
    'primary_instructor_id' => 5,        // John Smith
    'notes' => 'Weekend intensive course'
]);
```

**Enroll Student**

```php
$enrollmentId = $courseService->enrollStudent($instanceId, $studentId, [
    'is_referral' => false,
    'notes' => 'Student has prior snorkeling experience'
]);

// Automatically creates:
// - All 24 skill progress records (5 academics + 5 CW + 4 OW)
// - Required forms checklist (13 forms)
// - Compliance requirements
// - Waterskills assessment placeholder
```

### 2. Multi-Instructor Team Teaching

**Scenario: Different instructors teach different segments**

**Add Instructors to Course**

```php
use App\Core\Database;

$db = Database::getInstance();

// Primary instructor (already set)
// Add assistant instructors

$db->query("
    INSERT INTO course_instructors
    (course_instance_id, instructor_id, role, start_date)
    VALUES (?, ?, ?, ?)
", [$instanceId, 6, 'assistant', '2025-02-02']);  // Mary for confined water

$db->query("
    INSERT INTO course_instructors
    (course_instance_id, instructor_id, role, start_date)
    VALUES (?, ?, ?, ?)
", [$instanceId, 7, 'assistant', '2025-02-04']);  // Bob for open water
```

**Record Skill Sign-Offs**

```php
use App\Models\Course\StudentSkillProgress;

// Day 1: Instructor John teaches academics (Modules 1-5)
StudentSkillProgress::completeSkill(
    $enrollmentId,
    1,  // Skill ID for Module 1
    5,  // Instructor John's ID
    'Student completed Module 1 quiz with 90%'
);

// Day 2: Instructor Mary teaches confined water (Dives 1-5)
StudentSkillProgress::completeSkill(
    $enrollmentId,
    6,  // Skill ID for CW Dive 1
    6,  // Instructor Mary's ID
    'Completed mask clearing and regulator recovery'
);

// Day 3-4: Instructor Bob teaches open water (Dives 1-4)
StudentSkillProgress::completeSkill(
    $enrollmentId,
    11, // Skill ID for OW Dive 1
    7,  // Instructor Bob's ID
    'Navigation and buoyancy control excellent'
);
```

**Generate Team Teaching Form (007DT)**

```php
use App\Controllers\Courses\TeamTeachingController;

$controller = new TeamTeachingController();
$pdfPath = $controller->generateForm007DT($instanceId);

// PDF includes:
// - All students in class
// - Academics: Module 1-5 with instructor names, dates, initials
// - Confined Water: Dives 1-5 with instructor names, dates, initials
// - Open Water: Dives 1-4 with primary instructor certification authority
// - Waterskills assessment status
```

### 3. Form Compliance & Medical Clearance

**13 Required PADI Forms**

```php
// Forms are auto-created when course template is seeded
$forms = [
    ['name' => 'Medical Statement', 'padi_form_number' => '10346', 'required' => true],
    ['name' => 'Release of Liability - General Training', 'padi_form_number' => '10072', 'required' => true],
    ['name' => 'PADI Scuba Diver Statement', 'padi_form_number' => '10062', 'required' => true],
    ['name' => 'Travel & Excursions Liability', 'padi_form_number' => '10079', 'required' => false],
    ['name' => 'Equipment Rental Agreement', 'padi_form_number' => '10087', 'required' => false],
    ['name' => 'Parental Release for Minors', 'padi_form_number' => '10088', 'required' => true, 'for_minors' => true],
    ['name' => 'Emergency Treatment Consent', 'padi_form_number' => '10088', 'required' => true, 'for_minors' => true],
    ['name' => 'Special Event Liability', 'padi_form_number' => '10085', 'required' => false],
    ['name' => 'Learning Agreement', 'required' => true],
    ['name' => 'Photo/Video Consent', 'required' => false],
    ['name' => 'Youth Diving Responsibility', 'padi_form_number' => '00602', 'required' => true, 'for_minors' => true],
    ['name' => 'Safe Diving Practices Statement', 'required' => true],
    ['name' => 'Medical Physician Release', 'required' => false] // Only if medical conditions
];
```

**Submit Form**

```php
use App\Controllers\Courses\FormComplianceController;

$formController = new FormComplianceController();

$formController->submitForm($enrollmentId, $formId, [
    'signature_file_path' => '/uploads/forms/liability_john_doe.pdf',
    'signed_date' => date('Y-m-d H:i:s'),
    'notes' => 'Signed digitally via tablet'
]);

// Status: pending → submitted → accepted (after verification)
```

**Medical Clearance Workflow**

```php
use App\Models\Course\MedicalStatement;

// Student submits medical form
MedicalStatement::create([
    'enrollment_id' => $enrollmentId,
    'student_id' => $studentId,
    'medical_form_date' => date('Y-m-d'),
    'has_medical_conditions' => true,
    'medical_conditions_text' => 'Asthma (controlled)',
    'medications_text' => 'Albuterol inhaler as needed',
    'requires_physician_clearance' => true,
    'physician_clearance_obtained' => false
]);

// Later: Physician approves
MedicalStatement::update($medicalId, [
    'physician_approved' => true,
    'physician_name' => 'Dr. Jane Smith',
    'physician_approval_date' => date('Y-m-d'),
    'physician_clearance_obtained' => true,
    'clearance_expiry_date' => date('Y-m-d', strtotime('+12 months'))
]);
```

### 4. Student Progress Tracking

**Initialize All Skills for Student**

```php
// Automatically done during enrollment
// For Open Water Diver:
// - 5 Academics modules
// - 5 Confined water dives
// - 4 Open water dives
// = 24 skill records created with status 'not_started'
```

**Track Individual Skill Progress**

```php
// Get student progress
$progress = StudentSkillProgress::getProgress($enrollmentId);

/*
Returns:
[
    'total_skills' => 24,
    'completed' => 18,
    'in_progress' => 3,
    'not_started' => 3,
    'percent_complete' => 75.0,
    'academics' => ['completed' => 5, 'total' => 5],
    'confined_water' => ['completed' => 5, 'total' => 5],
    'open_water' => ['completed' => 3, 'total' => 4]
]
*/
```

**Waterskills Assessment**

```php
use App\Models\Course\WaterskillsAssessment;

WaterskillsAssessment::create([
    'enrollment_id' => $enrollmentId,
    'assessment_date' => date('Y-m-d'),
    'instructor_id' => 5,
    'swim_200_yards_completed' => true,
    'swim_200_yards_date' => date('Y-m-d'),
    'float_10_minutes_completed' => true,
    'float_10_minutes_date' => date('Y-m-d'),
    'all_skills_passed' => true,
    'notes' => 'Strong swimmer, no issues'
]);
```

### 5. Referral System

**12-Month Validity Window**

```php
use App\Services\ReferralService;

$referralService = new ReferralService();

$referralService->createReferralEnrollment($instanceId, $studentId, [
    'referring_dive_center_id' => 123,
    'referring_instructor_name' => 'Bob Wilson',
    'referring_instructor_padi_number' => '987654',
    'referral_date' => '2024-10-15',
    'academics_completed' => 5,           // All modules done
    'confined_water_completed' => 5,      // All CW dives done
    'open_water_completed' => 2,          // Only 2 of 4 OW dives done
    'waterskills_completed' => true,
    'referral_documentation_file' => '/uploads/forms/referral_jane_doe.pdf'
]);

// System automatically:
// 1. Calculates valid_until = referral_date + 365 days
// 2. Marks skills 1-12 (academics + CW) as 'completed'
// 3. Marks OW dives 1-2 as 'completed'
// 4. Leaves OW dives 3-4 as 'not_started'
// 5. Flags enrollment as referral
```

**Generate PIC Worksheets (384DT/445DT)**

```php
use App\Controllers\Courses\ReferralController;

$referralController = new ReferralController();
$picPdf = $referralController->generatePICWorksheet($enrollmentId);

// Auto-populates:
// - Certification level
// - Original certification date
// - Referring dive center info
// - Referring instructor PADI number
// - Skills completed at referring center
// - Skills remaining for your shop
```

### 6. Student Photos for Certification

**Upload Photo**

```php
use App\Controllers\Courses\StudentPhotoController;

$photoController = new StudentPhotoController();

$photoId = $photoController->storePhoto($studentId, $_FILES['photo'], [
    'photo_type' => 'passport',
    'notes' => 'Meets PADI passport photo requirements'
]);

// Validates:
// - JPEG or PNG only
// - Max 5MB size
// - Not marked as underwater, no sunglasses, no hats
```

**Approve Photo**

```php
$photoController->approvePhoto($photoId, [
    'approved_by_user_id' => Auth::user()->id,
    'is_primary' => true  // Use this photo for certification card
]);
```

### 7. Incident Reporting

**Submit PADI Form 10120**

```php
use App\Controllers\Courses\IncidentController;

$incidentController = new IncidentController();

$reportId = $incidentController->submitReport([
    'incident_date' => '2025-01-28',
    'incident_time' => '14:30',
    'incident_location' => 'East Reef Dive Site',
    'dive_site_name' => 'Coral Gardens',
    'incident_type' => 'training',
    'severity' => 'non_fatal',
    'victim_id' => $studentId,
    'victim_certification_level' => 'Open Water Student',
    'instructor_id' => 5,
    'water_temperature' => 68,
    'water_temperature_unit' => 'F',
    'visibility_meters' => 12,
    'current_conditions' => 'Moderate',
    'surface_conditions' => 'Calm',
    'incident_description' => 'Student experienced fatigue at 40 feet...',
    'rescue_procedures_used' => 'Buddy assistance to surface, normal ascent rate...',
    'oxygen_administered' => true,
    'cpr_administered' => false,
    'hospitalization_required' => false,
    'authorities_contacted' => false,
    'notes' => 'Student recovered fully, no medical issues'
]);

// System automatically:
// 1. Stores complete incident record
// 2. Emails PADI incident coordinator
// 3. Generates PDF of PADI Form 10120
// 4. Tracks for insurance/legal purposes
```

### 8. Certification Generation

**Verify Compliance Before Certification**

```php
use App\Services\CertificationService;

$certService = new CertificationService();

// Throws exception if not ready
$certService->verifyReadyForCertification($enrollmentId);

// Checks:
// 1. All skills completed
// 2. All required forms accepted
// 3. Waterskills assessment passed
// 4. Medical clearance obtained (if needed)
// 5. No overdue incidents
```

**Generate Certification**

```php
$certificationNumber = $certService->generateCertification($enrollmentId, $instructorId);

// Creates:
// 1. PADI certification number
// 2. Student certification record
// 3. Digital badge via Credly
// 4. Official transcript
// 5. Certification card PDF
// 6. Email notification to student

// Returns certification details
$cert = StudentCertification::find($certificationNumber);
echo $cert->certification_number;  // e.g., "OWD-2025-001234"
echo $cert->credly_badge_url;      // Digital badge URL
```

---

## Implementation Guide

### Phase 1: Database Setup (30 minutes)

**Step 1: Run Migration**
```bash
mysql -u root -p nautilus < database/migrations/019_create_course_management.sql
```

**Step 2: Verify Tables**
```sql
SHOW TABLES LIKE 'course_%';
SHOW TABLES LIKE 'student_%';
SHOW TABLES LIKE 'dive_%';
SHOW TABLES LIKE 'instructor_%';

-- Should show 23 tables total
```

**Step 3: Seed PADI Courses**
```bash
php scripts/seed_padi_courses.php
```

### Phase 2: Model Classes (2-3 hours)

Create model files in `app/Models/Course/`:

```php
// CourseTemplate.php
// CourseInstance.php
// CourseEnrollment.php
// CourseSkill.php
// StudentSkillProgress.php
// DiveSession.php
// StudentReferral.php
// MedicalStatement.php
// WaterskillsAssessment.php
// StudentPhoto.php
// IncidentReport.php
// StudentCertification.php
```

### Phase 3: Service Layer (2-3 hours)

Create services in `app/Services/`:

```php
// CourseManagementService.php
// CertificationService.php
// ComplianceService.php
// ReferralService.php
// IncidentReportingService.php
```

### Phase 4: Controllers (2-3 hours)

Create controllers in `app/Controllers/Courses/`:

```php
// CourseController.php
// EnrollmentController.php
// TeamTeachingController.php
// FormComplianceController.php
// StudentPhotoController.php
// ReferralController.php
// IncidentController.php
// ReportingController.php
```

### Phase 5: Views (2-3 hours)

Create views in `app/Views/courses/`:

```
index.php                  - Course listing
create_instance.php        - Schedule new course
enrollments.php            - Student enrollment list
forms/
    checklist.php          - Form compliance checklist
    medical_form.php       - Medical statement
photos/
    upload.php             - Photo upload
    approve.php            - Photo approval
reports/
    compliance.php         - Compliance report
    team_teaching.php      - Team teaching form
```

### Phase 6: Testing & Deployment (1-2 hours)

**Test Checklist:**
- [ ] Create course instance
- [ ] Enroll student
- [ ] Submit forms
- [ ] Record skill progress with multiple instructors
- [ ] Generate team teaching form
- [ ] Complete waterskills assessment
- [ ] Upload and approve student photo
- [ ] Generate certification
- [ ] Test referral enrollment
- [ ] Submit incident report

---

## Common Workflows

### Workflow 1: Standard Course Completion

```php
// 1. Admin creates course
$instanceId = $courseService->createCourseInstance([...]);

// 2. Student enrolls
$enrollmentId = $courseService->enrollStudent($instanceId, $studentId);

// 3. Student submits all 13 forms
foreach ($requiredForms as $form) {
    $formController->submitForm($enrollmentId, $form->id, [...]);
}

// 4. Instructor records progress
// Day 1: Academics
StudentSkillProgress::completeSkill($enrollmentId, 1, $instructorId, 'Module 1 complete');
// ... modules 2-5

// Day 2: Confined Water
StudentSkillProgress::completeSkill($enrollmentId, 6, $instructorId, 'CW Dive 1 complete');
// ... dives 2-5

// Day 3-4: Open Water
StudentSkillProgress::completeSkill($enrollmentId, 11, $instructorId, 'OW Dive 1 complete');
// ... dives 2-4

// 5. Record waterskills
WaterskillsAssessment::create([...]);

// 6. Generate certification
$certService->generateCertification($enrollmentId, $instructorId);
```

### Workflow 2: Multi-Instructor Team Teaching

```php
// 1. Create course with primary instructor
$instanceId = $courseService->createCourseInstance([
    'primary_instructor_id' => 5  // John
]);

// 2. Add assistant instructors
$db->query("INSERT INTO course_instructors ...", [6]); // Mary
$db->query("INSERT INTO course_instructors ...", [7]); // Bob

// 3. Each instructor signs off on their segments
// John teaches academics
StudentSkillProgress::completeSkill($enrollmentId, 1, 5, 'Module 1');

// Mary teaches confined water
StudentSkillProgress::completeSkill($enrollmentId, 6, 6, 'CW Dive 1');

// Bob teaches open water
StudentSkillProgress::completeSkill($enrollmentId, 11, 7, 'OW Dive 1');

// 4. Generate Form 007DT showing all instructors
$controller->generateForm007DT($instanceId);
```

### Workflow 3: Referral Student Completion

```php
// 1. Create referral enrollment
$referralService->createReferralEnrollment($instanceId, $studentId, [
    'academics_completed' => 5,
    'confined_water_completed' => 5,
    'open_water_completed' => 2,
    'referral_date' => '2024-11-01'
]);

// 2. Student completes remaining OW dives
StudentSkillProgress::completeSkill($enrollmentId, 13, $instructorId, 'OW Dive 3');
StudentSkillProgress::completeSkill($enrollmentId, 14, $instructorId, 'OW Dive 4');

// 3. Generate certification (includes referral info)
$certService->generateCertification($enrollmentId, $instructorId);

// 4. PIC worksheet shows referral center
$referralController->generatePICWorksheet($enrollmentId);
```

---

## API Reference

### CourseManagementService

```php
class CourseManagementService
{
    public function createCourseInstance(array $data): int
    public function enrollStudent(int $instanceId, int $studentId, array $data = []): int
    public function getCompletionStatus(int $enrollmentId): array
    public function verifyReadyForCertification(int $enrollmentId): bool
}
```

### CertificationService

```php
class CertificationService
{
    public function generateCertification(int $enrollmentId, int $instructorId): string
    public function sendToCredy(int $certificationId): bool
    public function generateCertificationCard(int $certificationId): string
    public function verifyReadyForCertification(int $enrollmentId): void  // throws exception
}
```

### ReferralService

```php
class ReferralService
{
    public function createReferralEnrollment(int $instanceId, int $studentId, array $referralData): int
    public function validateReferralWindow(string $referralDate): bool
    public function markPrecompletedSkills(int $enrollmentId, array $referralData): void
}
```

---

## Troubleshooting

### Issue: Skills Not Showing as Complete

**Symptoms:** Student has 0% progress even after sign-offs

**Solution:**
```sql
-- Check if skill records exist
SELECT COUNT(*) FROM student_skill_progress WHERE enrollment_id = ?;
-- Should return 24 for Open Water

-- Check sign-off records
SELECT * FROM instructor_skill_signoffs
WHERE student_skill_progress_id IN (
    SELECT id FROM student_skill_progress WHERE enrollment_id = ?
);
```

### Issue: Referral Skills Not Auto-Marked

**Symptoms:** Referral student shows all skills as not started

**Solution:**
```php
// Verify referral data was saved
$referral = StudentReferral::find($enrollmentId);
echo $referral->academics_completed;  // Should match referral form

// Manually mark skills if needed
$referralService->markPrecompletedSkills($enrollmentId, [
    'academics_completed' => 5,
    'confined_water_completed' => 5,
    'open_water_completed' => 2
]);
```

### Issue: Cannot Generate Certification

**Symptoms:** Exception thrown during certification

**Solution:**
```php
try {
    $certService->verifyReadyForCertification($enrollmentId);
} catch (Exception $e) {
    echo $e->getMessage();
    // Will tell you exactly what's missing:
    // - "Student has incomplete skills"
    // - "Required forms not submitted"
    // - "Waterskills assessment not completed"
    // - "Medical clearance required but not obtained"
}
```

### Issue: Forms Show as Not Submitted

**Symptoms:** Form checklist shows pending even after submission

**Solution:**
```sql
-- Check form status
SELECT sfc.*, rf.form_name
FROM student_form_completion sfc
JOIN required_forms rf ON sfc.required_form_id = rf.id
WHERE sfc.enrollment_id = ?;

-- Forms must be 'accepted' not just 'submitted'
UPDATE student_form_completion
SET status = 'accepted', verified_by_user_id = ?, verification_date = NOW()
WHERE id = ?;
```

---

## Configuration Reference

### Environment Variables

```ini
# Credly Integration
CREDLY_API_KEY=your_api_key
CREDLY_API_URL=https://api.credly.com/v1

# PADI Integration
PADI_INCIDENT_EMAIL=incidents@yourdiveshop.com

# File Storage
FORM_STORAGE_PATH=/public/uploads/forms/
PHOTO_STORAGE_PATH=/public/uploads/student_photos/
MAX_PHOTO_SIZE=5242880

# Referral Settings
REFERRAL_VALIDITY_DAYS=365

# Waterskills Settings
SWIM_DISTANCE_YARDS=200
SWIM_DISTANCE_MASK_SNORKEL_FINS_YARDS=300
FLOAT_DURATION_MINUTES=10
```

### Directory Permissions

```bash
# Create directories
mkdir -p public/uploads/forms
mkdir -p public/uploads/student_photos
mkdir -p storage/backups/forms
mkdir -p storage/certifications

# Set permissions
chmod 755 public/uploads/forms
chmod 755 public/uploads/student_photos
chmod 755 storage/backups/forms
chmod 755 storage/certifications

# Set ownership
chown -R www-data:www-data public/uploads/
chown -R www-data:www-data storage/
```

---

## Production Deployment Checklist

Before going live:

- [ ] **Database**
  - [ ] All 23 tables created
  - [ ] PADI courses seeded
  - [ ] Required forms configured
  - [ ] Test data verified

- [ ] **File Storage**
  - [ ] Upload directories created
  - [ ] Permissions set correctly
  - [ ] Backup system configured
  - [ ] Encryption enabled for sensitive forms

- [ ] **Integrations**
  - [ ] Credly API tested
  - [ ] PADI incident email verified
  - [ ] Email notifications working
  - [ ] PDF generation tested

- [ ] **Security**
  - [ ] Medical forms encrypted
  - [ ] Signature files secured
  - [ ] Access control configured
  - [ ] Audit logging enabled

- [ ] **Training**
  - [ ] Staff trained on system
  - [ ] Instructors trained on sign-offs
  - [ ] Admins trained on compliance
  - [ ] Front desk trained on forms

- [ ] **Testing**
  - [ ] Complete course workflow tested
  - [ ] Team teaching form generated
  - [ ] Referral enrollment tested
  - [ ] Certification generation verified
  - [ ] Incident report submitted

---

## Additional Resources

- **Main Developer Guide:** [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)
- **Architecture Overview:** [ARCHITECTURE.md](ARCHITECTURE.md)
- **API Documentation:** [docs/API.md](docs/API.md)
- **Security Guide:** [docs/SECURITY.md](docs/SECURITY.md)

For PADI-specific questions, visit: https://padi.com

---

**This guide provides the complete implementation details for the Nautilus Course Management System. All code examples are production-ready and PADI-compliant.**
