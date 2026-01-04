<?php
$pageTitle = 'QuickBooks Integration';
$activeMenu = 'integrations';

ob_start();
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-receipt-cutoff"></i> QuickBooks Integration
            </h1>
            <div>
                <a href="/integrations/quickbooks/export" class="btn btn-primary">
                    <i class="bi bi-download"></i> Export Data
                </a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row">
    <!-- Configuration Form -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Export Configuration
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/integrations/quickbooks/config">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">

                    <div class="mb-3">
                        <label for="companyName" class="form-label">Company Name</label>
                        <input type="text" class="form-control" id="companyName" name="company_name"
                               value="<?= htmlspecialchars($config['company_name'] ?? '') ?>"
                               placeholder="Your Company Name">
                        <small class="text-muted">This will appear in QuickBooks export files</small>
                    </div>

                    <div class="mb-3">
                        <label for="format" class="form-label">Export Format</label>
                        <select class="form-select" id="format" name="format">
                            <option value="iif" <?= ($config['format'] ?? 'iif') === 'iif' ? 'selected' : '' ?>>
                                IIF - QuickBooks Desktop
                            </option>
                            <option value="qbo" <?= ($config['format'] ?? 'iif') === 'qbo' ? 'selected' : '' ?>>
                                QBO - QuickBooks Online
                            </option>
                        </select>
                        <small class="text-muted">
                            Choose IIF for QuickBooks Desktop or QBO for QuickBooks Online
                        </small>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Account Mappings</h6>
                    <p class="text-muted small">Map Nautilus accounts to your QuickBooks chart of accounts</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="revenueAccount" class="form-label">Revenue Account</label>
                            <input type="text" class="form-control" id="revenueAccount" name="revenue_account"
                                   value="<?= htmlspecialchars($config['account_mappings']['revenue_account'] ?? 'Sales') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="cogsAccount" class="form-label">Cost of Goods Sold Account</label>
                            <input type="text" class="form-control" id="cogsAccount" name="cogs_account"
                                   value="<?= htmlspecialchars($config['account_mappings']['cogs_account'] ?? 'Cost of Goods Sold') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="inventoryAssetAccount" class="form-label">Inventory Asset Account</label>
                            <input type="text" class="form-control" id="inventoryAssetAccount" name="inventory_asset_account"
                                   value="<?= htmlspecialchars($config['account_mappings']['inventory_asset_account'] ?? 'Inventory Asset') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="salesTaxAccount" class="form-label">Sales Tax Payable Account</label>
                            <input type="text" class="form-control" id="salesTaxAccount" name="sales_tax_account"
                                   value="<?= htmlspecialchars($config['account_mappings']['sales_tax_account'] ?? 'Sales Tax Payable') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="accountsReceivable" class="form-label">Accounts Receivable</label>
                            <input type="text" class="form-control" id="accountsReceivable" name="accounts_receivable"
                                   value="<?= htmlspecialchars($config['account_mappings']['accounts_receivable'] ?? 'Accounts Receivable') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="depositToAccount" class="form-label">Deposit To Account</label>
                            <input type="text" class="form-control" id="depositToAccount" name="deposit_to_account"
                                   value="<?= htmlspecialchars($config['account_mappings']['deposit_to_account'] ?? 'Undeposited Funds') ?>">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Export Options</h6>

                    <div class="mb-3">
                        <label for="taxRate" class="form-label">Default Tax Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" id="taxRate" name="tax_rate"
                               value="<?= htmlspecialchars($config['tax_rate'] ?? 8.0) ?>">
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="includeCustomers" name="include_customers"
                               <?= ($config['include_customers'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="includeCustomers">
                            Include Customers
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="includeProducts" name="include_products"
                               <?= ($config['include_products'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="includeProducts">
                            Include Products/Inventory Items
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="includeInvoices" name="include_invoices"
                               <?= ($config['include_invoices'] ?? true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="includeInvoices">
                            Include Sales Receipts/Invoices
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Sidebar -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> About QuickBooks Export
                </h6>
            </div>
            <div class="card-body">
                <h6>File Formats</h6>
                <p class="small">
                    <strong>IIF (Intuit Interchange Format)</strong><br>
                    Tab-delimited text file compatible with QuickBooks Desktop.
                    Import via: File → Utilities → Import → IIF Files
                </p>
                <p class="small">
                    <strong>QBO (QuickBooks Online)</strong><br>
                    XML format for QuickBooks Online.
                    Import via: Settings → Import Data → Bank Data
                </p>

                <hr>

                <h6>What Gets Exported</h6>
                <ul class="small mb-0">
                    <li>Customer information</li>
                    <li>Product/inventory items</li>
                    <li>Sales receipts with line items</li>
                    <li>Sales tax calculations</li>
                    <li>Payment methods</li>
                </ul>

                <hr>

                <h6>Best Practices</h6>
                <ul class="small mb-0">
                    <li>Export in date ranges (monthly recommended)</li>
                    <li>Verify account names match your QuickBooks chart of accounts</li>
                    <li>Backup QuickBooks before importing</li>
                    <li>Review imported data for accuracy</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-clock-history"></i> Export History
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($exportHistory)): ?>
                    <p class="text-muted small text-center mb-0">No exports yet</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($exportHistory as $export): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <small class="d-block">
                                            <i class="bi bi-file-earmark-<?= $export['format'] === 'iif' ? 'text' : 'code' ?>"></i>
                                            <?= htmlspecialchars($export['filename']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <?= date('M d, Y g:i A', strtotime($export['created_at'])) ?>
                                        </small>
                                        <?php if ($export['start_date'] || $export['end_date']): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= $export['start_date'] ? date('M d, Y', strtotime($export['start_date'])) : 'All' ?>
                                                →
                                                <?= $export['end_date'] ? date('M d, Y', strtotime($export['end_date'])) : 'All' ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <form method="POST" action="/integrations/quickbooks/delete/<?= $export['id'] ?>" class="ms-2">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this export file?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
