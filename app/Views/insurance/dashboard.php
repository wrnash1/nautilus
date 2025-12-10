<?php $this->layout('layouts/admin', ['title' => $title ?? 'Insurance Dashboard']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/insurance">Insurance</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-check me-2"></i>Insurance Dashboard</h2>
        <a href="/store/insurance/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Policy
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Policies</h6>
                            <h2 class="mb-0"><?= $stats['total'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-shield display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Active Policies</h6>
                            <h2 class="mb-0"><?= $stats['active'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-shield-check display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Expiring This Month</h6>
                            <h2 class="mb-0"><?= $stats['expiring'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="/store/insurance/expiring" class="text-dark small">View expiring <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Policies -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Policies</h5>
                    <a href="/store/insurance" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentPolicies)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-shield display-1 text-muted"></i>
                            <p class="mt-3 text-muted">No policies yet</p>
                            <a href="/store/insurance/create" class="btn btn-primary">Add First Policy</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer</th>
                                        <th>Provider</th>
                                        <th>Policy #</th>
                                        <th>Expires</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentPolicies as $policy): ?>
                                        <?php
                                            $expDate = strtotime($policy['expiration_date']);
                                            $isExpired = $expDate < time();
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($policy['first_name'] . ' ' . $policy['last_name']) ?></td>
                                            <td><?= htmlspecialchars($policy['insurance_provider']) ?></td>
                                            <td><code><?= htmlspecialchars($policy['policy_number']) ?></code></td>
                                            <td>
                                                <?= date('M j, Y', $expDate) ?>
                                                <?php if ($isExpired): ?>
                                                    <span class="badge bg-danger ms-1">Expired</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="/store/insurance/<?= $policy['id'] ?>" class="btn btn-sm btn-outline-primary">
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

        <!-- By Provider -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Policies by Provider</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($byProvider)): ?>
                        <p class="text-muted text-center">No data yet</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($byProvider as $provider): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($provider['insurance_provider']) ?>
                                    <span class="badge bg-primary rounded-pill"><?= $provider['count'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightbulb me-2"></i>Tip</h5>
                    <p class="small mb-0">
                        Track all customer dive insurance policies to ensure they have valid coverage
                        before trips and to send renewal reminders proactively.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
