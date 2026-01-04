<?php
$pageTitle = 'Theme Designer';
$activeMenu = 'storefront';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-palette me-2"></i>Theme Designer
        </h1>
        <div>
            <a href="/store/storefront" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
            <button type="button" class="btn btn-primary" id="saveTheme">
                <i class="bi bi-check-lg me-1"></i>Save Changes
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Theme Selector -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Themes</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($allThemes ?? [] as $theme): ?>
                            <a href="#" class="list-group-item list-group-item-action theme-item <?= ($theme['is_active'] ?? false) ? 'active' : '' ?>"
                               data-theme-id="<?= $theme['id'] ?? 0 ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($theme['theme_name'] ?? 'Default') ?></span>
                                    <?php if ($theme['is_active'] ?? false): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#newThemeModal">
                        <i class="bi bi-plus-lg me-1"></i>Create New Theme
                    </button>
                </div>
            </div>
        </div>

        <!-- Theme Editor -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Colors</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Primary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="primaryColor"
                                       value="<?= htmlspecialchars($activeTheme['primary_color'] ?? '#0d6efd') ?>">
                                <input type="text" class="form-control" id="primaryColorText"
                                       value="<?= htmlspecialchars($activeTheme['primary_color'] ?? '#0d6efd') ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Secondary Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="secondaryColor"
                                       value="<?= htmlspecialchars($activeTheme['secondary_color'] ?? '#6c757d') ?>">
                                <input type="text" class="form-control" id="secondaryColorText"
                                       value="<?= htmlspecialchars($activeTheme['secondary_color'] ?? '#6c757d') ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Accent Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="accentColor"
                                       value="<?= htmlspecialchars($activeTheme['accent_color'] ?? '#0dcaf0') ?>">
                                <input type="text" class="form-control" id="accentColorText"
                                       value="<?= htmlspecialchars($activeTheme['accent_color'] ?? '#0dcaf0') ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Success Color</label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="successColor"
                                       value="<?= htmlspecialchars($activeTheme['success_color'] ?? '#198754') ?>">
                                <input type="text" class="form-control" id="successColorText"
                                       value="<?= htmlspecialchars($activeTheme['success_color'] ?? '#198754') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Typography</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Primary Font</label>
                        <select class="form-select" id="fontFamily">
                            <option value="'Inter', sans-serif">Inter</option>
                            <option value="'Roboto', sans-serif">Roboto</option>
                            <option value="'Open Sans', sans-serif">Open Sans</option>
                            <option value="'Lato', sans-serif">Lato</option>
                            <option value="'Poppins', sans-serif">Poppins</option>
                            <option value="'Montserrat', sans-serif">Montserrat</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="col-lg-3">
            <div class="card mb-4 sticky-top" style="top: 80px;">
                <div class="card-header">
                    <h5 class="mb-0">Live Preview</h5>
                </div>
                <div class="card-body p-0">
                    <div id="themePreview" class="p-3" style="background: #f8f9fa; min-height: 300px;">
                        <div class="mb-3 p-3 rounded" id="previewHeader" style="background: var(--primary-color, #0d6efd); color: white;">
                            <strong>Header</strong>
                        </div>
                        <div class="mb-2">
                            <button class="btn btn-sm me-1" id="previewBtnPrimary" style="background: var(--primary-color, #0d6efd); color: white;">Primary</button>
                            <button class="btn btn-sm" id="previewBtnSecondary" style="background: var(--secondary-color, #6c757d); color: white;">Secondary</button>
                        </div>
                        <p class="small text-muted">Sample text content here.</p>
                        <a href="#" id="previewLink" style="color: var(--accent-color, #0dcaf0);">Sample Link</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Theme Modal -->
<div class="modal fade" id="newThemeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/store/storefront/theme/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Theme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Theme Name</label>
                        <input type="text" name="theme_name" class="form-control" required>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="copy_current" class="form-check-input" id="copyCurrent" checked>
                        <label class="form-check-label" for="copyCurrent">Copy settings from current theme</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Theme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync color pickers with text inputs
    ['primary', 'secondary', 'accent', 'success'].forEach(function(color) {
        var colorPicker = document.getElementById(color + 'Color');
        var textInput = document.getElementById(color + 'ColorText');

        colorPicker.addEventListener('input', function() {
            textInput.value = this.value;
            updatePreview();
        });

        textInput.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                colorPicker.value = this.value;
                updatePreview();
            }
        });
    });

    function updatePreview() {
        var preview = document.getElementById('themePreview');
        preview.style.setProperty('--primary-color', document.getElementById('primaryColor').value);
        preview.style.setProperty('--secondary-color', document.getElementById('secondaryColor').value);
        preview.style.setProperty('--accent-color', document.getElementById('accentColor').value);

        document.getElementById('previewHeader').style.background = document.getElementById('primaryColor').value;
        document.getElementById('previewBtnPrimary').style.background = document.getElementById('primaryColor').value;
        document.getElementById('previewBtnSecondary').style.background = document.getElementById('secondaryColor').value;
        document.getElementById('previewLink').style.color = document.getElementById('accentColor').value;
    }

    // Save theme
    document.getElementById('saveTheme').addEventListener('click', function() {
        // Implement save functionality
        alert('Theme saved! (Implementation pending)');
    });
});
</script>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/admin.php';
?>
