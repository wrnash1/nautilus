<?php
$pageTitle = 'Wave Apps Integration';
$activeMenu = 'settings';

ob_start();
?>

<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/admin/settings">Settings</a></li>
            <li class="breadcrumb-item active">Wave Apps Integration</li>
        </ol>
    </nav>

    <h1 class="h3">
        <i class="bi bi-cloud-upload"></i> Wave Apps Integration
    </h1>
    <p class="text-muted">Sync your transactions and customers with Wave Accounting</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Connection Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-plug"></i> Connection Status</h5>
            </div>
            <div class="card-body">
                <div id="connectionStatus" class="mb-3">
                    <span class="badge bg-secondary">Not tested</span>
                </div>

                <button type="button" class="btn btn-primary" onclick="testConnection()">
                    <i class="bi bi-arrow-repeat"></i> Test Connection
                </button>

                <a href="/admin/settings/integrations" class="btn btn-secondary">
                    <i class="bi bi-gear"></i> Configure Credentials
                </a>
            </div>
        </div>

        <!-- Bulk Sync -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-upload"></i> Bulk Sync to Wave</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/integrations/wave/bulk-sync" onsubmit="return confirm('Sync transactions to Wave?')">
                    <p class="text-muted">Sync all completed transactions within a date range to Wave as invoices.</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" name="date_from" id="date_from"
                                   class="form-control" value="<?= date('Y-m-01') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" name="date_to" id="date_to"
                                   class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-cloud-upload"></i> Sync to Wave
                    </button>
                </form>
            </div>
        </div>

        <!-- CSV Export -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-file-earmark-spreadsheet"></i> Manual CSV Export</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Download transactions in Wave CSV format for manual import.</p>

                <form method="GET" action="/integrations/wave/export-csv">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="<?= date('Y-m-01') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-download"></i> Download CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Setup Guide -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Setup Guide</h6>
            </div>
            <div class="card-body small">
                <h6>1. Get Wave API Token</h6>
                <ol>
                    <li>Log in to <a href="https://waveapps.com" target="_blank">Wave Apps</a></li>
                    <li>Go to Settings → Integrations</li>
                    <li>Create API Application</li>
                    <li>Copy Access Token</li>
                </ol>

                <h6 class="mt-3">2. Find Business ID</h6>
                <ol>
                    <li>In Wave, go to your Business</li>
                    <li>Check URL: <code>waveapps.com/business/&lt;ID&gt;</code></li>
                    <li>Copy the Business ID</li>
                </ol>

                <h6 class="mt-3">3. Configure Nautilus</h6>
                <ol>
                    <li>Go to Settings → Integrations</li>
                    <li>Enter Access Token and Business ID</li>
                    <li>Save settings</li>
                    <li>Come back here and test connection</li>
                </ol>
            </div>
        </div>

        <!-- Features -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-check-circle"></i> Features</h6>
            </div>
            <div class="card-body small">
                <ul class="mb-0">
                    <li>Automatic customer creation</li>
                    <li>Transaction to invoice sync</li>
                    <li>Bulk sync by date range</li>
                    <li>CSV export for manual import</li>
                    <li>Prevent duplicate syncs</li>
                    <li>Audit trail of all syncs</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    const statusDiv = document.getElementById('connectionStatus');
    statusDiv.innerHTML = '<span class="badge bg-warning">Testing...</span>';

    fetch('/integrations/wave/test-connection')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> <strong>Connected!</strong><br>
                        <small>Business: ${data.business.name} (${data.business.currency.code})</small>
                    </div>
                `;
            } else {
                statusDiv.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-x-circle"></i> <strong>Connection Failed</strong><br>
                        <small>${data.error}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            statusDiv.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <i class="bi bi-x-circle"></i> <strong>Error:</strong> ${error.message}
                </div>
            `;
        });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
?>
