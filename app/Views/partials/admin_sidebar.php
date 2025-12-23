    <div class="sidebar">
        <!-- Sidebar Brand -->
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2 px-3 py-4">
                <i class="bi bi-water text-primary fs-4"></i>
                <div>
                    <h6 class="mb-0 fw-bold text-white tracking-wide"><?= strtoupper(htmlspecialchars($companyName)) ?></h6>
                    <small class="text-white-50" style="font-size: 0.7rem;">MANAGEMENT</small>
                </div>
            </div>
        </div>

        <nav class="nav flex-column pb-5">
            <?php 
            // Check for active POS customer context
            $customerQuery = '';
            if (isset($_SESSION['active_customer_id']) && $_SESSION['active_customer_id'] > 0) {
                $customerQuery = '?customer_id=' . $_SESSION['active_customer_id'];
            }
            ?>

            <!-- SECTION: OVERVIEW -->
            <div class="sidebar-header">OVERVIEW</div>

            <?php if (hasPermission('dashboard.view')): ?>
            <a class="nav-link <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>" href="/store">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('pos.view')): ?>
            <a class="nav-link <?= ($activeMenu ?? '') === 'pos' ? 'active' : '' ?>" href="/store/pos">
                <i class="bi bi-credit-card-fill"></i><span>Point of Sale</span>
            </a>
            <?php endif; ?>

            <!-- SECTION: COMMERCE -->
            <div class="sidebar-header mt-3">OPERATIONS</div>

            <?php if (hasPermission('customers.view')): ?>
            <a class="nav-link <?= ($activeMenu ?? '') === 'customers' ? 'active' : '' ?>" href="/store/customers<?= $customerQuery ?>">
                <i class="bi bi-people-fill"></i><span>Customers</span>
            </a>
            <?php endif; ?>

            <?php if (hasPermission('products.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'inventory' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#inventoryMenu" role="button" aria-expanded="false">
                    <i class="bi bi-box-seam-fill"></i><span>Inventory</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="inventoryMenu">
                    <a class="nav-link ps-5" href="/store/products">All Products</a>
                    <a class="nav-link ps-5" href="/store/categories">Categories</a>
                    <a class="nav-link ps-5" href="/store/vendors">Vendors</a>
                    <a class="nav-link ps-5" href="/store/purchase-orders">Purchase Orders</a>
                    <a class="nav-link ps-5" href="/store/inventory/adjustments">Stock Adjustments</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('rentals.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'rentals' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#rentalsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-briefcase-fill"></i><span>Rentals</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="rentalsMenu">
                    <a class="nav-link ps-5" href="/store/rentals<?= $customerQuery ?>">Equipment Fleet</a>
                    <a class="nav-link ps-5" href="/store/rentals/reservations<?= $customerQuery ?>">Reservations</a>
                    <a class="nav-link ps-5" href="/store/maintenance">Maintenance Log</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('services.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'services' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#servicesMenu" role="button" aria-expanded="false">
                    <i class="bi bi-tools"></i><span>Services & Air</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="servicesMenu">
                    <a class="nav-link ps-5" href="/store/workorders<?= $customerQuery ?>">Work Orders</a>
                    <a class="nav-link ps-5" href="/store/air-fills<?= $customerQuery ?>">Air Fills</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION: EDUCATION & TRAVEL -->
            <div class="sidebar-header mt-3">EXPERIENCES</div>

            <?php if (hasPermission('courses.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'courses' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#coursesMenu" role="button" aria-expanded="false">
                    <i class="bi bi-mortarboard-fill"></i><span>Courses</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="coursesMenu">
                    <a class="nav-link ps-5" href="/store/courses<?= $customerQuery ?>">Catalog</a>
                    <a class="nav-link ps-5" href="/store/courses/schedules<?= $customerQuery ?>">Class Schedule</a>
                    <a class="nav-link ps-5" href="/store/courses/enrollments<?= $customerQuery ?>">Students</a>
                    <a class="nav-link ps-5" href="/store/certifications<?= $customerQuery ?>">Certifications</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('trips.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'trips' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#tripsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-airplane-fill"></i><span>Trips</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="tripsMenu">
                    <a class="nav-link ps-5" href="/store/trips<?= $customerQuery ?>">Catalog</a>
                    <a class="nav-link ps-5" href="/store/trips/schedules<?= $customerQuery ?>">Trip Schedule</a>
                    <a class="nav-link ps-5" href="/store/trips/bookings<?= $customerQuery ?>">Bookings</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION: ADMIN -->
            <div class="sidebar-header mt-3">MANAGEMENT</div>

            <?php if (hasPermission('settings.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'storefront' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#storefrontMenu" role="button" aria-expanded="false">
                    <i class="bi bi-shop"></i><span>Storefront</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="storefrontMenu">
                    <a class="nav-link ps-5" href="/store/storefront/navigation">Navigation</a>
                    <a class="nav-link ps-5" href="/store/storefront/announcements">Announcements</a>
                    <a class="nav-link ps-5" href="/store/cms/pages">Pages</a>
                    <a class="nav-link ps-5" href="/store/cms/blog">Blog</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('reports.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'reports' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-graph-up-arrow"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="reportsMenu">
                    <a class="nav-link ps-5" href="/store/reports/sales">Sales Performance</a>
                    <a class="nav-link ps-5" href="/store/reports/inventory">Inventory Value</a>
                    <a class="nav-link ps-5" href="/store/reports/customers">Customer Insights</a>
                    <a class="nav-link ps-5" href="/store/financial/overview">Financial Overview</a>
                </div>
            </div>
            <?php endif; ?>

            <?php if (hasPermission('settings.view')): ?>
            <div class="nav-item">
                <a class="nav-link <?= ($activeMenu ?? '') === 'settings' ? 'active' : '' ?>" data-bs-toggle="collapse" href="#settingsMenu" role="button" aria-expanded="false">
                    <i class="bi bi-gear-fill"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="settingsMenu">
                    <a class="nav-link ps-5" href="/store/admin/settings/general">General Settings</a>
                    <a class="nav-link ps-5" href="/store/admin/users">Users & Roles</a>
                    <a class="nav-link ps-5" href="/store/storefront/settings">Storefront Config</a>
                    <a class="nav-link ps-5" href="/store/admin/settings/integrations">Integrations</a>
                </div>
            </div>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-chevron-left"></i>
    </button>
