<?php
$pageTitle = $title ?? 'Layaway Agreement';
$activeMenu = 'financial';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-calendar2-check me-2"></i><?= htmlspecialchars($agreement['agreement_number'] ?? 'Agreement') ?>
        </h1>
        <a href="/store/layaway" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Agreements
        </a>
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

    <div class="row">
        <div class="col-lg-8">
            <!-- Agreement Details -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Agreement Details</h5>
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
                    <span class="badge bg-<?= $color ?> fs-6">
                        <?= ucfirst($agreement['status']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th class="text-muted">Agreement #:</th>
                                    <td><?= htmlspecialchars($agreement['agreement_number']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Plan:</th>
                                    <td><?= htmlspecialchars($agreement['plan_name'] ?? 'N/A') ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Created:</th>
                                    <td><?= date('M j, Y', strtotime($agreement['created_at'])) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Payment Frequency:</th>
                                    <td><?= ucfirst($agreement['payment_frequency'] ?? 'monthly') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th class="text-muted">First Payment:</th>
                                    <td><?= $agreement['first_payment_date'] ? date('M j, Y', strtotime($agreement['first_payment_date'])) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Final Payment:</th>
                                    <td><?= $agreement['final_payment_date'] ? date('M j, Y', strtotime($agreement['final_payment_date'])) : 'N/A' ?></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Payments Made:</th>
                                    <td><?= $agreement['payments_made'] ?? 0 ?> of <?= $agreement['number_of_payments'] ?? 0 ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Customer</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <?= strtoupper(substr($agreement['first_name'] ?? '', 0, 1) . substr($agreement['last_name'] ?? '', 0, 1)) ?>
                        </div>
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars(($agreement['first_name'] ?? '') . ' ' . ($agreement['last_name'] ?? '')) ?></h5>
                            <small class="text-muted"><?= htmlspecialchars($agreement['email'] ?? '') ?></small><br>
                            <small class="text-muted"><?= htmlspecialchars($agreement['phone'] ?? '') ?></small>
                        </div>
                        <a href="/store/customers/<?= $agreement['customer_id'] ?>" class="btn btn-sm btn-outline-primary ms-auto">
                            <i class="bi bi-person me-1"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Items</h5>
                </div>
                <div class="card-body">
                    <?php
                    $items = json_decode($agreement['items'] ?? '[]', true);
                    if (empty($items)):
                    ?>
                        <p class="text-muted">No items recorded</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['name'] ?? 'Unknown') ?></td>
                                            <td><?= htmlspecialchars($item['sku'] ?? '') ?></td>
                                            <td class="text-end">$<?= number_format($item['price'] ?? 0, 2) ?></td>
                                            <td class="text-center"><?= $item['quantity'] ?? 1 ?></td>
                                            <td class="text-end">$<?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Schedule -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Schedule</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($schedule ?? [])): ?>
                        <p class="text-muted">No payment schedule available</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount Due</th>
                                        <th class="text-end">Amount Paid</th>
                                        <th>Status</th>
                                        <th>Paid Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedule as $payment): ?>
                                        <?php
                                        $pStatusColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'partial' => 'info',
                                            'late' => 'danger'
                                        ];
                                        $pColor = $pStatusColors[$payment['payment_status']] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td><?= $payment['payment_number'] ?></td>
                                            <td><?= date('M j, Y', strtotime($payment['due_date'])) ?></td>
                                            <td class="text-end">$<?= number_format($payment['amount_due'], 2) ?></td>
                                            <td class="text-end">$<?= number_format($payment['amount_paid'] ?? 0, 2) ?></td>
                                            <td><span class="badge bg-<?= $pColor ?>"><?= ucfirst($payment['payment_status']) ?></span></td>
                                            <td><?= $payment['paid_date'] ? date('M j, Y', strtotime($payment['paid_date'])) : '-' ?></td>
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
            <!-- Financial Summary -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Financial Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Product Total:</span>
                        <strong>$<?= number_format($agreement['total_amount'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Layaway Fee:</span>
                        <span>$<?= number_format($agreement['layaway_fee'] ?? 0, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total Due:</strong>
                        <strong>$<?= number_format($agreement['total_due'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Down Payment:</span>
                        <span>$<?= number_format($agreement['down_payment'] ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Amount Paid:</span>
                        <strong>$<?= number_format($agreement['amount_paid'] ?? 0, 2) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 fs-5">
                        <strong>Balance Remaining:</strong>
                        <strong class="text-danger">$<?= number_format($agreement['balance_remaining'] ?? 0, 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <small>Per Payment:</small>
                        <small>$<?= number_format($agreement['payment_amount'] ?? 0, 2) ?></small>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <?php if ($agreement['status'] === 'active' || $agreement['status'] === 'pending'): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <!-- Record Payment -->
                        <form action="/store/layaway/<?= $agreement['id'] ?>/payment" method="POST" class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="mb-2">
                                <label class="form-label">Record Payment</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                                           value="<?= number_format($agreement['payment_amount'] ?? 0, 2) ?>" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-cash me-1"></i>Record Payment
                            </button>
                        </form>

                        <hr>

                        <!-- Cancel Agreement -->
                        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="bi bi-x-circle me-1"></i>Cancel Agreement
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Print Receipt -->
            <div class="d-grid">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i>Print Agreement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/store/layaway/<?= $agreement['id'] ?>/cancel" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Layaway Agreement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This action cannot be undone. The reserved products will be released back to inventory.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="process_refund" class="form-check-input" id="processRefund" checked>
                        <label class="form-check-label" for="processRefund">
                            Process refund (minus cancellation/restocking fees)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Agreement</button>
                    <button type="submit" class="btn btn-danger">Cancel Agreement</button>
                </div>
            </form>
        </div>
    </div>
</div>
