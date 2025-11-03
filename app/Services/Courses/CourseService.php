<?php

namespace App\Services\Courses;

use App\Core\Database;
use App\Services\Courses\CourseEnrollmentWorkflow;

class CourseService
{
    private CourseEnrollmentWorkflow $workflow;

    public function __construct()
    {
        $this->workflow = new CourseEnrollmentWorkflow();
    }
    public function getCourseList(array $filters = []): array
    {
        $sql = "SELECT c.* FROM courses c WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.course_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY c.name ASC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getCourseById(int $id): ?array
    {
        $sql = "SELECT * FROM courses WHERE id = ?";
        return Database::fetchOne($sql, [$id]);
    }
    
    public function createCourse(array $data): int
    {
        $sql = "INSERT INTO courses 
                (course_code, name, description, duration_days, max_students, prerequisites, price, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $data['course_code'],
            $data['name'],
            $data['description'] ?? null,
            $data['duration_days'],
            $data['max_students'] ?? 10,
            $data['prerequisites'] ?? null,
            $data['price'],
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateCourse(int $id, array $data): bool
    {
        $sql = "UPDATE courses 
                SET name = ?, description = ?, duration_days = ?, max_students = ?, 
                    prerequisites = ?, price = ?, updated_by = ?
                WHERE id = ?";
        
        return Database::execute($sql, [
            $data['name'],
            $data['description'] ?? null,
            $data['duration_days'],
            $data['max_students'],
            $data['prerequisites'] ?? null,
            $data['price'],
            $_SESSION['user_id'] ?? 1,
            $id
        ]);
    }
    
    public function deleteCourse(int $id): bool
    {
        $sql = "DELETE FROM courses WHERE id = ?";
        return Database::execute($sql, [$id]);
    }
    
    public function getScheduleList(array $filters = []): array
    {
        $sql = "SELECT cs.*, c.name as course_name, c.course_code,
                       CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                       (SELECT COUNT(*) FROM course_enrollments WHERE schedule_id = cs.id) as enrolled_count
                FROM course_schedules cs
                LEFT JOIN courses c ON cs.course_id = c.id
                LEFT JOIN users u ON cs.instructor_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['course_id'])) {
            $sql .= " AND cs.course_id = ?";
            $params[] = $filters['course_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND cs.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY cs.start_date DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getScheduleById(int $id): ?array
    {
        $sql = "SELECT cs.*, c.name as course_name, c.course_code, c.max_students,
                       CONCAT(u.first_name, ' ', u.last_name) as instructor_name
                FROM course_schedules cs
                LEFT JOIN courses c ON cs.course_id = c.id
                LEFT JOIN users u ON cs.instructor_id = u.id
                WHERE cs.id = ?";
        
        return Database::fetchOne($sql, [$id]);
    }
    
    public function createSchedule(array $data): int
    {
        $sql = "INSERT INTO course_schedules 
                (course_id, instructor_id, start_date, end_date, location, status, max_students, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        Database::execute($sql, [
            $data['course_id'],
            $data['instructor_id'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['location'] ?? null,
            'scheduled',
            $data['max_students'] ?? 10,
            $_SESSION['user_id'] ?? 1
        ]);
        
        return Database::lastInsertId();
    }
    
    public function updateSchedule(int $id, array $data): bool
    {
        $sql = "UPDATE course_schedules 
                SET instructor_id = ?, start_date = ?, end_date = ?, location = ?, 
                    status = ?, max_students = ?, updated_by = ?
                WHERE id = ?";
        
        return Database::execute($sql, [
            $data['instructor_id'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['location'] ?? null,
            $data['status'],
            $data['max_students'],
            $_SESSION['user_id'] ?? 1,
            $id
        ]);
    }
    
    public function getEnrollmentList(array $filters = []): array
    {
        $sql = "SELECT ce.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as student_name,
                       c.email as student_email,
                       co.name as course_name,
                       cs.start_date
                FROM course_enrollments ce
                LEFT JOIN customers c ON ce.customer_id = c.id
                LEFT JOIN course_schedules cs ON ce.schedule_id = cs.id
                LEFT JOIN courses co ON cs.course_id = co.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND ce.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['schedule_id'])) {
            $sql .= " AND ce.schedule_id = ?";
            $params[] = $filters['schedule_id'];
        }
        
        $sql .= " ORDER BY ce.created_at DESC";
        
        return Database::fetchAll($sql, $params);
    }
    
    public function getEnrollmentById(int $id): ?array
    {
        $sql = "SELECT ce.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as student_name,
                       c.email as student_email,
                       c.phone as student_phone,
                       co.name as course_name,
                       cs.start_date,
                       cs.end_date
                FROM course_enrollments ce
                LEFT JOIN customers c ON ce.customer_id = c.id
                LEFT JOIN course_schedules cs ON ce.schedule_id = cs.id
                LEFT JOIN courses co ON cs.course_id = co.id
                WHERE ce.id = ?";
        
        return Database::fetchOne($sql, [$id]);
    }
    
    public function enrollStudent(int $scheduleId, int $customerId, float $amountPaid = 0): int
    {
        // Get course details
        $schedule = $this->getScheduleById($scheduleId);
        if (!$schedule) {
            throw new \Exception('Schedule not found');
        }

        // Create enrollment record
        $sql = "INSERT INTO course_enrollments
                (schedule_id, customer_id, status, enrollment_date, amount_paid, payment_status, created_by)
                VALUES (?, ?, 'enrolled', NOW(), ?, ?, ?)";

        $paymentStatus = $amountPaid > 0 ? 'partial' : 'pending';

        Database::execute($sql, [
            $scheduleId,
            $customerId,
            $amountPaid,
            $paymentStatus,
            $_SESSION['user_id'] ?? 1
        ]);

        $enrollmentId = Database::lastInsertId();

        // Trigger automated workflow
        $this->workflow->processEnrollment($enrollmentId);

        return $enrollmentId;
    }
    
    public function updateEnrollmentStatus(int $id, string $status): bool
    {
        $sql = "UPDATE course_enrollments SET status = ?, updated_by = ? WHERE id = ?";
        return Database::execute($sql, [$status, $_SESSION['user_id'] ?? 1, $id]);
    }
    
    public function recordAttendance(int $enrollmentId, array $data): bool
    {
        $sql = "INSERT INTO course_attendance 
                (enrollment_id, date, status, notes, created_by)
                VALUES (?, ?, ?, ?, ?)";
        
        return Database::execute($sql, [
            $enrollmentId,
            $data['date'],
            $data['status'],
            $data['notes'] ?? null,
            $_SESSION['user_id'] ?? 1
        ]);
    }
    
    public function submitCertification(int $enrollmentId): bool
    {
        $sql = "UPDATE course_enrollments 
                SET certification_number = ?, certification_date = NOW(), status = 'certified'
                WHERE id = ?";
        
        $certNumber = 'CERT-' . date('Ymd') . '-' . rand(1000, 9999);
        
        return Database::execute($sql, [$certNumber, $enrollmentId]);
    }
    
    public function getScheduleEnrollments(int $scheduleId): array
    {
        return $this->getEnrollmentList(['schedule_id' => $scheduleId]);
    }
    
    public function getEnrollmentAttendance(int $enrollmentId): array
    {
        return Database::fetchAll(
            "SELECT * FROM course_attendance WHERE enrollment_id = ? ORDER BY session_date ASC",
            [$enrollmentId]
        ) ?? [];
    }
    
    public function markAttendance(int $enrollmentId, array $data): bool
    {
        $existing = Database::fetchOne(
            "SELECT id FROM course_attendance WHERE enrollment_id = ? AND session_date = ?",
            [$enrollmentId, $data['session_date']]
        );
        
        $attended = isset($data['attended']) ? 1 : 0;
        
        if ($existing) {
            Database::execute(
                "UPDATE course_attendance SET attended = ?, performance_notes = ? WHERE id = ?",
                [$attended, $data['notes'] ?? null, $existing['id']]
            );
        } else {
            Database::execute(
                "INSERT INTO course_attendance (enrollment_id, session_date, session_type, attended, performance_notes)
                 VALUES (?, ?, ?, ?, ?)",
                [$enrollmentId, $data['session_date'], $data['session_type'], $attended, $data['notes'] ?? null]
            );
        }
        
        return true;
    }
    
    public function updateGrade(int $enrollmentId, string $grade, string $certNumber = null): bool
    {
        Database::execute(
            "UPDATE course_enrollments SET final_grade = ?, certification_number = ?, 
             completion_date = NOW(), status = 'completed', updated_at = NOW()
             WHERE id = ?",
            [$grade, $certNumber, $enrollmentId]
        );
        return true;
    }
}
