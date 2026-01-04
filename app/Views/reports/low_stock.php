<?php
$pageTitle = 'Low Stock Report';
$activeMenu = 'reports';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-exclamation-triangle"></i> Low Stock Report</h2>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> All products are adequately stocked!
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> <?= count($products) ?> product(s) below low stock threshold
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Threshold</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($product['sku']) ?></code></td>
                        <td>
                            <a href="/products/<?= $product['id'] ?>">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-danger"><?= $product['stock_quantity'] ?></span>
                        </td>
                        <td><?= $product['low_stock_threshold'] ?></td>
                        <td>
                            <a href="/products/<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-repeat"></i> Adjust Stock
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
