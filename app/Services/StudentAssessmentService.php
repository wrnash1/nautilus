<?php

namespace App\Services;

use PDO;
use Exception;

/**
 * Student Assessment Service
 * Handles PADI-compliant student assessment, skills tracking, and course records
 */
class StudentAssessmentService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new course student record
     */
    public function createStudentRecord(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO course_student_records (
                enrollment_id, instructor_id, form_type, knowledge_status,
                confined_water_status, open_water_status, overall_status
            ) VALUES (?, ?, ?, 'pending', 'pending', 'pending', 'enrolled')
        ");

        $stmt->execute([
            $data['enrollment_id'],
            $data['instructor_id'] ?? null,
            $data['form_type'] ?? 'course_record'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get student record by enrollment ID
     */
    public function getStudentRecordByEnrollment(int $enrollmentId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT csr.*,
                   c.first_name, c.last_name, c.email, c.phone,
                   u.first_name as instructor_first_name,
                   u.last_name as instructor_last_name,
                   ce.course_id, ce.schedule_id,
                   co.name as course_name
            FROM course_student_records csr
            JOIN course_enrollments ce ON csr.enrollment_id = ce.id
            JOIN customers c ON ce.customer_id = c.id
            LEFT JOIN users u ON csr.instructor_id = u.id
            LEFT JOIN courses co ON ce.course_id = co.id
            WHERE csr.enrollment_id = ?
        ");

        $stmt->execute([$enrollmentId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        return $record ?: null;
    }

    /**
     * Get all students for an instructor
     */
    public function getInstructorStudents(int $instructorId, ??string $status = null): array
    {
        $sql = "
            SELECT csr.*,
                   c.first_name, c.last_name, c.email, c.phone, c.photo_path,
                   ce.enrolled_at,
                   co.name as course_name,
                   cs.schedule_date, cs.start_time, cs.end_time
            FROM course_student_records csr
            JOIN course_enrollments ce ON csr.enrollment_id = ce.id
            JOIN customers c ON ce.customer_id = c.id
            LEFT JOIN courses co ON ce.course_id = co.id
            LEFT JOIN course_schedules cs ON ce.schedule_id = cs.id
            WHERE csr.instructor_id = ?
        ";

        $params = [$instructorId];

        if ($status) {
            $sql .= " AND csr.overall_status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY cs.schedule_date DESC, c.last_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record a skill assessment
     */
    public function recordSkillAssessment(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO student_skills_assessment (
                record_id, session_type, session_number, session_date, session_location,
                skill_name, skill_code, skill_category, performance, pass, attempts,
                assessed_by, assessment_notes, remediation_needed, remediation_notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['record_id'],
            $data['session_type'],
            $data['session_number'],
            $data['session_date'] ?? date('Y-m-d'),
            $data['session_location'] ?? null,
            $data['skill_name'],
            $data['skill_code'] ?? null,
            $data['skill_category'] ?? null,
            $data['performance'] ?? 'not_performed',
            $data['pass'] ?? false,
            $data['attempts'] ?? 1,
            $data['assessed_by'],
            $data['assessment_notes'] ?? null,
            $data['remediation_needed'] ?? false,
            $data['remediation_notes'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a skill assessment
     */
    public function updateSkillAssessment(int $assessmentId, array $data): bool
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, ['performance', 'pass', 'attempts', 'assessment_notes', 'remediation_needed', 'remediation_notes'])) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $assessmentId;

        $stmt = $this->db->prepare("
            UPDATE student_skills_assessment
            SET " . implode(', ', $fields) . "
            WHERE id = ?
        ");

        return $stmt->execute($values);
    }

    /**
     * Get skills assessment for a session
     */
    public function getSessionSkills(int $recordId, string $sessionType, int $sessionNumber): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM student_skills_assessment
            WHERE record_id = ? AND session_type = ? AND session_number = ?
            ORDER BY skill_code ASC
        ");

        $stmt->execute([$recordId, $sessionType, $sessionNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get standard PADI skills for a session
     */
    public function getStandardSkills(string $courseType, string $sessionType, int $sessionNumber): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM padi_standard_skills
            WHERE course_type = ? AND session_type = ? AND session_number = ?
            ORDER BY display_order ASC
        ");

        $stmt->execute([$courseType, $sessionType, $sessionNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Initialize skills for a session from standard PADI skills
     */
    public function initializeSessionSkills(int $recordId, string $sessionType, int $sessionNumber, string $courseType = 'open_water'): int
    {
        // Get standard skills
        $standardSkills = $this->getStandardSkills($courseType, $sessionType, $sessionNumber);

        if (empty($standardSkills)) {
            return 0;
        }

        // Check if already initialized
        $existingSkills = $this->getSessionSkills($recordId, $sessionType, $sessionNumber);
        if (!empty($existingSkills)) {
            return count($existingSkills);
        }

        // Get instructor from record
        $stmt = $this->db->prepare("SELECT instructor_id FROM course_student_records WHERE id = ?");
        $stmt->execute([$recordId]);
        $instructorId = $stmt->fetchColumn();

        // Create assessment records for each standard skill
        $count = 0;
        foreach ($standardSkills as $skill) {
            $this->recordSkillAssessment([
                'record_id' => $recordId,
                'session_type' => $sessionType,
                'session_number' => $sessionNumber,
                'skill_name' => $skill['skill_name'],
                'skill_code' => $skill['skill_code'],
                'skill_category' => $skill['skill_category'],
                'performance' => 'not_performed',
                'pass' => false,
                'assessed_by' => $instructorId
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Update session status based on skills completed
     */
    public function updateSessionStatus(int $recordId, string $sessionType, int $sessionNumber): bool
    {
        $skills = $this->getSessionSkills($recordId, $sessionType, $sessionNumber);

        if (empty($skills)) {
            return false;
        }

        $totalSkills = count($skills);
        $passedSkills = count(array_filter($skills, fn($s) => $s['pass']));

        $allPassed = ($passedSkills === $totalSkills);

        // Update the JSON field in course_student_records
        $field = $sessionType . '_sessions';
        $stmt = $this->db->prepare("
            SELECT $field FROM course_student_records WHERE id = ?
        ");
        $stmt->execute([$recordId]);
        $sessions = json_decode($stmt->fetchColumn() ?? '[]', true);

        $sessions[$sessionNumber] = [
            'completed' => $allPassed,
            'date' => date('Y-m-d'),
            'skills_total' => $totalSkills,
            'skills_passed' => $passedSkills
        ];

        $stmt = $this->db->prepare("
            UPDATE course_student_records
            SET $field = ?
            WHERE id = ?
        ");

        return $stmt->execute([json_encode($sessions), $recordId]);
    }

    /**
     * Update overall student status
     */
    public function updateOverallStatus(int $recordId): bool
    {
        $stmt = $this->db->prepare("
            SELECT knowledge_status, confined_water_status, open_water_status
            FROM course_student_records
            WHERE id = ?
        ");
        $stmt->execute([$recordId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return false;
        }

        // Determine overall status
        $statuses = [
            $record['knowledge_status'],
            $record['confined_water_status'],
            $record['open_water_status']
        ];

        if (in_array('failed', $statuses)) {
            $overallStatus = 'failed';
        } elseif (array_count_values($statuses)['completed'] === 3) {
            $overallStatus = 'completed';
        } elseif (in_array('in_progress', $statuses) || in_array('completed', $statuses)) {
            $overallStatus = 'in_training';
        } else {
            $overallStatus = 'enrolled';
        }

        $stmt = $this->db->prepare("
            UPDATE course_student_records
            SET overall_status = ?,
                completion_date = CASE WHEN ? = 'completed' THEN CURDATE() ELSE completion_date END
            WHERE id = ?
        ");

        return $stmt->execute([$overallStatus, $overallStatus, $recordId]);
    }

    /**
     * Create a referral
     */
    public function createReferral(int $recordId, array $referralData): bool
    {
        $stmt = $this->db->prepare("
            UPDATE course_student_records
            SET is_referral = 1,
                referral_shop_name = ?,
                referral_shop_location = ?,
                referral_shop_number = ?,
                referral_instructor_name = ?,
                referral_instructor_number = ?,
                referred_date = ?,
                referral_notes = ?,
                referral_portions = ?,
                overall_status = 'referred'
            WHERE id = ?
        ");

        return $stmt->execute([
            $referralData['shop_name'],
            $referralData['shop_location'] ?? null,
            $referralData['shop_number'] ?? null,
            $referralData['instructor_name'] ?? null,
            $referralData['instructor_number'] ?? null,
            $referralData['referred_date'] ?? date('Y-m-d'),
            $referralData['notes'] ?? null,
            json_encode($referralData['portions'] ?? []),
            $recordId
        ]);
    }

    /**
     * Get student progress summary
     */
    public function getStudentProgress(int $recordId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM course_student_records WHERE id = ?");
        $stmt->execute([$recordId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return [];
        }

        // Get skills breakdown
        $stmt = $this->db->prepare("
            SELECT session_type, session_number,
                   COUNT(*) as total_skills,
                   SUM(CASE WHEN pass = 1 THEN 1 ELSE 0 END) as passed_skills
            FROM student_skills_assessment
            WHERE record_id = ?
            GROUP BY session_type, session_number
            ORDER BY session_type, session_number
        ");
        $stmt->execute([$recordId]);
        $skillsBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'record' => $record,
            'skills_breakdown' => $skillsBreakdown,
            'knowledge_progress' => [
                'status' => $record['knowledge_status'],
                'exam_score' => $record['final_exam_score'],
                'completion_date' => $record['knowledge_completion_date']
            ],
            'confined_water_progress' => [
                'status' => $record['confined_water_status'],
                'completion_date' => $record['confined_water_completion_date']
            ],
            'open_water_progress' => [
                'status' => $record['open_water_status'],
                'completion_date' => $record['open_water_completion_date']
            ]
        ];
    }

    /**
     * Get students needing attention (remediation, incomplete, etc.)
     */
    public function getStudentsNeedingAttention(int $instructorId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT csr.*,
                   c.first_name, c.last_name, c.email, c.phone,
                   co.name as course_name,
                   COUNT(CASE WHEN ssa.remediation_needed = 1 THEN 1 END) as remediation_count
            FROM course_student_records csr
            JOIN course_enrollments ce ON csr.enrollment_id = ce.id
            JOIN customers c ON ce.customer_id = c.id
            LEFT JOIN courses co ON ce.course_id = co.id
            LEFT JOIN student_skills_assessment ssa ON csr.id = ssa.record_id
            WHERE csr.instructor_id = ?
              AND csr.overall_status IN ('enrolled', 'in_training')
            GROUP BY csr.id
            HAVING remediation_count > 0
            ORDER BY remediation_count DESC
        ");

        $stmt->execute([$instructorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
