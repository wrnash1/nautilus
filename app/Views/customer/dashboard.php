<?php
$pageTitle = 'My Account';
ob_start();
?>

<h2>Welcome, <?= htmlspecialchars($customer['first_name']) ?>!</h2>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="list-group">
            <a href="/account" class="list-group-item list-group-item-action active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="/account/orders" class="list-group-item list-group-item-action">
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($customer['phone'] ?? 'Not provided') ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Member Since:</strong> <?= date('F j, Y', strtotime($customer['customer_since'] ?? $customer['created_at'])) ?></p>
                        <p><strong>Total Orders:</strong> <?= count($orders) ?></p>
                        <p><strong>Lifetime Value:</strong> <?= formatCurrency($customer['lifetime_value'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="/account/orders" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($orders)): ?>
                    <p class="text-muted">No orders yet. <a href="/shop">Start shopping!</a></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['order_number']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= formatCurrency($order['total']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'shipped' ? 'info' : 'warning') ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/account/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
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
