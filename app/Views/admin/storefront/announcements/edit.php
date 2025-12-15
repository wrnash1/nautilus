<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Edit Announcement</h6>
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

                <form action="/store/storefront/announcements/<?= $announcement['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="title" class="form-label">Title (Optional)</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($announcement['title'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="banner_type" class="form-label">Type</label>
                            <select class="form-select" id="banner_type" name="banner_type">
                                <?php
                                $types = ['info' => 'Info (Blue)', 'warning' => 'Warning (Yellow)', 'danger' => 'Urgent (Red)', 'success' => 'Success (Green)', 'promotion' => 'Promotion (Primary)'];
                                foreach ($types as $val => $label):
                                ?>
                                <option value="<?= $val ?>" <?= ($announcement['banner_type'] == $val) ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Message Content <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="content" name="content" rows="3" required><?= htmlspecialchars($announcement['content']) ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="button_text" class="form-label">Button Text (Optional)</label>
                            <input type="text" class="form-control" id="button_text" name="button_text" value="<?= htmlspecialchars($announcement['button_text'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="button_url" class="form-label">Button URL</label>
                            <input type="text" class="form-control" id="button_url" name="button_url" value="<?= htmlspecialchars($announcement['button_url'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= $announcement['start_date'] ? date('Y-m-d\TH:i', strtotime($announcement['start_date'])) : '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= $announcement['end_date'] ? date('Y-m-d\TH:i', strtotime($announcement['end_date'])) : '' ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                             <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $announcement['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $announcement['display_order'] ?? 0 ?>">
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
