<?php $this->layout('layouts/admin', ['title' => $title ?? 'Conservation Initiatives']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Conservation</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-globe-americas me-2"></i>Conservation Initiatives</h2>
        <div>
            <a href="/store/conservation/dashboard" class="btn btn-outline-primary me-2">
                <i class="bi bi-bar-chart me-1"></i>Dashboard
            </a>
            <a href="/store/conservation/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Initiative
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Ongoing Initiatives</h6>
                            <h2 class="mb-0"><?= $stats['ongoing'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-tree display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Volunteer Hours</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_hours'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-clock display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Funds Raised</h6>
                            <h2 class="mb-0">$<?= number_format($stats['funds_raised'] ?? 0, 2) ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($initiatives)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-globe-americas display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No conservation initiatives yet</p>
                    <a href="/store/conservation/create" class="btn btn-primary">Create First Initiative</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Initiative</th>
                                <th>Type</th>
                                <th>Participants</th>
                                <th>Hours</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($initiatives as $initiative): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($initiative['initiative_name']) ?></strong>
                                        <?php if ($initiative['certification_program']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($initiative['certification_program']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $typeColors = [
                                                'cleanup' => 'primary',
                                                'reef_restoration' => 'success',
                                                'species_monitoring' => 'info',
                                                'education' => 'warning',
                                                'research' => 'secondary',
                                                'advocacy' => 'danger',
                                            ];
                                            $color = $typeColors[$initiative['initiative_type']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= ucfirst(str_replace('_', ' ', $initiative['initiative_type'])) ?>
                                        </span>
                                    </td>
                                    <td><?= $initiative['active_participants'] ?? 0 ?></td>
                                    <td><?= number_format($initiative['volunteer_hours'] ?? 0) ?></td>
                                    <td>
                                        <?php if ($initiative['is_ongoing']): ?>
                                            <span class="badge bg-success">Ongoing</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($initiative['start_date'])) ?></td>
                                    <td>
                                        <a href="/store/conservation/<?= $initiative['id'] ?>" class="btn btn-sm btn-outline-primary">
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
