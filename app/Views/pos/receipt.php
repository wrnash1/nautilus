<?php
$pageTitle = 'Receipt';
$activeMenu = 'pos';
$user = currentUser();

// Get company branding settings
use App\Services\Admin\SettingsService;
$settingsService = new SettingsService();
$brandingSettings = $settingsService->getSettingsByCategory('general');

ob_start();
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-receipt"></i> Receipt #<?= $transaction['id'] ?>
            </h1>
            <div>
                <a href="/pos" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to POS
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card" id="receipt">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <?php if (!empty($brandingSettings['company_logo_path'])): ?>
                        <img src="<?= htmlspecialchars($brandingSettings['company_logo_path']) ?>"
                             alt="<?= htmlspecialchars($brandingSettings['business_name'] ?? 'Company Logo') ?>"
                             style="max-width: <?= intval($brandingSettings['invoice_logo_width'] ?? 200) ?>px; height: auto; margin-bottom: 1rem;">
                    <?php else: ?>
                        <h2>
                            <i class="bi bi-water" style="color: <?= htmlspecialchars($brandingSettings['brand_primary_color'] ?? '#0066CC') ?>"></i>
                            <?= htmlspecialchars($brandingSettings['business_name'] ?? 'Nautilus Dive Shop') ?>
                        </h2>
                    <?php endif; ?>

                    <?php if (!empty($brandingSettings['company_tagline'])): ?>
                        <p class="text-muted fst-italic mb-2"><?= htmlspecialchars($brandingSettings['company_tagline']) ?></p>
                    <?php endif; ?>

                    <?php
                    $address = trim($brandingSettings['business_address'] ?? '');
                    $city = trim($brandingSettings['business_city'] ?? '');
                    $state = trim($brandingSettings['business_state'] ?? '');
                    $zip = trim($brandingSettings['business_zip'] ?? '');
                    ?>

                    <?php if ($address): ?>
                        <p class="mb-0"><?= htmlspecialchars($address) ?></p>
                    <?php endif; ?>

                    <?php if ($city || $state || $zip): ?>
                        <p class="mb-0">
                            <?= htmlspecialchars($city) ?>
                            <?= $city && ($state || $zip) ? ', ' : '' ?>
                            <?= htmlspecialchars($state) ?>
                            <?= $zip ? ' ' . htmlspecialchars($zip) : '' ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($brandingSettings['business_phone'])): ?>
                        <p class="mb-0">Phone: <?= htmlspecialchars($brandingSettings['business_phone']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($brandingSettings['business_email'])): ?>
                        <p>Email: <?= htmlspecialchars($brandingSettings['business_email']) ?></p>
                    <?php endif; ?>
                </div>
                
                <hr>
                
                <div class="row mb-4">
                    <div class="col-6">
                        <h5>Transaction Details</h5>
                        <p class="mb-1"><strong>Receipt #:</strong> <?= $transaction['id'] ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?= date('M d, Y g:i A', strtotime($transaction['created_at'])) ?></p>
                        <p class="mb-1"><strong>Cashier:</strong> <?= htmlspecialchars($transaction['cashier_name']) ?></p>
                        <p class="mb-1"><strong>Status:</strong> 
                            <span class="badge bg-success"><?= ucfirst($transaction['status']) ?></span>
                        </p>
                    </div>
                    <div class="col-6">
                        <h5>Customer</h5>
                        <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']) ?></p>
                        <?php if ($transaction['email']): ?>
                        <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($transaction['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h5 class="mb-3">Items Purchased</h5>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><?= htmlspecialchars($item['sku']) ?></td>
                            <td class="text-end"><?= formatCurrency($item['unit_price']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end"><?= formatCurrency($item['total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end"><?= formatCurrency($transaction['subtotal']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Tax (8%):</strong></td>
                            <td class="text-end"><?= formatCurrency($transaction['tax']) ?></td>
                        </tr>
                        <tr class="table-primary">
                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong><?= formatCurrency($transaction['total']) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="mb-1"><strong>Thank you for your business!</strong></p>
                    <p class="text-muted">Visit us at www.nautilusdive.com</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '<style>
@media print {
    .navbar, .sidebar, .btn, .no-print {
        display: none !important;
    }
    
    #receipt {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>';

require __DIR__ . '/../layouts/app.php';
?>
