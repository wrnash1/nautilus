<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Translator;

class SettingsController extends Controller
{
    /**
     * Change user locale
     */
    public function changeLocale(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $locale = $data['locale'] ?? null;

            if (!$locale) {
                echo json_encode(['success' => false, 'error' => 'Locale not specified']);
                return;
            }

            $translator = Translator::getInstance();
            $availableLocales = $translator->getAvailableLocales();

            if (!isset($availableLocales[$locale])) {
                echo json_encode(['success' => false, 'error' => 'Invalid locale']);
                return;
            }

            // Set locale in session
            $translator->setLocale($locale);

            // If user is logged in, update their preference in database
            if (isset($_SESSION['user_id'])) {
                $sql = "UPDATE users SET locale = ? WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$locale, $_SESSION['user_id']]);
            }

            echo json_encode([
                'success' => true,
                'locale' => $locale,
                'message' => 'Language changed successfully'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to change language: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * User settings page
     */
    public function index(): void
    {
        $this->checkPermission('settings.view');

        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $this->redirect('/login');
            return;
        }

        // Get user data
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = __('error.not_found', ['item' => 'User']);
            $this->redirect('/dashboard');
            return;
        }

        // Get user's notification preferences
        $sql = "SELECT * FROM notification_preferences WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $notificationPrefs = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get 2FA status
        $sql = "SELECT two_factor_enabled FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $twoFactorStatus = $stmt->fetchColumn();

        $this->view('settings/index', [
            'title' => __('messages.settings'),
            'user' => $user,
            'notification_prefs' => $notificationPrefs,
            'two_factor_enabled' => (bool)$twoFactorStatus
        ]);
    }

    /**
     * Update user settings
     */
    public function update(): void
    {
        $this->checkPermission('settings.update');

        try {
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            // Update user basic info
            $sql = "UPDATE users SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?,
                    locale = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_POST['first_name'] ?? '',
                $_POST['last_name'] ?? '',
                $_POST['email'] ?? '',
                $_POST['phone'] ?? '',
                $_POST['locale'] ?? 'en',
                $userId
            ]);

            // Update locale in session
            if (isset($_POST['locale'])) {
                $translator = Translator::getInstance();
                $translator->setLocale($_POST['locale']);
            }

            // Update notification preferences
            $sql = "INSERT INTO notification_preferences
                    (user_id, email_notifications, sms_notifications, in_app_notifications)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    email_notifications = VALUES(email_notifications),
                    sms_notifications = VALUES(sms_notifications),
                    in_app_notifications = VALUES(in_app_notifications)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                isset($_POST['email_notifications']) ? 1 : 0,
                isset($_POST['sms_notifications']) ? 1 : 0,
                isset($_POST['in_app_notifications']) ? 1 : 0
            ]);

            $_SESSION['success'] = __('messages.success.saved');
            $this->redirect('/settings');

        } catch (\Exception $e) {
            $_SESSION['error'] = __('messages.error.general') . ': ' . $e->getMessage();
            $this->redirect('/settings');
        }
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        $this->checkPermission('settings.update');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/settings');
            return;
        }

        try {
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                throw new \Exception('User not authenticated');
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate new password matches confirmation
            if ($newPassword !== $confirmPassword) {
                throw new \Exception(__('validation.confirmed', ['field' => 'password']));
            }

            // Validate password strength
            if (strlen($newPassword) < 8) {
                throw new \Exception(__('validation.min', ['field' => 'password', 'min' => 8]));
            }

            // Verify current password
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $currentHash = $stmt->fetchColumn();

            if (!password_verify($currentPassword, $currentHash)) {
                throw new \Exception('Current password is incorrect');
            }

            // Update password
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$newHash, $userId]);

            $_SESSION['success'] = 'Password changed successfully';
            $this->redirect('/settings');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/settings');
        }
    }
}
