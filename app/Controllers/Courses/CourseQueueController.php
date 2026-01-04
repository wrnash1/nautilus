<?php

namespace App\Controllers\Courses;

use App\Core\Database;
use App\Models\Customer;

class CourseQueueController
{
    public function addToQueue()
    {
        if (!hasPermission('pos.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        $courseId = (int)($_POST['course_id'] ?? 0);
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $notes = sanitizeInput($_POST['notes'] ?? 'Added from POS');

        if ($courseId <= 0 || $customerId <= 0) {
            jsonResponse(['error' => 'Invalid course or customer'], 400);
        }

        // Check if course exists
        $course = Database::fetchOne("SELECT id FROM courses WHERE id = ?", [$courseId]);
        if (!$course) {
            jsonResponse(['error' => 'Course not found'], 404);
        }

        // Check for duplicate pending entry
        $existing = Database::fetchOne(
            "SELECT id FROM course_interest_queue 
             WHERE course_id = ? AND customer_id = ? AND status = 'pending'",
            [$courseId, $customerId]
        );

        if ($existing) {
            jsonResponse(['success' => true, 'message' => 'Customer is already in the queue for this course or duplicates allowed? Let\'s just return success.'], 200);
            return;
        }

        try {
            Database::query(
                "INSERT INTO course_interest_queue (course_id, customer_id, notes, status) VALUES (?, ?, ?, 'pending')",
                [$courseId, $customerId, $notes]
            );

            jsonResponse(['success' => true, 'message' => 'Added to interest queue']);
        } catch (\Exception $e) {
            error_log("Failed to add to queue: " . $e->getMessage());
            jsonResponse(['error' => 'Database error'], 500);
        }
    }
}
