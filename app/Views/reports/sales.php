<?php
$pageTitle = 'Sales Report';
$activeMenu = 'reports';
$user = currentUser();

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-graph-up"></i> Sales Report</h2>
    <div>
        <?php if (hasPermission('dashboard.export')): ?>
        <a href="/reports/sales/export?period=<?= htmlspecialchars($_GET['period'] ?? 'last_30_days') ?><?= isset($_GET['start_date']) ? '&start_date=' . htmlspecialchars($_GET['start_date']) : '' ?><?= isset($_GET['end_date']) ? '&end_date=' . htmlspecialchars($_GET['end_date']) : '' ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/reports/sales" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Period</label>
                <select name="period" id="periodSelect" class="form-select">
                    <option value="today" <?= ($_GET['period'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="this_week" <?= ($_GET['period'] ?? '') === 'this_week' ? 'selected' : '' ?>>This Week</option>
                    <option value="this_month" <?= ($_GET['period'] ?? '') === 'this_month' ? 'selected' : '' ?>>This Month</option>
                    <option value="last_30_days" <?= ($_GET['period'] ?? 'last_30_days') === 'last_30_days' ? 'selected' : '' ?>>Last 30 Days</option>
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
                <h6 class="text-muted mb-1">Total Revenue</h6>
                <h3><?= formatCurrency($metrics['total_revenue'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">Transactions</h6>
                <h3><?= number_format($metrics['total_transactions'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="text-muted mb-1">Avg Order Value</h6>
                <h3><?= formatCurrency($metrics['avg_order_value'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Tax</h6>
                <h3><?= formatCurrency($metrics['total_tax'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Daily Sales Trend</h5>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="80"></canvas>
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

const salesData = ' . json_encode($dailySales ?? []) . ';

const labels = salesData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
});

const revenueData = salesData.map(d => parseFloat(d.total));
const transactionData = salesData.map(d => parseInt(d.transaction_count));

new Chart(document.getElementById("salesChart"), {
    type: "line",
    data: {
        labels: labels,
        datasets: [{
            label: "Revenue ($)",
            data: revenueData,
            borderColor: "rgb(13, 110, 253)",
            backgroundColor: "rgba(13, 110, 253, 0.1)",
            tension: 0.3,
            fill: true,
            yAxisID: "y"
        }, {
            label: "Transactions",
            data: transactionData,
            borderColor: "rgb(25, 135, 84)",
            backgroundColor: "rgba(25, 135, 84, 0.1)",
            tension: 0.3,
            fill: true,
            yAxisID: "y1"
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: "index",
            intersect: false
        },
        scales: {
            y: {
                type: "linear",
                display: true,
                position: "left",
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return "$" + value.toFixed(2);
                    }
                }
            },
            y1: {
                type: "linear",
                display: true,
                position: "right",
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    }
});
</script>';

require __DIR__ . '/../layouts/app.php';
?>
