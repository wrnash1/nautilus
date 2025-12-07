<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class FeedbackController extends Controller
{
    /**
     * Public feedback form (accessible without login)
     */
    public function create(): void
    {
        $data = [
            'page_title' => 'Submit Feedback'
        ];

        $this->view('feedback/create', $data);
    }

    /**
     * Submit feedback (public)
     */
    public function store(): void
    {
        // Get submitted data
        $type = $_POST['type'] ?? 'other';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Validation
        if (empty($title) || empty($description)) {
            $_SESSION['error'] = 'Title and description are required';
            header('Location: /feedback/create');
            exit;
        }

        // Determine submitted_by_type and ID
        $submittedByType = 'customer';
        $submittedById = null;

        // If user is logged in as customer
        if (isset($_SESSION['customer_id'])) {
            $submittedByType = 'customer';
            $submittedById = $_SESSION['customer_id'];

            // Get customer info
            $customer = Database::fetchOne("SELECT first_name, last_name, email FROM customers WHERE id = ?", [$submittedById]);
            $name = ($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '');
            $email = $customer['email'] ?? $email;
        }
        // If user is logged in as staff
        elseif (isset($_SESSION['user_id'])) {
            $submittedByType = 'staff';
            $submittedById = $_SESSION['user_id'];

            // Get staff info
            $user = Database::fetchOne("SELECT first_name, last_name, email FROM users WHERE id = ?", [$submittedById]);
            $name = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
            $email = $user['email'] ?? $email;
        }

        // Get browser info
        $browserInfo = json_encode([
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? ''
        ]);

        $url = $_POST['url'] ?? $_SERVER['HTTP_REFERER'] ?? '';

        // Insert feedback
        Database::query("
            INSERT INTO feedback (
                tenant_id, type, title, description, submitted_by_type, submitted_by_id,
                submitted_by_name, submitted_by_email, category, browser_info, url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $_SESSION['tenant_id'] ?? 1,
            $type,
            $title,
            $description,
            $submittedByType,
            $submittedById,
            $name,
            $email,
            $category,
            $browserInfo,
            $url
        ]);

        $feedbackId = Database::lastInsertId();

        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            $this->handleAttachments($feedbackId, $_FILES['attachments']);
        }

        $_SESSION['success'] = 'Thank you! Your feedback has been submitted successfully.';
        header('Location: /feedback/success');
        exit;
    }

    /**
     * Success page after submission
     */
    public function success(): void
    {
        $data = [
            'page_title' => 'Feedback Submitted'
        ];

        $this->view('feedback/success', $data);
    }

    /**
     * My feedback (for customers and staff)
     */
    public function myFeedback(): void
    {
        $submittedByType = isset($_SESSION['customer_id']) ? 'customer' : 'staff';
        $submittedById = $_SESSION['customer_id'] ?? $_SESSION['user_id'] ?? null;

        if (!$submittedById) {
            header('Location: /feedback/create');
            exit;
        }

        $feedback = Database::fetchAll("
            SELECT * FROM feedback
            WHERE submitted_by_type = ? AND submitted_by_id = ?
            ORDER BY created_at DESC
        ", [$submittedByType, $submittedById]) ?? [];

        $data = [
            'feedback' => $feedback,
            'page_title' => 'My Feedback'
        ];

        $this->view('feedback/my-feedback', $data);
    }

    /**
     * Handle file attachments
     */
    private function handleAttachments(int $feedbackId, array $files): void
    {
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/feedback/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $filename = $files['name'][$i];
                $tmpName = $files['tmp_name'][$i];
                $filesize = $files['size'][$i];
                $mimeType = $files['type'][$i];

                // Generate unique filename
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $uniqueName = uniqid() . '_' . time() . '.' . $ext;
                $filepath = $uploadDir . $uniqueName;

                if (move_uploaded_file($tmpName, $filepath)) {
                    Database::query("
                        INSERT INTO feedback_attachments (feedback_id, filename, filepath, filesize, mime_type)
                        VALUES (?, ?, ?, ?, ?)
                    ", [$feedbackId, $filename, '/uploads/feedback/' . $uniqueName, $filesize, $mimeType]);
                }
            }
        }
    }
}
