<?php
/**
 * Google Contacts Integration Page
 */
$pageTitle = 'Google Contacts Integration';
require_once __DIR__ . '/../layouts/admin.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="bi bi-google"></i> Google Contacts Integration
            </h1>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        Successfully connected to Google Contacts!
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        Error: <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Connection Status -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Connection Status</h5>
                    <?php if ($isConnected): ?>
                        <div class="text-success mb-3">
                            <i class="bi bi-check-circle-fill"></i> Connected
                        </div>
                        <p class="small text-muted">
                            Last synced: <?= $stats['last_sync'] ? date('M j, Y g:i A', strtotime($stats['last_sync'])) : 'Never' ?>
                        </p>
                        <button class="btn btn-danger btn-sm" onclick="disconnect()">
                            <i class="bi bi-x-circle"></i> Disconnect
                        </button>
                    <?php else: ?>
                        <div class="text-warning mb-3">
                            <i class="bi bi-exclamation-circle-fill"></i> Not Connected
                        </div>
                        <a href="/admin/integrations/google-contacts/connect" class="btn btn-primary">
                            <i class="bi bi-google"></i> Connect to Google
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sync Statistics -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sync Statistics</h5>
                    <div class="row text-center">
                        <div class="col">
                            <div class="display-6"><?= number_format($stats['total_mapped']) ?></div>
                            <small class="text-muted">Contacts Synced</small>
                        </div>
                        <div class="col">
                            <div class="display-6"><?= number_format($stats['total_exports']) ?></div>
                            <small class="text-muted">Exported</small>
                        </div>
                        <div class="col">
                            <div class="display-6"><?= number_format($stats['total_imports']) ?></div>
                            <small class="text-muted">Imported</small>
                        </div>
                        <div class="col">
                            <div class="display-6 text-warning"><?= number_format($stats['conflicts']) ?></div>
                            <small class="text-muted">Conflicts</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isConnected): ?>
    <!-- Sync Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sync Controls</h5>
                    <div class="btn-group" role="group">
                        <button class="btn btn-primary" onclick="triggerSync('incremental')">
                            <i class="bi bi-arrow-repeat"></i> Incremental Sync
                        </button>
                        <button class="btn btn-warning" onclick="triggerSync('full')">
                            <i class="bi bi-arrow-clockwise"></i> Full Sync
                        </button>
                    </div>
                    <div id="sync-status" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sync Configuration</h5>
                    <form id="config-form" onsubmit="saveConfig(event)">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sync_enabled" name="sync_enabled" 
                                    <?= ($config['sync_enabled'] ?? false) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sync_enabled">
                                    Enable Automatic Sync
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sync Direction</label>
                            <select class="form-select" name="sync_direction">
                                <option value="two_way" <?= ($config['sync_direction'] ?? 'two_way') === 'two_way' ? 'selected' : '' ?>>
                                    Two-Way Sync
                                </option>
                                <option value="export_only" <?= ($config['sync_direction'] ?? '') === 'export_only' ? 'selected' : '' ?>>
                                    Export Only (Nautilus → Google)
                                </option>
                                <option value="import_only" <?= ($config['sync_direction'] ?? '') === 'import_only' ? 'selected' : '' ?>>
                                    Import Only (Google → Nautilus)
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sync Frequency (minutes)</label>
                            <input type="number" class="form-control" name="sync_frequency_minutes" 
                                value="<?= $config['sync_frequency_minutes'] ?? 15 ?>" min="5" max="1440">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Conflict Resolution</label>
                            <select class="form-select" name="conflict_strategy">
                                <option value="last_modified_wins" <?= ($config['conflict_strategy'] ?? 'last_modified_wins') === 'last_modified_wins' ? 'selected' : '' ?>>
                                    Last Modified Wins
                                </option>
                                <option value="google_wins" <?= ($config['conflict_strategy'] ?? '') === 'google_wins' ? 'selected' : '' ?>>
                                    Google Always Wins
                                </option>
                                <option value="nautilus_wins" <?= ($config['conflict_strategy'] ?? '') === 'nautilus_wins' ? 'selected' : '' ?>>
                                    Nautilus Always Wins
                                </option>
                                <option value="manual" <?= ($config['conflict_strategy'] ?? '') === 'manual' ? 'selected' : '' ?>>
                                    Manual Review
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sync_only_active" name="sync_only_active"
                                    <?= ($config['sync_only_active'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="sync_only_active">
                                    Sync Only Active Customers
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Save Configuration
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Recent Sync Activity</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Results</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No sync history yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('M j, g:i A', strtotime($log['started_at'])) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= ucfirst($log['sync_type']) ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = match($log['status']) {
                                                'completed' => 'success',
                                                'in_progress' => 'info',
                                                'failed' => 'danger',
                                                default => 'warning'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($log['status']) ?></span>
                                        </td>
                                        <td class="small">
                                            <?php if ($log['status'] === 'completed'): ?>
                                                ↑<?= $log['customers_exported'] ?> ↓<?= $log['customers_imported'] ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="/admin/integrations/google-contacts/logs" class="btn btn-sm btn-outline-primary">
                        View Full Log
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function triggerSync(type) {
    const statusDiv = document.getElementById('sync-status');
    statusDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Syncing...';
    
    fetch('/admin/integrations/google-contacts/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'sync_type=' + type
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = `
                <div class="alert alert-success">
                    Sync completed! 
                    Exported: ${data.results.exported}, 
                    Imported: ${data.results.imported}, 
                    Updated: ${data.results.updated}
                </div>
            `;
            setTimeout(() => location.reload(), 2000);
        } else {
            statusDiv.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    });
}

function saveConfig(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('/admin/integrations/google-contacts/config', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Configuration saved successfully!');
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function disconnect() {
    if (confirm('Are you sure you want to disconnect from Google Contacts?')) {
        fetch('/admin/integrations/google-contacts/disconnect', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}
</script>
