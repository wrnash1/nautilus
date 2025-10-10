<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">
    <title><?= $pageTitle ?? 'Nautilus' ?> - Nautilus Dive Shop</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 250px;
        }
        
        body {
            min-height: 100vh;
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
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #0d6efd;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: 56px;
        }
        
        .navbar {
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 1030;
            background: #fff;
            border-bottom: 1px solid #dee2e6;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .navbar {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-water"></i> Nautilus Dive Shop
            </span>
            <div class="d-flex align-items-center">
                <span class="me-3">
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
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <?php endif; ?>
            
            <?php if (hasPermission('pos.view')): ?>
            <a class="nav-link <?= $activeMenu === 'pos' ? 'active' : '' ?>" href="/pos">
                <i class="bi bi-cart-check"></i> Point of Sale
            </a>
            <?php endif; ?>
            
            <?php if (hasPermission('customers.view')): ?>
            <a class="nav-link <?= $activeMenu === 'customers' ? 'active' : '' ?>" href="/customers">
                <i class="bi bi-people"></i> Customers
            </a>
            <?php endif; ?>
            
            <?php if (hasPermission('products.view')): ?>
            <a class="nav-link <?= $activeMenu === 'products' ? 'active' : '' ?>" href="/products">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <?php endif; ?>
            
            <?php if (hasPermission('categories.view')): ?>
            <a class="nav-link <?= $activeMenu === 'categories' ? 'active' : '' ?>" href="/categories">
                <i class="bi bi-tags"></i> Categories
            </a>
            <?php endif; ?>
        </nav>
    </div>

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
    </script>
    
    <?php if (isset($additionalJs)) echo $additionalJs; ?>
</body>
</html>
