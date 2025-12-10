<?php
$subject = 'Order Confirmation - Order #' . ($orderNumber ?? 'N/A');
ob_start();
?>

<h2>Thank You for Your Order!</h2>

<p>Hi <?= htmlspecialchars($firstName ?? 'Customer') ?>,</p>

<p>We've received your order and are getting it ready. Here are the details:</p>

<div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
    <h3 style="margin-top: 0;">Order #<?= htmlspecialchars($orderNumber ?? 'N/A') ?></h3>
    <p><strong>Order Date:</strong> <?= date('F j, Y', strtotime($orderDate ?? 'now')) ?></p>
    <p><strong>Total:</strong> $<?= number_format($orderTotal ?? 0, 2) ?></p>
</div>

<?php if (isset($items) && is_array($items)): ?>
<h3>Order Items:</h3>
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #f8f9fa; text-align: left;">
            <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">Item</th>
            <th style="padding: 10px; border-bottom: 2px solid #dee2e6;">Qty</th>
            <th style="padding: 10px; border-bottom: 2px solid #dee2e6; text-align: right;">Price</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?= htmlspecialchars($item['name']) ?></td>
            <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?= htmlspecialchars($item['quantity']) ?></td>
            <td style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: right;">$<?= number_format($item['price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<a href="<?= $_ENV['APP_URL'] ?? 'https://nautilus.com' ?>/orders/<?= $orderNumber ?? '' ?>" class="btn">View Order Details</a>

<p>We'll send you another email once your order has shipped.</p>

<p>Thank you for your business!</p>

<?php
$content = ob_get_clean();
include __DIR__ . '/base.php';
?>
