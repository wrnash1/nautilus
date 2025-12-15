<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Create New Announcement</h6>
                <a href="/store/storefront/announcements" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/store/storefront/announcements" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">Title (Optional)</label>
                            <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Holiday Sale">
                        </div>
                        <div class="col-md-4">
                            <label for="banner_type" class="form-label">Type</label>
                            <select class="form-select" id="banner_type" name="banner_type">
                                <option value="info">Info (Blue)</option>
                                <option value="warning">Warning (Yellow)</option>
                                <option value="danger">Urgent (Red)</option>
                                <option value="success">Success (Green)</option>
                                <option value="promotion">Promotion (Primary)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Message Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="3" required placeholder="Enter the main text for the banner..."></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="button_text" class="form-label">Button Text (Optional)</label>
                            <input type="text" class="form-control" id="button_text" name="button_text" placeholder="e.g. Shop Now">
                        </div>
                        <div class="col-md-6">
                            <label for="button_url" class="form-label">Button URL</label>
                            <input type="text" class="form-control" id="button_url" name="button_url" placeholder="e.g. /shop/sale">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date">
                            <div class="form-text">Leave empty to start immediately</div>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="datetime-local" class="form-control" id="end_date" name="end_date">
                            <div class="form-text">Leave empty for no expiration</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Active immediately</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
