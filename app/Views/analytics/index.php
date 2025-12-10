<?php
$pageTitle = 'Analytics Dashboard';
$activeMenu = 'analytics';

ob_start();
?>

<!-- Page Header -->
<div class="hero-section slide-up" style="background: linear-gradient(135deg, var(--primary-500), var(--primary-700)); color: white; padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 2rem; position: relative; overflow: hidden;">
    <div style="content: ''; position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0;"><i class="bi bi-graph-up-arrow"></i> Analytics Dashboard</h1>
                <p style="font-size: 1rem; opacity: 0.9; margin: 0;">Comprehensive business intelligence and performance metrics</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button onclick="exportAnalytics()" class="btn-modern btn-secondary">
                    <i class="bi bi-download"></i> Export Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="modern-card slide-up mb-4" style="animation-delay: 0.1s;">
    <div class="modern-card-body">
        <form method="GET" action="/analytics" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 500;">Start Date</label>
                <input type="date" name="start_date" class="input-modern" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" style="color: var(--text-secondary); font-size: 0.875rem; font-weight: 500;">End Date</label>
                <input type="date" name="end_date" class="input-modern" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-modern btn-primary">
                    <i class="bi bi-filter"></i> Apply Filter
                </button>
                <button type="button" onclick="window.location='/analytics'" class="btn-modern btn-ghost">
                    <i class="bi bi-x-circle"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Key Metrics -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="stat-card-modern slide-up" style="animation-delay: 0.15s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--primary-400), var(--primary-600));">
            <i class="bi bi-currency-dollar"></i>
        </div>
        <div class="stat-value-modern"><?= formatCurrency($metrics['total_revenue'] ?? 0) ?></div>
        <div class="stat-label-modern">Total Revenue</div>
        <?php if (isset($metrics['revenue_growth']) && $metrics['revenue_growth'] != 0): ?>
        <div class="stat-change-modern <?= $metrics['revenue_growth'] > 0 ? 'stat-change-positive' : 'stat-change-negative' ?>">
            <i class="bi bi-arrow-<?= $metrics['revenue_growth'] > 0 ? 'up' : 'down' ?>"></i>
            <span><?= abs(number_format($metrics['revenue_growth'], 1)) ?>% vs previous period</span>
        </div>
        <?php endif; ?>
    </div>

    <div class="stat-card-modern slide-up" style="animation-delay: 0.2s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--success), #059669);">
            <i class="bi bi-cart-check"></i>
        </div>
        <div class="stat-value-modern"><?= number_format($metrics['total_orders'] ?? 0) ?></div>
        <div class="stat-label-modern">Total Orders</div>
        <?php if (isset($metrics['orders_growth']) && $metrics['orders_growth'] != 0): ?>
        <div class="stat-change-modern <?= $metrics['orders_growth'] > 0 ? 'stat-change-positive' : 'stat-change-negative' ?>">
            <i class="bi bi-arrow-<?= $metrics['orders_growth'] > 0 ? 'up' : 'down' ?>"></i>
            <span><?= abs(number_format($metrics['orders_growth'], 1)) ?>%</span>
        </div>
        <?php endif; ?>
    </div>

    <div class="stat-card-modern slide-up" style="animation-delay: 0.25s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--warning), #d97706);">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="stat-value-modern"><?= formatCurrency($metrics['avg_order_value'] ?? 0) ?></div>
        <div class="stat-label-modern">Avg Order Value</div>
    </div>

    <div class="stat-card-modern slide-up" style="animation-delay: 0.3s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-value-modern"><?= number_format($metrics['new_customers'] ?? 0) ?></div>
        <div class="stat-label-modern">New Customers</div>
    </div>
</div>

