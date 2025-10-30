<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$user = currentUser();

ob_start();
?>

<!-- Dashboard Header -->
<div class="hero-section slide-up" style="background: linear-gradient(135deg, var(--primary-500), var(--primary-700)); color: white; padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 2rem; position: relative; overflow: hidden;">
    <div style="content: ''; position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0;"><i class="bi bi-water"></i> Nautilus Dashboard</h1>
                <p style="font-size: 1rem; opacity: 0.9; margin: 0;">Welcome back, <?= htmlspecialchars($user['first_name']) ?>! Here's what's happening with your scuba diving business today.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button onclick="refreshDashboard()" id="refreshDashboard" class="btn-modern btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Row 1 -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Today's Sales -->
    <div class="stat-card-modern slide-up">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--primary-400), var(--primary-600));">
            <i class="bi bi-currency-dollar"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['today_sales'] ?>" data-currency="true">
            $0.00
        </div>
        <div class="stat-label-modern">Today's Sales</div>
        <?php if ($metrics['sales_trend'] != 0): ?>
        <div class="stat-change-modern <?= $metrics['sales_trend'] > 0 ? 'stat-change-positive' : 'stat-change-negative' ?>">
            <i class="bi bi-arrow-<?= $metrics['sales_trend'] > 0 ? 'up' : 'down' ?>"></i>
            <span><?= abs(number_format($metrics['sales_trend'], 1)) ?>% vs last month</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Total Customers -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.05s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--success), #059669);">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['total_customers'] ?>">
            0
        </div>
        <div class="stat-label-modern">Total Customers</div>
        <?php if ($metrics['customer_trend'] != 0): ?>
        <div class="stat-change-modern <?= $metrics['customer_trend'] > 0 ? 'stat-change-positive' : 'stat-change-negative' ?>">
            <i class="bi bi-arrow-<?= $metrics['customer_trend'] > 0 ? 'up' : 'down' ?>"></i>
            <span><?= abs(number_format($metrics['customer_trend'], 1)) ?>% this month</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Active Rentals -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.1s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--warning), #d97706);">
            <i class="bi bi-gear"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['active_rentals'] ?>">
            0
        </div>
        <div class="stat-label-modern">Active Rentals</div>
    </div>

    <!-- Upcoming Courses -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.15s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <i class="bi bi-book"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['upcoming_courses'] ?>">
            0
        </div>
        <div class="stat-label-modern">Upcoming Courses</div>
    </div>
</div>

