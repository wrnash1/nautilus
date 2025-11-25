<?php

namespace App\Services\Courses;

use App\Core\Database;
use PDO;
use App\Core\Logger;
use Exception;

/**
 * Course Enrollment Service
 * Handles student enrollment in courses from POS and direct enrollment
 */
class EnrollmentService
{
    private PDO $db;
    private Logger $logger;

    public function __construct()
    {
        $this->db = Database::getPdo();
        $this->logger = new Logger();
    }

    /**
     * Enroll a customer in a course schedule from POS transaction
     *
     * @param int $customerId Customer ID
     * @param int $scheduleId Course schedule ID
     * @param float $amountPaid Amount paid for the course
     * @param int|null $transactionId Related POS transaction ID
     * @return int Enrollment ID
     * @throws Exception
     */
    public function enrollFromTransaction(int $customerId, int $scheduleId, float $amountPaid, ?int $transactionId = null): int
    {
        try {
            $this->db->beginTransaction();

            // Verify schedule exists and has capacity
            $schedule = $this->getSchedule($scheduleId);
            if (!$schedule) {
                throw new Exception('Course schedule not found');
            }

            if ($schedule['current_enrollment'] >= $schedule['max_students']) {
                throw new Exception('Course is full');
            }

            // Check if customer is already enrolled
            $existing = Database::fetchOne(
                "SELECT id FROM course_enrollments
                 WHERE schedule_id = ? AND customer_id = ?",
                [$scheduleId, $customerId]
            );

            if ($existing) {
                throw new Exception('Customer is already enrolled in this course');
            }

            // Determine payment status
            $coursePrice = (float)$schedule['price'];
            $paymentStatus = 'paid';
            if ($amountPaid < $coursePrice) {
                $paymentStatus = 'partial';
            } elseif ($amountPaid == 0) {
                $paymentStatus = 'pending';
            }

            // Create enrollment
            $stmt = $this->db->prepare("
                INSERT INTO course_enrollments
                (schedule_id, customer_id, enrollment_date, status, amount_paid, payment_status, notes)
                VALUES (?, ?, CURDATE(), 'enrolled', ?, ?, ?)
            ");

            $notes = $transactionId ? "Enrolled via POS Transaction #{$transactionId}" : "Enrolled via POS";

            $stmt->execute([
                $scheduleId,
                $customerId,
                $amountPaid,
                $paymentStatus,
                $notes
            ]);

            $enrollmentId = (int)$this->db->lastInsertId();

            // Update course schedule enrollment count
            Database::execute(
                "UPDATE course_schedules
                 SET current_enrollment = current_enrollment + 1
                 WHERE id = ?",
                [$scheduleId]
            );

            $this->db->commit();

            $this->logger->info('Course enrollment created from POS', [
                'enrollment_id' => $enrollmentId,
                'customer_id' => $customerId,
                'schedule_id' => $scheduleId,
                'transaction_id' => $transactionId
            ]);

            return $enrollmentId;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to create course enrollment', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
                'schedule_id' => $scheduleId
            ]);
            throw $e;
        }
    }

    /**
     * Transfer student to a different class schedule
     *
     * @param int $enrollmentId Enrollment ID to transfer
     * @param int $newScheduleId New schedule ID
     * @param string $reason Reason for transfer
     * @param int $staffId Staff member performing transfer
     * @return bool Success
     * @throws Exception
     */
    public function transferToSchedule(int $enrollmentId, int $newScheduleId, string $reason, int $staffId): bool
    {
        try {
            $this->db->beginTransaction();

            // Get current enrollment
            $enrollment = Database::fetchOne(
                "SELECT e.*, cs.course_id, cs.current_enrollment as old_enrollment
                 FROM course_enrollments e
                 JOIN course_schedules cs ON e.schedule_id = cs.id
                 WHERE e.id = ?",
                [$enrollmentId]
            );

            if (!$enrollment) {
                throw new Exception('Enrollment not found');
            }

            $oldScheduleId = $enrollment['schedule_id'];

            // Verify new schedule
            $newSchedule = $this->getSchedule($newScheduleId);
            if (!$newSchedule) {
                throw new Exception('New schedule not found');
            }

            // Must be same course
            if ($newSchedule['course_id'] != $enrollment['course_id']) {
                throw new Exception('Cannot transfer to a different course. New schedule must be for the same course.');
            }

            // Check capacity
            if ($newSchedule['current_enrollment'] >= $newSchedule['max_students']) {
                throw new Exception('New schedule is full');
            }

            // Update enrollment
            Database::execute(
                "UPDATE course_enrollments
                 SET schedule_id = ?,
                     notes = CONCAT(COALESCE(notes, ''), '\nTransferred from schedule #{$oldScheduleId} on ', NOW(), ' by staff #{$staffId}. Reason: {$reason}')
                 WHERE id = ?",
                [$newScheduleId, $enrollmentId]
            );

            // Update enrollment counts
            Database::execute(
                "UPDATE course_schedules
                 SET current_enrollment = current_enrollment - 1
                 WHERE id = ?",
                [$oldScheduleId]
            );

            Database::execute(
                "UPDATE course_schedules
                 SET current_enrollment = current_enrollment + 1
                 WHERE id = ?",
                [$newScheduleId]
            );

            $this->db->commit();

            $this->logger->info('Student transferred to new schedule', [
                'enrollment_id' => $enrollmentId,
                'old_schedule_id' => $oldScheduleId,
                'new_schedule_id' => $newScheduleId,
                'staff_id' => $staffId,
                'reason' => $reason
            ]);

            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error('Failed to transfer student', [
                'error' => $e->getMessage(),
                'enrollment_id' => $enrollmentId
            ]);
            throw $e;
        }
    }

    /**
     * Get course schedule with details
     */
    private function getSchedule(int $scheduleId): ?array
    {
        return Database::fetchOne(
            "SELECT cs.*, c.name as course_name, c.price, c.course_code,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name
             FROM course_schedules cs
             JOIN courses c ON cs.course_id = c.id
             JOIN users u ON cs.instructor_id = u.id
             WHERE cs.id = ?",
            [$scheduleId]
        );
    }

    /**
     * Get roster for a course schedule (for instructors)
     *
     * @param int $scheduleId Schedule ID
     * @return array List of enrolled students with details
     */
    public function getScheduleRoster(int $scheduleId): array
    {
        return Database::fetchAll(
            "SELECT e.id as enrollment_id,
                    e.enrollment_date,
                    e.status,
                    e.payment_status,
                    e.amount_paid,
                    e.final_grade,
                    e.certification_number,
                    e.completion_date,
                    c.id as customer_id,
                    c.first_name,
                    c.last_name,
                    c.email,
                    c.phone,
                    c.emergency_contact_name,
                    c.emergency_contact_phone,
                    GROUP_CONCAT(DISTINCT cert.name SEPARATOR ', ') as certifications
             FROM course_enrollments e
             JOIN customers c ON e.customer_id = c.id
             LEFT JOIN customer_certifications cc ON c.id = cc.customer_id
             LEFT JOIN certifications cert ON cc.certification_id = cert.id
             WHERE e.schedule_id = ?
             GROUP BY e.id, c.id
             ORDER BY e.enrollment_date ASC",
            [$scheduleId]
        );
    }

    /**
     * Get available schedules for a course
     *
     * @param int $courseId Course ID
     * @return array Available schedules
     */
    public function getAvailableSchedules(int $courseId): array
    {
        return Database::fetchAll(
            "SELECT cs.*,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                    c.name as course_name,
                    (cs.max_students - cs.current_enrollment) as available_spots
             FROM course_schedules cs
             JOIN courses c ON cs.course_id = c.id
             JOIN users u ON cs.instructor_id = u.id
             WHERE cs.course_id = ?
               AND cs.status = 'scheduled'
               AND cs.start_date >= CURDATE()
               AND cs.current_enrollment < cs.max_students
             ORDER BY cs.start_date ASC",
            [$courseId]
        );
    }

    /**
     * Get customer's course history
     */
    public function getCustomerCourseHistory(int $customerId): array
    {
        return Database::fetchAll(
            "SELECT e.*,
                    c.name as course_name,
                    c.course_code,
                    cs.start_date,
                    cs.end_date,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name
             FROM course_enrollments e
             JOIN course_schedules cs ON e.schedule_id = cs.id
             JOIN courses c ON cs.course_id = c.id
             JOIN users u ON cs.instructor_id = u.id
             WHERE e.customer_id = ?
             ORDER BY e.enrollment_date DESC",
            [$customerId]
        );
    }

    /**
     * Update enrollment status (for instructors/managers)
     */
    public function updateEnrollmentStatus(
        int $enrollmentId,
        string $status,
        ?string $finalGrade = null,
        ?string $certificationNumber = null
    ): bool {
        try {
            $updates = ['status = ?'];
            $params = [$status];

            if ($finalGrade !== null) {
                $updates[] = 'final_grade = ?';
                $params[] = $finalGrade;
            }

            if ($certificationNumber !== null) {
                $updates[] = 'certification_number = ?';
                $params[] = $certificationNumber;
            }

            if ($status === 'completed') {
                $updates[] = 'completion_date = CURDATE()';
            }

            $params[] = $enrollmentId;

            $sql = "UPDATE course_enrollments SET " . implode(', ', $updates) . " WHERE id = ?";
            Database::execute($sql, $params);

            return true;

        } catch (Exception $e) {
            $this->logger->error('Failed to update enrollment status', [
                'error' => $e->getMessage(),
                'enrollment_id' => $enrollmentId
            ]);
            return false;
        }
    }
}
