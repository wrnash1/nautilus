<?php $this->layout('layouts/admin', ['title' => $title ?? 'Safety Dashboard']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/safety-checks">Safety Checks</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up me-2"></i>Safety Dashboard</h2>
        <a href="/store/safety-checks/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Check
        </a>
    </div>

    <!-- Today's Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $todayStats['total'] ?? 0 ?></h3>
                    <small>Total Today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $todayStats['passed'] ?? 0 ?></h3>
                    <small>Passed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $todayStats['failed'] ?? 0 ?></h3>
                    <small>Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?= $weekCount ?? 0 ?></h3>
                    <small>This Week</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Checks -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Safety Checks</h5>
                    <a href="/store/safety-checks" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentChecks)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-clipboard-check display-1 text-muted"></i>
                            <p class="mt-3 text-muted">No safety checks recorded yet</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Diver</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentChecks as $check): ?>
                                        <tr>
                                            <td>
                                                <?= date('M j', strtotime($check['created_at'])) ?>
                                                <br><small class="text-muted"><?= date('g:i A', strtotime($check['created_at'])) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($check['first_name'] . ' ' . $check['last_name']) ?></td>
                                            <td>
                                                <?php
                                                    $statusClass = match($check['check_status']) {
                                                        'passed' => 'bg-success',
                                                        'failed' => 'bg-danger',
                                                        'incomplete' => 'bg-warning text-dark',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($check['check_status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="/store/safety-checks/<?= $check['id'] ?>" class="btn btn-sm btn-outline-primary">
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

        <div class="col-lg-4">
            <!-- Common Issues -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Common Issues (30 Days)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($commonIssues)): ?>
                        <p class="text-muted text-center">No issues reported</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($commonIssues as $issue): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <small><?= htmlspecialchars(substr($issue['issues_noted'], 0, 50)) ?>...</small>
                                    <span class="badge bg-secondary"><?= $issue['count'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card bg-light">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-2"></i>BWRAF Checklist</h6>
                    <ul class="small mb-0">
                        <li><strong>B</strong> - BCD (Buoyancy Control Device)</li>
                        <li><strong>W</strong> - Weights</li>
                        <li><strong>R</strong> - Releases</li>
                        <li><strong>A</strong> - Air</li>
                        <li><strong>F</strong> - Final Check</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
