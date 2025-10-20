<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    // Get company branding settings for title and favicon
    use App\Services\Admin\SettingsService;
    $settingsService = new SettingsService();
    $brandingSettings = $settingsService->getSettingsByCategory('general');
    $companyName = $brandingSettings['business_name'] ?? 'Nautilus Dive Shop';
    $favicon = $brandingSettings['company_favicon_path'] ?? '';
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= htmlspecialchars($companyName) ?></title>

    <?php if ($favicon): ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($favicon) ?>">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <?php if (isset($additionalCss)): ?>
        <?= $additionalCss ?>
    <?php endif; ?>

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #212529;
            padding-top: 56px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-link .bi-chevron-down {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar.collapsed .nav-link i:first-child {
            margin-right: 0;
        }

        .sidebar.collapsed .collapse {
            display: none !important;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
            white-space: nowrap;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link span {
            transition: opacity 0.3s ease, width 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #0d6efd;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            transition: margin 0.3s ease;
        }

        .sidebar .nav-link i:first-child {
            margin-right: 0.5rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: 56px;
            transition: margin-left 0.3s ease;
        }

        .main-content.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed-width);
        }

        .navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 1030;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
            transition: left 0.3s ease;
        }

        .navbar.sidebar-collapsed {
            left: var(--sidebar-collapsed-width);
        }

        .sidebar-toggle {
            position: fixed;
            bottom: 20px;
            left: 210px;
            width: 40px;
            height: 40px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: #0b5ed7;
            transform: scale(1.1);
        }

        .sidebar-toggle.collapsed {
            left: 30px;
        }

        .sidebar-toggle i {
            transition: transform 0.3s ease;
        }

        .sidebar-toggle.collapsed i {
            transform: rotate(180deg);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.sidebar-collapsed {
                margin-left: 0;
            }

            .navbar {
                left: 0;
            }

            .navbar.sidebar-collapsed {
                left: 0;
            }

            .sidebar-toggle {
                left: 20px;
                bottom: 20px;
            }

            .sidebar-toggle.collapsed {
                left: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <?php if (!empty($brandingSettings['company_logo_small_path'])): ?>
                    <img src="<?= htmlspecialchars($brandingSettings['company_logo_small_path']) ?>"
                         alt="<?= htmlspecialchars($companyName) ?>"
                         style="height: 32px; width: auto; margin-right: 0.5rem; vertical-align: middle;">
                <?php else: ?>
                    <i class="bi bi-water" style="color: <?= htmlspecialchars($brandingSettings['brand_primary_color'] ?? '#0066CC') ?>"></i>
                <?php endif; ?>
                <?= htmlspecialchars($companyName) ?>
            </span>
            <div class="d-flex align-items-center">
                <!-- Language Switcher -->
                <?php require BASE_PATH . '/app/Views/components/language_switcher.php'; ?>

                <span class="me-3 ms-3">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($user['first_name'] ?? 'User') ?>
                    <small class="text-muted">(<?= htmlspecialchars($user['role_name'] ?? 'Unknown') ?>)</small>
                </span>
                <form method="POST" action="/logout" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="sidebar">
        <nav class="nav flex-column">
            <?php if (hasPermission('dashboard.view')): ?>
            <a class="nav-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>" href="/">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('pos.view')): ?>
            <a class="nav-link <?= $activeMenu === 'pos' ? 'active' : '' ?>" href="/pos">
                <i class="bi bi-cart-check"></i><span>Point of Sale</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('customers.view')): ?>
            <a class="nav-link <?= $activeMenu === 'customers' ? 'active' : '' ?>" href="/customers">
                <i class="bi bi-people"></i><span>Customers</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('products.view')): ?>
            <a class="nav-link <?= $activeMenu === 'products' ? 'active' : '' ?>" href="/products">
                <i class="bi bi-box-seam"></i><span>Products</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('categories.view')): ?>
            <a class="nav-link <?= $activeMenu === 'categories' ? 'active' : '' ?>" href="/categories">
                <i class="bi bi-tags"></i><span>Categories</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('products.view')): ?>
            <a class="nav-link <?= $activeMenu === 'vendors' ? 'active' : '' ?>" href="/vendors">
                <i class="bi bi-building"></i><span>Vendors</span>
            </a>
            <?php endif; ?>
            
            <?php if (hasPermission('dashboard.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'reports' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-clipboard-data"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="reportsMenu">
                    <a class="nav-link ps-5" href="/reports/sales">Sales Report</a>
                    <a class="nav-link ps-5" href="/reports/customers">Customer Report</a>
                    <a class="nav-link ps-5" href="/reports/products">Product Report</a>
                    <a class="nav-link ps-5" href="/reports/payments">Payment Report</a>
                    <a class="nav-link ps-5" href="/reports/inventory">Inventory Report</a>
                    <a class="nav-link ps-5" href="/reports/low-stock">Low Stock Alert</a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (hasPermission('rentals.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'rentals' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#rentalsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-briefcase"></i><span>Rentals</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="rentalsMenu">
                    <a class="nav-link ps-5" href="/rentals">Equipment</a>
                    <a class="nav-link ps-5" href="/rentals/reservations">Reservations</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('air_fills.view')): ?>
            <a class="nav-link <?= $activeMenu === 'air-fills' ? 'active' : '' ?>" href="/air-fills">
                <i class="bi bi-wind"></i><span>Air Fills</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('dashboard.view')): ?>
            <a class="nav-link <?= $activeMenu === 'waivers' ? 'active' : '' ?>" href="/waivers">
                <i class="bi bi-file-earmark-check"></i><span>Waivers</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('courses.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'courses' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#coursesMenu" role="button" aria-expanded="false">
                    <i class="bi bi-mortarboard"></i><span>Courses</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="coursesMenu">
                    <a class="nav-link ps-5" href="/courses">Course Catalog</a>
                    <a class="nav-link ps-5" href="/courses/schedules">Schedules</a>
                    <a class="nav-link ps-5" href="/courses/enrollments">Enrollments</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('trips.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'trips' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#tripsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-airplane"></i><span>Trips</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="tripsMenu">
                    <a class="nav-link ps-5" href="/trips">Trip Catalog</a>
                    <a class="nav-link ps-5" href="/trips/schedules">Schedules</a>
                    <a class="nav-link ps-5" href="/trips/bookings">Bookings</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('workorders.view')): ?>
            <a class="nav-link <?= $activeMenu === 'workorders' ? 'active' : '' ?>" href="/workorders">
                <i class="bi bi-tools"></i><span>Work Orders</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('orders.view')): ?>
            <a class="nav-link <?= $activeMenu === 'orders' ? 'active' : '' ?>" href="/orders">
                <i class="bi bi-box-seam"></i><span>Orders</span>
            </a>
            <?php endif; ?>

            <a class="nav-link" href="/shop" target="_blank">
                <i class="bi bi-shop"></i><span>Online Store</span>
            </a>

            <hr class="my-2 text-white-50">

            <?php if (hasPermission('dashboard.view')): ?>
            <a class="nav-link <?= $activeMenu === 'dive-sites' ? 'active' : '' ?>" href="/dive-sites">
                <i class="bi bi-geo-alt"></i><span>Dive Sites</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('products.view')): ?>
            <a class="nav-link <?= $activeMenu === 'serial-numbers' ? 'active' : '' ?>" href="/serial-numbers">
                <i class="bi bi-upc-scan"></i><span>Serial Numbers</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('products.view')): ?>
            <a class="nav-link <?= $activeMenu === 'vendor-catalog' ? 'active' : '' ?>" href="/vendor-catalog/import">
                <i class="bi bi-cloud-download"></i><span>Vendor Import</span>
            </a>
            <?php endif; ?>

            <hr class="my-2 text-white-50">

            <?php if (hasPermission('dashboard.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'marketing' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#marketingMenu" role="button" aria-expanded="false">
                    <i class="bi bi-megaphone"></i><span>Marketing</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="marketingMenu">
                    <a class="nav-link ps-5" href="/marketing/loyalty">Loyalty Program</a>
                    <a class="nav-link ps-5" href="/marketing/coupons">Coupons</a>
                    <a class="nav-link ps-5" href="/marketing/campaigns">Email Campaigns</a>
                    <a class="nav-link ps-5" href="/marketing/referrals">Referrals</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('dashboard.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'cms' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#cmsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-file-earmark-text"></i><span>Content</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="cmsMenu">
                    <a class="nav-link ps-5" href="/cms/pages">Pages</a>
                    <a class="nav-link ps-5" href="/cms/blog">Blog Posts</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('staff.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'staff' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#staffMenu" role="button" aria-expanded="false">
                    <i class="bi bi-person-badge"></i><span>Staff</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="staffMenu">
                    <a class="nav-link ps-5" href="/staff">Employees</a>
                    <a class="nav-link ps-5" href="/staff/schedules">Schedules</a>
                    <a class="nav-link ps-5" href="/staff/timeclock">Time Clock</a>
                    <a class="nav-link ps-5" href="/staff/commissions">Commissions</a>
                </div>
            </div>
            <?php endif; ?>

            <hr class="my-2 text-white-50">

            <?php if (hasPermission('admin.integrations')): ?>
            <div class="nav-item">
                <a class="nav-link <?= $activeMenu === 'integrations' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#integrationsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-plugin"></i><span>Integrations</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="integrationsMenu">
                    <a class="nav-link ps-5" href="/integrations/wave">Wave Accounting</a>
                    <a class="nav-link ps-5" href="/integrations/quickbooks">QuickBooks</a>
                    <a class="nav-link ps-5" href="/integrations/google-workspace">Google Workspace</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('admin.api')): ?>
            <a class="nav-link <?= $activeMenu === 'api' ? 'active' : '' ?>" href="/api/tokens">
                <i class="bi bi-key"></i><span>API Tokens</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('admin.settings')): ?>
            <a class="nav-link <?= $activeMenu === 'settings' ? 'active' : '' ?>" href="/admin/settings">
                <i class="bi bi-gear"></i><span>Settings</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('admin.users')): ?>
            <a class="nav-link <?= $activeMenu === 'users' ? 'active' : '' ?>" href="/admin/users">
                <i class="bi bi-people-fill"></i><span>User Management</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('admin.roles')): ?>
            <a class="nav-link <?= $activeMenu === 'roles' ? 'active' : '' ?>" href="/admin/roles">
                <i class="bi bi-shield-lock"></i><span>Roles & Permissions</span>
            </a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>

    <div class="main-content">
        <div class="container-fluid py-4">
            <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_warning'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['flash_warning']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_warning']); ?>
            <?php endif; ?>
            
            <?php if (isset($content)) echo $content; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // Sidebar Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const navbar = document.querySelector('.navbar');
            const toggleBtn = document.getElementById('sidebarToggle');

            // Check localStorage for saved state
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
                navbar.classList.add('sidebar-collapsed');
                toggleBtn.classList.add('collapsed');
            }

            // Toggle button click handler
            toggleBtn.addEventListener('click', function() {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
                navbar.classList.toggle('sidebar-collapsed');
                toggleBtn.classList.toggle('collapsed');

                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });

            // Mobile: Toggle sidebar on button click
            if (window.innerWidth <= 768) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                });
            }
        });
    </script>

    <?php if (isset($additionalJs)) echo $additionalJs; ?>
</body>
</html>
