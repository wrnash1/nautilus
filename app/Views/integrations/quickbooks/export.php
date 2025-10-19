<?php
$pageTitle = 'Export to QuickBooks';
$activeMenu = 'integrations';

ob_start();
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-download"></i> Export to QuickBooks
            </h1>
            <div>
                <a href="/integrations/quickbooks" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Configuration
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
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-calendar-range"></i> Select Date Range
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/integrations/quickbooks/download" id="exportForm">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">

                    <div class="mb-4">
                        <label class="form-label">Quick Date Ranges</label>
                        <div class="btn-group-vertical w-100" role="group">
                            <?php foreach ($dateRanges as $key => $range): ?>
                                <button type="button" class="btn btn-outline-primary text-start quick-range-btn"
                                        data-start="<?= htmlspecialchars($range['start']) ?>"
                                        data-end="<?= htmlspecialchars($range['end']) ?>">
                                    <i class="bi bi-calendar"></i> <?= htmlspecialchars($range['label']) ?>
                                    <?php if ($range['start'] && $range['end']): ?>
                                        <small class="text-muted ms-2">
                                            (<?= date('M d', strtotime($range['start'])) ?> - <?= date('M d, Y', strtotime($range['end'])) ?>)
                                        </small>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Custom Date Range</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="start_date">
                            <small class="text-muted">Leave blank for all dates</small>
                        </div>

                        <div class="col-md-6">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="end_date">
                            <small class="text-muted">Leave blank for all dates</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Export Format</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="iif" <?= ($config['format'] ?? 'iif') === 'iif' ? 'selected' : '' ?>>
                                IIF - QuickBooks Desktop
                            </option>
                            <option value="qbo" <?= ($config['format'] ?? 'iif') === 'qbo' ? 'selected' : '' ?>>
                                QBO - QuickBooks Online
                            </option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-info" id="previewBtn">
                            <i class="bi bi-eye"></i> Preview Export
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="exportBtn">
                            <i class="bi bi-download"></i> Generate & Download Export File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Preview Summary Card -->
        <div class="card mb-4" id="previewCard" style="display: none;">
            <div class="card-header bg-success text-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-check-circle"></i> Export Preview
                </h6>
            </div>
            <div class="card-body">
                <div id="previewContent">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Customers:</span>
                        <strong id="previewCustomers">0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Products:</span>
                        <strong id="previewProducts">0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Invoices:</span>
                        <strong id="previewInvoices">0</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Total Revenue:</span>
                        <strong class="text-success" id="previewRevenue">$0.00</strong>
                    </div>
                </div>

                <div id="previewLoading" style="display: none;" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="small text-muted mt-2 mb-0">Loading preview...</p>
                </div>

                <div id="previewError" style="display: none;" class="alert alert-danger small mb-0">
                    Failed to load preview
                </div>
            </div>
        </div>

        <!-- Current Configuration Card -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Current Configuration
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-6">Company:</dt>
                    <dd class="col-sm-6"><?= htmlspecialchars($config['company_name'] ?: 'Not set') ?></dd>

                    <dt class="col-sm-6">Format:</dt>
                    <dd class="col-sm-6"><?= strtoupper($config['format'] ?? 'IIF') ?></dd>

                    <dt class="col-sm-6">Tax Rate:</dt>
                    <dd class="col-sm-6"><?= htmlspecialchars($config['tax_rate'] ?? 8) ?>%</dd>

                    <dt class="col-sm-12 mt-2 mb-1">Exporting:</dt>
                    <dd class="col-sm-12">
                        <?php if ($config['include_customers'] ?? true): ?>
                            <span class="badge bg-success">Customers</span>
                        <?php endif; ?>
                        <?php if ($config['include_products'] ?? true): ?>
                            <span class="badge bg-success">Products</span>
                        <?php endif; ?>
                        <?php if ($config['include_invoices'] ?? true): ?>
                            <span class="badge bg-success">Invoices</span>
                        <?php endif; ?>
                    </dd>
                </dl>

                <hr>

                <div class="d-grid">
                    <a href="/integrations/quickbooks" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit Configuration
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$additionalJs = '<script>
const csrfToken = "' . \App\Middleware\CsrfMiddleware::generateToken() . '";

$(document).ready(function() {
    // Quick range button clicks
    $(".quick-range-btn").on("click", function() {
        const startDate = $(this).data("start");
        const endDate = $(this).data("end");

        $("#startDate").val(startDate);
        $("#endDate").val(endDate);

        // Highlight selected button
        $(".quick-range-btn").removeClass("active");
        $(this).addClass("active");

        // Auto-preview
        loadPreview();
    });

    // Preview button click
    $("#previewBtn").on("click", function() {
        loadPreview();
    });

    // Date changes
    $("#startDate, #endDate").on("change", function() {
        $(".quick-range-btn").removeClass("active");
    });

    // Export form submit
    $("#exportForm").on("submit", function() {
        const $btn = $("#exportBtn");
        $btn.prop("disabled", true)
            .html("<span class=\"spinner-border spinner-border-sm\"></span> Generating...");

        // Form will submit normally, re-enable after delay
        setTimeout(() => {
            $btn.prop("disabled", false)
                .html("<i class=\"bi bi-download\"></i> Generate & Download Export File");
        }, 3000);
    });
});

function loadPreview() {
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();

    $("#previewCard").show();
    $("#previewContent").hide();
    $("#previewError").hide();
    $("#previewLoading").show();

    $.ajax({
        url: "/integrations/quickbooks/preview",
        method: "POST",
        data: {
            start_date: startDate,
            end_date: endDate,
            csrf_token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                $("#previewCustomers").text(response.summary.customers);
                $("#previewProducts").text(response.summary.products);
                $("#previewInvoices").text(response.summary.invoices);
                $("#previewRevenue").text("$" + parseFloat(response.summary.total_revenue).toFixed(2));

                $("#previewLoading").hide();
                $("#previewContent").show();
            } else {
                $("#previewLoading").hide();
                $("#previewError").text(response.error || "Failed to load preview").show();
            }
        },
        error: function(xhr) {
            console.error("Preview error:", xhr);
            $("#previewLoading").hide();
            $("#previewError").text("Failed to load preview. Please try again.").show();
        }
    });
}
</script>';

require __DIR__ . '/../../layouts/app.php';
?>
