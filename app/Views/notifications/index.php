<?php
$pageTitle = 'Notifications';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Notifications', 'url' => null]
];

// Include the app layout header
ob_start();
?>

<div class="notifications-page">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Notifications</h1>
                <p class="subtitle">Manage your notifications and alerts</p>
            </div>
            <div class="header-actions">
                <?php if ($unreadCount > 0): ?>
                    <button type="button" class="btn btn-secondary" onclick="markAllAsRead()">
                        Mark All as Read
                    </button>
                <?php endif; ?>
                <a href="/store/notifications?unread=1" class="btn btn-outline">
                    <i class="fas fa-filter"></i> Unread Only
                </a>
                <?php if (isset($_GET['unread'])): ?>
                    <a href="/store/notifications" class="btn btn-outline">
                        <i class="fas fa-list"></i> Show All
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <div class="notifications-summary">
        <div class="summary-card">
            <span class="summary-label">Total Notifications:</span>
            <span class="summary-value"><?= count($notifications) ?></span>
        </div>
        <div class="summary-card">
            <span class="summary-label">Unread:</span>
            <span class="summary-value unread-count"><?= $unreadCount ?></span>
        </div>
    </div>

    <div class="notifications-list">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash fa-3x"></i>
                <h3>No notifications</h3>
                <p>You're all caught up! Check back later for new updates.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?= $notification['is_read'] ? 'read' : 'unread' ?>"
                     data-notification-id="<?= $notification['id'] ?>">
                    <div class="notification-icon <?= htmlspecialchars($notification['type']) ?>">
                        <?php
                        $icons = [
                            'success' => 'check-circle',
                            'info' => 'info-circle',
                            'warning' => 'exclamation-triangle',
                            'danger' => 'exclamation-circle',
                            'error' => 'times-circle'
                        ];
                        $icon = $icons[$notification['type']] ?? 'bell';
                        ?>
                        <i class="fas fa-<?= $icon ?>"></i>
                    </div>

                    <div class="notification-content">
                        <div class="notification-header">
                            <h3 class="notification-title">
                                <?= htmlspecialchars($notification['title']) ?>
                            </h3>
                            <span class="notification-time">
                                <?= timeAgo($notification['created_at']) ?>
                            </span>
                        </div>

                        <p class="notification-message">
                            <?= htmlspecialchars($notification['message']) ?>
                        </p>

                        <div class="notification-actions">
                            <?php if ($notification['action_url']): ?>
                                <a href="<?= htmlspecialchars($notification['action_url']) ?>"
                                   class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            <?php endif; ?>

                            <?php if (!$notification['is_read']): ?>
                                <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        onclick="markAsRead(<?= $notification['id'] ?>)">
                                    Mark as Read
                                </button>
                            <?php endif; ?>

                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="deleteNotification(<?= $notification['id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.notifications-page {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-content h1 {
    margin: 0;
    font-size: 28px;
    color: #333;
}

.subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.notifications-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-card {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.summary-label {
    display: block;
    font-size: 14px;
    color: #666;
    margin-bottom: 5px;
}

.summary-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.summary-value.unread-count {
    color: #0d6efd;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.notification-item.unread {
    border-left: 4px solid #0d6efd;
    background: #f0f7ff;
}

.notification-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.notification-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.notification-icon.success {
    background: #d1e7dd;
    color: #0f5132;
}

.notification-icon.info {
    background: #cfe2ff;
    color: #084298;
}

.notification-icon.warning {
    background: #fff3cd;
    color: #997404;
}

.notification-icon.danger, .notification-icon.error {
    background: #f8d7da;
    color: #842029;
}

.notification-content {
    flex: 1;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 10px;
}

.notification-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.notification-time {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.notification-message {
    margin: 0 0 15px 0;
    color: #666;
    line-height: 1.5;
}

.notification-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    color: #ddd;
    margin-bottom: 20px;
}

.empty-state h3 {
    margin: 0 0 10px 0;
    color: #666;
}

.empty-state p {
    margin: 0;
    color: #999;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
}

.alert-success {
    background: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}

.alert-error {
    background: #f8d7da;
    color: #842029;
    border: 1px solid #f5c2c7;
}
</style>

<script>
function markAsRead(notificationId) {
    fetch(`/store/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.classList.remove('unread');
                item.classList.add('read');

                // Update unread count
                const unreadCountEl = document.querySelector('.unread-count');
                if (unreadCountEl) {
                    const currentCount = parseInt(unreadCountEl.textContent);
                    unreadCountEl.textContent = Math.max(0, currentCount - 1);
                }

                // Remove mark as read button
                const btn = item.querySelector('button.btn-secondary');
                if (btn) btn.remove();
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }

    fetch('/store/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (!confirm('Delete this notification?')) {
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

    fetch(`/store/notifications/${notificationId}/delete`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (item) {
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>

<?php
$content = ob_get_clean();

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';

    return date('M j, Y', $time);
}

require __DIR__ . '/../layouts/app.php';
?>
