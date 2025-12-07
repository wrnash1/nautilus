<?php require_once __DIR__ . '/../layouts/app.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Order <?= htmlspecialchars($order['order_number']) ?></h2>
    <a href="/orders" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Order Number:</strong><br>
                        <?= htmlspecialchars($order['order_number']) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Order Date:</strong><br>
                        <?= date('F j, Y g:i A', strtotime($order['created_at'])) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Order Type:</strong><br>
                        <span class="badge bg-info"><?= ucfirst($order['order_type']) ?></span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        <?php
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$order['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($order['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                <?php if ($order['customer_phone']): ?>
                <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Addresses</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Shipping Address</h6>
                        <?php if ($order['shipping_address_line1']): ?>
                            <p class="mb-0">
                                <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
                                <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?> <?= htmlspecialchars($order['shipping_postal_code']) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">No shipping address</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6>Billing Address</h6>
                        <?php if ($order['billing_address_line1']): ?>
                            <p class="mb-0">
                                <?= htmlspecialchars($order['billing_address_line1']) ?><br>
                                <?= htmlspecialchars($order['billing_city']) ?>, <?= htmlspecialchars($order['billing_state']) ?> <?= htmlspecialchars($order['billing_postal_code']) ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">Same as shipping</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body">
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
                                <td>$<?= number_format($item['unit_price'], 2) ?></td>
                                <td>$<?= number_format($item['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td>$<?= number_format($order['subtotal'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                <td>$<?= number_format($order['shipping'], 2) ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Tax:</strong></td>
                                <td>$<?= number_format($order['tax'], 2) ?></td>
                            </tr>
                            <?php if ($order['discount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Discount:</strong></td>
                                <td class="text-danger">-$<?= number_format($order['discount'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong>$<?= number_format($order['total'], 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Status</h5>
            </div>
            <div class="card-body">
                <?php
                $paymentColors = [
                    'pending' => 'warning',
                    'paid' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'secondary'
                ];
                $color = $paymentColors[$order['payment_status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst($order['payment_status']) ?></span>
            </div>
        </div>

        <?php if (hasPermission('orders.edit')): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Update Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/orders/<?= $order['id'] ?>/status">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <div class="mb-3">
                        <label for="status" class="form-label">Order Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if (hasPermission('orders.ship') && $order['status'] !== 'shipped' && $order['status'] !== 'delivered'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ship Order</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/orders/<?= $order['id'] ?>/ship">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <div class="mb-3">
                        <label for="carrier" class="form-label">Carrier</label>
                        <select class="form-select" id="carrier" name="carrier" required>
                            <option value="USPS">USPS</option>
                            <option value="UPS">UPS</option>
                            <option value="FedEx">FedEx</option>
                            <option value="DHL">DHL</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="service" class="form-label">Service</label>
                        <input type="text" class="form-control" id="service" name="service" placeholder="e.g., Ground, Priority" required>
                    </div>
                    <div class="mb-3">
                        <label for="tracking_number" class="form-label">Tracking Number</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-truck"></i> Mark as Shipped
                    </button>
                </form>
            </div>
        </div>
        <?php elseif ($order['tracking_number']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tracking Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Tracking Number:</strong><br><?= htmlspecialchars($order['tracking_number']) ?></p>
                <?php if ($order['shipped_at']): ?>
                <p><strong>Shipped:</strong><br><?= date('M j, Y', strtotime($order['shipped_at'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
