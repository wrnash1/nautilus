<?php
$pageTitle = 'Add Dive Site';
$activeMenu = 'dive-sites';

ob_start();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="bi bi-plus-circle"></i> Add New Dive Site</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/store/dive-sites" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/store/dive-sites/create">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Site Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Key Largo">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country" placeholder="e.g., USA">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="difficulty_level" class="form-label">Difficulty Level</label>
                                <select class="form-select" id="difficulty_level" name="difficulty_level">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="expert">Expert</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude" placeholder="25.0850">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude" placeholder="-80.4470">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="max_depth" class="form-label">Max Depth (meters)</label>
                                <input type="number" step="0.1" class="form-control" id="max_depth" name="max_depth">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="average_depth" class="form-label">Average Depth (meters)</label>
                                <input type="number" step="0.1" class="form-control" id="average_depth" name="average_depth">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="best_season" class="form-label">Best Season</label>
                            <input type="text" class="form-control" id="best_season" name="best_season" placeholder="e.g., May to September">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the dive site..."></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="/store/dive-sites" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Dive Site
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require BASE_PATH . '/app/Views/layouts/admin.php';
?>
