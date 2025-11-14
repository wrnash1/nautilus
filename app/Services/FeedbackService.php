<?php

namespace App\Services;

use App\Core\Database;

/**
 * Staff Feedback Service
 * Manages bug reports, feature requests, and staff suggestions
 */
class FeedbackService
{
    /**
     * Submit new feedback
     */
    public static function submit(array $data): ?int
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return null;
        }

        $feedbackId = Database::execute(
            "INSERT INTO staff_feedback (
                tenant_id, user_id, feedback_type, priority, category,
                title, description, steps_to_reproduce, expected_behavior,
                actual_behavior, screenshots, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $tenantId,
                $userId,
                $data['feedback_type'],
                $data['priority'] ?? 'medium',
                $data['category'] ?? null,
                $data['title'],
                $data['description'],
                $data['steps_to_reproduce'] ?? null,
                $data['expected_behavior'] ?? null,
                $data['actual_behavior'] ?? null,
                $data['screenshots'] ?? null
            ]
        );

        return $feedbackId;
    }

    /**
     * Get all feedback items
     */
    public static function getAll(array $filters = []): array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        $where = ["f.tenant_id = ?"];
        $params = [$tenantId];

        if (!empty($filters['status'])) {
            $where[] = "f.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where[] = "f.feedback_type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['priority'])) {
            $where[] = "f.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['category'])) {
            $where[] = "f.category = ?";
            $params[] = $filters['category'];
        }

        $whereClause = implode(' AND ', $where);

        return Database::fetchAll(
            "SELECT f.*,
                    CONCAT(u.first_name, ' ', u.last_name) as submitted_by_name,
                    u.email as submitted_by_email,
                    (SELECT COUNT(*) FROM feedback_votes WHERE feedback_id = f.id) as vote_count,
                    (SELECT COUNT(*) FROM feedback_comments WHERE feedback_id = f.id) as comment_count
             FROM staff_feedback f
             JOIN users u ON f.user_id = u.id
             WHERE $whereClause
             ORDER BY
                CASE f.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'medium' THEN 3
                    WHEN 'low' THEN 4
                END,
                f.votes DESC,
                f.created_at DESC",
            $params
        );
    }

    /**
     * Get feedback by ID
     */
    public static function getById(int $id): ?array
    {
        $feedback = Database::fetchOne(
            "SELECT f.*,
                    CONCAT(u.first_name, ' ', u.last_name) as submitted_by_name,
                    u.email as submitted_by_email,
                    (SELECT COUNT(*) FROM feedback_votes WHERE feedback_id = f.id) as vote_count
             FROM staff_feedback f
             JOIN users u ON f.user_id = u.id
             WHERE f.id = ?",
            [$id]
        );

        return $feedback ?: null;
    }

    /**
     * Vote on feedback
     */
    public static function vote(int $feedbackId): bool
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return false;
        }

        try {
            // Insert vote
            Database::execute(
                "INSERT INTO feedback_votes (feedback_id, user_id, created_at)
                 VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE created_at = NOW()",
                [$feedbackId, $userId]
            );

            // Update vote count
            Database::execute(
                "UPDATE staff_feedback
                 SET votes = (SELECT COUNT(*) FROM feedback_votes WHERE feedback_id = ?)
                 WHERE id = ?",
                [$feedbackId, $feedbackId]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove vote
     */
    public static function unvote(int $feedbackId): bool
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return false;
        }

        Database::execute(
            "DELETE FROM feedback_votes WHERE feedback_id = ? AND user_id = ?",
            [$feedbackId, $userId]
        );

        // Update vote count
        Database::execute(
            "UPDATE staff_feedback
             SET votes = (SELECT COUNT(*) FROM feedback_votes WHERE feedback_id = ?)
             WHERE id = ?",
            [$feedbackId, $feedbackId]
        );

        return true;
    }

    /**
     * Add comment to feedback
     */
    public static function addComment(int $feedbackId, string $comment): ?int
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return null;
        }

        $isAdmin = hasPermission('feedback.manage');

        return Database::execute(
            "INSERT INTO feedback_comments (feedback_id, user_id, comment, is_admin, created_at)
             VALUES (?, ?, ?, ?, NOW())",
            [$feedbackId, $userId, $comment, $isAdmin ? 1 : 0]
        );
    }

    /**
     * Get comments for feedback
     */
    public static function getComments(int $feedbackId): array
    {
        return Database::fetchAll(
            "SELECT c.*,
                    CONCAT(u.first_name, ' ', u.last_name) as user_name,
                    u.email as user_email
             FROM feedback_comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.feedback_id = ?
             ORDER BY c.created_at ASC",
            [$feedbackId]
        );
    }

    /**
     * Update feedback status
     */
    public static function updateStatus(int $feedbackId, string $status, ?string $adminNotes = null): bool
    {
        $params = [$status];
        $sql = "UPDATE staff_feedback SET status = ?, updated_at = NOW()";

        if ($adminNotes !== null) {
            $sql .= ", admin_notes = ?";
            $params[] = $adminNotes;
        }

        if ($status === 'completed') {
            $sql .= ", completed_at = NOW()";
        }

        $sql .= " WHERE id = ?";
        $params[] = $feedbackId;

        return Database::execute($sql, $params) > 0;
    }

    /**
     * Get feedback statistics
     */
    public static function getStats(): array
    {
        $tenantId = $_SESSION['tenant_id'] ?? 1;

        return Database::fetchOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN feedback_type = 'bug' THEN 1 ELSE 0 END) as bugs,
                SUM(CASE WHEN feedback_type = 'feature_request' THEN 1 ELSE 0 END) as features,
                SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent
             FROM staff_feedback
             WHERE tenant_id = ?",
            [$tenantId]
        );
    }

    /**
     * Get user's feedback
     */
    public static function getUserFeedback(int $userId): array
    {
        return Database::fetchAll(
            "SELECT f.*,
                    (SELECT COUNT(*) FROM feedback_votes WHERE feedback_id = f.id) as vote_count,
                    (SELECT COUNT(*) FROM feedback_comments WHERE feedback_id = f.id) as comment_count
             FROM staff_feedback f
             WHERE f.user_id = ?
             ORDER BY f.created_at DESC",
            [$userId]
        );
    }
}
