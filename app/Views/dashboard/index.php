<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$user = currentUser();

ob_start();
?>

<!-- Dashboard Header -->
<div class="dashboard-header slide-up">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="bi bi-water"></i> Nautilus Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($user['first_name']) ?>! Here's what's happening with your scuba diving business today.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button onclick="refreshDashboard()" id="refreshDashboard" class="btn btn-light text-primary fw-bold shadow-sm">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics Row 1 -->
<div class="row g-4 mb-4">
    <!-- Today's Sales -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card card-sales slide-up">
            <div class="metric-icon icon-sales">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['today_sales'] ?>" data-currency="true">
                $0.00
            </div>
            <div class="metric-label">Today's Sales</div>
            <?php if ($metrics['sales_trend'] != 0): ?>
            <div class="metric-change <?= $metrics['sales_trend'] > 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-arrow-<?= $metrics['sales_trend'] > 0 ? 'up' : 'down' ?>"></i>
                <span><?= abs(number_format($metrics['sales_trend'], 1)) ?>% vs last month</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card card-customers slide-up" style="animation-delay: 0.05s;">
            <div class="metric-icon icon-customers">
                <i class="bi bi-people"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['total_customers'] ?>">
                0
            </div>
            <div class="metric-label">Total Customers</div>
            <?php if ($metrics['customer_trend'] != 0): ?>
            <div class="metric-change <?= $metrics['customer_trend'] > 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-arrow-<?= $metrics['customer_trend'] > 0 ? 'up' : 'down' ?>"></i>
                <span><?= abs(number_format($metrics['customer_trend'], 1)) ?>% this month</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active Rentals -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card card-rentals slide-up" style="animation-delay: 0.1s;">
            <div class="metric-icon icon-rentals">
                <i class="bi bi-gear"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['active_rentals'] ?>">
                0
            </div>
            <div class="metric-label">Active Rentals</div>
            <a href="/store/rentals" class="metric-link">
                Manage Rentals <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Upcoming Courses -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card card-courses slide-up" style="animation-delay: 0.15s;">
            <div class="metric-icon icon-courses">
                <i class="bi bi-book"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['upcoming_courses'] ?>">
                0
            </div>
            <div class="metric-label">Upcoming Courses</div>
            <a href="/store/courses/schedules" class="metric-link">
                View Schedule <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Key Metrics Row 2 -->
<div class="row g-4 mb-4">
    <!-- Upcoming Trips -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card slide-up" style="animation-delay: 0.2s;">
            <div class="metric-icon" style="background: rgba(8, 145, 178, 0.1); color: #0891b2;">
                <i class="bi bi-airplane"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['upcoming_trips'] ?>">
                0
            </div>
            <div class="metric-label">Upcoming Trips</div>
        </div>
    </div>

    <!-- Equipment Maintenance -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card slide-up" style="animation-delay: 0.25s;">
            <div class="metric-icon" style="background: rgba(217, 119, 6, 0.1); color: #d97706;">
                <i class="bi bi-tools"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['equipment_maintenance'] ?>">
                0
            </div>
            <div class="metric-label">Needs Maintenance</div>
            <?php if ($metrics['equipment_maintenance'] > 0): ?>
            <div class="metric-change negative">
                <i class="bi bi-exclamation-triangle"></i>
                <span>Requires attention</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pending Certifications -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card slide-up" style="animation-delay: 0.3s;">
            <div class="metric-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
                <i class="bi bi-award"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['pending_certifications'] ?>">
                0
            </div>
            <div class="metric-label">Pending Certs</div>
            <?php if ($metrics['pending_certifications'] > 0): ?>
            <div class="metric-change negative">
                <i class="bi bi-exclamation-circle"></i>
                <span>Action needed</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Today's Air Fills -->
    <div class="col-md-6 col-lg-3">
        <div class="metric-card slide-up" style="animation-delay: 0.35s;">
            <div class="metric-icon" style="background: rgba(2, 132, 199, 0.1); color: #0284c7;">
                <i class="bi bi-wind"></i>
            </div>
            <div class="metric-value" data-count="<?= $metrics['today_air_fills'] ?>">
                0
            </div>
            <div class="metric-label">Air Fills Today</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Sales Overview Chart -->
    <div class="col-lg-8">
        <div class="chart-card slide-up" style="animation-delay: 0.4s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-graph-up text-primary"></i> Sales Overview (Last 7 Days)
                </h2>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="col-lg-4">
        <div class="chart-card slide-up" style="animation-delay: 0.45s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-pie-chart text-primary"></i> Revenue Breakdown
                </h2>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row g-4 mb-4">
    <!-- Upcoming Events -->
    <div class="col-lg-8">
        <div class="upcoming-card slide-up" style="animation-delay: 0.5s;">
            <div class="card-header bg-white border-bottom">
                <h2 class="card-title">
                    <i class="bi bi-calendar-event text-primary"></i> Upcoming Events
                </h2>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-item event-<?= $event['type'] ?>">
                        <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                        <div class="event-meta">
                            <span><i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($event['date'])) ?></span>
                            <span><i class="bi bi-info-circle"></i> <?= htmlspecialchars($event['meta']) ?></span>
                            <?php if (!empty($event['link'])): ?>
                            <a href="<?= $event['link'] ?>" class="text-decoration-none ms-auto text-primary">View</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">No upcoming events scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Alerts -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="quick-actions-card slide-up mb-4" style="animation-delay: 0.55s;">
            <div class="card-header">
                <h2 class="card-title text-white">
                    <i class="bi bi-lightning-fill"></i> Quick Actions
                </h2>
            </div>
            <div class="card-body p-0">
                <div class="d-flex flex-column">
                    <?php if (hasPermission('pos.create')): ?>
                    <a href="/store/pos" class="quick-action-btn text-dark hover-bg-light border-bottom">
                        <i class="bi bi-cart-plus text-primary"></i> New Sale
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('rentals.create')): ?>
                    <a href="/store/rentals/reservations/create" class="quick-action-btn text-dark hover-bg-light border-bottom">
                        <i class="bi bi-gear text-warning"></i> New Rental
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('customers.create')): ?>
                    <a href="/store/customers/create" class="quick-action-btn text-dark hover-bg-light">
                        <i class="bi bi-person-plus text-success"></i> Add Customer
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
        <div class="alert-card slide-up" style="animation-delay: 0.6s;">
            <div class="card-header bg-white border-bottom">
                <h2 class="card-title">
                    <i class="bi bi-bell text-danger"></i> Alerts
                </h2>
            </div>
            <div class="card-body">
                <?php foreach ($alerts as $alert): ?>
                <div class="alert-item alert-<?= $alert['type'] ?>">
                    <div class="d-flex align-items-start gap-2">
                        <div class="mt-1">
                            <i class="bi bi-<?= $alert['type'] === 'warning' ? 'exclamation-triangle' : ($alert['type'] === 'danger' || $alert['type'] === 'error' ? 'x-circle' : 'info-circle') ?>-fill text-<?= $alert['type'] === 'error' ? 'danger' : $alert['type'] ?>"></i>
                        </div>
                        <div>
                            <strong><?= htmlspecialchars($alert['title']) ?></strong>
                            <p class="mb-0 small text-muted">
                                <?= htmlspecialchars($alert['message']) ?>
                            </p>
                            <?php if (!empty($alert['link'])): ?>
                            <a href="<?= $alert['link'] ?>" class="small text-decoration-none mt-1 d-inline-block">Resolve</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Equipment Status & Top Products Row -->
