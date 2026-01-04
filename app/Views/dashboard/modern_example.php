<?php
/**
 * Modern Dashboard Example
 * Showcases the new theme system with dark mode support
 */
$pageTitle = 'Modern Dashboard';
$activeMenu = 'dashboard';

ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Nautilus</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Modern Theme CSS -->
    <link rel="stylesheet" href="/assets/css/modern-theme.css">

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .modern-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-500), var(--primary-700));
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--radius-xl);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 1rem 0;
        }

        .hero-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .feature-card {
            background: var(--bg-primary);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-base);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .feature-icon {
            width: 3rem;
            height: 3rem;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--primary-500), var(--primary-600));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .chart-container {
            background: var(--bg-primary);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-md);
        }

        .actions-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-tertiary);
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
        }
    </style>
</head>
<body>

<div class="modern-container">
    <!-- Hero Section -->
    <div class="hero-section slide-up">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to Nautilus Dive Shop</h1>
            <p class="hero-subtitle">Your complete dive shop management solution with modern design and dark mode support</p>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="actions-bar slide-up" style="animation-delay: 0.1s;">
        <div class="search-box">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="input-modern search-input" placeholder="Search products, customers, orders...">
        </div>
        <button class="btn-modern btn-primary" onclick="toast.success('Feature coming soon!')">
            <i class="bi bi-plus-lg"></i>
            New Sale
        </button>
        <button class="btn-modern btn-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i>
            Print
        </button>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card-modern slide-up" style="animation-delay: 0.2s;">
            <div class="stat-value-modern">$12,450</div>
            <div class="stat-label-modern">Today's Revenue</div>
            <div class="stat-change-modern stat-change-positive">
                <i class="bi bi-arrow-up"></i>
                <span>12.5%</span>
            </div>
        </div>

        <div class="stat-card-modern slide-up" style="animation-delay: 0.25s;">
            <div class="stat-value-modern">156</div>
            <div class="stat-label-modern">Active Orders</div>
            <div class="stat-change-modern stat-change-positive">
                <i class="bi bi-arrow-up"></i>
                <span>8.2%</span>
            </div>
        </div>

        <div class="stat-card-modern slide-up" style="animation-delay: 0.3s;">
            <div class="stat-value-modern">1,247</div>
            <div class="stat-label-modern">Total Customers</div>
            <div class="stat-change-modern stat-change-positive">
                <i class="bi bi-arrow-up"></i>
                <span>3.1%</span>
            </div>
        </div>

        <div class="stat-card-modern slide-up" style="animation-delay: 0.35s;">
            <div class="stat-value-modern">23</div>
            <div class="stat-label-modern">Low Stock Items</div>
            <div class="stat-change-modern stat-change-negative">
                <i class="bi bi-arrow-down"></i>
                <span>5 from yesterday</span>
            </div>
        </div>
    </div>

    <!-- Feature Cards -->
    <div class="feature-grid">
        <div class="feature-card slide-up" style="animation-delay: 0.4s;">
            <div class="feature-icon">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <h3 class="feature-title">Quick Actions</h3>
            <p class="feature-description">
                Access frequently used features with keyboard shortcuts. Press Ctrl+K to open the quick actions menu.
            </p>
            <button class="btn-modern btn-primary" style="margin-top: 1rem;" onclick="toast.info('Press Ctrl+K to open quick actions')">
                Try It Now
            </button>
        </div>

        <div class="feature-card slide-up" style="animation-delay: 0.45s;">
            <div class="feature-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <h3 class="feature-title">Advanced Analytics</h3>
            <p class="feature-description">
                Get deep insights into your business performance with comprehensive analytics and reporting tools.
            </p>
            <button class="btn-modern btn-success" style="margin-top: 1rem;" onclick="window.location='/analytics'">
                View Analytics
            </button>
        </div>

        <div class="feature-card slide-up" style="animation-delay: 0.5s;">
            <div class="feature-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                <i class="bi bi-award-fill"></i>
            </div>
            <h3 class="feature-title">Loyalty Program</h3>
            <p class="feature-description">
                Engage customers with a powerful 4-tier loyalty program featuring points, rewards, and exclusive benefits.
            </p>
            <button class="btn-modern btn-secondary" style="margin-top: 1rem; background: linear-gradient(135deg, var(--warning), #d97706); color: white;" onclick="window.location='/loyalty'">
                Manage Program
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="modern-card slide-up" style="animation-delay: 0.55s;">
        <div class="modern-card-header">
            <h2 class="modern-card-title">Recent Activity</h2>
            <button class="btn-modern btn-ghost">
                <i class="bi bi-three-dots"></i>
            </button>
        </div>
        <div class="modern-card-body">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Action</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-full); background: linear-gradient(135deg, var(--primary-400), var(--primary-600)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">JD</div>
                                <div>
                                    <div style="font-weight: 500;">John Doe</div>
                                    <div style="font-size: 0.875rem; color: var(--text-tertiary);">john@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Course Enrollment</td>
                        <td><strong>$450.00</strong></td>
                        <td><span class="badge-modern badge-success">Completed</span></td>
                        <td>2 minutes ago</td>
                    </tr>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-full); background: linear-gradient(135deg, var(--success), #059669); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">SM</div>
                                <div>
                                    <div style="font-weight: 500;">Sarah Miller</div>
                                    <div style="font-size: 0.875rem; color: var(--text-tertiary);">sarah.m@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Equipment Rental</td>
                        <td><strong>$85.00</strong></td>
                        <td><span class="badge-modern badge-warning">Pending</span></td>
                        <td>15 minutes ago</td>
                    </tr>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-full); background: linear-gradient(135deg, var(--warning), #d97706); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">RJ</div>
                                <div>
                                    <div style="font-weight: 500;">Robert Johnson</div>
                                    <div style="font-size: 0.875rem; color: var(--text-tertiary);">robert.j@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Product Purchase</td>
                        <td><strong>$1,250.00</strong></td>
                        <td><span class="badge-modern badge-success">Completed</span></td>
                        <td>1 hour ago</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Demo Alerts -->
    <div class="grid-modern grid-cols-2" style="margin-top: 2rem;">
        <div class="alert-modern alert-success slide-up" style="animation-delay: 0.6s;">
            <div class="alert-modern-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <strong>System Updated</strong>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">Your system has been updated to the latest version with new features and improvements.</p>
            </div>
        </div>

        <div class="alert-modern alert-warning slide-up" style="animation-delay: 0.65s;">
            <div class="alert-modern-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <strong>Low Stock Alert</strong>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem;">23 products are running low on stock. Review and create purchase orders.</p>
            </div>
        </div>
    </div>

    <!-- Demo Buttons -->
    <div class="modern-card" style="margin-top: 2rem;">
        <div class="modern-card-header">
            <h2 class="modern-card-title">Theme Features Demo</h2>
        </div>
        <div class="modern-card-body">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                <button class="btn-modern btn-primary" onclick="toast.success('Success toast notification!')">
                    <i class="bi bi-check-circle"></i>
                    Show Success Toast
                </button>
                <button class="btn-modern btn-secondary" onclick="toast.error('Error toast notification!')">
                    <i class="bi bi-x-circle"></i>
                    Show Error Toast
                </button>
                <button class="btn-modern btn-success" onclick="toast.warning('Warning toast notification!')">
                    <i class="bi bi-exclamation-triangle"></i>
                    Show Warning Toast
                </button>
                <button class="btn-modern btn-danger" onclick="testConfirmDialog()">
                    <i class="bi bi-question-circle"></i>
                    Show Confirm Dialog
                </button>
            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button class="btn-modern btn-ghost" onclick="testLoading()">
                    <i class="bi bi-hourglass"></i>
                    Show Loading
                </button>
                <button class="btn-modern btn-primary" disabled>
                    <i class="bi bi-lock"></i>
                    Disabled Button
                </button>
                <span class="badge-modern badge-primary">Primary Badge</span>
                <span class="badge-modern badge-success">Success Badge</span>
                <span class="badge-modern badge-warning">Warning Badge</span>
                <span class="badge-modern badge-danger">Danger Badge</span>
            </div>
        </div>
    </div>
</div>

<!-- Theme Manager JavaScript -->
<script src="/assets/js/theme-manager.js"></script>

<script>
// Demo functions
async function testConfirmDialog() {
    const confirmed = await confirmModern(
        'Are you sure you want to perform this action? This cannot be undone.',
        'Confirm Action'
    );

    if (confirmed) {
        toast.success('Action confirmed!');
    } else {
        toast.info('Action cancelled');
    }
}

function testLoading() {
    loading.show('Processing your request...');
    setTimeout(() => {
        loading.hide();
        toast.success('Processing complete!');
    }, 2000);
}

// Show welcome message
setTimeout(() => {
    toast.info('Toggle dark mode using the button in the bottom-right corner!', 5000);
}, 1000);
</script>

</body>
</html>

<?php
$content = ob_get_clean();
echo $content;
?>