<!-- Revenue Breakdown -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="modern-card slide-up" style="animation-delay: 0.35s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-bar-chart-line"></i> Revenue by Category
                </h2>
            </div>
            <div class="modern-card-body">
                <?php if (!empty($revenueBreakdown)): ?>
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Avg Value</th>
                                <th class="text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenueBreakdown as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['category']) ?></strong></td>
                                <td class="text-end" style="color: var(--success);"><strong><?= formatCurrency($item['revenue']) ?></strong></td>
                                <td class="text-end"><?= number_format($item['orders']) ?></td>
                                <td class="text-end"><?= formatCurrency($item['avg_value']) ?></td>
                                <td class="text-end">
                                    <span class="badge-modern badge-primary"><?= number_format($item['percentage'], 1) ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="color: var(--text-tertiary); margin: 0; padding: 2rem; text-align: center;">No revenue data available for the selected period.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="modern-card slide-up" style="animation-delay: 0.4s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-pie-chart"></i> Revenue Distribution
                </h2>
            </div>
            <div class="modern-card-body">
                <canvas id="revenueChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Hourly Sales Pattern -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card slide-up" style="animation-delay: 0.45s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-clock"></i> Hourly Sales Pattern
                </h2>
            </div>
            <div class="modern-card-body">
                <canvas id="hourlySalesChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Day of Week Performance -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card slide-up" style="animation-delay: 0.5s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-calendar-week"></i> Day of Week Performance
                </h2>
            </div>
            <div class="modern-card-body">
                <?php if (!empty($dayOfWeek)): ?>
                <div class="table-responsive">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Avg Order Value</th>
                                <th class="text-end">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day):
                                $data = $dayOfWeek[$day] ?? ['revenue' => 0, 'orders' => 0, 'avg_value' => 0, 'performance' => 0];
                            ?>
                            <tr>
                                <td><strong><?= $day ?></strong></td>
                                <td class="text-end" style="color: var(--success);"><strong><?= formatCurrency($data['revenue']) ?></strong></td>
                                <td class="text-end"><?= number_format($data['orders']) ?></td>
                                <td class="text-end"><?= formatCurrency($data['avg_value']) ?></td>
                                <td class="text-end">
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                                        <div style="flex: 0 0 100px; height: 8px; background: var(--bg-tertiary); border-radius: 4px; overflow: hidden;">
                                            <div style="width: <?= min(100, $data['performance']) ?>%; height: 100%; background: linear-gradient(135deg, var(--primary-500), var(--primary-600)); transition: width 0.3s;"></div>
                                        </div>
                                        <span style="font-size: 0.875rem; color: var(--text-secondary);"><?= number_format($data['performance'], 0) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="color: var(--text-tertiary); margin: 0; padding: 2rem; text-align: center;">No performance data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="grid-modern grid-cols-3" style="gap: 1.5rem;">
    <a href="/analytics/sales" class="modern-card slide-up" style="animation-delay: 0.55s; text-decoration: none; transition: all var(--transition-base);">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--primary-400), var(--primary-600)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-graph-up" style="font-size: 2rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Sales Analytics</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">Detailed sales metrics and trends</p>
        </div>
    </a>

    <a href="/analytics/customers" class="modern-card slide-up" style="animation-delay: 0.6s; text-decoration: none; transition: all var(--transition-base);">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--success), #059669); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-people" style="font-size: 2rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Customer Analytics</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">Customer behavior and retention</p>
        </div>
    </a>

    <a href="/analytics/products" class="modern-card slide-up" style="animation-delay: 0.65s; text-decoration: none; transition: all var(--transition-base);">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--warning), #d97706); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-box-seam" style="font-size: 2rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Product Analytics</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">Product performance insights</p>
        </div>
    </a>
</div>

<?php
$content = ob_get_clean();

// Additional CSS
$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">';

// Additional JavaScript
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function exportAnalytics() {
    const startDate = "<?= htmlspecialchars($startDate) ?>";
    const endDate = "<?= htmlspecialchars($endDate) ?>";
    window.location.href = `/analytics/export?start_date=${startDate}&end_date=${endDate}&type=dashboard`;
}

// Revenue Chart
const revenueData = <?= json_encode($revenueBreakdown ?? []) ?>;
if (revenueData.length > 0) {
    const ctx = document.getElementById("revenueChart");
    new Chart(ctx, {
        type: "doughnut",
        data: {
            labels: revenueData.map(item => item.category),
            datasets: [{
                data: revenueData.map(item => item.revenue),
                backgroundColor: [
                    "rgba(14, 165, 233, 0.8)",
                    "rgba(16, 185, 129, 0.8)",
                    "rgba(251, 146, 60, 0.8)",
                    "rgba(139, 92, 246, 0.8)",
                    "rgba(236, 72, 153, 0.8)",
                    "rgba(234, 179, 8, 0.8)"
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom"
                }
            }
        }
    });
}

// Hourly Sales Chart
const hourlySalesData = <?= json_encode($hourlySales ?? []) ?>;
if (hourlySalesData.length > 0) {
    const ctx = document.getElementById("hourlySalesChart");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: hourlySalesData.map(item => item.hour + ":00"),
            datasets: [{
                label: "Sales",
                data: hourlySalesData.map(item => item.revenue),
                borderColor: "rgba(14, 165, 233, 1)",
                backgroundColor: "rgba(14, 165, 233, 0.1)",
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Show welcome toast
setTimeout(() => {
    toast.info("Use the date filters to view analytics for different time periods", 5000);
}, 1000);
</script>';

require __DIR__ . '/../layouts/app.php';
?>
