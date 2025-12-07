<?php
$pageTitle = $pageTitle ?? 'Referral Program';
$activeMenu = 'marketing';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people me-2"></i>Referral Program
        </h1>
        <a href="/store/marketing/referrals/settings" class="btn btn-outline-primary">
            <i class="bi bi-gear me-1"></i>Settings
        </a>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Referrals</h6>
                    <h2 class="mb-0"><?= number_format($stats['total_referrals'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Converted</h6>
                    <h2 class="mb-0"><?= number_format($stats['converted'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Pending</h6>
                    <h2 class="mb-0"><?= number_format($stats['pending'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Rewards Given</h6>
                    <h2 class="mb-0">$<?= number_format($stats['rewards_given'] ?? 0, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Referrals</h5>
        </div>
        <div class="card-body">
            <?php if (empty($referrals ?? [])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="mt-3">No referrals yet</h4>
                    <p class="text-muted">Configure your referral program settings to start getting referrals.</p>
                    <a href="/store/marketing/referrals/settings" class="btn btn-primary">
                        <i class="bi bi-gear me-1"></i>Configure Program
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Referrer</th>
                                <th>Referred Customer</th>
                                <th>Status</th>
                                <th>Reward</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $referral): ?>
                                <tr>
                                    <td><?= htmlspecialchars($referral['referrer_name'] ?? 'Unknown') ?></td>
                                    <td><?= htmlspecialchars($referral['referred_name'] ?? 'Unknown') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $referral['status'] === 'converted' ? 'success' : ($referral['status'] === 'pending' ? 'warning' : 'secondary') ?>">
                                            <?= ucfirst($referral['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>$<?= number_format($referral['reward_amount'] ?? 0, 2) ?></td>
                                    <td><?= date('M j, Y', strtotime($referral['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
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
