<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nautilus Installation - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .install-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .install-body {
            padding: 40px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list i {
            color: #0d6efd;
            margin-right: 10px;
        }
        .requirement {
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            margin-bottom: 10px;
        }
        .requirement.met {
            border-left-color: #198754;
        }
        .requirement.not-met {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <i class="bi bi-water" style="font-size: 4rem;"></i>
                <h1>Nautilus Dive Shop</h1>
                <p class="lead mb-0">Welcome to the Installation Wizard</p>
            </div>

            <div class="install-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    This wizard will guide you through the installation process for Nautilus,
                    a comprehensive dive shop management system.
                </div>

                <h3 class="mb-3">Key Features</h3>
                <ul class="feature-list mb-4">
                    <li><i class="bi bi-check-circle-fill"></i> Point of Sale (POS) System</li>
                    <li><i class="bi bi-check-circle-fill"></i> Customer Relationship Management (CRM)</li>
                    <li><i class="bi bi-check-circle-fill"></i> Inventory & Product Management</li>
                    <li><i class="bi bi-check-circle-fill"></i> Equipment Rental System</li>
                    <li><i class="bi bi-check-circle-fill"></i> Dive Course & Trip Management</li>
                    <li><i class="bi bi-check-circle-fill"></i> Work Order Tracking</li>
                    <li><i class="bi bi-check-circle-fill"></i> E-Commerce Integration</li>
                    <li><i class="bi bi-check-circle-fill"></i> Marketing & Loyalty Programs</li>
                    <li><i class="bi bi-check-circle-fill"></i> Staff Management & Reporting</li>
                </ul>

                <h3 class="mb-3">System Requirements</h3>
                <div class="requirement met">
                    <strong><i class="bi bi-check-circle-fill text-success"></i> PHP Version:</strong>
                    PHP <?= PHP_VERSION ?> (Requires PHP 8.2+)
                </div>
                <div class="requirement <?= extension_loaded('pdo_mysql') ? 'met' : 'not-met' ?>">
                    <strong>
                        <i class="bi bi-<?= extension_loaded('pdo_mysql') ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                        PDO MySQL Extension:
                    </strong>
                    <?= extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed' ?>
                </div>
                <div class="requirement <?= extension_loaded('mbstring') ? 'met' : 'not-met' ?>">
                    <strong>
                        <i class="bi bi-<?= extension_loaded('mbstring') ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                        mbstring Extension:
                    </strong>
                    <?= extension_loaded('mbstring') ? 'Installed' : 'Not Installed' ?>
                </div>
                <div class="requirement <?= is_writable(__DIR__ . '/../../../storage') ? 'met' : 'not-met' ?>">
                    <strong>
                        <i class="bi bi-<?= is_writable(__DIR__ . '/../../../storage') ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                        Storage Directory:
                    </strong>
                    <?= is_writable(__DIR__ . '/../../../storage') ? 'Writable' : 'Not Writable' ?>
                </div>
                <div class="requirement <?= is_writable(__DIR__ . '/../../../.env') || is_writable(__DIR__ . '/../../..') ? 'met' : 'not-met' ?>">
                    <strong>
                        <i class="bi bi-<?= is_writable(__DIR__ . '/../../../.env') || is_writable(__DIR__ . '/../../..') ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?>"></i>
                        .env File:
                    </strong>
                    <?= is_writable(__DIR__ . '/../../../.env') || is_writable(__DIR__ . '/../../..') ? 'Writable' : 'Not Writable' ?>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="/install/configure" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-right-circle"></i> Begin Installation
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center text-white mt-3">
            <small>Nautilus v6.0.0 | Powered by PHP <?= PHP_VERSION ?></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
