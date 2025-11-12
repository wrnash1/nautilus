<?php

namespace App\Services\Notification;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Notification Preference Service
 *
 * Manages user notification preferences and delivery channels
 */
class NotificationPreferenceService
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    /**
     * Get user's notification preferences
     */
    public function getUserPreferences(int $userId): array
    {
        try {
            $preferences = TenantDatabase::fetchAllTenant(
                "SELECT
                    np.id,
                    nt.notification_type,
                    nt.notification_name,
                    nt.description,
                    nt.category,
                    np.email_enabled,
                    np.sms_enabled,
                    np.in_app_enabled,
                    np.push_enabled,
                    np.frequency
                 FROM notification_preferences np
                 JOIN notification_types nt ON np.notification_type_id = nt.id
                 WHERE np.user_id = ?
                 ORDER BY nt.category, nt.notification_name",
                [$userId]
            ) ?? [];

            // Group by category
            $grouped = [];
            foreach ($preferences as $pref) {
                $category = $pref['category'];
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $pref;
            }

            return [
                'success' => true,
                'preferences' => $preferences,
                'grouped' => $grouped
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get user preferences failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update notification preference
     */
    public function updatePreference(int $userId, int $preferenceId, array $settings): array
    {
        try {
            // Verify preference belongs to user
            $preference = TenantDatabase::fetchOneTenant(
                "SELECT id FROM notification_preferences WHERE id = ? AND user_id = ?",
                [$preferenceId, $userId]
            );

            if (!$preference) {
                return ['success' => false, 'error' => 'Preference not found'];
            }

            $updateData = [];

            if (isset($settings['email_enabled'])) {
                $updateData['email_enabled'] = (bool)$settings['email_enabled'];
            }
            if (isset($settings['sms_enabled'])) {
                $updateData['sms_enabled'] = (bool)$settings['sms_enabled'];
            }
            if (isset($settings['in_app_enabled'])) {
                $updateData['in_app_enabled'] = (bool)$settings['in_app_enabled'];
            }
            if (isset($settings['push_enabled'])) {
                $updateData['push_enabled'] = (bool)$settings['push_enabled'];
            }
            if (isset($settings['frequency'])) {
                $updateData['frequency'] = $settings['frequency'];
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            TenantDatabase::updateTenant('notification_preferences',
                $updateData,
                'id = ?',
                [$preferenceId]
            );

            return [
                'success' => true,
                'message' => 'Preference updated successfully'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Update preference failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Bulk update preferences
     */
    public function bulkUpdatePreferences(int $userId, array $preferences): array
    {
        try {
            $updated = 0;
            $errors = [];

            foreach ($preferences as $prefId => $settings) {
                $result = $this->updatePreference($userId, $prefId, $settings);
                if ($result['success']) {
                    $updated++;
                } else {
                    $errors[] = "Preference {$prefId}: " . $result['error'];
                }
            }

            return [
                'success' => empty($errors),
                'updated_count' => $updated,
                'errors' => $errors,
                'message' => "{$updated} preferences updated successfully"
            ];

        } catch (\Exception $e) {
            $this->logger->error('Bulk update preferences failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Initialize default preferences for new user
     */
    public function initializeUserPreferences(int $userId): array
    {
        try {
            // Get all notification types
            $notificationTypes = TenantDatabase::fetchAllTenant(
                "SELECT id, default_email, default_sms, default_in_app, default_push, default_frequency
                 FROM notification_types
                 WHERE is_active = 1",
                []
            ) ?? [];

            foreach ($notificationTypes as $type) {
                // Check if preference already exists
                $exists = TenantDatabase::fetchOneTenant(
                    "SELECT id FROM notification_preferences
                     WHERE user_id = ? AND notification_type_id = ?",
                    [$userId, $type['id']]
                );

                if (!$exists) {
                    TenantDatabase::insertTenant('notification_preferences', [
                        'user_id' => $userId,
                        'notification_type_id' => $type['id'],
                        'email_enabled' => $type['default_email'],
                        'sms_enabled' => $type['default_sms'],
                        'in_app_enabled' => $type['default_in_app'],
                        'push_enabled' => $type['default_push'],
                        'frequency' => $type['default_frequency'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'User preferences initialized'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Initialize preferences failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if user should receive notification
     */
    public function shouldNotify(int $userId, string $notificationType, string $channel = 'email'): bool
    {
        try {
            $preference = TenantDatabase::fetchOneTenant(
                "SELECT np.*
                 FROM notification_preferences np
                 JOIN notification_types nt ON np.notification_type_id = nt.id
                 WHERE np.user_id = ?
                 AND nt.notification_type = ?",
                [$userId, $notificationType]
            );

            if (!$preference) {
                // No preference found, use default behavior
                return true;
            }

            // Check channel-specific setting
            switch ($channel) {
                case 'email':
                    return (bool)$preference['email_enabled'];
                case 'sms':
                    return (bool)$preference['sms_enabled'];
                case 'in_app':
                    return (bool)$preference['in_app_enabled'];
                case 'push':
                    return (bool)$preference['push_enabled'];
                default:
                    return false;
            }

        } catch (\Exception $e) {
            $this->logger->error('Check notification preference failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get notification frequency preference
     */
    public function getNotificationFrequency(int $userId, string $notificationType): string
    {
        try {
            $preference = TenantDatabase::fetchOneTenant(
                "SELECT np.frequency
                 FROM notification_preferences np
                 JOIN notification_types nt ON np.notification_type_id = nt.id
                 WHERE np.user_id = ?
                 AND nt.notification_type = ?",
                [$userId, $notificationType]
            );

            return $preference['frequency'] ?? 'instant';

        } catch (\Exception $e) {
            $this->logger->error('Get notification frequency failed', ['error' => $e->getMessage()]);
            return 'instant';
        }
    }

    /**
     * Disable all notifications for user
     */
    public function disableAllNotifications(int $userId): array
    {
        try {
            TenantDatabase::queryTenant(
                "UPDATE notification_preferences
                 SET email_enabled = 0,
                     sms_enabled = 0,
                     in_app_enabled = 0,
                     push_enabled = 0,
                     updated_at = ?
                 WHERE user_id = ?",
                [date('Y-m-d H:i:s'), $userId]
            );

            return [
                'success' => true,
                'message' => 'All notifications disabled'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Disable all notifications failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enable all notifications for user
     */
    public function enableAllNotifications(int $userId): array
    {
        try {
            TenantDatabase::queryTenant(
                "UPDATE notification_preferences
                 SET email_enabled = 1,
                     in_app_enabled = 1,
                     updated_at = ?
                 WHERE user_id = ?",
                [date('Y-m-d H:i:s'), $userId]
            );

            return [
                'success' => true,
                'message' => 'All notifications enabled'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Enable all notifications failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get notification history for user
     */
    public function getNotificationHistory(int $userId, array $filters = []): array
    {
        try {
            $limit = $filters['limit'] ?? 50;
            $offset = $filters['offset'] ?? 0;
            $channel = $filters['channel'] ?? null;
            $status = $filters['status'] ?? null;

            $where = ["user_id = ?"];
            $params = [$userId];

            if ($channel) {
                $where[] = "channel = ?";
                $params[] = $channel;
            }

            if ($status) {
                $where[] = "status = ?";
                $params[] = $status;
            }

            $whereClause = implode(' AND ', $where);

            $notifications = TenantDatabase::fetchAllTenant(
                "SELECT
                    nh.id,
                    nt.notification_name,
                    nh.channel,
                    nh.recipient,
                    nh.subject,
                    nh.status,
                    nh.sent_at,
                    nh.delivered_at,
                    nh.read_at,
                    nh.error_message
                 FROM notification_history nh
                 JOIN notification_types nt ON nh.notification_type_id = nt.id
                 WHERE {$whereClause}
                 ORDER BY nh.sent_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            ) ?? [];

            // Get total count
            $totalResult = TenantDatabase::fetchOneTenant(
                "SELECT COUNT(*) as total FROM notification_history WHERE {$whereClause}",
                $params
            );

            return [
                'success' => true,
                'notifications' => $notifications,
                'total' => $totalResult['total'] ?? 0,
                'limit' => $limit,
                'offset' => $offset
            ];

        } catch (\Exception $e) {
            $this->logger->error('Get notification history failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Record notification sent
     */
    public function recordNotification(int $userId, string $notificationType, string $channel, array $data): array
    {
        try {
            // Get notification type ID
            $typeRecord = TenantDatabase::fetchOneTenant(
                "SELECT id FROM notification_types WHERE notification_type = ?",
                [$notificationType]
            );

            if (!$typeRecord) {
                return ['success' => false, 'error' => 'Notification type not found'];
            }

            $notificationId = TenantDatabase::insertTenant('notification_history', [
                'user_id' => $userId,
                'notification_type_id' => $typeRecord['id'],
                'channel' => $channel,
                'recipient' => $data['recipient'] ?? null,
                'subject' => $data['subject'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => $data['status'] ?? 'sent',
                'sent_at' => date('Y-m-d H:i:s'),
                'metadata' => json_encode($data['metadata'] ?? [])
            ]);

            return [
                'success' => true,
                'notification_id' => $notificationId
            ];

        } catch (\Exception $e) {
            $this->logger->error('Record notification failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update notification status
     */
    public function updateNotificationStatus(int $notificationId, string $status, ?string $errorMessage = null): array
    {
        try {
            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($status === 'delivered') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'read') {
                $updateData['read_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'failed' && $errorMessage) {
                $updateData['error_message'] = $errorMessage;
            }

            TenantDatabase::updateTenant('notification_history',
                $updateData,
                'id = ?',
                [$notificationId]
            );

            return [
                'success' => true,
                'message' => 'Notification status updated'
            ];

        } catch (\Exception $e) {
            $this->logger->error('Update notification status failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
