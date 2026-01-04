<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-mortarboard"></i> Courses</h2>
    <div>
        <?php if (hasPermission('courses.create')): ?>
        <a href="/store/courses/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Course
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/store/courses" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search courses..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Search</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <?php if (empty($courses)): ?>
    <div class="col-12">
        <div class="alert alert-info">No courses found</div>
    </div>
    <?php else: ?>
        <?php foreach ($courses as $course): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($course['name']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($course['course_code']) ?></h6>
                    <p class="card-text"><?= htmlspecialchars(substr($course['description'] ?? '', 0, 100)) ?>...</p>
                    
                    <hr>
                    
                    <div class="mb-2">
                        <i class="bi bi-clock"></i> <?= $course['duration_days'] ?> days
                    </div>
                    <div class="mb-2">
                        <i class="bi bi-people"></i> Max <?= $course['max_students'] ?> students
                    </div>
                    <div class="mb-3">
                        <i class="bi bi-currency-dollar"></i> <?= formatCurrency($course['price']) ?>
                    </div>
                    
                    <a href="/store/courses/<?= $course['id'] ?>" class="btn btn-sm btn-info me-2">
                        <i class="bi bi-eye"></i> View
                    </a>
                    <?php if (hasPermission('courses.edit')): ?>
                    <a href="/store/courses/<?= $course['id'] ?>/edit" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
