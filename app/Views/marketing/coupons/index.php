<?php
$pageTitle = 'Coupons';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Coupons</h2>
    <?php if (\App\Core\Auth::hasPermission('marketing.create')): ?>
        <a href="<?= url('/marketing/coupons/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Coupon
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Times Used</th>
                        <th>Total Discount</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($coupon['code']) ?></code></td>
                            <td><?= ucfirst($coupon['discount_type']) ?></td>
                            <td><?= $coupon['discount_type'] === 'percentage' ? $coupon['discount_value'] . '%' : '$' . number_format($coupon['discount_value'], 2) ?></td>
                            <td><?= $coupon['times_used'] ?></td>
                            <td>$<?= number_format($coupon['total_discount'], 2) ?></td>
                            <td><?= $coupon['valid_until'] ? date('M d, Y', strtotime($coupon['valid_until'])) : 'No expiry' ?></td>
                            <td>
                                <span class="badge bg-<?= $coupon['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $coupon['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= url('/marketing/coupons/' . $coupon['id']) ?>" class="btn btn-sm btn-info">View</a>
                                <?php if (\App\Core\Auth::hasPermission('marketing.edit')): ?>
                                    <a href="<?= url('/marketing/coupons/' . $coupon['id'] . '/edit') ?>" class="btn btn-sm btn-warning">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
