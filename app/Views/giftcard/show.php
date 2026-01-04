<?php $this->layout('layouts/admin', ['title' => $title ?? 'Gift Card Details']) ?>

<?php
    $isExpired = strtotime($card['expiry_date']) < time();
    $isActive = $card['status'] === 'active' && !$isExpired;
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/store/gift-cards">Gift Cards</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-gift me-2"></i>Gift Card Details</h2>
        <a href="/store/gift-cards" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Card Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Card Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Card Number</h6>
                            <h3><code><?= htmlspecialchars($card['card_number']) ?></code></h3>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted">Status</h6>
                            <?php if ($isExpired): ?>
                                <span class="badge bg-danger fs-5">Expired</span>
                            <?php elseif ($card['status'] === 'active'): ?>
                                <span class="badge bg-success fs-5">Active</span>
                            <?php elseif ($card['status'] === 'inactive'): ?>
                                <span class="badge bg-secondary fs-5">Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-info fs-5"><?= ucfirst($card['status']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">PIN:</th>
                                    <td><code class="fs-5"><?= htmlspecialchars($card['pin']) ?></code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Type:</th>
                                    <td><span class="badge bg-secondary"><?= ucfirst($card['card_type']) ?></span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Customer:</th>
                                    <td>
                                        <?php if ($card['first_name']): ?>
                                            <a href="/store/customers/<?= $card['customer_id'] ?>">
                                                <?= htmlspecialchars($card['first_name'] . ' ' . $card['last_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-muted" style="width:40%">Issued:</th>
                                    <td><?= date('M j, Y', strtotime($card['issued_at'] ?? $card['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Expires:</th>
                                    <td>
                                        <?= date('M j, Y', strtotime($card['expiry_date'])) ?>
                                        <?php if ($isExpired): ?>
                                            <span class="badge bg-danger ms-1">Expired</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted">Initial Balance</h6>
                            <h3>$<?= number_format($card['initial_balance'], 2) ?></h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Used</h6>
                            <h3 class="text-danger">$<?= number_format($card['initial_balance'] - $card['current_balance'], 2) ?></h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Current Balance</h6>
                            <h3 class="text-success">$<?= number_format($card['current_balance'], 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaction History</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <p class="text-muted text-center py-3">No transactions yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Before</th>
                                        <th>After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr>
                                            <td><?= date('M j, Y g:i A', strtotime($tx['created_at'])) ?></td>
                                            <td>
                                                <?php
                                                    $typeClass = match($tx['transaction_type']) {
                                                        'issue' => 'bg-success',
                                                        'reload' => 'bg-primary',
                                                        'redemption' => 'bg-warning text-dark',
                                                        'refund' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?= $typeClass ?>"><?= ucfirst($tx['transaction_type']) ?></span>
                                            </td>
                                            <td>
                                                <?php if (in_array($tx['transaction_type'], ['issue', 'reload', 'refund'])): ?>
                                                    <span class="text-success">+$<?= number_format($tx['amount'], 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger">-$<?= number_format($tx['amount'], 2) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>$<?= number_format($tx['balance_before'], 2) ?></td>
                                            <td>$<?= number_format($tx['balance_after'], 2) ?></td>
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
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($isActive): ?>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reloadModal">
                                <i class="bi bi-plus-circle me-2"></i>Reload Card
                            </button>
                            <form method="POST" action="/store/gift-cards/<?= $card['id'] ?>/deactivate"
                                  onsubmit="return confirm('Are you sure you want to deactivate this card?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-x-circle me-2"></i>Deactivate Card
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Card is not active</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Print Card -->
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-printer display-4 text-muted"></i>
                    <p class="mt-2 mb-0 text-muted">Print card receipt or certificate</p>
                    <button class="btn btn-outline-secondary btn-sm mt-2" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reload Modal -->
<div class="modal fade" id="reloadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/store/gift-cards/<?= $card['id'] ?>/reload">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Reload Gift Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount to Add</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" required>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        Current balance: $<?= number_format($card['current_balance'], 2) ?>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Add Funds
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
