<?php

namespace App\Controllers\Instructor;

use App\Core\Database;
use App\Services\Email\EmailService;
use App\Services\Communication\SMSService;

/**
 * Instructor Portal Controller
 * 
 * Dashboard and management for instructors to:
 * - View their courses and students
 * - Track student progress (PADI-aligned)
 * - Send communications (email/SMS)
 * - Manage enrollments
 */
class InstructorController
{
    private int $instructorId;

    public function __construct()
    {
        $this->instructorId = $_SESSION['user_id'] ?? 0;
    }

    /**
     * Instructor Dashboard
     */
    public function index(): void
    {
        $instructorId = $this->instructorId;

        // Get instructor info
        $instructor = Database::fetchOne(
            "SELECT id, first_name, last_name, email FROM users WHERE id = ?",
            [$instructorId]
        );

        // Get today's classes
        $today = date('Y-m-d');
        $todayClasses = Database::fetchAll(
            "SELECT cs.*, c.name as course_name, c.course_code,
                    (SELECT COUNT(*) FROM course_enrollments WHERE schedule_id = cs.id) as enrolled_count
             FROM course_schedules cs
             JOIN courses c ON c.id = cs.course_id
             WHERE cs.instructor_id = ? 
               AND ? BETWEEN cs.start_date AND cs.end_date
               AND cs.status IN ('scheduled', 'in_progress')
             ORDER BY cs.start_time ASC",
            [$instructorId, $today]
        );

        // Get total student count
        $result = Database::fetchOne(
            "SELECT COUNT(DISTINCT ce.customer_id) as count
             FROM course_enrollments ce
             JOIN course_schedules cs ON cs.id = ce.schedule_id
             WHERE cs.instructor_id = ?
               AND ce.status IN ('enrolled', 'in_progress')",
            [$instructorId]
        );
        $studentCount = $result['count'] ?? 0;

        // Get upcoming sessions (next 7 days)
        $upcomingSessions = Database::fetchAll(
            "SELECT cs.*, c.name as course_name, c.course_code,
                    (SELECT COUNT(*) FROM course_enrollments WHERE schedule_id = cs.id) as enrolled_count
             FROM course_schedules cs
             JOIN courses c ON c.id = cs.course_id
             WHERE cs.instructor_id = ?
               AND cs.start_date BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY)
               AND cs.status = 'scheduled'
             ORDER BY cs.start_date ASC, cs.start_time ASC
             LIMIT 10",
            [$instructorId, $today, $today]
        );

        // Get pending paperwork
        $pendingPaperwork = Database::fetchAll(
            "SELECT ce.id as enrollment_id, cu.first_name, cu.last_name, c.name as course_name,
                    csr.overall_status
             FROM course_enrollments ce
             JOIN course_schedules cs ON cs.id = ce.schedule_id
             JOIN courses c ON c.id = cs.course_id
             JOIN customers cu ON cu.id = ce.customer_id
             LEFT JOIN course_student_records csr ON csr.enrollment_id = ce.id
             WHERE cs.instructor_id = ?
               AND ce.status IN ('enrolled', 'in_progress')
               AND (csr.id IS NULL OR csr.overall_status NOT IN ('completed', 'referred'))
             ORDER BY cs.start_date ASC
             LIMIT 10",
            [$instructorId]
        );

        // Get recent completions
        $recentCompletions = Database::fetchAll(
            "SELECT ce.id, cu.first_name, cu.last_name, c.name as course_name,
                    csr.completion_date, csr.certification_number
             FROM course_enrollments ce
             JOIN course_schedules cs ON cs.id = ce.schedule_id
             JOIN courses c ON c.id = cs.course_id
             JOIN customers cu ON cu.id = ce.customer_id
             JOIN course_student_records csr ON csr.enrollment_id = ce.id
             WHERE cs.instructor_id = ?
               AND csr.overall_status = 'completed'
               AND csr.completion_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             ORDER BY csr.completion_date DESC
             LIMIT 5",
            [$instructorId]
        );

