<?php
$pageTitle = 'Payment Settings';
$activeMenu = 'settings';

// Include secure input component
require __DIR__ . '/../../components/secure-input.php';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Payment Settings</li>
        </ol>
    </nav>

    <h1 class="h3"><i class="bi bi-credit-card"></i> Payment Gateway Configuration</h1>
    <p class="text-muted">Configure payment processors and accepted payment methods</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Stripe Configuration -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-stripe"></i> Stripe Payment Gateway
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update" id="stripeForm">
                    <input type="hidden" name="category" value="payment">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="settings[stripe_enabled]" class="form-check-input" id="stripe_enabled"
                               <?= !empty($settings['stripe_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="stripe_enabled">
                            <strong>Enable Stripe Payments</strong>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="stripe_publishable_key" class="form-label">Stripe Publishable Key</label>
                        <input type="text" name="settings[stripe_publishable_key]" id="stripe_publishable_key"
                               class="form-control font-monospace"
                               value="<?= htmlspecialchars($settings['stripe_publishable_key'] ?? '') ?>"
                               placeholder="pk_live_...">
                        <small class="text-muted">Public key - safe to expose in client-side code</small>
                    </div>

                    <?= renderSecureInput(
                        'stripe_secret_key',
                        'Stripe Secret Key',
                        $settings['stripe_secret_key'] ?? '',
                        'sk_live_...',
                        'Keep this secret! Never expose in client-side code.'
                    ) ?>

                    <?= renderSecureInput(
                        'stripe_webhook_secret',
                        'Stripe Webhook Secret',
                        $settings['stripe_webhook_secret'] ?? '',
                        'whsec_...',
                        'Used to verify webhook signatures from Stripe'
                    ) ?>

                    <div class="mb-3">
                        <label for="stripe_currency" class="form-label">Currency</label>
                        <select name="settings[stripe_currency]" id="stripe_currency" class="form-select">
                            <option value="usd" <?= ($settings['stripe_currency'] ?? 'usd') === 'usd' ? 'selected' : '' ?>>USD - US Dollar</option>
                            <option value="eur" <?= ($settings['stripe_currency'] ?? '') === 'eur' ? 'selected' : '' ?>>EUR - Euro</option>
                            <option value="gbp" <?= ($settings['stripe_currency'] ?? '') === 'gbp' ? 'selected' : '' ?>>GBP - British Pound</option>
                            <option value="cad" <?= ($settings['stripe_currency'] ?? '') === 'cad' ? 'selected' : '' ?>>CAD - Canadian Dollar</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Stripe Settings
                    </button>
                    <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right"></i> Get API Keys
                    </a>
                </form>
            </div>
        </div>

        <!-- Square Configuration -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-square"></i> Square Payment Gateway
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update" id="squareForm">
                    <input type="hidden" name="category" value="payment">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="settings[square_enabled]" class="form-check-input" id="square_enabled"
                               <?= !empty($settings['square_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="square_enabled">
                            <strong>Enable Square Payments</strong>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="square_application_id" class="form-label">Square Application ID</label>
                        <input type="text" name="settings[square_application_id]" id="square_application_id"
                               class="form-control font-monospace"
                               value="<?= htmlspecialchars($settings['square_application_id'] ?? '') ?>"
                               placeholder="sq0idp-...">
                        <small class="text-muted">Application ID from Square Developer Dashboard</small>
                    </div>

                    <?= renderSecureInput(
                        'square_access_token',
                        'Square Access Token',
                        $settings['square_access_token'] ?? '',
                        'EAAAl...',
                        'Personal access token - keep this secret!'
                    ) ?>

                    <div class="mb-3">
                        <label for="square_location_id" class="form-label">Location ID</label>
                        <input type="text" name="settings[square_location_id]" id="square_location_id"
                               class="form-control font-monospace"
                               value="<?= htmlspecialchars($settings['square_location_id'] ?? '') ?>"
                               placeholder="LH...">
                        <small class="text-muted">Your Square business location ID</small>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Save Square Settings
                    </button>
                    <a href="https://developer.squareup.com/apps" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right"></i> Square Developer Portal
                    </a>
                </form>
            </div>
        </div>

        <!-- BTCPay Server (Cryptocurrency) -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-currency-bitcoin"></i> BTCPay Server (Cryptocurrency)
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update" id="btcpayForm">
                    <input type="hidden" name="category" value="payment">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" name="settings[btcpay_enabled]" class="form-check-input" id="btcpay_enabled"
                               <?= !empty($settings['btcpay_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="btcpay_enabled">
                            <strong>Enable Cryptocurrency Payments (Bitcoin, Lightning)</strong>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="btcpay_server_url" class="form-label">BTCPay Server URL</label>
                        <input type="url" name="settings[btcpay_server_url]" id="btcpay_server_url"
                               class="form-control"
                               value="<?= htmlspecialchars($settings['btcpay_server_url'] ?? '') ?>"
                               placeholder="https://your-btcpay-server.com">
                    </div>

                    <div class="mb-3">
                        <label for="btcpay_store_id" class="form-label">Store ID</label>
                        <input type="text" name="settings[btcpay_store_id]" id="btcpay_store_id"
                               class="form-control font-monospace"
                               value="<?= htmlspecialchars($settings['btcpay_store_id'] ?? '') ?>"
                               placeholder="4g6...">
                    </div>

                    <?= renderSecureInput(
                        'btcpay_api_key',
                        'BTCPay API Key',
                        $settings['btcpay_api_key'] ?? '',
                        'API Key',
                        'API key from BTCPay Server → Store Settings → Access Tokens'
                    ) ?>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Save BTCPay Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Accepted Payment Methods</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/settings/update">
                    <input type="hidden" name="category" value="payment">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="form-check mb-3">
                        <input type="checkbox" name="settings[cash_enabled]" class="form-check-input" id="cash_enabled"
                               <?= !empty($settings['cash_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cash_enabled">
                            <strong>Cash</strong> - Accept cash payments
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="settings[check_enabled]" class="form-check-input" id="check_enabled"
                               <?= !empty($settings['check_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="check_enabled">
                            <strong>Check</strong> - Accept check payments
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="settings[credit_card_enabled]" class="form-check-input" id="credit_card_enabled"
                               <?= !empty($settings['credit_card_enabled']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="credit_card_enabled">
                            <strong>Credit/Debit Card</strong> - Manual card entry (requires Stripe or Square)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Payment Methods
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Security Notice -->
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-shield-exclamation"></i> Security Notice</h6>
            </div>
            <div class="card-body small">
                <p><strong>API keys are sensitive credentials:</strong></p>
                <ul class="mb-0">
                    <li>Never share secret keys publicly</li>
                    <li>Use test keys during development</li>
                    <li>All secret keys are encrypted in the database</li>
                    <li>Only administrators can view/edit these settings</li>
                    <li>Changes are logged for security auditing</li>
                </ul>
            </div>
        </div>

        <!-- PCI Compliance Info -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> PCI Compliance</h6>
            </div>
            <div class="card-body small">
                <p>When using Stripe or Square:</p>
                <ul>
                    <li>Card data never touches your server</li>
                    <li>Tokenized payments reduce PCI scope</li>
                    <li>Use official SDKs for card processing</li>
                    <li>Never log full card numbers</li>
                </ul>
                <a href="https://stripe.com/docs/security" target="_blank" class="btn btn-sm btn-outline-info">
                    Learn More <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
        </div>

        <!-- Test Mode Warning -->
        <?php if (($_ENV['APP_ENV'] ?? 'production') !== 'production'): ?>
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Test Mode Active</h6>
            </div>
            <div class="card-body small">
                <p>You're in <strong><?= htmlspecialchars($_ENV['APP_ENV'] ?? 'development') ?></strong> environment.</p>
                <p class="mb-0">Use test API keys. Never use production keys in development!</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
