<?php $this->layout('layouts/admin', ['title' => $title ?? 'Dive Logs']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Dive Logs</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-journal-text me-2"></i>Dive Logs</h2>
        <div>
            <a href="/store/dive-logs/dashboard" class="btn btn-outline-primary me-2">
                <i class="bi bi-bar-chart me-1"></i>Dashboard
            </a>
            <a href="/store/dive-logs/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Log New Dive
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Dives</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_logs'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-water display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Unique Divers</h6>
                            <h2 class="mb-0"><?= number_format($stats['unique_divers'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Avg Depth</h6>
                            <h2 class="mb-0"><?= $stats['avg_depth'] ?? 0 ?> ft</h2>
                        </div>
                        <i class="bi bi-arrow-down display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-10">
                    <input type="text" class="form-control" name="search" placeholder="Search by diver name, site, or location..."
                           value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-journal-x display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No dive logs found</p>
                    <a href="/store/dive-logs/create" class="btn btn-primary">Log First Dive</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Dive #</th>
                                <th>Diver</th>
                                <th>Date</th>
                                <th>Site</th>
                                <th>Depth</th>
                                <th>Time</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><span class="badge bg-primary">#<?= $log['dive_number'] ?></span></td>
                                    <td>
                                        <a href="/store/dive-logs/customer/<?= $log['customer_id'] ?>">
                                            <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($log['dive_date'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($log['dive_site_name_db'] ?? $log['dive_site_name'] ?? '-') ?>
                                        <?php if ($log['location']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($log['location']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['max_depth_feet']): ?>
                                            <?= $log['max_depth_feet'] ?> ft
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['bottom_time_minutes']): ?>
                                            <?= $log['bottom_time_minutes'] ?> min
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
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
