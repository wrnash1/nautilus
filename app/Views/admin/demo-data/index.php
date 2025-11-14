<?php
$pageTitle = 'Demo Data Management';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Demo Data</li>
        </ol>
    </nav>

    <h1 class="h3"><i class="bi bi-database"></i> Demo Data Management</h1>
    <p class="text-muted">Load or clear sample data for testing</p>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_warning'])): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_warning']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_warning']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header <?= $demo_data_loaded ? 'bg-success text-white' : 'bg-primary text-white' ?>">
                <h5 class="card-title mb-0">
                    <i class="bi bi-box"></i> Demo Data Status
                </h5>
            </div>
            <div class="card-body">
                <?php if ($demo_data_loaded): ?>
                    <div class="alert alert-success">
                        <strong><i class="bi bi-check-circle"></i> Demo Data Loaded</strong>
                        <p class="mb-0 mt-2">Your database contains sample data for testing.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> No Demo Data</strong>
                        <p class="mb-0 mt-2">Load demo data to test the application with sample customers, products, and courses.</p>
                    </div>
                <?php endif; ?>

                <h6 class="mt-4 mb-3">Current Data Count:</h6>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-primary"><?= $counts['customers'] ?></h2>
                                <p class="mb-0">Customers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-success"><?= $counts['products'] ?></h2>
                                <p class="mb-0">Products</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h2 class="text-info"><?= $counts['courses'] ?></h2>
                                <p class="mb-0">Courses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <?php if (!$demo_data_loaded): ?>
                    <form method="POST" action="/store/admin/demo-data/load" onsubmit="return confirm('Load demo data? This will add sample customers, products, and courses to your database.')">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-download"></i> Load Demo Data
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/store/admin/demo-data/clear" onsubmit="return confirm('⚠️ Clear demo data? This will DELETE all sample customers, products, and courses. This cannot be undone!')">
                        <button type="submit" class="btn btn-danger btn-lg w-100">
                            <i class="bi bi-trash"></i> Clear Demo Data
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> What's Included?
                </h5>
            </div>
            <div class="card-body">
                <h6>Demo Data Includes:</h6>
                <ul class="mb-0">
                    <li><strong>8 Customers</strong>
                        <ul class="small text-muted">
                            <li>Various certification levels</li>
                            <li>Different customer tags</li>
                            <li>Contact information</li>
                        </ul>
                    </li>
                    <li class="mt-2"><strong>20 Products</strong>
                        <ul class="small text-muted">
                            <li>Regulators, BCDs, Wetsuits</li>
                            <li>Fins, Masks, Dive Computers</li>
                            <li>Realistic pricing</li>
                        </ul>
                    </li>
                    <li class="mt-2"><strong>5 Training Courses</strong>
                        <ul class="small text-muted">
                            <li>Open Water Diver</li>
                            <li>Advanced Open Water</li>
                            <li>Rescue Diver</li>
                            <li>Divemaster</li>
                            <li>Enriched Air Nitrox</li>
                        </ul>
                    </li>
                    <li class="mt-2"><strong>6 Product Categories</strong></li>
                    <li class="mt-2"><strong>8 Customer Tags</strong></li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightbulb"></i> When to Use
                </h5>
            </div>
            <div class="card-body">
                <h6>Load Demo Data For:</h6>
                <ul class="small">
                    <li>Testing features</li>
                    <li>Training staff</li>
                    <li>Demonstrations</li>
                    <li>Development</li>
                </ul>

                <h6 class="mt-3">Clear Demo Data Before:</h6>
                <ul class="small">
                    <li>Going live with real data</li>
                    <li>Customer-facing deployment</li>
                    <li>Production use</li>
                </ul>

                <div class="alert alert-warning small mt-3">
                    <strong>Note:</strong> Clearing demo data only removes the sample entries, not your real data.
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
