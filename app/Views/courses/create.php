<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-mortarboard"></i> Create Course</h2>
    <a href="/courses" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/courses">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="course_code" class="form-label">Course Code *</label>
                    <input type="text" class="form-control" id="course_code" name="course_code" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Course Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="duration_days" class="form-label">Duration (days) *</label>
                    <input type="number" class="form-control" id="duration_days" name="duration_days" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="max_students" class="form-label">Max Students *</label>
                    <input type="number" class="form-control" id="max_students" name="max_students" value="10" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="price" class="form-label">Price *</label>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="prerequisites" class="form-label">Prerequisites</label>
                <textarea class="form-control" id="prerequisites" name="prerequisites" rows="2"></textarea>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Course
                </button>
                <a href="/courses" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
