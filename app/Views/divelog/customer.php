<?php $this->layout('layouts/admin', ['title' => $title ?? 'Dive History']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/dive-logs">Dive Logs</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($customer['first_name'] ?? '') ?>'s Dive History</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-journal-text me-2"></i>Dive History
            <small class="text-muted">- <?= htmlspecialchars(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')) ?></small>
        </h2>
        <div>
            <a href="/store/customers/<?= $customer['id'] ?>" class="btn btn-outline-secondary me-2">
                <i class="bi bi-person me-1"></i>Customer Profile
            </a>
            <a href="/store/dive-logs/create?customer_id=<?= $customer['id'] ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Log New Dive
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= $stats['total_dives'] ?? 0 ?></h2>
                    <small class="text-white-50">Total Dives</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= $stats['max_depth'] ?? 0 ?></h2>
                    <small class="text-white-50">Max Depth (ft)</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0"><?= number_format(($stats['total_time'] ?? 0) / 60, 1) ?></h2>
                    <small class="text-white-50">Total Hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h5 class="mb-0"><?= $stats['first_dive'] ? date('M Y', strtotime($stats['first_dive'])) : '-' ?></h5>
                    <small class="text-muted">First Dive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <h5 class="mb-0"><?= $stats['last_dive'] ? date('M j, Y', strtotime($stats['last_dive'])) : '-' ?></h5>
                    <small class="text-muted">Last Dive</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Dive Log</h5>
        </div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-x display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No dives logged yet</p>
                    <a href="/store/dive-logs/create?customer_id=<?= $customer['id'] ?>" class="btn btn-primary">Log First Dive</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Dive #</th>
                                <th>Date</th>
                                <th>Site</th>
                                <th>Max Depth</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-primary">#<?= $log['dive_number'] ?></span></td>
                                    <td><?= date('M j, Y', strtotime($log['dive_date'])) ?></td>
                                    <td><?= htmlspecialchars($log['site_name_db'] ?? $log['dive_site_name'] ?? '-') ?></td>
                                    <td><?= $log['max_depth_feet'] ? $log['max_depth_feet'] . ' ft' : '-' ?></td>
                                    <td><?= $log['bottom_time_minutes'] ? $log['bottom_time_minutes'] . ' min' : '-' ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($log['dive_type'] ?? 'recreational') ?></span></td>
                                    <td>
                                        <a href="/store/dive-logs/<?= $log['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
