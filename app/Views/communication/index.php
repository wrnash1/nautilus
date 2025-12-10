<?php
$pageTitle = 'Customer Communication';
$activeMenu = 'communication';

ob_start();
?>

<style>
.communication-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.communication-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.communication-header p {
    margin: 0;
    opacity: 0.9;
}

.actions-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.action-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.2s;
}

.action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.action-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.action-card h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.action-card p {
    color: #718096;
    margin-bottom: 16px;
    font-size: 14px;
}

.action-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.action-btn:hover {
    opacity: 0.9;
}

.campaigns-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
}

.campaigns-table {
    width: 100%;
    border-collapse: collapse;
}

.campaigns-table th {
    background: #f7fafc;
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 14px;
    border-bottom: 2px solid #e2e8f0;
}

.campaigns-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
}

.campaigns-table tr:hover {
    background: #f7fafc;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.sending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.completed {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.failed {
    background: #fee2e2;
    color: #991b1b;
}

.type-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.type-badge.sms {
    background: #dbeafe;
    color: #1e40af;
}

.type-badge.push {
    background: #e0e7ff;
    color: #5b21b6;
}

.type-badge.email {
    background: #fce7f3;
    color: #9f1239;
}

.progress-bar {
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 8px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    transition: width 0.3s;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #718096;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}
</style>

<div class="communication-header">
    <h1><i class="bi bi-chat-dots"></i> Customer Communication</h1>
    <p>Send SMS messages and push notifications to your customers</p>
</div>

<div class="actions-section">
    <div class="action-card">
        <div class="action-icon">
            <i class="bi bi-chat-text"></i>
        </div>
        <h3>Send SMS</h3>
        <p>Send individual or bulk SMS messages to customers</p>
        <a href="/communication/create?type=sms" class="action-btn">
            <i class="bi bi-send"></i> Send SMS
        </a>
    </div>

    <div class="action-card">
        <div class="action-icon">
            <i class="bi bi-bell"></i>
        </div>
        <h3>Push Notifications</h3>
        <p>Send push notifications to mobile app users</p>
        <a href="/communication/create?type=push" class="action-btn">
            <i class="bi bi-send"></i> Send Push
        </a>
    </div>

    <div class="action-card">
        <div class="action-icon">
            <i class="bi bi-people"></i>
        </div>
        <h3>Bulk Messaging</h3>
        <p>Send messages to multiple customers at once</p>
        <a href="/communication/create?type=bulk" class="action-btn">
            <i class="bi bi-send"></i> Bulk Send
        </a>
    </div>
</div>

<div class="campaigns-card">
    <div class="card-header">
        <h3><i class="bi bi-megaphone"></i> Recent Campaigns</h3>
    </div>

    <?php if (empty($campaigns)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>No campaigns yet. Start by sending your first message!</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="campaigns-table">
                <thead>
                    <tr>
                        <th>Campaign Name</th>
                        <th>Type</th>
                        <th>Target</th>
                        <th>Sent</th>
                        <th>Failed</th>
                        <th>Success Rate</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $campaign): ?>
                        <?php
                        $successRate = $campaign['target_count'] > 0
                            ? ($campaign['sent_count'] / $campaign['target_count']) * 100
                            : 0;
                        ?>
                        <tr onclick="window.location='/communication/campaigns/<?= $campaign['id'] ?>'" style="cursor: pointer;">
                            <td>
                                <strong><?= htmlspecialchars($campaign['name']) ?></strong>
                            </td>
                            <td>
                                <span class="type-badge <?= $campaign['type'] ?>">
                                    <?= strtoupper($campaign['type']) ?>
                                </span>
                            </td>
                            <td><?= number_format($campaign['target_count']) ?></td>
                            <td class="text-success">
                                <strong><?= number_format($campaign['sent_count']) ?></strong>
                            </td>
                            <td class="text-danger">
                                <?= number_format($campaign['failed_count']) ?>
                            </td>
                            <td>
                                <div>
                                    <?= number_format($successRate, 1) ?>%
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $successRate ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?= $campaign['status'] ?>">
                                    <?= ucfirst($campaign['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($campaign['created_at'])) ?>
                                <br>
                                <small class="text-muted"><?= date('H:i', strtotime($campaign['created_at'])) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
