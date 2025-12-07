<?php

namespace App\Controllers\Instructor;

use App\Core\Controller;
use App\Core\Database;
use App\Services\StudentAssessmentService;

/**
 * Skills Checkoff Controller
 * Tablet-optimized interface for instructors to track student skills at dive sites
 */
class SkillsCheckoffController extends Controller
{
    private StudentAssessmentService $assessmentService;

    public function __construct()
    {
        parent::__construct();
        $this->assessmentService = new StudentAssessmentService(Database::getInstance()->getConnection());
    }

    /**
     * Show instructor's students list (tablet view)
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requireRole(['instructor', 'admin']);

        $instructorId = $_SESSION['user']['id'];

        // Get all active students
        $students = $this->assessmentService->getInstructorStudents($instructorId, null);

        // Get students needing attention
        $needsAttention = $this->assessmentService->getStudentsNeedingAttention($instructorId);

        $this->view('instructor/skills/index', [
            'students' => $students,
            'needs_attention' => $needsAttention,
            'page_title' => 'Student Skills Tracking'
        ]);
    }

    /**
     * Show student record with skills checkoff
     */
    public function studentRecord(int $recordId): void
    {
        $this->requireAuth();
        $this->requireRole(['instructor', 'admin']);

        $progress = $this->assessmentService->getStudentProgress($recordId);

        if (empty($progress)) {
            $this->redirect('/instructor/skills?error=record_not_found');
            return;
        }

        $this->view('instructor/skills/student_record', [
            'progress' => $progress,
            'record' => $progress['record'],
            'page_title' => 'Student Record'
        ]);
    }

    /**
     * Show skills checkoff for specific session (tablet-optimized)
     */
    public function session(int $recordId, string $sessionType, int $sessionNumber): void
    {
        $this->requireAuth();
        $this->requireRole(['instructor', 'admin']);

        // Get student record
        $stmt = Database::getInstance()->getConnection()->prepare("
            SELECT csr.*,
                   c.first_name, c.last_name, c.photo_path,
                   co.name as course_name
            FROM course_student_records csr
            JOIN course_enrollments ce ON csr.enrollment_id = ce.id
            JOIN customers c ON ce.customer_id = c.id
            LEFT JOIN courses co ON ce.course_id = co.id
            WHERE csr.id = ?
        ");
        $stmt->execute([$recordId]);
        $record = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$record) {
            $this->redirect('/instructor/skills?error=record_not_found');
            return;
        }

        // Get existing skills for this session
        $skills = $this->assessmentService->getSessionSkills($recordId, $sessionType, $sessionNumber);

        // If no skills exist, initialize from standard PADI skills
        if (empty($skills)) {
            $this->assessmentService->initializeSessionSkills($recordId, $sessionType, $sessionNumber);
            $skills = $this->assessmentService->getSessionSkills($recordId, $sessionType, $sessionNumber);
        }

        $this->view('instructor/skills/session_checkoff', [
            'record' => $record,
            'session_type' => $sessionType,
            'session_number' => $sessionNumber,
            'skills' => $skills,
            'page_title' => ucfirst(str_replace('_', ' ', $sessionType)) . ' ' . $sessionNumber
        ]);
    }

    /**
     * Update skill assessment (AJAX)
     */
    public function updateSkill(): void
    {
        $this->requireAuth();
        $this->requireRole(['instructor', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['assessment_id'])) {
            $this->jsonResponse(['error' => 'Assessment ID required'], 400);
            return;
        }

        try {
            $success = $this->assessmentService->updateSkillAssessment(
                (int)$data['assessment_id'],
                [
                    'performance' => $data['performance'] ?? 'not_performed',
                    'pass' => $data['pass'] ?? false,
                    'attempts' => $data['attempts'] ?? 1,
                    'assessment_notes' => $data['notes'] ?? null,
                    'remediation_needed' => $data['remediation_needed'] ?? false
                ]
            );

            if ($success) {
                // Update session status
                if (isset($data['record_id'], $data['session_type'], $data['session_number'])) {
                    $this->assessmentService->updateSessionStatus(
                        (int)$data['record_id'],
                        $data['session_type'],
                        (int)$data['session_number']
                    );
                }

                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Update failed'], 500);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Complete a session
     */
    public function completeSession(): void
    {
        $this->requireAuth();
        $this->requireRole(['instructor', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['record_id'], $data['session_type'], $data['session_number'])) {
            $this->jsonResponse(['error' => 'Missing required fields'], 400);
            return;
        }

        try {
            $recordId = (int)$data['record_id'];
            $sessionType = $data['session_type'];
            $sessionNumber = (int)$data['session_number'];

            // Update session status
            $this->assessmentService->updateSessionStatus($recordId, $sessionType, $sessionNumber);

            // Update phase status (knowledge/confined/open water)
            $statusField = $sessionType . '_status';
            $completionField = $sessionType . '_completion_date';

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                UPDATE course_student_records
                SET $statusField = 'completed',
                    $completionField = CURDATE()
                WHERE id = ?
            ");
            $stmt->execute([$recordId]);

            // Update overall status
            $this->assessmentService->updateOverallStatus($recordId);

            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add notes to student record
     */
    public function addNotes(): void
    {
        $this->requireAuth();
        $this->requireRole(['instructor', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['record_id'])) {
            $this->jsonResponse(['error' => 'Record ID required'], 400);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                UPDATE course_student_records
                SET instructor_notes = ?,
                    student_strengths = ?,
                    student_areas_for_improvement = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $data['instructor_notes'] ?? null,
                $data['strengths'] ?? null,
                $data['areas_for_improvement'] ?? null,
                (int)$data['record_id']
            ]);

            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
