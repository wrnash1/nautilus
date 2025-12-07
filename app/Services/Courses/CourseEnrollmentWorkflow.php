<?php

namespace App\Services\Courses;

use App\Core\Database;
use App\Services\Email\EmailService;
use App\Services\Notifications\NotificationService;
use App\Core\Logger;

/**
 * Course Enrollment Workflow Service
 * Handles automated workflows when students enroll in courses
 */
class CourseEnrollmentWorkflow
{
    private EmailService $emailService;
    private NotificationService $notificationService;
    private Logger $logger;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->notificationService = new NotificationService();
        $this->logger = new Logger();
    }

    /**
     * Execute complete enrollment workflow
     * This is the main method called when a student enrolls in a class
     */
    public function processEnrollment(int $enrollmentId): bool
    {
        try {
            $enrollment = $this->getEnrollmentDetails($enrollmentId);

            if (!$enrollment) {
                $this->logger->error('Enrollment not found', ['enrollment_id' => $enrollmentId]);
                return false;
            }

            // Step 1: Create requirement checklist for student
            $this->createRequirementChecklist($enrollmentId, $enrollment['course_id']);

            // Step 2: Update roster count
            $this->updateRosterCount($enrollment['schedule_id']);

            // Step 3: Send welcome email to student
            $this->sendStudentWelcomeEmail($enrollment);

            // Step 4: Notify instructor about new enrollment
            $this->notifyInstructor($enrollment);

            // Step 5: Send requirement reminders to student
            $this->sendRequirementReminders($enrollmentId);

            // Step 6: Create in-app notifications
            $this->createSystemNotifications($enrollment);

            // Step 7: Auto-send waiver if configured
            $this->sendWaiverIfRequired($enrollment);

            $this->logger->info('Enrollment workflow completed', [
                'enrollment_id' => $enrollmentId,
                'customer_id' => $enrollment['customer_id'],
                'course_id' => $enrollment['course_id']
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Enrollment workflow failed', [
                'enrollment_id' => $enrollmentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get enrollment details with related data
     */
    private function getEnrollmentDetails(int $enrollmentId): ?array
    {
        $sql = "SELECT
                    ce.*,
                    c.first_name, c.last_name, c.email as customer_email, c.phone as customer_phone, c.photo_path,
                    co.name as course_name, co.course_code, co.duration_days, co.certification_id,
                    cs.start_date, cs.end_date, cs.instructor_id, cs.location,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name, u.email as instructor_email
                FROM course_enrollments ce
                LEFT JOIN customers c ON ce.customer_id = c.id
                LEFT JOIN course_schedules cs ON ce.schedule_id = cs.id
                LEFT JOIN courses co ON cs.course_id = co.id
                LEFT JOIN users u ON cs.instructor_id = u.id
                WHERE ce.id = ?";

        return Database::fetchOne($sql, [$enrollmentId]);
    }

    /**
     * Create requirement checklist for the student based on course requirements
     */
    private function createRequirementChecklist(int $enrollmentId, int $courseId): void
    {
        // Get all requirements for this course
        $requirements = Database::fetchAll(
            "SELECT cr.*, crt.name, crt.code, crt.requirement_type
             FROM course_requirements cr
             JOIN course_requirement_types crt ON cr.requirement_type_id = crt.id
             WHERE cr.course_id = ? AND crt.is_active = TRUE
             ORDER BY cr.sort_order ASC",
            [$courseId]
        );

        // Create an enrollment_requirement record for each requirement
        foreach ($requirements as $req) {
            Database::execute(
                "INSERT INTO enrollment_requirements
                 (enrollment_id, requirement_type_id, status, created_at)
                 VALUES (?, ?, 'pending', NOW())
                 ON DUPLICATE KEY UPDATE updated_at = NOW()",
                [$enrollmentId, $req['requirement_type_id']]
            );
        }

        $this->logger->info('Created requirement checklist', [
            'enrollment_id' => $enrollmentId,
            'requirement_count' => count($requirements)
        ]);
    }

    /**
     * Update the current enrollment count for the schedule
     */
    private function updateRosterCount(int $scheduleId): void
    {
        Database::execute(
            "UPDATE course_schedules
             SET current_enrollment = (
                 SELECT COUNT(*) FROM course_enrollments
                 WHERE schedule_id = ? AND status IN ('enrolled', 'in_progress')
             )
             WHERE id = ?",
            [$scheduleId, $scheduleId]
        );
    }

    /**
     * Send welcome email to student with course details and requirements
     */
    private function sendStudentWelcomeEmail(array $enrollment): void
    {
        // Get requirements for email
        $requirements = $this->getEnrollmentRequirements($enrollment['id']);

        $data = [
            'subject' => 'Welcome to ' . $enrollment['course_name'] . '!',
            'customer_name' => $enrollment['first_name'] . ' ' . $enrollment['last_name'],
            'course_name' => $enrollment['course_name'],
            'course_code' => $enrollment['course_code'],
            'start_date' => date('F j, Y', strtotime($enrollment['start_date'])),
            'end_date' => date('F j, Y', strtotime($enrollment['end_date'])),
            'instructor_name' => $enrollment['instructor_name'],
            'location' => $enrollment['location'] ?? 'TBD',
            'requirements' => $requirements,
            'enrollment_id' => $enrollment['id']
        ];

        $this->emailService->sendTemplate(
            $enrollment['customer_email'],
            'course_enrollment_welcome',
            $data
        );

        $this->logger->info('Welcome email sent', [
            'enrollment_id' => $enrollment['id'],
            'email' => $enrollment['customer_email']
        ]);
    }

    /**
     * Notify instructor about new enrollment
     */
    private function notifyInstructor(array $enrollment): void
    {
        if (empty($enrollment['instructor_id'])) {
            return;
        }

        $studentName = $enrollment['first_name'] . ' ' . $enrollment['last_name'];

        // Create in-app notification
        $this->notificationService->create(
            $enrollment['instructor_id'],
            'New Student Enrolled',
            sprintf('%s has enrolled in %s starting %s',
                $studentName,
                $enrollment['course_name'],
                date('M j', strtotime($enrollment['start_date']))
            ),
            'info',
            '/courses/schedules/' . $enrollment['schedule_id']
        );

        // Create instructor notification record
        Database::execute(
            "INSERT INTO instructor_notifications
             (instructor_id, schedule_id, notification_type, message, created_at)
             VALUES (?, ?, 'enrollment', ?, NOW())",
            [
                $enrollment['instructor_id'],
                $enrollment['schedule_id'],
                sprintf('%s enrolled in %s', $studentName, $enrollment['course_name'])
            ]
        );

        // Send email to instructor
        if (!empty($enrollment['instructor_email'])) {
            $data = [
                'subject' => 'New Enrollment: ' . $enrollment['course_name'],
                'instructor_name' => $enrollment['instructor_name'],
                'student_name' => $studentName,
                'student_email' => $enrollment['customer_email'],
                'student_phone' => $enrollment['customer_phone'],
                'course_name' => $enrollment['course_name'],
                'start_date' => date('F j, Y', strtotime($enrollment['start_date'])),
                'schedule_id' => $enrollment['schedule_id']
            ];

            $this->emailService->sendTemplate(
                $enrollment['instructor_email'],
                'instructor_new_enrollment',
                $data
            );
        }

        $this->logger->info('Instructor notified', [
            'instructor_id' => $enrollment['instructor_id'],
            'enrollment_id' => $enrollment['id']
        ]);
    }

    /**
     * Send requirement reminders to student
     */
    private function sendRequirementReminders(int $enrollmentId): void
    {
        $requirements = $this->getEnrollmentRequirements($enrollmentId);
        $enrollment = $this->getEnrollmentDetails($enrollmentId);

        if (empty($requirements)) {
            return;
        }

        $data = [
            'subject' => 'Action Required: Course Requirements for ' . $enrollment['course_name'],
            'customer_name' => $enrollment['first_name'],
            'course_name' => $enrollment['course_name'],
            'start_date' => date('F j, Y', strtotime($enrollment['start_date'])),
            'requirements' => $requirements,
            'enrollment_id' => $enrollmentId
        ];

        $this->emailService->sendTemplate(
            $enrollment['customer_email'],
            'course_requirements_reminder',
            $data
        );

        // Mark reminders as sent
        Database::execute(
            "UPDATE enrollment_requirements
             SET reminder_sent = TRUE, reminder_sent_at = NOW(), reminder_count = reminder_count + 1
             WHERE enrollment_id = ? AND status = 'pending'",
            [$enrollmentId]
        );
    }

    /**
     * Create system notifications for relevant staff
     */
    private function createSystemNotifications(array $enrollment): void
    {
        // Notify admin users
        $adminUsers = Database::fetchAll(
            "SELECT id FROM users WHERE role = 'admin' AND is_active = TRUE"
        );

        foreach ($adminUsers as $admin) {
            $this->notificationService->create(
                $admin['id'],
                'New Course Enrollment',
                sprintf('%s %s enrolled in %s',
                    $enrollment['first_name'],
                    $enrollment['last_name'],
                    $enrollment['course_name']
                ),
                'info',
                '/courses/enrollments/' . $enrollment['id']
            );
        }
    }

    /**
     * Auto-send training waiver if configured
     */
    private function sendWaiverIfRequired(array $enrollment): void
    {
        // Check if training waiver is required
        $waiverTemplate = Database::fetchOne(
            "SELECT id FROM waiver_templates WHERE type = 'training' AND is_active = TRUE LIMIT 1"
        );

        if ($waiverTemplate) {
            $token = bin2hex(random_bytes(32));
            $waiverUrl = ($_ENV['APP_URL'] ?? 'https://nautilus.local') . '/waivers/sign/' . $token;

            // Queue waiver email
            Database::execute(
                "INSERT INTO waiver_email_queue
                 (customer_id, waiver_template_id, reference_type, reference_id, email_to, subject,
                  unique_token, waiver_url, status, expires_at, created_at)
                 VALUES (?, ?, 'course', ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())",
                [
                    $enrollment['customer_id'],
                    $waiverTemplate['id'],
                    $enrollment['id'],
                    $enrollment['customer_email'],
                    'Required Waiver: ' . $enrollment['course_name'],
                    $token,
                    $waiverUrl
                ]
            );

            $this->logger->info('Waiver queued for sending', [
                'enrollment_id' => $enrollment['id'],
                'customer_id' => $enrollment['customer_id']
            ]);
        }
    }

    /**
     * Get enrollment requirements with status
     */
    private function getEnrollmentRequirements(int $enrollmentId): array
    {
        return Database::fetchAll(
            "SELECT er.*, crt.name, crt.code, crt.description, crt.requirement_type,
                    cr.is_mandatory, cr.instructions
             FROM enrollment_requirements er
             JOIN course_requirement_types crt ON er.requirement_type_id = crt.id
             JOIN course_requirements cr ON (cr.requirement_type_id = crt.id)
             JOIN course_enrollments ce ON (ce.id = er.enrollment_id AND ce.schedule_id IN
                 (SELECT schedule_id FROM course_enrollments WHERE id = ?) AND cr.course_id =
                 (SELECT cs.course_id FROM course_schedules cs WHERE cs.id = ce.schedule_id))
             WHERE er.enrollment_id = ?
             ORDER BY cr.sort_order ASC",
            [$enrollmentId, $enrollmentId]
        ) ?? [];
    }

    /**
     * Mark requirement as completed
     */
    public function markRequirementComplete(int $enrollmentRequirementId, array $data = []): bool
    {
        try {
            $sql = "UPDATE enrollment_requirements
                    SET status = 'completed',
                        is_completed = TRUE,
                        completed_at = NOW(),
                        verified_by = ?,
                        verified_at = NOW()";

            $params = [$_SESSION['user_id'] ?? null];

            // Add type-specific data
            if (!empty($data['waiver_id'])) {
                $sql .= ", waiver_id = ?";
                $params[] = $data['waiver_id'];
            }
            if (!empty($data['elearning_completion_date'])) {
                $sql .= ", elearning_completion_date = ?";
                $params[] = $data['elearning_completion_date'];
            }
            if (!empty($data['photo_path'])) {
                $sql .= ", photo_path = ?";
                $params[] = $data['photo_path'];
            }
            if (!empty($data['document_path'])) {
                $sql .= ", document_path = ?";
                $params[] = $data['document_path'];
            }
            if (!empty($data['notes'])) {
                $sql .= ", notes = ?";
                $params[] = $data['notes'];
            }

            $sql .= " WHERE id = ?";
            $params[] = $enrollmentRequirementId;

            Database::execute($sql, $params);

            // Check if all requirements are complete
            $requirement = Database::fetchOne(
                "SELECT enrollment_id FROM enrollment_requirements WHERE id = ?",
                [$enrollmentRequirementId]
            );

            if ($requirement) {
                $this->checkAndNotifyAllRequirementsComplete($requirement['enrollment_id']);
            }

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to mark requirement complete', [
                'requirement_id' => $enrollmentRequirementId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if all requirements are complete and notify instructor
     */
    private function checkAndNotifyAllRequirementsComplete(int $enrollmentId): void
    {
        $incomplete = Database->fetchOne(
            "SELECT COUNT(*) as count FROM enrollment_requirements er
             JOIN course_requirements cr ON er.requirement_type_id = cr.requirement_type_id
             WHERE er.enrollment_id = ? AND cr.is_mandatory = TRUE AND er.is_completed = FALSE",
            [$enrollmentId]
        );

        if ($incomplete['count'] == 0) {
            $enrollment = $this->getEnrollmentDetails($enrollmentId);

            // Notify instructor
            if (!empty($enrollment['instructor_id'])) {
                $this->notificationService->create(
                    $enrollment['instructor_id'],
                    'Student Ready',
                    sprintf('%s %s has completed all requirements for %s',
                        $enrollment['first_name'],
                        $enrollment['last_name'],
                        $enrollment['course_name']
                    ),
                    'success',
                    '/courses/enrollments/' . $enrollmentId
                );

                Database::execute(
                    "INSERT INTO instructor_notifications
                     (instructor_id, schedule_id, notification_type, message, created_at)
                     VALUES (?, ?, 'requirement_complete', ?, NOW())",
                    [
                        $enrollment['instructor_id'],
                        $enrollment['schedule_id'],
                        sprintf('%s %s completed all requirements',
                            $enrollment['first_name'],
                            $enrollment['last_name']
                        )
                    ]
                );
            }

            $this->logger->info('All requirements completed', ['enrollment_id' => $enrollmentId]);
        }
    }

    /**
     * Get roster for a schedule with requirement status
     */
    public function getRosterWithRequirements(int $scheduleId): array
    {
        $enrollments = Database::fetchAll(
            "SELECT
                ce.id as enrollment_id,
                ce.status as enrollment_status,
                CONCAT(c.first_name, ' ', c.last_name) as student_name,
                c.email, c.phone, c.photo_path,
                ce.enrollment_date,
                (SELECT COUNT(*) FROM enrollment_requirements er
                 JOIN course_requirements cr ON er.requirement_type_id = cr.requirement_type_id
                 WHERE er.enrollment_id = ce.id AND cr.is_mandatory = TRUE) as total_requirements,
                (SELECT COUNT(*) FROM enrollment_requirements er
                 JOIN course_requirements cr ON er.requirement_type_id = cr.requirement_type_id
                 WHERE er.enrollment_id = ce.id AND cr.is_mandatory = TRUE AND er.is_completed = TRUE) as completed_requirements
            FROM course_enrollments ce
            LEFT JOIN customers c ON ce.customer_id = c.id
            WHERE ce.schedule_id = ?
            ORDER BY ce.enrollment_date ASC",
            [$scheduleId]
        ) ?? [];

        // Get detailed requirements for each enrollment
        foreach ($enrollments as &$enrollment) {
            $enrollment['requirements'] = Database::fetchAll(
                "SELECT
                    er.*,
                    crt.name as requirement_name,
                    crt.code as requirement_code,
                    crt.requirement_type,
                    cr.is_mandatory
                FROM enrollment_requirements er
                JOIN course_requirement_types crt ON er.requirement_type_id = crt.id
                JOIN course_requirements cr ON cr.requirement_type_id = crt.id
                WHERE er.enrollment_id = ?
                ORDER BY cr.sort_order ASC",
                [$enrollment['enrollment_id']]
            ) ?? [];

            // Calculate completion percentage
            if ($enrollment['total_requirements'] > 0) {
                $enrollment['completion_percentage'] = round(
                    ($enrollment['completed_requirements'] / $enrollment['total_requirements']) * 100
                );
            } else {
                $enrollment['completion_percentage'] = 100;
            }
        }

        return $enrollments;
    }
}
