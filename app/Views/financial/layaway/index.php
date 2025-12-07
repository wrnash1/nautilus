<?php
$pageTitle = $title ?? 'Layaway Agreements';
$activeMenu = 'financial';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-calendar2-check me-2"></i>Layaway Agreements
        </h1>
        <div>
            <a href="/store/layaway/upcoming" class="btn btn-outline-info me-2">
                <i class="bi bi-bell me-1"></i>Upcoming Payments
            </a>
            <a href="/store/layaway/plans" class="btn btn-outline-secondary me-2">
                <i class="bi bi-gear me-1"></i>Manage Plans
            </a>
            <a href="/store/layaway/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Layaway
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Active Agreements</h6>
                    <h2 class="mb-0"><?= number_format($stats['active'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Pending Down Payment</h6>
                    <h2 class="mb-0"><?= number_format($stats['pending'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed This Month</h6>
                    <h2 class="mb-0"><?= number_format($stats['completed_month'] ?? 0) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Outstanding Balance</h6>
                    <h2 class="mb-0">$<?= number_format($stats['outstanding_balance'] ?? 0, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" <?= ($currentStatus ?? '') === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="pending" <?= ($currentStatus ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="active" <?= ($currentStatus ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= ($currentStatus ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= ($currentStatus ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="defaulted" <?= ($currentStatus ?? '') === 'defaulted' ? 'selected' : '' ?>>Defaulted</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Agreement # or customer name..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Agreements Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($agreements ?? [])): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar2-check display-1 text-muted"></i>
                    <h4 class="mt-3">No layaway agreements found</h4>
                    <p class="text-muted">Create your first layaway agreement to help customers pay over time.</p>
                    <a href="/store/layaway/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Create Layaway Agreement
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Agreement #</th>
                                <th>Customer</th>
                                <th>Plan</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Next Due</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agreements as $agreement): ?>
                                <tr>
                                    <td>
                                        <a href="/store/layaway/<?= $agreement['id'] ?>">
                                            <strong><?= htmlspecialchars($agreement['agreement_number']) ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(($agreement['first_name'] ?? '') . ' ' . ($agreement['last_name'] ?? '')) ?>
                                    </td>
                                    <td><?= htmlspecialchars($agreement['plan_name'] ?? 'N/A') ?></td>
                                    <td>$<?= number_format($agreement['total_due'] ?? 0, 2) ?></td>
                                    <td>$<?= number_format($agreement['amount_paid'] ?? 0, 2) ?></td>
                                    <td>$<?= number_format($agreement['balance_remaining'] ?? 0, 2) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'active' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary',
                                            'defaulted' => 'danger'
                                        ];
                                        $color = $statusColors[$agreement['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= ucfirst($agreement['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($agreement['status'] === 'active' && $agreement['first_payment_date']): ?>
                                            <?= date('M j, Y', strtotime($agreement['first_payment_date'])) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/store/layaway/<?= $agreement['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
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
