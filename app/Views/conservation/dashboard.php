<?php $this->layout('layouts/admin', ['title' => $title ?? 'Conservation Dashboard']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/conservation">Conservation</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-globe-americas me-2"></i>Conservation Dashboard</h2>
        <a href="/store/conservation/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Initiative
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Initiatives</h6>
                            <h2 class="mb-0"><?= $stats['total_initiatives'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-tree display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Participants</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_participants'] ?? 0) ?></h2>
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
                            <h6 class="text-white-50 mb-1">Volunteer Hours</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_hours'] ?? 0) ?></h2>
                        </div>
                        <i class="bi bi-clock display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-dark-50 mb-1">Funds Raised</h6>
                            <h2 class="mb-0">$<?= number_format($stats['funds_raised'] ?? 0, 0) ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Initiatives -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Initiatives</h5>
                    <a href="/store/conservation" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentInitiatives)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-globe-americas display-1 text-muted"></i>
                            <p class="mt-3 text-muted">No initiatives yet</p>
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentInitiatives as $initiative): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($initiative['initiative_name']) ?></strong>
                                                <?php if ($initiative['is_ongoing']): ?>
                                                    <span class="badge bg-success ms-2">Ongoing</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= ucfirst(str_replace('_', ' ', $initiative['initiative_type'])) ?></td>
                                            <td><?= $initiative['participants_count'] ?? 0 ?></td>
                                            <td><?= number_format($initiative['volunteer_hours'] ?? 0) ?></td>
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

        <!-- By Type -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Initiatives by Type</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($byType)): ?>
                        <p class="text-muted text-center">No data yet</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($byType as $type): ?>
                                <?php
                                    $typeColors = [
                                        'cleanup' => 'primary',
                                        'reef_restoration' => 'success',
                                        'species_monitoring' => 'info',
                                        'education' => 'warning',
                                        'research' => 'secondary',
                                        'advocacy' => 'danger',
                                    ];
                                    $color = $typeColors[$type['initiative_type']] ?? 'secondary';
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= ucfirst(str_replace('_', ' ', $type['initiative_type'])) ?>
                                    <span class="badge bg-<?= $color ?> rounded-pill"><?= $type['count'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-award me-2"></i>Environmental Impact</h5>
                    <p class="small">
                        Your conservation efforts help protect marine ecosystems.
                        Every hour volunteered and every participant engaged makes a difference!
                    </p>
                    <a href="/store/conservation/create" class="btn btn-light btn-sm">
                        Start New Initiative
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
