<?php $this->layout('layouts/admin', ['title' => $title ?? 'Dive Log Dashboard']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/dive-logs">Dive Logs</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart me-2"></i>Dive Log Dashboard</h2>
        <a href="/store/dive-logs/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Log New Dive
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Dives</h6>
                            <h2 class="mb-0"><?= number_format($overallStats['total_dives'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-water display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Unique Divers</h6>
                            <h2 class="mb-0"><?= number_format($overallStats['unique_divers'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-people display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Max Depth</h6>
                            <h2 class="mb-0"><?= round($overallStats['max_depth'] ?? 0) ?> ft</h2>
                        </div>
                        <i class="bi bi-arrow-down display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Hours</h6>
                            <h2 class="mb-0"><?= number_format(($overallStats['total_time'] ?? 0) / 60, 0) ?></h2>
                        </div>
                        <i class="bi bi-clock display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Divers -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Divers</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topDivers)): ?>
                        <p class="text-muted text-center">No data yet</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Diver</th>
                                    <th>Dives</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topDivers as $i => $diver): ?>
                                    <tr>
                                        <td>
                                            <?php if ($i < 3): ?>
                                                <span class="badge bg-<?= ['warning', 'secondary', 'danger'][$i] ?>"><?= $i + 1 ?></span>
                                            <?php else: ?>
                                                <?= $i + 1 ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/store/dive-logs/customer/<?= $diver['id'] ?>">
                                                <?= htmlspecialchars($diver['first_name'] . ' ' . $diver['last_name']) ?>
                                            </a>
                                        </td>
                                        <td><span class="badge bg-primary"><?= $diver['dive_count'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Popular Sites -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Popular Dive Sites</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($popularSites)): ?>
                        <p class="text-muted text-center">No data yet</p>
                    <?php else: ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Dives</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularSites as $site): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($site['site_name'] ?? 'Unknown') ?></td>
                                        <td><span class="badge bg-info"><?= $site['dive_count'] ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Dives -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Dives</h5>
            <a href="/store/dive-logs" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentLogs)): ?>
                <div class="text-center py-4">
                    <p class="text-muted">No dives logged yet</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Diver</th>
                                <th>Dive #</th>
                                <th>Date</th>
                                <th>Depth</th>
                                <th>Time</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log): ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?></td>
                                    <td><span class="badge bg-primary">#<?= $log['dive_number'] ?></span></td>
                                    <td><?= date('M j, Y', strtotime($log['dive_date'])) ?></td>
                                    <td><?= $log['max_depth_feet'] ? $log['max_depth_feet'] . ' ft' : '-' ?></td>
                                    <td><?= $log['bottom_time_minutes'] ? $log['bottom_time_minutes'] . ' min' : '-' ?></td>
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