        $pageTitle = 'Instructor Dashboard';
        $activeMenu = 'instructor';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/instructor/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    /**
     * List instructor's courses
     */
    public function courses(): void
    {
        $instructorId = $this->instructorId;

        $courses = Database::fetchAll(
            "SELECT cs.*, c.name as course_name, c.course_code, c.description,
                    c.duration_days, c.max_students,
                    (SELECT COUNT(*) FROM course_enrollments WHERE schedule_id = cs.id) as enrolled_count,
                    (SELECT COUNT(*) FROM course_enrollments ce2 
                     JOIN course_student_records csr ON csr.enrollment_id = ce2.id 
                     WHERE ce2.schedule_id = cs.id AND csr.overall_status = 'completed') as completed_count
             FROM course_schedules cs
             JOIN courses c ON c.id = cs.course_id
             WHERE cs.instructor_id = ?
             ORDER BY cs.start_date DESC",
            [$instructorId]
        );

        $activeCourses = array_filter($courses, fn($c) => in_array($c['status'], ['scheduled', 'in_progress']));
        $completedCourses = array_filter($courses, fn($c) => $c['status'] === 'completed');

        $pageTitle = 'My Courses';
        $activeMenu = 'instructor';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/instructor/courses.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    /**
     * List all students
     */
    public function students(): void
    {
        $instructorId = $this->instructorId;

        $students = Database::fetchAll(
            "SELECT DISTINCT cu.id, cu.first_name, cu.last_name, cu.email, cu.phone,
                    c.name as course_name, cs.start_date, cs.end_date,
                    ce.status as enrollment_status, ce.id as enrollment_id,
                    csr.overall_status as progress_status,
                    csr.knowledge_status, csr.confined_water_status, csr.open_water_status,
                    cu.sms_opt_in
             FROM course_enrollments ce
             JOIN course_schedules cs ON cs.id = ce.schedule_id
             JOIN courses c ON c.id = cs.course_id
             JOIN customers cu ON cu.id = ce.customer_id
             LEFT JOIN course_student_records csr ON csr.enrollment_id = ce.id
             WHERE cs.instructor_id = ?
             ORDER BY cs.start_date DESC, cu.last_name ASC",
            [$instructorId]
        );

        $availableCourses = Database::fetchAll(
            "SELECT cs.id, c.name as course_name, cs.start_date, cs.max_students,
                    (SELECT COUNT(*) FROM course_enrollments WHERE schedule_id = cs.id) as enrolled_count
             FROM course_schedules cs
             JOIN courses c ON c.id = cs.course_id
             WHERE cs.status = 'scheduled'
               AND cs.start_date >= CURDATE()
             ORDER BY cs.start_date ASC"
        );

        $pageTitle = 'My Students';
        $activeMenu = 'instructor';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/instructor/students.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    /**
     * View a specific course
     */
    public function showCourse(int $scheduleId): void
    {
        $instructorId = $this->instructorId;

        $schedule = Database::fetchOne(
            "SELECT cs.*, c.name as course_name, c.course_code, c.description,
                    c.duration_days, c.max_students, c.price
             FROM course_schedules cs
             JOIN courses c ON c.id = cs.course_id
             WHERE cs.id = ? AND cs.instructor_id = ?",
            [$scheduleId, $instructorId]
        );

        if (!$schedule) {
            header('Location: /instructor/courses');
            exit;
        }

        $students = Database::fetchAll(
            "SELECT cu.id, cu.first_name, cu.last_name, cu.email, cu.phone,
                    ce.id as enrollment_id, ce.status as enrollment_status,
                    ce.enrollment_date, ce.payment_status,
                    csr.overall_status, csr.knowledge_status, 
                    csr.confined_water_status, csr.open_water_status,
                    cu.sms_opt_in
             FROM course_enrollments ce
             JOIN customers cu ON cu.id = ce.customer_id
             LEFT JOIN course_student_records csr ON csr.enrollment_id = ce.id
             WHERE ce.schedule_id = ?
             ORDER BY cu.last_name ASC",
            [$scheduleId]
        );

        $pageTitle = 'Course: ' . $schedule['course_name'];
        $activeMenu = 'instructor';
        $user = $_SESSION['user'] ?? [];

        ob_start();
        require __DIR__ . '/../../Views/instructor/course_detail.php';
        $content = ob_get_clean();

        require __DIR__ . '/../../Views/layouts/admin.php';
    }

    /**
     * Assign a student to a class
     */
    public function assignStudent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /instructor/students');
            exit;
        }

        $customerId = (int) ($_POST['customer_id'] ?? 0);
        $scheduleId = (int) ($_POST['schedule_id'] ?? 0);

        if (!$customerId || !$scheduleId) {
            $_SESSION['error'] = 'Invalid student or course selected.';
            header('Location: /instructor/students');
            exit;
        }

        try {
            $existing = Database::fetchOne(
                "SELECT id FROM course_enrollments WHERE schedule_id = ? AND customer_id = ?",
                [$scheduleId, $customerId]
            );

            if ($existing) {
                $_SESSION['error'] = 'Student is already enrolled in this class.';
                header('Location: /instructor/students');
                exit;
            }

            $enrollmentId = Database::insert('course_enrollments', [
                'schedule_id' => $scheduleId,
                'customer_id' => $customerId,
                'enrollment_date' => date('Y-m-d'),
                'status' => 'enrolled',
                'amount_paid' => 0,
                'payment_status' => 'pending'
            ]);

            Database::insert('course_student_records', [
                'enrollment_id' => $enrollmentId,
                'form_type' => 'course_record',
                'overall_status' => 'enrolled',
                'instructor_id' => $this->instructorId
            ]);

            Database::execute(
                "UPDATE course_schedules SET current_enrollment = current_enrollment + 1 WHERE id = ?",
                [$scheduleId]
            );

            $_SESSION['success'] = 'Student assigned to class successfully!';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to assign student: ' . $e->getMessage();
        }

        header('Location: /instructor/students');
        exit;
    }

