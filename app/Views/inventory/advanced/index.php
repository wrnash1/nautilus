<?php
$pageTitle = 'Advanced Inventory Management';
$activeMenu = 'inventory';

ob_start();
?>

<style>
.inventory-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}

.inventory-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-card.highlight {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
}

.stat-card.alert {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-left: 4px solid #dc2626;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 4px;
}

.stat-meta {
    font-size: 13px;
    color: #94a3b8;
}

.action-bar {
    display: flex;
    gap: 12px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.btn-action {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.btn-action.secondary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-body {
    padding: 24px;
}

.product-grid {
    display: grid;
    gap: 16px;
}

.product-row {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto;
    gap: 16px;
    align-items: center;
    transition: all 0.2s;
}

.product-row:hover {
    border-color: #8b5cf6;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
}

.product-row.critical {
    border-color: #dc2626;
    background: #fef2f2;
}

.product-row.warning {
    border-color: #f59e0b;
    background: #fffbeb;
}

.product-info h4 {
    margin: 0 0 4px 0;
    font-size: 15px;
}

.product-info .meta {
    font-size: 13px;
    color: #64748b;
}

.stock-indicator {
    text-align: center;
}

.stock-value {
    font-size: 24px;
    font-weight: bold;
}

.stock-value.critical {
    color: #dc2626;
}

.stock-value.warning {
    color: #f59e0b;
}

.stock-value.ok {
    color: #10b981;
}

.stock-label {
    font-size: 12px;
    color: #64748b;
}

.velocity-badge {
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.velocity-badge.fast {
    background: #dcfce7;
    color: #166534;
}

.velocity-badge.slow {
    background: #fef3c7;
    color: #92400e;
}

.btn-small {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-reorder {
    background: #8b5cf6;
    color: white;
}

.btn-reorder:hover {
    background: #7c3aed;
}

.tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
}

.tab {
    padding: 12px 24px;
    border: none;
    background: none;
    cursor: pointer;
    font-weight: 600;
    color: #64748b;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}

.tab.active {
    color: #8b5cf6;
    border-bottom-color: #8b5cf6;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #94a3b8;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.3;
}
</style>

<div class="inventory-header">
    <h1><i class="bi bi-boxes"></i> Advanced Inventory Management</h1>
    <p>Automated reordering, forecasting, and inventory optimization</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Inventory Value</div>
        <div class="stat-value">$<?= number_format($statistics['total_retail_value'], 0) ?></div>
        <div class="stat-meta">Retail value of <?= number_format($statistics['total_units']) ?> units</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Cost Basis</div>
        <div class="stat-value">$<?= number_format($statistics['total_cost_value'], 0) ?></div>
        <div class="stat-meta">Potential profit: $<?= number_format($statistics['potential_profit'], 0) ?></div>
    </div>

    <div class="stat-card highlight">
        <div class="stat-label">Low Stock Items</div>
        <div class="stat-value"><?= $statistics['low_stock_count'] ?></div>
        <div class="stat-meta">Below reorder point</div>
    </div>

    <div class="stat-card alert">
        <div class="stat-label">Out of Stock</div>
        <div class="stat-value"><?= $statistics['out_of_stock_count'] ?></div>
        <div class="stat-meta">Zero inventory</div>
    </div>
</div>

<!-- Action Bar -->
<div class="action-bar">
    <a href="/inventory/advanced/reorder" class="btn-action">
        <i class="bi bi-cart-plus"></i>
        Reorder Management
    </a>
    <a href="/inventory/advanced/cycle-count" class="btn-action secondary">
        <i class="bi bi-clipboard-check"></i>
        Cycle Count
    </a>
    <a href="/inventory/advanced/valuation" class="btn-action secondary">
        <i class="bi bi-currency-dollar"></i>
        Valuation Report
    </a>
    <button class="btn-action secondary" onclick="exportInventory()">
        <i class="bi bi-download"></i>
        Export Data
    </button>
</div>

<!-- Tabs -->
<div class="section-card">
    <div class="card-header">
        <h3><i class="bi bi-graph-up"></i> Inventory Analysis</h3>
    </div>
    <div class="card-body">
        <div class="tabs">
            <button class="tab active" onclick="switchTab('reorder')">
                Needs Reordering (<?= count($needingReorder) ?>)
            </button>
            <button class="tab" onclick="switchTab('fast')">
                Fast Moving (<?= count($fastMoving) ?>)
            </button>
            <button class="tab" onclick="switchTab('slow')">
                Slow Moving (<?= count($slowMoving) ?>)
            </button>
        </div>

        <!-- Needs Reordering Tab -->
        <div id="reorder-tab" class="tab-content active">
            <?php if (empty($needingReorder)): ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <p>All products are adequately stocked!</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($needingReorder as $product): ?>
                        <?php
                        $urgency = $product['stock_quantity'] <= 0 ? 'critical' :
                                  ($product['stock_quantity'] <= $product['low_stock_threshold'] / 2 ? 'warning' : '');
                        ?>
                        <div class="product-row <?= $urgency ?>">
                            <div class="product-info">
                                <h4><?= htmlspecialchars($product['name']) ?></h4>
                                <div class="meta">
                                    SKU: <?= htmlspecialchars($product['sku']) ?> |
                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?> |
                                    Vendor: <?= htmlspecialchars($product['vendor_name'] ?? 'N/A') ?>
                                </div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value <?= $urgency ?>">
                                    <?= $product['stock_quantity'] ?>
                                </div>
                                <div class="stock-label">Current Stock</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value">
                                    <?= $product['reorder_point'] ?>
                                </div>
                                <div class="stock-label">Reorder Point</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value" style="color: #8b5cf6;">
                                    <?= $product['suggested_quantity'] ?>
                                </div>
                                <div class="stock-label">Suggested Order</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value" style="font-size: 18px;">
                                    <?= $product['lead_time_days'] ?? 7 ?> days
                                </div>
                                <div class="stock-label">Lead Time</div>
                            </div>
                            <div>
                                <button class="btn-small btn-reorder" onclick="reorderProduct(<?= $product['id'] ?>)">
                                    <i class="bi bi-cart-plus"></i> Order
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Fast Moving Tab -->
        <div id="fast-tab" class="tab-content">
            <?php if (empty($fastMoving)): ?>
                <div class="empty-state">
                    <i class="bi bi-box"></i>
                    <p>No fast-moving inventory in the last 30 days</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($fastMoving as $product): ?>
                        <div class="product-row">
                            <div class="product-info">
                                <h4><?= htmlspecialchars($product['name']) ?></h4>
                                <div class="meta">
                                    SKU: <?= htmlspecialchars($product['sku']) ?> |
                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                </div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value ok">
                                    <?= $product['units_sold'] ?>
                                </div>
                                <div class="stock-label">Units Sold (30d)</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value">
                                    <?= number_format($product['avg_daily_sales'], 1) ?>
                                </div>
                                <div class="stock-label">Avg Daily Sales</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value">
                                    <?= $product['stock_quantity'] ?>
                                </div>
                                <div class="stock-label">Current Stock</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value" style="color: #8b5cf6;">
                                    <?= $product['days_of_stock'] ?>
                                </div>
                                <div class="stock-label">Days of Stock</div>
                            </div>
                            <div>
                                <span class="velocity-badge fast">Fast Moving</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Slow Moving Tab -->
        <div id="slow-tab" class="tab-content">
            <?php if (empty($slowMoving)): ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle"></i>
                    <p>No slow-moving inventory detected</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($slowMoving as $product): ?>
                        <div class="product-row">
                            <div class="product-info">
                                <h4><?= htmlspecialchars($product['name']) ?></h4>
                                <div class="meta">
                                    SKU: <?= htmlspecialchars($product['sku']) ?> |
                                    <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                                </div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value warning">
                                    <?= $product['units_sold'] ?>
                                </div>
                                <div class="stock-label">Units Sold (90d)</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value">
                                    <?= $product['stock_quantity'] ?>
                                </div>
                                <div class="stock-label">Stock on Hand</div>
                            </div>
                            <div class="stock-indicator">
                                <div class="stock-value" style="color: #dc2626;">
                                    $<?= number_format($product['tied_up_capital'], 0) ?>
                                </div>
                                <div class="stock-label">Tied-up Capital</div>
                            </div>
                            <div>
                                <span class="velocity-badge slow">Slow Moving</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 20px; padding: 16px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <strong>Optimization Tip:</strong> Consider running promotions on slow-moving items to free up capital and warehouse space.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    // Deactivate all tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');

    // Activate selected tab
    event.target.classList.add('active');
}

function reorderProduct(productId) {
    window.location.href = `/inventory/advanced/reorder?product_id=${productId}`;
}

function exportInventory() {
    window.location.href = '/inventory/advanced/export';
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
