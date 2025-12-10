<?php
$pageTitle = $title ?? 'Layaway Plans';
$activeMenu = 'financial';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-gear me-2"></i>Layaway Plans
        </h1>
        <div>
            <a href="/store/layaway" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal" onclick="editPlan(null)">
                <i class="bi bi-plus-lg me-1"></i>New Plan
            </button>
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

    <div class="row">
        <?php if (empty($plans ?? [])): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar3 display-1 text-muted"></i>
                        <h4 class="mt-3">No layaway plans configured</h4>
                        <p class="text-muted">Create your first layaway plan to start offering payment plans to customers.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal" onclick="editPlan(null)">
                            <i class="bi bi-plus-lg me-1"></i>Create First Plan
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($plans as $plan): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?= $plan['is_active'] ? '' : 'border-secondary opacity-75' ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?= htmlspecialchars($plan['plan_name']) ?></h5>
                            <?php if ($plan['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <p class="text-muted"><?= htmlspecialchars($plan['description'] ?? 'No description') ?></p>

                            <table class="table table-sm">
                                <tr>
                                    <td class="text-muted">Payments:</td>
                                    <td><strong><?= $plan['number_of_payments'] ?> <?= ucfirst($plan['payment_frequency']) ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Down Payment:</td>
                                    <td><?= $plan['down_payment_percentage'] ?>% (min $<?= number_format($plan['down_payment_minimum'], 2) ?>)</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Layaway Fee:</td>
                                    <td>
                                        <?php if ($plan['layaway_fee'] > 0): ?>
                                            <?= $plan['layaway_fee_type'] === 'percentage' ? $plan['layaway_fee'] . '%' : '$' . number_format($plan['layaway_fee'], 2) ?>
                                        <?php else: ?>
                                            None
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Min Purchase:</td>
                                    <td>$<?= number_format($plan['min_purchase_amount'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cancellation Fee:</td>
                                    <td>$<?= number_format($plan['cancellation_fee'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Restocking Fee:</td>
                                    <td><?= $plan['restocking_fee_percentage'] ?>%</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#planModal"
                                    onclick='editPlan(<?= json_encode($plan) ?>)'>
                                <i class="bi bi-pencil me-1"></i>Edit
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/store/layaway/plans" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" id="planId" value="0">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">New Layaway Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="plan_name" id="planName" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Purchase</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="min_purchase_amount" id="minPurchase" class="form-control" step="0.01" value="100">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="planDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Number of Payments</label>
                            <input type="number" name="number_of_payments" id="numPayments" class="form-control" value="4" min="2" max="24">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Frequency</label>
                            <select name="payment_frequency" id="paymentFreq" class="form-select">
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-weekly</option>
                                <option value="monthly" selected>Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Down Payment %</label>
                            <div class="input-group">
                                <input type="number" name="down_payment_percentage" id="downPct" class="form-control" step="0.1" value="25">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Down Payment Minimum</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="down_payment_minimum" id="downMin" class="form-control" step="0.01" value="50">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Layaway Fee</label>
                            <input type="number" name="layaway_fee" id="layawayFee" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fee Type</label>
                            <select name="layaway_fee_type" id="feeType" class="form-select">
                                <option value="fixed">Fixed ($)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cancellation Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="cancellation_fee" id="cancelFee" class="form-control" step="0.01" value="25">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Restocking Fee %</label>
                            <div class="input-group">
                                <input type="number" name="restocking_fee_percentage" id="restockPct" class="form-control" step="0.1" value="10">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                                <label class="form-check-label" for="isActive">Active (available for new agreements)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPlan(plan) {
    if (plan) {
        document.getElementById('modalTitle').textContent = 'Edit Layaway Plan';
        document.getElementById('planId').value = plan.id;
        document.getElementById('planName').value = plan.plan_name;
        document.getElementById('planDescription').value = plan.description || '';
        document.getElementById('numPayments').value = plan.number_of_payments;
        document.getElementById('paymentFreq').value = plan.payment_frequency;
        document.getElementById('downPct').value = plan.down_payment_percentage;
        document.getElementById('downMin').value = plan.down_payment_minimum;
        document.getElementById('layawayFee').value = plan.layaway_fee;
        document.getElementById('feeType').value = plan.layaway_fee_type;
        document.getElementById('minPurchase').value = plan.min_purchase_amount;
        document.getElementById('cancelFee').value = plan.cancellation_fee;
        document.getElementById('restockPct').value = plan.restocking_fee_percentage;
        document.getElementById('isActive').checked = plan.is_active == 1;
    } else {
        document.getElementById('modalTitle').textContent = 'New Layaway Plan';
        document.getElementById('planId').value = 0;
        document.getElementById('planName').value = '';
        document.getElementById('planDescription').value = '';
        document.getElementById('numPayments').value = 4;
        document.getElementById('paymentFreq').value = 'monthly';
        document.getElementById('downPct').value = 25;
        document.getElementById('downMin').value = 50;
        document.getElementById('layawayFee').value = 0;
        document.getElementById('feeType').value = 'fixed';
        document.getElementById('minPurchase').value = 100;
        document.getElementById('cancelFee').value = 25;
        document.getElementById('restockPct').value = 10;
        document.getElementById('isActive').checked = true;
    }
}
</script>