    /**
     * Send email to a student
     */
    public function sendEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /instructor/students');
            exit;
        }

        $enrollmentId = (int) ($_POST['enrollment_id'] ?? 0);
        $emailType = $_POST['email_type'] ?? 'welcome';

        if (!$enrollmentId) {
            $_SESSION['error'] = 'Invalid enrollment.';
            header('Location: /instructor/students');
            exit;
        }

        try {
            $enrollment = Database::fetchOne(
                "SELECT ce.*, cu.email, cu.first_name, cu.last_name, cu.phone, cu.sms_opt_in,
                        c.name as course_name, cs.start_date, cs.end_date, cs.location,
                        u.first_name as instructor_first, u.last_name as instructor_last
                 FROM course_enrollments ce
                 JOIN customers cu ON cu.id = ce.customer_id
                 JOIN course_schedules cs ON cs.id = ce.schedule_id
                 JOIN courses c ON c.id = cs.course_id
                 JOIN users u ON u.id = cs.instructor_id
                 WHERE ce.id = ?",
                [$enrollmentId]
            );

            if (!$enrollment) {
                $_SESSION['error'] = 'Enrollment not found.';
                header('Location: /instructor/students');
                exit;
            }

            $emailService = new EmailService();
            $smsService = new SMSService();

            switch ($emailType) {
                case 'welcome':
                    $subject = "Welcome to {$enrollment['course_name']}!";
                    $body = $this->renderEmailTemplate('course_welcome', $enrollment);
                    $smsMessage = "Welcome to {$enrollment['course_name']}! Your class starts {$enrollment['start_date']}. Check your email for details.";
                    break;

                case 'reminder':
                    $daysUntil = max(0, (strtotime($enrollment['start_date']) - time()) / 86400);
                    $subject = "Reminder: {$enrollment['course_name']} starts in " . ceil($daysUntil) . " days!";
                    $body = $this->renderEmailTemplate('course_reminder', array_merge($enrollment, ['days_until' => ceil($daysUntil)]));
                    $smsMessage = "Reminder: Your {$enrollment['course_name']} class starts " . date('M j', strtotime($enrollment['start_date'])) . ". See you there!";
                    break;

                case 'thankyou':
                    $subject = "Thank you for completing {$enrollment['course_name']}!";
                    $body = $this->renderEmailTemplate('course_thankyou', $enrollment);
                    $smsMessage = "Congratulations on completing {$enrollment['course_name']}! Check your email for next steps.";
                    break;

                case 'progress':
                    $subject = "Progress Update: {$enrollment['course_name']}";
                    $body = $this->renderEmailTemplate('course_progress', $enrollment);
                    $smsMessage = "Your progress in {$enrollment['course_name']} has been updated. Check your email for details.";
                    break;

                default:
                    throw new \Exception('Invalid email type.');
            }

            $result = $emailService->send($enrollment['email'], $subject, $body);

            if ($enrollment['sms_opt_in'] && !empty($enrollment['phone'])) {
                $smsService->send($enrollment['phone'], $smsMessage);
            }

            $_SESSION['success'] = ucfirst($emailType) . ' email sent successfully!';

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to send email: ' . $e->getMessage();
        }

        header('Location: /instructor/students');
        exit;
    }

    /**
     * Render an email template
     */
    private function renderEmailTemplate(string $template, array $data): string
    {
        extract($data);
        ob_start();
        require __DIR__ . "/../../Views/emails/{$template}.php";
        return ob_get_clean();
    }
}
