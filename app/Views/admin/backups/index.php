<?php
$pageTitle = 'Database Backups';
$activeMenu = 'admin';

ob_start();
?>

<style>
.backup-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.backup-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.backup-header p {
    margin: 0;
    opacity: 0.9;
}

.statistics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-label {
    font-size: 14px;
    color: #718096;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #2d3748;
    margin-bottom: 8px;
}

.stat-meta {
    font-size: 13px;
    color: #a0aec0;
}

.actions-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.actions-card h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-create-backup {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s;
}

.btn-create-backup:hover {
    transform: translateY(-2px);
}

.btn-clean-old {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s;
}

.btn-clean-old:hover {
    transform: translateY(-2px);
}

.backups-table-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
}

.table-responsive {
    overflow-x: auto;
}

.backups-table {
    width: 100%;
    border-collapse: collapse;
}

.backups-table th {
    background: #f7fafc;
    padding: 16px;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 14px;
    border-bottom: 2px solid #e2e8f0;
}

.backups-table td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
}

.backups-table tr:hover {
    background: #f7fafc;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.error {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn {
    background: none;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    margin-right: 6px;
    font-size: 13px;
}

.action-btn:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
}

.action-btn.download {
    color: #2563eb;
}

.action-btn.restore {
    color: #059669;
}

.action-btn.delete {
    color: #dc2626;
}

.action-btn.download:hover {
    background: #dbeafe;
    border-color: #2563eb;
}

.action-btn.restore:hover {
    background: #d1fae5;
    border-color: #059669;
}

.action-btn.delete:hover {
    background: #fee2e2;
    border-color: #dc2626;
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

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    margin-bottom: 20px;
}

.modal-header h3 {
    margin: 0;
    font-size: 22px;
}

