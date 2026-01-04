<?php $this->layout('layouts/admin', ['title' => $title ?? 'Gift Cards']) ?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item active">Gift Cards</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gift me-2"></i>Gift Cards</h2>
        <div>
            <a href="/store/gift-cards/check-balance" class="btn btn-outline-secondary me-2">
                <i class="bi bi-search me-1"></i>Check Balance
            </a>
            <a href="/store/gift-cards/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Issue New Card
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
                            <h6 class="text-white-50 mb-1">Active Cards</h6>
                            <h2 class="mb-0"><?= $stats['active'] ?? 0 ?></h2>
                        </div>
                        <i class="bi bi-credit-card display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Balance</h6>
                            <h2 class="mb-0">$<?= number_format($stats['total_balance'] ?? 0, 2) ?></h2>
                        </div>
                        <i class="bi bi-wallet2 display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Issued</h6>
                            <h2 class="mb-0">$<?= number_format($stats['total_issued'] ?? 0, 2) ?></h2>
                        </div>
                        <i class="bi bi-gift display-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-auto">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Cards</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="redeemed" <?= $status === 'redeemed' ? 'selected' : '' ?>>Fully Redeemed</option>
                        <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($cards)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-gift display-1 text-muted"></i>
                    <p class="mt-3 text-muted">No gift cards found</p>
                    <a href="/store/gift-cards/create" class="btn btn-primary">Issue First Card</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Card Number</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Initial</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Issued</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cards as $card): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($card['card_number']) ?></code></td>
                                    <td>
                                        <?php if ($card['first_name']): ?>
                                            <?= htmlspecialchars($card['first_name'] . ' ' . $card['last_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($card['card_type']) ?></span></td>
                                    <td>$<?= number_format($card['initial_balance'], 2) ?></td>
                                    <td><strong>$<?= number_format($card['current_balance'], 2) ?></strong></td>
                                    <td>
                                        <?php
                                            $statusClass = match($card['status']) {
                                                'active' => 'bg-success',
                                                'inactive' => 'bg-secondary',
                                                'redeemed' => 'bg-info',
                                                'expired' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= ucfirst($card['status']) ?></span>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($card['issued_at'] ?? $card['created_at'])) ?></td>
                                    <td>
                                        <a href="/store/gift-cards/<?= $card['id'] ?>" class="btn btn-sm btn-outline-primary">
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
