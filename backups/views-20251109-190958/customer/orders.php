<?php
$pageTitle = 'My Orders';
ob_start();
?>

<h2>My Orders</h2>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/account" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/account/orders" class="list-group-item list-group-item-action active">
                <i class="bi bi-box-seam"></i> My Orders
            </a>
            <a href="/account/profile" class="list-group-item list-group-item-action">
                <i class="bi bi-person"></i> Profile
            </a>
            <a href="/account/addresses" class="list-group-item list-group-item-action">
                <i class="bi bi-geo-alt"></i> Addresses
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p class="text-muted text-center py-4">No orders yet. <a href="/shop">Start shopping!</a></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= $order['total_items'] ?? '-' ?></td>
                                    <td><?= formatCurrency($order['total']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'shipped' ? 'info' : ($order['status'] === 'processing' ? 'warning' : 'secondary')) ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/account/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
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
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/customer.php';
?>
