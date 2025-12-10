<?php $this->layout('layouts/admin', ['title' => $title ?? 'Pre-Dive Safety Checks']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Safety Checks</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-clipboard-check me-2"></i>Pre-Dive Safety Checks (BWRAF)</h2>
        <div>
            <a href="/store/safety-checks/dashboard" class="btn btn-outline-info me-2">
                <i class="bi bi-graph-up me-1"></i>Dashboard
            </a>
            <a href="/store/safety-checks/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Check
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Passed Today</h6>
                            <h2 class="mb-0"><?= $stats['passed'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-check-circle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Failed Today</h6>
                            <h2 class="mb-0"><?= $stats['failed'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-x-circle display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Incomplete</h6>
                            <h2 class="mb-0"><?= $stats['incomplete'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-hourglass-split display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="passed" <?= $status === 'passed' ? 'selected' : '' ?>>Passed</option>
                        <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="incomplete" <?= $status === 'incomplete' ? 'selected' : '' ?>>Incomplete</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Checks Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($checks)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-check display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No safety checks for this date</p>
                    <a href="/store/safety-checks/create" class="btn btn-primary">Start New Check</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Diver</th>
                                <th>Dive Site</th>
                                <th>Dive Type</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($checks as $check): ?>
                                <tr>
                                    <td><?= date('g:i A', strtotime($check['created_at'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($check['first_name'] . ' ' . $check['last_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($check['dive_site_name'] ?? '-') ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($check['dive_type']) ?></span></td>
                                    <td>
                                        <?php
                                            $statusClass = match($check['check_status']) {
                                                'passed' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                'incomplete' => 'bg-warning text-dark',
                                                'waived' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst($check['check_status']) ?>
                                        </span>
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
