<?php
$pageTitle = 'Inventory Report';
$activeMenu = 'reports';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-data"></i> Inventory Report</h2>
    <button onclick="exportInventoryCsv()" class="btn btn-success">
        <i class="bi bi-download"></i> Export CSV
    </button>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
        <p class="text-muted text-center py-4">No inventory data available.</p>
        <?php else: ?>
        <?php
        $totalValue = 0;
        $totalStock = 0;
        foreach ($products as $product) {
            $totalValue += $product['inventory_value'];
            $totalStock += $product['stock_quantity'];
        }
        ?>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase">Total Products</h6>
                        <h3><?= count($products) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase">Total Stock Units</h6>
                        <h3><?= number_format($totalStock) ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="text-uppercase">Total Inventory Value</h6>
                        <h3><?= formatCurrency($totalValue) ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Threshold</th>
                        <th>Cost</th>
                        <th>Retail</th>
                        <th>Value</th>
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
                            <?php if ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                                <span class="badge bg-danger"><?= $product['stock_quantity'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-success"><?= $product['stock_quantity'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= $product['low_stock_threshold'] ?></td>
                        <td><?= formatCurrency($product['cost_price']) ?></td>
                        <td><?= formatCurrency($product['retail_price']) ?></td>
                        <td><?= formatCurrency($product['inventory_value']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="7" class="text-end">Total Inventory Value:</td>
                        <td><?= formatCurrency($totalValue) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = <<<'JS'
<script>
function exportInventoryCsv() {
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = '/reports/inventory/export';
    document.body.appendChild(iframe);
    
    setTimeout(() => {
        document.body.removeChild(iframe);
    }, 5000);
}
</script>
JS;

require __DIR__ . '/../layouts/app.php';
?>
