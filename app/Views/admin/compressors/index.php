<?php
$pageTitle = 'Compressor Management';
$activeMenu = 'admin';
require __DIR__ . '/../../layouts/admin.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Compressor Management</h1>
            <p class="text-muted">Track run hours and maintenance intervals.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompressorModal">
            <i class="bi bi-plus-circle"></i> Add Compressor
        </button>
    </div>

    <!-- Compressor Cards -->
    <div class="row mb-4">
        <?php foreach ($compressors as $comp): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-left-<?= $comp['maintenance_needed'] ? 'danger' : 'success' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-0"><?= htmlspecialchars($comp['name']) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($comp['model']) ?> (S/N: <?= htmlspecialchars($comp['serial_number']) ?>)</small>
                        </div>
                        <span class="badge bg-<?= $comp['status'] === 'active' ? 'success' : 'warning' ?>">
                            <?= strtoupper($comp['status']) ?>
                        </span>
                    </div>

                    <div class="text-center py-3 mb-3 bg-light rounded">
                        <h2 class="mb-0 text-primary"><?= number_format($comp['current_hours'], 1) ?></h2>
                        <small class="text-muted">Current Run Hours</small>
                    </div>

                    <!-- Oil Status -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Oil Change</span>
                            <span class="<?= $comp['oil_remaining'] < 10 ? 'text-danger fw-bold' : '' ?>">
                                <?= $comp['oil_remaining'] > 0 ? round($comp['oil_remaining']) . ' hrs left' : 'OVERDUE' ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <?php 
                                $oilPercent = min(100, max(0, ($comp['current_hours'] - $comp['last_oil_change_hours']) / $comp['oil_change_interval'] * 100));
                                $oilColor = $oilPercent > 90 ? 'danger' : ($oilPercent > 75 ? 'warning' : 'info');
                            ?>
                            <div class="progress-bar bg-<?= $oilColor ?>" role="progressbar" style="width: <?= $oilPercent ?>%"></div>
                        </div>
                    </div>

                    <!-- Filter Status -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Filter Change</span>
                            <span class="<?= $comp['filter_remaining'] < 10 ? 'text-danger fw-bold' : '' ?>">
                                <?= $comp['filter_remaining'] > 0 ? round($comp['filter_remaining']) . ' hrs left' : 'OVERDUE' ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <?php 
                                $filterPercent = min(100, max(0, ($comp['current_hours'] - $comp['last_filter_change_hours']) / $comp['filter_change_interval'] * 100));
                                $filterColor = $filterPercent > 90 ? 'danger' : ($filterPercent > 75 ? 'warning' : 'primary');
                            ?>
                            <div class="progress-bar bg-<?= $filterColor ?>" role="progressbar" style="width: <?= $filterPercent ?>%"></div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="logMaintenance(<?= $comp['id'] ?>, '<?= $comp['name'] ?>', <?= $comp['current_hours'] ?>)">
                            <i class="bi bi-wrench"></i> Log Maintenance
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($compressors)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">No compressors found. Add one to start tracking.</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent Logs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Maintenance Logs</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Compressor</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Hours</th>
                            <th>Logged By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                            <td><?= htmlspecialchars($log['compressor_name']) ?></td>
                            <td>
                                <span class="badge bg-<?= $log['type'] === 'fill_run' ? 'secondary' : 'info' ?>">
                                    <?= str_replace('_', ' ', strtoupper($log['type'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($log['description']) ?></td>
                            <td><?= number_format($log['hours_recorded'], 1) ?></td>
                            <td><?= htmlspecialchars($log['user_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center">No logs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Compressor Modal -->
<div class="modal fade" id="addCompressorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/compressors">
                <div class="modal-header">
                    <h5 class="modal-title">Add Compressor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Model</label>
                            <input type="text" name="model" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label>Serial Number</label>
                            <input type="text" name="serial_number" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Oil Interval (Hours)</label>
                            <input type="number" name="oil_change_interval" class="form-control" value="100">
                        </div>
                        <div class="col-6 mb-3">
                            <label>Filter Interval (Hours)</label>
                            <input type="number" name="filter_change_interval" class="form-control" value="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log Maintenance Modal -->
<div class="modal fade" id="logMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="maintenanceForm">
                <div class="modal-header">
                    <h5 class="modal-title">Log Maintenance: <span id="modalCompName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Maintenance Type</label>
                        <select name="type" class="form-select" required>
                            <option value="check">Routine Check</option>
                            <option value="oil_change">Oil Change</option>
                            <option value="filter_change">Filter Change</option>
                            <option value="maintenance">Full Maintenance (Oil & Filter)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Current Hours</label>
                        <input type="number" step="0.1" name="current_hours" id="modalCurrentHours" class="form-control" required>
                        <small class="text-muted">Update only if hours have increased.</small>
                    </div>
                    <div class="mb-3">
                        <label>Description / Notes</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Maintenance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function logMaintenance(id, name, hours) {
    document.getElementById('modalCompName').textContent = name;
    document.getElementById('modalCurrentHours').value = hours;
    document.getElementById('maintenanceForm').action = `/admin/compressors/${id}/maintenance`;
    new bootstrap.Modal(document.getElementById('logMaintenanceModal')).show();
}
</script>
