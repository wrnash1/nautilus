<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\FeedbackService;

/**
 * Feedback Controller
 * Staff feedback, bug reports, and feature requests
 */
class FeedbackController extends Controller
{
    /**
     * View all feedback
     */
    public function index()
    {
        $this->checkPermission('feedback.submit');

        $filters = [
            'status' => $_GET['status'] ?? null,
            'type' => $_GET['type'] ?? null,
            'priority' => $_GET['priority'] ?? null,
            'category' => $_GET['category'] ?? null
        ];

        $feedback = FeedbackService::getAll($filters);
        $stats = FeedbackService::getStats();

        // Check if user can manage feedback
        $canManage = hasPermission('feedback.manage');

        $data = [
            'feedback' => $feedback,
            'stats' => $stats,
            'filters' => $filters,
            'can_manage' => $canManage,
            'pageTitle' => 'Staff Feedback',
            'activeMenu' => 'feedback'
        ];

        $this->view('feedback/index', $data);
    }

    /**
     * Show feedback details
     */
    public function show(int $id)
    {
        $this->checkPermission('feedback.submit');

        $feedback = FeedbackService::getById($id);

        if (!$feedback) {
            $_SESSION['flash_error'] = 'Feedback not found';
            $this->redirect('/store/feedback');
            return;
        }

        $comments = FeedbackService::getComments($id);
        $canManage = hasPermission('feedback.manage');

        $data = [
            'feedback' => $feedback,
            'comments' => $comments,
            'can_manage' => $canManage,
            'pageTitle' => $feedback['title'],
            'activeMenu' => 'feedback'
        ];

        $this->view('feedback/show', $data);
    }

    /**
     * Show submission form
     */
    public function create()
    {
        $this->checkPermission('feedback.submit');

        $data = [
            'pageTitle' => 'Submit Feedback',
            'activeMenu' => 'feedback'
        ];

        $this->view('feedback/create', $data);
    }

    /**
     * Submit new feedback
     */
    public function store()
    {
        $this->checkPermission('feedback.submit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/feedback/create');
            return;
        }

        $data = [
            'feedback_type' => $_POST['feedback_type'] ?? 'feature_request',
            'priority' => $_POST['priority'] ?? 'medium',
            'category' => $_POST['category'] ?? null,
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'steps_to_reproduce' => $_POST['steps_to_reproduce'] ?? null,
            'expected_behavior' => $_POST['expected_behavior'] ?? null,
            'actual_behavior' => $_POST['actual_behavior'] ?? null
        ];

        // Validate
        if (empty($data['title']) || empty($data['description'])) {
            $_SESSION['flash_error'] = 'Title and description are required';
            $this->redirect('/store/feedback/create');
            return;
        }

        $feedbackId = FeedbackService::submit($data);

        if ($feedbackId) {
            $_SESSION['flash_success'] = 'Thank you! Your feedback has been submitted.';
            $this->redirect('/store/feedback/' . $feedbackId);
        } else {
            $_SESSION['flash_error'] = 'Failed to submit feedback';
            $this->redirect('/store/feedback/create');
        }
    }

    /**
     * Vote on feedback
     */
    public function vote(int $id)
    {
        $this->checkPermission('feedback.vote');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/feedback');
            return;
        }

        if (FeedbackService::vote($id)) {
            $_SESSION['flash_success'] = 'Vote recorded';
        } else {
            $_SESSION['flash_error'] = 'Failed to vote';
        }

        $this->redirect('/store/feedback/' . $id);
    }

    /**
     * Remove vote
     */
    public function unvote(int $id)
    {
        $this->checkPermission('feedback.vote');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/feedback');
            return;
        }

        if (FeedbackService::unvote($id)) {
            $_SESSION['flash_success'] = 'Vote removed';
        } else {
            $_SESSION['flash_error'] = 'Failed to remove vote';
        }

        $this->redirect('/store/feedback/' . $id);
    }

    /**
     * Add comment
     */
    public function comment(int $id)
    {
        $this->checkPermission('feedback.submit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/feedback/' . $id);
            return;
        }

        $comment = $_POST['comment'] ?? '';

        if (empty($comment)) {
            $_SESSION['flash_error'] = 'Comment cannot be empty';
            $this->redirect('/store/feedback/' . $id);
            return;
        }

        if (FeedbackService::addComment($id, $comment)) {
            $_SESSION['flash_success'] = 'Comment added';
        } else {
            $_SESSION['flash_error'] = 'Failed to add comment';
        }

        $this->redirect('/store/feedback/' . $id);
    }

    /**
     * Update status (admin only)
     */
    public function updateStatus(int $id)
    {
        $this->checkPermission('feedback.manage');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/store/feedback/' . $id);
            return;
        }

        $status = $_POST['status'] ?? '';
        $adminNotes = $_POST['admin_notes'] ?? null;

        if (FeedbackService::updateStatus($id, $status, $adminNotes)) {
            $_SESSION['flash_success'] = 'Status updated';
        } else {
            $_SESSION['flash_error'] = 'Failed to update status';
        }

        $this->redirect('/store/feedback/' . $id);
    }

    /**
     * My feedback
     */
    public function myFeedback()
    {
        $this->checkPermission('feedback.submit');

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $this->redirect('/store/feedback');
            return;
        }

        $feedback = FeedbackService::getUserFeedback($userId);

        $data = [
            'feedback' => $feedback,
            'pageTitle' => 'My Feedback',
            'activeMenu' => 'feedback'
        ];

        $this->view('feedback/my-feedback', $data);
    }
}