<div class="row g-4 mb-4">
    <!-- Equipment Status -->
    <div class="col-lg-6">
        <div class="chart-card slide-up" style="animation-delay: 0.65s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-gear-wide-connected text-primary"></i> Equipment Status
                </h2>
            </div>
            <div class="card-body">
                <canvas id="equipmentChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="chart-card slide-up" style="animation-delay: 0.7s;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="bi bi-star-fill text-warning"></i> Top Selling Products (Last 30 Days)
                </h2>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($top_products)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 modern-table">
                        <thead class="bg-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Sold</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($product['sku']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill"><?= number_format($product['total_sold']) ?></span>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    <?= formatCurrency($product['revenue']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted p-3 mb-0">No sales data available for the last 30 days.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<?php if (!empty($recent_transactions)): ?>
<div class="row">
    <div class="col-12">
        <div class="modern-card slide-up" style="animation-delay: 0.75s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-clock-history text-primary"></i> Recent Transactions
                </h2>
            </div>
            <div class="modern-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover modern-table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $t): ?>
                            <tr>
                                <td><strong>#<?= $t['id'] ?></strong></td>
                                <td><?= date('M d, Y g:i A', strtotime($t['created_at'])) ?></td>
                                <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                                <td><strong class="text-success"><?= formatCurrency($t['total']) ?></strong></td>
                                <td>
                                    <span class="badge rounded-pill bg-<?= strtolower($t['status']) === 'completed' ? 'success' : (strtolower($t['status']) === 'pending' ? 'warning' : 'secondary') ?>">
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

// Additional CSS
$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">
<link rel="stylesheet" href="/assets/css/dashboard.css">';

// Additional JavaScript
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>
<script src="/assets/js/dashboard.js"></script>
<script>
// Sales Chart Data
const salesData = ' . json_encode($sales_chart_data ?? []) . ';
createSalesChart("salesChart", salesData);

// Revenue Breakdown Chart
const revenueData = ' . json_encode($revenue_breakdown ?? ['labels' => [], 'values' => []]) . ';
if (revenueData.values.some(v => v > 0)) {
    createRevenueBreakdownChart("revenueChart", revenueData);
} else {
    document.getElementById("revenueChart").parentElement.innerHTML = "<p class=\"text-muted text-center\">No revenue data available</p>";
}

// Equipment Status Chart
const equipmentData = ' . json_encode($equipment_status ?? ['labels' => [], 'values' => []]) . ';
if (equipmentData.values.some(v => v > 0)) {
    createEquipmentStatusChart("equipmentChart", equipmentData);
} else {
    document.getElementById("equipmentChart").parentElement.innerHTML = "<p class=\"text-muted text-center\">No equipment data available</p>";
}
</script>';

require __DIR__ . '/../layouts/admin.php';
?>

