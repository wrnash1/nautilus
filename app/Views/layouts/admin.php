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

    <!-- PWA Meta Tags -->
    <meta name="description" content="Complete dive shop management system with POS, inventory, courses, and more">
    <meta name="theme-color" content="#0066cc">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($companyName) ?>">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Favicons -->
    <?php if ($favicon): ?>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($favicon) ?>">
    <?php else: ?>
    <link rel="icon" type="image/svg+xml" href="/favicon.ico">
    <?php endif; ?>
    <link rel="apple-touch-icon" href="/assets/images/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/icon-180x180.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/assets/images/icon-167x167.png">
    
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
    <?php include BASE_PATH . '/app/Views/partials/admin_header.php'; ?>

    <?php include BASE_PATH . '/app/Views/partials/admin_sidebar.php'; ?>

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

            <?php
            // Display Alpha/Development Warning Banner
            require BASE_PATH . '/app/Views/partials/alpha-warning.php';
            ?>

            <?php if (isset($content)) echo $content; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Alpine.js for interactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Nautilus Core JavaScript -->
    <script src="/assets/js/notifications.js"></script>
    <script src="/assets/js/keyboard-shortcuts.js"></script>
    <script src="/assets/js/accessibility.js"></script>
    <script src="/assets/js/form-validation.js"></script>
    <script src="/assets/js/alpine-components.js"></script>
    <script src="/assets/js/pwa-installer.js"></script>
    
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
            
            // Convert flash messages to toast notifications
            <?php if (isset($_SESSION['flash_success'])): ?>
                if (window.toast) {
                    toast.success('<?= addslashes($_SESSION['flash_success']) ?>');
                }
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_error'])): ?>
                if (window.toast) {
                    toast.error('<?= addslashes($_SESSION['flash_error']) ?>');
                }
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash_warning'])): ?>
                if (window.toast) {
                    toast.warning('<?= addslashes($_SESSION['flash_warning']) ?>');
                }
            <?php endif; ?>
        });
    </script>

    <?php if (isset($additionalJs)) echo $additionalJs; ?>
</body>
</html>
