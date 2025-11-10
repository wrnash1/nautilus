<?php

namespace App\Services\Notifications;

use App\Core\TenantDatabase;

/**
 * Real-Time WebSocket Notification Service
 *
 * Features:
 * - Real-time push notifications
 * - User presence tracking
 * - Live updates
 * - Chat functionality
 * - Broadcasting to channels
 */
class WebSocketService
{
    private string $socketUrl;

    public function __construct()
    {
        $this->socketUrl = $_ENV['WEBSOCKET_URL'] ?? 'ws://localhost:8080';
    }

    /**
     * Send real-time notification to user
     */
    public function notifyUser(int $userId, string $type, array $data): bool
    {
        $message = [
            'event' => 'notification',
            'user_id' => $userId,
            'type' => $type,
            'data' => $data,
            'timestamp' => time()
        ];

        return $this->sendToSocket($message);
    }

    /**
     * Broadcast to channel
     */
    public function broadcast(string $channel, string $event, array $data): bool
    {
        $message = [
            'event' => 'broadcast',
            'channel' => $channel,
            'type' => $event,
            'data' => $data,
            'timestamp' => time()
        ];

        return $this->sendToSocket($message);
    }

    /**
     * Send to specific tenant channel
     */
    public function broadcastToTenant(int $tenantId, string $event, array $data): bool
    {
        return $this->broadcast("tenant.{$tenantId}", $event, $data);
    }

    /**
     * Update user presence
     */
    public function updatePresence(int $userId, string $status = 'online'): bool
    {
        TenantDatabase::updateTenant('users', [
            'online_status' => $status,
            'last_seen' => date('Y-m-d H:i:s')
        ], 'id = ?', [$userId]);

        // Notify other users
        return $this->broadcast('presence', 'user_status_changed', [
            'user_id' => $userId,
            'status' => $status
        ]);
    }

    /**
     * Get online users
     */
    public function getOnlineUsers(int $tenantId): array
    {
        return TenantDatabase::fetchAllTenant("
            SELECT id, first_name, last_name, online_status
            FROM users
            WHERE tenant_id = ?
            AND online_status = 'online'
            AND last_seen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ", [$tenantId]) ?? [];
    }

    /**
     * Send message to chat channel
     */
    public function sendChatMessage(int $fromUserId, int $toUserId, string $message): bool
    {
        // Store message
        $messageId = TenantDatabase::insertTenant('chat_messages', [
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'is_read' => 0
        ]);

        // Send real-time notification
        return $this->notifyUser($toUserId, 'chat_message', [
            'message_id' => $messageId,
            'from_user_id' => $fromUserId,
            'message' => $message
        ]);
    }

    /**
     * Notify about new transaction
     */
    public function notifyNewTransaction(int $tenantId, array $transaction): bool
    {
        return $this->broadcastToTenant($tenantId, 'new_transaction', $transaction);
    }

    /**
     * Notify about low stock
     */
    public function notifyLowStock(int $tenantId, array $product): bool
    {
        return $this->broadcastToTenant($tenantId, 'low_stock', $product);
    }

    /**
     * Notify about new order
     */
    public function notifyNewOrder(int $tenantId, array $order): bool
    {
        return $this->broadcastToTenant($tenantId, 'new_order', $order);
    }

    /**
     * Send to WebSocket server
     */
    private function sendToSocket(array $message): bool
    {
        try {
            // In production, this would use a proper WebSocket client
            // For now, store in database for polling fallback
            TenantDatabase::insertTenant('websocket_queue', [
                'message' => json_encode($message),
                'created_at' => date('Y-m-d H:i:s'),
                'processed' => 0
            ]);

            return true;
        } catch (\Exception $e) {
            error_log("WebSocket send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending messages for user (polling fallback)
     */
    public function getPendingMessages(int $userId): array
    {
        $messages = TenantDatabase::fetchAllTenant("
            SELECT * FROM websocket_queue
            WHERE JSON_EXTRACT(message, '$.user_id') = ?
            AND processed = 0
            ORDER BY created_at ASC
        ", [$userId]) ?? [];

        // Mark as processed
        if (!empty($messages)) {
            $ids = array_column($messages, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            TenantDatabase::updateTenant('websocket_queue', [
                'processed' => 1,
                'processed_at' => date('Y-m-d H:i:s')
            ], "id IN ($placeholders)", $ids);
        }

        return array_map(fn($m) => json_decode($m['message'], true), $messages);
    }
}
