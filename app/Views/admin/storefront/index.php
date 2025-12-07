<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storefront Configuration - Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Include admin sidebar if you have one -->
            <div class="col-md-12">
                <div class="p-4">
                    <h1 class="mb-4">
                        <i class="bi bi-shop"></i> Storefront Configuration
                    </h1>

                    <p class="lead mb-4">
                        Configure your online store's appearance, features, and settings.
                    </p>

                    <!-- Quick Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Active Theme</h6>
                                    <h3><?= htmlspecialchars($activeTheme['theme_name'] ?? 'No Theme') ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Store Status</h6>
                                    <h3>Online</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Homepage Sections</h6>
                                    <h3><?= count($activeTheme ? [] : []) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h6 class="card-title">Settings</h6>
                                    <h3><?= count($allSettings) ?> categories</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Sections -->
                    <div class="row g-4">
                        <!-- Theme Designer -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary text-white rounded p-3 me-3">
                                            <i class="bi bi-palette fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Theme Designer</h5>
                                            <small class="text-muted">Colors, fonts & layout</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        Customize your store's visual appearance including colors, typography, and layout options.
                                    </p>
                                    <a href="/admin/storefront/theme-designer" class="btn btn-primary">
                                        <i class="bi bi-brush"></i> Design Theme
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Homepage Builder -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success text-white rounded p-3 me-3">
                                            <i class="bi bi-layout-text-window fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Homepage Builder</h5>
                                            <small class="text-muted">Sections & content</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        Build your homepage with drag-and-drop sections like hero, products, courses, and more.
                                    </p>
                                    <a href="/admin/storefront/homepage-builder" class="btn btn-success">
                                        <i class="bi bi-grid-3x3"></i> Build Homepage
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Store Settings -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-info text-white rounded p-3 me-3">
                                            <i class="bi bi-gear fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Store Settings</h5>
                                            <small class="text-muted">General configuration</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        Configure store name, contact info, SEO, features, and integrations.
                                    </p>
                                    <a href="/admin/storefront/settings" class="btn btn-info text-white">
                                        <i class="bi bi-sliders"></i> Manage Settings
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Manager -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning text-dark rounded p-3 me-3">
                                            <i class="bi bi-menu-button-wide fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Navigation Menus</h5>
                                            <small class="text-muted">Header & footer links</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        Customize your site's navigation menus, including header, footer, and mobile menus.
                                    </p>
                                    <a href="/admin/storefront/navigation" class="btn btn-warning">
                                        <i class="bi bi-list"></i> Edit Navigation
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Store -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-secondary text-white rounded p-3 me-3">
                                            <i class="bi bi-eye fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Preview Store</h5>
                                            <small class="text-muted">See customer view</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        Preview your online store as customers will see it before making changes live.
                                    </p>
                                    <a href="/" target="_blank" class="btn btn-secondary">
                                        <i class="bi bi-box-arrow-up-right"></i> View Store
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Assets & Media -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-danger text-white rounded p-3 me-3">
                                            <i class="bi bi-images fs-3"></i>
                                        </div>
                                        <div>
                                            <h5 class="card-title mb-0">Theme Assets</h5>
                                            <small class="text-muted">Logos & images</small>
                                        </div>
                                    </div>
                                    <p class="card-text">
                                        Upload and manage your store's logo, favicon, hero images, and other visual assets.
                                    </p>
                                    <a href="/admin/storefront/theme-designer#assets" class="btn btn-danger">
                                        <i class="bi bi-upload"></i> Manage Assets
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="btn-group" role="group">
                                <a href="/admin/storefront/preview" class="btn btn-outline-primary" target="_blank">
                                    <i class="bi bi-eye"></i> Preview Theme
                                </a>
                                <a href="/products" class="btn btn-outline-primary">
                                    <i class="bi bi-box"></i> Manage Products
                                </a>
                                <a href="/categories" class="btn btn-outline-primary">
                                    <i class="bi bi-tags"></i> Manage Categories
                                </a>
                                <a href="/orders" class="btn btn-outline-primary">
                                    <i class="bi bi-cart"></i> View Orders
                                </a>
                                <a href="/admin" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Categories Overview -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-list-check"></i> Settings Categories</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($categories as $key => $category): ?>
                                <div class="col-md-6 col-lg-3">
                                    <a href="/admin/storefront/settings?category=<?= htmlspecialchars($key) ?>"
                                       class="text-decoration-none">
                                        <div class="border rounded p-3 h-100 hover-shadow">
                                            <div class="d-flex align-items-center">
                                                <i class="<?= htmlspecialchars($category['icon']) ?> fs-4 me-2 text-primary"></i>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($category['name']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($category['description']) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hover-shadow {
            transition: box-shadow 0.2s ease;
        }
        .hover-shadow:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</body>
</html>
