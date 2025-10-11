<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-check"></i> Enrollment Details</h2>
    <a href="/courses/enrollments" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <h5><?= htmlspecialchars($enrollment['student_name']) ?></h5>
        <p>Course: <?= htmlspecialchars($enrollment['course_name']) ?></p>
        <p>Status: <span class="badge bg-primary"><?= ucfirst($enrollment['status']) ?></span></p>
    </div>
</div>
