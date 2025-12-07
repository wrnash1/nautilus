<?php $this->layout('layouts/admin', ['title' => $title ?? 'Insurance Policies']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Insurance</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-check me-2"></i>Dive Insurance Policies</h2>
        <div>
            <a href="/store/insurance/dashboard" class="btn btn-outline-primary me-2">
                <i class="bi bi-bar-chart me-1"></i>Dashboard
            </a>
            <a href="/store/insurance/expiring" class="btn btn-outline-warning me-2">
                <i class="bi bi-exclamation-triangle me-1"></i>Expiring
            </a>
            <a href="/store/insurance/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Policy
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
                            <h6 class="mb-1">Expiring Soon</h6>
                            <h2 class="mb-0"><?= $stats['expiring'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Expired</h6>
                            <h2 class="mb-0"><?= $stats['expired'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-shield-x display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($policies)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-shield display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No insurance policies recorded yet</p>
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
                                <th>Type</th>
                                <th>Coverage</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($policies as $policy): ?>
                                <?php
                                    $expDate = strtotime($policy['expiration_date']);
                                    $now = time();
                                    $daysUntil = floor(($expDate - $now) / 86400);
                                    $isExpired = $expDate < $now;
                                    $isExpiringSoon = !$isExpired && $daysUntil <= 30;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($policy['first_name'] . ' ' . $policy['last_name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($policy['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($policy['insurance_provider']) ?></td>
                                    <td><code><?= htmlspecialchars($policy['policy_number']) ?></code></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($policy['policy_type']) ?></span></td>
                                    <td><?= htmlspecialchars($policy['coverage_level'] ?? '-') ?></td>
                                    <td>
                                        <?= date('M j, Y', $expDate) ?>
                                        <?php if (!$isExpired && $daysUntil <= 30): ?>
                                            <br><small class="text-warning"><?= $daysUntil ?> days left</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-danger">Expired</span>
                                        <?php elseif ($isExpiringSoon): ?>
                                            <span class="badge bg-warning text-dark">Expiring</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
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
