<?php
$pageTitle = 'Air Fill #' . $airFill['id'];
$activeMenu = 'air-fills';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/air-fills">Air Fills</a></li>
            <li class="breadcrumb-item active">Air Fill #<?= $airFill['id'] ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">
            <i class="bi bi-wind"></i> Air Fill #<?= $airFill['id'] ?>
        </h1>
        <div>
            <?php if (hasPermission('air_fills.update') && !$airFill['transaction_id']): ?>
            <a href="/store/air-fills/<?= $airFill['id'] ?>/edit" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <?php endif; ?>
            <a href="/store/air-fills" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Fill Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Fill Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Fill Type</label>
                        <p class="mb-0">
                            <span class="badge bg-<?=
                                $airFill['fill_type'] === 'air' ? 'primary' :
                                ($airFill['fill_type'] === 'nitrox' ? 'success' :
                                ($airFill['fill_type'] === 'trimix' ? 'warning' : 'info'))
                            ?> fs-6">
                                <?= strtoupper($airFill['fill_type']) ?>
                            </span>
                            <?php if ($airFill['fill_type'] === 'nitrox' && $airFill['nitrox_percentage']): ?>
                                <span class="ms-2"><?= $airFill['nitrox_percentage'] ?>% O2</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Fill Pressure</label>
                        <p class="mb-0"><strong><?= number_format($airFill['fill_pressure']) ?> PSI</strong></p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Cost</label>
                        <p class="mb-0"><strong class="text-success"><?= formatCurrency($airFill['cost']) ?></strong></p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Date & Time</label>
                        <p class="mb-0"><?= date('l, F j, Y g:i A', strtotime($airFill['created_at'])) ?></p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Filled By</label>
                        <p class="mb-0"><?= htmlspecialchars($airFill['filled_by_name']) ?></p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Payment Status</label>
                        <p class="mb-0">
                            <?php if ($airFill['transaction_id']): ?>
                                <span class="badge bg-success">Paid</span>
                                <a href="/transactions/<?= $airFill['transaction_id'] ?>" class="ms-2 small">
                                    View Transaction #<?= $airFill['transaction_id'] ?>
                                </a>
                            <?php else: ?>
                                <span class="badge bg-warning">Unpaid</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if ($airFill['notes']): ?>
                <div class="mt-3 pt-3 border-top">
                    <label class="text-muted small">Notes</label>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($airFill['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customer Information -->
        <?php if ($airFill['customer_name']): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person"></i> Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Name</label>
                        <p class="mb-0">
                            <a href="/customers/<?= $airFill['customer_id'] ?>">
                                <?= htmlspecialchars($airFill['customer_name']) ?>
                            </a>
                        </p>
                    </div>

                    <?php if ($airFill['customer_email']): ?>
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0"><?= htmlspecialchars($airFill['customer_email']) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($airFill['customer_phone']): ?>
                    <div class="col-md-6 mb-2">
                        <label class="text-muted small">Phone</label>
                        <p class="mb-0"><?= htmlspecialchars($airFill['customer_phone']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Equipment Information -->
        <?php if ($airFill['equipment_name']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Equipment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="text-muted small">Equipment</label>
                        <p class="mb-0">
                            <a href="/rentals/equipment/<?= $airFill['equipment_id'] ?>">
                                <?= htmlspecialchars($airFill['equipment_name']) ?>
                            </a>
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Equipment Code</label>
                        <p class="mb-0"><?= htmlspecialchars($airFill['equipment_code']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightning"></i> Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (hasPermission('air_fills.update') && !$airFill['transaction_id']): ?>
                    <a href="/store/air-fills/<?= $airFill['id'] ?>/edit" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Air Fill
                    </a>
                    <?php endif; ?>

                    <?php if (!$airFill['transaction_id'] && $airFill['customer_id']): ?>
                    <form method="POST" action="/pos/air-fill-transaction">
                        <input type="hidden" name="air_fill_id" value="<?= $airFill['id'] ?>">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-credit-card"></i> Create Transaction
                        </button>
                    </form>
                    <?php endif; ?>

                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="bi bi-printer"></i> Print Receipt
                    </button>

                    <?php if (hasPermission('air_fills.delete') && !$airFill['transaction_id']): ?>
                    <form method="POST" action="/store/air-fills/<?= $airFill['id'] ?>"
                          onsubmit="return confirm('Are you sure you want to delete this air fill?')">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Summary</h6>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="small text-muted">Record ID</dt>
                    <dd>#<?= $airFill['id'] ?></dd>

                    <dt class="small text-muted">Created</dt>
                    <dd><?= date('M d, Y g:i A', strtotime($airFill['created_at'])) ?></dd>

                    <dt class="small text-muted">Status</dt>
                    <dd>
                        <?php if ($airFill['transaction_id']): ?>
                            <span class="badge bg-success">Completed & Paid</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Recorded</span>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
