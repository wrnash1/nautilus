<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$user = currentUser();

ob_start();
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <p class="text-muted">Welcome back, <?= htmlspecialchars($user['first_name']) ?>!</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Today's Sales</p>
                        <h3 class="mb-0"><?= formatCurrency($metrics['today_sales'] ?? 0) ?></h3>
                        <a href="/reports/sales?period=today" class="small text-primary">View Details →</a>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Total Customers</p>
                        <h3 class="mb-0"><?= number_format($metrics['total_customers'] ?? 0) ?></h3>
                        <a href="/reports/customers" class="small text-success">View Details →</a>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-people" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Low Stock Items</p>
                        <h3 class="mb-0"><?= number_format($metrics['low_stock_count'] ?? 0) ?></h3>
                        <a href="/reports/low-stock" class="small text-warning">View Details →</a>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 small">Products</p>
                        <h3 class="mb-0"><?= number_format($metrics['total_products'] ?? 0) ?></h3>
                        <a href="/reports/products" class="small text-info">View Details →</a>
                    </div>
                    <div class="text-info">
                        <i class="bi bi-box-seam" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-graph-up"></i> Sales Overview (Last 7 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (hasPermission('pos.create')): ?>
                    <a href="/pos" class="btn btn-primary">
                        <i class="bi bi-cart-plus"></i> New Sale
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('customers.create')): ?>
                    <a href="/customers/create" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Add Customer
                    </a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('products.create')): ?>
                    <a href="/products/create" class="btn btn-info">
                        <i class="bi bi-box-seam"></i> Add Product
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recent_transactions)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Recent Transactions
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $t): ?>
                            <tr>
                                <td>#<?= $t['id'] ?></td>
                                <td><?= date('M d, Y g:i A', strtotime($t['created_at'])) ?></td>
                                <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                                <td><?= formatCurrency($t['total']) ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();

$additionalJs = '<script>
const ctx = document.getElementById("salesChart");
const salesData = ' . json_encode($sales_chart_data ?? []) . ';

const labels = salesData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
});

const data = salesData.map(d => parseFloat(d.total));

new Chart(ctx, {
    type: "line",
    data: {
        labels: labels,
        datasets: [{
            label: "Sales ($)",
            data: data,
            borderColor: "rgb(13, 110, 253)",
            backgroundColor: "rgba(13, 110, 253, 0.1)",
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return "$" + value.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>';

require __DIR__ . '/../layouts/app.php';
?>
