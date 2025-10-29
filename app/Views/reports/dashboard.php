<?php
$pageTitle = 'Reports Dashboard';
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '/store/dashboard'],
    ['title' => 'Reports', 'url' => null]
];

ob_start();
?>

<div class="reports-dashboard">
    <div class="page-header">
        <div class="header-content">
            <div>
                <h1>Reports & Analytics</h1>
                <p class="subtitle">Comprehensive business insights and metrics</p>
            </div>
            <div class="header-actions">
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print
                </button>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <div class="dropdown-menu">
                        <a href="/store/reports/export?type=overview&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">Overview Report</a>
                        <a href="/store/reports/export?type=sales&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">Sales Report</a>
                        <a href="/store/reports/export?type=customers&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">Customers Report</a>
                        <a href="/store/reports/export?type=products&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>">Products Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="date-filter">
        <form method="GET" action="/store/reports/dashboard">
            <div class="filter-group">
                <label>Start Date:</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
            </div>
            <div class="filter-group">
                <label>End Date:</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Apply
            </button>
            <div class="quick-dates">
                <a href="?start_date=<?= date('Y-m-d') ?>&end_date=<?= date('Y-m-d') ?>">Today</a>
                <a href="?start_date=<?= date('Y-m-d', strtotime('-7 days')) ?>&end_date=<?= date('Y-m-d') ?>">Last 7 Days</a>
                <a href="?start_date=<?= date('Y-m-01') ?>&end_date=<?= date('Y-m-t') ?>">This Month</a>
                <a href="?start_date=<?= date('Y-m-d', strtotime('-30 days')) ?>&end_date=<?= date('Y-m-d') ?>">Last 30 Days</a>
            </div>
        </form>
    </div>

    <div class="report-period">
        <strong>Report Period:</strong> <?= date('M j, Y', strtotime($startDate)) ?> - <?= date('M j, Y', strtotime($endDate)) ?>
    </div>

    <!-- Revenue Overview -->
    <section class="report-section">
        <h2><i class="fas fa-dollar-sign"></i> Revenue Overview</h2>
        <div class="metrics-grid">
            <div class="metric-card primary">
                <div class="metric-label">Total Revenue</div>
                <div class="metric-value">$<?= number_format($metrics['revenue']['total'], 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Retail Sales</div>
                <div class="metric-value">$<?= number_format($metrics['revenue']['retail'], 2) ?></div>
                <div class="metric-percentage">
                    <?= $metrics['revenue']['total'] > 0 ? round(($metrics['revenue']['retail'] / $metrics['revenue']['total']) * 100, 1) : 0 ?>%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Equipment Rentals</div>
                <div class="metric-value">$<?= number_format($metrics['revenue']['rentals'], 2) ?></div>
                <div class="metric-percentage">
                    <?= $metrics['revenue']['total'] > 0 ? round(($metrics['revenue']['rentals'] / $metrics['revenue']['total']) * 100, 1) : 0 ?>%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Training Courses</div>
                <div class="metric-value">$<?= number_format($metrics['revenue']['courses'], 2) ?></div>
                <div class="metric-percentage">
                    <?= $metrics['revenue']['total'] > 0 ? round(($metrics['revenue']['courses'] / $metrics['revenue']['total']) * 100, 1) : 0 ?>%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Dive Trips</div>
                <div class="metric-value">$<?= number_format($metrics['revenue']['trips'], 2) ?></div>
                <div class="metric-percentage">
                    <?= $metrics['revenue']['total'] > 0 ? round(($metrics['revenue']['trips'] / $metrics['revenue']['total']) * 100, 1) : 0 ?>%
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Air Fills</div>
                <div class="metric-value">$<?= number_format($metrics['revenue']['air_fills'], 2) ?></div>
                <div class="metric-percentage">
                    <?= $metrics['revenue']['total'] > 0 ? round(($metrics['revenue']['air_fills'] / $metrics['revenue']['total']) * 100, 1) : 0 ?>%
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </section>

    <!-- Sales Metrics -->
    <section class="report-section">
        <h2><i class="fas fa-shopping-cart"></i> Sales Performance</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Total Transactions</div>
                <div class="metric-value"><?= number_format($metrics['sales']['transaction_count']) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Average Sale</div>
                <div class="metric-value">$<?= number_format($metrics['sales']['average_sale'], 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Highest Sale</div>
                <div class="metric-value">$<?= number_format($metrics['sales']['highest_sale'], 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Items Sold</div>
                <div class="metric-value"><?= number_format($metrics['sales']['items_sold']) ?></div>
            </div>
        </div>

        <!-- Sales by Category -->
        <?php if (!empty($charts['sales_by_category'])): ?>
        <div class="chart-container">
            <h3>Sales by Category</h3>
            <canvas id="categoryChart"></canvas>
        </div>
        <?php endif; ?>
    </section>

    <!-- Customer Metrics -->
    <section class="report-section">
        <h2><i class="fas fa-users"></i> Customer Analytics</h2>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">New Customers</div>
                <div class="metric-value"><?= number_format($metrics['customers']['new_customers']) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Active Customers</div>
                <div class="metric-value"><?= number_format($metrics['customers']['active_customers']) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Customers</div>
                <div class="metric-value"><?= number_format($metrics['customers']['total_customers']) ?></div>
            </div>
        </div>

        <!-- Top Customers -->
        <?php if (!empty($metrics['customers']['top_customers'])): ?>
        <div class="table-container">
            <h3>Top Customers by Lifetime Value</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Customer</th>
                        <th>Lifetime Value</th>
                        <th>Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($metrics['customers']['top_customers'] as $customer): ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($customer['name']) ?></td>
                        <td>$<?= number_format($customer['lifetime_value'], 2) ?></td>
                        <td><?= number_format($customer['transaction_count']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>

    <!-- Top Products -->
    <?php if (!empty($charts['top_products'])): ?>
    <section class="report-section">
        <h2><i class="fas fa-star"></i> Top Selling Products</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Units Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach ($charts['top_products'] as $product): ?>
                    <tr>
                        <td><?= $rank++ ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['sku']) ?></td>
                        <td><?= number_format($product['units_sold']) ?></td>
                        <td>$<?= number_format($product['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

    <!-- Inventory, Rentals, Courses, Trips in Grid -->
    <div class="secondary-metrics">
        <section class="report-section-small">
            <h3><i class="fas fa-boxes"></i> Inventory</h3>
            <div class="metric-list">
                <div class="metric-item">
                    <span>Total Products</span>
                    <strong><?= number_format($metrics['inventory']['total_products']) ?></strong>
                </div>
                <div class="metric-item alert">
                    <span>Low Stock</span>
                    <strong><?= number_format($metrics['inventory']['low_stock_count']) ?></strong>
                </div>
                <div class="metric-item danger">
                    <span>Out of Stock</span>
                    <strong><?= number_format($metrics['inventory']['out_of_stock_count']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Inventory Value</span>
                    <strong>$<?= number_format($metrics['inventory']['inventory_value'], 2) ?></strong>
                </div>
            </div>
        </section>

        <section class="report-section-small">
            <h3><i class="fas fa-life-ring"></i> Rentals</h3>
            <div class="metric-list">
                <div class="metric-item">
                    <span>Total Rentals</span>
                    <strong><?= number_format($metrics['rentals']['total_rentals']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Active</span>
                    <strong><?= number_format($metrics['rentals']['active_rentals']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Completed</span>
                    <strong><?= number_format($metrics['rentals']['completed_rentals']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Equipment Available</span>
                    <strong><?= number_format($metrics['rentals']['available_equipment']) ?> / <?= number_format($metrics['rentals']['total_equipment']) ?></strong>
                </div>
            </div>
        </section>

        <section class="report-section-small">
            <h3><i class="fas fa-graduation-cap"></i> Courses</h3>
            <div class="metric-list">
                <div class="metric-item">
                    <span>Total Enrollments</span>
                    <strong><?= number_format($metrics['courses']['total_enrollments']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Completed</span>
                    <strong><?= number_format($metrics['courses']['completed']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>In Progress</span>
                    <strong><?= number_format($metrics['courses']['in_progress']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Upcoming</span>
                    <strong><?= number_format($metrics['courses']['upcoming_courses']) ?></strong>
                </div>
            </div>
        </section>

        <section class="report-section-small">
            <h3><i class="fas fa-ship"></i> Dive Trips</h3>
            <div class="metric-list">
                <div class="metric-item">
                    <span>Total Bookings</span>
                    <strong><?= number_format($metrics['trips']['total_bookings']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Confirmed</span>
                    <strong><?= number_format($metrics['trips']['confirmed']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Completed</span>
                    <strong><?= number_format($metrics['trips']['completed']) ?></strong>
                </div>
                <div class="metric-item">
                    <span>Upcoming Trips</span>
                    <strong><?= number_format($metrics['trips']['upcoming_trips']) ?></strong>
                </div>
            </div>
        </section>
    </div>
</div>

<style>
.reports-dashboard {
    padding: 20px;
}

.page-header {
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.header-content h1 {
    margin: 0;
    font-size: 28px;
}

.subtitle {
    margin: 5px 0 0 0;
    color: #666;
    font-size: 14px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.date-filter {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.date-filter form {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #666;
}

.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.quick-dates {
    display: flex;
    gap: 10px;
    align-items: center;
}

.quick-dates a {
    color: #007bff;
    text-decoration: none;
    font-size: 13px;
}

.quick-dates a:hover {
    text-decoration: underline;
}

.report-period {
    margin-bottom: 20px;
    color: #666;
    font-size: 14px;
}

.report-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.report-section h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.metric-card {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.metric-card.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.metric-label {
    font-size: 13px;
    margin-bottom: 10px;
    opacity: 0.8;
}

.metric-value {
    font-size: 28px;
    font-weight: bold;
}

.metric-percentage {
    font-size: 14px;
    margin-top: 5px;
    opacity: 0.7;
}

.chart-container {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.chart-container h3 {
    margin: 0 0 20px 0;
    font-size: 16px;
}

.secondary-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.report-section-small {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.report-section-small h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.metric-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.metric-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.metric-item.alert {
    background: #fff3cd;
}

.metric-item.danger {
    background: #f8d7da;
}

.table-container {
    margin-top: 20px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #dee2e6;
}

@media print {
    .header-actions, .date-filter, .btn {
        display: none !important;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Trend Chart
<?php if (!empty($charts['revenue_trend'])): ?>
const revenueData = <?= json_encode($charts['revenue_trend']) ?>;
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: revenueData.map(d => d.date),
        datasets: [{
            label: 'Daily Revenue',
            data: revenueData.map(d => d.revenue),
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: (context) => '$' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2})
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value) => '$' + value.toLocaleString()
                }
            }
        }
    }
});
<?php endif; ?>

// Category Sales Chart
<?php if (!empty($charts['sales_by_category'])): ?>
const categoryData = <?= json_encode($charts['sales_by_category']) ?>;
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: categoryData.map(d => d.category),
        datasets: [{
            data: categoryData.map(d => d.total),
            backgroundColor: [
                '#667eea', '#764ba2', '#f093fb', '#4facfe',
                '#43e97b', '#fa709a', '#fee140', '#30cfd0'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: (context) => context.label + ': $' + context.parsed.toLocaleString('en-US', {minimumFractionDigits: 2})
                }
            }
        }
    }
});
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
