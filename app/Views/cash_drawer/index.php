<?php
$pageTitle = 'Cash Drawer Management';
$activeMenu = 'cash-drawer';
// This view content will be rendered inside layouts/app.php
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-cash-stack"></i> Cash Drawer Management</h1>
        <a href="/store/cash-drawer/history" class="btn btn-outline-primary">
            <i class="bi bi-clock-history"></i> View History
        </a>
    </div>

    <!-- Open Sessions Alert -->
    <?php if (!empty($openSessions)): ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle"></i>
        <strong><?= count($openSessions) ?></strong> drawer session(s) currently open
    </div>
    <?php endif; ?>

    <!-- Open Sessions -->
    <?php if (!empty($openSessions)): ?>
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-unlock-fill"></i> Open Sessions</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Session #</th>
                            <th>Drawer</th>
                            <th>Opened By</th>
                            <th>Opened At</th>
                            <th>Starting Balance</th>
                            <th>Expected Balance</th>
                            <th>Sales Today</th>
                            <th>Hours Open</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($openSessions as $session): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($session['session_number']) ?></code></td>
                            <td>
                                <strong><?= htmlspecialchars($session['drawer_name']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($session['drawer_location']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($session['user_name']) ?></td>
                            <td><?= date('m/d/Y g:i A', strtotime($session['opened_at'])) ?></td>
                            <td><strong><?= formatCurrency($session['starting_balance']) ?></strong></td>
                            <td><strong class="text-primary"><?= formatCurrency($session['expected_current_balance']) ?></strong></td>
                            <td><?= formatCurrency($session['total_sales_today']) ?></td>
                            <td><?= $session['hours_open'] ?> hrs</td>
                            <td>
                                <a href="/store/cash-drawer/session/<?= $session['id'] ?>/close" class="btn btn-sm btn-danger">
                                    <i class="bi bi-lock-fill"></i> Close
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Available Drawers -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Available Drawers</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($drawers as $drawer): ?>
                <?php
                // Check if drawer has an open session
                $hasOpenSession = false;
                foreach ($openSessions as $session) {
                    if ($session['drawer_id'] == $drawer['id']) {
                        $hasOpenSession = true;
                        break;
                    }
                }
                ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 <?= $hasOpenSession ? 'border-success' : ($drawer['is_active'] ? 'border-primary' : 'border-secondary') ?>">
                        <div class="card-header <?= $hasOpenSession ? 'bg-success text-white' : ($drawer['is_active'] ? 'bg-primary text-white' : 'bg-secondary text-white') ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-cash-stack"></i> <?= htmlspecialchars($drawer['name']) ?>
                                </h6>
                                <?php if ($hasOpenSession): ?>
                                <span class="badge bg-light text-dark">OPEN</span>
                                <?php elseif (!$drawer['is_active']): ?>
                                <span class="badge bg-light text-dark">INACTIVE</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Location:</strong> <?= htmlspecialchars($drawer['location'] ?? 'N/A') ?>
                            </p>
                            <p class="mb-2">
                                <strong>Current Balance:</strong>
                                <span class="fs-5 text-primary"><?= formatCurrency($drawer['current_balance']) ?></span>
                            </p>
                            <p class="mb-2">
                                <strong>Starting Float:</strong> <?= formatCurrency($drawer['starting_float']) ?>
                            </p>
                            <?php if ($drawer['notes']): ?>
                            <p class="mb-2 small text-muted">
                                <i class="bi bi-info-circle"></i> <?= htmlspecialchars($drawer['notes']) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <?php if ($hasOpenSession): ?>
                            <button class="btn btn-secondary btn-sm w-100" disabled>
                                <i class="bi bi-lock-fill"></i> Session Already Open
                            </button>
                            <?php elseif ($drawer['is_active']): ?>
                            <a href="/store/cash-drawer/<?= $drawer['id'] ?>/open" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-unlock-fill"></i> Open Drawer
                            </a>
                            <?php else: ?>
                            <button class="btn btn-secondary btn-sm w-100" disabled>
                                <i class="bi bi-x-circle"></i> Inactive
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Drawers</h6>
                            <h2 class="mb-0"><?= count($drawers) ?></h2>
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Open Sessions</h6>
                            <h2 class="mb-0"><?= count($openSessions) ?></h2>
                        </div>
                        <i class="bi bi-unlock-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Active Drawers</h6>
                            <h2 class="mb-0"><?= count(array_filter($drawers, fn($d) => $d['is_active'])) ?></h2>
                        </div>
                        <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Cash</h6>
                            <h2 class="mb-0"><?= formatCurrency(array_sum(array_column($drawers, 'current_balance'))) ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
