<?php
$pageTitle = 'Product Report';
$activeMenu = 'reports';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Product Performance Report</h2>
    <div>
        <?php if (hasPermission('dashboard.export')): ?>
        <a href="/reports/products/export?period=<?= htmlspecialchars($_GET['period'] ?? 'all_time') ?><?= isset($_GET['start_date']) ? '&start_date=' . htmlspecialchars($_GET['start_date']) : '' ?><?= isset($_GET['end_date']) ? '&end_date=' . htmlspecialchars($_GET['end_date']) : '' ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/reports/products" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select name="period" id="periodSelect" class="form-select">
                    <option value="all_time" <?= ($_GET['period'] ?? 'all_time') === 'all_time' ? 'selected' : '' ?>>All Time</option>
                    <option value="today" <?= ($_GET['period'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="this_week" <?= ($_GET['period'] ?? '') === 'this_week' ? 'selected' : '' ?>>This Week</option>
                    <option value="this_month" <?= ($_GET['period'] ?? '') === 'this_month' ? 'selected' : '' ?>>This Month</option>
                    <option value="last_30_days" <?= ($_GET['period'] ?? '') === 'last_30_days' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="custom" <?= ($_GET['period'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                </select>
            </div>
            <div class="col-md-3" id="startDateField" style="display: <?= ($_GET['period'] ?? '') === 'custom' ? 'block' : 'none' ?>;">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-3" id="endDateField" style="display: <?= ($_GET['period'] ?? '') === 'custom' ? 'block' : 'none' ?>;">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Products Sold</h6>
                <h3><?= number_format(array_sum(array_column($bestSellers ?? [], 'units_sold'))) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Revenue</h6>
                <h3><?= formatCurrency(array_sum(array_column($bestSellers ?? [], 'revenue'))) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="text-muted mb-1">Product Categories</h6>
                <h3><?= count($categoryRevenue ?? []) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Best Selling Products</h5>
            </div>
            <div class="card-body">
                <?php if (empty($bestSellers)): ?>
                <p class="text-muted text-center py-4">No sales data available for this period.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bestSellers as $product): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($product['sku']) ?></code></td>
                                <td>
                                    <a href="/products/<?= $product['id'] ?>">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                                <td><span class="badge bg-primary"><?= number_format($product['units_sold']) ?></span></td>
                                <td><?= formatCurrency($product['revenue']) ?></td>
                                <td><?= number_format($product['order_count']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Revenue by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '<script>
const periodSelect = document.getElementById("periodSelect");
const startDateField = document.getElementById("startDateField");
const endDateField = document.getElementById("endDateField");

periodSelect.addEventListener("change", function() {
    if (this.value === "custom") {
        startDateField.style.display = "block";
        endDateField.style.display = "block";
    } else {
        startDateField.style.display = "none";
        endDateField.style.display = "none";
    }
});

const categoryRevenue = ' . json_encode($categoryRevenue ?? []) . ';

const categoryLabels = categoryRevenue.map(c => c.category);
const categoryData = categoryRevenue.map(c => parseFloat(c.revenue));

const backgroundColors = [
    "rgba(13, 110, 253, 0.7)",
    "rgba(25, 135, 84, 0.7)",
    "rgba(255, 193, 7, 0.7)",
    "rgba(220, 53, 69, 0.7)",
    "rgba(13, 202, 240, 0.7)",
    "rgba(108, 117, 125, 0.7)",
    "rgba(111, 66, 193, 0.7)"
];

new Chart(document.getElementById("categoryChart"), {
    type: "pie",
    data: {
        labels: categoryLabels,
        datasets: [{
            data: categoryData,
            backgroundColor: backgroundColors,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom"
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ": $" + context.parsed.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>';

require __DIR__ . '/../layouts/app.php';
?>
