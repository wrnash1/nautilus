<?php $this->layout('layouts/admin', ['title' => $title ?? 'Expiring Policies']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/insurance">Insurance</a></li>
                    <li class="breadcrumb-item active">Expiring</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-exclamation-triangle text-warning me-2"></i>Expiring Policies (Next 60 Days)</h2>
        <a href="/store/insurance" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to All
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($policies)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-1 text-success"></i>
                    <p class="mt-3 text-muted">No policies expiring in the next 60 days</p>
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong><?= count($policies) ?> policies</strong> are expiring within the next 60 days.
                    Consider reaching out to customers for renewals.
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Provider</th>
                                <th>Policy #</th>
                                <th>Expires</th>
                                <th>Days Left</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($policies as $policy): ?>
                                <?php
                                    $expDate = strtotime($policy['expiration_date']);
                                    $daysUntil = floor(($expDate - time()) / 86400);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($policy['first_name'] . ' ' . $policy['last_name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($policy['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($policy['insurance_provider']) ?></td>
                                    <td><code><?= htmlspecialchars($policy['policy_number']) ?></code></td>
                                    <td><?= date('M j, Y', $expDate) ?></td>
                                    <td>
                                        <?php if ($daysUntil <= 7): ?>
                                            <span class="badge bg-danger"><?= $daysUntil ?> days</span>
                                        <?php elseif ($daysUntil <= 30): ?>
                                            <span class="badge bg-warning text-dark"><?= $daysUntil ?> days</span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?= $daysUntil ?> days</span>
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
