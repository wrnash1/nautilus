<?php
$pageTitle = 'Integrations & AI Settings';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/store">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/store/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Integrations</li>
        </ol>
    </nav>

    <h1 class="h3"><i class="bi bi-plugin"></i> Integrations & AI Configuration</h1>
    <p class="text-muted">Configure third-party integrations and AI features</p>
</div>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<form method="POST" action="/store/admin/settings/update-integrations">
    <div class="row">
        <div class="col-lg-8">

            <!-- Wave Apps Integration -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cloud"></i> Wave Apps (Accounting)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Connect to Wave Apps to automatically sync invoices, customers, and payments.
                    </p>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="wave_enabled" class="form-check-input" id="wave_enabled"
                                   value="true" <?= ($settings['integrations']['wave_enabled']['setting_value'] ?? '') === 'true' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="wave_enabled">
                                <strong>Enable Wave Apps Integration</strong>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="wave_business_id" class="form-label">
                            <strong>Wave Business ID</strong>
                        </label>
                        <input type="text" name="wave_business_id" id="wave_business_id"
                               class="form-control"
                               value="<?= htmlspecialchars($settings['integrations']['wave_business_id']['setting_value'] ?? '') ?>"
                               placeholder="e.g., QnVzaW5lc3M6NWNhYjZlZmEtOWY5ZC00">
                        <small class="text-muted">
                            Found in your Wave business URL or API settings
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="wave_api_token" class="form-label">
                            <strong>Wave API Access Token</strong>
                        </label>
                        <input type="password" name="wave_api_token" id="wave_api_token"
                               class="form-control"
                               value="<?= htmlspecialchars($settings['integrations']['wave_api_token']['setting_value'] ?? '') ?>"
                               placeholder="Enter your Wave API token">
                        <small class="text-muted">
                            Get this from Wave Settings → Integrations → API Access Token
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> How to get Wave API credentials:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Log in to <a href="https://www.waveapps.com" target="_blank">Wave Apps</a></li>
                            <li>Go to Settings → Integrations</li>
                            <li>Create a new API Access Token</li>
                            <li>Copy your Business ID from the URL or API settings</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- AI Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-robot"></i> AI Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Enable AI-powered features like product recommendations, inventory forecasting, and customer insights.
                    </p>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="ai_enabled" class="form-check-input" id="ai_enabled"
                                   value="true" <?= ($settings['ai']['ai_enabled']['setting_value'] ?? '') === 'true' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ai_enabled">
                                <strong>Enable AI Features</strong>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ai_provider" class="form-label">
                            <strong>AI Provider</strong>
                        </label>
                        <select name="ai_provider" id="ai_provider" class="form-select">
                            <?php $currentProvider = $settings['ai']['ai_provider']['setting_value'] ?? 'local'; ?>
                            <option value="local" <?= $currentProvider === 'local' ? 'selected' : '' ?>>
                                Local AI Model (Self-hosted)
                            </option>
                            <option value="openai" <?= $currentProvider === 'openai' ? 'selected' : '' ?>>
                                OpenAI (GPT-4, GPT-3.5)
                            </option>
                            <option value="anthropic" <?= $currentProvider === 'anthropic' ? 'selected' : '' ?>>
                                Anthropic (Claude)
                            </option>
                        </select>
                    </div>

                    <!-- Local AI Settings -->
                    <div id="local-ai-settings" class="ai-provider-settings">
                        <div class="mb-3">
                            <label for="ai_model_path" class="form-label">
                                <strong>Local AI Model Directory</strong>
                            </label>
                            <input type="text" name="ai_model_path" id="ai_model_path"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['ai']['ai_model_path']['setting_value'] ?? '/opt/models') ?>"
                                   placeholder="/opt/models">
                            <small class="text-muted">
                                Path to directory containing local AI models (LLaMA, Mistral, etc.)
                            </small>
                        </div>
                    </div>

                    <!-- OpenAI Settings -->
                    <div id="openai-settings" class="ai-provider-settings" style="display: none;">
                        <div class="mb-3">
                            <label for="openai_api_key" class="form-label">
                                <strong>OpenAI API Key</strong>
                            </label>
                            <input type="password" name="openai_api_key" id="openai_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['ai']['openai_api_key']['setting_value'] ?? '') ?>"
                                   placeholder="sk-...">
                            <small class="text-muted">
                                Get from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Dashboard</a>
                            </small>
                        </div>
                    </div>

                    <!-- Anthropic Settings -->
                    <div id="anthropic-settings" class="ai-provider-settings" style="display: none;">
                        <div class="mb-3">
                            <label for="anthropic_api_key" class="form-label">
                                <strong>Anthropic API Key</strong>
                            </label>
                            <input type="password" name="anthropic_api_key" id="anthropic_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['ai']['anthropic_api_key']['setting_value'] ?? '') ?>"
                                   placeholder="sk-ant-...">
                            <small class="text-muted">
                                Get from <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a>
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <strong><i class="bi bi-exclamation-triangle"></i> Note:</strong>
                        AI features require additional setup. For local AI, you need to install and configure AI models.
                        For cloud providers (OpenAI/Anthropic), API usage will incur costs.
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Save Integration Settings
                </button>
            </div>

        </div>

        <div class="col-lg-4">
            <!-- Help & Documentation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-question-circle"></i> Integration Help
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Wave Apps</h6>
                    <p class="small text-muted">
                        Automatically sync your transactions to Wave for accounting and invoicing.
                    </p>

                    <h6 class="mt-3">AI Features</h6>
                    <p class="small text-muted">
                        Enable intelligent product recommendations, inventory forecasting, and customer behavior analysis.
                    </p>

                    <h6 class="mt-3">Local vs Cloud AI</h6>
                    <ul class="small text-muted">
                        <li><strong>Local:</strong> Free, private, requires GPU</li>
                        <li><strong>OpenAI:</strong> Fast, reliable, pay-per-use</li>
                        <li><strong>Anthropic:</strong> Advanced reasoning, pay-per-use</li>
                    </ul>
                </div>
            </div>

            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle"></i> Integration Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Wave Apps</span>
                        <?php if (!empty($settings['integrations']['wave_enabled']['setting_value']) && $settings['integrations']['wave_enabled']['setting_value'] === 'true'): ?>
                            <span class="badge bg-success">Connected</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Disabled</span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>AI Features</span>
                        <?php if (!empty($settings['ai']['ai_enabled']['setting_value']) && $settings['ai']['ai_enabled']['setting_value'] === 'true'): ?>
                            <span class="badge bg-success">Enabled</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Disabled</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Show/hide AI provider settings based on selection
document.getElementById('ai_provider').addEventListener('change', function() {
    document.querySelectorAll('.ai-provider-settings').forEach(el => el.style.display = 'none');

    const provider = this.value;
    if (provider === 'local') {
        document.getElementById('local-ai-settings').style.display = 'block';
    } else if (provider === 'openai') {
        document.getElementById('openai-settings').style.display = 'block';
    } else if (provider === 'anthropic') {
        document.getElementById('anthropic-settings').style.display = 'block';
    }
});

// Trigger on page load
document.getElementById('ai_provider').dispatchEvent(new Event('change'));
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../../layouts/app.php';
?>
