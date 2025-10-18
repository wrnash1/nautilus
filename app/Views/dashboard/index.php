<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$user = currentUser();

ob_start();
?>

<!-- Dashboard Header -->
<div class="dashboard-header fade-in-up">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1><i class="bi bi-water"></i> Nautilus Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($user['first_name']) ?>! Here's what's happening with your scuba diving business today.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button onclick="refreshDashboard()" id="refreshDashboard" class="btn btn-light">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics Row 1 -->
<div class="row g-4 mb-4">
    <!-- Today's Sales -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card card-sales fade-in-up">
            <div class="metric-icon icon-sales">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="metric-label">Today's Sales</div>
            <div class="metric-value" data-count="<?= $metrics['today_sales'] ?>" data-currency="true">
                $0.00
            </div>
            <?php if ($metrics['sales_trend'] != 0): ?>
            <div class="metric-change <?= $metrics['sales_trend'] > 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-arrow-<?= $metrics['sales_trend'] > 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($metrics['sales_trend'], 1)) ?>% vs last month
            </div>
            <?php endif; ?>
            <a href="/reports/sales?period=today" class="metric-link">
                View Details <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card card-customers fade-in-up">
            <div class="metric-icon icon-customers">
                <i class="bi bi-people"></i>
            </div>
            <div class="metric-label">Total Customers</div>
            <div class="metric-value" data-count="<?= $metrics['total_customers'] ?>">
                0
            </div>
            <?php if ($metrics['customer_trend'] != 0): ?>
            <div class="metric-change <?= $metrics['customer_trend'] > 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-arrow-<?= $metrics['customer_trend'] > 0 ? 'up' : 'down' ?>"></i>
                <?= abs(number_format($metrics['customer_trend'], 1)) ?>% this month
            </div>
            <?php endif; ?>
            <a href="/customers" class="metric-link">
                View All <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Rentals -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card card-rentals fade-in-up">
            <div class="metric-icon icon-rentals">
                <i class="bi bi-gear"></i>
            </div>
            <div class="metric-label">Active Rentals</div>
            <div class="metric-value" data-count="<?= $metrics['active_rentals'] ?>">
                0
            </div>
            <a href="/rentals/reservations?status=active" class="metric-link">
                View Rentals <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Upcoming Courses -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card card-courses fade-in-up">
            <div class="metric-icon icon-courses">
                <i class="bi bi-book"></i>
            </div>
            <div class="metric-label">Upcoming Courses</div>
            <div class="metric-value" data-count="<?= $metrics['upcoming_courses'] ?>">
                0
            </div>
            <a href="/courses/schedules?status=scheduled" class="metric-link">
                View Schedules <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Key Metrics Row 2 -->
