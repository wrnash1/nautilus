<?php
$pageTitle = 'Audit Logs';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Admin', 'url' => '/store/admin'],
    ['title' => 'Audit Logs', 'url' => null]
];

ob_start();
?>

<div class="audit-logs-page">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1><i class="fas fa-history"></i> Audit Logs</h1>
                <p class="subtitle">Track all system activity and user actions</p>
            </div>
            <div class="header-actions">
                <a href="/store/admin/audit/activity" class="btn btn-secondary">
                    <i class="fas fa-chart-line"></i> Activity Dashboard
                </a>
                <a href="/store/admin/audit/export?<?= http_build_query($filters) ?>" class="btn btn-primary">
                    <i class="fas fa-download"></i> Export CSV
                </a>
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

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" action="/store/admin/audit" class="filters-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>User:</label>
                    <select name="user_id">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($filters['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Action:</label>
                    <select name="action">
                        <option value="">All Actions</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?= htmlspecialchars($action) ?>" <?= ($filters['action'] ?? '') === $action ? 'selected' : '' ?>>
                                <?= ucfirst($action) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Entity Type:</label>
                    <select name="entity_type">
                        <option value="">All Types</option>
                        <?php foreach ($entityTypes as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= ($filters['entity_type'] ?? '') === $type ? 'selected' : '' ?>>
                                <?= ucfirst($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Start Date:</label>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($filters['start_date'] ?? date('Y-m-01')) ?>">
                </div>

                <div class="filter-group">
                    <label>End Date:</label>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($filters['end_date'] ?? date('Y-m-t')) ?>">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <a href="/store/admin/audit" class="btn btn-outline">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Logs</div>
            <div class="stat-value"><?= number_format($totalLogs) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Showing</div>
            <div class="stat-value"><?= number_format(count($logs)) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Page</div>
            <div class="stat-value"><?= $page ?> / <?= $totalPages ?></div>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="table-container">
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list fa-3x"></i>
                <h3>No audit logs found</h3>
                <p>No logs match your current filters.</p>
            </div>
        <?php else: ?>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="log-row">
                            <td class="timestamp">
                                <?= date('M j, Y', strtotime($log['created_at'])) ?><br>
                                <small><?= date('g:i:s A', strtotime($log['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <strong><?= htmlspecialchars($log['user_name'] ?? 'Unknown') ?></strong>
                                    <?php if ($log['user_email']): ?>
                                        <small><?= htmlspecialchars($log['user_email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="action-badge action-<?= strtolower($log['action']) ?>">
                                    <?= ucfirst($log['action']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($log['entity_type']): ?>
                                    <div class="entity-cell">
                                        <strong><?= ucfirst($log['entity_type']) ?></strong>
                                        <?php if ($log['entity_id']): ?>
                                            <small>#<?= $log['entity_id'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="ip-address"><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></small>
                            </td>
                            <td>
                                <a href="/store/admin/audit/<?= $log['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $queryParams = array_merge($_GET, ['page' => $page - 1]);
                    unset($queryParams['page']);
                    $prevUrl = '/store/admin/audit?' . http_build_query($queryParams) . '&page=' . ($page - 1);
                    $nextUrl = '/store/admin/audit?' . http_build_query($queryParams) . '&page=' . ($page + 1);
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="<?= $prevUrl ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>

                    <span class="pagination-info">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </span>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $nextUrl ?>" class="pagination-btn">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Admin Actions -->
    <div class="admin-actions">
        <h3>Maintenance</h3>
        <p>Audit logs are kept for compliance and security purposes. Old logs can be removed to save database space.</p>
        <div class="action-buttons">
            <a href="/store/admin/audit/cleanup?days=365" class="btn btn-warning"
               onclick="return confirm('Delete audit logs older than 365 days?')">
                <i class="fas fa-trash"></i> Delete Logs Older Than 1 Year
            </a>
            <a href="/store/admin/audit/cleanup?days=730" class="btn btn-warning"
               onclick="return confirm('Delete audit logs older than 730 days (2 years)?')">
                <i class="fas fa-trash"></i> Delete Logs Older Than 2 Years
            </a>
        </div>
    </div>
</div>

<style>
.audit-logs-page {
    padding: 20px;
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
    display: flex;
    align-items: center;
    gap: 10px;
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

.filters-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #666;
}

.filter-group select,
.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow-x: auto;
    margin-bottom: 30px;
}

.audit-table {
    width: 100%;
    border-collapse: collapse;
}

.audit-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.audit-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

.audit-table tr:hover {
    background: #f8f9fa;
}

.timestamp {
    white-space: nowrap;
}

.timestamp small {
    color: #666;
    font-size: 11px;
}

.user-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-cell small {
    color: #666;
    font-size: 11px;
}

.action-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.action-create { background: #d1f4e0; color: #0f5132; }
.action-update { background: #cfe2ff; color: #084298; }
.action-delete { background: #f8d7da; color: #842029; }
.action-login { background: #d1e7dd; color: #0a3622; }
.action-logout { background: #f8f9fa; color: #495057; }
.action-view { background: #fff3cd; color: #856404; }
.action-export { background: #e2d9f3; color: #432874; }

.entity-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.entity-cell small {
    color: #666;
    font-size: 11px;
}

.ip-address {
    color: #666;
    font-family: monospace;
}

.text-muted {
    color: #999;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    padding: 20px;
}

.pagination-btn {
    padding: 8px 16px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pagination-btn:hover {
    background: #0056b3;
}

.pagination-info {
    color: #666;
    font-size: 14px;
}

.admin-actions {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.admin-actions h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.admin-actions p {
    margin: 0 0 20px 0;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 15px;
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
