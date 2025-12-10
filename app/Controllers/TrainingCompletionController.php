<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class TrainingCompletionController extends Controller
{
    /**
     * Show training completion form (PADI Form 10234)
     */
    public function create(): void
    {
        $enrollmentId = $_GET['enrollment_id'] ?? null;

        if (!$enrollmentId) {
            $_SESSION['error'] = 'Enrollment ID is required';
            redirect('/store/courses');
            return;
        }

        $db = $this->db();

        // Get enrollment details with student and course info
        $stmt = $db->prepare("
            SELECT
                ce.*,
                c.first_name, c.last_name,
                CONCAT(c.first_name, ' ', c.last_name) as student_name,
                c.date_of_birth,
                co.course_name,
                co.certification_level,
                u.username as instructor_name,
                CONCAT(u.first_name, ' ', u.last_name) as instructor_full_name
            FROM course_enrollments ce
            JOIN customers c ON ce.customer_id = c.id
            JOIN courses co ON ce.course_id = co.id
            LEFT JOIN users u ON ce.instructor_id = u.id
            WHERE ce.id = ?
        ");
        $stmt->execute([$enrollmentId]);
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$enrollment) {
            $_SESSION['error'] = 'Enrollment not found';
            redirect('/store/courses');
            return;
        }

        // Check if already completed
        $stmt = $db->prepare("
            SELECT * FROM padi_training_completion
            WHERE enrollment_id = ?
            ORDER BY completion_date DESC
            LIMIT 1
        ");
        $stmt->execute([$enrollmentId]);
        $existingCompletion = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get student's skills completion status
        $stmt = $db->prepare("
            SELECT
                COUNT(*) as total_skills,
                SUM(CASE WHEN performance_level = 'proficient' THEN 1 ELSE 0 END) as proficient_count
            FROM padi_student_skills
            WHERE enrollment_id = ?
        ");
        $stmt->execute([$enrollmentId]);
        $skillsStatus = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->view('training/complete', [
            'enrollment' => $enrollment,
            'existingCompletion' => $existingCompletion,
            'skillsStatus' => $skillsStatus,
            'pageTitle' => 'Training Completion - ' . $enrollment['student_name']
        ]);
    }

    /**
     * Submit training completion (PADI Form 10234)
     */
    public function store(): void
    {
        $enrollmentId = $_POST['enrollment_id'] ?? null;

        if (!$enrollmentId) {
            $_SESSION['error'] = 'Enrollment ID is required';
            redirect('/store/courses');
            return;
        }

        $db = $this->db();

        try {
            $db->beginTransaction();

            // Insert training completion record
            $stmt = $db->prepare("
                INSERT INTO padi_training_completion (
                    enrollment_id,
                    completion_date,
                    certification_number,
                    ecard_issued,
                    ecard_number,
                    ecard_issue_date,
                    instructor_signature_data,
                    instructor_signature_date,
                    instructor_padi_number,
                    theory_completed,
                    confined_water_completed,
                    open_water_completed,
                    all_skills_proficient,
                    additional_training_required,
                    additional_training_notes,
                    padi_submitted,
                    padi_submission_date,
                    submitted_by_user_id,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $enrollmentId,
                $_POST['completion_date'] ?? date('Y-m-d'),
                $_POST['certification_number'] ?? null,
                isset($_POST['ecard_issued']) ? 1 : 0,
                $_POST['ecard_number'] ?? null,
                $_POST['ecard_issue_date'] ?? null,
                $_POST['instructor_signature'] ?? null,
                $_POST['instructor_padi_number'] ?? null,
                isset($_POST['theory_completed']) ? 1 : 0,
                isset($_POST['confined_water_completed']) ? 1 : 0,
                isset($_POST['open_water_completed']) ? 1 : 0,
                isset($_POST['all_skills_proficient']) ? 1 : 0,
                isset($_POST['additional_training_required']) ? 1 : 0,
                $_POST['additional_training_notes'] ?? null,
                isset($_POST['padi_submitted']) ? 1 : 0,
                $_POST['padi_submission_date'] ?? null,
                $_SESSION['user_id'] ?? null
            ]);

            $completionId = $db->lastInsertId();

            // Update enrollment status
            $stmt = $db->prepare("
                UPDATE course_enrollments
                SET status = 'completed',
                    completion_date = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['completion_date'] ?? date('Y-m-d'),
                $enrollmentId
            ]);

            // TODO: Generate PADI Form 10234 PDF
            // TODO: Send eCard if issued
            // TODO: Submit to PADI API if enabled

            $db->commit();

            $_SESSION['success'] = 'Training completion recorded successfully';
            redirect('/store/courses/enrollments/' . $enrollmentId);

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Failed to record completion: ' . $e->getMessage();
            redirect('/training/complete?enrollment_id=' . $enrollmentId);
        }
    }

    /**
     * View training completion record
     */
    public function show(): void
    {
        $completionId = $this->getRouteParam('id');

        $db = $this->db();
        $stmt = $db->prepare("
            SELECT
                tc.*,
                ce.customer_id,
                c.first_name, c.last_name,
                CONCAT(c.first_name, ' ', c.last_name) as student_name,
                co.course_name,
                u.username as instructor_name
            FROM padi_training_completion tc
            JOIN course_enrollments ce ON tc.enrollment_id = ce.id
            JOIN customers c ON ce.customer_id = c.id
            JOIN courses co ON ce.course_id = co.id
            LEFT JOIN users u ON ce.instructor_id = u.id
            WHERE tc.id = ?
        ");
        $stmt->execute([$completionId]);
        $completion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$completion) {
            $_SESSION['error'] = 'Training completion record not found';
            redirect('/store/courses');
            return;
        }

        $this->view('training/show', [
            'completion' => $completion,
            'pageTitle' => 'Training Completion - ' . $completion['student_name']
        ]);
    }

    /**
     * Submit to PADI (if API integration available)
     */
    public function submitToPadi(): void
    {
        $completionId = $_POST['completion_id'] ?? null;

        if (!$completionId) {
            echo json_encode(['success' => false, 'message' => 'Completion ID required']);
            return;
        }

        // TODO: Implement PADI API submission
        // For now, just mark as submitted

        $db = $this->db();
        $stmt = $db->prepare("
            UPDATE padi_training_completion
            SET padi_submitted = 1,
                padi_submission_date = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$completionId]);

        echo json_encode([
            'success' => true,
            'message' => 'Marked as submitted to PADI'
        ]);
    }
}
