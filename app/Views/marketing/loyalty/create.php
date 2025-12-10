<?php
$pageTitle = 'Create Loyalty Program';
ob_start();
?>

<div class="mb-4">
    <h2>Create Loyalty Program</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('/marketing/loyalty') ?>">Loyalty Programs</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('/marketing/loyalty') ?>">
            <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Program Name *</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="points_per_dollar" class="form-label">Points Per Dollar *</label>
                    <input type="number" class="form-control" id="points_per_dollar" name="points_per_dollar" 
                           value="1" min="0" step="0.01" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="points_expiry_days" class="form-label">Points Expiry (Days)</label>
                    <input type="number" class="form-control" id="points_expiry_days" name="points_expiry_days" 
                           min="0" placeholder="Leave empty for no expiry">
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Create Program</button>
                <a href="<?= url('/marketing/loyalty') ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/app.php';
?>
