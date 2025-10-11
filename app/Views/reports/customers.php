<?php
$pageTitle = 'Customer Report';
$activeMenu = 'reports';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Customer Report</h2>
    <div>
        <?php if (hasPermission('dashboard.export')): ?>
        <a href="/reports/customers/export?period=<?= htmlspecialchars($_GET['period'] ?? 'all_time') ?><?= isset($_GET['start_date']) ? '&start_date=' . htmlspecialchars($_GET['start_date']) : '' ?><?= isset($_GET['end_date']) ? '&end_date=' . htmlspecialchars($_GET['end_date']) : '' ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/reports/customers" class="row g-3">
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
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Customers</h6>
                <h3><?= number_format($metrics['total_customers'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">B2C Customers</h6>
                <h3><?= number_format($metrics['b2c_count'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="text-muted mb-1">B2B Customers</h6>
                <h3><?= number_format($metrics['b2b_count'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <h6 class="text-muted mb-1">Avg Customer Value</h6>
                <h3><?= formatCurrency($metrics['avg_customer_value'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Top Customers by Revenue</h5>
            </div>
            <div class="card-body">
                <?php if (empty($topCustomers)): ?>
                <p class="text-muted text-center py-4">No customer data available for this period.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Total Spent</th>
                                <th>Orders</th>
                                <th>Avg Order</th>
                                <th>Last Purchase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topCustomers as $customer): ?>
                            <tr>
                                <td>
                                    <a href="/customers/<?= $customer['id'] ?>">
                                        <?php if ($customer['customer_type'] === 'B2B'): ?>
                                            <?= htmlspecialchars($customer['company_name']) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $customer['customer_type'] === 'B2B' ? 'primary' : 'secondary' ?>">
                                        <?= htmlspecialchars($customer['customer_type']) ?>
                                    </span>
                                </td>
                                <td><?= formatCurrency($customer['total_spent']) ?></td>
                                <td><?= number_format($customer['transaction_count']) ?></td>
                                <td><?= formatCurrency($customer['avg_order_value']) ?></td>
                                <td><?= date('M d, Y', strtotime($customer['last_purchase_date'])) ?></td>
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
                <h5 class="mb-0">Top 10 Customers</h5>
            </div>
            <div class="card-body">
                <canvas id="customersChart" height="300"></canvas>
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

const topCustomers = ' . json_encode(array_slice($topCustomers ?? [], 0, 10)) . ';

const customerLabels = topCustomers.map(c => {
    return c.customer_type === "B2B" ? c.company_name : c.first_name + " " + c.last_name;
});

const customerData = topCustomers.map(c => parseFloat(c.total_spent));

new Chart(document.getElementById("customersChart"), {
    type: "bar",
    data: {
        labels: customerLabels,
        datasets: [{
            label: "Total Spent ($)",
            data: customerData,
            backgroundColor: "rgba(13, 110, 253, 0.7)",
            borderColor: "rgb(13, 110, 253)",
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
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
