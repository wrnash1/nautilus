<?php
$pageTitle = $product['name'];
$activeMenu = 'products';

ob_start();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/products">Products</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> <?= htmlspecialchars($product['name']) ?></h2>
    <div class="btn-group">
        <?php if (hasPermission('products.adjust_stock')): ?>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
            <i class="bi bi-arrow-repeat"></i> Adjust Stock
        </button>
        <?php endif; ?>
        <?php if (hasPermission('products.edit')): ?>
        <a href="/products/<?= $product['id'] ?>/edit" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>SKU:</strong><br>
                        <code><?= htmlspecialchars($product['sku']) ?></code>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge bg-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Category:</strong><br>
                        <?= htmlspecialchars($product['category_name'] ?? 'N/A') ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Vendor:</strong><br>
                        <?= htmlspecialchars($product['vendor_name'] ?? 'N/A') ?>
                    </div>
                </div>
                
                <?php if ($product['description']): ?>
                <div class="mb-3">
                    <strong>Description:</strong><br>
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaction History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($transactions)): ?>
                <p class="text-muted">No transactions recorded.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>User</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $transaction['transaction_type'] === 'sale' ? 'danger' : 'success' ?>">
                                        <?= ucfirst($transaction['transaction_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $transaction['quantity_change'] > 0 ? '+' : '' ?><?= $transaction['quantity_change'] ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(($transaction['first_name'] ?? '') . ' ' . ($transaction['last_name'] ?? '')) ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['reference_type'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pricing</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Cost Price:</strong><br>
                    <h4 class="mb-0"><?= formatCurrency($product['cost_price']) ?></h4>
                </div>
                <div class="mb-3">
                    <strong>Retail Price:</strong><br>
                    <h4 class="mb-0"><?= formatCurrency($product['retail_price']) ?></h4>
                </div>
                <div>
                    <strong>Margin:</strong><br>
                    <?php 
                    $margin = $product['retail_price'] - $product['cost_price'];
                    $marginPercent = $product['cost_price'] > 0 ? ($margin / $product['cost_price']) * 100 : 0;
                    ?>
                    <?= formatCurrency($margin) ?> (<?= number_format($marginPercent, 1) ?>%)
                </div>
            </div>
        </div>
        
        <?php if ($product['track_inventory']): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Inventory</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Current Stock:</strong><br>
                    <?php if ($product['stock_quantity'] <= $product['low_stock_threshold']): ?>
                        <h4 class="mb-0 text-danger"><?= $product['stock_quantity'] ?></h4>
                        <small class="text-danger">Low Stock Alert!</small>
                    <?php else: ?>
                        <h4 class="mb-0 text-success"><?= $product['stock_quantity'] ?></h4>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <strong>Low Stock Threshold:</strong><br>
                    <?= $product['low_stock_threshold'] ?>
                </div>
                <div>
                    <strong>Inventory Value:</strong><br>
                    <?= formatCurrency($product['stock_quantity'] * $product['cost_price']) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if (hasPermission('products.adjust_stock')): ?>
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/products/<?= $product['id'] ?>/adjust-stock">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Current Stock: <strong><?= $product['stock_quantity'] ?></strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity Change *</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" required>
                        <small class="text-muted">Use positive numbers to add stock, negative to remove</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Adjust Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
