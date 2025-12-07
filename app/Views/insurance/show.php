<?php $this->layout('layouts/admin', ['title' => $title ?? 'Policy Details']) ?>

<?php
    $expDate = strtotime($policy['expiration_date']);
    $now = time();
    $daysUntil = floor(($expDate - $now) / 86400);
    $isExpired = $expDate < $now;
    $isExpiringSoon = !$isExpired && $daysUntil <= 30;
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/insurance">Insurance</a></li>
                    <li class="breadcrumb-item active">Policy Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-check me-2"></i>Policy Details</h2>
        <div>
            <a href="/store/insurance/<?= $policy['id'] ?>/edit" class="btn btn-outline-primary me-2">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <a href="/store/insurance" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <?php if ($isExpired): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>This policy has expired!</strong> Please renew immediately to ensure coverage.
        </div>
    <?php elseif ($isExpiringSoon): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>This policy expires in <?= $daysUntil ?> days.</strong> Consider sending a renewal reminder.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Policy Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Policy Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Customer</h6>
                            <h4><?= htmlspecialchars($policy['first_name'] . ' ' . $policy['last_name']) ?></h4>
                            <p class="text-muted mb-0"><?= htmlspecialchars($policy['email']) ?></p>
                            <?php if ($policy['phone']): ?>
                                <p class="text-muted"><?= htmlspecialchars($policy['phone']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted">Status</h6>
                            <?php if ($isExpired): ?>
                                <span class="badge bg-danger fs-5">Expired</span>
                            <?php elseif ($isExpiringSoon): ?>
                                <span class="badge bg-warning text-dark fs-5">Expiring Soon</span>
                            <?php else: ?>
                                <span class="badge bg-success fs-5">Active</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Provider:</th>
                                    <td><strong><?= htmlspecialchars($policy['insurance_provider']) ?></strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Policy Number:</th>
                                    <td><code class="fs-5"><?= htmlspecialchars($policy['policy_number']) ?></code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Policy Type:</th>
                                    <td><span class="badge bg-secondary"><?= ucfirst($policy['policy_type']) ?></span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Coverage Level:</th>
                                    <td><?= htmlspecialchars($policy['coverage_level'] ?? '-') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Effective:</th>
                                    <td><?= date('M j, Y', strtotime($policy['effective_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Expires:</th>
                                    <td>
                                        <strong><?= date('M j, Y', $expDate) ?></strong>
                                        <?php if (!$isExpired): ?>
                                            <br><small class="text-muted"><?= $daysUntil ?> days remaining</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Coverage Amount:</th>
                                    <td><?= $policy['coverage_amount'] ? '$' . number_format($policy['coverage_amount'], 2) : '-' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Deductible:</th>
                                    <td><?= $policy['deductible'] ? '$' . number_format($policy['deductible'], 2) : '-' ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coverage Details -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Coverage Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-<?= $policy['covers_hyperbaric'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?> fs-4 me-2"></i>
                                <span>Hyperbaric Treatment</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-<?= $policy['covers_evacuation'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?> fs-4 me-2"></i>
                                <span>Emergency Evacuation</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-<?= $policy['covers_recompression'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?> fs-4 me-2"></i>
                                <span>Recompression</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-<?= $policy['covers_medical'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?> fs-4 me-2"></i>
                                <span>Medical Coverage</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-<?= $policy['covers_equipment'] ? 'check-circle-fill text-success' : 'x-circle text-danger' ?> fs-4 me-2"></i>
                                <span>Equipment Coverage</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Emergency Contacts -->
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-telephone me-2"></i>Emergency Contacts</h5>
                    <?php if ($policy['emergency_phone']): ?>
                        <p class="mb-2">
                            <strong>Emergency:</strong><br>
                            <a href="tel:<?= htmlspecialchars($policy['emergency_phone']) ?>" class="text-white fs-5">
                                <?= htmlspecialchars($policy['emergency_phone']) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if ($policy['claims_phone']): ?>
                        <p class="mb-0">
                            <strong>Claims:</strong><br>
                            <a href="tel:<?= htmlspecialchars($policy['claims_phone']) ?>" class="text-white">
                                <?= htmlspecialchars($policy['claims_phone']) ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    <?php if (!$policy['emergency_phone'] && !$policy['claims_phone']): ?>
                        <p class="mb-0 text-white-50">No emergency contacts on file</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/store/insurance/<?= $policy['id'] ?>/edit" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-2"></i>Edit Policy
                        </a>
                        <a href="/store/customers/<?= $policy['customer_id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-person me-2"></i>View Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