<!-- Key Metrics Row 2 -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Upcoming Trips -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.2s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
            <i class="bi bi-airplane"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['upcoming_trips'] ?>">
            0
        </div>
        <div class="stat-label-modern">Upcoming Trips</div>
    </div>

    <!-- Equipment Maintenance -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.25s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--warning), #d97706);">
            <i class="bi bi-tools"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['equipment_maintenance'] ?>">
            0
        </div>
        <div class="stat-label-modern">Needs Maintenance</div>
        <?php if ($metrics['equipment_maintenance'] > 0): ?>
        <div class="stat-change-modern stat-change-negative">
            <i class="bi bi-exclamation-triangle"></i>
            <span>Requires attention</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pending Certifications -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.3s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <i class="bi bi-award"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['pending_certifications'] ?>">
            0
        </div>
        <div class="stat-label-modern">Pending Certs</div>
        <?php if ($metrics['pending_certifications'] > 0): ?>
        <div class="stat-change-modern stat-change-negative">
            <i class="bi bi-exclamation-circle"></i>
            <span>Action needed</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Today's Air Fills -->
    <div class="stat-card-modern slide-up" style="animation-delay: 0.35s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #06b6d4, #0284c7);">
            <i class="bi bi-wind"></i>
        </div>
        <div class="stat-value-modern" data-count="<?= $metrics['today_air_fills'] ?>">
            0
        </div>
        <div class="stat-label-modern">Air Fills Today</div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Sales Overview Chart -->
    <div class="col-lg-8">
        <div class="modern-card slide-up" style="animation-delay: 0.4s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-graph-up"></i> Sales Overview (Last 7 Days)
                </h2>
            </div>
            <div class="modern-card-body">
                <canvas id="salesChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue Breakdown -->
    <div class="col-lg-4">
        <div class="modern-card slide-up" style="animation-delay: 0.45s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-pie-chart"></i> Revenue Breakdown
                </h2>
            </div>
            <div class="modern-card-body">
                <canvas id="revenueChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row g-4 mb-4">
    <!-- Upcoming Events -->
    <div class="col-lg-8">
        <div class="modern-card slide-up" style="animation-delay: 0.5s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-calendar-event"></i> Upcoming Events
                </h2>
            </div>
            <div class="modern-card-body">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); transition: background var(--transition-base);">
                        <div style="font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);"><?= htmlspecialchars($event['title']) ?></div>
                        <div style="display: flex; gap: 1rem; font-size: 0.875rem; color: var(--text-secondary);">
                            <span><i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($event['date'])) ?></span>
                            <span><i class="bi bi-info-circle"></i> <?= htmlspecialchars($event['meta']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-tertiary); margin: 0;">No upcoming events scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Alerts -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="modern-card slide-up mb-4" style="animation-delay: 0.55s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-lightning-fill"></i> Quick Actions
                </h2>
            </div>
            <div class="modern-card-body">
                <div class="d-grid gap-2">
                    <?php if (hasPermission('pos.create')): ?>
                    <a href="/pos" class="btn-modern btn-primary">
                        <i class="bi bi-cart-plus"></i> New Sale
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('rentals.create')): ?>
                    <a href="/rentals/reservations/create" class="btn-modern btn-success">
                        <i class="bi bi-gear"></i> New Rental
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('customers.create')): ?>
                    <a href="/customers/create" class="btn-modern btn-secondary">
                        <i class="bi bi-person-plus"></i> Add Customer
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($alerts)): ?>
        <div class="modern-card slide-up" style="animation-delay: 0.6s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-bell"></i> Alerts
                </h2>
            </div>
            <div class="modern-card-body">
                <?php foreach ($alerts as $alert): ?>
                <div class="alert-modern alert-<?= $alert['type'] ?>" style="margin-bottom: 1rem;">
                    <div class="alert-modern-icon">
                        <i class="bi bi-<?= $alert['type'] === 'warning' ? 'exclamation-triangle' : ($alert['type'] === 'error' ? 'x-circle' : 'info-circle') ?>-fill"></i>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($alert['title']) ?></strong>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">
                            <?= htmlspecialchars($alert['message']) ?>
                        </p>
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
        <div class="modern-card slide-up" style="animation-delay: 0.65s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-gear-wide-connected"></i> Equipment Status
                </h2>
            </div>
            <div class="modern-card-body">
                <canvas id="equipmentChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="modern-card slide-up" style="animation-delay: 0.7s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-star-fill"></i> Top Selling Products (Last 30 Days)
                </h2>
            </div>
            <div class="modern-card-body">
                <?php if (!empty($top_products)): ?>
                <div class="table-responsive">
                    <table class="table-modern">
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
                                    <br><small style="color: var(--text-tertiary);"><?= htmlspecialchars($product['sku']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge-modern badge-primary"><?= number_format($product['total_sold']) ?></span>
                                </td>
                                <td class="text-end" style="color: var(--success);">
                                    <strong><?= formatCurrency($product['revenue']) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p style="color: var(--text-tertiary); margin: 0;">No sales data available for the last 30 days.</p>
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
                    <i class="bi bi-clock-history"></i> Recent Transactions
                </h2>
            </div>
            <div class="modern-card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table-modern" style="margin-bottom: 0;">
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
                                <td><strong style="color: var(--success);"><?= formatCurrency($t['total']) ?></strong></td>
                                <td>
                                    <span class="badge-modern badge-<?= strtolower($t['status']) === 'completed' ? 'success' : (strtolower($t['status']) === 'pending' ? 'warning' : 'secondary') ?>">
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

require __DIR__ . '/../layouts/app.php';
?>