.modal-body {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.btn-cancel {
    background: #e2e8f0;
    color: #4a5568;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-submit {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}
</style>

<div class="backup-header">
    <h1><i class="bi bi-database"></i> Database Backups</h1>
    <p>Create, manage, and restore database backups for disaster recovery</p>
</div>

<!-- Statistics -->
<div class="statistics-grid">
    <div class="stat-card">
        <div class="stat-label">Total Backups</div>
        <div class="stat-value"><?= number_format($statistics['total_backups']) ?></div>
        <div class="stat-meta">Available backup files</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Size</div>
        <div class="stat-value"><?= $statistics['total_size_formatted'] ?></div>
        <div class="stat-meta">Disk space used</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Database Size</div>
        <div class="stat-value"><?= $statistics['database_size_formatted'] ?></div>
        <div class="stat-meta">Current database</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Last Backup</div>
        <div class="stat-value">
            <?php if ($statistics['last_backup']): ?>
                <?= date('M d', strtotime($statistics['last_backup'])) ?>
            <?php else: ?>
                Never
            <?php endif; ?>
        </div>
        <div class="stat-meta">
            <?php if ($statistics['last_backup']): ?>
                <?= date('Y H:i', strtotime($statistics['last_backup'])) ?>
            <?php else: ?>
                No backups yet
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Actions -->
<div class="actions-card">
    <h3>Quick Actions</h3>
    <div class="action-buttons">
        <button class="btn-create-backup" onclick="showCreateBackupModal()">
            <i class="bi bi-plus-circle"></i>
            Create New Backup
        </button>

        <button class="btn-clean-old" onclick="cleanOldBackups()">
            <i class="bi bi-trash"></i>
            Clean Old Backups
        </button>
    </div>
</div>

<!-- Backups Table -->
<div class="backups-table-card">
    <div class="card-header">
        <h3><i class="bi bi-list"></i> Backup History</h3>
    </div>

    <?php if (empty($backups)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>No backups found. Create your first backup to get started.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="backups-table">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Description</th>
                        <th>Size</th>
                        <th>Documents</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($backup['filename']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($backup['description'] ?: '-') ?></td>
                            <td><?= $backup['file_size_formatted'] ?></td>
                            <td>
                                <?php if ($backup['includes_documents']): ?>
                                    <i class="bi bi-check-circle text-success"></i> Yes
                                <?php else: ?>
                                    <i class="bi bi-x-circle text-muted"></i> No
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y H:i', strtotime($backup['created_at'])) ?></td>
                            <td>
                                <?php if ($backup['file_exists']): ?>
                                    <span class="status-badge success">Available</span>
                                <?php else: ?>
                                    <span class="status-badge error">Missing</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($backup['file_exists']): ?>
                                    <button class="action-btn download" onclick="downloadBackup(<?= $backup['id'] ?>)" title="Download">
                                        <i class="bi bi-download"></i>
                                    </button>
                                    <button class="action-btn restore" onclick="restoreBackup(<?= $backup['id'] ?>, '<?= htmlspecialchars($backup['filename']) ?>')" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="action-btn delete" onclick="deleteBackup(<?= $backup['id'] ?>)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Create Backup Modal -->
<div id="createBackupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Backup</h3>
        </div>
        <form id="createBackupForm" onsubmit="createBackup(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <input type="text" id="description" name="description" class="form-control" placeholder="e.g., Before major update">
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="include_documents" name="include_documents" value="1">
                        <span>Include uploaded documents (increases backup size)</span>
                    </label>
                </div>

                <div style="background: #fef3c7; padding: 12px; border-radius: 6px; font-size: 13px;">
                    <i class="bi bi-exclamation-triangle" style="color: #f59e0b;"></i>
                    This will create a full database backup. The process may take a few moments.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="hideCreateBackupModal()">Cancel</button>
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-circle"></i> Create Backup
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateBackupModal() {
    document.getElementById('createBackupModal').classList.add('show');
}

function hideCreateBackupModal() {
    document.getElementById('createBackupModal').classList.remove('show');
    document.getElementById('createBackupForm').reset();
}

async function createBackup(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('.btn-submit');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';

    try {
        const response = await fetch('/admin/backups/create', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Backup created successfully!');
            location.reload();
        } else {
            alert('Failed to create backup: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error creating backup: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Create Backup';
    }
}

function downloadBackup(backupId) {
    window.location.href = `/admin/backups/${backupId}/download`;
}

async function restoreBackup(backupId, filename) {
    if (!confirm(`Are you sure you want to restore from this backup?\n\nFilename: ${filename}\n\nWARNING: This will replace your current database. A backup of the current state will be created automatically before restoration.`)) {
        return;
    }

    if (!confirm('FINAL WARNING: This action cannot be undone. Continue with restoration?')) {
        return;
    }

    try {
        const response = await fetch(`/admin/backups/${backupId}/restore`, {
            method: 'POST'
        });

        const result = await response.json();

        if (result.success) {
            alert('Database restored successfully! The page will refresh.');
            location.reload();
        } else {
            alert('Failed to restore backup: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error restoring backup: ' + error.message);
    }
}

async function deleteBackup(backupId) {
    if (!confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/admin/backups/${backupId}/delete`, {
            method: 'POST'
        });

        const result = await response.json();

        if (result.success) {
            alert('Backup deleted successfully!');
            location.reload();
        } else {
            alert('Failed to delete backup: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error deleting backup: ' + error.message);
    }
}

async function cleanOldBackups() {
    const keepCount = prompt('How many recent backups would you like to keep?', '10');

    if (!keepCount || isNaN(keepCount) || parseInt(keepCount) < 1) {
        return;
    }

    if (!confirm(`This will delete all backups except the ${keepCount} most recent ones. Continue?`)) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('keep_count', keepCount);

        const response = await fetch('/admin/backups/clean-old', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Failed to clean backups: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        alert('Error cleaning backups: ' + error.message);
    }
}

// Close modal on outside click
document.getElementById('createBackupModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCreateBackupModal();
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
