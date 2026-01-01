<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    // Get company branding settings for title and favicon
    // Get company branding settings for title and favicon
    use App\Core\Settings;
    // Direct access to ensure we get the latest value regardless of category logic
    $settings = Settings::getInstance();
    $companyName = $settings->get('business_name') ?: (getenv('APP_NAME') ?: 'Nautilus Dive Shop');
    $favicon = $settings->get('company_favicon_path');
    $brandingSettings = $settings->all(); // Pass all settings to be safe
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
            /* Semi-transparent Deep Ocean */
            background: linear-gradient(180deg, rgba(8, 51, 68, 0.95) 0%, rgba(8, 145, 178, 0.85) 100%);
            backdrop-filter: blur(10px);
            padding-top: 0;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
            transition: all 0.3s ease;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Fish Animation Container */
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .fish {
            position: absolute;
            font-size: 1.5rem;
            opacity: 0.3;
            pointer-events: none;
            z-index: 1;
            /* Simple fish shape using emoji or icon for now, usually SVGs */
        }

        @keyframes swimLeft {
            from {
                transform: translateX(100%);
                left: 100%;
            }

            to {
                transform: translateX(-100%);
                left: -50px;
            }
        }

        @keyframes swimRight {
            from {
                transform: translateX(-100%) scaleX(-1);
                left: -50px;
            }

            to {
                transform: translateX(100%) scaleX(-1);
                left: 100%;
            }
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .nav-link span,
        .sidebar.collapsed .nav-link .bi-chevron-down,
        .sidebar.collapsed .sidebar-header,
        .sidebar.collapsed .sidebar-brand small {
            opacity: 0;
            width: 0;
            overflow: hidden;
            display: none;
        }

        .sidebar.collapsed .sidebar-brand h6 {
            display: none;
        }

        .sidebar.collapsed .sidebar-brand i {
            margin: 0 auto;
            font-size: 1.5rem;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed .nav-link i:first-child {
            margin-right: 0;
        }

        .sidebar.collapsed .collapse {
            display: none !important;
        }

        .sidebar-brand {
            height: 60px;
            display: flex;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            margin-bottom: 1rem;
        }

        .sidebar-header {
            color: #ffffff !important;
            font-size: 0.70rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            padding: 1.5rem 1.25rem 0.5rem;
            text-transform: uppercase;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
            position: relative;
            z-index: 100;
        }

        .sidebar .nav-link {
            color: #ffffff !important;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
            opacity: 1 !important;
            padding: 0.65rem 1.25rem;
            border-left: 3px solid transparent;
            white-space: nowrap;
            transition: all 0.2s ease;
            position: relative;
            z-index: 100 !important;
            display: flex;
            pointer-events: auto !important;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Fish Animation */
        .fish-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 1;
        }

        .sidebar-decor {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 300px;
            background-image: url('/assets/images/sidebar_bg_v2.png');
            background-repeat: no-repeat;
            background-position: bottom center;
            background-size: contain;
            pointer-events: none;
            z-index: 1;
            opacity: 0.8;
        }

        .fish {
            position: absolute;
            width: 40px;
            height: 40px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.6;
            transition: transform 0.2s;
            user-select: none;
        }

        @keyframes swimLeft {
            from {
                transform: translateX(280px);
            }

            to {
                transform: translateX(-50px);
            }
        }

        @keyframes swimRight {
            from {
                transform: translateX(-50px) scaleX(-1);
            }

            to {
                transform: translateX(280px) scaleX(-1);
            }
        }

        align-items: center;
        font-weight: 500;
        }

        .sidebar .nav-link span {
            transition: opacity 0.3s ease, width 0.3s ease;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.15), transparent);
            /* Sky 500 alpha */
            border-left-color: #38bdf8;
            /* Sky 400 */
        }

        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            transition: margin 0.3s ease;
            opacity: 0.9;
        }

        .sidebar .nav-link i:first-child {
            margin-right: 0.75rem;
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
            // Version Check
            $versionFile = BASE_PATH . '/VERSION';
            $serverVersion = file_exists($versionFile) ? trim(file_get_contents($versionFile)) : '1.0.0';

            // In a real app, you would fetch this from a remote endpoint
            // For now, we assume we are up to date unless specified otherwise
            $latestVersion = $serverVersion;
            $updateAvailable = version_compare($serverVersion, $latestVersion, '<');

            // Only show to Admin (Role ID 1)
            $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;

            if ($is_admin && $updateAvailable):
                ?>
                <div class="alert alert-info alert-dismissible fade show shadow-sm border-info mt-3" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cloud-arrow-down-fill fs-4 me-3"></i>
                        <div>
                            <strong>Update Available!</strong> A new version of Nautilus (v<?= $latestVersion ?>) is
                            available.
                            Your version: v<?= $serverVersion ?>.
                            <div class="mt-1">
                                <a href="/store/admin/system/update"
                                    class="btn btn-sm btn-light text-info fw-bold border">Update Now</a>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none text-info"
                                    data-bs-dismiss="alert">Remind me later</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php
            // Display Alpha/Development Warning Banner
            require BASE_PATH . '/app/Views/partials/alpha-warning.php';
            ?>

            <?php if (isset($content))
                echo $content; ?>
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
        document.addEventListener('DOMContentLoaded', function () {
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
            toggleBtn.addEventListener('click', function () {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('sidebar-collapsed');
                navbar.classList.toggle('sidebar-collapsed');
                toggleBtn.classList.toggle('collapsed');

                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });

            // Mobile: Toggle sidebar on button click
            if (window.innerWidth <= 768) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('show');
                });

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function (event) {
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

        // Fish Animation Logic
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar || sidebar.classList.contains('collapsed')) return;

            const container = document.createElement('div');
            container.className = 'fish-container';
            container.style.position = 'absolute';
            container.style.top = '0';
            container.style.left = '0';
            container.style.width = '100%';
            container.style.height = '100%';
            container.style.pointerEvents = 'none'; // CRITICAL: prevent clicks blocking
            container.style.zIndex = '0'; // Background layer
            sidebar.insertBefore(container, sidebar.firstChild); // NEW: Put at start

            const decor = document.createElement('div');
            decor.className = 'sidebar-decor';
            decor.style.position = 'absolute';
            decor.style.bottom = '0';
            decor.style.left = '0';
            decor.style.width = '100%';
            decor.style.height = '300px';
            decor.style.pointerEvents = 'none'; // FORCE
            decor.style.zIndex = '0'; // Background layer
            sidebar.insertBefore(decor, sidebar.firstChild); // NEW: Put at start (decor first, then fish?) 
            // Actually order of prepends matter. 
            // If we prepend container, then prepend decor, decor is first child.
            // Both are z-index 0.

            const seaLife = [
                'sealife_turtle.png', 'sealife_shark.png', 'sealife_ray.png',
                'sealife_jellyfish.png', 'sealife_fish1.png', 'sealife_seahorse.png'
            ];

            function createFish() {
                if (sidebar.classList.contains('collapsed')) return;
                if (document.querySelectorAll('.fish').length > 15) return;

                const fish = document.createElement('div');
                fish.className = 'fish';
                const creature = seaLife[Math.floor(Math.random() * seaLife.length)];
                fish.style.backgroundImage = `url('/assets/images/${creature}')`;

                let size = 25 + Math.random() * 35;
                fish.style.width = `${size}px`;
                fish.style.height = `${size}px`;
                fish.style.backgroundSize = 'contain';
                fish.style.backgroundRepeat = 'no-repeat';
                fish.style.position = 'absolute';

                const top = Math.random() * (sidebar.offsetHeight - 50);
                const duration = 10000 + Math.random() * 10000;
                const isRight = Math.random() > 0.5;

                fish.style.top = `${top}px`;
                fish.style.animation = `${isRight ? 'swimRight' : 'swimLeft'} ${duration}ms linear forwards`;
                fish.style.opacity = '0.7';
                fish.style.pointerEvents = 'none';
                fish.style.zIndex = '0';  // Background

                const hue = Math.floor(Math.random() * 360);
                fish.style.filter = `hue-rotate(${hue}deg)`;

                container.appendChild(fish);

                setTimeout(() => { fish.remove(); }, duration);
            }

            setInterval(createFish, 1200);
            createFish();
        });
    </script>

    <?php if (isset($additionalJs))
        echo $additionalJs; ?>
</body>

</html>