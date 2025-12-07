<?php
$pageTitle = 'Order #' . $order['order_number'];
ob_start();
?>

<div class="mb-3">
    <a href="/account/orders" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Order #<?= htmlspecialchars($order['order_number']) ?></h4>
            <span class="badge bg-<?= $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'shipped' ? 'info' : 'warning') ?> fs-6">
                <?= ucfirst($order['status']) ?>
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Order Information</h6>
                <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></p>
                <p><strong>Payment Status:</strong> 
                    <span class="badge bg-<?= $order['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                        <?= ucfirst($order['payment_status']) ?>
                    </span>
                </p>
                <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
                <h6>Shipping Information</h6>
                <?php if (!empty($order['shipping_address_line1'])): ?>
                    <p>
                        <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
                        <?php if (!empty($order['shipping_address_line2'])): ?>
                            <?= htmlspecialchars($order['shipping_address_line2']) ?><br>
                        <?php endif; ?>
                        <?= htmlspecialchars($order['shipping_city']) ?>, 
                        <?= htmlspecialchars($order['shipping_state']) ?> 
                        <?= htmlspecialchars($order['shipping_postal_code']) ?>
                    </p>
                <?php else: ?>
                    <p class="text-muted">No shipping address provided</p>
                <?php endif; ?>
                <?php if (!empty($order['tracking_number'])): ?>
                    <p><strong>Tracking Number:</strong> <?= htmlspecialchars($order['tracking_number']) ?></p>
                    <p><strong>Carrier:</strong> <?= htmlspecialchars($order['shipping_carrier'] ?? 'N/A') ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <h6>Order Items</h6>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['sku']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= formatCurrency($item['unit_price']) ?></td>
                        <td><?= formatCurrency($item['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <table class="table">
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td class="text-end"><?= formatCurrency($order['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Shipping:</strong></td>
                        <td class="text-end"><?= formatCurrency($order['shipping']) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Tax:</strong></td>
                        <td class="text-end"><?= formatCurrency($order['tax']) ?></td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><strong><?= formatCurrency($order['total']) ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/customer.php';
?>