<div class="row g-4 mb-4">
    <!-- Upcoming Trips -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card fade-in-up">
            <div class="metric-icon" style="background: linear-gradient(135deg, rgba(5, 191, 219, 0.1) 0%, rgba(8, 131, 149, 0.2) 100%); color: #05bfdb;">
                <i class="bi bi-airplane"></i>
            </div>
            <div class="metric-label">Upcoming Trips</div>
            <div class="metric-value" data-count="<?= $metrics['upcoming_trips'] ?>">
                0
            </div>
            <a href="/trips/schedules" class="metric-link">
                View Trips <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Equipment Maintenance -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card fade-in-up">
            <div class="metric-icon" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 152, 0, 0.2) 100%); color: #ffc107;">
                <i class="bi bi-tools"></i>
            </div>
            <div class="metric-label">Needs Maintenance</div>
            <div class="metric-value" data-count="<?= $metrics['equipment_maintenance'] ?>">
                0
            </div>
            <?php if ($metrics['equipment_maintenance'] > 0): ?>
            <div class="metric-change negative">
                <i class="bi bi-exclamation-triangle"></i>
                Requires attention
            </div>
            <?php endif; ?>
            <a href="/rentals/equipment?status=maintenance" class="metric-link">
                View Equipment <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Pending Certifications -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card fade-in-up">
            <div class="metric-icon" style="background: linear-gradient(135deg, rgba(139, 69, 19, 0.1) 0%, rgba(101, 67, 33, 0.2) 100%); color: #8b4513;">
                <i class="bi bi-award"></i>
            </div>
            <div class="metric-label">Pending Certs</div>
            <div class="metric-value" data-count="<?= $metrics['pending_certifications'] ?>">
                0
            </div>
            <?php if ($metrics['pending_certifications'] > 0): ?>
            <div class="metric-change negative">
                <i class="bi bi-exclamation-circle"></i>
                Action needed
            </div>
            <?php endif; ?>
            <a href="/courses/enrollments?status=completed" class="metric-link">
                Issue Certs <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Today's Air Fills -->
    <div class="col-md-4 col-lg-3">
        <div class="metric-card fade-in-up">
            <div class="metric-icon" style="background: linear-gradient(135deg, rgba(0, 217, 255, 0.1) 0%, rgba(0, 150, 255, 0.2) 100%); color: #00d9ff;">
                <i class="bi bi-wind"></i>
            </div>
            <div class="metric-label">Air Fills Today</div>
            <div class="metric-value" data-count="<?= $metrics['today_air_fills'] ?>">
                0
            </div>
            <a href="/air-fills" class="metric-link">
                View Fills <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Sales Overview Chart -->
    <div class="col-lg-8">
        <div class="chart-card fade-in-up">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-graph-up"></i> Sales Overview (Last 7 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="col-lg-4">
        <div class="chart-card fade-in-up">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-pie-chart"></i> Revenue Breakdown
                </h5>
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
        <div class="upcoming-card fade-in-up">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-event"></i> Upcoming Events
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-item event-<?= $event['type'] ?>">
                        <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                        <div class="event-meta">
                            <span><i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($event['date'])) ?></span>
                            <span><i class="bi bi-info-circle"></i> <?= htmlspecialchars($event['meta']) ?></span>
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
        <div class="quick-actions-card fade-in-up mb-4">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-lightning-fill"></i> Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (hasPermission('pos.create')): ?>
                    <a href="/pos" class="quick-action-btn btn btn-primary">
                        <i class="bi bi-cart-plus"></i> New Sale
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('rentals.create')): ?>
                    <a href="/rentals/reservations/create" class="quick-action-btn btn btn-success">
                        <i class="bi bi-gear"></i> New Rental
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('customers.create')): ?>
                    <a href="/customers/create" class="quick-action-btn btn btn-info">
                        <i class="bi bi-person-plus"></i> Add Customer
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
        <div class="alert-card fade-in-up">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bell"></i> Alerts
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($alerts as $alert): ?>
                <div class="alert-item alert-<?= $alert['type'] ?>">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;"><?= htmlspecialchars($alert['title']) ?></div>
                    <div style="font-size: 0.875rem; color: #4a5568;">
                        <?= htmlspecialchars($alert['message']) ?>
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
        <div class="chart-card fade-in-up">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-gear-wide-connected"></i> Equipment Status
                </h5>
            </div>
            <div class="card-body">
                <canvas id="equipmentChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="chart-card fade-in-up">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-star-fill"></i> Top Selling Products (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($top_products)): ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
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
                                    <span class="badge bg-primary"><?= number_format($product['total_sold']) ?></span>
                                </td>
                                <td class="text-end text-success">
                                    <strong><?= formatCurrency($product['revenue']) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted mb-0">No sales data available for the last 30 days.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<?php if (!empty($recent_transactions)): ?>
<div class="row">
    <div class="col-12">
        <div class="chart-card fade-in-up">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="bi bi-clock-history"></i> Recent Transactions
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
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
                                    <span class="status-badge status-<?= strtolower($t['status']) ?>">
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
$additionalCss = '<link rel="stylesheet" href="/assets/css/dashboard.css">';

// Additional JavaScript
$additionalJs = '<script src="/assets/js/dashboard.js"></script>
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

require __DIR__ . '/../layouts/app.php';
?>
